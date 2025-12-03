<?php

namespace Modules\VehicleManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VehicleStoreUpdateRequest extends FormRequest
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
            'brand_id' => 'required',
            'model_id' => 'required',
            'category_id' => 'required',
            'licence_plate_number' => 'required',
            'licence_expire_date' => 'required|date',
            'vin_number' => 'nullable',
            'transmission' => 'nullable',
            'parcel_weight_capacity' => 'nullable',
            'fuel_type' => 'required',
            'ownership' => 'required|in:admin,driver',
            'driver_id' => 'required|unique:vehicles,driver_id,' . $id,
            'existing_documents' => 'nullable|array',
            'deleted_documents' => 'nullable|array',
            'other_documents' => 'array',
            'other_documents.*' => [
                Rule::requiredIf(empty($id)),
                'mimes:' . str_replace(['.', ' '], '', (IMAGE_ACCEPTED_EXTENSIONS . ', ' . FILE_ACCEPTED_EXTENSIONS) ),
                'max:' . (convertToBytes(maxUploadSize('image')) / 1024)
            ],
            'type' => 'nullable'
        ];
    }

    public function messages(): array
    {
        return [
            'other_documents.*.max' => translate(key: 'Each Document must be less than {maxSize}', replace: ['maxSize' => readableUploadMaxFileSize('file')]),
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
