<?php

namespace CMS;

class Express extends \CMS
{

    public $methods = array(
        "generator_header"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'express';
	    $this->cmsurl = 'https://expressjs.com/de/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Express";
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

		if(is_array($this->header)) {

		    if (preg_match('/^Express/i', $this->header['x-powered-by'], $matches)) {
			$this->version = $matches[1];
		       return true;
		    }

		}

		return FALSE;

	}

	
	


}
