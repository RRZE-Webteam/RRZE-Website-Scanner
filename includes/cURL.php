<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class cURL {
    var $headers;
    var $user_agent;
    var $compression;
    var $cookie_file;
    var $proxy;
    
    function cURL($cookies=TRUE,$cookie='cookies.txt',$compression='gzip',$proxy='') {
	$this->headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
	$this->headers[] = 'Connection: Keep-Alive';
	$this->headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
	$this->user_agent = 'Mozilla/4.0 (RRZE CheckBot)';
	$this->compression=$compression;
	$this->proxy=$proxy;
	$this->cookies=$cookies;
	$this->header = array();
	if ($this->cookies == TRUE) $this->cookie($cookie);
    }
    
    function cookie($cookie_file) {
	if (file_exists($cookie_file)) {
	    $this->cookie_file=$cookie_file;
	} else {
	    fopen($cookie_file,'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
	    $this->cookie_file=$cookie_file;
	    fclose($this->cookie_file);
	}
    }
    function get($url) {
	$res = array(
	    "content" => '',
	    "meta" => array(
		"http_code" => 0
	    ),
	);
	
	if (!$this->is_valid_url($url)) {
	    $res['meta']['http_code'] = -1;
	    return $res;
	}
	$process = curl_init($url);
	$this->url = $url;
	curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
	curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
	curl_setopt($process, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
	
	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($process, CURLOPT_HEADER, 1);
	
	if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
	if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
	
	curl_setopt($process,CURLOPT_ENCODING , $this->compression);
	curl_setopt($process, CURLOPT_TIMEOUT, 30);
	
	if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
	curl_setopt($process, CURLOPT_POSTREDIR, 7);
	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
	
	
	
	$response = curl_exec($process);
	$header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
	

	
	$header = substr($response, 0, $header_size);
	$body = substr($response, $header_size);
	
	$this->parse_header($header);
	$res['header'] = $this->header;
	$res['meta'] = curl_getinfo($process);
	
	
	$location = curl_getinfo($process, CURLINFO_EFFECTIVE_URL);
	if ($location !== $url) {
	    if (isset($res['header']['location'])) {
		if ($location !== $res['header']['location']) {
		    $res['meta']['location'] = $location;
		}
		
	    } else {
		$res['meta']['location'] = $location;
	    }
	    
	}
	
	
	$httpcode = curl_getinfo($process, CURLINFO_HTTP_CODE);
	curl_close($process);

	if ($httpcode>=200 && $httpcode<500) {
	    $res['content'] = $body;	   
	} else {
	     $res['content'] = '';
	}
	
	
	// if ((empty($res['content'])) && ($httpcode == 303) && ($res['meta']['redirect_url']) && ($res['meta']['redirect_url'] !== $url)) {
	//     return $this->get($res['meta']['redirect_url']);
	// }
	return $res;
    }
     
    function post($url,$data) {
	$res = array(
	    "content" => '',
	    "meta" => array(
		"http_code" => 0
	    ),
	);
	
	if (!$this->is_valid_url($url)) {
	    $res['meta']['http_code'] = -1;
	    return $res;
	}
	$process = curl_init($url);
	$this->url = $url;

	curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
	curl_setopt($process, CURLOPT_HEADER, 1);
	curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
	curl_setopt($process, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
	if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
	if ($this->cookies == TRUE) curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
	
	curl_setopt($process, CURLOPT_ENCODING , $this->compression);
	curl_setopt($process, CURLOPT_TIMEOUT, 30);
	
	if ($this->proxy) curl_setopt($process, CURLOPT_PROXY, $this->proxy);
	
	curl_setopt($process, CURLOPT_POSTFIELDS, $data);
	curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($process, CURLOPT_POST, 1);
	$return = curl_exec($process);
	$res['meta'] = curl_getinfo($process);
	$httpcode = curl_getinfo($process, CURLINFO_HTTP_CODE);
	curl_close($process);
	if ($httpcode>=200 && $httpcode<500) {
	    $res['content'] = $return;	   
	} else {
	    $res['content'] = '';
	}
	
	return $res;
    }
    function error($error) {
	echo "cURL Error: $error";
	die;
    }
    function get_ssl_info() {
	if (!$this->is_valid_url($this->url)) {
	    return false;
	}
	
	$contextOptions = array(
	    'ssl' => array(
		'verify_peer' => false, // You could skip all of the trouble by changing this to false, but it's WAY uncool for security reasons.
		'cafile' => '/etc/ssl/certs/cacert.pem',
		'CN_match' => 'fau.de', // Change this to your certificates Common Name (or just comment this line out if not needed)
		'ciphers' => 'HIGH:!SSLv2:!SSLv3',
		'disable_compression' => true,
		"capture_peer_cert"=> true
	    )
	);

	
	$orignal_parse = parse_url($this->url, PHP_URL_HOST);
	$get = stream_context_create($contextOptions);
	@ $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
	if ($read) {
	    $cert = stream_context_get_params($read);
	    $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
	    
	    $p = parse_url($this->url);
	    if ($p['scheme'] == 'http' ) {
		$this->url = preg_replace('/^http:/i', 'https:', $this->url);
	    }
	    
	    return $certinfo;
	}
	return false;
	
    }
    function is_valid_url($urlinput) {
	$url = filter_var($urlinput, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED|FILTER_FLAG_HOST_REQUIRED);

	if (empty($url) || (strlen($url) != strlen($urlinput))) {
	    return false;
	}
	return true;
    }

    public function parse_header($header) {
	 if (isset($header)) {
	    $haderlines = preg_split('/[\n\r]+/',$header);
	    if (!empty($haderlines)) {
		foreach ($haderlines as $line) {
		    $cur = trim($line);
		    if (!empty($cur)) {
			if (strpos($cur, ': ')) { 
			    list($name, $value) = explode(": ", $cur);
			    if ((!empty($name)) && (!empty($value))) {
				$name = strtolower($name);
				if ((isset($this->header[$name])) && (is_string($this->header[$name]))) {
				    if ($value !== $this->header[$name]) {
					// Nur wenn der neue Wert nicht gleich dem alten ist...
					$oldval = $this->header[$name];
					$this->header[$name] = array();
					$this->header[$name][] = $oldval;
					$this->header[$name][] = $value;
				    }
				} elseif  ((isset($this->header[$name])) && (is_array($this->header[$name]))) {   
				    $this->header[$name][] = $value;
				} else {
				    $this->header[$name] = $value;
				}
			    }
			}
		    }
		}
	    }
	    return true;
	 }
	 $this->header = array();
	 return false;
     }
     
     public function is_url_location_host($setnew = false) {
	 if ((isset($this->header['location'])) && is_string($this->header['location'])) {
	     $lu = parse_url($this->header['location']);     
	     $lo = parse_url($this->url);
 
	     
	     if (!isset($lu['host'])) {
		 // Kein Host angegeben => relativer Link, Umleitung bleibt beim selben Host
		 return true;
	     }
	     $wwwlu = 'www.'.$lu['host'];
	     $wwwlo = 'www.'.$lo['host'];
	     if ($lu['host'] == $lo['host']) {
		 return true;

	     } else {
		 
		 if ($lu['host'] == $wwwlo )  {
		     // Ist Umlenkung auf Domainhost ohne www, es bleibt also dieselbe Domain
		   if ($setnew) {
		       $this->url = $this->header['location']; 
		   }
		     return true;
		 }
		 if ($wwwlu == $lo['host'] )  {
		     // Ist Umlenkung auf Domainhost mit www, es bleibt also dieselbe Domain
		    if ($setnew) {
		       $this->url = $this->header['location']; 
		    }
		     return true;
		 }
		 
		 return false;
	     }
	 }
	 return true;
     }
}
