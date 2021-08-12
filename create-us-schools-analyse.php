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
    
$shortopts = "o:i:h:";
$longopts  = array(
    "out:",
    "input:"    // Optional value
);
$displayhelp = false;

$options = getopt($shortopts,$longopts);



if (isset($options['o'])) {
   $jsonfile = $options['o'];
} elseif (isset($options['out'])) {
   $jsonfile = $options['out'];
} else {
    $jsonfile = $config["analyse_file"];
}
if (isset($options['i'])) {
   $inputfile = $options['i'];
} elseif (isset($options['input'])) {
   $inputfile = $options['input'];
} else {
    $inputfile = $config["index_jsonfile"];
}

if (isset($options['h'])) {
   $displayhelp = true;
}
if ($displayhelp) {
    echo "Usage: php create-us-schools-analyse.php \n";
    
    echo "\t-o|--out\n";
    echo "\t\tJSON Ausgabedatei\n";
     echo "\t\tDefault: ".$config["analyse_file"]."\n";
    echo "\t-i|--input\n";
    echo "\t\tJSON Input Index\n";
    echo "\t\tDefault: ".$config["index_jsonfile"]."\n";
    exit;
   
} 

$index = get_index($inputfile);
$table = create_indextable($index);


if ($table) {
    $table = utf8ize($table);


	
	$dataarray['data'] = $table;
	$dataarray['meta']['date-analyse'] = date("d.m.Y H:i:s");
	$dataarray['meta']['total'] = count($table);
	if (isset($index['meta']['date-list'])) {
		$dataarray['meta']['date-list'] = $index['meta']['date-list'];
	}
	$json = json_encode($dataarray);
	
       if($json === false || is_null($json)){
	    echo "JSON Encoding failed.\n";

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
    
    $cnt = $sc = 0;
    $maxcnt = 5000;
     
    if (isset($index['data'])) {
	$domainindex = $index['data'];
    } else {
	$domainindex = $index;
    }
    $total = 0;
    if (isset($index['meta']['total'])) {
	$total = $index['meta']['total'];
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
		
		echo $cnt;
		
		if ($total>0) {
		    echo "/".$total;
		} else {
		    echo ".";
		}
		echo "\t";
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
		     	     
		     if (isset($analysedata['title']))
			$json_grunddata['title'] = $analysedata['title'];
		     
		     if (isset($analysedata['logo_src']))
			$json_grunddata['logo_src'] = $analysedata['logo_src'];
		     
		     if (isset($analysedata['favicon_src']))
			$json_grunddata['favicon_src'] = $analysedata['favicon_src'];
		     
		     if (isset($analysedata['content']) && isset($analysedata['content']['lang']))
			$json_grunddata['content']['lang'] = $analysedata['content']['lang'];
		     
		      if (isset($analysedata['content']) && isset($analysedata['content']['tos']))
			$json_grunddata['content']['tos'] = $analysedata['content']['tos'];
		      
		     if (isset($analysedata['generator'])) 
			 $json_grunddata['generator'] = $analysedata['generator'];
		     
		     if (isset($analysedata['template']))
			$json_grunddata['template'] = $analysedata['template'];
		     
		     $json_data[] = $json_grunddata;
	     
	        } elseif (!$locationchange) {
		    echo "\t (".$url.") redirect to: ".$cc->header['location']."\n";
		    
		    $json_grunddata['redirect'] = $cc->header['location'];
		    $json_data[] = $json_grunddata;
		    
		} else {
		    echo " \t Status Error (".$data['meta']['http_code'].")  at ".$entry['school.name']." (".$url.")\n";
		    $json_data[] = $json_grunddata;
		}
		
		$sc++;
		if ($sc > 5) {
		    $sc =0;
		    sleep(1);
		    // sleep every 5 seconds to be friendly to our network :)
		}
		
	    } else {
		 echo " \t No URL at ".$entry['name'];
		 echo "\n";
		$json_data[] = $json_grunddata;
	    }
    }
    return $json_data;
    
}



function get_index($inputfile) {
    global $config;
    $data = file_get_contents($inputfile);

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
	$url = trim($url,"/");
	return $url;
    }
}