<?php


namespace App\Models\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use phpDocumentor\Reflection\Types\Self_;

trait UploadFiles
{
    public $oldFiles = [];
    protected abstract function uploadDir();

    public static function bootUploadFiles()
    {
        static::updating(function(Model $model){
            $fieldsUpdated = array_keys($model->getDirty());
            $fileUpdated = array_intersect($fieldsUpdated, Self::$fieldFiles);
            $fileFiltered = Arr::where($fileUpdated, function($fileField) use($model) {
               return $model->getOriginal($fileField);
            });

            $model->oldFiles = array_map(function($fileField) use($model){
                return $model->getOriginal($fileField);
            },$fileFiltered);
        });
    }

    /**
     * @param UploadedFile[] $files
     */
    public function uploadFiles(array $files)
    {
        foreach ($files as $file){
            $this->uploadFile($file);
        }
    }

    public function uploadFile(UploadedFile $file)
    {
        $file->store($this->uploadDir());
    }

    public function deleteOldFiles()
    {
        $this->deleteFiles($this->oldFiles);
    }

    public function deleteFiles(array $files)
    {
        foreach ($files as $file){
            $this->deleteFile($file);
        }
    }

    /**
     * @param string|UploadedFile $file
     */
    public function deleteFile($file)
    {
        $fileName = $file instanceof UploadedFile ? $file->hashName() : $file;
        \Storage::delete("{$this->uploadDir()}/{$fileName}");
    }

    public static function extractField (array &$attributes = [])
    {
        $files = [];
        foreach (self::$fieldFiles as $file){
            if(isset($attributes[$file]) && $attributes[$file] instanceof UploadedFile){
                $files[] = $attributes[$file];
                $attributes[$file] = $attributes[$file]->hashName();
            }
        }
        return $files;
    }
}
