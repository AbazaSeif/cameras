<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CameraRequest extends FormRequest
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
     * @param Request $request
     * @return array
     */
    public function rules(Request $request)
    {
        if ($request->isMethod('post')) {
            $passwordRule = 'required_with:user|confirmed';
        }

        if ($request->isMethod('put')) {
            $passwordRule = 'sometimes|confirmed';
        }
        return [
            'label' => 'required',
            'ip' => 'required|ip',
            'port' => [
                'required',
                Rule::unique('cameras')->where(function ($query) use ($request){
                    $query->where('ip', $request->get('ip'));
                })
            ],
            'user' => 'sometimes',
            'password' => $passwordRule,
        ];
    }
}
