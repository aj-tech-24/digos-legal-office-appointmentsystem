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
    protected string $apiUrl;    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->apiUrl = config('services.gemini.api_url', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');
    }

    /**
     * Process a case narrative and generate recommendations
     */
    public function processNarrative(string $narrative): AiRecommendation
    {
        // Check for existing recommendation based on narrative only
        $narrativeHash = AiRecommendation::generateHash($narrative);
        $existing = AiRecommendation::where('narrative_hash', $narrativeHash)->first();

        if ($existing) {
            return $existing;
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
            // Return mock response for development
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
                
                return $this->extractJsonFromResponse($text);
            }

            Log::error('Gemini API Error', ['response' => $response->body()]);
            return $this->getMockResponse($narrative);

        } catch (\Exception $e) {
            Log::error('Gemini API Exception', ['error' => $e->getMessage()]);
            return $this->getMockResponse($narrative);
        }
    }

    /**
     * Build the prompt for AI analysis
     */
    protected function buildPrompt(string $narrative): string
    {
        $specializations = Specialization::active()->pluck('name')->toArray();
        $specializationList = implode(', ', $specializations);

        return <<<PROMPT
You are a legal case analyzer for the Digos City Legal Office. Analyze the following case narrative and provide a structured response.

Available legal service categories: {$specializationList}

Case Narrative:
"{$narrative}"

Please analyze this case and respond ONLY with a valid JSON object (no markdown, no explanation) with the following structure:
{
    "professional_summary": "A detailed 4-5 sentence professional summary describing the case, key legal issues, and the client's request",
    "detected_services": {
        "primary": "The main legal service category",
        "secondary": "A secondary category if applicable, or null"
    },
    "complexity_level": "simple|moderate|complex",
    "estimated_duration_minutes": 45|60|90|120,
    "document_checklist": [
        {"item": "Document name", "required": true|false, "description": "Why this document is needed"}
    ],
    "key_issues": ["Issue 1", "Issue 2"],
    "recommended_approach": "Brief recommendation for handling this case"
}

Base complexity on:
- Simple: Straightforward matters like document notarization, simple consultations (30-45 mins)
- Moderate: Cases requiring legal research or multiple consultations (60-90 mins)
- Complex: Cases involving litigation, multiple parties, or extensive documentation (120 mins)
PROMPT;
    }

    /**
     * Extract JSON from AI response
     */
    protected function extractJsonFromResponse(string $response): array
    {
        // Try to find JSON in the response
        $response = trim($response);
        
        // Remove markdown code blocks if present
        $response = preg_replace('/```json\s*/i', '', $response);
        $response = preg_replace('/```\s*/', '', $response);
        
        try {
            $decoded = json_decode($response, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        } catch (\Exception $e) {
            // Continue to regex extraction
        }

        // Try to extract JSON object
        if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
            try {
                $decoded = json_decode($matches[0], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                // Return empty array
            }
        }

        return [];
    }

    /**
     * Parse the AI response into structured data
     */
    protected function parseAiResponse(array $aiResponse, string $narrative): array
    {
        $detectedServices = [];
        
        if (isset($aiResponse['detected_services'])) {
            if (is_array($aiResponse['detected_services'])) {
                if (isset($aiResponse['detected_services']['primary'])) {
                    $detectedServices = $aiResponse['detected_services'];
                } else {
                    $detectedServices = [
                        'primary' => $aiResponse['detected_services'][0] ?? 'General Consultation',
                        'secondary' => $aiResponse['detected_services'][1] ?? null,
                    ];
                }
            }
        }

        return [
            'professional_summary' => $aiResponse['professional_summary'] ?? $this->generateFallbackSummary($narrative),
            'detected_services' => $detectedServices,
            'complexity_level' => $aiResponse['complexity_level'] ?? 'moderate',
            'estimated_duration_minutes' => $aiResponse['estimated_duration_minutes'] ?? 60,
            'document_checklist' => $aiResponse['document_checklist'] ?? [],
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
    }    /**
     * Get mock response for development/testing
     */
    protected function getMockResponse(string $narrative): array
    {
        // Detect keywords for service classification
        $narrativeLower = strtolower($narrative);
        
        $services = [
            'primary' => 'General Consultation',
            'secondary' => null,
        ];

        // Define keyword mappings for each specialization
        $serviceKeywords = [
            'Family Law' => ['family', 'divorce', 'custody', 'marriage', 'annulment', 'child support', 'alimony', 'adoption', 'separation', 'spouse', 'husband', 'wife', 'children', 'parent'],
            'Criminal Law' => ['criminal', 'arrest', 'crime', 'theft', 'assault', 'murder', 'robbery', 'fraud', 'drug', 'police', 'jail', 'prison', 'accused', 'victim', 'complaint', 'barangay blotter'],
            'Labor Law' => ['labor', 'employment', 'work', 'employer', 'employee', 'salary', 'wages', 'termination', 'fired', 'dismissed', 'resignation', 'overtime', 'benefits', 'dole', 'nlrc'],
            'Property Law' => ['property', 'land', 'title', 'deed', 'real estate', 'lot', 'house', 'boundary', 'ejectment', 'tenant', 'lease', 'mortgage', 'foreclosure', 'inheritance', 'transfer'],
            'Civil Law' => ['contract', 'agreement', 'breach', 'damages', 'obligation', 'debt', 'loan', 'collection', 'dispute', 'sue', 'lawsuit', 'liability', 'negligence'],
            'Administrative Law' => ['government', 'permit', 'license', 'agency', 'administrative', 'appeal', 'civil service', 'public official', 'ordinance', 'regulation'],
            'Corporate Law' => ['business', 'corporation', 'company', 'partnership', 'sec', 'incorporation', 'shareholders', 'board', 'merger', 'acquisition'],
            'Tax Law' => ['tax', 'bir', 'income tax', 'vat', 'assessment', 'tax evasion', 'tax return', 'audit'],
            'Immigration Law' => ['immigration', 'visa', 'passport', 'deportation', 'alien', 'foreigner', 'naturalization', 'citizenship'],
            'Notarial Services' => ['notarize', 'notary', 'affidavit', 'acknowledgment', 'oath', 'jurat', 'certified copy'],
        ];

        // Find matching services
        $matchedServices = [];
        foreach ($serviceKeywords as $service => $keywords) {
            $matchCount = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($narrativeLower, $keyword)) {
                    $matchCount++;
                }
            }
            if ($matchCount > 0) {
                $matchedServices[$service] = $matchCount;
            }
        }

        // Sort by match count and get top 2
        arsort($matchedServices);
        $topServices = array_keys(array_slice($matchedServices, 0, 2, true));

        if (count($topServices) > 0) {
            $services['primary'] = $topServices[0];
            if (count($topServices) > 1) {
                $services['secondary'] = $topServices[1];
            }
        }

        // Determine complexity based on word count and keyword density
        $wordCount = str_word_count($narrative);
        $complexity = 'moderate';
        $duration = 60;

        if ($wordCount < 50) {
            $complexity = 'simple';
            $duration = 45;
        } elseif ($wordCount > 150 || count($matchedServices) > 2) {
            $complexity = 'complex';
            $duration = 120;
        }

        // Generate service-specific document checklist
        $documentChecklist = $this->getDocumentChecklist($services['primary']);

        return [
            'professional_summary' => 'The client seeks legal consultation regarding ' . $services['primary'] . ' matters. Based on the provided narrative, this case requires professional legal assessment and guidance.',
            'detected_services' => $services,
            'complexity_level' => $complexity,
            'estimated_duration_minutes' => $duration,
            'document_checklist' => $documentChecklist,
            'key_issues' => ['Legal consultation required', 'Document review needed'],
            'recommended_approach' => 'Schedule an initial consultation to assess the case details and provide legal advice.',
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
