<?php

namespace CMS;

class Typo3 extends \CMS
{

    public $methods = array(
        "generator_header",
        "scripts"
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

}
