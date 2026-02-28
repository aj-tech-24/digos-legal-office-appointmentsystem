<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestGeminiApi extends Command
{
    protected $signature = 'ai:test {--narrative= : Custom narrative to analyze}';
    protected $description = 'Test the Gemini AI API connection and case analysis';

    public function handle(): int
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════╗');
        $this->line('║         Gemini AI API — Connection Test       ║');
        $this->line('╚══════════════════════════════════════════════╝');
        $this->newLine();

        // ── 1. Check config ──────────────────────────────────────────────
        $apiKey = config('services.gemini.api_key', '');
        $apiUrl = config('services.gemini.api_url', '');

        $this->info('📋  Config Check');
        $this->line('─────────────────────────────────────');

        if (empty($apiKey)) {
            $this->error('  ❌  GEMINI_API_KEY is NOT set in .env');
            return Command::FAILURE;
        }

        $this->line("  API Key : <fg=green>" . substr($apiKey, 0, 8) . "...</> (length: " . strlen($apiKey) . ")");
        $this->line("  API URL : <fg=green>{$apiUrl}</>");
        $this->newLine();

        // ── 2. Send test prompt ──────────────────────────────────────────
        $narrative = $this->option('narrative')
            ?? 'My husband has been physically hurting me and my children. I want to file a case against him for VAWC.';

        $this->info('📝  Test Narrative');
        $this->line('─────────────────────────────────────');
        $this->line("  \"{$narrative}\"");
        $this->newLine();

        $this->info('🌐  Sending request to Gemini API...');
        $this->line('─────────────────────────────────────');

        $start = microtime(true);

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($apiUrl . '?key=' . $apiKey, [
                'contents' => [[
                    'parts' => [['text' => "You are a legal AI. Analyze this narrative and return ONLY valid JSON with keys: professional_summary, detected_service, complexity_level (simple|moderate|complex). Narrative: \"{$narrative}\""]]
                ]],
                'generationConfig' => [
                    'temperature'     => 0.3,
                    'maxOutputTokens' => 512,
                ],
            ]);

            $elapsed = round((microtime(true) - $start) * 1000);

            $this->line("  HTTP Status : <fg=" . ($response->successful() ? 'green' : 'red') . ">{$response->status()}</>");
            $this->line("  Response Time : <fg=yellow>{$elapsed}ms</>");
            $this->newLine();

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '(empty)';

                $this->info('✅  API call SUCCEEDED!');
                $this->line('─────────────────────────────────────');
                $this->line('  Raw AI text response:');
                $this->newLine();
                $this->line('  ' . wordwrap($text, 80, "\n  ", true));
                $this->newLine();

                // Try parse JSON
                $cleaned = preg_replace('/```json\s*/i', '', $text);
                $cleaned = preg_replace('/```\s*/', '', $cleaned);
                $json = json_decode(trim($cleaned), true);

                if ($json) {
                    $this->info('🎯  Parsed Result:');
                    $this->line('─────────────────────────────────────');
                    $this->line('  Detected Service : <fg=cyan>' . ($json['detected_service'] ?? $json['professional_summary'] ?? '?') . '</>');
                    $this->line('  Complexity       : <fg=cyan>' . ($json['complexity_level'] ?? '?') . '</>');
                    $this->line('  Summary          : ' . substr($json['professional_summary'] ?? '?', 0, 120) . '...');
                } else {
                    $this->warn('  ⚠️  Response was not valid JSON — but API itself is working.');
                }

                $this->newLine();
                $this->info('✔  Your Gemini API key is valid and working!');
                return Command::SUCCESS;

            } else {
                $this->newLine();
                $this->error('❌  API call FAILED!');
                $this->line('─────────────────────────────────────');
                $this->line('  Status  : ' . $response->status());
                $this->line('  Body    : ' . substr($response->body(), 0, 600));
                $this->newLine();
                $this->warn('💡  Common causes:');
                $this->line('   • Invalid API key    → Check GEMINI_API_KEY in .env');
                $this->line('   • Wrong API URL      → Check GEMINI_API_URL in .env');
                $this->line('   • API quota exceeded → Check your Google AI Studio quota');
                $this->line('   • Wrong model name   → ensure model is "gemini-2.0-flash"');
                return Command::FAILURE;
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $elapsed = round((microtime(true) - $start) * 1000);
            $this->newLine();
            $this->error('❌  CONNECTION FAILED after ' . $elapsed . 'ms');
            $this->line('─────────────────────────────────────');
            $this->line('  Error : ' . $e->getMessage());
            $this->newLine();
            $this->warn('💡  Common causes:');
            $this->line('   • No internet connection');
            $this->line('   • Firewall blocking outbound HTTPS');
            $this->line('   • DNS cannot resolve generativelanguage.googleapis.com');
            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌  UNEXPECTED ERROR');
            $this->line('  ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
