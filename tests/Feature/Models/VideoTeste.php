<?php

namespace Tests\Feature\Models;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VideoTeste extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(Video::class, 1)->create();
        $videos = Video::all();
        $this->assertCount(1, $videos);

        $videosKeys = array_keys($videos->first()->getAttributes());

        $attributes = [
            'id',
            'title',
            'description',
            'year_launched',
            'opened',
            'rating',
            'duration',
            'created_at', 'updated_at', 'deleted_at'
        ];
        $this->assertEqualsCanonicalizing($attributes, $videosKeys);
    }

    public function testUuid()
    {
        $video = factory(Video::class)->create();

        $regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
        $this->assertTrue((bool) preg_match($regex, $video->id));

        $searchVideo= Video::find($video->id);
        $this->assertNotNull($searchVideo);
    }

    public function testCreate()
    {
        $video = Video::create([
            'title' => 'Teste',
            'description' => 'description',
            'year_launched' => 2000,
            'rating' => 'L',
            'duration' => 30,
        ]);
        $video->refresh();

        $this->assertEquals('Teste', $video->title);
        $this->assertFalse($video->opened);

        $video = Video::create([
            'title' => 'Teste',
            'description' => 'description',
            'year_launched' => 2000,
            'opened' => true,
            'rating' => 'L',
            'duration' => 30,
        ]);
        $this->assertTrue($video->opened);
    }

    public function testUpdate()
    {
        /** @var Video $video */
        $video = factory(Video::class, 1)
            ->create([
                'title' => 'Teste',
                'description' => 'description',
                'year_launched' => 2000,
                'rating' => 'L',
                'duration' => 30,
            ])
            ->first();

        $data = [
            'title' => 'Teste Mudança',
            'opened' => true,
        ];

        $video->update($data);

        foreach ($data as $key=>$value) {
            $this->assertEquals($value, $video->{$key});
        }
    }

    public function testDelete()
    {
        $video = factory(Video::class)->create();
        $video->delete();
        $this->assertNull(Video::find($video->id));

//        Exclusão lógica
        $video->restore();
        $this->assertNotNull(Video::find($video->id));
    }
}
