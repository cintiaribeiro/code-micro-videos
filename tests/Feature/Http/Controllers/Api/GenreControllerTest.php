<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Http\Resources\GenreResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exception\TestException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidation;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidation, TestResources;

    private $genre;
    private $sendData;
    private $serializeFields = [
        'id',
        'name',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();

        $this->sendData = [
            'name' => 'Teste',
            'is_active' => false
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));
        $response
            ->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializeFields
                ],
                'links' => [],
                'meta' => []
            ]);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializeFields,
            ]);

    }

    public function testInvalidationData()
    {
        $data = [
            "name" => "",
            "category_id" => "",
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256),
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'is_active' => 'A'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');

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

    public function testSave()
    {
        $categotyID = factory(Category::class)->create()->id;
        $data = [
            [
                'send_data' => [
                    'name' => 'test',
                    'category_id' => [$categotyID]
                ],
                'test_data' => [
                    'name' => 'test',
                    'is_active' => true
                ]
            ],
            [
              'send_data' => [
                  'name' => 'test',
                  'is_active' => false,
                  'category_id' => [$categotyID]
              ],
              'test_data' => [
                  'name' => 'test',
                  'is_active' => false
              ]
            ],
        ];

        foreach ($data as $test) {            
            $response = $this->assertStore(
                $test['send_data'], $test['test_data']);
            $response->assertJsonStructure([
                'data' => $this->serializeFields
            ]);
            //$this->assertHasCategory($response->json('id'), $categoty->id);
            $this->assertResource($response, new GenreResource(Genre::find($response->json('data.id'))));

            $response = $this->assertUpdate($test['send_data'], $test['test_data']);
            $response->assertJsonStructure([
                'data' => $this->serializeFields
            ]);
            $this->assertResource($response, new GenreResource(Genre::find($response->json('data.id'))));

            //update
            // $response = $this->assertUpdate(
            //     $value['send_data'], $value['test_data'] + ['deleted_at' => null]
            // );
            // $response->assertJsonStructure([
            //     'created_at', 'updated_at'
            // ]);
            // $this->assertHasCategory($response->json('id'), $categoty->id);
        }
    }

    public function testRollbackStore()
    {
        //configuro o mockery
        $controller = \Mockery::mock(GenreController::class)
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

        $hasError = false;
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        //configuro o mockery
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        //ignoro a validação dos dados passados
        $controller->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->genre);

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

        $hasError = false;
        try {
            $controller->update($request, 1);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testDelete()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $genre->id]));
        $response->assertStatus(204);
        $this->assertNull(Genre::find($genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($genre->id)); //verifica se consegue pegar a genre na lixera (exclusão logica)
    }

    public function testSyncCategory()
    {
        $categoriesId = factory(Category::class,3)->create()->pluck('id')->toArray(); //retorna somente id
        $sendData = [
            'name' => 'teste',
            'category_id' => [$categoriesId[0]]
        ];
        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id' => $response->json('data.id')
        ]);

        $sendData = [
          'name' => 'teste',
          'category_id' =>[$categoriesId[1], $categoriesId[2]]
        ];
        $response = $this->json(
            'PUT',
            route('genres.update', ['genre'=> $response->json('data.id')]),
            $sendData);

        //verifica se não tem no banco
        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id' => $response->json('data.id')
        ]);

        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[1],
            'genre_id' => $response->json('data.id')
        ]);

        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[2],
            'genre_id' => $response->json('data.id')
        ]);

    }

    protected  function assertHasCategory($genreId, $cateoryId)
    {
        $this->assertDatabaseHas('category_genre', [
            "category_id" => $cateoryId,
            "genre_id" => $genreId
        ]);
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    protected function model()
    {
        return Genre::class;
    }
}
