<?php

namespace CMS;

class Pimcore extends \CMS {

    public $methods = array(
        "generator_header", "scripts"
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

		if(isset($this->header) AND is_array($this->header)) {

		    if (preg_match('/^pimcore/i', $this->header['x-powered-by'], $matches)) {
		       return true;
		    }
		    if (preg_match('/^pimcore/i', $this->header['X-Powered-By'], $matches)) {
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


}
