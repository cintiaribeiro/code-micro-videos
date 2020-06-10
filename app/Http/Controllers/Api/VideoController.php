<?php

namespace App\Http\Controllers\Api;

use App\Video;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VideoController extends BasicCrudController
{
    protected function model()
    {
        return Video::class();
    }
    protected function rolesStore()
    {

    }
    protected function rolesUpdate()
    {

    }
}
