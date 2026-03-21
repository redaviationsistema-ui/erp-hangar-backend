<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 🔥 IMPORTANTE
    }

    public function rules(): array
    {
        return [
            'tipo_id' => 'required|exists:tipos_orden,id',
            'descripcion' => 'required|string|max:1000',
        ];
    }
}