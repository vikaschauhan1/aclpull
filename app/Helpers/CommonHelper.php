<?php 


if (! function_exists('getTransactionId')) {
    /**
     * Returns transaction ID
     * @return array
     */
    function getTransactionId() {
        //echo 'tes'; die ;
        return  'SM00101-'.uniqid();
    }
}


?>