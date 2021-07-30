<?php

namespace CMS;

class Roxen extends \CMS
{

    public $methods = array(
        "generator_header"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'roxen';
	    $this->cmsurl = 'https://www.roxen.com/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Roxen CMS";
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

		    if (isset($this->header['Server']) && (preg_match('/^Roxen\/([a-z0-9\.\-]+)/i', $this->header['Server'], $matches))) {
			$this->version = $matches[1];
		       return true;
		    }
		    if (isset($this->header['server']) && (preg_match('/^Roxen\/([a-z0-9\.\-]+)/i', $this->header['server'], $matches))) {
			$this->version = $matches[1];
		       return true;
		    }

		}

		return FALSE;

	}

	
	


}
