<?php

namespace Tests\Feature\Models;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exception\TestException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VideoTeste extends TestCase
{
    use DatabaseMigrations;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90
        ];
    }

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

    public function testCreateWithBasicFields()
    {
        $video  = Video::create($this->data);
        $video->refresh();

        $this->assertEquals(36, strlen($video->id));
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = Video::create($this->data + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);
    }

    public function testCreateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = Video::create($this->data + [
                'category_id' => [$category->id],
                'genre_id' => [$genre->id]
            ]);

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
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

    public function testUpdateWithBasicFields()
    {
        $video = factory(Video::class)->create(
            ['opened' => false]
        );
        $video->update($this->data);
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = factory(Video::class)->create(
            ['opened' => false]
        );
        $video->update($this->data + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);
    }

    public function testUpdateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = factory(Video::class)->create();
        $video->update($this->data + [
                'category_id' => [$category->id],
                'genre_id' => [$genre->id],
            ]);
        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testHandleRelation()
    {
        $video = factory(Video::class)->create();
        Video::handleRelations($video, []);
        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);

        $category = factory(Category::class)->create();
        Video::handleRelations($video, [
            'category_id' => [$category->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);

        $genre = factory(Genre::class)->create();
        Video::handleRelations($video, [
            'genre_id' => [$genre->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->genres);

        $video->categories()->delete();
        $video->genres()->delete();

        Video::handleRelations($video, [
            'genre_id' => [$genre->id],
            'category_id' => [$category->id],
        ]);
        $video->refresh();
        $this->assertCount(1, $video->genres);
        $this->assertCount(1, $video->categories);


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

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
            'category_id' => [$categoriesId[0]]
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'category_id' => [$categoriesId[1], $categoriesId[2]]
        ]);
        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1],
            'video_id' => $video->id
        ]);
    }

    public function testSyncGenres()
    {
        $genreId = factory(Genre::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
            'genre_id' => [$genreId[0]]
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genreId[0],
            'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'genre_id' => [$genreId[1], $genreId[2]]
        ]);
        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genreId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genreId[1],
            'video_id' => $video->id
        ]);

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

    public function testRollbackStore()
    {

        $hasError = false;
        try {
            Video::create([
                'title' => 'Titulo',
                'description' => "Descrição",
                'year_launched' => 2010,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'category_id' => [0, 1, 2]
            ]);
        } catch (QueryException $e) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
       $video = factory(Video::class)->create();
       $oldTitle = $video->title;
        try {
            $video->update([
                'title' => 'Titulo',
                'description' => "Descrição",
                'year_launched' => 2010,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'category_id' => [0, 1, 2]
            ]);
        } catch (QueryException $e) {
            $this->assertDatabaseHas('videos', [
               'title'=> $oldTitle
            ]);
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    protected  function assertHasCategory($videoId, $cateoryId)
    {
        $this->assertDatabaseHas('category_video', [
            "category_id" => $cateoryId,
            "video_id" => $videoId
        ]);
    }

    protected  function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video', [
            "genre_id" => $genreId,
            "video_id" => $videoId
        ]);
    }

}
