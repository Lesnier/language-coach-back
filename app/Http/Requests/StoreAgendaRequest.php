<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Availability;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAgendaRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'time' => 'required|date_format:H:i:s',
            'professor_id' => 'required|exists:users,id',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if the professor_id belongs to a user with role_id = 3
            $isProfessor = User::where('id', $this->professor_id)
                ->where('role_id', 3)
                ->exists();
                
            if (!$isProfessor) {
                $validator->errors()->add('professor_id', 'The selected user is not a professor (must have role_id=3).');
                return;
            }

            // Check if the professor is available at the requested time
            $isAvailable = Availability::where('user_id', $this->professor_id)
                ->where('date', $this->date)
                ->where('start_time', '<=', $this->time)
                ->where('end_time', '>', $this->time)
                ->where('is_available', 1)
                ->exists();

            if (!$isAvailable) {
                $validator->errors()->add('professor_id', 'Professor is not available at the requested time.');
            }
        });
    }
    
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors()
        ], 422));
    }
}