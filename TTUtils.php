<?php
//ステータスコード、エラーコードなんかがあまいので要検討
require_once("Include.php");
require_once('TTOauth.php');
require_once('lib/Pusher.php');
$ttOauth = new TTOauth();//typetalk oAuthオブジェクト

//JSONとして吐き出すためヘッダーを追加
header("Content-Type: text/javascript; charset=utf-8");

//getでtypeがあることと、セッションにtokenがあることを確認。
//まだ処理が完璧じゃない。
if(isset($_GET['type']) && isset($_SESSION['tt_token'])){
	$token = $_SESSION['tt_token'];
	$r_token = $_SESSION['tt_token'];
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
}else{
	unsetSession();
	printError();
}

//プロフィールの取得
function getProfile(){
	global $token,$r_token,$ttOauth;

	$options = array('http' => array(
	    'method' => 'GET',
	    'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Bearer " . $token));
	$res = @file_get_contents(PROFILE_URI, false, stream_context_create($options));
	list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);
	//var_dump($res);
	//認証エラーの時
	if($status_code == '401'){
		$tokenObj = $ttOauth->updateAccessToken();
		$status = $tokenObj->status;
		//access_tokenアップデートが成功したら
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
			getProfile();
		//access_tokenアップデートが失敗したら
		}else{
			backToOAuth();
		}
	//戻り値が空の時（access_token期限切れ？）
	}else if(is_null($res)){
		//access_tokenアップデート
		$tokenObj = $ttOauth->updateAccessToken();
		$status = $tokenObj->status;
		//access_tokenアップデートが成功したら
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
			getProfile();
		//access_tokenアップデートが失敗したら
		}else{
			backToOAuth();
		}
	//取得成功
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
	global $token,$r_token,$ttOauth;
	$options = array('http' => array(
	    'method' => 'GET',
	    'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Bearer " . $token));
	$res = @file_get_contents(TOPICS_URI, false, stream_context_create($options));
	list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);
	//認証エラーの時
	if($status_code == '401'){
		//access_tokenアップデート
		$tokenObj = $ttOauth->updateAccessToken();
		$status = $tokenObj->status;
		//access_tokenアップデートが成功したら
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
			getTopicsList();
		//access_tokenアップデートが失敗したら
		}else{
			backToOAuth();
		}
	//戻り値が空の時（access_token期限切れ）
	}else if(is_null($res)){
		//access_tokenアップデート
		$tokenObj = $ttOauth->updateAccessToken();
		$status = $tokenObj->status;
		//access_tokenアップデートが成功したら
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
			getTopicsList();
		//access_tokenアップデートが失敗したら
		}else{
			backToOAuth();
		}
	//取得成功
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
	global $token,$r_token,$ttOauth;
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
	    //'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Bearer ",
	    'content' => http_build_query($fields)));

	$res = @file_get_contents(TOPICS_URI."/".$id, false, stream_context_create($options));
	list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);
	if($status_code == '401'){
		//access_tokenアップデート
		$tokenObj = $ttOauth->updateAccessToken();
		$status = $tokenObj->status;
		//access_tokenアップデートが成功したら
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
			getTopic($id, $from, $count, $direction);
		//access_tokenアップデートが失敗したら
		}else{
			backToOAuth();
		}
	}else if(is_null($res)){
		//access_tokenアップデート
		$tokenObj = $ttOauth->updateAccessToken();
		$status = $tokenObj->status;
		//access_tokenアップデートが成功したら
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
			getTopic($id, $from, $count, $direction);
		//access_tokenアップデートが失敗したら
		}else{
			backToOAuth();
		}
	//取得成功
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

//メッセージ送信
//画像添付とか未処理。
function sendMessage($id, $message){
	global $token,$r_token,$ttOauth;
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
		$tokenObj = $ttOauth->updateAccessToken();
		$status = $tokenObj->status;
		//access_tokenアップデートが成功したら
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
			sendMessage($id, $message);
		//access_tokenアップデートが失敗したら
		}else{
			backToOAuth();
		}
	}else if(is_null($res)){
		//access_tokenアップデート
		$tokenObj = $ttOauth->updateAccessToken();
		$status = $tokenObj->status;
		//access_tokenアップデートが成功したら
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
			sendMessage($id, $message);
		//access_tokenアップデートが失敗したら
		}else{
			backToOAuth();
		}
	//取得成功
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

//Like送信
function postLike($topicId, $postId){
	global $token,$r_token,$ttOauth;
	$url = TOPICS_URI."/".$topicId."/posts/".$postId."/like";
	$options = array('http' => array(
	    'method' => 'POST',
	    'header' => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: Bearer " . $token));
	$res = @file_get_contents($url, false, stream_context_create($options));
	list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);

	if($status_code == '401'){
		$tokenObj = $ttOauth->updateAccessToken();
		$status = $tokenObj->status;
		//access_tokenアップデートが成功したら
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
			postLike($topicId, $postId);
		//access_tokenアップデートが失敗したら
		}else{
			backToOAuth();
		}
	}else if(is_null($res)){
		//access_tokenアップデート
		$tokenObj = $ttOauth->updateAccessToken();
		$status = $tokenObj->status;
		//access_tokenアップデートが成功したら
		if($status == "0"){
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
			postLike($topicId, $postId);
		//access_tokenアップデートが失敗したら
		}else{
			backToOAuth();
		}
	//取得成功
	}else{
		$jsonObj = new stdClass();
		$jsonObj->status = "0";
		$json = json_encode($jsonObj);
		echo $json;
	}
}

//エラー出力
function printError($status = null){
	if(is_null($status)){
		$status = 1;
	}
	$jsonObj = new stdClass();
	$jsonObj->status = $status;
	$json = json_encode($jsonObj);
	echo $json;
}
?>