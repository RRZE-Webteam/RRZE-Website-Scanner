<?php

/* 
 * Detect CMS or try it
 */

class CMS {
    var $name;
    var $version;
    var $info;
    var $content;
    var $classname;
    var $icon;
    var $url; 
    
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
		$res = $this->add_generator_info($res);
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
		$this->add_generator_info($res);
		return $res;
	    }
	}
	// nichts im Meta, also nochmal den Content analysieren..  
	// TODO.

	return $res;

    }
    
   function add_generator_info($info) {
       if (!isset($info)) {
	   return;
       }
       $searchname = $info['name'];
       $searchname = preg_replace('/[^a-z0-9A-Z]+/', "", $searchname);
       if (in_array( $searchname, ["WordPress"])) {
	    $controller = 'CMS\\'.$searchname;
	    $cmsdata = new $controller;
	    $info['classname'] = $cmsdata->classname;
	    $info['icon'] = $cmsdata->icon;
	    $this->classname = $cmsdata->classname;
	    $this->icon = $cmsdata->icon;
	    $this->url= $cmsdata->url;
	    $info['url'] = $cmsdata->url;
	}
       return $info;
   }
}