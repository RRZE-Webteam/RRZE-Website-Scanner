<?php

namespace CMS;

class Neos extends \CMS
{

    public $methods = array(
        "scripts",
	"api",
	"content_string"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'neos';
	    $this->cmsurl = 'https://www.neos.io/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Neos";
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
        
  	public function content_string() {
	    if ($this->content) {
		if (preg_match('/This website is powered by Neos, the Open Source Content Application Platform/i', $this->content, $matches)) {
		       return true;
		}
		if (preg_match('/class="neos-contentcollection"/i', $this->content, $matches)) {
		       return true;
		}
		if (preg_match('/class="neos-message-header"/i', $this->content, $matches)) {
		       return true;
		}
	    }
	    return FALSE;
	}
	
	/**
	 * Check for Core API
	 * @return [boolean]
	 */
	public function api() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			    if ($type == 'stylesheet') {
				if ((preg_match('/_Resources\/Static\/Packages\/[a-z0-9]+\.[a-z0-9]+\//i', $lc['href'], $matches)))
				    return true;
				
			    }
			    
			    
			}
		    }

		}

		return FALSE;

	}
	
	/**
	 * Check for typical scrip pathes Core scripts
	 * @return [boolean]
	 */
	public function scripts() {
		if($this->scripts) {
		    foreach($this->scripts as $num => $element) {
			if ((preg_match('/_Resources\/Static\/Packages\/[a-z0-9]+\.[a-z0-9]+\//i', $element, $matches)))
			    return true;
		    }

		}

		return FALSE;

	}

	
}
