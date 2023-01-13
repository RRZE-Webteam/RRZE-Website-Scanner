<?php


// Input: Directory in which analse files from several monthes are stored in 
// syntax:   $filenameprefix-$M-$y.json
// it will read all files and make a cms datafile that can be used
// to create a trand statistic for the cms evolution


$prefix_analyse_file = "hochschulen-analyse";
$analyse_dir = "./";
$output_file = 'hochschulen-analyse-merge.json';
	
$shortopts = "d:h:n:o:";
$longopts  = array();
$displayhelp = false;
$string_unknown_traeger = 'unbekannt';

// Script example.php
$options = getopt($shortopts,$longopts);


if (isset($options['d'])) {
   $analyse_dir = $options['d'];
}
if (isset($options['n'])) {
   $prefix_analyse_file = $options['n'];
}

if (isset($options['o'])) {
   $output_file = $options['o'];
} 

if (isset($options['h'])) {
   $displayhelp = true;
}
if ($displayhelp) {
    echo "Usage: php merge-analyse-files.php -d /somewhere/somedir/ -n analysefile -o outputfile\n";
    
    echo "\with:\n";
    echo "\t-d dir\n";
    echo "\t\tLocation of Analyse Files\n";
    echo "\t\tDefault: $analyse_dir\n";
    echo "\t-n fileprefix\n";
    echo "\t\tPrefix Filename. Is followed by -Month-Year.json\n";
    echo "\t\t                 e.g.: $prefix_analyse_file-07.22.json\n";
    echo "\t\tDefault: $prefix_analyse_file\n";
    echo "\t-o filename\n";
    echo "\t\tWrite merge to a JSON-file\n";
    echo "\t\tDefault: $output_file\n";    
    exit;
   
} 

echo "Reading in directory $analyse_dir / $prefix_analyse_file:\n";

$filelist = getAnalyseFilesMonJahr($analyse_dir,$prefix_analyse_file);
$filelist = getDatafromFiles($filelist);


$status = save_jsonfile($output_file,$filelist);
if ($status) {
    echo "Merge file created: $output_file\n";
} else {
    echo "Could not write JSON file\n";
}


exit;
/******************************************************************************
 * Functions
 *****************************************************************************/ 
function save_jsonfile($filename, $data) {
    $data = utf8ize($data);
    $json = json_encode(  array('data' => $data) );
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

    if (file_put_contents($filename, $json)) {
	return true;
    } else {    
	return false;
    }	
}


function getDatafromFiles($filelist) {
    global $string_unknown_traeger;
    if (empty($filelist)) {
	return;
    }
    
    
    foreach ($filelist['data'] as $key => $data) {
	if (!empty($data['filename'])) {
  
	    $filedata = file_get_contents($data['filename']);	    
	    if ($filedata !==false) {
		$analyse = json_decode($filedata, true);
		$res = array(
		    "counter"	=> [
			'num'	=> 0,
			'status'    => [],
			'traegerschaft' => [],
			'generator' => [],
			'generator-traegerschaft' => []
		    ]
		);


		foreach ($analyse['data'] as $num => $hochschule) {
		    $res['counter']['num']++;		  
		    $traeger = '';
		    
		    if (!empty($hochschule['httpstatus'])) {
			if (!isset($res['counter']['status'][$hochschule['httpstatus']])) {
			    $res['counter']['status'][$hochschule['httpstatus']] = 0;
			}
			$res['counter']['status'][$hochschule['httpstatus']]++;
		    }
		    
		   if (!empty($hochschule['trgerschaft'])) {
			$traeger = sanitize_traeger($hochschule['trgerschaft']);
			
			if (($traeger == $string_unknown_traeger) && (!empty($hochschule['typ']['traeger']))) {
			     $traeger = sanitize_traeger($hochschule['typ']['traeger']);
			}
		   } else {
		       if (!empty($hochschule['typ']['traeger'])) {
			    $traeger = sanitize_traeger($hochschule['typ']['traeger']);
		       } else {
			   $traeger = $string_unknown_traeger;
		       }
		   }
			if (!isset($res['counter']['traegerschaft'][$traeger])) {
			    $res['counter']['traegerschaft'][$traeger] = 0;
			}
			$res['counter']['traegerschaft'][$traeger]++;
		   
		   
		    if (!empty($hochschule['generator'])) {
			if (!isset($res['counter']['generator'][$hochschule['generator']['name']])) {
			    $res['counter']['generator'][$hochschule['generator']['name']] = 0;
			}
			$res['counter']['generator'][$hochschule['generator']['name']]++;
			
			if (!isset($res['counter']['generator-traegerschaft'][$traeger][$hochschule['generator']['name']])) {
			    $res['counter']['generator-traegerschaft'][$traeger][$hochschule['generator']['name']] = 0;
			}
			$res['counter']['generator-traegerschaft'][$traeger][$hochschule['generator']['name']]++;
			
		    } else {
			if (!isset($res['counter']['generator']['unknown'])) {
			    $res['counter']['generator']['unknown'] = 0;
			}
			$res['counter']['generator']['unknown']++;
			if (!isset($res['counter']['generator-traegerschaft'][$traeger]['unknown'])) {
			    $res['counter']['generator-traegerschaft'][$traeger]['unknown'] = 0;
			}
			$res['counter']['generator-traegerschaft'][$traeger]['unknown']++;
		    }
			
		   $filelist['data'][$key]['analyse'] = $res;
		}
		
	    }
	}
	
    }
    return $filelist;
}


