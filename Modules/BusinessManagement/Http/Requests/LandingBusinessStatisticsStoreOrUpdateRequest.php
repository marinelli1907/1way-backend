<?php

namespace Modules\BusinessManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LandingBusinessStatisticsStoreOrUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->id;
        return [
            'total_download_image' => 'sometimes|image|mimes:jpg,png,jpeg,webp|max:' . (convertToBytes(maxUploadSize('image')) / 1024),
            'total_download_count' => 'required|string',
            'total_download_content' => 'required|string',
            'complete_ride_image' => 'sometimes|image|mimes:jpg,png,jpeg,webp|max:' . (convertToBytes(maxUploadSize('image')) / 1024),
            'complete_ride_count' => 'required|string',
            'complete_ride_content' => 'required|string',
            'happy_customer_image' => 'sometimes|image|mimes:jpg,png,jpeg,webp|max:' . (convertToBytes(maxUploadSize('image')) / 1024),
            'happy_customer_count' => 'required|string',
            'happy_customer_content' => 'required|string',
            'support_image' => 'sometimes|image|mimes:jpg,png,jpeg,webp|max:' . (convertToBytes(maxUploadSize('image')) / 1024),
            'support_title' => 'required|string',
            'support_content' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'total_download_image.max' => translate(key: 'The Total Download Image must be less than {maxSize}', replace: ['maxSize' => readableUploadMaxFileSize('image')]),
            'complete_ride_image.max' => translate(key: 'The Complete Ride Image must be less than {maxSize}', replace: ['maxSize' => readableUploadMaxFileSize('image')]),
            'happy_customer_image.max' => translate(key: 'The Happy Customer Image must be less than {maxSize}', replace: ['maxSize' => readableUploadMaxFileSize('image')]),
            'support_image.max' => translate(key: 'The Support Image Image must be less than {maxSize}', replace: ['maxSize' => readableUploadMaxFileSize('image')]),
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    protected function prepareForValidation()
    {
        showValidationMessageForUploadMaxSize(files: $this->allFiles(), isAjax: $this->ajax(), doesExpectJson: $this->expectsJson());
    }
}
