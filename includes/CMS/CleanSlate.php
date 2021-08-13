<?php

/* 
 * Getting Infos from a detecting CleanSlate (WVU)
 */
namespace CMS;

class CleanSlate extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'cleanslate';
	 $this->cmsurl = 'https://cleanslatecms.wvu.edu/';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "CleanSlate";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	    "generator_meta", "content_string"
	);

     
    public function generator_meta($string = '') {
	if ((empty($string)) && isset($this->tags['editorURL'])) {
	    $string = $this->tags['editorURL'];
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
	    '/cleanslate/i'
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
		if (preg_match('/CleanSlate/i', $this->content, $matches)) {
		       return true;
		}
	    }
	    return FALSE;
	}
}