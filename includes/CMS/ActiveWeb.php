<?php

namespace CMS;

class ActiveWeb extends \CMS
{

    public $methods = array(
        "content_string"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	     $this->classname = 'activeweb';
	    $this->cmsurl = 'https://www.active-web.de/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "activeWeb";
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
        

	
	public function content_string() {
	    if ($this->content) {
            if (preg_match('/page generated with activeWeb contentserver ([a-z0-9\.]+)/mi', $this->content, $matches)) {

                    $this->version = $matches[1]; 

                   return true;
            }
	    }
	    return FALSE;
	}
	


}
