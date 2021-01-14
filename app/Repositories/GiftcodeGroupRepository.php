<?php

namespace App\Repositories;

use App\Models\GiftcodeGroup;
use App\Repositories\Eloquent\Repository;
use Illuminate\Support\Facades\DB;
use App\Services\Util;

class GiftcodeGroupRepository extends Repository
{
    /**getGiftcodeGroup
     * Specify Model class name
     * @return mixed
     */
    public function model()
    {
        // TODO: Implement model() method.
        return GiftcodeGroup::class;
    }

    public function getById($id){
        return $this->find($id);
    }

    public function getAll($list_client = null){
        if (!empty($list_client)){
            $lists = DB::table('giftcode_group as a')
                ->join('client as b','a.client_id','=','b.id')
                ->whereIn('a.client_id',$list_client)
                ->select('a.*', 'b.name as app')
                ->orderBy('id', 'desc');

            $data = $lists->get()->toArray();
        } else {
            $cache = $this->getCache('GiftcodeGroup_getAll');
            if (!empty($cache)){
                $data = unserialize($cache);
            } else {
                $lists = DB::table('giftcode_group as a')
                    ->join('client as b','a.client_id','=','b.id')
                    ->select('a.*', 'b.name as app')->orderBy('id', 'desc')->get();
                if (empty($lists)) return null;
                $data = $lists->toArray();

                $this->saveCache('GiftcodeGroup_getAll', $data);
            }
        }
        return (object) $data;
    }

    public function saveGroup($id, $data){
        try{
            if (!empty($id) && $id > 0){
                $result = $this->update($data, $id);
            } else {
                $result = $this->create($data);
            }
            if ($result){
                return ['status'=>true,'message'=>$result];
            } else {
                return ['status'=>false,'message'=>$result];
            }
        } catch (Exception $e){
            return ['status'=>false,'message'=>$e->getMessage()];
        }

        $util = new Util();
        $this->deleteCache('GiftcodeGroup_getAll');
        $util->delCachePrefix('*group_find*');
    }

    public function getGiftcodeGroup($params){
        $lists = DB::table('giftcode_group as a')
            ->join('client as b','a.client_id','=','b.id')
            ->where('b.app_id','=',$params['app_id'])
            ->where('a.status','=',1)
            ->select('a.*', 'b.name as app', DB::raw('(SELECT COUNT(id) FROM giftcode WHERE user_id IS NULL AND group_id = a.id) AS count_gc'))
            ->orderBy('id', 'desc');

        if (!empty($params['type']))
            $lists->where('a.type','=',$params['type']);

        return $lists->get();
    }

    public function checkUserTest($params){
        $data = DB::table('user_test')->where('user_test.list_client','like','%'.$params['client_id'].'%');

        if (!empty($params['soha_id'])){
            $data->join('users as b','b.id','=','user_test.user_id')
            ->where('b.soha_id',$params['soha_id']);
        } else if(!empty($params['user_id'])){
            $data->where('user_test.user_id',$params['soha_id']);
        } else {
            return false;
        }

        if (!empty($data->first())) return true;
        else return false;
    }
}
