<?php

namespace CMS;

class Pimcore extends \CMS {

    public $methods = array(
        "generator_header", "scripts", "content_string"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'pimcore';
	    $this->cmsurl = 'https://pimcore.com/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Pimcore";
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
		    if (isset($this->header['x-powered-by']) && (!is_array($this->header['x-powered-by'])) && (preg_match('/^pimcore/i', $this->header['x-powered-by'], $matches))) {
		       return true;
		    }
		    if (isset($this->header['X-Powered-By']) && (!is_array($this->header['x-powered-by'])) && (preg_match('/^pimcore/i', $this->header['X-Powered-By'], $matches))) {
		       return true;
		    }

		}

		return FALSE;

	}

	/**
	 * Check for Core scripts
	 * @return [boolean]
	 */
	public function scripts() {
		if($this->scripts) {
		    foreach($this->scripts as $num => $element) {

			    if (strpos($element, '/bundles/pimcore/js') !==FALSE) {
				return true;
			    }
				  
			    
			   if (strpos($element, '/bundles/pimcorecore/js') !==FALSE)
				    return true;
		    }

		}

		return FALSE;

	}
	public function content_string() {

	    if ($this->content) {
		if (preg_match('/pimcore_area_content/i', $this->content, $matches)) {
		       return true;
		}
		if (preg_match('/pimcore_area_headline/i', $this->content, $matches)) {
		       return true;
		}
	
	    }
	    return FALSE;
	}

}
