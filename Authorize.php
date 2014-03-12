<?php
require("Config.php");
$url;
if (isset($_COOKIE["tt_code"])){
	$url = REDIRECT_URI;
}else{
	$data = array(
	  'client_id' => CLIENT_ID,
	  'redirect_uri' => REDIRECT_URI,
	  'scope' => 'topic.read,topic.post,my',
	  'response_type' => 'code');

	$url = AUTHORIZE_URI."?" . http_build_query($data);
}
header("Location: ".$url, 301);
?>