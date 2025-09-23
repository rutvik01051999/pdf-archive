<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class CertificateRateLimitStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certificate:rate-limit-stats {--clear : Clear all rate limit data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View and manage certificate download rate limiting statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('clear')) {
            $this->clearRateLimitData();
            return;
        }

        $this->displayRateLimitStats();
    }

    private function displayRateLimitStats()
    {
        $this->info('Certificate Download Rate Limiting Statistics');
        $this->line('==========================================');

        $config = config('certificate_rate_limit');
        
        $this->line('');
        $this->info('Current Rate Limiting Configuration:');
        $this->table(
            ['Type', 'Per Minute', 'Per Hour', 'Per Day'],
            [
                ['OTP Requests', $config['otp_requests']['per_minute'], $config['otp_requests']['per_hour'], $config['otp_requests']['per_day']],
                ['Downloads', $config['downloads']['per_minute'], $config['downloads']['per_hour'], $config['downloads']['per_day']],
                ['IP Requests', $config['ip_limits']['per_minute'], $config['ip_limits']['per_hour'], $config['ip_limits']['per_day']],
            ]
        );

        $this->line('');
        $this->info('Cache Keys in Use:');
        $this->line('- OTP Requests: ' . $config['cache_prefix']['otp_requests']);
        $this->line('- Downloads: ' . $config['cache_prefix']['downloads']);
        $this->line('- IP Requests: ' . $config['cache_prefix']['ip_requests']);

        $this->line('');
        $this->info('To clear all rate limit data, run:');
        $this->line('php artisan certificate:rate-limit-stats --clear');
    }

    private function clearRateLimitData()
    {
        $this->info('Clearing all certificate rate limiting data...');
        
        $config = config('certificate_rate_limit');
        
        // Clear cache entries with our prefixes
        $prefixes = array_values($config['cache_prefix']);
        
        foreach ($prefixes as $prefix) {
            // This is a simplified approach - in production you might want to use Redis SCAN
            $keys = Cache::getRedis()->keys("*{$prefix}*");
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
                $this->line("Cleared " . count($keys) . " cache entries for prefix: {$prefix}");
            }
        }
        
        $this->info('Rate limiting data cleared successfully!');
    }
}
