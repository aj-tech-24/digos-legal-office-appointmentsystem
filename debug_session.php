<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Session Info ===" . PHP_EOL;
echo "Current time: " . now()->format('Y-m-d H:i:s T') . PHP_EOL;
echo "Timezone: " . config('app.timezone') . PHP_EOL;
echo PHP_EOL;

// Clean up expired drafts
App\Models\BookingDraft::cleanupExpired();

// Show all drafts
$drafts = App\Models\BookingDraft::orderBy('id', 'desc')->get();
echo "All Booking Drafts (" . $drafts->count() . " total):" . PHP_EOL;
echo str_repeat('-', 80) . PHP_EOL;

foreach ($drafts as $d) {
    $createdAt = $d->created_at;
    $expiresAt = $d->expires_at;
    $diffMinutes = $createdAt ? $createdAt->diffInMinutes($expiresAt) : 'N/A';
    
    echo "Draft #{$d->id}" . PHP_EOL;
    echo "  Session ID: " . $d->session_id . PHP_EOL;
    echo "  Step: {$d->current_step}" . PHP_EOL;
    echo "  Created: {$createdAt}" . PHP_EOL;
    echo "  Expires: {$expiresAt}" . PHP_EOL;
    echo "  Diff from created: {$diffMinutes} minutes" . PHP_EOL;
    echo "  isExpired(): " . ($d->isExpired() ? 'YES' : 'NO') . PHP_EOL;
    echo PHP_EOL;
}

// Test creating a new draft
echo "=== Creating test draft ===" . PHP_EOL;
$testSession = 'debug-test-' . time();
$newDraft = App\Models\BookingDraft::create([
    'session_id' => $testSession
]);
echo "Created draft with session: " . $newDraft->session_id . PHP_EOL;
echo "Created at: " . $newDraft->created_at . PHP_EOL;
echo "Expires at: " . $newDraft->expires_at . PHP_EOL;
echo "Diff: " . $newDraft->created_at->diffInMinutes($newDraft->expires_at) . " minutes" . PHP_EOL;
echo "Is expired: " . ($newDraft->isExpired() ? 'YES' : 'NO') . PHP_EOL;
