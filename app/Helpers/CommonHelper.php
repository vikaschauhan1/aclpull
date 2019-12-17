<?php 


if (! function_exists('getTransactionId')) {
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


?>