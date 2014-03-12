<?php
require_once("Config.php");
require_once('lib/Pusher.php');
include("Include.php");

header("Content-Type: text/javascript; charset=utf-8");
if(isset($_GET['type']) && isset($_SESSION['token'])){
	$token = $_SESSION['token'];
	switch ($_GET['type']) {
		case 'profile':
			getProfile();
			break;
		case 'topicsList':
			getTopicsList();
			break;
		case 'topic':
			if(isset($_GET["id"])){
				getTopic($_GET['id'], null, null, null);
			}else{
				printError();
				exit;
			}
			break;
		case 'sendMessage':
			if(isset($_GET["id"]) && isset($_GET['message'])){
				sendMessage($_GET['id'], $_GET['message']);
			}else{
				printError();
				exit;
			}
			break;
		case 'like':
			if(isset($_GET["topicId"]) && isset($_GET['postId'])){
				postLike($_GET['topicId'], $_GET['postId']);
			}else{
				printError();
				exit;
			}
			break;
		default:
			break;
	}
}
function getProfile(){
	global $token;
	$options = array('http' => array(
	    'method' => 'GET',
	    'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Bearer " . $token));
	$res = @file_get_contents(PROFILE_URI, false, stream_context_create($options));
	list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);
	if($status_code == '401'){
		printError();
	}else{
		$json = json_decode($res);
		
		$jsonObj = new stdClass();
		$jsonObj->status = "0";
		$jsonObj->name = $json->account->name;
		$jsonObj->id = $json->account->id;
		$jsonObj->imageUrl = $json->account->imageUrl;

		echo json_encode($jsonObj);
	}
}
//トピック一覧取得
function getTopicsList(){
	global $token;
	$options = array('http' => array(
	    'method' => 'GET',
	    'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Bearer " . $token));
	$res = @file_get_contents(TOPICS_URI, false, stream_context_create($options));
	list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);
	if($status_code == '401'){
		printError();
	}else{
		$json = json_decode($res);
		$array = array();
		$array = $json->topics;
		$count = count($array);
		$tempArray = array();
		for($i = 0; $i < $count; $i++){
			$tempObj = new stdClass();
			$tempObj->topic = new stdClass();
			$tempObj->topic->name = $array[$i]->topic->name;
			$tempObj->topic->id = $array[$i]->unread->topicId;
			array_push($tempArray, $tempObj);
		}
		$jsonObj = new stdClass();
		$jsonObj->status = "0";
		$jsonObj->topics = $tempArray;

		echo json_encode($jsonObj);
	}
}
//トピック内容取得
function getTopic($id, $from, $count, $direction){
	global $token;
	$fields = array();
	if($id == null){
		printError();
		exit;
	}
	if($from != null) { $fields += array('from' => $from); }
	if($count != null) { $fields += array('count' => $count); }
	if($direction == null) { $fields += array('direction' => "backward"); }

	$options = array('http' => array(
	    'method' => 'GET',
	    'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Bearer " . $token,
	    'content' => http_build_query($fields)));

	$res = @file_get_contents(TOPICS_URI."/".$id, false, stream_context_create($options));
	list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);
	if($status_code == '401'){
		printError();
	}else{
		$json = json_decode($res);
		$array = $json->posts;
		$count = count($array);
		$tempArray = array();
		for($i = 0; $i < $count; $i++){
			$tempObj = new stdClass();
			$tempObj->post = new stdClass();
			$tempObj->url = $array[$i]->url;
			$tempObj->likes = $array[$i]->likes;
			$tempObj->id = $array[$i]->id;
			$tempObj->createdAt = $array[$i]->createdAt;
			$tempObj->message = $array[$i]->message;
			$tempObj->account = new stdClass();
			$tempObj->account->name = $array[$i]->account->name;
			$tempObj->account->id = $array[$i]->account->id;
			$tempObj->account->imageUrl = $array[$i]->account->imageUrl;
			array_push($tempArray, $tempObj);
		}
		$jsonObj = new stdClass();
		$jsonObj->status = "0";
		$jsonObj->posts = $tempArray;
		$jsonObj->topic = $json->topic;

		$json = json_encode($jsonObj);
		echo $json;
	}
}

function sendMessage($id, $message){
	global $token;
	$url = TOPICS_URI."/".$id;
	$fields = array(
	    'message' => $message
	);
	$options = array('http' => array(
	    'method' => 'POST',
	    'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Bearer " . $token,
	    'content' => http_build_query($fields)));
	$res = @file_get_contents($url, false, stream_context_create($options));
	list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);
	if($status_code == '401'){
		printError();
	}else{
		$pusher = new Pusher('39daabf949c81c9b1bea', '3c2d2f70a09e11bf0ddd', '66619');
		$pusher->trigger('tt-channel', 'tt-event', $id );

		$jsonObj = new stdClass();
		$jsonObj->status = "0";
		$jsonObj->id = $id;
		$json = json_encode($jsonObj);
		echo $json;
	}
}

function postLike($topicId, $postId){
	global $token;
	$url = TOPICS_URI."/".$topicId."/posts/".$postId."/like";
	$options = array('http' => array(
	    'method' => 'POST',
	    'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Bearer " . $token));
	$res = @file_get_contents($url, false, stream_context_create($options));
	list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);

	if($status_code == '401'){
		printError();
	}else{
		$jsonObj = new stdClass();
		$jsonObj->status = "0";
		$json = json_encode($jsonObj);
		echo $json;
	}
}

//エラー出力
function printError(){
	$jsonObj = new stdClass();
	$jsonObj->status = "1";
	$json = json_encode($jsonObj);
	echo $json;
}
?>