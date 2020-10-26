<?php

namespace FDT\DataLoader\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'action' => 'required|in:approve,reject',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->action == 'reject') {
                if (strlen($this->comment) < 10 || strlen($this->comment) > 100) {
                    $validator->errors()->add('comment', 'Required if action is reject 10-100 characters');
                }
            }
        });
    }
}
