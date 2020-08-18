<?php

namespace Tests\Feature\Http\Controllers\VideoController;

use App\Models\Video;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

abstract class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations;

    protected $video;
    protected $sendData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->video = factory(Video::class)->create([
            'opened' => false,
            'thumb_file' => 'thumb.jpg',
            'banner_file' => 'banner.jpg',
            'video_file' => 'video.mp4',
            'trailer_file' => 'trailer.mp4',
        ]);

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category);

        $this->sendData = [
            "title" => 'Title',
            "description" => 'description',
            "year_launched" => 2010,
            "rating" => Video::RATING_LIST[0],
            "duration" => 90,
            "opened" => false,
            "category_id" => [$category->id],
            "genre_id" => [$genre->id]
        ];
    }

    protected function assertIfFilesUrlExists(Video $video, TestResponse $response)
    {
        $fileFields = Video::$fieldFiles;
        $data = $response->json('data');
        $data = array_key_exists(0, $data) ? $data[0] : $data;
        foreach ($fileFields as $field) {            
            $file = $video->{$field};           
            $this->assertEquals(
                \Storage::url($video->relativeFilePath($file)),
                $data[$field . '_url']
            );
        }
    }
}
