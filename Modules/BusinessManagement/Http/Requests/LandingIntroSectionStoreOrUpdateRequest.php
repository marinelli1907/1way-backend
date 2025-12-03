<?php

namespace Modules\BusinessManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LandingIntroSectionStoreOrUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => ['required', Rule::in(INTRO_SECTION, INTRO_SECTION_IMAGE)],
            'title' => Rule::requiredIf(function () {
                return $this->input('type') === INTRO_SECTION;
            }),
            'sub_title' => Rule::requiredIf(function () {
                return $this->input('type') === INTRO_SECTION;
            }),
            'background_image' => [
                'image',
                'mimes:jpg,png,jpeg,webp',
                'max:' . (convertToBytes(maxUploadSize('image')) / 1024)],
            ];
    }

    public function messages()
    {
        return [
            'background_image.max' => translate(key: 'The Background Image must be less than {maxSize}', replace: ['maxSize' => readableUploadMaxFileSize('image')])
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
