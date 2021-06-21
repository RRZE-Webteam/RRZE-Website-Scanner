<?php

/* 
 * Getting Infos from a detecting Webbaukasten
 */
namespace CMS;

class Webbaukasten extends \CMS  {
    
    
    public function __construct($url, $tags, $content,$links, $linkrels, $scripts) {
         $this->classname = 'webbaukasten';
      //   $this->icon = '';
	 $this->cmsurl = 'https://webbaukasten.rrze.fau.de/';
	 $this->searchurl = $url;
	 $this->tags = $tags;
	 $this->content = $content;
 	 $this->links = $links;
         $this->linkrels = $linkrels;
         $this->scripts = $scripts;
     } 
     public $methods = array(
		"generator_header"
    );
     
     public function generator_header() {
	
	$string = $this->tags['generator'];
	 
	if (empty($string)) {
	    return false;
	}
	
	
	$matches = $this->get_regexp_matches();
	foreach ($matches as $m) {
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
	     '/^Web\-baukasten der Friedrich\-Alexander\-Universit&auml;t \(([0-9\/\-\.]+)\)$/ui',
	    '/^Web\-Baukasten der Friedrich\-Alexander\-Universität\s*\(?([0-9\-\/\.]*)\)?/ui'
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