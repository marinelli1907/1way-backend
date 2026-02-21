<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiSetting extends Model
{
    protected $fillable = ['key', 'value', 'is_encrypted'];

    protected $casts = ['is_encrypted' => 'boolean'];

    // ── Static helpers ───────────────────────────────────────────────────────

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }
        if ($setting->is_encrypted && $setting->value) {
            try {
                return Crypt::decryptString($setting->value);
            } catch (\Throwable) {
                return null;
            }
        }
        return $setting->value;
    }

    public static function set(string $key, mixed $value, bool $encrypt = false): void
    {
        $stored = $encrypt ? Crypt::encryptString((string) $value) : $value;
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'is_encrypted' => $encrypt]
        );
    }

    public static function isEnabled(string $feature): bool
    {
        return (bool) static::get($feature, false);
    }

    // ── Default feature keys ─────────────────────────────────────────────────

    const FEATURE_PRICING_SUGGESTIONS = 'ai_feature_pricing_suggestions';
    const FEATURE_DEMAND_HEATMAP      = 'ai_feature_demand_heatmap';
    const FEATURE_FRAUD_FLAGGING      = 'ai_feature_fraud_flagging';
    const FEATURE_ETA_PREDICTOR       = 'ai_feature_eta_predictor';
    const KEY_OPENAI_API_KEY          = 'ai_openai_api_key';
    const KEY_ANTHROPIC_API_KEY       = 'ai_anthropic_api_key';
}