function sanitize_traeger($traeger = '') {
    global $string_unknown_traeger;
    $valid = array("privat", "staatlich", "konfessionell");
    if (!empty($traeger)) {	
	if ($traeger == 'öffentlich-rechtlich') {
	    $traeger = "staatlich";
	}
	if ($traeger == 'kirchlich') {
	    $traeger = "konfessionell";
	}
	$traeger = preg_replace('/[^a-z]+/i', '', $traeger);
	$traeger = strtolower($traeger);
    }
   
    
    if (in_array($traeger, $valid)) {
	return $traeger;
    }
   
    $found = $string_unknown_traeger;
    foreach ($valid as $search) {
	$sgrep = '/'.$search.'/i';
	if (preg_match ($sgrep, $traeger, $m)) {
	    $found = $search;
	    break;
	}
    }
    return $found;
}



function getAnalyseFilesMonJahr($dir = '', $fileprefix = '') {
    global $prefix_analyse_file;
    global $analyse_dir;
    
    $listmonthes = array();
    $num = 0;
    
	if (empty($dir)) {
	    $dir = $analyse_dir;
	}    
	if (empty($fileprefix)) {
	    $fileprefix =  $prefix_analyse_file;
	}

	$fileprefix = '/^'.preg_quote($fileprefix).'-([0-9]+)\.([0-9]+)\./i';

	$handle = opendir($dir);
	$res = array();
	    
	while (false !== ($file = readdir($handle))) {
	    if ($file == '.' || $file == '..') {
		continue;
	    }
	    $filepath = $dir == '.' ? $file : $dir . '/' . $file;
	    if (is_link($filepath)) {
		continue;
	    } elseif (is_file($filepath)) {
		if ( preg_match($fileprefix, $file, $matches) ) {
		    $num++;
		    $key = '20'.$matches[2].'-'.$matches[1];
		    $name = getMonth($matches[1]).' 20'.$matches[2];
		    $res['data'][$key]['monthname'] = $name;
		    $res['data'][$key]['month'] = $matches[1];
		    $res['data'][$key]['year'] = $matches[2];
		    $res['data'][$key]['filename'] = $filepath;

		   $listmonthes[] = $key;
		}
	    }
	}
	if (!empty($listmonthes)) {
	    natsort($listmonthes);
	}
	
	$res['monthes'] = $listmonthes;
	$res['start'] = $listmonthes[0];
	$res['end'] = end($listmonthes);
	$res['filenum'] = $num;
	   
	closedir($handle);
	return $res;

}

function getMonth($month) {
    $monthes = array(
	"Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"
    );
    $m = intval($month) -1;
    return $monthes[$m];
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