<?php

namespace App\Repositories;

use App\Models\Test;
use App\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\DB;

class TestRepository extends Repository
{

    /**
     * Specify Model class name
     * @return mixed
     */
    public function model()
    {
        return Test::class;
    }

    public function getList() {
        return $this->listAndPaginate();
    }

}
