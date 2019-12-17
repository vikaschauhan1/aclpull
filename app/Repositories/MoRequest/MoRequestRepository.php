<?php

namespace App\Repositories\MoRequest;

use App\Repositories\MoRequest\MoRequestRepositoryInterface ;
use App\Models\MoRequest;
use App\Repositories\RepositoryInterface;

class MoRequestRepository implements MoRequestRepositoryInterface
{
    public $MoRequest;

    function __construct(MoRequest $MoRequest)
    {
        $this->MoRequest = $MoRequest;
    }


    public function getAllMoRequest($request)
    {
        //echo '<pre>'; print_r($request->all());die ;
        $to = $request->to; 
        $from = $request->from;
        $smsc = $request->smsc;
        $text = $request->text;
        $transactionId = 'SMO0101'.'-'.rand(10000000000,100000000000).'-'.rand(10,100);
        $data = array(
            'SHORTCODE' => substr($to,0,5),
            'SUFFIX' => substr($to,6),
            'MESSAGE' => $text,
            'TRANSACTIONID' => $transactionId
        );
       $res =  MoRequest::insert($data);
    }


}
