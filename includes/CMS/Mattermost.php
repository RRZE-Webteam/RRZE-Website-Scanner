<?php

namespace CMS;

class Mattermost extends \CMS
{

    public $methods = array(
        "generator_meta"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'mattermost';
	    $this->cmsurl = 'https://mattermost.com/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Mattermost";
	    $this->links = $links;
	    $this->linkrels = $linkrels;
	    $this->scripts = $scripts;
	} 

     
    public function generator_meta($string = '') {
	if ((empty($string)) && isset($this->tags['application-name'])) {
	    $string = $this->tags['application-name'];
	}
	
	if (empty($string)) {
	    return false;
	}
	
	if (is_array($string)) {
	    foreach ($string as $line) {
		 $ret = $this->generator_meta($line);
		 if ($ret !== false) {
		     return $ret;
		 }
	    }
	} else {
	    $matches = $this->get_regexp_matches();
	    foreach ($matches as $m) {
		if (preg_match($m, $string, $matches)) {

		    $this->version = $matches[1]; 
		    return $this->get_info();
		}
	    }
	}
	return false;
	
    }
	 private function get_regexp_matches() {
	    $match_reg = [
		'/^Mattermost\s*([0-9\.\-a-z\s&;]*)/iu'
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
        


}
