<?php  
namespace App\Extensions;

class Helper {
	/**
	* Truncate a string after an amount of characters, but keep complete words
	*
	*
	*/
    public function truncate($string, $length = 300, $append = "..."){
        $string = trim($string);

        if(strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0]. $append;
        }

        return $string;

    }

    /**
    * Get the matching key of a value in an associative array
    *
    */
    public function getIndex($name, $array){
	    foreach($array as $key => $value){
	        if(is_array($value) && $value['name'] == $name)
	              return $key;
	    }
    	return null;
    }
}