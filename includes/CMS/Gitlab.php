<?php

namespace CMS;

class Gitlab extends \CMS
{

    public $methods = array(
        "generator_meta",
        "generator_header",
        "read_manifest",
        
    );

    
    public function __construct($url, $tags, $content, $links, $linkrels, $scripts) {
	    $this->classname = 'gitlab';
	    $this->cmsurl = 'https://about.gitlab.com/';
	    $this->url = $url;
	    $this->tags = $tags;
	    $this->content = $content;
	    $this->name = "Gitlab";
	    $this->links = $links;
	    $this->linkrels = $linkrels;
	    $this->scripts = $scripts;
	} 

     /**
	 * Check for Generator header
	 * @return [boolean]
	 */
	public function generator_header() {

		if (isset($this->header) && is_array($this->header)) {

            if (isset($this->header['x-gitlab-meta'])) {
                 return $this->get_info();
            }
            

		}

		return FALSE;

	}
    public function generator_meta($string = '') {
       
        
        if ((empty($string)) && isset($this->tags['_property']['og:site_name'])) {
            $string = $this->tags['_property']['og:site_name'];
        }

        if (empty($string)) {
            return false;
        }

        if (is_array($string)) {
            foreach ($string as $line) {
                $ret = $this->generator_meta($line);
                if ($ret !== false) {
                    return $ret;
                }
            }
        } else {
            $matches = $this->get_regexp_matches();
            foreach ($matches as $m) {
                if (preg_match($m, $string, $matches))  {
                    // ok, its Gitlab. 
                     return $this->get_info();
                }
            }
        }
        return false;
	
    }
	private function get_regexp_matches() {
	    $match_reg = [
            '/^GitLab/iu'
	    ];
	    return $match_reg;
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
        
	public function read_manifest() {
		   if($data = $this->fetch($this->url."/-/manifest.json")) {
                $lines = explode(PHP_EOL, $data);

                for($i=0;$i<count($lines);$i++) {
                    if (preg_match('/"name":"([0-9\.]+)"/', $lines[$i], $matches)) {
                        $this->name = $matches[1]; 
                        return $this->get_info();
                    }


                }
                return false;

		}

		return FALSE;
    }

}
