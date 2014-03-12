<?php
require_once("Config.php");
class TTOauth {
	//token取得
	public function getToken($code){
		$obj = new stdClass();
		$fields = array(
		    'client_id' => CLIENT_ID,
		    'client_secret' => CLIENT_SECRET,
		    'redirect_uri' => REDIRECT_URI,
		    'grant_type' => 'authorization_code',
		    'code' => $code
		);
		$options = array('http' => array(
		    'method' => 'POST',
		    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
		    'content' => http_build_query($fields)));

		$json = json_decode(@file_get_contents(ACCESS_TOKEN_URI, false, stream_context_create($options)));
		if($json == null){
			$obj->status= "1";
			$obj->token= "";
			$obj->r_token= "";
		}else{
			$token = $json->access_token;
			$r_token = $json->refresh_token;
			setcookie('tt_token', $token, time() + (60 * 60));
			setcookie('tt_r_token', $r_token, time() + (60 * 60 * 24 * 30));
			$obj->status = "0";
			$obj->token = $token;
			$obj->r_token = $r_token;
		}
		return $obj;
	}
	//リフレッシュtokenでaccess tokenを更新
	public function updateAccessToken(){
		$obj = new stdClass();
		if(isset($_COOKIE["tt_r_token"])){//refresh token確認
			$r_token = $_COOKIE['tt_r_token'];
			$fields = array(
			    'client_id' => CLIENT_ID,
			    'client_secret' => CLIENT_SECRET,
			    'grant_type' => 'refresh_token',
			    'refresh_token' => $r_token
			);
			$options = array('http' => array(
			    'method' => 'POST',
			    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
			    'content' => http_build_query($fields)));

			$json = json_decode(file_get_contents(ACCESS_TOKEN_URI, false, stream_context_create($options)));
			//var_dump($json);
			$token = $json->access_token;
			setcookie('tt_token', $token, time() + (60 * 60));
			if($json == null){
				$obj->status= "1";
				$obj->token= "";
				$obj->r_token= "";
			}else{
				//setcookie("tt_token", $token, 60 * 60);
				$obj->status= "0";
				$obj->token= $token;
				$obj->r_token= $r_token;
			}
		}else{//tokenセットなしの場合
			$obj->status= "1";
			$obj->token= "";
			$obj->r_token= "";
		}
		return $obj;
	}
	//cookie削除
	public function deleteCookies(){
		setcookie('tt_code','',time() - 3600);
		setcookie('tt_token','',time() - 3600);
		setcookie('tt_r_token','',time() - 3600);
	}
}
?>