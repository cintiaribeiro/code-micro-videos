<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
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
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidation;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidation;

    private $genre;
    private $sendData;

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
            ->assertJson([$this->genre->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());
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
        $categoty = factory(Category::class)->create();
        $data = [
            [
                'send_data' => $this->sendData + [
                        'category_id' => [$categoty->id],
                    ],
                'test_data' => $this->sendData + ['is_active' => false],
            ],
            [
                'send_data' => $this->sendData + [
                        'is_active' => true,
                        'category_id' => [$categoty->id],
                    ],
                'test_data' => $this->sendData + ['is_active' => true],
            ],
        ];

        foreach ($data as $key => $value) {

            //store
            $response = $this->assertStore(
                $value['send_data'], $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at', 'updated_at'
            ]);
            dump($response->json('id'));
            dump($categoty->id);
            dd($response);
            $this->assertHasCategory($response->json('id'), $categoty->id);

            //update
            $response = $this->assertUpdate(
                $value['send_data'], $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at', 'updated_at'
            ]);
//            $this->assertHasCategory($response->json('id'), $categoty->id);
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

        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
        }
    }

//    public function testStore()
//    {
//        $data = [
//            "name" => "teste"
//        ];
//        $response = $this->assertStore($data, $data + ['is_active' => true, 'deleted_at' => null ]);
//        $response->assertJsonStructure([
//            'created_at', 'updated_at'
//        ]);
//
//        $data = [
//            'name' => 'Teste',
//            'is_active' => false,
//        ];
//        $this->assertStore($data, $data + ['is_active' => false, 'deleted_at' => null ]);
//    }
//
//    public function testUpdate()
//    {
//        $this->genre = factory(Genre::class)->create([
//            'is_active' => false
//        ]);
//        $data = [
//            'name' => 'Teste',
//            'is_active' => true
//        ];
//        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
//        $response->assertJsonStructure([
//            'created_at', 'updated_at'
//        ]);
//
//    }

    public function testDelete()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $genre->id]));
        $response->assertStatus(204);
        $this->assertNull(Genre::find($genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($genre->id)); //verifica se consegue pegar a genre na lixera (exclusão logica)
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
