<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BookingDraft;

echo "=== Testing BookingDraft Expiration ===\n\n";

echo "Current time (now()): " . now()->toDateTimeString() . "\n";
echo "Timezone: " . config('app.timezone') . "\n\n";

// Create a new draft
$draft = BookingDraft::create([
    'session_id' => 'test-' . time(),
]);

echo "1. Created draft ID: {$draft->id}\n";
echo "   expires_at (model): {$draft->expires_at}\n";

// Check raw DB value
$raw = \DB::table('booking_drafts')->where('id', $draft->id)->first();
echo "   expires_at (raw DB): {$raw->expires_at}\n\n";

// Simulate processStep flow
echo "2. Calling extendExpiration(24)...\n";
$draft->extendExpiration(24);
$raw = \DB::table('booking_drafts')->where('id', $draft->id)->first();
echo "   expires_at (model): {$draft->expires_at}\n";
echo "   expires_at (raw DB): {$raw->expires_at}\n\n";

echo "3. Calling update(['privacy_accepted' => true])...\n";
$draft->update(['privacy_accepted' => true]);
$raw = \DB::table('booking_drafts')->where('id', $draft->id)->first();
echo "   expires_at (model): {$draft->expires_at}\n";
echo "   expires_at (raw DB): {$raw->expires_at}\n\n";

echo "4. Calling nextStep()...\n";
$draft->nextStep();
$raw = \DB::table('booking_drafts')->where('id', $draft->id)->first();
echo "   expires_at (model): {$draft->expires_at}\n";
echo "   expires_at (raw DB): {$raw->expires_at}\n\n";

echo "5. Refreshing model from database...\n";
$draft->refresh();
echo "   expires_at (model after refresh): {$draft->expires_at}\n\n";

echo "6. Is expired? " . ($draft->isExpired() ? 'YES' : 'NO') . "\n\n";

// Clean up
$draft->delete();
echo "Test draft deleted.\n";
