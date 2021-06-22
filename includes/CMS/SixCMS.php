<?php

namespace CMS;

class SixCMS extends \CMS
{

    public $methods = array(
        "css"
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'sixcms';
	    $this->cmsurl = 'https://www.six.de';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "SixCMS";
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
        
  

   
	
	 /* Check for CSS
	 * @return [boolean]
	 */
	public function css() {
		if($this->linkrels) {
		    foreach($this->linkrels as $num => $element) {
			
			  foreach($element as $type => $lc) {

			    if ($type == 'stylesheet') {
				if (strpos($lc['href'], '/sixcms/detail.php/') !==FALSE)
				    return true;
			    }
			   
			    
			}
		    }

		}

		return FALSE;

	}

}
