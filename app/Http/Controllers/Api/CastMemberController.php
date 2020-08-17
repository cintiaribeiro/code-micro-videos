<?php

namespace App\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CastMemberResource;

class CastMemberController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'name' => 'required|max:255',
            'type' => 'required|in:' . implode(',', [
                    CastMember::TYPE_ACTOR,
                    CastMember::TYPE_DIRECTOR
                ])
        ];
    }

    protected function model()
    {
        return CastMember::class;
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
        return CastMemberResource::class;
    }
}
