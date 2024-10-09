<?php


// Automatische Laden von Klassen.
spl_autoload_register(function ($class) {
    $prefix = __NAMESPACE__;
    $base_dir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
    



if (empty($argv[1])) {
    echo "Usage: php get-website-meta.php <url> ([display-content-hash] [display-content-raw])\n";
    exit(1);
}
$url = $argv[1];

$display_content_hash = false;
if (!empty($argv[2])) {
    $display_content_hash = true;
}
$display_content_raw = false;
if (!empty($argv[3])) {
    $display_content_raw = true;
}

if (is_valid_url($url)) {
    echo "Checking URL ".$url."\n";
    
   parse_website($url);
} else {
   echo "URL invalid.\n";
   exit(1);   
}



function parse_website($url) {
    if (empty($url)) return false;
    
    global $display_content_hash;
    global $display_content_raw;
    
    $cc = new cURL();
    $data = $cc->get($url);
    $locationchange = $cc->is_url_location_host(false);


    if ($data['meta']['http_code'] < 0 ) {
        echo "Invalid URL to analyse: \"".$url."\"\n";
        exit;
    }

    echo "Status Code:        ".$data['meta']['http_code']."\n";
    if (($data['meta']['http_code'] >= 300) && ($data['meta']['http_code']<=303)) {
        echo "Redirect Location:  ".$data['meta']['location']."\n";
    }
    echo "connect_time:       ".$data['meta']['connect_time']."\n";
    echo "pretransfer_time:   ".$data['meta']['pretransfer_time']."\n";
    echo "starttransfer_time: ".$data['meta']['starttransfer_time']."\n";
    echo "Total Time:         ".$data['meta']['total_time']."\n";
    echo "Size:               ".$data['meta']['size_download']." Bytes\n";
    echo "primary_ip:         ".$data['meta']['primary_ip']."\n";
    echo "SSL:                ";
   
 

    $certinfo = $cc->get_ssl_info();
    if ($certinfo) {
    //	 var_dump($certinfo);
        if (isset($certinfo['issuer'])) {
            if (isset($certinfo['issuer']['O'])) {
                echo $certinfo['issuer']['O'];
            }
            if (isset($certinfo['issuer']['OU'])) {
                echo ", ".$certinfo['issuer']['OU'];
            }
            if (isset($certinfo['issuer']['CN'])) {
                echo ", ".$certinfo['issuer']['CN'];
            }
            echo "\n";
        }
        if (isset($certinfo['extensions'])) {
            if (isset($certinfo['extensions']['authorityKeyIdentifier'])) {
                echo "authorityKeyIdentifier: ".trim($certinfo['extensions']['authorityKeyIdentifier'])."\n";
            }
        ///     if (isset($certinfo['extensions']['certificatePolicies'])) {
        //	echo "\tcertificatePolicies: ".$certinfo['extensions']['certificatePolicies']."\n";
        //    }
            if (isset($certinfo['extensions']['subjectAltName'])) {
                echo "subjectAltName:     ".$certinfo['extensions']['subjectAltName']."\n";
            }
        }
        echo "\n";
    } else {
        echo "*Kein SSL Zugang verfügbar*\n";
    }    
    echo "\n";
    echo "Header: \n";
    foreach ($cc->header as $name => $value) {
        if (is_string($value)) {
            echo "\t$name: $value\n";
        } elseif (is_array($value)) {
            echo "\t$name = Array:\n";
            foreach ($value as $kv => $vv) {
            echo "\t\t".$kv.': '.$vv."\n";
            }
        }
    }
    echo "\n"; 
    
    if (empty($data['content'])) {
        echo "*ACHTUNG: Kein Inhalt erhalten*\n";
    }
    if (($locationchange) && ($data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 500)) {
	echo "CHECKE URL ".$cc->url."\n";
	$analyse = new Analyse($cc->url);
	$analyse->header = $cc->header;
	$analyse->httpstatus = $data['meta']['http_code'];
	$analyse->init($data);

	echo "Analyse:\n";
	
	echo "Title:              ".$analyse->title."\n";
	echo "URL:                ".$analyse->url."\n";
	if (isset($analyse->original_url)){
	    echo "Original URL:      ".$analyse->original_url;
	    echo "\n";
	}


	if (isset($analyse->header['location'])) {
	   if (is_array($analyse->header['location'])) {
		    echo "Location:\n";
		     foreach ($analyse->header['location'] as $msub => $mval) {
			 echo "\t\t".$msub." => ".$mval."\n";
		     }
	    } else {
		echo "Location:           ".$analyse->header['location']."\n";
	    }
	}
	
	if (isset($analyse->canonical)){
	    echo "Canonical URL:      ".$analyse->canonical;
	    echo "\n";
	}
	if (isset($analyse->lang)){
	    echo "Language:           ".$analyse->lang;
	    echo "\n";
	}
	if ((isset($analyse->generator)) && (isset($analyse->generator['name']))) {
	    echo "Generator:          ".$analyse->generator['name'];
	    if (isset($analyse->generator['version'])) {
		echo " (".$analyse->generator['version'].")";
	    }
	    echo "\n";
	    
	}
	if ((isset($analyse->template)) && ($analyse->template !== $analyse->generator)) {
	    echo "Template:           ".$analyse->template;
	    if (isset($analyse->template_version)) {
		echo " (".$analyse->template_version.")";
	    }
	    echo "\n";
	}
	//if (isset($analyse->meta) && isset($analyse->meta['description'])) {
	//    echo "Meta-Description:   ".$analyse->meta['description']."\n";
	//}
	if (isset($analyse->meta)) {
	    echo "\nMeta-Angaben (Response Head):\n";
	     foreach ($analyse->meta as $key => $value) {
		echo "\t".$key.":\t";
		if (is_string($value)) {
		    echo $value;
		} elseif (is_array($value)) {
		    echo "\n";
		     foreach ($value as $msub => $mval) {
			 echo "\t\t".$msub." => ".$mval."\n";
		     }
		}
		echo "\n";
	    }
	}
	if ((isset($analyse->favicon)) && (!empty($analyse->favicon['href']))) {
	    echo "Favicon:            ".$analyse->favicon['href'];
	    if (!empty($analyse->favicon['sizes'])) {
		echo " (".$analyse->favicon['sizes'].")";
	    }
	    echo "\n";
	}
	if (!empty($analyse->logosrc)) {
	    echo "Logo:               ".$analyse->logosrc."\n";
	}
	if ($analyse->toslinks) {
	    echo "\nRechtliche Angaben:\n";
	    foreach ($analyse->toslinks as $tos => $value) {
            echo "\t".$tos.":\t".$value['linktext']." (".$value['href'].")\n";
	    }
	}
	if (!empty($analyse->linkrels)) {
	    echo "\n<Link> im HTML <meta>:\n";
	      foreach ($analyse->linkrels as $i => $entry) {
            if (is_array($entry)) {
                  foreach ($entry as $name => $sub) {
                      echo "\t".$name.":\t";
                      if (is_array($sub)) {
                          foreach ($sub as $i => $val) {
                             echo "$i=\"$val\" ";
                          }
                          echo "\n";
                      } else {
                          echo $sub."\n";
                      }
                  }
            } else {
                echo "\t".$entry."\n";
            }
	    }
	}
	if (!empty($analyse->scripts)) {
	    echo "\nSkripten:\n";
	    foreach ($analyse->scripts as $i => $entry) {
            if (is_array($entry)) {
                foreach ($entry as $name => $sub) {
                    echo "\t".$name.":\t";
                    if (is_array($sub)) {
                        foreach ($sub as $i => $val) {
                             echo "\"$i\"=\"$val\" ";
                        }
                        echo "\n";
                    } else {
                        echo $sub."\n";
                    }
                }
            } else {
                echo "\t".$entry."\n";
            }
	    }
	}
	
	if (!empty($analyse->external)) {
         echo "\nExterne Ressourcen:\n";
	    foreach ($analyse->external as $link) {
                echo "\t".$link."\n";
	    }
	}
	
	if ($display_content_hash) {
	    var_dump($analyse->get_analyse_data());
	}
	if ($display_content_raw) {
	    echo "\n";
	    echo "################################################################################\n";
	    echo "# Content:\n";
	    echo "################################################################################\n";
	    
	    echo $analyse->content;
	    
	}
    } elseif (!$locationchange) {
        echo "Domain ".$cc->url." wird umgelenkt auf: ".$cc->header['location']."\n";
        echo "Bitte diese Domain gesondert analysieren.\n";
    } else {
        echo "Fehler beim Zugriff: ".$data['meta']['http_code']."\n";
    }
	
}
    
    
function is_valid_url($urlinput) {
    $url = filter_var($urlinput, FILTER_VALIDATE_URL);
    
    if (empty($url) || (strlen($url) != strlen($urlinput))) {
        return false;
    }
    return true;
}

