<?php

namespace Tests\Feature\Http\Controllers\Api;


use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidation;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidation;

    private $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);
    }


    protected function model()
    {
        return Video::class();
    }

    protected function routeStore()
    {
        // TODO: Implement routeStore() method.
    }

    protected function routeUpdate()
    {
        // TODO: Implement routeUpdate() method.
    }
}
