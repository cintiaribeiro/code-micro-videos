<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreTeste extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(Genre::class, 1)->create();
        $genres = Genre::all();
        $this->assertCount(1, $genres);

        $genreKeys = array_keys($genres->first()->getAttributes());
        $attributes = [
            'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ];
        $this->assertEqualsCanonicalizing($attributes, $genreKeys);
    }

    public function testUuid()
    {
        $genre = factory(Genre::class)->create();

        $regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
        $this->assertTrue((bool) preg_match($regex, $genre->id));

        $searchGenre = Genre::find($genre->id);
        $this->assertNotNull($searchGenre);
    }

    public function testCreate()
    {
        $genre = Genre::create(['name' => 'Teste']);
        $genre->refresh();

        $this->assertEquals('Teste', $genre->name);
        $this->assertTrue($genre->is_active);

        $genre = Genre::create(['name' => 'Teste', 'is_active' => false]);
        $this->assertFalse($genre->is_active);

        $genre = Genre::create(['name' => 'Teste', 'is_active' => true]);
        $this->assertTrue($genre->is_active);
    }

    public function testUpdate()
    {
        /** @var Genre $genre */
        $genre = factory(Genre::class, 1)
            ->create(["name" => "test", "is_active" => true])
            ->first();

        $data = [
            'name' => 'GenreTesteUpdate',
            'is_active' => false
        ];

        $genre->update($data);

        foreach ($data as $key=>$value) {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDelete()
    {
        $genre = factory(Genre::class)->create();
        $genre->delete();
        $this->assertNull(Genre::find($genre->id));

//        Exclusão lógica
        $genre->restore();
        $this->assertNotNull(Genre::find($genre->id));
    }

}
