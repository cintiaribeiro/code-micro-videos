<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidation;

class CastMemberController extends TestCase
{
    use DatabaseMigrations, TestSaves, TestValidation;

    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create();
    }
    public function testIndex()
    {
        $response = $this->get(route('castMembers.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('castMembers.show', ['castMember' => $this->castMember->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->castMember->toArray());
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
            'created_at', 'updated_at'
        ]);

        $data = [
            'name' => 'Teste',
            'type' => CastMember::TYPE_ACTOR,
        ];
        $this->assertStore($data, $data + ['deleted_at' => null ]);
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
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
    }

    public function testDelete()
    {
        $castMember = factory(CastMember::class)->create();
        $response = $this->json('DELETE', route('castMembers.destroy', ['castMember' => $castMember->id]));
        $response->assertStatus(204);
        $this->assertNull(CastMember::find($castMember->id));
        $this->assertNotNull(CastMember::withTrashed()->find($castMember->id)); //verifica se consegue pegar a genre na lixera (exclusão logica)
    }

    protected function routeStore()
    {
        return route('castMembers.store');
    }

    protected function routeUpdate()
    {
        return route('castMembers.update', ['castMember' => $this->castMember->id]);
    }

    protected function model()
    {
        return CastMember::class;
    }
}
