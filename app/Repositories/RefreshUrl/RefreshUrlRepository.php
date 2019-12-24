<?php

namespace App\Repositories\RefreshUrl;

use App\Repositories\RefreshUrl\RefreshUrlRepositoryInterface ;
use App\Models\MoRequest;
use App\Models\OperatorMapping;
use App\Models\OperatorMaster;
use App\Models\SeriesMaster;
use App\Models\CountryMaster;
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
        // $operatorMapping = OperatorMapping::all()->toArray();
        // if($operatorMapping){
        //     foreach($operatorMapping as $value ){
        //         $value = (object) $value;
        //         Redis::hmset('SMSCMASTER:'.$value->smsc_id, [
        //             'OPERATORID' => $value->operator_id,
        //         ]);
        //     }
        // }
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



    public function flushallRedisdata(){
        Redis::flushall();
        return 'Flush all the data of Redis';
    }

    public function refreshUrl(){
        $this->storeSeriesMasterData();
        $this->storeOperatorMappingData();
        $this->storeOperatorMasterData();
        $this->storeCountryMasterData();
        return 'Refreshed Data'; 
    }

}
