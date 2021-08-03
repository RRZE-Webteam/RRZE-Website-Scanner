<?php

/* 
 * Getting Infos from a detecting MS Sharepoint
 *  https://www.c-sharpcorner.com/blogs/how-to-identify-whether-a-site-is-based-on-sharepoint-or-not
 */
namespace CMS;

class Sharepoint extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'sharepoint';
	 $this->cmsurl = 'https://www.microsoft.com/de-de/microsoft-365/sharepoint/collaboration';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "Microsoft Sharepoint";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	 "generator_meta",  "scripts",  "mssharepoint_errorpage", "generator_header"	);

     
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
		    if (isset($matches[1])) {
			$this->version = $matches[1]; 
		    }
		    return $this->get_info();
		}
	    }
	}
	return false;
	
    }
     
    private function get_regexp_matches() {
	$match_reg = [
	    '/^Microsoft SharePoint/i'
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
	 * Check for errorcode on a known sharepoint url
	 *
	 * @return [boolean]
	 */
	public function mssharepoint_errorpage() {
		if($data = $this->fetch($this->url."/_layouts/lists.asmx")) {
			   
		   if (preg_match('/SharePointError/i', $data, $matches)) {
		       return true;
		    }
		}

		return FALSE;

	}
    
	/**
	 * Check for Generator header
	 * @return [boolean]
	 */
	public function generator_header() {
		if (isset($this->header) && is_array($this->header)) {
		 
		    if (isset($this->header['MicrosoftSharePointTeamServices'])) {
			$this->version = $this->header['MicrosoftSharePointTeamServices'];
			
		        return $this->get_info();
		    }
		    if (isset($this->header['Server']) && (preg_match('/Microsoft\-IIS/i', $this->header['Server'], $matches))) {
		        return $this->get_info();
		    }
		     if (isset($this->header['server']) && (preg_match('/Microsoft\-IIS/i', $this->header['server'], $matches))) {
		        return $this->get_info();
		    }

		}

		return FALSE;

	}
	
	/**
	 * Check for GovernmentSiteBuilder Core scripts
	 * @return [boolean]
	 */
	public function scripts() {
		if($this->scripts) {
		    foreach($this->scripts as $num => $element) {
			if ((preg_match('/_layouts\/[0-9]+\/init\.js/i', $element, $matches)))
			    return true;
		    }

		}

		return FALSE;

	}

	
}