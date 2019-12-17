<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Input;

class AclPullController extends Controller
{
    public function aclpullAPI(){

        $allInput = Input::all();
        $allKeys = Redis::keys('*');
        echo strtotime("now") ;
        print_r($allKeys); // nothing here
        $transactionId = getTransactionId();
       // $redisExpireTime = config('environment.REDIS_EXPIRE_TIME');
        if($allInput['to']){
            $allInput = Input::all();
            Redis::hmset($transactionId,$allInput);
        }
        Redis::expire($transactionId, config('environment.REDIS_EXPIRE_TIME'));
     //  Redis::flushAll();
        $user = Redis::hgetall($transactionId);
        return $transactionId ;
        return $user = Redis::hgetall($transactionId);
    }
}
