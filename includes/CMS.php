<?php

/* 
 * Detect CMS or try it
 */

class CMS {
    var $name;
    var $version;
    var $info;
    var $content;
    
    public function __construct() {
         $this->name = '';
         $this->version = '';
	 $this->info = array();
	 

     } 
     
     
    function get_generator($tags,$content) {
	$res = array();
	if (isset($tags)) {
	    $genatorstring = $tags['generator'];
	    if ((isset($genatorstring)) && (!is_array($genatorstring))) {
		$res['name'] = $genatorstring;
		$this->name = $genatorstring;
		preg_match('/([\w\s]+)\s+([\d\.]+)/i', $genatorstring, $output_array);
		if (isset($output_array)) {
		    $res['name'] = $output_array[1];
		    $this->name = $output_array[1];
		    if (isset($output_array[2])) {
			$res['version'] = $output_array[2];
			$this->version = $output_array[2];
		    }
		}

		return $res;
	    } elseif (is_array($genatorstring)) {
		
		foreach ($genatorstring as $i => $value) {
		    preg_match('/^([\w\s]+)\s+([\d\.]+)$/i', $value, $output_array);
		    if (isset($output_array)) {
			$res['name'] = $output_array[1];
			$this->name = $output_array[1];
			if (isset($output_array[2])) {
			    $res['version'] = $output_array[2];
			    $this->version = $output_array[2];
			}
			break;
		    }
		}
		if (!isset($res['name'])) {
		    $res['name'] = '';
		    foreach ($genatorstring as $i => $value) {
			$res['name'] .= $value."\n";
		    }
		    $this->name = $res['name'];
		}
		return $res;
	    }
	}
	// nichts im Meta, also nochmal den Content analysieren..  
	// TODO.

	return $res;

    }
    
   
}