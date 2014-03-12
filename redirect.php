<?php
require_once("Config.php");
require_once("TTOauth.php");
include("Include.php");

$code;//アプリコード
$token;//アプリtoken
$r_token;//アプリrefresh token
$ttOauth = new TTOauth();//typetalk oAuthオブジェクト


//諸々判定
if(isset($_GET['code'])){
	$_SESSION["tt_code"] = $_GET['code'];
	header("Location: redirect.php");
	exit;
}

//tt_codeがない場合
if(!isset($_SESSION["tt_code"])){
	backToOAuth();
}else{
	$code = $_SESSION["tt_code"];
	//tokenセットありの場合
	if(isset($_SESSION["tt_token"]) && isset($_SESSION["tt_r_token"])){
		$token = $_SESSION["tt_token"];
		$r_token = $_SESSION["tt_r_token"];
	//tokenなし、refresh tokenありの場合
	}else if(!isset($_SESSION["tt_token"]) && isset($_SESSION["tt_r_token"])){
		$tokenObj = $ttOauth->updateAccessToken();
		$status = $tokenObj->status;
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
		}else{
			backToOAuth();
		}
	//token関係なしの場合
	}else{
		$tokenObj = $ttOauth->getToken($code);
		$status = $tokenObj->status;
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
		}else{
			backToOAuth();
		}
	}
}
function backToOAuth(){
	global $ttOauth;
	$ttOauth->deleteCookies();
	header("Location: Authorize.php");
}
?>
<html>
<head>
<meta content="text/html" charset="UTF-8">
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0" />
<script type="text/javascript" src="http://js.pusher.com/2.1/pusher.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>
<script type="text/javascript" src="js/Config.js"></script>
<script type="text/javascript" src="js/Main.js"></script>
<link rel="stylesheet" type="text/css" href="css/Main.css">
</head>
<body>
	<div id="list"></div>
	<form id="messageForm">
		<input type="text" name="message" id="inputMessage"><br>
		<button>送信！</button>
	</form>
	<div id="timeline"></div>
</body>
</html>