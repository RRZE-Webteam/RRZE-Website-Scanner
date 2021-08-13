<?php

namespace CMS;

class ASP extends \CMS {

    public $methods = array(
        "generator_header", "content_string"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'asp';
	    $this->cmsurl = 'https://dotnet.microsoft.com/apps/aspnet';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "ASP.NET";
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

		    if (isset($this->header['x-powered-by']) && (preg_match('/^ASP\.NET/i', $this->header['x-powered-by'], $matches))) {
		       return true;
		    }

		}

		return FALSE;

	}

	
	public function content_string() {
	    if ($this->content) {
		if (preg_match('/<form method="post" action="\.\/default\.aspx"/i', $this->content, $matches)) {
		       return true;
		}
	
	    }
	    return FALSE;
	}


}
