<?php

/* 
 * Getting Infos from a detecting Omni
 */
namespace CMS;

class Omni extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'omni';
	 $this->cmsurl = 'https://web.vanderbilt.edu/omni/';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "Omni";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	     "content_string"
	);

     
   
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
		if (preg_match('/<!\-\- OU Search Ignore End Here \-\->/i', $this->content, $matches)) {
		       return true;
		}
		if (preg_match('/<!\-\- ouc:info uuid="[a-z0-9\-]+"\/ \-\->/i', $this->content, $matches)) {
		       return true;
		}
		if (preg_match('/ href="https:\/\/a\.cms\.omniupdate\.com\//i', $this->content, $matches)) {
		       return true;
		}
		if (preg_match('/rel="nofollow" href="https:\/\/a\.ou\./i', $this->content, $matches)) {
		       return true;
		}
		if (preg_match('/ href="https:\/\/omni\.[a-z0-9\-;&=\/\?]+path=\/index\.pcf">/i', $this->content, $matches)) {
		       return true;
		}
		if (preg_match('/ href="https:\/\/ou\.[a-z0-9\-;&=\/\?]+path=\/index\.pcf">/i', $this->content, $matches)) {
		       return true;
		}
		
		if (preg_match('/<script src="\/_resources\/js\/direct\-edit\.js"><\/script>/i', $this->content, $matches)) {
		       return true;
		}
	
	    }
	    return FALSE;
	}
}