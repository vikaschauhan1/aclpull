<?php

namespace App\Repositories\MoRequest;

use App\Repositories\MoRequest\MoRequestRepositoryInterface ;
use App\Models\MoRequest;
use App\Models\OperatorMapping;
use App\Models\OperatorMaster;
use App\Models\SeriesMaster;
use App\Models\SmscMaster;
use App\Models\SuffixMaster;
use App\Models\CircleMaster;
use App\Repositories\RepositoryInterface;
use Illuminate\Support\Facades\Redis;

class MoRequestRepository implements MoRequestRepositoryInterface
{
    public $MoRequest;

    function __construct(MoRequest $MoRequest) 
    {
        $this->MoRequest = $MoRequest;
    }


    public function getAllMoRequest($request)
    {
       // print_r($this->MoRequest);die ;
        $to = $request->to; 
        $from = $request->from;
        $smsc = $request->smsc;
        $text = $request->text;
 $abc = $this->getApplicationId($smscId = '1111', $to);

        if(empty($request->TXNID) || !isset($request->TXNID)){
            $transactionId = getTransactionId();
        }else{
            $transactionId = $request->TXNID ;
        }

        $getMsisdn = getValidNumbers($from, 'domestic');
        if($getMsisdn && (!empty($getMsisdn))){
           $smscId = $request->smsc;
            // if(empty($smscId)){
            //   return $getSmcIdByMsisdn = $this->getSmcIdByMsisdn($getMsisdn);
            // }else{
            //    return $getOperatorId = $this->getOperatorBySmsc($smscId);
            // }

           return $this->getApplicationId($request);
            $getOperatorId = 11;
           // print_r($getMsisdn);die ;
            // $getOperatorId = $this->getOperatorBySmsc($request->smsc);
            // print_r($getOperatorId);die;
            return $getOperatorName = $this->getOperatorNameById($getOperatorId);
             $data = array(
                 'SHORTCODE' => substr($to,0,5),
                 'SUFFIX' => substr($to,6),
                 'MESSAGE' => $text,
                 'TRANSACTIONID' => $transactionId
             );
            // MoRequest::insert($data);
                return $transactionId;
        }else{
                return 'Invalid Number' ;
        }

      
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
        $key = 'OPERATORMASTER:'.$operatorId;
        $redisData = Redis::hgetall($key);
        if(!empty($redisData) && is_array($redisData)){
          //  return $redisData ;
        }
        
        if(empty($redisData)){
            $operatorData = OperatorMaster::select('OPERATORNAME','OPERATORTYPE')
            ->where('OPERATORID', $operatorId)
            ->first()->toArray();
            if(!empty($operatorData) && count($operatorData) > 0){
                return $operatorData ;
            }
            return false;
        }
    }

    public function getOperatorByMsisdn(){
        
    }

    /**
     * getSmcIdByMsisdn()
     * @Return a array with SMSCID,OPERATORID,CIRCLEID
     * @param MSISDN Number with country code
     * 
     */

    public function getSmcIdByMsisdn($msisdn)
    { 
        $countryCode = $msisdn[0];
        $msisdnNo = $msisdn[1];
        $minItr = config('constant.MINITERATION');
        for($i = $minItr; $i<= strlen($msisdnNo); $i++){
            $seriesList[] = substr($msisdnNo,0,$i);
            $key = 'SERIESMASTER:' .$countryCode.substr($msisdnNo,0,$i);
            $stored = Redis::hgetall($key);
            if(!empty($stored) && is_array($stored)){
               return $stored ;
            }
        }

        if(empty($stored)){
            $res = SeriesMaster::select('SMSCID','OPERATORID','CIRCLEID')
            ->where('COUNTRYCODE',$countryCode)
            ->whereIn('SERIES', $seriesList)
            ->first()->toArray();

            if(!empty($res) && count($res) > 0){
                return $res ;
            }
            return false;
        }
    }

    public function getApplicationId($smscId, $to)
    {
        if($smscId){
           $shortCode = $this->getShortCodeViaSmsc($smscId);
        }
        // else{
        //     $smscId = $this->getSmcIdByMsisdn($abc);
        //     $shortCode = $this->getShortCodeViaSmsc($smscId);
        // }
        if(!empty($shortCode)){
            $suffixShortCode = $to;
            $getSuffix = substr($suffixShortCode, strlen($shortCode) , strlen($suffixShortCode));
        }

        $getApplicationId = SuffixMaster::select('APPLICATIONID')
            ->where('SHORTCODE', $shortCode)->where('SUFFIX', $getSuffix)->first();
            
    }

    public function getShortCodeViaSmsc($smscId) 
    {
        $shortCode = SmscMaster::select('SHORTCODE')
               ->where('SMSCID', $smscId)
               ->first()
               ->toArray();
        return $shortCode['shortcode'];
    }

    public function getCircleById($circleId)
    {
        $circleName = CircleMaster::select('CIRCLENAME')->where('CIRCLEID', $circleId)->first();
        return $circleName;
                         
    }

}
