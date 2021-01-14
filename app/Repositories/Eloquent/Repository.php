<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Redis;

abstract class Repository implements RepositoryInterface
{

    /**
     * @var App
     */
    private $app;

    /**
     * @var
     */
    protected $model;

    /**
     * @param App $app
     * @throws RepositoryException
     */
    public function __construct(App $app)
    {
        $this->cache = Redis::connection();
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract public function model();


    /**
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model)
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
    }

    /**
     * Lấy danh sách các trường
     *
     * @return void
     */
    public function getTableColumns()
    {
        return Schema::getColumnListing((new $this->model())->getTable());
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($c_cache = true)
    {
        if ($c_cache){
            $cache = $this->getCache(CACHE_DB_.$this->model->getTable().'_all');

            if (!empty(unserialize($cache))){
                $data = unserialize($cache);
            } else {
                $data = $this->model->get();
                if (empty($data)) return null;
                $data = $data->toArray();

                $this->saveCache(CACHE_DB_.$this->model->getTable().'_all', $data);
            }

            return (object) $data;
        } else {
            return $this->model->get();
        }
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function paginate($columns = array('*'), $per_page = 10)
    {
        $user_id = Auth::guard()->user()->id;
        return $this->model->where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->paginate($per_page, $columns);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @param string $value
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $value, $attribute = 'id')
    {
        return $this->model->where($attribute, '=', $value)->update($data);
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginateWithTrashed($columns = array('*'), $per_page = 10)
    {
        return $this->model->withTrashed()->paginate($per_page, $columns);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $c_cache = true)
    {
        if ($c_cache){
            $cache = $this->getCache($this->model->getTable().'_find_'.$id);

            if (!empty(unserialize($cache))){
                $data = unserialize($cache);
            } else {
                $data = $this->model->find($id);

                if (empty($data))
                    return null;
                $data = $data->toArray();
                $this->saveCache($this->model->getTable().'_find_'.$id, $data);
            }

            return (object) $data;
        } else {
            return $this->model->find($id);
        }
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value = null, $columns = array('*'))
    {
        $result = $this->model;

        if(gettype($attribute) == "object"){

            foreach($attribute as $key => $value){
                $result = $result->where($key, '=', $value);
            }
            return $result->first($columns)->only(['id']);

        }else{

            return $this->model->where($attribute, '=', $value)->first($columns);
        }

    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function restore($ids)
    {
        return $this->model->withTrashed()->whereIn('id', $ids)->restore();
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function firstOrCreate(array $attributes, array $values = [])
    {
        return $this->model->firstOrCreate($attributes, $values);
    }

    public function where($attribute, $value = null)
    {
        $result = $this->model;

        if(gettype($attribute) == "object"){

            foreach($attribute as $key => $value){
                $result = $result->where($key, '=', $value);
            }
            return $result->get();

        }else{

            return $this->model->where($attribute, '=', $value)->get();
        }
    }

    public function delete()
    {
        return $this->model->delete();
    }

    public function replicate()
    {
        return $this->model->replicate();
    }

    public function push()
    {
        return $this->model->push();
    }

    public function count($conditions)
    {
        return $this->model->where($conditions)->count();
    }

    // get list and paginate
    public function listAndPaginate($columns = array('*'), $per_page = 10) {
        return $this->model->orderBy('id', 'desc')
            // ->paginate($per_page, $columns);
            ->get($columns);
    }

    public function saveCache($key, $data, $t = 60*60*24){
        try{
            $this->cache->set(CACHE_DB_.$key, serialize($data));
            $this->cache->expire(CACHE_DB_.$key, $t);
        } catch (\Throwable $e){}
    }

    public function deleteCache($key){
        $this->cache->del(CACHE_DB_.$key);
    }

    public function getCache($key){
        return $this->cache->get(CACHE_DB_.$key);;
    }
}
