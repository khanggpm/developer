<?php

namespace App\Http\Controllers;

use App\Repositories\TestRepository;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function __construct(TestRepository $testObj)
    {
        $this->testObj = $testObj;
    }

    public function list() {
        $lists = $this->testObj->getList();
        var_dump($lists); die;
    }

    public function add() {
        $data = [
            "id" =>"23123",
            "text" =>"bababa"
        ];
        $model = $this->testObj->create($data);
        dd($model);

    }

}
