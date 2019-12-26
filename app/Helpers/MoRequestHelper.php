<?php

use App\Repositories\RepositoryInterface;


if (!function_exists('getValidNumbers')) {
    /**
     * @param string PhoneNumbers from manual recipient type
     * @return  array: list of PhoneNumbers
     *
     */

    function getValidNumbers($phone, $type = 'domestic')
    {
        $returnNumbers = [];
        $maxLength = config('constant.MAXLENGTH');
        $removeSubstring = config('constant.REMOVESUBSTRING');

        if(strlen($phone) == $maxLength  && substr(trim($phone), 0, 4) == $removeSubstring)
        {
            $phone = substr(trim($phone), 2, 14);
        }

        $phoneRequirements = config('phoneRegex.phone');
       
        $validNumber = isValid($phoneRequirements[$type], trim($phone));
            $minLength = config('constant.MINLENGTH');
            if(!empty($validNumber) && strlen($validNumber[0]) == $minLength){
                $addSubstring = config('constant.ADDSUBSTRING');
                $returnNumbers[] = $addSubstring.$validNumber[0];
            }
            elseif(!empty($validNumber)){
                return $validNumber;
                // print_r($validNumber);die;
                // $returnNumbers[] = $validNumber[0];
            }
            else{
                return false;
            }
            // print_r($returnNumbers);die;
            // $validMsisdn[] = substr(trim($returnNumbers[0]), 0, 2);
            // $validMsisdn[] = substr(trim($returnNumbers[0]), 2, 10);   
        // return $returnNumbers;

    }


    function isValid($regexValidation, $phoneNumber) {

        if($regexValidation){
            preg_match_all($regexValidation, $phoneNumber, $matches);
            return $matches[0];
        }

        return [];
    }
}

?>