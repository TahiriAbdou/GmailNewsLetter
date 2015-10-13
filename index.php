<?php 

session_start();

require 'vendor/autoload.php';

ini_set('memory_limit',-1);
set_time_limit(0);

if(!function_exists('dd')){
	function dd($var){
		echo '<pre>';
		var_export($var);
		echo '</pre>';
	}
}

$client = new Google_Client();
$client->setApplicationName("ServerKey");
$client->setDeveloperKey("AIzaSyC_r2mls0k4KtCKV1TazUJxTguNrb9FQxA");
$client->setClientId('852156071671-k3jmn0ngdbijp7t7l8c1r829gfpa5vqk.apps.googleusercontent.com');
$client->setClientSecret('Zxj2xpHTI3EmMAJVIGR6KuKe');
$client->setRedirectUri('http://127.0.0.1/google/');
$client->addScope('email');

//$client->addScope('profile');     
$client->addScope('https://mail.google.com');           
$client->setAccessType('offline');


$service = new Google_Service_Oauth2($client);

if (isset($_GET['code'])) {
	$client->authenticate($_GET['code']);
	$_SESSION['access_token'] = $client->getAccessToken();
	header('Location: ' . filter_var('http://127.0.0.1/google/', FILTER_SANITIZE_URL));
	exit;
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
	$client->setAccessToken($_SESSION['access_token']);
	if($client->isAccessTokenExpired()) {
    	$authUrl = $client->createAuthUrl();
	    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
	}
} else {
	$authUrl = $client->createAuthUrl();
}


if (isset($authUrl)){ 
	echo '<a class="login" href="' . $authUrl . '">login</a>';
} else {
    $user = $service->userinfo->get(); //get user info 
}

$service = new Google_Service_Gmail($client);



$optParamsGet = [];
$optParamsGet['format'] = 'full';

$notAllowed = ['facebook.com','facebookmail.com','bounce.linkedin.com','scoutcamp.bounces.google.com','plus.google.com'];

//dd(count($list));
/*
foreach ($list as $key => $value) {
	$m = $service->users_messages->get('me',$value->getId(),$optParamsGet);
	echo $m->getSnippet() . "<br>";
}
die;
*/

$optParams = [
	'labelIds'		=>	'INBOX',
	'maxResults'	=>	20,
	'q'				=>	'location de voiture',
	'pageToken'		=>	!empty($_GET['page']) ? $_GET['page'] : '',
];

$messages = $service->users_messages->listUsersMessages('me',$optParams);
$list = $messages->getMessages();
$nextToken = $messages->getNextPageToken();


while(!empty($list)){
	$mails = [];
	foreach ($list as $jk => $l) {
		$message = $service->users_messages->get('me',$l->getId(),$optParamsGet);
		$messagePayload = $message->getPayload();
		$headers = $message->getPayload()->getHeaders();
		foreach ($headers as $k => $v) {
			if($v['name']=='Received-SPF'){
				$pattern = '/[a-z\d._%+-]+@[a-z\d.-]+\.[a-z]{2,4}\b/i';
				preg_match($pattern,$v['value'],$matches);
				$email = current($matches);
				$domain = explode('@',$email);
				$domain = array_pop($domain);
				if(!in_array($domain,$notAllowed)){
					$mails[$jk] = current($matches);
				}
			}

			if($v['name']=='From'){
				if(isset($mails[$jk])) $mails[$jk] = trim(ucwords(strtolower($v['value']))).', '.$mails[$jk];
			}
		}
	}
	$mails = array_unique($mails);
	file_put_contents('mails', implode("\n",$mails).PHP_EOL, FILE_APPEND);
	$optParams['pageToken'] = $nextToken;
	$messages = $service->users_messages->listUsersMessages('me',$optParams);
	$list = $messages->getMessages();
	$nextToken = $messages->getNextPageToken();
}


// id 852156071671-k3jmn0ngdbijp7t7l8c1r829gfpa5vqk.apps.googleusercontent.com

// secret Zxj2xpHTI3EmMAJVIGR6KuKe
