<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\MoRequest\MoRequestRepositoryInterface;
use App\Repositories\RefreshUrl\RefreshUrlRepositoryInterface;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Input;

class RefreshUrlController extends Controller
{
   
    Protected $refreshUrlRepository;

    public function __construct(RefreshUrlRepositoryInterface $refreshUrlRepositoryInterface)
    {
        $this->refreshUrlRepository = $refreshUrlRepositoryInterface;
    }

    public function refreshUrl()
    {
       return $this->refreshUrlRepository->refreshUrl();
    }

    public function flushallRedisdata()
    {
       return $this->refreshUrlRepository->flushallRedisdata();
    }

}

