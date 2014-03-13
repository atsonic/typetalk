<?php
class TTOauth {
	//tokenを取得するための関数
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
		list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);

		//認証エラーの時
		if($status_code == '401' || is_null($json)){
			$obj->status= "1";
			$obj->token= "";
			$obj->r_token= "";
		//取得成功
		}else{
			$token = $json->access_token;
			$r_token = $json->refresh_token;
			$_SESSION["tt_token"] = $token;
			$_SESSION["tt_r_token"] = $r_token;
			$obj->status = "0";
			$obj->token = $token;
			$obj->r_token = $r_token;
		}
		return $obj;
	}
	//refresh tokenでaccess tokenを更新する関数
	public function updateAccessToken(){
		$obj = new stdClass();
		if(isset($_SESSION["tt_r_token"])){//refresh token確認
			$r_token = $_SESSION['tt_r_token'];
			//$r_token = "";
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

			$json = json_decode(@file_get_contents(ACCESS_TOKEN_URI, false, stream_context_create($options)));
			list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);

			//認証エラーの時
			if($status_code == '401' || is_null($json)){
				$obj->status= "1";
				$obj->token= "";
				$obj->r_token= "";
			//取得成功
			}else{
				$token = $json->access_token;
				$_SESSION["tt_token"] = $token;

				$obj->status = "0";
				$obj->token = $token;
				$obj->r_token = $r_token;
			}
		}else{//refresh tokenなしの場合
			unsetSession();
			header("Location: Authorize.php");
		}
		return $obj;
	}
	//cookie削除
	public function deleteCookies(){
		setcookie(session_name(),'',time() - 3600);
	}
}
?>