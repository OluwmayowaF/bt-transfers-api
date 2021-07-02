<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransfer extends FormRequest
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
            'user_bank' => ['required'],
            'user_acc_num' => ['required'],
            'recipient_acc_num' => ['required'],
            'recipient_bank' => ['required'],
            'amount' => ['required']
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_bank.required' => 'User bank name field is required',
            'user_acc_num.required' => 'User account number field is required',
            'recipient_acc_num.required' => 'Recipient account number field is required',
            'recipient_bank.required' => 'Recipient bank name field is required',
            'amount.required' => 'Amount field is required'
        ];
    }
}
