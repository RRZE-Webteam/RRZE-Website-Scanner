<?php

/* 
 * Getting Infos from a detecting CMS
 */
namespace CMS;

class OpenCms extends \CMS  {
    
    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
         $this->classname = 'opencms';
	 $this->cmsurl = 'http://www.opencms.org/';
	 $this->url = $url;
	 $this->tags = $tags;
	 $this->content = $content;
	 $this->name = "OpenCms";
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
	 * Check for OpenCms Core scripts
	 * @return [boolean]
	 */
	public function scripts() {
		if($this->scripts) {
		    
		    $p = parse_url($this->url);
		    $host_names = explode(".", $p['host']);
		    $tld = $host_names[count($host_names)-1];
		    $dom = $host_names[count($host_names)-2];
		    
		    $searchscript = '/system/modules/'.$tld.'.'.$dom;
		    
		    foreach($this->scripts as $num => $element) {
			    
			    if (strpos($element, $searchscript) !==FALSE) {
				return true;
			    }
				  
			    
			  
		    }

		}

		return FALSE;

	}

	
}