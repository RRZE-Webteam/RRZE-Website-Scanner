<?php

/* 
 * Getting Infos from a detecting Cascade
 */
namespace CMS;

class Cascade extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'cascade';
	 $this->cmsurl = 'https://www.hannonhill.com/products/index.html';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "Cascade CMS";
	 $this->links = $links;
	 $this->linkrels = $linkrels;
	 $this->scripts = $scripts;
     } 
     public $methods = array(
	      "api"
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
    
   
	
	
	
	public function api() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			      if ($type == 'stylesheet') {
				if ((preg_match('/\/assets\/[a-z0-9]+\/styles\/global\.min\.css/i', $lc['href'], $matches)))
				    return true;
				
			    }
			    
			    
			}
		    }

		}

		return FALSE;

	}
	


}