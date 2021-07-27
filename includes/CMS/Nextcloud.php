<?php

namespace CMS;

class Nextcloud extends \CMS
{

    public $methods = array(
        "generator_meta",
	"readstatus"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'nextcloud';
	    $this->cmsurl = 'https://nextcloud.com/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Nextcloud";
	    $this->links = $links;
	    $this->linkrels = $linkrels;
	    $this->scripts = $scripts;
	} 

     
    public function generator_meta($string = '') {
	if (empty($string)) {
	    $string = $this->tags['apple-itunes-app'] ?? '';
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

		    // ok, its Nextcloud. Additional try to get the version number from status.php
		    
		   $readstatus =  $this->readstatus();
		     return $this->get_info();
		}
	    }
	}
	return false;
	
    }
	 private function get_regexp_matches() {
	    $match_reg = [
		'/^app\-id=1125420102/iu'
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
        
	public function readstatus() {
		   if($data = $this->fetch($this->url."/status.php")) {

			$lines = explode(PHP_EOL, $data);

			for($i=0;$i<count($lines);$i++) {
				if (preg_match('/"version":"([0-9\.]+)"/', $lines[$i], $matches)) {
				    $this->version = $matches[1]; 
				    return $this->get_info();
				}
			   

			}
			 return $this->get_info();

		}

		return FALSE;
        }

}
