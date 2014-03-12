<?php
require("Config.php");
session_set_cookie_params(60 * 60 * 2, "/", DOMAIN, TRUE, TRUE );
session_start();
?>