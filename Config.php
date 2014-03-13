<?php
//TT関連
define('DOMAIN', 'typetalk.atsonic.jp');//使用するドメイン・・・この変数使ってないかも
define('CLIENT_ID', '1uCOYhimOXU1B01OZVSAI7ctM1U3QduP');//typetalkでアプリ登録すると発行される
define('CLIENT_SECRET', 'vLK1lLccvyM48zdazUkw07wi1fx95pjMlaJgBHiD7UPpIPP7WPH8XzvcRTsECJbT');//typetalkでアプリ登録すると発行される
define('REDIRECT_URI', 'http://typetalk.atsonic.jp/redirect.php');//typetalkで認証後に戻ってくるページ
define('AUTHORIZE_URI', 'https://typetalk.in/oauth2/authorize');//typetalkの認証ページ
define('ACCESS_TOKEN_URI', 'https://typetalk.in/oauth2/access_token');//typetalkのtoken発行ページ
define('TOPICS_URI', 'https://typetalk.in/api/v1/topics');//typetalkのTOPICS API
define('PROFILE_URI', 'https://typetalk.in/api/v1/profile');//typetalkのPROFILE API
//PUSHER関連
//pusherにサインアップ、アプリ登録をすると発行される
define('PUSHER_KEY', '39daabf949c81c9b1bea');
define('PUSHER_SECRET', '3c2d2f70a09e11bf0ddd');
define('PUSHER_APPID', '66619');
?>