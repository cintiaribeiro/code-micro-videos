<?php

namespace Tests\Feature\Http\Controllers\VideoController;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidation;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidation, TestUploads;

    // public function testInvalidationVideoField()
    // {
    //     $this->assertInvalidationFile (
    //         'video_file',
    //         'mp4',
    //         video::VIDEO_FILE_MAX_SIZE,
    //         'mimetypes', ['values' => 'video/mp4']);
    // }

    // public function testInvalidationThumberFile()
    // {
    //     $this->assertInvalidationFile(
    //         'thumb_file',
    //         'jpg',
    //         video::THUMB_FILE_MAX_SIZE,
    //         'image'
    //     );
    // }

    // public function testInvalidationBannerFile()
    // {
    //     $this->assertInvalidationFile(
    //         'banner_file',
    //         'jpg',
    //         video::BANNER_FILE_MAX_SIZE,
    //         'image'
    //     );
    // }

    public function testInvalidationTrailerFile()
    {
        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
            video::TRAILER_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testStoreWithFiles()
    {
        UploadedFile::fake()->create("image.jpg");

        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData +
            [
                'category_id' => [$category->id],
                'genre_id' => [$genre->id],
            ] +
            $files
        );

        $response->assertStatus(201);
        $id = $response->json('id');
        foreach ($files as $file ) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData +
            [
                'category_id' => [$category->id],
                'genre_id' => [$genre->id]
            ] +
            $files
        );

        $response->assertStatus(200);
        $id = $response->json('id');
        foreach($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video.mp4')
        ];
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}
