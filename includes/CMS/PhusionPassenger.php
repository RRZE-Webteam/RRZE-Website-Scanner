<?php

namespace CMS;

class PhusionPassenger extends \CMS {

    public $methods = array(
        "generator_header", 
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'phusion';
	    $this->cmsurl = 'https://www.phusionpassenger.com/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Phusion Passenger";
	    $this->links = $links;
	    $this->linkrels = $linkrels;
	    $this->scripts = $scripts;
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
	 * Check for Generator header
	 * @return [boolean]
	 */
	public function generator_header() {
		if (isset($this->header) && is_array($this->header)) {
		 
		    if (preg_match('/^phusion/i', $this->header['x-powered-by'], $matches)) {
		        return $this->get_info();
		    }
		    if (preg_match('/Phusion/i', $this->header['server'], $matches)) {
		        return $this->get_info();
		    }

		}

		return FALSE;

	}

	
}
