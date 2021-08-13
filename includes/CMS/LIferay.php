<?php

namespace CMS;

class Liferay extends \CMS {

    public $methods = array(
        "generator_header", 
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'liferay';
	    $this->cmsurl = 'https://www.liferay.com/de/home';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Liferay";
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
		 
		    if (isset($this->header['liferay-portal']) && (preg_match('/^Liferay Portal Community Edition ([a-z0-9\(\)\s,\.\/]+)/i', $this->header['liferay-portal'], $matches))) {
			if (isset($matches[1])) {
			 $this->version = $matches[1]; 
			}
			return $this->get_info();			
		    }
		   

		}

		return FALSE;

	}

	
}
