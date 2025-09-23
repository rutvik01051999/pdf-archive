<?php

namespace App\Console\Commands;

use App\Models\MobileVerification;
use Illuminate\Console\Command;

class CleanupExpiredOtps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired OTPs from mobile_verifications table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deletedCount = MobileVerification::cleanupExpiredOtps();
        
        $this->info("Cleaned up {$deletedCount} expired OTPs.");
        
        return Command::SUCCESS;
    }
}