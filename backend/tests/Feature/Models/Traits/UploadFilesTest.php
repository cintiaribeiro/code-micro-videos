<?php

namespace Tests\Feature\Models\Traits;

use Tests\Stubs\Models\UploadFilesStubs;
use Tests\TestCase;

class UploadFilesTest extends TestCase
{
    private $obj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new UploadFilesStubs();

        UploadFilesStubs::dropTable();
        UploadFilesStubs::makeTable();
    }

    public function testMakeOldFieldsOnSaving()
    {
        $this->obj->fill([
            'name'=> 'test',
            'file1' => 'teste.mp4',
            'file2' => 'teste2.mp4',
        ]);
        $this->obj->save();

        $this->assertCount(0, $this->obj->oldFiles);

        $this->obj->update([
            'name'=> 'test_teste',
            'file2' => 'teste3.mp4',
        ]);
        $this->assertEqualsCanonicalizing(['teste2.mp4'], $this->obj->oldFiles);
    }

    public function testMakeOldFilesNullOnSaving()
    {
        $this->obj->fill([
            'name'=> 'test',
        ]);
        $this->obj->save();
        $this->obj->update([
            'name'=> 'test_teste',
            'file2' => 'teste3.mp4',
        ]);
        $this->assertEqualsCanonicalizing([], $this->obj->oldFiles);
    }
}
