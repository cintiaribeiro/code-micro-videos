<?php


namespace Tests\Stubs\Models;


use App\Models\Traits\UploadFiles;

class UploadFilesStubs
{
    use UploadFiles;

//    protected $table = 'upload_file_stubs';
//    protected $fillable = ['name', 'file1', 'file2'];
    protected static $fieldFiles = ['file1', 'file2'];
//
//    public static function makeTable()
//    {
//        \Schema::create('upload_file_stubs', function($table)
//        {
//            $table->increments('id');
//            $table->string('name');
//            $table->string('file1');
//            $table->string('file2');
//            $table->timestamps();
//        });
//    }
//
//    public static function dropTable()
//    {
//        \Schema::dropIfExists('upload_file_stubs');
//    }
    protected function uploadDir()
    {
        return "1";
    }
}
