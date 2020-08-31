<?php
namespace TymFrontiers;
require_once ".appinit.php";
require_once APP_BASE_INC;
\require_login(true);
HTTP\Header::redirect("/app/devman/dashboard");
