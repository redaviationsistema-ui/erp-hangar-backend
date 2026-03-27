<?php

namespace App\Http\Requests;

class UpdateOrdenRequest extends StoreOrdenRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        foreach ([
            'area_id',
            'tipo_id',
            'user_id',
            'descripcion',
        ] as $key) {
            $rules[$key] = preg_replace('/^required\|/', 'sometimes|', $rules[$key]);
        }

        return $rules;
    }
}
