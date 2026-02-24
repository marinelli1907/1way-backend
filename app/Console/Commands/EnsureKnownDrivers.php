<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\UserManagement\Entities\DriverDetail;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAccount;
use Modules\UserManagement\Entities\UserLevel;

/**
 * Ensures "known" driver accounts exist (idempotent).
 *
 * 1) Daniel Marinelli: 14404889279 / Jake5419 (primary; production requires --force)
 * 2) Test driver: 15555550124 / Jake5419 (gated: non-production or --force)
 *
 * Run: php artisan drivers:ensure-known [--force] [--test]
 */
class EnsureKnownDrivers extends Command
{
    protected $signature = 'drivers:ensure-known
                            {--force : Allow running in production / update existing passwords}
                            {--test : Also ensure test driver (15555550124)}';

    protected $description = 'Ensure known driver accounts exist (Daniel Marinelli + optional test driver). Idempotent.';

    private const DANIEL_PHONE = '14404889279';
    private const DANIEL_PASSWORD = 'Jake5419';
    private const DANIEL_FIRST_NAME = 'Daniel';
    private const DANIEL_LAST_NAME = 'Marinelli';

    private const TEST_PHONE = '15555550124';
    private const TEST_PASSWORD = 'Jake5419';
    private const TEST_FIRST_NAME = 'Test';
    private const TEST_LAST_NAME = 'Driver';

    public function handle(): int
    {
        $isProduction = app()->environment('production');
        $force = $this->option('force');
        $includeTest = $this->option('test');

        if ($isProduction && !$force) {
            $this->error('Production environment: run with --force to create/update drivers.');
            return self::FAILURE;
        }

        $firstLevel = UserLevel::where('user_type', DRIVER)->orderBy('sequence')->first();
        if (!$firstLevel) {
            $this->error('No driver level found. Run migrations / seed user_levels.');
            return self::FAILURE;
        }

        $this->ensureDriver(
            self::DANIEL_PHONE,
            self::DANIEL_PASSWORD,
            self::DANIEL_FIRST_NAME,
            self::DANIEL_LAST_NAME,
            'Daniel Marinelli (primary)'
        );

        if ($includeTest) {
            if ($isProduction && !$force) {
                $this->warn('Skipping test driver in production without --force.');
            } else {
                $this->ensureDriver(
                    self::TEST_PHONE,
                    self::TEST_PASSWORD,
                    self::TEST_FIRST_NAME,
                    self::TEST_LAST_NAME,
                    'Test driver (fixture)'
                );
            }
        } else {
            $this->line('Tip: use --test to ensure test driver (15555550124).');
        }

        return self::SUCCESS;
    }

    private function ensureDriver(string $phone, string $password, string $firstName, string $lastName, string $label): void
    {
        $normalized = $this->normalizePhone($phone);
        $user = User::where('phone', $normalized)->where('user_type', DRIVER)->first();

        if ($user) {
            $this->line("[{$label}] Driver already exists: {$normalized} (id: {$user->id}).");
            if ($this->option('force')) {
                $user->password = Hash::make($password);
                $user->save();
                $this->info("  -> Password updated.");
            }
            return;
        }

        // Also match by alternate format (e.g. with/without country code)
        $user = User::where('user_type', DRIVER)->where(function ($q) use ($phone, $normalized) {
            $q->where('phone', $phone)->orWhere('phone', $normalized);
        })->first();
        if ($user) {
            $user->phone = $normalized;
            $user->password = Hash::make($password);
            $user->first_name = $firstName;
            $user->last_name = $lastName;
            $user->full_name = "{$firstName} {$lastName}";
            $user->save();
            $this->info("[{$label}] Updated existing driver to {$normalized}.");
            return;
        }

        DB::beginTransaction();
        try {
            $user = new User();
            $user->first_name = $firstName;
            $user->last_name = $lastName;
            $user->full_name = "{$firstName} {$lastName}";
            $user->email = 'driver+' . $normalized . '@internal.1way';
            $user->phone = $normalized;
            $user->password = Hash::make($password);
            $user->user_type = DRIVER;
            $user->user_level_id = $firstLevel->id;
            $user->is_active = 1;
            $user->ref_code = generateReferralCode();
            $user->save();

            $user->driverDetails()->create([
                'is_online' => false,
                'availability_status' => 'unavailable',
            ]);
            $user->userAccount()->create();

            DB::commit();
            $this->info("[{$label}] Created driver: {$normalized} (id: {$user->id}).");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("[{$label}] Create failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D/', '', $phone) ?: $phone;
    }
}
