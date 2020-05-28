<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Tests\Stubs\Models\CategoryStub;

class CategoryControllerStub extends BasicCrudController
{
    protected function model()
    {
        return CategoryStub::class;
    }

    protected function rolesStore()
    {
        return  [
            "name" => "required|max:255",
            "description" => "nullable"
        ];
    }

    protected function rolesUpdate()
    {
        return  [
            "name" => "required|max:255",
            "description" => "nullable"
        ];
    }
}
