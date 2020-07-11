<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, Traits\Uuid, Traits\UploadFiles;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
    ];

    protected $dates = ["deleted_at"];

    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration'  => 'integer',
    ];

    public $incrementing = false;

    public static $fieldFiles = ['video_file'];

    public static function create(array $attributes = [])
    {
        $files = self::extractField($attributes);
        try {
            \DB::beginTransaction();
            /** @var Video $obj **/
            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);
            $obj->uploadFiles($files);
            \DB::commit();
            return $obj;
        } catch (\Exception $e) {
            if (isset($obj)) {
                //excluir arquivos de uploads
            }
            \DB::rollback();
            throw $e;
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        try {
            \DB::beginTransaction();
            $salved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if ($salved) {
                //uploads novos arquivos
                //exluir antigos
            }
            return $salved;
            \DB::commit();
        } catch (\Exception $e) {
            //excluir arquivos de upload
            \DB::rollback();
            throw $e;
        }
    }

    public static function handleRelations(Video $video, array $attributes)
    {
        if (isset($attributes['category_id'])) {
            $video->categories()->sync($attributes['category_id']);
        }

        if (isset($attributes['genre_id'])) {
            $video->genres()->sync($attributes['genre_id']);
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    protected function uploadDir()
    {
        return $this->id;
    }
}
