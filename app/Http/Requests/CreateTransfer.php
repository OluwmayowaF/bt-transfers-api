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
            'account_number' => 'required|min:10',
            'bank' => 'required|string|min:3',
            'amount' => 'required'
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
           
            'account_number.required' => 'Recipient account number field is required',
            'bank.required' => 'Recipient bank name field is required',
            'amount.required' => 'Amount field is required'
        ];
    }
}
