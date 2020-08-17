<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use App\Http\Resources\CastMemberResource;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidation;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidation, TestSaves, TestResources;
    
    private $castMember;
    private $serializeFields = [
        'id',
        'name',
        'type',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create();
    }
    public function testIndex()
    {
        $response = $this->get(route('cast_members.index'));

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

        $resource = CastMemberResource::collection(collect([$this->castMember]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializeFields,
            ]);

        $id = $response->json('data.id');
        $resource = new CastMemberResource(CastMember::find($id));
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
            'type' => 'A'
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testStore()
    {
        $data = [
            "name" => "teste",
            "type" => CastMember::TYPE_DIRECTOR
        ];
        $response = $this->assertStore($data, $data + ['deleted_at' => null ]);
        $response->assertJsonStructure([
            'data' => $this->serializeFields,
        ]);

        $data = [
            'name' => 'Teste',
            'type' => CastMember::TYPE_ACTOR,
        ];
        $this->assertStore($data, $data + ['deleted_at' => null ]);

        $id = $response->json('data.id');
        $resource = new CastMemberResource(CastMember::find($id));
        $this->assertResource($response, $resource);
    }

    public function testUpdate()
    {
        $this->castMember = factory(CastMember::class)->create([
            'name' => 'Teste',
            'type' => CastMember::TYPE_DIRECTOR
        ]);
        $data = [
            'name' => 'Teste update',
            'type' => CastMember::TYPE_ACTOR
        ];
        
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        // $response->assertJsonStructure([
        //     'data' => $this->serializeFields
        // ]);
    }

    public function testDelete()
    {
        $castMember = factory(CastMember::class)->create();
        $response = $this->json('DELETE', route('cast_members.destroy', ['cast_member' => $castMember->id]));
        $response->assertStatus(204);
        $this->assertNull(CastMember::find($castMember->id));
        $this->assertNotNull(CastMember::withTrashed()->find($castMember->id)); //verifica se consegue pegar a genre na lixera (exclusão logica)
    }

    protected function routeStore()
    {
        return route('cast_members.store');
    }

    protected function routeUpdate()
    {
        return route('cast_members.update', ['cast_member' => $this->castMember->id]);
    }

    protected function model()
    {
        return CastMember::class;
    }
}
