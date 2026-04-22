<?php

namespace App\Dto;

use Illuminate\Http\UploadedFile;

class ServiceOrderPhotoDto
{
    public function __construct(
        public string $service_order_id,
        public UploadedFile $photo,
        public int $uploaded_by,
    ) {
    }
}
