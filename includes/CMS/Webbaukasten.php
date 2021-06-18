<?php

/* 
 * Getting Infos from a detecting Webbaukasten
 */
namespace CMS;

class Webbaukasten {
    
    
    public function __construct() {
         $this->classname = 'webbaukasten';
      //   $this->icon = '';
	 $this->url = 'https://webbaukasten.rrze.fau.de/';
     } 
     
     public function matchbymeta($string) {
	if (!isset($string)) {
	    return;
	}
	$matches = $this->get_regexp_matches();
	foreach ($matches as $m) {
	    echo "CHECK FOR $m\n";
	    if (preg_match($m, $string, $matches)) {
		$this->name = "Webbaukasten";
		$this->version = $matches[1]; 
		return $this->get_info();
	    }
	}
	return false;
	
    }
     
    private function get_regexp_matches() {
	$match_reg = [
	    '/^Web\-Baukasten der Friedrich\-Alexander\-Universität \(([0-9\/\-\.]+)\)$/ui',
	    '/^Webbaukasten der Friedrich\-Alexander\-Universität \(([0-9\/\-\.]+)\)$/ui',
	     '/^Web\-baukasten der Friedrich\-Alexander\-Universit&auml;t \(([0-9\/\-\.]+)\)$/ui'
	];
	return $match_reg;

	
    }   
    public function get_info() {
	$info = array();
        $info['icon']	    = $this->icon;
	$info['classname']  = $this->classname;	   
	$info['url']	    = $this->url;
	$info['name']	    = $this->name; 
	$info['version']    = $this->version; 
	return $info;
    }   
}