<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenreCollection;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
    private $rules = [
        "name" => "required|max:255",
        "is_active" => "boolean",
        "category_id" => "required|array|exists:categories,id,deleted_at,NULL",
    ];

    public function store(Request $request)
    {

        $self = $this;
        $validatedData = $this->validate($request, $this->rolesStore());

        $obj = \DB::transaction(function () use($request, $validatedData, $self) {
            /**@var Genre $obj **/
            $obj = $this->model()::create($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $self = $this;
        $validatedData = $this->validate($request, $this->rolesUpdate());

        $obj = \DB::transaction(function () use($request, $id, $validatedData, $self){
            $obj = $this->findOrFail($id);
            $obj->update($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function handleRelations($genre, Request $request)
    {
        $genre->categories()->sync($request->get('category_id'));
    }

    protected function model()
    {
        return Genre::class;
    }

    protected function rolesStore()
    {
        return $this->rules;
    }

    protected function rolesUpdate()
    {
        return $this->rules;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return GenreResource::class;
    }

}
