<?php
namespace CMS;

class Joomla extends \CMS {

	public $methods = array(
		"readme",
		"generator_header",
		"core_js"
	);

	
	public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'joomla';
	    $this->cmsurl = 'https://www.joomla.de/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Joomla!";
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
		'/^Joomla! ([0-9\.]+) /i'
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
        
	public function readme() {
        /*
         * See if README.TXT exists, and check for Drupal 6.xx
         * @return [boolean]
         */
                if($data = $this->fetch($this->url."/README.txt")) {
			/**
			 * Loop first 10 lines and look for Joomla text
			 */
			$lines = explode(PHP_EOL, $data);

			for($i=0;$i<count($lines);$i++) {

				if(strpos($lines[$i], "2- What is Joomla?") !== FALSE) {
					return TRUE;
				}

			}

              
                }
	    return FALSE;
        }
	
	
	 /**
	 * Check /media/system/js/core.js content
	 * @return [boolean]
	 */
	public function core_js() {

		if($data = $this->fetch($this->url."/media/system/js/core.js")) {

			/**
			 * 4th line always has Joomla declaration
			 */
			$lines = explode(PHP_EOL, $data);
            if(array_key_exists(3,$lines))
            {
                return strpos($lines[3], "var Joomla={};") !== FALSE;
            }
		}

		return FALSE;

	}

	
}
