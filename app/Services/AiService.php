<?php

namespace App\Services;

use App\Models\AiRecommendation;
use App\Models\AiRecommendationItem;
use App\Models\Lawyer;
use App\Models\Specialization;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->apiUrl = config('services.gemini.api_url', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');
    }

    /**
     * Process a case narrative and generate recommendations
     */
    public function processNarrative(string $narrative): AiRecommendation
    {
        // Check for existing recommendation based on narrative only
        $narrativeHash = AiRecommendation::generateHash($narrative);
        $existing = AiRecommendation::where('narrative_hash', $narrativeHash)->first();

        // Only trust cached records that came from a REAL successful Gemini API call.
        // Records with _source = 'mock', 'gemini_fallback_mock', 'gemini_exception_mock', or
        // 'unknown' were created during quota errors / no-key periods and must be refreshed.
        $cachedSource = $existing->raw_ai_response['_source'] ?? 'unknown';
        $isTrustedCache = $existing && $cachedSource === 'gemini';

        if ($isTrustedCache) {
            return $existing;
        }

        // Delete stale/fallback record — will be replaced with a fresh real API call
        if ($existing) {
            $existing->delete();
        }

        // Process with AI
        $aiResponse = $this->callGeminiApi($narrative);

        // Parse the AI response
        $parsedResponse = $this->parseAiResponse($aiResponse, $narrative);

        // Use try-catch to handle race conditions
        try {
            // Create recommendation record
            $recommendation = AiRecommendation::create([
                'narrative_hash' => $narrativeHash,
                'original_narrative' => $narrative,
                'professional_summary' => $parsedResponse['professional_summary'] ?? '',
                'detected_services' => $parsedResponse['detected_services'] ?? [],
                'complexity_level' => $parsedResponse['complexity_level'] ?? 'moderate',
                'estimated_duration_minutes' => $parsedResponse['estimated_duration_minutes'] ?? 60,
                'document_checklist' => $parsedResponse['document_checklist'] ?? [],
                'raw_ai_response' => $aiResponse,
            ]);

            // Score and rank lawyers
            $this->scoreLawyers($recommendation);

            return $recommendation;
        } catch (\Illuminate\Database\QueryException $e) {
            // If duplicate key error, fetch the existing record
            if ($e->errorInfo[1] == 1062) {
                return AiRecommendation::where('narrative_hash', $narrativeHash)->first();
            }
            throw $e;
        }
    }

    /**
     * Call Gemini API
     */
    protected function callGeminiApi(string $narrative): array
    {
        if (empty($this->apiKey)) {
            return $this->getMockResponse($narrative);
        }

        try {
            $prompt = $this->buildPrompt($narrative);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 2048,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

                $parsed = $this->extractJsonFromResponse($text);
                $parsed['_source'] = 'gemini';
                return $parsed;
            }

            Log::error('AiService: Gemini API returned HTTP error', [
                'status'   => $response->status(),
                'body'     => substr($response->body(), 0, 500),
            ]);
            $fallback = $this->getMockResponse($narrative);
            $fallback['_source'] = 'gemini_fallback_mock'; // tag: API called but failed
            return $fallback;

        } catch (\Exception $e) {
            Log::error('AiService: Gemini API Exception', [
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            $fallback = $this->getMockResponse($narrative);
            $fallback['_source'] = 'gemini_exception_mock';
            return $fallback;
        }
    }

    /**
     * Build the prompt for AI analysis
     */
    protected function buildPrompt(string $narrative): string
    {
        // Kuhaon ang listahan sa services gikan sa database
        $specializations = Specialization::active()->pluck('name')->toArray();
        $specializationList = implode(', ', $specializations);

        return <<<PROMPT
    You are a senior Legal Intake Specialist for a Philippine Law Firm.
    Your goal is to convert the client's raw narrative (English, Tagalog, or Bisaya/Cebuano) into a **professional, detailed legal summary** in English.

    **Context for Translation (Bisaya/Tagalog Keywords):**
    - "Yuta", "Lupa", "Titulo", "Silingan", "Harass", "Ali" -> **Property Law** (Neighbor Dispute/Land)
    - "Asawa", "Bana", "Sustento", "Support", "Live-in", "Bun-og" -> **Family Law** (VAWC/Support)
    - "Utang", "Bayad", "Singil", "Estafa", "Bounce Check" -> **Civil Law** (Small Claims/Collection)
    - "Pulis", "Blotter", "Kulata", "Sumbag", "Kawat" -> **Criminal Law**
    - "Trabaho", "Sweldo", "Dismiss", "Backpay" -> **Labor Law**
    
    **Categorization Rules (Priority Logic):**
    1. **Family vs. Criminal:** If violence ("kulata", "sumbag", "threat") involves a spouse, partner, or child, prioritize **Family Law** (VAWC). If it involves a stranger or neighbor, prioritize **Criminal Law**.
    2. **Property vs. Criminal:** If the conflict starts with a land/boundary dispute ("ilog yuta") leading to harassment, prioritize **Property Law** as Primary, and Criminal Law as Secondary.
    3. **Labor vs. Civil:** If the unpaid money is from an employer ("amo", "boss"), prioritize **Labor Law**. If it is a personal loan between friends/others, prioritize **Civil Law**.

    **Client's Raw Narrative:**
    "{$narrative}"

    **Complexity Rubric (STRICTLY FOLLOW THIS):**
    - **Simple:** Notarial services, simple inquiries, single administrative requirement (e.g., "Pa notaryo", "Unsay requirements sa visa").
    - **Moderate:** Standard disputes, collection of sum of money, labor complaints, child support requests, simple ejectment.
    - **Complex:** Serious crimes (murder, drugs, rape, non-bailable offenses), multiple legal issues (e.g., land dispute leading to physical injury), cases involving corporations, large estates, Annulment, or Appeals.

    **INSTRUCTIONS:**
    1. **Analyze:** Identify the core legal issue, the incident (The "Naunsa"), and the client's goal (The "Gusto").
    2. **Summarize (CRITICAL):** Create a detailed "Professional Summary" (3-5 sentences).
    - Structure: "The client seeks assistance regarding [Legal Category]. The client states that [Details of Incident]. The client desires to [Desired Action]."
    3. **Categorize:** Select the best service from this list ONLY: {$specializationList}. If unclear, use "General Consultation".
    4. **Determine Complexity:** Use the Rubric above.
    5. **Docs Checklist:** Suggest 2-3 specific Philippine legal documents required (e.g., "Barangay Certificate to File Action", "Police Blotter", "Marriage Certificate").

    **Output Requirement:**
    Return ONLY valid JSON. Do not use markdown code blocks.

    JSON Structure:
    {
        "professional_summary": "Detailed English legal summary...",
        "detected_services": {
            "primary": "Category Name from list",
            "secondary": "Alternative Category from list or null"
        },
        "complexity_level": "simple|moderate|complex",
        "estimated_duration_minutes": 30|45|60,
        "document_checklist": [
            {"item": "Document Name", "required": true, "description": "Short reason why"}
        ]
    }
    PROMPT;
        }

    /**
     * Extract JSON from AI response — handles markdown fences and trailing text
     */
    protected function extractJsonFromResponse(string $response): array
    {
        $response = trim($response);

        // ── Strategy 1: Extract from inside ```json ... ``` fences (most common with Gemini)
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $response, $fenceMatch)) {
            $candidate = trim($fenceMatch[1]);
            $decoded = json_decode($candidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // ── Strategy 2: Entire response is plain JSON (no fences)
        $decoded = json_decode($response, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // ── Strategy 3: Find first { and last } and extract that block
        $start = strpos($response, '{');
        $end   = strrpos($response, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $candidate = substr($response, $start, $end - $start + 1);
            $decoded = json_decode($candidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        Log::error('AiService: Failed to extract JSON from Gemini response', [
            'response_preview' => substr($response, 0, 400),
        ]);

        return [];
    }

    /**
     * Parse the AI response into structured data
     */
    protected function parseAiResponse(array $aiResponse, string $narrative): array
    {
        // 1. Get Services
        $primaryService = $aiResponse['detected_services']['primary'] ?? 'General Consultation';
        if (is_array($primaryService)) $primaryService = $primaryService[0] ?? 'General Consultation';

        $detectedServices = [
            'primary' => $primaryService,
            'secondary' => $aiResponse['detected_services']['secondary'] ?? null,
        ];

        // 2. Logic Update: Dili na nato i-force og "General Consultation" basta-basta.
        // Check lang nato kung nonsense ba kaayo ang input (sobra ka mubo).
        $isVeryShort = strlen(trim($narrative)) < 15; // Example: "hi" or "test"

        if ($isVeryShort) {
            $summary = "The client seeks general legal consultation. Please interview the client to determine specific needs.";
            $complexity = 'simple';
            $duration = 30;
        } else {
            // TRUST THE AI: Gamiton nato ang detailed summary gikan sa buildPrompt
            $summary = $aiResponse['professional_summary'] ?? "Client narrative requires lawyer review. Input: " . $narrative;
            $complexity = $aiResponse['complexity_level'] ?? 'moderate';
            $duration = $aiResponse['estimated_duration_minutes'] ?? 60;
        }

        // 3. Get Checklist
        $checklist = $aiResponse['document_checklist'] ?? $this->getDocumentChecklist($detectedServices['primary']);

        return [
            'professional_summary' => $summary,
            'detected_services' => $detectedServices,
            'complexity_level' => $complexity,
            'estimated_duration_minutes' => $duration,
            'document_checklist' => $checklist,
        ];
    }

    /**
     * Generate a fallback summary if AI fails
     */
    protected function generateFallbackSummary(string $narrative): string
    {
        $truncated = substr($narrative, 0, 200);
        return "Client requires legal consultation regarding: " . $truncated . (strlen($narrative) > 200 ? '...' : '');
    }

    /**
     * Score lawyers based on the recommendation
     */
    public function scoreLawyers(AiRecommendation $recommendation): void
    {
        $lawyers = Lawyer::approved()->with(['specializations', 'schedules', 'appointments'])->get();
        $detectedServices = $recommendation->detected_services;
        $primaryService = $detectedServices['primary'] ?? '';
        $secondaryService = $detectedServices['secondary'] ?? '';

        $scores = [];

        foreach ($lawyers as $lawyer) {
            $breakdown = $this->calculateLawyerScore($lawyer, $primaryService, $secondaryService, $recommendation);
            $totalScore = array_sum($breakdown);

            $scores[] = [
                'lawyer_id' => $lawyer->id,
                'match_score' => $totalScore,
                'score_breakdown' => $breakdown,
            ];
        }

        // Sort by score descending
        usort($scores, fn($a, $b) => $b['match_score'] <=> $a['match_score']);

        // Create recommendation items with rank
        foreach ($scores as $rank => $score) {
            AiRecommendationItem::create([
                'ai_recommendation_id' => $recommendation->id,
                'lawyer_id' => $score['lawyer_id'],
                'match_score' => $score['match_score'],
                'score_breakdown' => $score['score_breakdown'],
                'rank' => $rank + 1,
            ]);
        }
    }

    /**
     * Calculate score for a single lawyer
     */
    protected function calculateLawyerScore(Lawyer $lawyer, string $primaryService, ?string $secondaryService, AiRecommendation $recommendation): array
    {
        $scores = [
            'specialization_match' => 0,
            'similar_cases_handled' => 0,
            'availability_match' => 0,
            'experience' => 0,
            'language_match' => 0,
            'current_workload' => 0,
        ];

        // Specialization Match (max 40)
        $specializations = $lawyer->specializations->pluck('name')->map(fn($s) => strtolower($s))->toArray();
        $primaryMatch = in_array(strtolower($primaryService), $specializations);
        $secondaryMatch = $secondaryService && in_array(strtolower($secondaryService), $specializations);
        
        if ($primaryMatch) {
            $scores['specialization_match'] = 30;
            if ($secondaryMatch) {
                $scores['specialization_match'] = 40;
            }
        } elseif ($secondaryMatch) {
            $scores['specialization_match'] = 20;
        }

        // Similar Cases Handled (max 15) - based on completed appointments with similar services
        $similarCases = $lawyer->appointments()
            ->where('status', 'completed')
            ->whereJsonContains('detected_services->primary', $primaryService)
            ->count();
        $scores['similar_cases_handled'] = min(15, $similarCases * 3);

        // Availability Match (max 15) - based on schedule coverage
        $scheduleDays = $lawyer->schedules()->where('is_available', true)->count();
        $scores['availability_match'] = min(15, ($scheduleDays / 5) * 15);

        // Experience (max 10)
        $years = $lawyer->years_of_experience;
        if ($years >= 10) {
            $scores['experience'] = 10;
        } elseif ($years >= 5) {
            $scores['experience'] = 7;
        } elseif ($years >= 2) {
            $scores['experience'] = 5;
        } else {
            $scores['experience'] = 3;
        }

        // Language Match (max 10) - assuming Filipino/English for now
        $languages = $lawyer->languages ?? [];
        if (in_array('Filipino', $languages) || in_array('Tagalog', $languages)) {
            $scores['language_match'] += 5;
        }
        if (in_array('English', $languages)) {
            $scores['language_match'] += 3;
        }
        if (in_array('Bisaya', $languages) || in_array('Cebuano', $languages)) {
            $scores['language_match'] += 2;
        }
        $scores['language_match'] = min(10, $scores['language_match']);

        // Current Workload (max 10) - less appointments = higher score
        $todayAppointments = $lawyer->getCurrentWorkload();
        $maxDaily = $lawyer->max_daily_appointments;
        $workloadRatio = $maxDaily > 0 ? ($maxDaily - $todayAppointments) / $maxDaily : 0;
        $scores['current_workload'] = round($workloadRatio * 10);

        return $scores;
    }    
    
    /**
     * Get mock response for development/testing
     */
    protected function getMockResponse(string $narrative): array
    {
        // 1. Pre-process text
        $narrativeLower = strtolower($narrative);
        $wordCount = str_word_count($narrative);
        
        // 2. Define High Severity Keywords (Automatic Complex & High Priority)
        $highSeverityKeywords = [
            'murder', 'homicide', 'rape', 'drugs', 'shabu', 'kidnap', 'carnapping', 'rebellion',
            'patay', 'pumatay', 'lugos', 'droga', 'pusil', 'baril', 'dunggab'
        ];

        // Check for severity first
        $isSevere = false;
        foreach ($highSeverityKeywords as $severeWord) {
            if (str_contains($narrativeLower, $severeWord)) {
                $isSevere = true;
                break;
            }
        }

        // 3. Define Categorization Keywords
        $serviceKeywords = [
            'Family Law' => [
                'family', 'divorce', 'custody', 'marriage', 'annulment', 'child support', 'spousal support', 'adoption', 'separation', 'vawc',
                'asawa', 'bana', 'anak', 'sustento', 'live-in', 'bun-og', 'panagbuwag', 'relasyon'
            ],
            'Criminal Law' => [
                'criminal', 'arrest', 'theft', 'assault', 'robbery', 'fraud', 'police', 'jail', 'prison', 'blotter', 'warrant',
                'pulis', 'priso', 'kawat', 'sumbag', 'kaso', 'pyansa', 'holdup', 'ilad', 'estafa'
            ],
            'Labor Law' => [
                'labor', 'employment', 'employer', 'employee', 'salary', 'wages', 'termination', 'dismissed', 'resignation', 'overtime', 'dole', 'nlrc',
                'trabaho', 'sweldo', 'amo', 'backpay', 'separation pay', 'illegal dismissal', 'endo'
            ],
            'Property Law' => [
                'property', 'land', 'title', 'deed', 'real estate', 'boundary', 'ejectment', 'tenant', 'lease', 'mortgage', 'inheritance',
                'yuta', 'lupa', 'titulo', 'silingan', 'ali', 'harass', 'encroach', 'renta', 'papahawa', 'panulundon'
            ],
            'Civil Law' => [
                'contract', 'agreement', 'breach', 'damages', 'debt', 'loan', 'collection', 'sue', 'negligence',
                'utang', 'bayad', 'singil', 'bounce check', 'kasabutan', 'lugi', 'danyos'
            ],
            'Administrative Law' => ['government', 'permit', 'license', 'civil service', 'ordinance', 'munisipyo', 'mayor', 'kapitan'],
            'Corporate Law' => ['corporation', 'company', 'partnership', 'sec', 'incorporation', 'stocks', 'shares', 'negosyo'],
            'Tax Law' => ['tax', 'bir', 'income tax', 'vat', 'audit', 'buhis', 'amilyar'],
            'Immigration Law' => ['immigration', 'visa', 'passport', 'deportation', 'ofw', 'abroad'],
            'Notarial Services' => ['notarize', 'notary', 'affidavit', 'spaa', 'panotaryo'],
        ];

        // 4. Scoring Logic (Count matches)
        $matchedServices = [];
        foreach ($serviceKeywords as $service => $keywords) {
            $matchCount = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($narrativeLower, $keyword)) {
                    $matchCount++;
                }
            }
            if ($matchCount > 0) {
                // Bonus points for High Severity keywords matching Criminal Law
                if ($isSevere && $service === 'Criminal Law') {
                    $matchCount += 10; 
                }
                $matchedServices[$service] = $matchCount;
            }
        }

        // 5. Determine Winners
        arsort($matchedServices); // Sort highest first
        $topServices = array_keys(array_slice($matchedServices, 0, 2, true));

        $primaryService = $topServices[0] ?? 'General Consultation';
        $secondaryService = $topServices[1] ?? null;

        // 6. Determine Complexity & Duration
        if ($isSevere) {
            $complexity = 'complex';
            $duration = 90; // Severe cases need more time
        } elseif ($wordCount < 20 && empty($matchedServices)) {
            $complexity = 'simple';
            $duration = 30;
        } elseif ($wordCount > 100 || count($matchedServices) >= 2) {
            $complexity = 'complex';
            $duration = 60;
        } else {
            $complexity = 'moderate';
            $duration = 45;
        }

        // 7. Generate Output
        $summary = match($primaryService) {
            'General Consultation' => "The client seeks general legal consultation. Please interview to determine specifics.",
            'Criminal Law' => $isSevere 
                ? "URGENT: Client reports a serious criminal incident involving potential violence or heavy penalties. Immediate legal assessment required." 
                : "The client seeks assistance regarding a Criminal Law matter based on reported incidents.",
            default => "The client requests assistance regarding {$primaryService}. Based on keywords detected, the case involves issues related to this field."
        };

        return [
            'professional_summary'     => $summary,
            'detected_services'        => [
                'primary'   => $primaryService,
                'secondary' => $secondaryService
            ],
            'complexity_level'         => $complexity,
            'estimated_duration_minutes' => $duration,
            'document_checklist'       => $this->getDocumentChecklist($primaryService),
            'key_issues'               => ['Legal assessment required', 'Document review'],
            'recommended_approach'     => 'Schedule an immediate consultation.',
            '_source'                  => 'mock', // tag: local keyword-matching used
        ];
    }

    /**
     * Get document checklist based on service type
     */
    protected function getDocumentChecklist(string $serviceType): array
    {
        $baseDocuments = [
            ['item' => 'Valid Government ID', 'required' => true, 'description' => 'For client identification'],
        ];

        $serviceDocuments = [
            'Family Law' => [
                ['item' => 'Marriage Certificate', 'required' => true, 'description' => 'Proof of marriage if applicable'],
                ['item' => 'Birth Certificates', 'required' => false, 'description' => 'For children involved in the case'],
            ],
            'Criminal Law' => [
                ['item' => 'Police Blotter/Report', 'required' => true, 'description' => 'Official incident report'],
                ['item' => 'Barangay Certification', 'required' => false, 'description' => 'If mediation was attempted'],
            ],
            'Labor Law' => [
                ['item' => 'Employment Contract', 'required' => true, 'description' => 'Proof of employment terms'],
                ['item' => 'Payslips', 'required' => false, 'description' => 'Recent salary records'],
            ],
            'Property Law' => [
                ['item' => 'Land Title/TCT/OCT', 'required' => true, 'description' => 'Proof of property ownership'],
                ['item' => 'Tax Declaration', 'required' => true, 'description' => 'Current real property tax records'],
            ],
            'Civil Law' => [
                ['item' => 'Contract/Agreement', 'required' => true, 'description' => 'The disputed document'],
                ['item' => 'Correspondence', 'required' => false, 'description' => 'Any written communications'],
            ],
        ];

        $documents = $baseDocuments;
        if (isset($serviceDocuments[$serviceType])) {
            $documents = array_merge($documents, $serviceDocuments[$serviceType]);
        } else {
            $documents[] = ['item' => 'Relevant Documents', 'required' => true, 'description' => 'Any documents related to the case'];
        }
        $documents[] = ['item' => 'Timeline of Events', 'required' => false, 'description' => 'A written summary of events in chronological order'];

        return $documents;
    }
}
