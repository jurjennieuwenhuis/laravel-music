<?php

namespace Database\Factories;

use App\Models\UploadedFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class UploadedFileFactory extends Factory
{
    protected $model = UploadedFile::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'original_name' => $this->faker->name(),
            'url' => $this->faker->url(),
            'mime_type' => $this->faker->word(),
            'extension' => $this->faker->word(),
            'size' => $this->faker->word(),
            'path' => $this->faker->word(),
            'disk' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
