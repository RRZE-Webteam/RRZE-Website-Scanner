<?php

/* 
 * Detect CMS or try it
 * In parts adapted by https://github.com/Krisseck/Detect-CMS
 */

class CMS {
    var $name;
    var $version;
    var $info;
    var $content;
    var $classname;
    var $icon;
    var $url; 
    var $cmsurl;
    var $links;
    var $scripts;
    var $linkrels;
    var $tags;
    var $header;
    
    public $systems = [
        "WordPress",
        "Drupal",
        "Typo3",
        "MediaWiki",
        "DokuWiki",
        "Joomla",
        "Contao",
        "Imperia",
        "ActiveWeb",
        "InfoparkFiona",
        "Roxen",
        "Zope",
        "Express",
        "SixCMS",
        "GovernmentSiteBuilder",
        "Plone",
        "Moodle",
        "Ilias",
        "Gitlab",
        "Webbaukasten",
        "OpenCms",
        "ProcessWire",
        "Pimcore",
        "Mattermost",
        "Nextcloud",
        "Sharepoint",
        "HisInOne",
        "Neos",
        "IdM",
        "PhusionPassenger",
        "Additor",
        "Idbase",
        "Framula",
        "Tucal",
        "FirstSpirit",
        "Sitepark",
        "CraftCMS",
        "Magnolia",
        "Cabacos",
        "GCMS",
        "MaGIC",
        "SilverStripe",
        "Scrivito",
        "PlatoCMS",
        "WebEdition",
        "XIMS","Liferay", 
        "HubSpot", "CleanSlate",  "Dreamweaver", "Percussion", "Omni", "DayCMS","Cascade","Kentico",
        "ASP", "MaxE", "Django"

    ];

    
    private $common_methods = ["generator_meta", "generator_header", "api", "scripts", "content_string"];
    
    
    public function __construct($url) {
        $this->name = '';
        $this->version = '';
        $this->url = $url;
    } 
     
    public function add_linkrel($linkrels) {
        if (isset($linkrels)) {
            $this->linkrels = $linkrels;
        }
    }
    public function add_scripts($scripts) {
        if (isset($scripts)) {
            $this->scripts = $scripts;
        }
    }
    public function add_links($links) {
        if (isset($links)) {
            $this->links = $links;
        }
    }
    public function add_header($header) {
        if (isset($header)) {
            $this->header = $header;
        }
     }
      

    public function get_generator($tags,$content) {

        /*
         * Common, easy way first: check for Generator metatags or Generator headers
         */
	
        foreach ($this->systems as $system_name) {
            $system_class = 'CMS\\' . $system_name;
            $system = new $system_class($this->url, $tags, $content, $this->links, $this->linkrels, $this->scripts);
            $system->header = $this->header;
            foreach ($this->common_methods as $method) {
                if (method_exists($system, $method)) {
                    if ($system->$method()) {
                        $this->name = $system->name;
                        $this->version = $system->version;
                        $this->classname = $system->classname;
                        $this->icon = $system->icon;
                        $this->cmsurl= $system->cmsurl;
                        return $this->name;
                    }
                }

            }

        }

        /*
         * Didn't find it yet, let's just use regular tricks
         */

        foreach ($this->systems as $system_name) {
            $system_class = 'CMS\\' . $system_name;
            $system = new $system_class($this->url, $tags, $content, $this->links, $this->linkrels, $this->scripts);
            $system->header = $this->header;
            foreach ($system->methods as $method) {
                if (!in_array($method, $this->common_methods)) {
                    if ($system->$method()) {
                        $this->name = $system->name;
                        $this->version = $system->version;
                        $this->classname = $system->classname;
                        $this->icon = $system->icon;
                        $this->cmsurl= $system->cmsurl;
                        return $this->name;
                    }

                }

            }

        }
	// Didnt find anything till yet. If meta tag filled with a string, return this.
	
	if (isset($tags)) {
	    if (isset($tags['generator'])) {
            if (is_array($tags['generator'])) {
                $genatorstring = '';
                foreach ($tags['generator'] as $entry) {
                    if (!empty($genatorstring)) {
                        $genatorstring .= ', ';
                    }
                    $genatorstring .= trim($entry);
                }
            } else {
                $genatorstring = trim($tags['generator']);
            }
		
            $this->name = $genatorstring;
	    }
	   
	    return $this->name;
	}


        return false;

    }
    
    public function get_cms_template($tags,$content) {
        foreach ($this->systems as $system_name) {

            $system_class = 'CMS\\' . $system_name;
            $system = new $system_class($this->url, $tags, $content, $this->links, $this->linkrels, $this->scripts);

            if (method_exists($system, "get_template")) {
                $res = $system->get_template();
                if ($res !== false) {
                    return  $res;
                }
            }
        }
        return false;

    }

     
    protected function fetch($url = null)  {

        $ch = curl_init();

        if ($url == null) {
            $url = $this->url;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (RRZE CheckBot)');

        $return = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode >= 400) {
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return $return;

    }

    protected function fetchBodyAndHeaders()  {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (RRZE CheckBot)');

        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode >= 400) {
            curl_close($ch);
            return false;
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $header_array = [];

        foreach (explode("\r\n", $header) as $line) {
            if ($line == '') {
                continue;
            }

            $array = explode(': ', $line);
            if (array_key_exists(1, $array)) {
                list ($key, $value) = $array;
                $header_array[$key] = $value;
                continue;
            }

            $header_array['http_code'] = $line;
        }

        curl_close($ch);

        return [$header_array, $body];

    }
   
    
    public function is_grepmeta($metavar, $string) {
        if (empty($metavar)) {
            return false;
        }
        if (empty($string)) {
            return false;
        }

        if (!isset($metavar)) {
            return false;
        }
        if (is_array($metavar)) {
            foreach ($metavar as $name => $value) {
                if ((!empty($value)) && (is_string($value))) {
                    if (preg_match($string, $value)) {
                        return true;
                    }
                }
            }
        } elseif (is_string($metavar)) {
            if (preg_match($string, $metavar)) {
                return true;
            }
        }
        return false;
    }
   
}