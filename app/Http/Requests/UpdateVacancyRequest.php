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
            "position"       => "required|integer",
            "helpline"       => "required|string|max:255|regex:/^[0-9\-, ]+$/",
//            "open_at"        => "required|date_format:Y-m-d H:i:s",
//            "close_at"       => "required|date_format:Y-m-d H:i:s",
//            "publication"    => "nullable|boolean",
            "demands"        => "required|array|min:1",
            "specials"       => "array",
            'demands.*.dir_demand_id' => 'required_with:demands.*.score,demands.*.adder_text|integer',
            'demands.*.score'         => 'required_with:demands.*.dir_demand_id,demands.*.adder_text|numeric',
            'demands.*.adder_text'    => 'required_with:demands.*.dir_demand_id,demands.*.score',
            'demands.*.demand_type'   => 'required_with:demands.*.dir_demand_id,demands.*.score,demands.*.adder_text',
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

            // Asosiy talablar ballari yig'indisi aynan 5 ball bo'lishi shart
            $mainScoreSum = 0;
            foreach ($demands as $demand) {
                $type = $demand['demand_type'] ?? 'main';
                if ($type === 'main' && (!empty($demand['dir_demand_id']) || !empty($demand['score']))) {
                    $mainScoreSum += (float) ($demand['score'] ?? 0);
                }
            }

            if ($mainScoreSum != 5) {
                $validator->errors()->add(
                    "demands",
                    "Asosiy talablar bo‘yicha umumiy ball aynan 5 ball bo‘lishi shart (hozirgi yig‘indi: {$mainScoreSum} ball)."
                );
            }

            // Qo'shimcha talablar ballari yig'indisi 5 balldan oshmasligi kerak (ixtiyoriy, lekin 5 dan oshmasligi shart)
            $additionalScoreSum = 0;
            foreach ($demands as $demand) {
                $type = $demand['demand_type'] ?? 'main';
                if ($type === 'additional' && (!empty($demand['dir_demand_id']) || !empty($demand['score']))) {
                    $additionalScoreSum += (float) ($demand['score'] ?? 0);
                }
            }

            if ($additionalScoreSum > 5) {
                $validator->errors()->add(
                    "demands",
                    "Qo‘shimcha talablar bo‘yicha umumiy ball 5 balldan oshmasligi kerak (hozirgi yig‘indi: {$additionalScoreSum} ball)."
                );
            }
        });
    }
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'message' => $validator->errors()->first() ?: 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
