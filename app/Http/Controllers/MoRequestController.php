<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\MoRequest\MoRequestRepositoryInterface;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Input;

class MoRequestController extends Controller
{
   
    Protected $moRequestRepository;

    public function __construct(MoRequestRepositoryInterface $moRequestRepositoryInterface)
    {
        $this->moRequestRepository = $moRequestRepositoryInterface;
    }

    public function getMoRequest(Request $request)
    {
        $getMoRequest = $this->moRequestRepository->getAllMoRequest($request);
        return $getMoRequest;
      
    }

    public function aclpullAPI(){

        $allInput = Input::all();
        $transactionId = getTransactionId();
        if($allInput['to']){
            $allInput = Input::all();
            Redis::hmset($transactionId,$allInput);
        }
        Redis::expire($transactionId, config('environment.REDIS_EXPIRE_TIME'));
        $user = Redis::hgetall($transactionId);
        return $transactionId ;
        return $user = Redis::hgetall($transactionId);
    }



}

