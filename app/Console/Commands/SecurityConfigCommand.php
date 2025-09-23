<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SecurityMonitoringService;
use Illuminate\Support\Facades\Cache;

class SecurityConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:config 
                            {action : Action to perform (status|block-ip|unblock-ip|clear-cache|report)}
                            {--ip= : IP address for block/unblock operations}
                            {--days=7 : Number of days for report}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage security configuration and monitoring';

    protected SecurityMonitoringService $securityService;

    public function __construct(SecurityMonitoringService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'status':
                $this->showSecurityStatus();
                break;
            case 'block-ip':
                $this->blockIp();
                break;
            case 'unblock-ip':
                $this->unblockIp();
                break;
            case 'clear-cache':
                $this->clearSecurityCache();
                break;
            case 'report':
                $this->generateReport();
                break;
            default:
                $this->error('Invalid action. Available actions: status, block-ip, unblock-ip, clear-cache, report');
        }
    }

    /**
     * Show security status
     */
    protected function showSecurityStatus(): void
    {
        $this->info('Security Configuration Status');
        $this->line('============================');

        // Security headers
        $this->line('Security Headers:');
        $this->line('  X-Frame-Options: ' . (config('security.headers.x_frame_options') ?: 'Not configured'));
        $this->line('  X-Content-Type-Options: ' . (config('security.headers.x_content_type_options') ?: 'Not configured'));
        $this->line('  X-XSS-Protection: ' . (config('security.headers.x_xss_protection') ?: 'Not configured'));

        // Content protection
        $this->line('Content Protection:');
        $this->line('  Hotlink Protection: ' . (config('security.content_protection.hotlink_protection') ? 'Enabled' : 'Disabled'));
        $this->line('  Download Protection: ' . (config('security.content_protection.download_protection') ? 'Enabled' : 'Disabled'));

        // Rate limiting
        $this->line('Rate Limiting:');
        $this->line('  Global: ' . (config('security.rate_limiting.global.enabled') ? 'Enabled' : 'Disabled'));
        $this->line('  API: ' . (config('security.rate_limiting.api.enabled') ? 'Enabled' : 'Disabled'));

        // Monitoring
        $this->line('Security Monitoring:');
        $this->line('  Events Logging: ' . (config('security.monitoring.security_events.enabled') ? 'Enabled' : 'Disabled'));
        $this->line('  Intrusion Detection: ' . (config('security.monitoring.intrusion_detection.enabled') ? 'Enabled' : 'Disabled'));

        // Session security
        $this->line('Session Security:');
        $this->line('  Admin Timeout: ' . config('session_timeout.admin.inactive_timeout') . ' minutes');
        $this->line('  Session Rotation: Enabled');

        // Login security
        $this->line('Login Security:');
        $this->line('  Rate Limiting: Enabled');
        $this->line('  Account Lockout: Enabled');
        $this->line('  Max Attempts: ' . config('login_rate_limit.account_lockout.max_attempts'));
    }

    /**
     * Block IP address
     */
    protected function blockIp(): void
    {
        $ip = $this->option('ip');
        
        if (!$ip) {
            $ip = $this->ask('Enter IP address to block');
        }

        if (!$ip) {
            $this->error('IP address is required');
            return;
        }

        // Validate IP
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->error('Invalid IP address format');
            return;
        }

        // Block IP
        Cache::put("blocked_ip:{$ip}", true, now()->addHours(24));
        
        $this->info("IP address {$ip} has been blocked for 24 hours");
        
        // Log the action
        $this->securityService->logAdminAction('block_ip', ['ip' => $ip]);
    }

    /**
     * Unblock IP address
     */
    protected function unblockIp(): void
    {
        $ip = $this->option('ip');
        
        if (!$ip) {
            $ip = $this->ask('Enter IP address to unblock');
        }

        if (!$ip) {
            $this->error('IP address is required');
            return;
        }

        // Unblock IP
        $this->securityService->unblockIp($ip);
        
        $this->info("IP address {$ip} has been unblocked");
    }

    /**
     * Clear security cache
     */
    protected function clearSecurityCache(): void
    {
        $this->info('Clearing security cache...');
        
        // Clear rate limiting cache
        $keys = Cache::getRedis()->keys('rate_limit:*');
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        // Clear intrusion detection cache
        $keys = Cache::getRedis()->keys('intrusion_attempts:*');
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        // Clear blocked IPs cache
        $keys = Cache::getRedis()->keys('blocked_ip:*');
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        $this->info('Security cache cleared successfully');
    }

    /**
     * Generate security report
     */
    protected function generateReport(): void
    {
        $days = $this->option('days');
        
        $this->info("Generating security report for the last {$days} days...");
        
        $report = $this->securityService->generateSecurityReport($days);
        
        $this->line('Security Report');
        $this->line('==============');
        $this->line('Period: ' . $report['period']);
        $this->line('Generated: ' . $report['generated_at']);
        
        $this->line('');
        $this->line('Summary:');
        foreach ($report['summary'] as $key => $value) {
            $this->line("  " . ucwords(str_replace('_', ' ', $key)) . ": {$value}");
        }
        
        $this->line('');
        $this->line('Recommendations:');
        if (empty($report['recommendations'])) {
            $this->line('  No specific recommendations at this time');
        } else {
            foreach ($report['recommendations'] as $recommendation) {
                $this->line("  - {$recommendation}");
            }
        }
    }
}