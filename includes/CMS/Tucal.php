<?php

namespace CMS;

class Tucal extends \CMS
{

    public $methods = array(
       "api"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	     $this->classname = 'tucal';
	    $this->cmsurl = 'https://www.tu-chemnitz.de/urz/www/tucal.html';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "TUCAL";
	    $this->links = $links;
	    $this->linkrels = $linkrels;
	    $this->scripts = $scripts;
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
	 * Check for Known Link rels
	 * @return [boolean]
	 */
	public function api() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			    if ($type == 'stylesheet') {
				if (strpos($lc['href'], '/tucal4/') !==FALSE)
				    return true;
			    }
			    
			    
			}
		    }

		}

		return FALSE;

	} 
	
	
	


}
