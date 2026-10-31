<?php

namespace App\Console\Commands;

use App\Models\AuthToken;
use App\Models\ResidentAddress;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PurgeExpiredDeletedAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firesight:purge-deleted-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently anonymize and purge resident accounts whose 30-day deletion grace period has expired.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoffDate = now()->subDays(30);

        $expiredUsers = User::where('role', User::ROLE_RESIDENT)
            ->where('status', User::STATUS_INACTIVE)
            ->whereNotNull('suspended_at')
            ->where('suspended_at', '<', $cutoffDate)
            ->where('suspension_reason', 'like', '%deletion%')
            ->get();

        if ($expiredUsers->isEmpty()) {
            $this->info('No accounts to purge today.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($expiredUsers as $user) {
            // 1. Delete profile photo file from disk if present
            if ($user->profile_image) {
                $filePath = storage_path('app/public/'.$user->profile_image);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }

            // 2. Wipe all auth tokens
            AuthToken::where('user_id', $user->user_id)->delete();

            // 3. Delete resident address record
            ResidentAddress::where('user_id', $user->user_id)->delete();

            // 4. Anonymize personal details (Data Privacy Act) while retaining user_id reference for public safety incident records (Fire Code)
            $user->first_name = 'Deleted';
            $user->middle_name = null;
            $user->last_name = 'User';
            $user->suffix = null;
            $user->email = "deleted_{$user->user_id}_".time().'@deleted.firesight.local';
            $user->contact_number = null;
            $user->profile_image = null;
            $user->password = '';
            $user->suspension_reason = 'Permanently purged after 30-day deletion grace period';
            $user->save();

            $count++;
        }

        $this->info("Successfully purged {$count} expired account(s).");
        Log::info("[PurgeExpiredDeletedAccounts] Purged {$count} resident account(s).");

        return self::SUCCESS;
    }
}
