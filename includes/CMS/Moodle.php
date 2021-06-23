<?php
namespace CMS;

class Moodle extends \CMS {

	public $methods = array(
		"readme"
	);

	
	public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'moodle';
	    $this->cmsurl = 'https://www.drupal.org';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Moodle";
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
		   if($data = $this->fetch($this->url."/README.txt")) {

			/*
			 * Search "Moodle" text
			 */
			$lines = explode(PHP_EOL, $data);

			for($i=0;$i<count($lines);$i++) {
				if(strpos($lines[$i], "Moodle") !== FALSE) {
					return TRUE;
				}

			}

		}

		return FALSE;
        }
	

	
}
