<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\DB;
use App\Services\Util;

class UserRepository extends Repository
{

    /**
     * Specify Model class name
     * @return mixed
     */
    public function model()
    {
        // TODO: Implement model() method.
        return User::class;
    }

    public function getById($id){
        $cache = $this->getCache(GET_BY_ID_USER_.$id);
        if (!empty($cache)){
            $user = unserialize($cache);
        } else {
            $user = $this->findBy('id', $id)->toArray();
            if (empty($user)) return null;
            $this->saveCache(GET_BY_ID_USER_.$id, $user);
        }

        return (object) $user;
    }

    public function getBySoha_Id($soha_id){
        $cache = $this->getCache(GET_BY_SOHA_ID_USER_.$soha_id);
        if (!empty($cache)){
            $user = unserialize($cache);
        } else {
            $user = $this->findBy('soha_id', $soha_id);
            if (empty($user)) return null;
            $user = $user->toArray();
            $this->saveCache(GET_BY_SOHA_ID_USER_.$soha_id, $user);
        }

        return (object) $user;
    }

    public function getList() {
        return $this->listAndPaginate();
    }

    public function getAll() {
        return $this->all();
    }

    public function getGiftcodeUser($soha_id){
        $cache = $this->getCache(GET_GiftcodeUser_USER_.$soha_id);
        if (!empty($cache)){
            $list = unserialize($cache);
        } else {
            $giftcodes = DB::table('giftcode as a')
                ->join('users as b','a.user_id','=','b.id')
                ->join('giftcode_group as c','c.id','=','a.group_id')
                ->leftJoin('client as d','c.client_id','=','d.id')
                ->where('b.soha_id','=',$soha_id)
                ->select('a.code','a.buy_time','c.name','c.desc','c.point','c.image','a.status','c.type','d.name AS game_name')
                ->orderBy('a.buy_time','desc')->get();
            if (empty($giftcodes)) return null;
            $list = $giftcodes->toArray();

            $this->saveCache(GET_GiftcodeUser_USER_.$soha_id, $list);
        }

        return (object) $list;
    }

    public function searchUser($s){
        $data = $this->model->where('name','like','%'.$s.'%')
            ->orWhere('soha_id','like','%'.$s.'%')
            ->orWhere('puid','like','%'.$s.'%')
            ->where('status',1)->limit(10000);
        return $data->get()->toArray();
    }

    public function getListIn($in){
        $data = $this->model
                ->whereIn('id',explode(',',$in));

        return $data->get()->toArray();
    }

    public function reportPoint(){
        $util = new Util();
        $list_user_test = $util->getAllUserTest();
        $data = $this->model->select(DB::raw('SUM(`point`) AS total_point'), DB::raw('SUM(lock_point) AS total_lock_point'));
//                ->whereNotIn('soha_id', $list_user_test);

        return $data->first()->toArray();
    }
}
