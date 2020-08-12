<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

// classe especifica - vendor/bin/phpunit tests/unit/CategoryTest.php
// metodo especifico em um arquivo  - vendor/bin/phpunit --filter testIfUseTraits tests/unit/CategoryTest.php
// metodo especifico em em classe  - vendor/bin/phpunit --filter testIfUseTraits CategoryTest::testIfUseTraits
class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
