<?php

namespace CMS;

class Zope extends \CMS {

    public $methods = array(
        "generator_header"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'zope';
	    $this->cmsurl = 'https://zope.org/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Zope";
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

		    if (isset($this->header['Server']) && (preg_match('/^Zope\/\(([a-z0-9\.\-]+)/i', $this->header['Server'], $matches))) {
			$this->version = $matches[1];
		       return true;
		    }
		    if (isset($this->header['x-powered-by']) && (preg_match('/^Zope /i', $this->header['x-powered-by'], $matches))) {
		       return true;
		    }

		}

		return FALSE;

	}

	
	


}
