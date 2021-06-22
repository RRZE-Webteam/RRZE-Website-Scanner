<?php
namespace CMS;

class Joomla extends \CMS {

	public $methods = array(
		"readme",
		"generator_meta",
		"core_js",
	    "core_site_js"
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

     
    public function generator_meta($string = '') {
	if (empty($string)) {
	    $string = $this->tags['generator'];
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
		    if(array_key_exists(3,$lines))    {
			return strpos($lines[3], "var Joomla={};") !== FALSE;
		    }
		}

		return FALSE;

	}
 /**
	 * Check /media/system/js/core.js content
	 * @return [boolean]
	 */
	public function core_site_js() {

		if($data = $this->fetch($this->url."/site/media/system/js/core.js")) {

			/**
			 * Look if the Joomla Definiton starts
			 */
		      if (preg_match('/^Joomla=window\.Joomla/i', $data, $matches)) {
		       return true;
		    }
		}

		return FALSE;

	}

	  /*
     * Get Template  
     */
    
    function get_template() {
	$linkarray = $this->linkrels;
	$genversion = $this->version;
	$found = false;
	$res = array();
	foreach ($linkarray as $i => $values) {
	    if (isset($linkarray[$i]['stylesheet'])) {
		if (isset($linkarray[$i]['stylesheet']['href'])) {
		    
		    $href = $linkarray[$i]['stylesheet']['href'];
		    if (preg_match('/templates\/([a-zA-Z0-9\-_]+)\/css\/template\.css/i', $href, $output_array)) {
			if (isset($output_array)) {
			    $res['name'] = $output_array[1];
			    $res['url'] = $linkarray[$i]['stylesheet']['href'];
			    
			   
			    $found = true;
			    break;
			}
		    }
		}
	    }
	}
	if ($found) {
	    return $res;
	} else {
	    return false;
	}
    }
}
