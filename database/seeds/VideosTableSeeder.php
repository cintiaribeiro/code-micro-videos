<?php

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;

class VideosTableSeeder extends Seeder
{
    private $allGenres;
    private $relations = [
        'genre_id' => [],
        'category_id' => [],
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dir = \Storage::getDriver()->getAdapter()->getPathPrefix();
        \File::deleteDirectory($dir, true);

        $self = $this;
        $this->allGenres = Genre::all();
        Model::reguard(); //mass assignment
        factory(Video::class, 100)
            ->make()
            ->each(function(Video $video) use ($self){
                $self->fetchRelations();
                Video::create(
                    array_merge(
                        $video->toArray(),
                        [
                            'thumb_file' => $self->getImageFile(),
                            'banner_file' => $self->getImageFile(),
                            'trailer_file' => $self->getVideoFile(),
                            'video_file' => $self->getVideoFile(),
                        ],
                        $this->relations
                    )
                );
            });
        Model::unguard();
    }

    public function fetchRelations()
    {
        $subGenres = $this->allGenres->random(5)->load('categories');
        $categoriesId = [];
        foreach ($subGenres as $genre) {
            array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
        }
        $categoriesId = array_unique($categoriesId);;
        $genresId = $subGenres->pluck('id')->toArray();
        $this->relations['category_id'] = $categoriesId;
        $this->relations['genre_id'] = $genresId;
    }

    public function getImageFile()
    {
        return new UploadedFile(
            storage_path('faker/thumbs/Laravel.png'),
            'Laravel Framework.png'
        );
    }

    public function getVideoFile()
    {
        return new UploadedFile(
            storage_path('faker/videos/teste.mp4'),
            '01-Como vai funcionar os uploads.mp4'
        );
    }
}
