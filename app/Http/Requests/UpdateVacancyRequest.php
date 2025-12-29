<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVacancyRequest extends FormRequest
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
            "region_id"      => "required|integer",
            "occupation_id"  => "required|integer",
//            "open_at"        => "required|date_format:Y-m-d H:i:s",
//            "close_at"       => "required|date_format:Y-m-d H:i:s",
            "publication"    => "nullable|boolean",
            "demands"        => "required|array|min:1",
            "specials"       => "array",
            'demands.*.dir_demand_id' => 'required_with:demands.*.score,demands.*.adder_text|integer',
            'demands.*.score'         => 'required_with:demands.*.dir_demand_id,demands.*.adder_text|numeric',
            'demands.*.adder_text'    => 'required_with:demands.*.dir_demand_id,demands.*.score',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $demands = $this->input('demands', []);
            $hasFilled = false;
            foreach ($demands as $index => $item) {
                $filled = collect($item)->filter(fn($v) => !empty($v))->isNotEmpty();
                if ($filled && !$hasFilled) {
                    $hasFilled = true;
                }
                elseif ($hasFilled && collect($item)->filter(fn($v) => empty($v))->isNotEmpty()) {
                    $validator->errors()->add(
                        "demands.$index",
                        "Barcha demands to‘ldirilishi kerak, chunki avvalgilari to‘ldirilgan."
                    );
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
