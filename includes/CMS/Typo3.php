<?php

namespace CMS;

class Typo3 extends \CMS
{

    public $methods = array(
        "generator_meta",
        "scripts",
	"content_string"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'typo3';
	    $this->cmsurl = 'https://typo3.org';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "TYPO3";
	    $this->links = $links;
	    $this->linkrels = $linkrels;
	    $this->scripts = $scripts;
	} 

     
    public function generator_meta($string = '') {
	if ((empty($string)) && isset($this->tags['generator'])) {
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
		'/^TYPO3 /i'
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
		if (preg_match('/This website is powered by TYPO3 /i', $this->content, $matches)) {
		       return true;
		}
	    }
	    return FALSE;
	}
	

    /**
     * Check for Typo3 scripts
     * @return boolean
     */
   
	public function scripts() {
		if($this->scripts) {
		    foreach($this->scripts as $num => $element) {
			    if (strpos($element, '/typo3conf/') !==FALSE)
				    return true;
		    }

		}

		return FALSE;

	}
	/*
     * Get Template (TYPO3 Extention)
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
		    if (preg_match('/typo3conf\/ext\/([a-zA-Z0-9\-_]+)\/Resources/i', $href, $output_array)) {
			if (isset($output_array)) {
			    $res['name'] = $output_array[1];
			    $res['url'] = $linkarray[$i]['stylesheet']['href'];

			    $found = true;
			    break;
			}
		    }
		}
	    }
	    if (isset($linkarray[$i]['manifest'])) {
		if (isset($linkarray[$i]['manifest']['href'])) {
		    
		    $href = $linkarray[$i]['manifest']['href'];
		    if (preg_match('/typo3conf\/ext\/([a-zA-Z0-9\-_]+)\/Resources/i', $href, $output_array)) {
			if (isset($output_array)) {
			    $res['name'] = $output_array[1];
			    $res['url'] = $linkarray[$i]['manifest']['href'];

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
