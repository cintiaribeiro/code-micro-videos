<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
use App\Rules\hasGenre;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VideoController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'category_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genre_id' => [
                'required',
                'array',
                'exists:genres,id,deleted_at,NULL'
            ]
        ];
    }

    public function store(Request $request)
    {
        $this->addRuleIfGenreHasCategories($request);
        $validatedData = $this->validate($request, $this->rolesStore());
        $self = $this;
        /** @var Video $obj **/
       $obj = \DB::transaction(function () use($request, $validatedData, $self) {
            $obj = $this->model()::create($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
       });
        $obj->refresh();
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $this->addRuleIfGenreHasCategories($request);
        $validatedData = $this->validate($request, $this->rolesUpdate());
        $self = $this;
        $obj = \DB::transaction(function () use($request, $validatedData, $id, $self) {
            $obj = $this->findOrFail($id);
            $obj->update($validatedData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        return $obj;
    }

    protected function addRuleIfGenreHasCategories(Request $request)
    {
        $categoriesId = $request->get('category_id');
        $categoriesId = is_array($categoriesId) ? $categoriesId : [];
        $this->rules['genre_id'][] = new GenresHasCategoriesRule(
            $categoriesId
        );
    }

    protected function handleRelations($video, Request $request)
    {
        $video->categories()->sync($request->get('category_id'));
        $video->genres()->sync($request->get('genre_id'));
    }

    protected function model()
    {
        return Video::class;
    }
    protected function rolesStore()
    {
        return $this->rules;
    }
    protected function rolesUpdate()
    {
        return $this->rules;
    }
}
