<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BookingDraft;
use App\Models\ClientRecord;
use App\Models\Lawyer;
use App\Models\LawyerSchedule;
use App\Services\AiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Mail\AppointmentSubmitted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Show the booking page
     */
    public function index(Request $request): View
    {
        // Clean up expired drafts first
        BookingDraft::cleanupExpired();
        
        // Get session ID from Laravel session (more reliable than cookies for AJAX)
        $sessionId = session('booking_session_id');
        $draft = null;
        
        // Try to find existing valid draft
        if ($sessionId) {
            $draft = BookingDraft::where('session_id', $sessionId)
                ->where('expires_at', '>', now())
                ->first();
        }
        
        // If no valid draft found, create a new session
        if (!$draft) {
            $sessionId = Str::uuid()->toString();
            $draft = BookingDraft::create([
                'session_id' => $sessionId,
            ]);
            
            // Store in Laravel session
            session(['booking_session_id' => $sessionId]);
        }

        return view('client.book', [
            'draft' => $draft,
            'sessionId' => $sessionId,
        ]);
    }

    /**
     * Process step submission
     */
    public function processStep(Request $request, int $step): JsonResponse
    {
        // Get session ID from form input (sent with every AJAX request)
        $sessionId = $request->input('session_id');
        
        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'error' => 'Session ID missing. Please refresh the page.',
            ], 400);
        }

        $draft = BookingDraft::where('session_id', $sessionId)->first();

        if (!$draft) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found. Please refresh the page and try again.',
            ], 400);
        }

        if ($draft->isExpired()) {
            $draft->delete();
            return response()->json([
                'success' => false,
                'error' => 'Session expired. Please refresh the page to start over.',
            ], 400);
        }
        
        // Extend expiration on each step
        $draft->extendExpiration(24);

        // Process based on step
        $result = match ($step) {
            1 => $this->processStep1($request, $draft),
            2 => $this->processStep2($request, $draft),
            3 => $this->processStep3($request, $draft),
            4 => $this->processStep4($request, $draft),
            5 => $this->processStep5($request, $draft),
            default => ['success' => false, 'error' => 'Invalid step'],
        };

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        // Render next step
        $nextStep = $step + 1;
        $html = $this->renderStep($nextStep, $draft);

        return response()->json([
            'success' => true,
            'html' => $html,
            'step' => $nextStep,
        ]);
    }

    /**
     * Step 1: Privacy Consent
     */
    protected function processStep1(Request $request, BookingDraft $draft): array
    {
        $validator = Validator::make($request->all(), [
            'privacy_consent' => 'required|accepted',
        ], [
            'privacy_consent.required' => 'You must accept the privacy policy to continue.',
            'privacy_consent.accepted' => 'You must accept the privacy policy to continue.',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        $draft->update(['privacy_accepted' => true]);
        $draft->nextStep();

        return ['success' => true];
    }

    /**
     * Step 2: Case Narrative
     */
    protected function processStep2(Request $request, BookingDraft $draft): array
    {
        $validator = Validator::make($request->all(), [
            'narrative' => 'required|min:30|max:5000',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255', // Added validation
        ], [
            'narrative.required' => 'Please describe your legal concern.',
            'narrative.min' => 'Please provide more details (at least 30 characters).',
            'address.required' => 'Please provide your barangay or address.',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Process narrative with AI
        $recommendation = $this->aiService->processNarrative($request->narrative);

        // Store in draft
        $draft->updateState([
            'narrative' => $request->narrative,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address, // Store address
            'ai_recommendation_id' => $recommendation->id,
        ]);

        $draft->update(['client_email' => $request->email]);
        $draft->nextStep();

        return ['success' => true];
    }

    /**
     * Step 3: Review Detected Service (AI Results)
     */
    protected function processStep3(Request $request, BookingDraft $draft): array
    {
        // User confirms the AI analysis is correct
        $validator = Validator::make($request->all(), [
            'confirm_analysis' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        $draft->nextStep();

        return ['success' => true];
    }

    /**
     * Step 4: Select Lawyer
     */
    protected function processStep4(Request $request, BookingDraft $draft): array
    {
        $validator = Validator::make($request->all(), [
            'lawyer_id' => 'required|exists:lawyers,id',
        ], [
            'lawyer_id.required' => 'Please select a lawyer.',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        $lawyer = Lawyer::approved()->find($request->lawyer_id);
        
        if (!$lawyer) {
            return ['success' => false, 'errors' => ['lawyer_id' => ['Selected lawyer is not available.']]];
        }

        $draft->updateState(['lawyer_id' => $request->lawyer_id]);
        $draft->nextStep();

        return ['success' => true];
    }

    /**
     * Step 5: Select Schedule
     */
    protected function processStep5(Request $request, BookingDraft $draft): array
    {
        $validator = Validator::make($request->all(), [
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required',
        ], [
            'appointment_date.required' => 'Please select a date.',
            'appointment_date.after' => 'Please select a future date.',
            'appointment_time.required' => 'Please select a time slot.',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        $draft->updateState([
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
        ]);
        $draft->nextStep();

        return ['success' => true];
    }

    /**
     * Submit the final booking
     */
    public function submit(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id');
        
        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'error' => 'Session ID missing. Please refresh the page.',
            ], 400);
        }

        $draft = BookingDraft::where('session_id', $sessionId)->first();

        if (!$draft) {
            return response()->json([
                'success' => false,
                'error' => 'Session not found. Please refresh the page and try again.',
            ], 400);
        }

        if ($draft->isExpired()) {
            $draft->delete();
            return response()->json([
                'success' => false,
                'error' => 'Session expired. Please refresh the page to start over.',
            ], 400);
        }

        $state = $draft->draft_state;

        // Create or find client record
        $clientRecord = ClientRecord::findOrCreateByEmail($state['email'], [
            'first_name' => $state['first_name'],
            'last_name' => $state['last_name'],
            'phone' => $state['phone'] ?? null,
            'address' => $state['address'] ?? null, // Ensure address is passed
        ]);

        // Update address if it exists but wasn't set or changed
        if (!empty($state['address'])) {
            $clientRecord->update(['address' => $state['address']]);
        }

        // Get AI recommendation
        $recommendation = \App\Models\AiRecommendation::find($state['ai_recommendation_id']);

        // Parse appointment datetime
        $appointmentDate = Carbon::parse($state['appointment_date']);
        $timeParts = explode('-', $state['appointment_time']);
        $startTime = Carbon::parse($appointmentDate->format('Y-m-d') . ' ' . trim($timeParts[0]));
        $endTime = Carbon::parse($appointmentDate->format('Y-m-d') . ' ' . trim($timeParts[1]));

        // Create appointment
        $appointment = Appointment::create([
            'client_record_id' => $clientRecord->id,
            'lawyer_id' => $state['lawyer_id'],
            'ai_recommendation_id' => $recommendation?->id,
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
            'estimated_duration_minutes' => $recommendation?->estimated_duration_minutes ?? 60,
            'narrative' => $state['narrative'],
            'professional_summary' => $recommendation?->professional_summary,
            'detected_services' => $recommendation?->detected_services,
            'complexity_level' => $recommendation?->complexity_level ?? 'moderate',
            'document_checklist' => $recommendation?->document_checklist,
            'status' => 'pending',
        ]);

        // Create timeline entry
        $clientRecord->entries()->create([
            'appointment_id' => $appointment->id,
            'entry_type' => 'appointment_created',
            'title' => 'Appointment Booked',
            'content' => 'Appointment scheduled with reference number: ' . $appointment->reference_number,
            'metadata' => [
                'reference_number' => $appointment->reference_number,
                'lawyer_id' => $state['lawyer_id'],
            ],
        ]);

        // Delete the draft
        $draft->delete();

        // Load lawyer relationship for the email
        $appointment->load('lawyer.user');

        // Send acknowledgement email to the client
        try {
            Mail::to($clientRecord->email)
                ->send(new AppointmentSubmitted($appointment, $clientRecord));
        } catch (\Exception $e) {
            Log::error('Failed to send appointment acknowledgement email', [
                'appointment_id' => $appointment->id,
                'email'          => $clientRecord->email,
                'error'          => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'reference_number' => $appointment->reference_number,
            'html' => view('client.steps.step7_confirmation', [
                'appointment' => $appointment,
                'clientRecord' => $clientRecord,
            ])->render(),
        ]);
    }

    /**
     * Render a specific step
     */
    protected function renderStep(int $step, BookingDraft $draft): string
    {
        $state = $draft->draft_state;
        $recommendation = null;

        if (isset($state['ai_recommendation_id'])) {
            $recommendation = \App\Models\AiRecommendation::with('items.lawyer.user', 'items.lawyer.specializations')
                ->find($state['ai_recommendation_id']);
        }

        return match ($step) {
            1 => view('client.steps.step1_privacy')->render(),
            2 => view('client.steps.step2_narrative', ['draft' => $draft])->render(),
            3 => view('client.steps.step3_detected_service', [
                'recommendation' => $recommendation,
            ])->render(),
            4 => view('client.steps.step4_lawyers', [
                'recommendation' => $recommendation,
                'lawyers' => $recommendation?->items ?? collect(),
            ])->render(),
            5 => view('client.steps.step5_schedule', [
                'lawyerId' => $state['lawyer_id'] ?? null,
                'recommendation' => $recommendation,
            ])->render(),
            6 => view('client.steps.step6_review', [
                'draft' => $draft,
                'recommendation' => $recommendation,
                'lawyer' => isset($state['lawyer_id']) ? Lawyer::with('user', 'specializations')->find($state['lawyer_id']) : null,
            ])->render(),
            default => view('client.steps.step1_privacy')->render(),
        };
    }

    /**
     * Go back to a previous step
     */
    public function goBack(Request $request, int $step): JsonResponse
    {
        $sessionId = $request->input('session_id');
        
        if (!$sessionId) {
            return response()->json(['success' => false, 'error' => 'Session ID missing. Please refresh the page.'], 400);
        }

        $draft = BookingDraft::where('session_id', $sessionId)->first();

        if (!$draft) {
            return response()->json(['success' => false, 'error' => 'Session not found. Please refresh the page and try again.'], 400);
        }

        if ($draft->isExpired()) {
            $draft->delete();
            return response()->json(['success' => false, 'error' => 'Session expired. Please refresh the page to start over.'], 400);
        }

        // Extend expiration on navigation
        $draft->extendExpiration(24);

        $draft->goToStep($step);
        $html = $this->renderStep($step, $draft);

        return response()->json([
            'success' => true,
            'html' => $html,
            'step' => $step,
        ]);
    }

    /**
     * Get available time slots for a lawyer on a specific date
     */
    public function getTimeSlots(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lawyer_id' => 'required|exists:lawyers,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $lawyer = Lawyer::find($request->lawyer_id);
        $date = Carbon::parse($request->date);
        $dayOfWeek = $date->dayOfWeek;

        // Get lawyer's schedule for this day
        $schedule = $lawyer->schedules()->where('day_of_week', $dayOfWeek)->first();

        if (!$schedule || !$schedule->is_available) {
            return response()->json([
                'success' => true,
                'slots' => [],
                'message' => 'Lawyer is not available on this day.',
            ]);
        }

        // Get session to retrieve duration
        $sessionId = $request->input('session_id');
        $draft = $sessionId ? BookingDraft::where('session_id', $sessionId)->first() : null;
        $duration = 60; // default
        
        // Generate time slots
        $slots = $this->generateTimeSlots($lawyer, $date, $schedule, $duration);

        return response()->json([
            'success' => true,
            'slots' => $slots,
            'duration' => $duration,
        ]);
    }

    /**
     * Generate available time slots
     */
    protected function generateTimeSlots(Lawyer $lawyer, Carbon $date, LawyerSchedule $schedule, int $duration): array
    {
        $slots = [];
        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->start_time);
        $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->end_time);

        // Get existing appointments for this day
        $existingAppointments = $lawyer->appointments()
            ->whereDate('start_datetime', $date)
            ->whereNotIn('status', ['cancelled'])
            ->get();

        while ($startTime->copy()->addMinutes($duration)->lte($endTime)) {
            $slotEnd = $startTime->copy()->addMinutes($duration);
            
            // Check if slot overlaps with existing appointments
            $isAvailable = true;
            foreach ($existingAppointments as $apt) {
                if ($startTime->lt($apt->end_datetime) && $slotEnd->gt($apt->start_datetime)) {
                    $isAvailable = false;
                    break;
                }
            }

            // Don't show past slots for today
            if ($date->isToday() && $startTime->lt(now())) {
                $isAvailable = false;
            }

            if ($isAvailable) {
                $slots[] = [
                    'start' => $startTime->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'display' => $startTime->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                    'value' => $startTime->format('H:i') . '-' . $slotEnd->format('H:i'),
                ];
            }

            $startTime->addMinutes(30); // 30-minute intervals
        }

        return $slots;
    }

    /**
     * Get disabled dates based on lawyer unavailability
     */
    public function getDisabledDates(Request $request)
    {
        try {
            // 1. Gamita ang saktong Session Key
            $sessionId = session('booking_session_id');

            if (!$sessionId) {
                return response()->json([]);
            }

            $draft = BookingDraft::where('session_id', $sessionId)->first();
            
            // 2. Kuhaa ang lawyer_id gikan sa 'draft_state' (JSON)
            if (!$draft || empty($draft->draft_state['lawyer_id'])) {
                return response()->json([]);
            }

            $lawyerId = $draft->draft_state['lawyer_id'];

            // 3. I-query ang unavailable dates
            $blocked = \App\Models\lawyerUnavailability::where('lawyer_id', $lawyerId)
                        ->pluck('unavailable_date')
                        ->toArray();

            return response()->json($blocked);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Calendar Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server Error'], 500);
        }
    }
    
}