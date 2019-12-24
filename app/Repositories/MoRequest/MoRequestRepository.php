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

    /**
     * @params @Request array with to,from,smscid
     * @return Void
     * 
     */
    public function getAllMoRequest($request)
    {
        $to = $request->to; 
        $from = $request->from;
        $smscId = $request->smsc;
        $text = $request->text;
        $appId = $this->getApplicationId($smscId = '1111', $to);
        if(empty($request->TXNID) || !isset($request->TXNID)){
            $transactionId = getTransactionId();
        }else{
            $transactionId = $request->TXNID ;
        }

        $getMsisdn = getValidNumbers($from, 'domestic');
        if(isset($getMsisdn) && (!empty($getMsisdn)) && is_array($getMsisdn)){
            
            $this->setRequestInRedis($transactionId,$request);
            $requestRedisKey = 'REQ:'.$transactionId;
            if(empty($smscId)){
               $getSmcIdOperatorDetail = $this->getSmcIdByMsisdn($getMsisdn);
               $getOperatorId = $getSmcIdOperatorDetail->operatorid;
               $smscId = $getSmcIdOperatorDetail->smscid;
               $circleId = $getSmcIdOperatorDetail->circleid;
            }else{
                $getOperatorId = $this->getOperatorIdBySmsc($smscId);
                $getSmcIdOperatorDetail = $this->getSmcIdByMsisdn($getMsisdn);
               // $circleId = $this->getcircleId($requestRedisKey,$getMsisdn);
            }

            $getOperatorData = $this->getOperatorNameById($getOperatorId);
            $operatorName = $getOperatorData->operatorname;
            $networkType = $getOperatorData->operatortype;
            $circleName = '';
            $milliseconds = round(microtime(true) * 1000);
            $data = array(
                'REQRECEIVEDTIME' => $milliseconds,
                'TRANSACTIONID' => $transactionId,
                'ORIGNATOR' => '',
                'NETWORKTYPE' => $networkType,
                'BEARER' => config('core-properties.bearer'),
                'CIRCLE' => '',
                'OPERATOR' => $operatorName,
                'DESTINATION' => '',
                'COUNTRYCODE' => '',
                'MSISDNSERIES' => '',
                'SHORTCODE' => '',
                'SUFFIX' => substr($to,6),
                'MESSAGE' => $text,
                'MESSAGESTATUS' => '',
                'MESSAGESTATUSDESC' => '',
                'APPLICATIONID' => '',
                'DELIVERYTIME' => '',
             );
             MoRequest::insert($data);
                return $transactionId;
        }else{
                return 'Invalid Number' ;
        }
      
    }


    public function getOperatorIdBySmsc($smscId)
    {
        $key = 'OPERATORMAPPING:'.$smscId;
        $redisData = Redis::hgetall($key);
        if(!empty($redisData) && is_array($redisData)){
            return $redisData['operator_id'] ;
        }

        if(empty($redisData)){
            $operatorId = OperatorMapping::select('OPERATOR_ID')
            ->where('SMSC_ID', $smscId)
            ->first()->toArray();
            if(!empty($operatorId) && count($operatorId) > 0){
                return $operatorId['operator_id'] ;
            }
            return false;
        }  
    }

    public function getOperatorNameById($operatorId) 
    {
        $key = 'OPERATORMASTER:'.$operatorId;
        $redisData = Redis::hgetall($key);
        if(!empty($redisData) && is_array($redisData)){
           return $redisData = (object) $redisData;
        }
        
        if(!empty($redisData)){
            $operatorData = OperatorMaster::select('OPERATORNAME','OPERATORTYPE')
            ->where('OPERATORID', $operatorId)
            ->first()->toArray();
            if(!empty($operatorData) && count($operatorData) > 0){
                $operatorData = (object) $operatorData;
                return $operatorData ;
            }
            return false;
        }
    }

    /**
     * getSmcIdByMsisdn()
     * @Return a array with SMSCID,OPERATORID,CIRCLEID
     * @param MSISDN @Array, Number with country code
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
                return $stored = (object) $stored;
            }
        }

        if(empty($stored)){
            $seriesData = SeriesMaster::select('SMSCID','OPERATORID','CIRCLEID','SERIES')
            ->where('COUNTRYCODE',$countryCode)
            ->whereIn('SERIES', $seriesList)
            ->first()->toArray();

            if(!empty($seriesData) && count($seriesData) > 0){
                return $seriesData = (object) $seriesData;
            }
            return false;
        }
    }

    /**
    * Set in redis all request parameter 
    * @param TransId and Request Array
    * @Return Void
    */

    public function setRequestInRedis($transactionId,$request){
        Redis::hmset('REQ:'.$transactionId, [
            'TO' => $request->to,
            'FROM' => $request->from,
            'SMSC' => $request->smsc,
            'TEXT' => $request->text,
            'UHID' => $request->uhid,
        ]);
    }

    /**
     * Pending 
     * Some query
     * 
     */
    public function getcircleId($requestRedisKey,$getMsisdn){
        $redisData = Redis::hgetall($requestRedisKey);
        $smscId = $redisData['SMSC'] ;
        return true;
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
