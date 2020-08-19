<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BasicCrudController extends Controller
{
    protected $paginateSize = 15;

    protected abstract function model();
    protected abstract function rolesStore();
    protected abstract function rolesUpdate();
    protected abstract function resource();
    protected abstract function resourceCollection();

    public function index()
    {
        $data = !$this->paginateSize ? $this->model()::all() : $this->model()::paginate($this->paginateSize);
        $resourceCollectionClass = $this->resourceCollection();
        $refClass = new \ReflectionClass($this->resourceCollection());
        return $refClass->isSubclassOf(ResourceCollection::class)
            ? new $resourceCollectionClass($data)
            : $resourceCollectionClass::collection($data) ;

    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rolesStore());
        $obj = $this->model()::create($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id)
    {
        $obj = $this->findOrFail($id);
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validate($request, $this->rolesUpdate());
        $obj = $this->findOrFail($id);
        $obj->update($validatedData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function destroy($id)
    {
        $obj = $this->findOrFail($id);
        $obj->delete();
        return response()->noContent();
    }

}
