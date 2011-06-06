<?php

class Kutu_Core_Number
{
	public function generateNumber()
	{
		return rand();
	}
	public function generateNumber_()
	{
		$registry = Zend_Registry::getInstance();
		$config = $registry->get('config');
		
		$rand_max = "";
        $random_number = 0;
        $digits = 0;
    
        while($digits < $config->digits->quantity)
        {
            $rand_max .= "9";
            $digits++;
        }
        
        mt_srand((double) microtime() * 1000000); 
        $random_number = mt_rand($config->digits->zero, intval($rand_max));
    
        if($config->digits->string)
        {
            if(strlen(strval($random_number)) < $config->digits->quantity)
            {
                $zeros_quantity = $config->digits->quantity - strlen(strval($random_number));
                $str_zeros = "";
                $digits = 0;
                while($digits < $zeros_quantity)
                {
                    $str_zeros .= "0";
                    $digits++;
                }
                $random_number = $str_zeros . $random_number;
            }
        }
        return $random_number;
	}
}