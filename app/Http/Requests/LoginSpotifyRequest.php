<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginSpotifyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'remember' => 'required|boolean',
        ];
    }
}
