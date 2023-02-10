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

             if ($this->is_grepmeta($this->header['Server'],'/^Zope/i')) {
                 if (is_string($this->header['Server']) && (preg_match('/^Zope\/\(([a-z0-9\.\-]+)/i', $this->header['Server'], $matches))) {
                        $this->version = $matches[1];
                 }
                 return $this->get_info();
            }
            if ($this->is_grepmeta($this->header['x-powered-by'],'/^Zope/i')) {
                 return $this->get_info();
            }
            
		 

		}

		return FALSE;

	}

	
	


}
