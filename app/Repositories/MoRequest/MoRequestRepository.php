<?php

namespace App\Repositories\MoRequest;

use App\Repositories\MoRequest\MoRequestRepositoryInterface ;
use App\Models\MoRequest;
use App\Models\OperatorMapping;
use App\Models\OperatorMaster;
use App\Models\SeriesMaster;
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
        $transactionId = getTransactionId();
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

    /**
     * getSmcIdByMsisdn()
     * @Return a array with SMSCID,OPERATORID,CIRCLEID
     * @param MSISDN Number with country code
     * 
     */

    public function getSmcIdByMsisdn($msisdn=null)
    {
        $countryCode = 91;
        $msisdnNo = 9935788771;
        $minItr = config('constant.MINITERATION');
        for($i = $minItr; $i<= strlen($msisdnNo); $i++){
            $seriesList[] = substr($msisdnNo,0,$i);
        }
        $res = SeriesMaster::select('SMSCID','OPERATORID','CIRCLEID')
                ->where('COUNTRYCODE', '=', $countryCode)
                ->whereIn('SERIES', $seriesList)
                ->get()->toArray();
        if(!empty($res) && count($res) > 0){
            return $res ;
        }
        return false;
    }


}
