<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(Category::class, 1)->create();
        $categories = Category::all();
        $this->assertCount(1, $categories);

        $categoryKeys = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                "id",
                "name",
                "description",
                "is_active",
                "created_at",
                "updated_at",
                "deleted_at" ],
            $categoryKeys
        );

    }

    public function testUuid()
    {
        $category = factory(Category::class)->create();

        $regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
        $this->assertTrue((bool) preg_match($regex, $category->id));

        $searchCategory = Category::find($category->id);
        $this->assertNotNull($searchCategory);
    }

    public function testCreate()
    {
        $category = Category::create([
            "name" => "Teste"
        ]);
        $category->refresh();

        $this->assertEquals(36,  strlen($category->id));
        $this->assertEquals("Teste", $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);

        $category = Category::create([
            "name" => "Teste",
            "description" => null,
        ]);
        $this->assertNull($category->description);

        $category = Category::create([
            "name" => "Teste",
            "description" => "Test description",
        ]);
        $this->assertEquals('Test description', $category->description);

        $category = Category::create([
            "name" => "Teste",
            "is_active" => false,
        ]);
        $this->assertFalse($category->is_active);

        $category = Category::create([
            "name" => "Teste",
            "is_active" => true,
        ]);
        $this->assertTrue($category->is_active);
    }

    public function testUpdate()
    {
        /** @var Category $category */
        $category = factory(Category::class)
            ->create(["description" => "test description", "is_active" => false]);

        $data = [
            "name" => "Test_name_update",
            "description" => "Test_description_update",
            "is_active" => true
        ];
        $category->update($data);

        foreach ($data as $key=>$value) {
            $this->assertEquals($value, $category->{$key});
        }

    }

    public function testDelete()
    {
        $caegory = factory(Category::class)->create();
        $caegory->delete();
        $this->assertNull(Category::find($caegory->id));

        $caegory->restore();
        $this->assertNotNull(Category::find($caegory->id));
    }

}
