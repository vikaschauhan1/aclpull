<?php 
use Illuminate\Support\Facades\Redis;

if (!function_exists('getTransactionId')) {
    /**
     * Returns transaction ID
     * @return string
     */

    function getTransactionId() {
        $milliseconds = round(microtime(true) * 1000);
        $incr = Redis::incr("transIncrCounter");
        if($incr > config('core-properties.maxIncrementVal')){
            $incr = Redis::set("transIncrCounter",1);
        }
        return  config('core-properties.transSMOID').'-'.$milliseconds.'-'.$incr;
    }
}


if (!function_exists('getSmscId')) {
    /**
     * Returns transaction ID
     * @return array
     */
    function getSmscId() {
        
        $milliseconds = round(microtime(true) * 1000);
        return  'SM00101-'.$milliseconds;
    }
}


?>