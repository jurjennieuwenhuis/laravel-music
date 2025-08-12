<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Genre;
use App\Models\Record;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;

class RecordSeeder extends Seeder
{
    use AddImageTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            [
                'name' => 'Concert in Central Park',
                'artists' => 'Simon & Garfunkel',
                'description' => 'Since Simon and Garfunkel’s split in 1970 they have reunited several times, most famously in 1981 for, “The Concert in Central Park”, which attracted more than 500,000 people, the seventh-largest concert attendance in history.',
                'release_date' => '1982-02-16',
                'type' => 'LP',
                'is_visible' => true,
                'images' => ['concert-in-the-park.jpg', 'concert-in-the-park-back.jpg', 'concert-in-central-park-3.jpg'],
                'genres' => ['Classic rock', 'Singer-songwriter', 'Folkrock'],
                'url' => 'https://genius.com/albums/Simon-and-garfunkel/The-concert-in-central-park',
                'spotify_code' => '3nIU4gxyq0MK4mlWE8ePqb',
            ],
            [
                'name' => 'Abbey Road',
                'artists' => 'The Beatles',
                'description' => '',
                'release_date' => '1969-09-26',
                'type' => 'LP',
                'is_visible' => true,
                'images' => 'abbey-road-front.jpg',
                'genres' => ['Pop', 'Rock', 'Sixties'],
                'url' => 'https://genius.com/albums/The-beatles/Abbey-road',
                'spotify_code' => 'https://open.spotify.com/album/0ETFjACtuP2ADo6LFhL6HN?si=wvYT9Wu8QTGViyCCSS9YAw',
            ],
        ];

        // Genres
        $genres = [];
        foreach ($records as $record) {
            $recordGenres = array_filter((array) ($record['genres'] ?? []));
            if (! empty($recordGenres)) {
                $genres = array_merge($genres, $recordGenres);
            }
        }

        /** @var array<string,Genre> $genreRecords */
        $genreRecords = [];
        foreach ($genres as $genre) {
            $genreRecords[$genre] = Genre::create(['name' => $genre]);
        }

        foreach ($records as $record) {
            $images = (array) ($record['images'] ?? []);
            $genres = array_filter((array) ($record['genres'] ?? []));
            $artists = (array) ($record['artists'] ?? []);
            unset($record['images'], $record['genres'], $record['artists']);

            $model = Record::factory()->create($record);

            foreach ($artists as $artist) {
                if ($artistId = $this->findArtistId($artist)) {
                    $model->artists()->attach($artistId);
                }
            }

            foreach ($genres as $genre) {
                $model->genres()->attach($genreRecords[$genre]->id);
            }

            $this->addImages($model, $images, 'record-images');
        }
    }

    private function findArtistId(string $name): ?int
    {
        try {
            return Artist::where('name', $name)->firstOrFail()->id;
        }
        catch (ModelNotFoundException) {}

        return null;
    }
}
