<?php

namespace App\Repositories\Contracts;

interface RepositoryInterface
{
    public function getTableColumns();

    public function all();

    public function paginate($columns = array('*'));

    public function create(array $data);

    public function update(array $data, $id);

    public function paginateWithTrashed($columns = array('*'));

    public function find($id);

    public function findBy($attribute, $value);

    public function restore($ids);

    public function updateOrCreate(array $attributes, array $values = []);

    public function firstOrCreate(array $attributes, array $values = []);

    public function where($attribute, $value);

    public function delete();

    public function replicate();

    public function push();

    public function count(array $conditions);
}
