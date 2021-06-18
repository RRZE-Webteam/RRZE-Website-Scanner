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
    
    
    public $systems = [
        "WordPress",
	"Webbaukasten"

    ];
    public function __construct() {
         $this->name = '';
         $this->version = '';
     } 
     
     
    function get_generator($tags,$content) {
	if (isset($tags)) {
	    $genatorstring = trim($tags['generator']);
	    $this->name = $genatorstring;
	    
	    if ((isset($genatorstring)) && (!is_array($genatorstring))) {

		foreach ($this->systems as $system_name) {
		    $controller = 'CMS\\'.$system_name;
		    
		    $cmsdata = new $controller;
		    if ($cmsdata->matchbymeta($genatorstring)) {
			$this->name = $cmsdata->name;
			$this->version = $cmsdata->version;
			$this->classname = $cmsdata->classname;
			$this->icon = $cmsdata->icon;
			$this->url = $cmsdata->url;
			return true;
		    }
		}
		

		preg_match('/^([\wa-zA-Z\s\-;&]+)\(?([\s\d\.\/]*)\)?$/iu', $genatorstring, $output_array);
		if (isset($output_array)) {
		    $this->name = trim($output_array[1]);
		    if (isset($output_array[2])) {
			$this->version = trim($output_array[2]);
		    }
		}

	    } elseif (is_array($genatorstring)) {
		
		foreach ($genatorstring as $i => $value) {
		    
		    foreach ($this->systems as $system_name) {
			$controller = 'CMS\\'.$searchname;
			$cmsdata = new $controller;
			if ($cmsdata->matchbymeta($value)) {
			    $this->name = $cmsdata->name;
			    $this->version = $cmsdata->version;
			    $this->classname = $cmsdata->classname;
			    $this->icon = $cmsdata->icon;
			    $this->url = $cmsdata->url;
			    return true;
			}
		    }
		
 
		    preg_match('/^([\wa-z0-9A-Z\s\-;&]+)\(?([\s\d\.\/]*)\)?$/iu', $value, $output_array);
		    if (isset($output_array)) {
			$this->name = $output_array[1];
			if (isset($output_array[2])) {
			    $this->version = $output_array[2];
			}
			break;
		    }
		}
		
		return;
	    }
	}
	// nichts im Meta, also nochmal den Content analysieren..  
	// TODO.

	return false;

    }
    
   
   
}