<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidation;
use Tests\Traits\TestResources;


class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidation, TestSaves, TestResources;

    private $category;
    private $serializeFields = [
        'id',
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));
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

            $resource = CategoryResource::collection(collect([$this->category]));
            $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));
    
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializeFields,
            ]);
            // ->assertJson($this->category->toArray());

            $id = $response->json('data.id');
            $resource = new CategoryResource(Category::find($id));
            $this->assertResource($response, $resource);
    }

    public function testInvalidationData()
    {
        $data = [
            "name" => ""
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

    public function testStore()
    {
        $data = [
            "name" => "teste"
        ];
        $response = $this->assertStore($data, $data + ['description' => null, 'is_active' => true, 'deleted_at' => null ]);
        $response->assertJsonStructure([
            'data' => $this->serializeFields,
        ]);
        $data = [
            'name' => 'Teste',
            'description' => 'description',
            'is_active' => false,            
        ];
        $this->assertStore($data, $data + ['description' => 'description', 'is_active' => false]);

        $id = $response->json('data.id');
        $resource = new CategoryResource(Category::find($id));
        $this->assertResource($response, $resource);
        // $array =  (new CategoryResource(Category::first()))->response()->getData(true);
        // $response->assertJson($array);
    }

    public function testUpdate()
    {
        $this->category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
        ]);
        $data = [
            'name' => 'Teste',
            'description' => 'Test',
            'is_active' => true
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'data' => $this->serializeFields
        ]);

        // $id = $response->json('data.id');
        // $resource = new CategoryResource(Category::find($id));
        // $this->assertResource($response, $resource);
            
        $data = [
            'name' => 'Teste',
            'description' => '',
        ];
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test';
        $this->assertUpdate($data, array_merge($data, ['description' => 'test']));

        $data['description'] = null;
        $this->assertUpdate($data, array_merge($data, ['description' => null]));
    }

    public function testDelete()
    {
        $category = factory(Category::class)->create();
        $response = $this->json('DELETE', route('categories.destroy', ['category' => $category->id]));
        $response->assertStatus(204);
        $this->assertNull(Category::find($category->id));
        $this->assertNotNull(Category::withTrashed()->find($category->id));
    }

    private function assertInvalidationRequire(TestResponse $response)
    {
        $this
            ->assertInvalitationFields($response, ['name'], 'required');
        $response
            ->assertJsonMissingValidationErrors(['is_active']);
    }

    private function assertInvalidationMax(TestResponse $response)
    {
        $this
            ->assertInvalitationFields($response, ['name'], 'max.string', ['max' => 255]);
    }

    private function assertInvalidationBoolean(TestResponse $response)
    {
        $this
            ->assertInvalitationFields($response, ['is_active'], 'boolean');
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }
}
