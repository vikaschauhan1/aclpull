<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;


class AclPullController extends Controller
{
    public function aclpullAPI(){
        $id = 1;
        Redis::set('name', 'vinay');
        Redis::set('test', 'hello');
       return Redis::dbSize();
        return $user = Redis::get('test');
    //sudo pecl7.3-sp install redis
        return 'hello';

    }

    public function aclpullAPI1(){
       return 'test22';

    }
}
