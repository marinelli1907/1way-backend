<?php

namespace Modules\UserManagement\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Entities\DriverApplication;

class DriverApplicationApiController extends Controller
{
    private const DISK = 'local';

    private const DOC_FIELDS = [
        'driver_license_front',
        'driver_license_back',
        'insurance_card',
        'selfie',
    ];

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'phone'         => 'required|string|max:30',
            'email'         => 'required|email|max:255',
            'city'          => 'required|string|max:255',
            'state'         => 'required|string|max:10',
            'vehicle_make'  => 'nullable|string|max:255',
            'vehicle_model' => 'nullable|string|max:255',
            'vehicle_year'  => 'nullable|digits:4',
            'rideshare_insurance' => 'nullable',
            'availability'  => 'nullable',
            'preferred_service_area' => 'nullable|string|max:1000',
            'notes'         => 'nullable|string|max:5000',
            'consent'       => 'required|accepted',

            'driver_license_front' => 'required|image|max:10240|mimes:jpg,jpeg,png,heic,heif,webp',
            'driver_license_back'  => 'nullable|image|max:10240|mimes:jpg,jpeg,png,heic,heif,webp',
            'insurance_card'       => 'nullable|image|max:10240|mimes:jpg,jpeg,png,heic,heif,webp',
            'selfie'               => 'nullable|image|max:10240|mimes:jpg,jpeg,png,heic,heif,webp',
        ];

        $messages = [
            'consent.accepted'                => 'You must consent to be contacted.',
            'driver_license_front.required'    => "A photo of the front of your driver's license is required.",
            'driver_license_front.image'       => 'The license front must be a valid image.',
            'driver_license_front.max'         => 'The license front must not exceed 10 MB.',
            'driver_license_back.image'        => 'The license back must be a valid image.',
            'driver_license_back.max'          => 'The license back must not exceed 10 MB.',
            'insurance_card.image'             => 'The insurance card must be a valid image.',
            'insurance_card.max'               => 'The insurance card must not exceed 10 MB.',
            'selfie.image'                     => 'The selfie must be a valid image.',
            'selfie.max'                       => 'The selfie must not exceed 10 MB.',
        ];

        $rideshareInsurance = $request->input('rideshare_insurance');
        if (is_string($rideshareInsurance) && in_array(strtolower($rideshareInsurance), ['yes', '1', 'true'], true)) {
            $rules['insurance_card'] = 'required|image|max:10240|mimes:jpg,jpeg,png,heic,heif,webp';
            $messages['insurance_card.required'] = 'Insurance card photo is required when you have rideshare insurance.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'ok'     => false,
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $phone = preg_replace('/\D/', '', $request->input('phone', ''));

            if (is_string($rideshareInsurance)) {
                $rideshareInsurance = in_array(strtolower($rideshareInsurance), ['yes', '1', 'true'], true);
            }

            $availability = $request->input('availability');
            if (is_string($availability)) {
                $decoded = json_decode($availability, true);
                $availability = is_array($decoded) ? $decoded : [$availability];
            }
            if (!is_array($availability)) {
                $availability = $availability ? [$availability] : null;
            }

            $application = DriverApplication::create([
                'first_name'             => $request->input('first_name'),
                'last_name'              => $request->input('last_name'),
                'phone'                  => $phone,
                'email'                  => $request->input('email'),
                'city'                   => $request->input('city'),
                'state'                  => $request->input('state'),
                'vehicle_make'           => $request->input('vehicle_make'),
                'vehicle_model'          => $request->input('vehicle_model'),
                'vehicle_year'           => $request->input('vehicle_year'),
                'rideshare_insurance'    => $rideshareInsurance,
                'availability'           => $availability,
                'preferred_service_area' => $request->input('preferred_service_area'),
                'notes'                  => $request->input('notes'),
                'consent'                => true,
            ]);

            $docs = [];
            $storageDir = 'driver-applications/' . $application->id;

            foreach (self::DOC_FIELDS as $field) {
                if (!$request->hasFile($field)) {
                    continue;
                }

                $file = $request->file($field);
                $storedPath = $file->store($storageDir, self::DISK);

                $docs[$field] = [
                    'path'          => $storedPath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime'          => $file->getClientMimeType(),
                    'size'          => $file->getSize(),
                ];
            }

            $updateData = ['docs' => $docs];

            // Backward compat: populate legacy license_photo columns from driver_license_front
            if (isset($docs['driver_license_front'])) {
                $front = $docs['driver_license_front'];
                $updateData['license_photo_path']          = $front['path'];
                $updateData['license_photo_original_name'] = $front['original_name'];
                $updateData['license_photo_mime']           = $front['mime'];
                $updateData['license_photo_size']           = $front['size'];
            }

            $application->update($updateData);

            return response()->json([
                'ok'             => true,
                'application_id' => $application->id,
                'status'         => 'pending',
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Driver application submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok'     => false,
                'errors' => ['server' => ['An unexpected error occurred. Please try again.']],
            ], 500);
        }
    }
}
