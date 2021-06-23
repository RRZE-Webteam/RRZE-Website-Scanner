<?php

/* 
 * Getting Infos from a detecting CMS
 */
namespace CMS;

class Ilias extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'ilias';
	 $this->cmsurl = 'https://www.ilias.de/';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "Ilias";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	"scripts"
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
	 * Check for ILIAS Core scripts
	 * @return [boolean]
	 */
	public function scripts() {
		if($this->scripts) {
		    foreach($this->scripts as $num => $element) {

			    if (strpos($element, '/Services/JavaScript/js/Basic.js') !==FALSE) {
				if (preg_match('/Services\/JavaScript\/js\/Basic\.js\?vers=([a-z\-0-9\.]+)/i', $element, $output_array)) {
				    if (isset($output_array)) {			
					if (isset($output_array[1])) {
					   $this->version = $output_array[1];
					   $this->version = preg_replace('/\-/', '.', $this->version);
					}
				    }
				}
				
				return true;
			    }
				  
			    
			   if (strpos($element, '/Customizing/global/') !==FALSE)
				    return true;
		    }

		}

		return FALSE;

	}

	
}