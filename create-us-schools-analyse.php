<?php


		
$config = [
    
    "index_jsonfile"   => 'current-us-schools.json',
    "analyse_file"   => 'current-us-schools-analyse.json'
];


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
    
$shortopts = "o:j:h:";
$longopts  = array(
    "output:",
    "json:"    // Optional value
);
$displayhelp = false;

// Script example.php
$options = getopt($shortopts,$longopts);




if (isset($options['j'])) {
   $jsonfile = $options['j'];
} elseif (isset($options['json'])) {
   $jsonfile = $options['json'];
} else {
    $jsonfile = $config["analyse_file"];
}
if (isset($options['h'])) {
   $displayhelp = true;
}
if ($displayhelp) {
    echo "Usage: php create-us-schools-analyse.php \n";
    
    echo "\tmit:\n";
    echo "\t-j|--json\n";
    echo "\t\tJSON Ausgabedatei\n";
    echo "\t\tDefault: $jsonfile\n";
    exit;
   
} 



echo "JSONFIle: \"$jsonfile\"\n";



$index = get_index();
$table = create_indextable($index);


if ($table) {
    $table = utf8ize($table);


	
	$dataarray['data'] = $table;
	$dataarray['meta']['date'] = date("d.m.Y h:i:s");
	$dataarray['meta']['total'] = count($table);
	$json = json_encode($dataarray);
	
       if($json === false || is_null($json)){
	    echo "JSON Encoding schlug fehl.\n";

	     //Get the last JSON error.
	    $jsonError = json_last_error();    
	    //If an error exists.
	    if($jsonError != JSON_ERROR_NONE){
		$error = 'Could not decode JSON! ';

		//Use a switch statement to figure out the exact error.
		switch($jsonError){
		    case JSON_ERROR_DEPTH:
			$error .= 'Maximum depth exceeded!';
		    break;
		    case JSON_ERROR_STATE_MISMATCH:
			$error .= 'Underflow or the modes mismatch!';
		    break;
		    case JSON_ERROR_CTRL_CHAR:
			$error .= 'Unexpected control character found';
		    break;
		    case JSON_ERROR_SYNTAX:
			$error .= 'Malformed JSON';
		    break;
		    case JSON_ERROR_UTF8:
			 $error .= 'Malformed UTF-8 characters found!';
		    break;
		    default:
			$error .= 'Unknown error!';
		    break;
		}
		throw new Exception($error);
	    }

	    exit;
       }
	if (file_put_contents($jsonfile, $json))
	    echo "JSON file $jsonfile created successfully...\n";
	else 
	    echo "Oops! Error creating json file $jsonfile...\n";
  
} else {
    echo "Ausgabe-Table leer\n";
}
exit;



function create_indextable($index) {   
    if (!isset($index)){
	return;
    }
    
    $cnt = 0;
    $maxcnt = 5000;
     
    if (isset($index['data'])) {
	$domainindex = $index['data'];
    } else {
	$domainindex = $index;
    }
    
    foreach ($domainindex as $num => $entry) {
	$line = '';
	$json_grunddata = $entry;
	    if ($cnt > $maxcnt) {
		break;
	    }
	    $cnt = $cnt +1;
	     
	
	  
	   
	    $json_grunddata['name'] = $entry['school.name'];
	    unset($json_grunddata['school.name']);
	    
	    if (isset($entry['school.school_url'])) {
		
		
	        $cc = new cURL();
		$url = sanitize_url($entry['school.school_url']);
		
		$data = $cc->get($url);
		$locationchange = $cc->is_url_location_host(true);
		$certinfo = $cc->get_ssl_info();
		
		echo $cc->url;
		$json_grunddata['url'] = $url;
		unset($json_grunddata['school.school_url']);
		$json_grunddata['httpstatus'] = $data['meta']['http_code'];
		
		
		if ($locationchange &&  $data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 500) {
		   
		    $analyse = new Analyse($cc->url);
		    $analyse->header = $cc->header;
		    @ $analyse->init($data);
    
		    echo " \t Ok\n";
		
		     $analysedata = $analyse->get_analyse_data();  
		     
		     $jsonadd =  array_merge($json_grunddata, $analysedata);
		     $json_data[] = $jsonadd;
	     
	        } elseif (!$locationchange) {
		    echo "\t (".$url.") wird umgelenkt auf: ".$cc->header['location']."\n";
		    
		    $json_grunddata['redirect'] = $cc->header['location'];
		    $json_data[] = $json_grunddata;
		    
		} else {
		    echo " \t Status Error (".$data['meta']['http_code'].")  bei ".$entry['name']." (".$entry['wiki-url'].")\n";
		    $json_data[] = $json_grunddata;
		}
		sleep(1);
	    } else {
		 echo " \t Keine URL bei ".$entry['name']." (".$entry['wiki-url']."). ";
		 echo "\n";
		$json_data[] = $json_grunddata;
	    }
    }
    return $json_data;
    
}



function get_index() {
    global $config;
    $data = file_get_contents($config['index_jsonfile']);

    $res = json_decode($data, true);

    return $res;
}



 function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                unset($d[$k]);
		$d[utf8ize($k)] = utf8ize($v);
            }
        } else if (is_object($d)) {
	    $objVars = get_object_vars($d);
	    foreach($objVars as $key => $value) {
	    $d->$key = utf8ize($value);
        }       
    } else if (is_string ($d)) {
	 return mb_convert_encoding($d, "UTF-8", "UTF-8");
    }
    return $d;
}

function sanitize_url($url) {
    if (!empty($url)) {
	$url = filter_var ( $url, FILTER_SANITIZE_URL);
	if (preg_match('/^http/i', $url, $matches)) {  
	    // starts with protokoll, ok
	} elseif  (preg_match('/^\/\//i', $url, $matches)) {    
	    // starts with double backslash
	     $url = 'http:'.$url;
	} else {
	    $url = 'http://'.$url;
	}
	return $url;
    }
}