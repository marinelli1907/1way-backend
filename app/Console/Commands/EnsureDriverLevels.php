<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\UserManagement\Entities\UserLevel;

class EnsureDriverLevels extends Command
{
    protected $signature = 'drivers:ensure-levels
                            {--force : Allow running in production}';

    protected $description = 'Ensure at least one driver UserLevel exists (idempotent, env-safe).';

    public function handle(): int
    {
        $isProduction = app()->environment('production');
        $force        = (bool) $this->option('force');

        if ($isProduction && ! $force) {
            $this->warn('Production environment detected. Run with --force to create driver levels.');
            return self::FAILURE;
        }

        $existing = UserLevel::where('user_type', DRIVER)
            ->orderBy('sequence', 'asc')
            ->get();

        if ($existing->count() > 0) {
            $this->info("Driver levels already exist ({$existing->count()}); no changes made.");
            foreach ($existing as $level) {
                $this->line(sprintf(
                    ' - [%d] %s (id: %s, reward_type: %s)',
                    $level->sequence,
                    $level->name,
                    $level->id,
                    $level->reward_type
                ));
            }
            return self::SUCCESS;
        }

        $this->info('No driver levels found; creating defaults.');

        $defaults = [
            [
                'sequence'               => 1,
                'name'                   => 'Starter Driver',
                'reward_type'            => 'no_rewards',
                'reward_amount'          => null,
                'image'                  => null,
                'targeted_ride'          => 0,
                'targeted_ride_point'    => 0,
                'targeted_amount'        => 0,
                'targeted_amount_point'  => 0,
                'targeted_cancel'        => 0,
                'targeted_cancel_point'  => 0,
                'targeted_review'        => 0,
                'targeted_review_point'  => 0,
                'user_type'              => DRIVER,
                'is_active'              => 1,
            ],
        ];

        foreach ($defaults as $data) {
            $level = UserLevel::create($data);
            $this->line(sprintf(
                'Created driver level: [%d] %s (id: %s)',
                $level->sequence,
                $level->name,
                $level->id
            ));
        }

        $this->info('Driver levels ensure process completed.');

        return self::SUCCESS;
    }
}

