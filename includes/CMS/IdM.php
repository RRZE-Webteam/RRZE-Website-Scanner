<?php
namespace CMS;

class IdM extends \CMS {

	public $methods = array(
		"readme"
	);

	
	public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'idm';
	    $this->cmsurl = 'https://idm.fau.de';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "IdM";
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
        
	public function readme() {
		   if($data = $this->fetch($this->url."/static/images/favicons/fau/site.webmanifest")) {

			/*
			 * Search "IdM" text
			 */
			$lines = explode(PHP_EOL, $data);

			for($i=0;$i<count($lines);$i++) {
				if(strpos($lines[$i], "\"IdM\"") !== FALSE) {
					return TRUE;
				}

			}

		}

		return FALSE;
        }
	

	
}
