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

            if ($this->is_grepmeta($this->header['x-powered-by'],'/^phusion/i')) {
                 return $this->get_info();
            }
		   
            
            if (!empty($this->header['server'])) {
                if ($this->is_grepmeta($this->header['server'],'/Phusion/i')) {
                    return $this->get_info();
                }
		    }
		
            
            
		}

		return FALSE;

	}

	
}
