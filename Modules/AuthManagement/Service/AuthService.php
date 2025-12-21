<?php

namespace Modules\AuthManagement\Service;

use App\Service\BaseService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Modules\AuthManagement\Service\Interface\AuthServiceInterface;
use Modules\BusinessManagement\Repository\SettingRepositoryInterface;
use Modules\Gateways\Traits\SmsGateway;
use Modules\UserManagement\Repository\OtpVerificationRepositoryInterface;
use Modules\UserManagement\Repository\UserRepositoryInterface;

class AuthService extends BaseService implements AuthServiceInterface
{
    use SmsGateway;

    protected UserRepositoryInterface $userRepository;
    protected OtpVerificationRepositoryInterface $otpVerificationRepository;
    protected SettingRepositoryInterface $settingRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        OtpVerificationRepositoryInterface $otpVerificationRepository,
        SettingRepositoryInterface $settingRepository
    ) {
        parent::__construct($userRepository);

        $this->userRepository = $userRepository;
        $this->otpVerificationRepository = $otpVerificationRepository;
        $this->settingRepository = $settingRepository;
    }

    public function checkClientRoute($request)
    {
        $prefix = $request->route()?->getPrefix() ?? '';
        $isCustomer = str_contains($prefix, 'customer');

        $userType = $isCustomer ? CUSTOMER : DRIVER;
        $value = trim((string) ($request->phone_or_email ?? ''));

        // Try phone first
        $user = $this->userRepository->findOneBy([
            'user_type' => $userType,
            'phone' => $value,
        ]);

        // Fallback to email
        if (!$user) {
            $user = $this->userRepository->findOneBy([
                'user_type' => $userType,
                'email' => $value,
            ]);
        }

        return $user;
    }

    private function generateOtp($user, string $otp): string
    {
        $expires = env('APP_MODE') === 'live' ? 3 : 1000;

        $attributes = [
            'phone_or_email' => $user->phone,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes($expires),
        ];

        $existing = $this->otpVerificationRepository->findOneBy([
            'phone_or_email' => $user->phone
        ]);

        if ($existing) {
            $existing->delete();
        }

        $this->otpVerificationRepository->create($attributes);

        return $otp;
    }

    public function updateLoginUser(string|int $id, array $data): ?Model
    {
        return $this->userRepository->update($id, $data);
    }

    public function sendOtpToClient($user, $type = null)
    {
        if ($type === 'trip') {
            $otp = env('APP_MODE') === 'live' ? rand(1000, 9999) : '0000';

            if (self::send($user->phone, $otp) === 'not_found') {
                return $this->generateOtp($user, '0000');
            }

            return $this->generateOtp($user, (string) $otp);
        }

        $configs = $this->settingRepository->getBy([
            'settings_type' => SMS_CONFIG
        ]);

        $otp = ($configs && $configs->where('live_values.status', 1)->isNotEmpty())
            ? (string) rand(100000, 999999)
            : '000000';

        if (self::send($user->phone, $otp) === 'not_found') {
            return $this->generateOtp($user, '000000');
        }

        return $this->generateOtp($user, $otp);
    }
}
