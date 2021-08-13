<?php

/* 
 * Getting Infos from a detecting Percussion
 */
namespace CMS;

class Percussion extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'percussion';
	 $this->cmsurl = 'https://www.percussion.com/percussion-cms/';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "Percussion CMS";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	  "api", "content_string"
	);

     
   
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
	 * Check for Link Rels in Meta
	 * @return [boolean]
	 */
	public function api() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			    if ($type == 'preload') {
				if ((preg_match('/\/web_resources\/cm\/themes/i', $lc['href'], $matches)))
				    return true;
			    }
			   
			    if ($type == 'stylesheet') {
				if ((preg_match('/\/web_resources\/cm\/themes/i', $lc['href'], $matches)))
				    return true;
				
			    }
			    
			    
			}
		    }

		}

		return FALSE;

	}
	public function content_string() {
	    if ($this->content) {
		if (preg_match('/perc: https:\/\/percussion\.com\/perc\/elements\/1\.0\//i', $this->content, $matches)) {
		       return true;
		}
	
	    }
	    return FALSE;
	}
}