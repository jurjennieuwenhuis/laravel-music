<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

trait AddImageTrait
{
    public function addImages(Model $model, array $images, string $collection): void
    {
        $imagePath = database_path('seeders/local_images/');

        foreach ($images as $image) {
            try {
                $model
                    ->addMedia($imagePath . $image)
                    ->preservingOriginal()
                    ->toMediaCollection($collection);
            } catch (FileDoesNotExist|FileIsTooBig) {}
        }
    }
}
