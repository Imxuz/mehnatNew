<?php

namespace App\Http\Requests;

use App\Models\AdderDemand;
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
        return [
            'file' => 'nullable|mimes:png,jpeg,jpg,svg,pdf|max:5120',
            'dir_demand_id' => 'required|integer|exists:dir_demands,id',
            'id' => 'nullable|integer|exists:doc_users,id',
            'adder_demands_id' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $dirDemand = DirDemand::find($this->dir_demand_id);
            if (!$dirDemand) {
                return $validator->errors()->add('dir_demand_id', 'Talab topilmadi.');
            }
            if ($dirDemand->type === 'file') {
                if (!$this->hasFile('file')) {
                    $validator->errors()->add('file', 'Bu talab uchun fayl yuklash majburiy.');
                }
            }
            if ($dirDemand->type === 'text') {
                if (!$this->adder_demands_id || !AdderDemand::find($this->adder_demands_id)) {
                    $validator->errors()->add('adder_demands_id', 'To‘g‘ri maʼlumot kiriting.');
                }
            }
        });
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
