<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Exceptions\ServiceOrder\ServiceOrderPhotoNotFoundException;
use App\Models\Tenant\ServiceOrderPhoto;
use Throwable;

readonly class DeleteServiceOrderPhotoAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(string $serviceOrderId, string $photoId): void
    {
        $photo = ServiceOrderPhoto::query()
            ->where('id', $photoId)
            ->where('service_order_id', $serviceOrderId)
            ->first();

        throw_if(is_null($photo), ServiceOrderPhotoNotFoundException::class);

        // The model's deleting event will handle file deletion
        $photo->delete();
    }
}
