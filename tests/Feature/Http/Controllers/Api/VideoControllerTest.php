<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exception\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidation;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidation;

    private $video;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create([
            'opened' => false
        ]);
        $this->sendData = [
            'title' => 'Titulo',
            'description' => "Descrição",
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'category_id' => '',
            'genre_id' => '',
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }
    public function testInvalidationMax()
    {
        $data = [
            'title' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger()
    {
        $data = [
            'duration' => 's',
        ];

        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testInvalidationYearLaunchedField()
    {
        $data = [
            'year_launched' =>  'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format'=>'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format'=>'Y']);
    }

    public function testInvalidationOpenedField()
    {
        $data = [
            'opened' => 's',
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidationRatingField()
    {
        $data = [
            'rating' => 0
        ];

        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testInvalidationCategoriesIdField()
    {
        $data = [
            'category_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'category_id' => [100]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            "category_id" => [$category->id]
        ];

        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

    }

    public function testInvalidationGenresIdField()
    {
        $data = [
            'genre_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'category_id' => [100]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $genre = factory(Genre::class)->create();
        $genre->delete();
        $data = [
            "genre_id" => [$genre->id]
        ];

        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testSave()
    {
        $categoty = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($categoty->id);
        $data = [
            [
                'send_data' => $this->sendData + [
                    'category_id' => [$categoty->id],
                    'genre_id' => [$genre->id],
                ],
                'test_data' => $this->sendData + ['opened' => false],
            ],
            [
                'send_data' => $this->sendData + [
                    'opened' => true,
                    'category_id' => [$categoty->id],
                    'genre_id' => [$genre->id],
                ],
                'test_data' => $this->sendData + ['opened' => true],
            ],
            [
                'send_data' => $this->sendData + [
                    'rating' => Video::RATING_LIST[1],
                    'category_id' => [$categoty->id],
                    'genre_id' => [$genre->id],
                ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]],
            ],
        ];

        foreach ($data as $key => $value) {
            $response = $this->assertStore(
                $value['send_data'], $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at', 'updated_at'
            ]);
            $this->assertHasCategory(
                $response->json('id'),
                $value['send_data']['category_id'][0]
            );

            $this->assertHasGenre(
                $response->json('id'),
                $value['send_data']['genre_id'][0]
            );
            $response = $this->assertUpdate(
                $value['send_data'], $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at', 'updated_at'
            ]);
            $this->assertHasCategory(
                $response->json('id'),
                $value['send_data']['category_id'][0]
            );

            $this->assertHasGenre(
                $response->json('id'),
                $value['send_data']['genre_id'][0]
            );
        }
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

    public function testRollbackStore()
    {
        //configuro o mockery
        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        //ignoro a validação dos dados passados
        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller->shouldReceive('rolesStore')
            ->withAnyArgs()
            ->andReturn([]);

        //forçando o método retornar um exception
        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')
                ->withAnyArgs()
                ->andReturnNull();

        $hasError = false;
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Video::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        //configuro o mockery
        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        //ignoro a validação dos dados passados
        $controller->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->video);

        //ignoro a validação dos dados passados
        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                "name"=>'testt'
            ]);

        $controller->shouldReceive('rolesUpdate')
            ->withAnyArgs()
            ->andReturn([]);

        //forçando o método retornar um exception
        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->withAnyArgs()
            ->andReturnNull();

        $hasError = false;
        try {
            $controller->update($request, 1);
        } catch (TestException $e) {
            $this->assertCount(1, Video::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testDelete()
    {
        $video = factory(Video::class)->create();
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($video->id));
        $this->assertNotNull(Video::withTrashed()->find($video->id)); //verifica se consegue pegar a genre na lixera (exclusão logica)
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($categoriesId);
        $genreId = $genre->id;

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + [
                'genre_id' => [$genreId],
                'category_id' => [$categoriesId[0]]
            ]
        );

        $this->assertDatabaseHas('category_video', [
           'category_id' => $categoriesId[0],
           'video_id' =>  $response->json('id')
        ]);

        $response = $this->json(
            'PUT',
            route('videos.update', ['video' => $response->json('id')]),
            $this->sendData + [
                'genre_id' => [$genreId],
                'category_id' => [$categoriesId[1], $categoriesId[2]]
            ]
        );

        $this->assertDatabaseMissing('category_video',[
            'video_id' => $response->json('id'),
            'category_id' => $categoriesId[0]
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1],
            'video_id' =>  $response->json('id')
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[2],
            'video_id' =>  $response->json('id')
        ]);
    }

    public function testSyncGenres()
    {
        /** @var Collection $genres **/
        $genres = factory(Genre::class, 3)->create();
        $genresId = $genres->pluck('id')->toArray();
        $categotyId = factory(Category::class)->create()->id;
        $genres->each(function ($genre) use ($categotyId){
           $genre->categories()->sync($categotyId);
        });

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData +
            [
                'category_id' => [$categotyId],
                'genre_id' => [$genresId[0]]
            ]
        );

        $this->assertDatabaseHas('genre_video',[
                'video_id' => $response->json('id'),
                'genre_id' => $genresId[0]
            ]
        );

        $response = $this->json(
            'PUT',
           route('videos.update', ['video' => $response->json('id')]),
            $this->sendData + [
                'category_id' => [$categotyId],
                'genre_id' => [$genresId[1], $genresId[2]]
            ]
        );

        $this->assertDatabaseMissing('genre_video', [
            'video_id' => $response->json('id'),
            'genre_id' => $genresId[0]
        ]);

        $this->assertDatabaseHas('genre_video', [
            'video_id' => $response->json('id'),
            'genre_id' => $genresId[1]
        ]);

        $this->assertDatabaseHas('genre_video', [
            'video_id' => $response->json('id'),
            'genre_id' => $genresId[2]
        ]);

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
