<?php

/* 
 * Getting Infos from a detecting Government Site Builder CMS
 */
namespace CMS;

class GovernmentSiteBuilder extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'governmentsitebuilder';
	 $this->cmsurl = 'https://www.government-site-builder.de';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "Government Site Builder";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	 "generator_meta", "api", "scripts", "content_string"
	);

     
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
	    '/^Government Site Builder/i'
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
	 * Check for WordPress Core scripts
	 * @return [boolean]
	 */
	public function scripts() {
		if($this->scripts) {
		    foreach($this->scripts as $num => $element) {
			    if (strpos($element, 'SiteGlobals/') !==FALSE)
				    return true;
		    }

		}

		return FALSE;

	}

	/**
	 * Check for WordPress Core API
	 * @return [boolean]
	 */
	public function api() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			    if ($type == 'alternate') {
				if (strpos($lc['href'], 'SiteGlobals/') !==FALSE)
				    return true;
			    }
			    
			    
			}
		    }

		}

		return FALSE;

	}
	public function content_string() {
	    if ($this->content) {
		if (preg_match('/Realisiert mit dem Government Site Builder/i', $this->content, $matches)) {
		       return true;
		}
	    }
	    return FALSE;
	}
}