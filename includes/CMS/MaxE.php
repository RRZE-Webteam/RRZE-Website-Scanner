<?php

namespace CMS;

class MaxE extends \CMS {

    public $methods = array(
	"content_string"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'max-e';
	    $this->cmsurl = 'https://hilfe.max-e.info/startseite';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "max-e";
	    $this->links = $links;
	    $this->linkrels = $linkrels;
	    $this->scripts = $scripts;
	} 


	public function get_info() {
	    $info = array();
	    $info['icon']	    = $this->icon;
	    $info['classname']	    = $this->classname;	   
	    $info['url']	    = $this->url;
	    $info['name']	    = $this->name; 
	    $info['version']	    = $this->version; 
	    return $info;
	}
        

	public function content_string() {
	    if ($this->content) {
		if (preg_match('/<body([^<>]+)data\-template=([\"\']+)homepage([\"\']+)/i', $this->content, $matches)) {
		    if (preg_match('/assets\/application\-([a-z0-9]+)\.js/i', $this->content, $matches)) {
			   return true;
		    }
		}
	    }
	    return FALSE;
	}
    
}
