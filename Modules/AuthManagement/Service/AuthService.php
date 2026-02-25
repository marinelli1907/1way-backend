<?php

namespace Modules\AuthManagement\Service;

use App\Service\BaseService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Modules\BusinessManagement\Repository\SettingRepositoryInterface;
use Modules\Gateways\Traits\SmsGateway;
use Modules\UserManagement\Repository\OtpVerificationRepositoryInterface;
use Modules\UserManagement\Repository\UserRepositoryInterface;
use Modules\UserManagement\Entities\User;

class AuthService extends BaseService implements Interface\AuthServiceInterface
{
    use SmsGateway;

    protected $userRepository;
    protected $otpVerificationRepository;
    protected $settingRepository;

    public function __construct(UserRepositoryInterface $userRepository, OtpVerificationRepositoryInterface $otpVerificationRepository, SettingRepositoryInterface $settingRepository)
    {
        parent::__construct($userRepository);
        $this->userRepository = $userRepository;
        $this->otpVerificationRepository = $otpVerificationRepository;
        $this->settingRepository = $settingRepository;
    }

    public function checkClientRoute($request)
    {
        $route = str_contains($request->route()?->getPrefix(), 'customer');
        $userType = $route ? CUSTOMER : DRIVER;
        $login = trim((string) ($request->phone_or_email ?? ''));

        if ($login === '') {
            return null;
        }

        // 1) Exact phone match first for backward compatibility.
        $user = $this->userRepository->findOneBy(criteria: [
            'phone' => $login,
            'user_type' => $userType,
        ]);
        if ($user) {
            return $user;
        }

        // 2) If input is an email, allow email-based login.
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userRepository->findOneBy(criteria: [
                'email' => strtolower($login),
                'user_type' => $userType,
            ]);
            if ($user) {
                return $user;
            }
        }

        // 3) Try normalized phone variants (spaces/dashes/parentheses/+ differences).
        $digits = preg_replace('/\D+/', '', $login);
        $normalized = preg_replace('/[^\d+]/', '', $login);
        $candidates = array_values(array_unique(array_filter([
            $normalized,
            $digits,
            $digits !== '' ? '+' . $digits : null,
        ])));

        foreach ($candidates as $candidate) {
            $user = $this->userRepository->findOneBy(criteria: [
                'phone' => $candidate,
                'user_type' => $userType,
            ]);
            if ($user) {
                return $user;
            }
        }

        // 4) Database-level normalized phone comparison fallback.
        if ($digits !== '') {
            $user = User::query()
                ->where('user_type', $userType)
                ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') = ?", [$digits])
                ->first();
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    private function generateOtp($user, $otp)
    {
        $expires_at = env('APP_MODE') == 'live' ? 3 : 1000;
        $attributes = [
            'phone_or_email' => $user->phone,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes($expires_at),
        ];
        $verification = $this->otpVerificationRepository->findOneBy(['phone_or_email' => $user->phone]);
        if ($verification) {
            $verification->delete();
        }
        $this->otpVerificationRepository->create(data: $attributes);
        return $otp;
    }

    public function updateLoginUser(string|int $id, array $data): ?Model
    {
        return $this->userRepository->update(id: $id, data: $data);
    }


    public function sendOtpToClient($user, $type = null)
    {
        if ($type == 'trip') {
            $otp = env('APP_MODE') == 'live' ? rand(1000, 9999) : '0000';
            if (self::send($user->phone, $otp) == "not_found") {
                return $this->generateOtp($user, '0000');
            }
            return $this->generateOtp($user, $otp);
        }
        $dataValues = $this->settingRepository->getBy(criteria: ['settings_type' => SMS_CONFIG]);
        if ($dataValues->where('live_values.status', 1)->isNotEmpty()) {
            $otp = rand(100000, 999999);
        } else {
            $otp = '000000';
        }

        if (self::send($user->phone, $otp) == "not_found") {
            return $this->generateOtp($user, '000000');
        }
        return $this->generateOtp($user, $otp);

    }
}
