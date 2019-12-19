<?php

namespace App\Repositories\MoRequest;

use App\Repositories\MoRequest\MoRequestRepositoryInterface ;
use App\Models\MoRequest;
use App\Models\OperatorMapping;
use App\Models\OperatorMaster;
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
        $to = $request->to; 
        $from = $request->from;
        $getMsisdn = getValidNumbers($from, 'domestic');
        $getOperatorId = $this->getOperatorBySmsc($request->smsc);
        $getOperatorName = $this->getOperatorNameById($getOperatorId);
       
       
        $smsc = $request->smsc;
        $text = $request->text;
        $transactionId = 'SMO0101'.'-'.rand(10000000000,100000000000).'-'.rand(10,100);
        $data = array(
            'SHORTCODE' => substr($to,0,5),
            'SUFFIX' => substr($to,6),
            'MESSAGE' => $text,
            'TRANSACTIONID' => $transactionId
        );
       // MoRequest::insert($data);

        return $transactionId;
    }


    public function getOperatorBySmsc($smscId)
    {
        $operatorId = OperatorMapping::select('OPERATOR_ID')
            ->where('SMSC_ID', $smscId)
            ->first()
            ->toArray();
        return $operatorId['operator_id'];    
    }

    public function getOperatorNameById($operatorId) 
    {
        $operatorName = OperatorMaster::select('OPERATORNAME','OPERATORTYPE')
            ->where('OPERATORID', $operatorId)
           ->get();
        return $operatorName;
    }

    public function getOperatorByMsisdn(){
        
    }


}
