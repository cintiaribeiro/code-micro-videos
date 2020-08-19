<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(CastMember::class, 1)->create();
        $castMember = CastMember::all();
        $this->assertCount(1, $castMember);

        $castMemberKeys = array_keys($castMember->first()->getAttributes());
        $attributes = [
            'id', 'name', 'type', 'created_at', 'updated_at', 'deleted_at'
        ];
        $this->assertEqualsCanonicalizing($attributes, $castMemberKeys);
    }

    public function testUuid()
    {
        $castMember = factory(CastMember::class)->create();

        $regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
        $this->assertTrue((bool) preg_match($regex, $castMember->id));

        $searchCastMember = CastMember::find($castMember->id);
        $this->assertNotNull($searchCastMember);
    }

    public function testCreate()
    {
        $castMember = CastMember::create(['name' => 'Teste','type' => CastMember::TYPE_DIRECTOR]);
        $castMember->refresh();
        $this->assertEquals('Teste', $castMember->name);
        $this->assertEquals(1, $castMember->type);

        $castMember = CastMember::create(['name' => 'Teste','type' => CastMember::TYPE_ACTOR]);
        $castMember->refresh();
        $this->assertEquals('Teste', $castMember->name);
        $this->assertEquals(2, $castMember->type);

    }

    public function testUpdate()
    {
        $castMember = CastMember::create(['name' => 'Teste','type' => CastMember::TYPE_DIRECTOR]) ->first();
        $data = [
            'name' => 'Teste Name',
            'type' => CastMember::TYPE_ACTOR
        ];

        $castMember->update($data);


        foreach ($data as $key=>$value) {
            $this->assertEquals($value, $castMember->{$key});
        }
    }

    public function testDelete()
    {
        $castMember = factory(CastMember::class)->create();
        $castMember->delete();
        $this->assertNull(CastMember::find($castMember->id));

//        Exclusão lógica
        $castMember->restore();
        $this->assertNotNull(CastMember::find($castMember->id));
    }
}
