<?php
require("Config.php");
// MySQL に接続し、データベースを選択します。
$conn = mysql_connect(DB_URL, DB_USER, DB_PASS) or die(mysql_error());
mysql_select_db(DB_NAME) or die(mysql_error());

// SQL クエリを実行します。
$res = mysql_query('SELECT * from keystores') or die(mysql_error());

// 結果を出力します。
while ($row = mysql_fetch_array($res, MYSQL_NUM)) {
    echo $row[0] . "\n";
}


$sql = "INSERT INTO keystores (seskey, token, r_token) VALUES ('4', 'skldjfls', 'dsadfasd')";
$result_flag = mysql_query($sql);

if (!$result_flag) {
    die('INSERTクエリーが失敗しました。'.mysql_error());
}


// 結果セットを開放し、接続を閉じます。
mysql_free_result($res);
mysql_close($conn);

?>