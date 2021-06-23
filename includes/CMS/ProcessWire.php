<?php

namespace CMS;

class ProcessWire extends \CMS {

    public $methods = array(
        "generator_header", "application_name"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'processwire';
	    $this->cmsurl = 'https://processwire.com/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "ProcessWire";
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

		if(is_array($this->header)) {

		    if (preg_match('/^ProcessWire/i', $this->header['x-powered-by'], $matches)) {
		       return true;
		    }
		    if (preg_match('/^ProcessWire/i', $this->header['X-Powered-By'], $matches)) {
		       return true;
		    }

		}

		return FALSE;

	}

	public function application_name($string = '') {
	    if (empty($string)) {
		$string = $this->tags['application_name'];
	    }

	    if (empty($string)) {
		return false;
	    }

	    if (is_array($string)) {
		foreach ($string as $line) {
		     $ret = $this->application_name($line);
		     if ($ret !== false) {
			 return $ret;
		     }
		}
	    } else {
		$matches = $this->get_regexp_matches();
		foreach ($matches as $m) {
		    if (preg_match($m, $string, $matches)) {
			return $this->get_info();
		    }
		}
	    }
	    return false;

	}
	private function get_regexp_matches() {
	    $match_reg = [
		'/^ProcessWire /i'
	    ];
	    return $match_reg;
	}  


}
