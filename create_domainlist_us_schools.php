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
	    "school.state_fipsv",
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
    ],
    "output_jsonfile"   => 'current-us-schools.json',
    "outjson"	=> true,
];
$degree_award = [
    "0" => "Non-degree-granting",
    "1" => "Certificate degree",
    "2" => "Associate degree",
    "3" => "Bachelor's degree",
    "4" => "Graduate degree",
];


$ownership = [
    "1"	 => "Public",
    "2"	 => "Private nonprofit",
    "3"	 => "Private for-profit"
];

$fips_codes = [
    "1" => "Alabama",
    "2" => "Alaska",
    "4" => "Arizona",
    "5" => "Arkansas",
    "6" => "California",
    "8" => "Colorado",
    "9" => "Connecticut",
    "10" => "Delaware",
    "11" => "District of Columbia",
    "12" => "Florida",
    "13" => "Georgia",
    "15" => "Hawaii",
    "16" => "Idaho",
    "17" => "Illinois",
    "18" => "Indiana",
    "19" => "Iowa",
    "20" => "Kansas",
    "21" => "Kentucky",
    "22" => "Louisiana",
    "23" => "Maine",
    "24" => "Maryland",
    "25" => "Massachusetts",
    "26" => "Michigan",
    "27" => "Minnesota",
    "28" => "Mississippi",
    "29" => "Missouri",
    "30" => "Montana",
    "31" => "Nebraska",
    "32" => "Nevada",
    "33" => "New Hampshire",
    "34" => "New Jersey",
    "35" => "New Mexico",
    "36" => "New York",
    "37" => "North Carolina",
    "38" => "North Dakota",
    "39" => "Ohio",
    "40" => "Oklahoma",
    "41" => "Oregon",
    "42" => "Pennsylvania",
    "44" => "Rhode Island",
    "45" => "South Carolina",
    "46" => "South Dakota",
    "47" => "Tennessee",
    "48" => "Texas",
    "49" => "Utah",
    "50" => "Vermont",
    "51" => "Virginia",
    "53" => "Washington",
    "54" => "West Virginia",
    "55" => "Wisconsin",
    "56" => "Wyoming",
    "60" => "American Samoa",
    "64" => "Federated States of Micronesia",
    "66" => "Guam",
    "69" => "Northern Mariana Islands",
    "70" => "Palau",
    "72" => "Puerto Rico",
    "78" => "Virgin Islands"
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




if (empty($config['api_key']) || ($config['api_key'] == 'DEMO_KEY')) {
    
    if ($argc !== 2) {
	echo "Usage: php crate-domainlist_us_schools.php <api key>\n";
	
	echo "\nGet your API Key at: https://api.data.gov/ \n";
	exit(1);
    }
    $apikey = $argv[1];
    if (!empty($apikey)) {
	$config['api_key'] = urlencode($apikey);
    }
}


$list = get_hochschullist_from_api();


if ($list) {
    if ($config['outjson']) {
	
	$dataarray['data'] = $list;
	$dataarray['meta']['date'] = date("d.m.Y h:i:s");
	$dataarray['meta']['total'] = count($list);
	$json = json_encode($dataarray);
	$jsonfile = $config['output_jsonfile'];
	if (file_put_contents($jsonfile, $json)) {
	    echo "JSON file $jsonfile created successfully.\n";
	} else {
	    echo "Oops! Error creating json file $jsonfile...\n";
	}
    }
}

exit;


function get_hochschullist_from_api() {
    global $config;
    $page = 0;
    
    $thisurl = make_apiurl($page);
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
			
			$thisurl = make_apiurl($i);
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



function make_apiurl($page = 0) {
     global $config;
     
     $url = $config['api_url'];
     
     $api_key = $config['api_key'];
     

     if (($api_key) && ($api_key !== 'DEMO_KEY')) {
	 
	 
	 $url .= '?api_key='.$api_key;
	 
	 foreach ($config['filter'] as $filter => $value) {
	     $url .= '&'.$filter.'='.$value;
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


