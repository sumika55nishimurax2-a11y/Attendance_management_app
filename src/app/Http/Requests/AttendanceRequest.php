<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
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
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],
            'new_break.break_start' => ['nullable', 'date_format:H:i'],
            'new_break.break_end'   => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'note.required' => '備考を記入してください',
            'breaks.*.start.date_format' => '休憩開始時間は 00:00 形式で入力してください',
            'breaks.*.end.date_format'   => '休憩終了時間は 00:00 形式で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breaks = $this->input('breaks', []);


            if ($clockIn && $clockOut && strtotime($clockIn) >= strtotime($clockOut)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            foreach ($breaks as $index => $break) {
                $start = $break['start'] ?? null;
                $end   = $break['end'] ?? null;

                if ($start && $end) {
                    if (strtotime($start) < strtotime($clockIn) || strtotime($end) > strtotime($clockOut)) {
                        $validator->errors()->add("breaks.$index.start", '休憩時間が勤務時間外です');
                    }
                    if (strtotime($start) >= strtotime($end)) {
                        $validator->errors()->add("breaks.$index.start", '休憩開始と終了の順序が不正です');
                    }
                }
            }
        });
    }
}
