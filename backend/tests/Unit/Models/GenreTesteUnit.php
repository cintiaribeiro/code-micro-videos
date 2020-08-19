<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreTesteUnit extends TestCase
{
    private $genre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->genre = new Genre();
    }
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testFillable()
    {
        $fillable = ['name', 'is_active'];
        $this->assertEquals($fillable, $this->genre->getFillable());
    }

    public function testTraits()
    {
        $trait = [
            SoftDeletes::class,
            \App\Models\Traits\Uuid::class
        ];

        $genreTraits = array_keys(class_uses(Genre::class));
        $this->assertEquals($trait, $genreTraits);
    }

    public function testCastsAttribute()
    {
        $cats = [
            'id' => 'string',
            'is_active' => 'boolean'
        ];

        $this->assertEquals($cats, $this->genre->getCasts());
    }

    public function testDatesAttibuilte()
    {
        $fieldDate = [
            'created_at',
            'updated_at',
            'deleted_at'
        ];

        foreach ($fieldDate as $field){
            $this->assertContains($field, $this->genre->getDates());
        }

        $this->assertCount(count($fieldDate), $this->genre->getDates() );
    }

    public function testIncrementingAttribute()
    {
        $this->assertFalse($this->genre->incrementing);
    }
}
