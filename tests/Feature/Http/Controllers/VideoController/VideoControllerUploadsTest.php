<?php

namespace Tests\Feature\Http\Controllers\VideoController;

use App\Models\Genre;
use App\Models\Video;
use App\Models\Category;
use Illuminate\Support\Arr;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidation;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\TestResponse;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidation, TestUploads;

    public function testInvalidationVideoField()
    {
        $this->assertInvalidationFile (
            'video_file',
            'mp4',
            video::VIDEO_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']);
    }

    public function testInvalidationThumberFile()
    {
        $this->assertInvalidationFile(
            'thumb_file',
            'jpg',
            video::THUMB_FILE_MAX_SIZE,
            'image'
        );
    }

    public function testInvalidationBannerFile()
    {
        $this->assertInvalidationFile(
            'banner_file',
            'jpg',
            video::BANNER_FILE_MAX_SIZE,
            'image'
        );
    }

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
        // UploadedFile::fake()->create("image.jpg");

        \Storage::fake();
        $files = $this->getFiles();

        // $category = factory(Category::class)->create();
        // $genre = factory(Genre::class)->create();
        // $genre->categories()->sync($category->id);

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + $files
        );

        $response->assertStatus(201);
        $this->assertFilesOnPersist($response, $files);
        // $id = $response->json('id');
        // foreach ($files as $file ) {
        //     \Storage::assertExists("$id/{$file->hashName()}");
        // }
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        // $category = factory(Category::class)->create();
        // $genre = factory(Genre::class)->create();
        // $genre->categories()->sync($category->id);

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $files
        );

        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, $files);

        $newFiles = [
            'thumb_file' => UploadedFile::fake()->create('thumb_file.jpg'),
            'video_file' => UploadedFile::fake()->create('video_file.mp4')
        ];

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData + $newFiles
        );

        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, Arr::except($files, ['thumb_file', 'vide_file']) + newFiles);

        $id = $response->json('id');
        $video = Video::find($id);
        \Storage::assertMissing($video->relativeFilePath($files['thumb_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['video_file']->hashName()));
        // foreach($files as $file) {
        //     \Storage::assertExists("$id/{$file->hashName()}");
        // }
    }

    protected function assertFilesOnPersist(TestResponse $response, $files)
    {
        $id = $response->json('id');
        $video = Video::find($id);
        $this->assertFilesExistInStorage($video, $files);
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video.mp4'),
            'banner_file' => UploadedFile::fake()->create('banner.jpg'),
            'thumb_file' => UploadedFile::fake()->create('thumb.jpg'),
            'trailer_file' => UploadedFile::fake()->create('trailer.mp4'),
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
