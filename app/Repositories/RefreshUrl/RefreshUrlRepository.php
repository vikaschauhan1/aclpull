<?php

namespace App\Repositories\RefreshUrl;

use App\Models\MoRequest;
use App\Models\SmscMaster;
use App\Models\SeriesMaster;
use App\Models\CountryMaster;
use App\Models\OperatorMaster;
use App\Models\OperatorMapping;
use App\Models\CircleMaster;
use Illuminate\Support\Facades\Redis;
use App\Repositories\RepositoryInterface;
use App\Repositories\RefreshUrl\RefreshUrlRepositoryInterface ;

class RefreshUrlRepository implements RefreshUrlRepositoryInterface
{
    public $seriesMaster;

    function __construct(SeriesMaster $seriesMaster)
    {
        $this->seriesMaster = $seriesMaster;
    }


    public function storeSeriesMasterData()
    {
        $seriesMaster = SeriesMaster::all()->toArray();
        if($seriesMaster){
            foreach($seriesMaster as $value){
                $value = (object) $value;
                Redis::hmset('SERIESMASTER:'. $value->countrycode.$value->series, [
                    'circleid'    => $value->circleid,
                    'smscid'      => $value->smscid,
                    'operatorid'  => $value->operatorid,
                    'series'      => $value->series,
                ]);
            }
        }
        return 'Refreshed Data';
    }

    public function storeOperatorMappingData()
    {
        $operatorMapping = OperatorMapping::all()->toArray();
        if($operatorMapping){
            foreach($operatorMapping as $value ){
                $value = (object) $value;
                Redis::hmset('OPERATORMAPPING:'.$value->smsc_id, [
                    'operator_id' => $value->operator_id,
                ]);
            }
        }
       return 'Refreshed Data';
    }

    public function storeOperatorMasterData()
    {
        $operatorMaster = OperatorMaster::all()->toArray();
        if($operatorMaster){
            foreach($operatorMaster as $value ){
                $value = (object) $value;
                Redis::hmset('OPERATORMASTER:'.$value->operatorid, [
                    'operatorname' => $value->operatorname,
                    'operatortype' => $value->operatortype
                ]);
            }
        }
       return 'Refreshed Data';
    }

    public function storeSmscMasterData()
    {
        $operatorMapping = SmscMaster::all()->toArray();
        if($operatorMapping){
            foreach($operatorMapping as $value ){
                $value = (object) $value;
                Redis::hmset('SMSCMASTER:'.$value->smscid, [
                    'SHORTCODE' => $value->shortcode,
                ]);
            }
        }
       return 'Refreshed Data';
    }

    public function storeCountryMasterData()
    {
        $countryMaster = CountryMaster::all()->toArray();

        if($countryMaster){
            foreach($countryMaster as $value ){
                $value = (object) $value;
                Redis::hmset('COUNTRYMASTER:'.$value->countrycode, [
                    'countryname' => $value->countryname,
                    'countrycodelength' => $value->countrycodelength,
                ]);
            }
        }
       return 'Refreshed Data';
    }

    public function storeCircleMasterData()
    {
        $circleMaster = CircleMaster::all()->toArray();
        if($circleMaster){
            foreach($circleMaster as $value ){
                $value = (object) $value;
                Redis::hmset('CIRCLEMASTER:'.$value->circleid, [
                    'circlename' => $value->circlename,
                ]);
            }
        }
       return 'Refreshed Data';
    }

    public function flushallRedisdata(){
        Redis::flushall();
        return 'Flush all the data of Redis';
    }

    public function refreshUrl(){
        $this->storeSeriesMasterData();
        $this->storeOperatorMappingData();
        $this->storeOperatorMasterData();
        $this->storeCountryMasterData();
        $this->storeSmscMasterData();
        $this->storeCircleMasterData();

        return 'Refreshed Data'; 
    }

}
