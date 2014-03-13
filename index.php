<?php
require_once("Include.php");
require_once("TTOauth.php");
$code;//アプリコード
$token;//アプリtoken
$r_token;//アプリrefresh token
$cont;//コンテンツ設定用
$ttOauth = new TTOauth();//typetalk oAuthオブジェクト

//諸々判定
//typetalkから戻ってきた時に認証コードがURLに含まれている
//そのまま残すのは美しくないので認証コードをセッションに保存して
//引数無しのURLにするためリダイレクト
if(isset($_GET['code'])){
	$_SESSION["tt_code"] = $_GET['code'];
	header("Location: /");
	exit;
}
$cont = '<a href="Authorize.php">ログイン</a>';
//セッションに認証コードが含まれる場合
if(isset($_SESSION["tt_code"])){
	//セッションから認証コードを取り出す
	$code = $_SESSION["tt_code"];
	//access tokenとrefresh tokenがすでにセッションにある場合
	if(isset($_SESSION["tt_token"]) && isset($_SESSION["tt_r_token"])){
		$token = $_SESSION["tt_token"];
		$r_token = $_SESSION["tt_r_token"];
		setContent();
	//access tokenとrefresh tokenがセッションにない場合
	}else{
		//認証サーバーを叩いて各トークンを発行
		$tokenObj = $ttOauth->getToken($code);
		$status = $tokenObj->status;
		//取得成功した場合（statusが0の場合）
		echo $status;
		if($status == "0"){
			//セッションに保存してるから
			//変数に格納するのはあまり意味が無い
			$token = $tokenObj->token;
			$r_token = $tokenObj->r_token;
			setContent();
		//取得失敗した場合もう一度認証へ
		}else{
			backToOAuth();
		}
	}
}
//認証後のコンテンツを設定
function setContent(){
	global $cont;
	$cont = '<div id="list"></div>
				<form id="messageForm">
					<input type="text" name="message" id="inputMessage"><br>
					<button>送信！</button>
				</form>
			<div id="timeline"></div>';
}
?>
<html>
<head>
<meta content="text/html" charset="UTF-8">
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0" />
<script type="text/javascript" src="http://js.pusher.com/2.1/pusher.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript" src="js/Config.js"></script>
<script type="text/javascript" src="js/Main.js"></script>
<link rel="stylesheet" type="text/css" href="css/Main.css">
</head>
<body>
	<div id="wrapper">
		<?php
			echo $cont;//コンテンツを出力	
			exit;
		?>
	</div>
</body>
</html>