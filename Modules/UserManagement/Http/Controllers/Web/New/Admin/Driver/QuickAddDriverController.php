<?php

namespace Modules\UserManagement\Http\Controllers\Web\New\Admin\Driver;

use App\Http\Controllers\BaseController;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\UserManagement\Entities\DriverInviteToken;
use Modules\UserManagement\Entities\DriverOnboardingStatus;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Service\Interface\DriverLevelServiceInterface;
use Modules\UserManagement\Service\Interface\DriverServiceInterface;

class QuickAddDriverController extends BaseController
{
    use AuthorizesRequests;

    public function __construct(
        protected DriverServiceInterface      $driverService,
        protected DriverLevelServiceInterface $driverLevelService,
    ) {
        parent::__construct($driverService);
    }

    /** GET /admin/driver/quick-add */
    public function create(): View
    {
        $this->authorize('user_add');
        return view('usermanagement::admin.driver.quick-add');
    }

    /** POST /admin/driver/quick-add */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('user_add');

        $data = $request->validate([
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'email'               => 'required|email|unique:users,email',
            'phone'               => 'required|string|max:20',
            'city_region'         => 'nullable|string|max:120',
            'driver_split_percent'=> 'nullable|numeric|min:0|max:100',
            'vehicle_make'        => 'nullable|string|max:100',
            'vehicle_model'       => 'nullable|string|max:100',
            'vehicle_year'        => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'vehicle_plate'       => 'nullable|string|max:20',
        ]);

        $firstLevel = $this->driverLevelService->findOneBy(['user_type' => DRIVER, 'sequence' => 1]);
        if (! $firstLevel) {
            Toastr::error(translate('Please create at least one Driver Level before adding drivers.'));
            return back()->withInput();
        }

        DB::beginTransaction();
        try {
            $tempPassword = Str::random(12);

            $driver = User::create([
                'user_level_id'        => $firstLevel->id,
                'first_name'           => $data['first_name'],
                'last_name'            => $data['last_name'],
                'full_name'            => trim($data['first_name'] . ' ' . $data['last_name']),
                'email'                => $data['email'],
                'phone'                => $data['phone'],
                'city_region'          => $data['city_region'] ?? null,
                'driver_split_percent' => $data['driver_split_percent'] ?? 80,
                'password'             => Hash::make($tempPassword),
                'user_type'            => DRIVER,
                'is_active'            => true,
                'ref_code'             => Str::upper(Str::random(8)),
            ]);

            // Create blank driver details
            $driver->driverDetails()->create([
                'is_online'           => false,
                'availability_status' => 'unavailable',
            ]);

            // Create blank onboarding checklist
            DriverOnboardingStatus::create([
                'driver_id'       => $driver->id,
                'profile_complete'=> false,
                'docs_uploaded'   => false,
                'approved'        => false,
                'active'          => false,
            ]);

            // Generate magic invite token
            $invite = DriverInviteToken::generate($driver->id);

            DB::commit();

            // Try to send invite email
            $inviteUrl = $invite->inviteUrl();
            $this->sendInviteEmail($driver, $inviteUrl, $tempPassword);

            Toastr::success(translate('Driver created! Invite link generated.'));
            return redirect()->route('admin.driver.quick-add.show', $driver->id)
                ->with('invite_url', $inviteUrl)
                ->with('temp_password', $tempPassword);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('QuickAddDriver error: ' . $e->getMessage());
            Toastr::error(translate('Something went wrong. Please try again.'));
            return back()->withInput();
        }
    }

    /** GET /admin/driver/quick-add/{id}/show — shows the driver + invite link */
    public function show(string $id): View
    {
        $this->authorize('user_view');
        $driver   = User::with(['onboardingStatus', 'inviteTokens' => fn($q) => $q->latest()->first()])
                        ->findOrFail($id);
        $invite   = $driver->inviteTokens()->where('used', false)->latest()->first();
        $inviteUrl = $invite ? $invite->inviteUrl() : null;

        return view('usermanagement::admin.driver.quick-add-result', compact('driver', 'inviteUrl'));
    }

    /** POST /admin/driver/quick-add/{id}/regenerate-invite */
    public function regenerateInvite(string $id): JsonResponse
    {
        $this->authorize('user_edit');
        $driver = User::findOrFail($id);
        $invite = DriverInviteToken::generate($driver->id);
        return response()->json(['invite_url' => $invite->inviteUrl()]);
    }

    /** POST /admin/driver/quick-add/{id}/onboarding */
    public function updateOnboarding(Request $request, string $id): JsonResponse
    {
        $this->authorize('user_edit');

        $data = $request->validate([
            'profile_complete' => 'boolean',
            'docs_uploaded'    => 'boolean',
            'approved'         => 'boolean',
            'active'           => 'boolean',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $status = DriverOnboardingStatus::firstOrCreate(
            ['driver_id' => $id],
            ['profile_complete' => false, 'docs_uploaded' => false, 'approved' => false, 'active' => false]
        );

        $status->update($data);

        // Sync driver is_active with onboarding active flag
        if (isset($data['active'])) {
            User::where('id', $id)->update(['is_active' => (bool) $data['active']]);
        }

        return response()->json([
            'success'          => true,
            'progress_percent' => $status->refresh()->progressPercent(),
        ]);
    }

    /** GET /admin/driver/quick-add/invite/accept?token=xxx  (public — no admin auth) */
    public function acceptInvite(Request $request): View|RedirectResponse
    {
        $token = DriverInviteToken::where('token', $request->token)
                                   ->where('used', false)
                                   ->first();

        if (! $token || ! $token->isValid()) {
            return redirect()->route('auth.login')
                ->with('error', translate('Invite link is expired or invalid. Please ask your admin for a new link.'));
        }

        return view('usermanagement::driver.accept-invite', compact('token'));
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function sendInviteEmail(User $driver, string $inviteUrl, string $tempPassword): void
    {
        try {
            Mail::send([], [], function ($message) use ($driver, $inviteUrl, $tempPassword) {
                $message->to($driver->email)
                    ->subject('Welcome to 1Way — Set Up Your Driver Account')
                    ->html(
                        "<h2>Welcome to 1Way, {$driver->first_name}!</h2>
                        <p>Your driver account has been created by an admin.</p>
                        <p><strong>Email:</strong> {$driver->email}<br>
                        <strong>Temporary password:</strong> {$tempPassword}</p>
                        <p>Click the button below to activate your account and set a new password:</p>
                        <p><a href='{$inviteUrl}' style='background:#CC0000;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;'>Activate Account</a></p>
                        <p style='color:#666;font-size:12px;'>This link expires in 7 days.</p>"
                    );
            });
        } catch (\Throwable $e) {
            Log::warning('Failed to send driver invite email: ' . $e->getMessage());
        }
    }
}
