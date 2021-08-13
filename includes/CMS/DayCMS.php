<?php

/* 
 * Getting Infos from a detecting Omni
 */
namespace CMS;

class DayCMS extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'daycms';
	 $this->cmsurl = '';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "Day CMS";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	     "content_string", "scripts", "api"
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
	 * Check for Core scripts
	 * @return [boolean]
	 */
	public function scripts() {
		if($this->scripts) {
		    foreach($this->scripts as $num => $element) {
			    if (strpos($element, '/etc.clientlibs/clientlibs/') !==FALSE)
				    return true;
			    
		    }

		}

		return FALSE;

	}
	
	
	public function api() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			      if ($type == 'stylesheet') {
				if ((preg_match('/\/etc\/designs\/[a-z0-9]+\.css/i', $lc['href'], $matches)))
				    return true;
				
			    }
			    
			    
			}
		    }

		}

		return FALSE;

	}
	

	
	public function content_string() {
	    if ($this->content) {
		if (preg_match('/GraniteClientLibraryManager\.write\(/i', $this->content, $matches)) {
		       return true;
		}
		
	
	    }
	    return FALSE;
	}
}