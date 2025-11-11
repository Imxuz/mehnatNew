<?php

namespace App\Http\Requests;

use App\Models\DirDemand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Regex;
class StoreUserRequest extends FormRequest

{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = auth('api')->user();
        $demand = DirDemand::join('doc_users as d', 'd.dir_demand_id', '=', 'dir_demands.id')
            ->where('dir_demands.name', 'passport')
            ->where('d.user_id', $user->id)
            ->first();
        $passportRule = !empty($demand)
            ? 'nullable|mimes:png,jpeg,jpg,svg,pdf|max:5120'
            : 'required|mimes:png,jpeg,jpg,svg,pdf|max:5120';

        return [
            'passport' => $passportRule,
            'driverLicence' => 'nullable|mimes:png,jpeg,jpg,svg,pdf|max:5120',
            'loadDriverLicence' => 'nullable|mimes:png,jpeg,jpg,svg,pdf|max:5120',
            'certificate' => 'nullable|mimes:png,jpeg,jpg,svg,pdf|max:5120',
            'education' => 'nullable|mimes:png,jpeg,jpg,svg,pdf|max:5120',
            'workbook' => 'nullable|mimes:png,jpeg,jpg,svg,pdf|max:5120',
            'militaryCertificate' => 'nullable|mimes:png,jpeg,jpg,svg,pdf|max:5120',
        ];
    }
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
