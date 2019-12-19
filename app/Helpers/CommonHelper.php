<?php 


if (!function_exists('getTransactionId')) {
    /**
     * Returns transaction ID
     * @return array
     */
    function getTransactionId() {
        //echo 'tes'; die ;
        $milliseconds = round(microtime(true) * 1000);
        return  'SM00101-'.$milliseconds;
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