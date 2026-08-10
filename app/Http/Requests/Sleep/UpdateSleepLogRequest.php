<?php

namespace App\Http\Requests\Sleep;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSleepLogRequest extends FormRequest
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
            'bedtime' => ['nullable', 'date_format:H:i'],
            'wake_time' => ['nullable', 'date_format:H:i'],
            'sleep_quality' => ['nullable', 'in:very_poor,poor,fair,good,excellent'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bedtime.date_format' => 'فرمت زمان خواب نامعتبر است',
            'wake_time.date_format' => 'فرمت زمان بیداری نامعتبر است',
            'sleep_quality.in' => 'کیفیت خواب باید یکی از مقادیر معتبر باشد',
            'note.max' => 'یادداشت نباید بیشتر از ۵۰۰ کاراکتر باشد',
        ];
    }
}
