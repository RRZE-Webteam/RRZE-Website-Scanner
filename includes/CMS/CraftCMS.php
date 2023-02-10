<?php

namespace CMS;

class CraftCMS extends \CMS {

    public $methods = array(
        "generator_header"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'craftCMS';
	    $this->cmsurl = 'https://craftcms.com/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Craft CMS";
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
             if (isset($this->header['x-powered-by']) &&  ($this->is_grepmeta($this->header['x-powered-by'],'/^Craft CMS/i'))) {
                 return $this->get_info();
            }
		}

		return FALSE;

	}

	
	


}
