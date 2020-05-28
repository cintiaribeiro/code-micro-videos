<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
    private $roles = [
        "name" => "required|max:255",
        "is_active" => "boolean",
    ];

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

}
