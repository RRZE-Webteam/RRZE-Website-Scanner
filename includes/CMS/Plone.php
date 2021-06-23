<?php

/* 
 * Getting Infos from a detecting Government Site Builder CMS
 */
namespace CMS;

class Plone extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'plone';
	 $this->cmsurl = 'https://plone.org/';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "Plone";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	 "generator_meta", "api"
	);

     
    public function generator_meta($string = '') {
	if (empty($string)) {
	    $string = $this->tags['generator'];
	}
	
	if (empty($string)) {
	    return false;
	}
	
	if (is_array($string)) {
	    foreach ($string as $line) {
		 $ret = $this->generator_meta($line);
		 if ($ret !== false) {
		     return $ret;
		 }
	    }
	} else {
	    $matches = $this->get_regexp_matches();
	    foreach ($matches as $m) {
		if (preg_match($m, $string, $matches)) {

		    $this->version = $matches[1]; 
		    return $this->get_info();
		}
	    }
	}
	return false;
	
    }
     
    private function get_regexp_matches() {
	$match_reg = [
	    '/^Plone /i'
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
    
    


	/**
	 * Check for Plone API
	 * @return [boolean]
	 */
	public function api() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			    if ($type == 'shortcut icon') {
				if (strpos($lc['href'], '/++') !==FALSE)
				    return true;
			    }
			     if ($type == 'search') {
				if (strpos($lc['href'], '@@search') !==FALSE)
				    return true;
			    }
			    
			    
			}
		    }

		}

		return FALSE;

	}
	
}