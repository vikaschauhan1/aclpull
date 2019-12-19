<?php

namespace App\Repositories\MoRequest;

use App\Repositories\MoRequest\MoRequestRepositoryInterface ;
use App\Models\MoRequest;
use App\Models\SeriesMaster ;
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
        $smsc = $request->smsc;
        $text = $request->text;
        $transactionId = getTransactionId();
        $data = array(
            'SHORTCODE' => substr($to,0,5),
            'SUFFIX' => substr($to,6),
            'MESSAGE' => $text,
            'TRANSACTIONID' => $transactionId
        );
       //$res =  MoRequest::insert($data);
       return $transactionId ;
    }

    /**
     * @Return a array with SMSCID,OPERATORID,CIRCLEID
     * @param MSISDN Number with country code
     * 
     */

    public function getSmcIdByMsisdn($msisdn=null)
    {
        $countryCode = 91;
        $msisdnNo = 9935788771;
        for($i = 3; $i<= strlen($msisdnNo); $i++){
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
