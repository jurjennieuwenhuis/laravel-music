<?php

namespace Database\Seeders;

use App\Models\Artist;
use Illuminate\Database\Seeder;

class ArtistSeeder extends Seeder
{
    use AddImageTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $artists = [
            [
                'name' => 'Simon & Garfunkel',
                'bio' => 'Originally inspired by the Everly Brothers, Paul Simon & Art Garfunkel met at grade school in Forest Hills, Queens during a production of Alice In Wonderland and began releasing music as Tom & Jerry (named after the 1940s cat & mouse cartoon characters). In 1957, at sixteen years old, their single “Hey Schoolgirl” reached #49 on the Pop Chart.',
                'image' => 'simon-and-garfunkel.jpg',
                'url' => 'https://genius.com/artists/Simon-and-garfunkel',
                'spotify_code' => null,
            ],
            [
                'name' => 'The Beatles',
                'bio' => 'The Beatles are arguably the most famous, critically-acclaimed, and successful rock band of all time—certainly the preeminent group of the 20th century. They started out as four teenagers playing grimy basement clubs in Liverpool and Hamburg, but they progressed to become world-beating rock stars who are still influential to this day.',
                'image' => 'the-beatles.jpg',
                'url' => 'https://genius.com/artists/The-beatles',
                'spotify_code' => 'https://open.spotify.com/artist/3WrFJ7ztbogyGnTHbHJFl2',
            ],
        ];

        foreach ($artists as $artist) {
            $images = (array) ($artist['image'] ?? []);
            unset($artist['image']);
            $model = Artist::create($artist);
            $this->addImages($model, $images, 'artist-images');
        }
    }
}
