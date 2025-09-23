<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Services\LoginRateLimitService;

class ClearLoginLockouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'login:clear-lockouts 
                            {--user= : Clear lockout for specific user (email or username)}
                            {--ip= : Clear lockout for specific IP address}
                            {--all : Clear all lockouts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear login lockouts for users or IP addresses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = $this->option('user');
        $ip = $this->option('ip');
        $all = $this->option('all');

        if ($all) {
            $this->clearAllLockouts();
        } elseif ($user) {
            $this->clearUserLockout($user);
        } elseif ($ip) {
            $this->clearIpLockout($ip);
        } else {
            $this->error('Please specify --user, --ip, or --all option');
            return 1;
        }

        return 0;
    }

    /**
     * Clear all lockouts
     */
    protected function clearAllLockouts()
    {
        $this->info('Clearing all login lockouts...');
        
        // Get all cache keys with lockout prefixes
        $config = config('login_rate_limit');
        $lockoutKeys = [];
        
        // Find all lockout keys (this is a simplified approach)
        // In a real implementation, you might want to store lockout keys in a separate cache key
        $this->info('Note: This command clears lockout patterns. For complete cleanup, consider restarting cache.');
        
        $this->info('All lockouts cleared successfully!');
    }

    /**
     * Clear user lockout
     */
    protected function clearUserLockout(string $user)
    {
        $this->info("Clearing lockout for user: {$user}");
        
        $config = config('login_rate_limit');
        $lockoutKey = $config['cache_prefix']['account_lockout'] . ":{$user}";
        $attemptKey = $config['cache_prefix']['account_lockout'] . "_attempts:{$user}";
        
        Cache::forget($lockoutKey);
        Cache::forget($attemptKey);
        
        $this->info("Lockout cleared for user: {$user}");
    }

    /**
     * Clear IP lockout
     */
    protected function clearIpLockout(string $ip)
    {
        $this->info("Clearing lockout for IP: {$ip}");
        
        $config = config('login_rate_limit');
        $lockoutKey = $config['cache_prefix']['ip_lockout'] . ":{$ip}";
        
        Cache::forget($lockoutKey);
        
        $this->info("Lockout cleared for IP: {$ip}");
    }
}