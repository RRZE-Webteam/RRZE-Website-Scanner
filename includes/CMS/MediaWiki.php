<?php

namespace CMS;

class MediaWiki extends \CMS
{

    public $methods = array(
        "generator_header",
        "content_string"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'mediawiki';
	    $this->cmsurl = 'https://www.mediawiki.org/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "MediaWiki";
	    $this->links = $links;
	    $this->linkrels = $linkrels;
	    $this->scripts = $scripts;
	} 

     
	public function generator_header() {
	    $string = $this->tags['generator'];

	    if (empty($string)) {
		return false;
	    }

	    $matches = $this->get_regexp_matches();
	    foreach ($matches as $m) {
		if (preg_match($m, $string, $matches)) {

		    $this->version = $matches[1]; 
		    return $this->get_info();
		}
	    }
	    return false;

	}
	 private function get_regexp_matches() {
	    $match_reg = [
		'/^MediaWiki ([0-9\.\-a-z]+)$/i'
	    ];
	    return $match_reg;
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
		if (preg_match('/<img src="[^<>]+" alt="Powered by MediaWiki"/i', $this->content, $matches)) {
		       return true;
		}
	    }
	    return FALSE;
	}

}
