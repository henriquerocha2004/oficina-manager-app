<?php

namespace App\Http\Requests\tenant;

use Illuminate\Foundation\Http\FormRequest;

class UploadServiceOrderPhotoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'photo' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:30720', // 30MB in kilobytes
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Nenhuma foto foi enviada.',
            'photo.file' => 'O arquivo enviado é inválido.',
            'photo.image' => 'O arquivo deve ser uma imagem.',
            'photo.mimes' => 'A foto deve ser nos formatos: JPEG, JPG, PNG ou WEBP.',
            'photo.max' => 'A foto não pode ser maior que 30MB.',
        ];
    }
}
