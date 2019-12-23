<?php

namespace App\Repositories\RefreshUrl;

use App\Repositories\RefreshUrl\RefreshUrlRepositoryInterface ;
use App\Models\MoRequest;
use App\Models\OperatorMapping;
use App\Models\OperatorMaster;
use App\Models\SeriesMaster;
use App\Repositories\RepositoryInterface;
use Illuminate\Support\Facades\Redis;

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
                    'CIRCLEID'    => $value->circleid,
                    'SMSCID'      => $value->smscid,
                    'OPERATORID'  => $value->operatorid,
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
                    'OPERATORID' => $value->operator_id,
                ]);
            }
        }
       return 'Refreshed Data';
    }

    public function storeOperatorMasterData()
    {
        $operatorMaster = OperatorMaster::all()->toArray();
        //echo '<pre>'; print_r($operatorMaster); die ;
        if($operatorMaster){
            foreach($operatorMaster as $value ){
                $value = (object) $value;
                Redis::hmset('OPERATORMASTER:'.$value->operatorid, [
                    'OPERATORNAME' => $value->operatorname,
                    'OPERATORTYPE' => $value->operatortype
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
        return 'Refreshed Data'; 
    }

}
