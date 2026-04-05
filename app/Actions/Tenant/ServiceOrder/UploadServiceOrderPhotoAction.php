<?php

namespace App\Actions\Tenant\ServiceOrder;

use App\Dto\ServiceOrderPhotoDto;
use App\Exceptions\ServiceOrder\ServiceOrderNotFoundException;
use App\Exceptions\ServiceOrder\ServiceOrderPhotoLimitExceededException;
use App\Models\Tenant\ServiceOrder;
use App\Models\Tenant\ServiceOrderPhoto;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Throwable;

readonly class UploadServiceOrderPhotoAction
{
    private const int MAX_PHOTOS_PER_ORDER = 5;

    /**
     * @throws Throwable
     */
    public function __invoke(ServiceOrderPhotoDto $dto): ServiceOrderPhoto
    {
        $serviceOrder = ServiceOrder::query()->find($dto->service_order_id);

        throw_if(is_null($serviceOrder), ServiceOrderNotFoundException::class);

        // Check photo limit
        $currentPhotoCount = $serviceOrder->photos()->count();
        throw_if(
            $currentPhotoCount >= self::MAX_PHOTOS_PER_ORDER,
            ServiceOrderPhotoLimitExceededException::class
        );

        // Generate unique filename (convert to jpg for consistency)
        $filename = uniqid('photo_', true) . '.jpg';
        $relativePath = "service-orders/{$dto->service_order_id}/{$filename}";

        // Process image with Intervention/Image v4
        $manager = new ImageManager(new Driver());
        $image = $manager->decodePath($dto->photo->getRealPath());

        // Scale down if wider than 1920px (maintains aspect ratio, never upscales)
        $image->scaleDown(width: 1920);

        // Fill transparent areas with white before JPEG encoding
        // (PNG/WebP alpha channel pixels become black without this)
        $image->fillTransparentAreas('ffffff');

        // Get image dimensions after processing
        $width = $image->width();
        $height = $image->height();

        // Encode as JPEG with 85% quality
        $encodedImage = $image->encode(new JpegEncoder(quality: 85));

        // Store the processed image
        Storage::disk('public')->put($relativePath, (string) $encodedImage);

        // Get file size
        $fileSize = Storage::disk('public')->size($relativePath);

        // Get next display order
        $displayOrder = $serviceOrder->photos()->max('display_order') + 1;

        // Create database record
        $photo = ServiceOrderPhoto::query()->create([
            'service_order_id' => $dto->service_order_id,
            'file_path' => $relativePath,
            'original_filename' => $dto->photo->getClientOriginalName(),
            'file_size' => $fileSize,
            'mime_type' => 'image/jpeg',
            'width' => $width,
            'height' => $height,
            'display_order' => $displayOrder,
            'uploaded_by' => $dto->uploaded_by,
        ]);

        return $photo;
    }
}
