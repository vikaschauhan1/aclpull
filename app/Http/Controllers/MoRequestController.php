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
        return $getMoRequest = $this->moRequestRepository->getAllMoRequest($request);
    }

}

