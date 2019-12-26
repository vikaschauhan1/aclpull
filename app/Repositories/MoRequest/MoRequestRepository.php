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

        $from = $request->from;
        $to = $request->to;
        if(empty($request->TXNID) || !isset($request->TXNID)){
            $transactionId = getTransactionId();
        }else{
            $transactionId = $request->TXNID ;
        }

        $getMsisdn = getValidNumbers($from, 'domestic');
        if(isset($getMsisdn) && (!empty($getMsisdn))){
            $this->setRequestInRedis($transactionId,$request);
            $requestRedisKey = 'REQ:'.$transactionId;
            return $this->saveMoRequest($request, $transactionId, $getMsisdn);
        }else{
            return 'Invalid Number' ;
        }
      
    }


    public function saveMoRequest($request,$transactionId,$getMsisdn){

            $getSmcIdOperatorDetail = $this->getSmcIdByMsisdn($getMsisdn);
            if(empty($smscId)){
            $getOperatorId = $getSmcIdOperatorDetail->operatorid;
            $smscId = $getSmcIdOperatorDetail->smscid;
            }else{
                $getOperatorId = $this->getOperatorIdBySmsc($smscId);
            }
            
            $to = $request->to ;
            $text = $request->text ;
            $circleId = $getSmcIdOperatorDetail->circleid;
            $circleName = $this->getCircleById($circleId);
            $getOperatorData = $this->getOperatorNameById($getOperatorId);
            $operatorName = $getOperatorData->operatorname;

            $response = $this->isBlockOperatorShortCode($operatorName,$to);
            if($response){

                $networkType = $getOperatorData->operatortype;
                $countryCode = substr(trim($getMsisdn[0]), 0, 2);
                $getApplicationData =   $this->getApplicationId($smscId, $to, $getMsisdn);
                $applicationId = $getApplicationData->applicationid ;
                $suffix = $getApplicationData->suffix ;
                $shortCode = $getApplicationData->shortcode ;
                $message = 'SUCCESSFULLY FORWORDED TO APPLICATION';
                $milliSeconds = round(microtime(true) * config('core-properties.MICROSECOND'));
                $data = array(
                    'REQRECEIVEDTIME' => $milliSeconds,
                    'TRANSACTIONID' => $transactionId,
                // 'ORIGNATOR' => $getMsisdn,
                    'NETWORKTYPE' => $networkType,
                    'BEARER' => config('core-properties.bearer'),
                    'CIRCLE' => $circleName,
                    'OPERATOR' => $operatorName,
                    'DESTINATION' => $to,
                    'COUNTRYCODE' => $countryCode,
                    'MSISDNSERIES' => '',
                    'SHORTCODE' => $shortCode,
                    'SUFFIX' => $suffix,
                    'MESSAGE' => $text,
                    'MESSAGESTATUS' => 'Y',
                    'MESSAGESTATUSDESC' => $message,
                    'APPLICATIONID' => $applicationId,
                    'DELIVERYTIME' => '',
             );
            MoRequest::insert($data);
            return $transactionId;
        }else{
            return 'Operator Blocked';
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
        $countryCode = substr(trim($msisdn), 0, 2);
        $msisdnNo =  substr(trim($msisdn), 2, 10); 
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

    public function getApplicationId($smscId, $to, $getMsisdn)
    {
        if(!$smscId){
           $smscId = $this->getSmcIdByMsisdn($getMsisdn);
        }
       
        $shortCode = $this->getShortCodeViaSmsc($smscId);

        if(!empty($shortCode)){
            $suffixShortCode = $to;
            $getSuffix = substr($suffixShortCode, strlen($shortCode) , strlen($suffixShortCode));
        }

        $getApplicationId = SuffixMaster::select('APPLICATIONID','SUFFIX','SHORTCODE')
            ->where('SHORTCODE', $shortCode)->where('SUFFIX', $getSuffix)->first();
        if($getApplicationId){
            return $getApplicationId ;
        }
        return false;
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
        $circleName = CircleMaster::select('CIRCLENAME')->where('CIRCLEID', $circleId)->first()->toArray();
        return $circleName['circlename'];
                         
    }

    public function isBlockOperatorShortCode($operatorName,$to){
        $operatorKey = $operatorName.'-'.$to;
        $blockedOperatorList = config('operator-block-list');
        foreach($blockedOperatorList as $key => $val){
            if($operatorKey == $key && $val == 1){
               return false;
            }
        }
        return true ;
    }

}
