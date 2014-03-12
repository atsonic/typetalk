<?php
//各ページにこのファイルを埋め込み
//セッションを開始する。
//セッション期限は一週間で設定。
session_set_cookie_params(60 * 60 * 24 * 7, "/");
session_start();
require_once("Config.php");

//セッションをクリアする場合は下記を実行
function unsetSession(){
	$_SESSION = array();
	if (isset($_COOKIE[session_name()])) {
	    setcookie(session_name(), '', time()-42000, '/');
	}
	session_destroy();
}
?>