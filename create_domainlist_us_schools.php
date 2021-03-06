<?php

/* 
 * Gets US School list from Opendata Portal of us.gov
 * See:  https://collegescorecard.ed.gov/data/documentation/
 *      https://github.com/RTICWDT/open-data-maker/blob/master/API.md 
 */

$config = [
   
    "api_url"	=> 'https://api.data.gov/ed/collegescorecard/v1/schools',
    "api_key" => '',
	// Use your own key here. Register at https://api.data.gov
    "result_fields" =>[
	    "id",
	    "school.name",
	    "school.city",
	    "school.state",
	    "school.school_url",
	    "school.degrees_awarded.highest",
	    "school.ownership",
	    "school.state_fips",
	    "school.online_only",
	    "latest.student.size",
	    "latest.student.grad_students"
	    
	],
    "per_page"	=> 100,
	// results per request.
	// number of page is returned in "page"-attribut, tital number in "total"
    "filter"	=> [
	  "school.operating"	=> 1,
	// Flag for currently operating institution, 0=closed, 1=operating
	 "school.degrees_awarded.highest" => '3,4',
	// Filter for schools with their hightest degree. 
	"school.ownership"  => 1,
    ],
    "output_jsonfile"   => 'current-us-schools.json',
    "outjson"	=> true,
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


$shortopts = "o:j:h:a:";
$longopts  = array(
    "owner:",
    "api:",
    "json:"    // Optional value
);
$displayhelp = false;

$options = getopt($shortopts,$longopts);


if (isset($options['o'])) {
   $ownership = $options['o'];
} elseif (isset($options['owner'])) {
   $ownership = $options['owner'];
} else {
   $ownership = 1;
}

if (isset($options['j'])) {
   $jsonfile = $options['j'];
} elseif (isset($options['json'])) {
   $jsonfile = $options['json'];
} else {
    $jsonfile = $config["output_jsonfile"];
}

if (isset($options['a'])) {
   $apikey = $options['a'];
} elseif (isset($options['api'])) {
   $apikey = $options['api'];
} else {
    $apikey = $config['api_key'];
}
$config['api_key'] = urlencode($apikey);

if (empty($apikey) || ($apikey == 'DEMO_KEY')) {
     $displayhelp = true;
}

if (isset($options['h'])) {
   $displayhelp = true;
}
if ($displayhelp) {
    echo "Usage: php create-us-schools-analyse.php \n";
   
    echo "\t-a|--api\n";
    echo "\t\tAPI-KEY: String (Get your API Key at: https://api.data.gov/ )\n";   
    echo "\t-j|--json\n";
    echo "\t\tJSON Ausgabedatei\n";
    echo "\t\tDefault: $jsonfile\n";
    echo "\t-o|--owner\n";
    echo "\t\tOwnership: 1 (public), 2 (privat non profit), 3 (privat for profit)\n";
    exit;
   
} 


$list = get_hochschullist_from_api($ownership);


if ($list) {
    if ($config['outjson']) {
	
	$dataarray['data'] = $list;
	$dataarray['meta']['date-list'] = date("d.m.Y H:i:s");
	$dataarray['meta']['total'] = count($list);
	$json = json_encode($dataarray);
	if (file_put_contents($jsonfile, $json)) {
	    echo "JSON file $jsonfile created successfully.\n";
	} else {
	    echo "Oops! Error creating json file $jsonfile...\n";
	}
    }
}

exit;


function get_hochschullist_from_api($ownership) {
    global $config;
    $page = 0;
    
    
    $thisurl = make_apiurl($page, $ownership);
    echo $thisurl."\n";
    
        $cc = new cURL();
	$data = $cc->get($thisurl);
	$res = array();
	
	 if ($data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 400) {
		$pagedata = json_decode($data['content'], true);
	
		if (($pagedata['metadata']['total']) && ($pagedata['metadata']['total'] > $config['per_page'])) {
		    // we have to loop through all pages
		    
		   
		    echo "Total Data: ".$pagedata['metadata']['total']."\n";
		    
		    
		    // move data in Output array. 
		    // use school id as key instead of number
		    
		    if ($pagedata['results']) {
			foreach ($pagedata['results'] as $num => $fielddata) {
			    if (isset($fielddata['id'])) {
				$res[$fielddata['id']] = $fielddata;
			    } else {
				echo "No school id found at entry $num!\n";
			    }
			}
		    }
		    
		    
		    
		    $num_requests_float = (($pagedata['metadata']['total'] - $config['per_page']) / $config['per_page']);
		    if (intval($num_requests_float) !== $num_requests_float) {
			$maxreq = intval($num_requests_float) +1;
		    } else {
			$maxreq = intval($num_requests_float); 
		    }
		    echo "have to get ".$maxreq." additional request pages\n";
		    
		    
		    for ($i = 1; $i <= $maxreq; $i++) {
			
			$thisurl = make_apiurl($i,$ownership);
			echo "Getting page URL ".$i.": ".$thisurl."\n";
			
			
			$data = $cc->get($thisurl);
			if ($data['meta']['http_code'] >= 200 && $data['meta']['http_code'] < 400) {
			     $thispagedata = json_decode($data['content'], true);
			     
				if ($thispagedata['results']) {
				    foreach ($thispagedata['results'] as $num => $fielddata) {
					if (isset($fielddata['id'])) {
					    $res[$fielddata['id']] = $fielddata;
					} else {
					    echo "No school id found at entry $num on page $i!\n";
					}
				    }
				}
			     
			}
			sleep(1);
			// cause we are friendly
			
			
			
		    }
		    
		}
	
	} else {
	    echo "Error on reading ".$thisurl."\n";
	    return;
	}
	return $res;
}



function make_apiurl($page = 0, $ownership) {
     global $config;
     
     $url = $config['api_url'];
     
     $api_key = $config['api_key'];
     

     if (($api_key) && ($api_key !== 'DEMO_KEY')) {
	 
	 
	 $url .= '?api_key='.$api_key;
	 
	 foreach ($config['filter'] as $filter => $value) {
	     
	     if (($filter == 'school.ownership') && ($ownership > 0)) {
		  $url .= '&school.ownership='.$ownership;
	     } else {
		  $url .= '&'.$filter.'='.$value;
	     }
	    
	 }
	 
	 if ($config['result_fields']) {
	    
	     $fields = '';
	     foreach ($config['result_fields'] as $fieldname) {
		 $fields .= $fieldname.',';
	    }
	    $fields = trim($fields,',');
	    if ($fields) {
		 $url .= '&fields='.$fields;
	    }
	 }
	 if ($config['per_page']) {
	      $url .= '&per_page='.$config['per_page'];
	 }
	 if ($page > 0) {
	     $url .= '&page='.$page;
	 }
	 return $url;
	 
     } else {
	 echo "PLEASE REGISTER AN API KEY FIRST!\n";
	 echo "Register at: https://api.data.gov/ \n";
	 echo "\nAfter you got your API key, insert it into the Config-Array in this file.\n";
	 exit;
     }
}


