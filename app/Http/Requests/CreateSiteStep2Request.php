<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSiteStep2Request extends FormRequest
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
            'db_name' => 'required',
            'db_user' => 'required',
            'db_pw' => 'required|alpha_num',
            'services.*.*' => 'sometimes|required',
            'admin_email' => 'required|email',
            'admin_pw' => 'required|min:5'
        ];
    }

    public function messages()
    {
        return [
            'services.rollbar.token.required' => 'rollbar token field is required'
        ];
    }
}
