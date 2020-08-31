<?php
namespace TymFrontiers;

require_once "../.appinit.php";

\header("Content-Type: application/json");
\require_login(false);
\check_access("/app", false, "project-dev");

$post = \json_decode( \file_get_contents('php://input'), true); // json data
$post = !empty($post) ? $post : (
  !empty($_POST) ? $_POST : []
);
$gen = new Generic;
$auth = new API\Authentication ($api_sign_patterns);
$http_auth = $auth->validApp ();
if ($http_auth) $app_name = $auth->appName();
if ( !$http_auth && ( empty($post['form']) || empty($post['CSRF_token']) ) ){
  HTTP\Header::unauthorized (false,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
}
$rqp = [
  "name" => ["name","username",5,55,[],'LOWER',['-',"."]],
  "domain" => ["domain","username",5,125,[],'LOWER',['-',"."]],
  "prefix" => ["prefix","username",3,7,[],'UPPER'],
  "endpoint" => ["endpoint","username",5,55,[],'LOWER',['-',".","/","_"]],
  "request_timeout" => ["request_timeout","option", \array_keys((new API\DevApp())->request_timeout_opt)],
  "title" => ["title","text",5,65],
  "description" => ["description","text",25,256],
  "user" => ["owner","username",3,12],

  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
];
$req = [
  "name",
  "prefix",
  "title",
  "description",
  "request_timeout",
  "user"
];
if (!$http_auth) {
  $req[] = 'form';
  $req[] = 'CSRF_token';
}

$params = $gen->requestParam($rqp,$post,$req);
if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen,true))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted"
  ]);
  exit;
}

if( !$http_auth ){
  if ( !$gen->checkCSRF($params["form"],$params["CSRF_token"]) ) {
    $errors = (new InstanceError($gen,true))->get("checkCSRF",true);
    echo \json_encode([
      "status" => "3." . \count($errors),
      "errors" => $errors,
      "message" => "Request halted."
    ]);
    exit;
  }
}
// connect to dev
if ($http_auth) {
  $GLOBALS["database"]->closeConnection();
  $GLOBALS["database"] = new \TymFrontiers\MySQLDatabase(MYSQL_SERVER, MYSQL_DEVELOPER_USERNAME, MYSQL_DEVELOPER_PASS);
}
if (!(new MultiForm(MYSQL_ADMIN_DB, "user", "_id"))
  ->findBySql("SELECT _id FROM :db:.:tbl: WHERE _id='{$database->escapeValue($params['user'])}' AND status='ACTIVE' LIMIT 1")) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["No active record found for app [owner/user]"],
    "message" => "Request halted."
  ]);
  exit;
}
include PRJ_ROOT . "/src/Pre-Process.php";
$app = new API\DevApp($params);
if (empty($app->name)) {
  $do_errors = [];
  // echo "<tt> <pre>";
  // \print_r($app->errors);
  // echo "</pre></tt>";
  $more_errors = (new InstanceError($app,true))->get('self',true);
  if (!empty($more_errors)) {
    foreach ($more_errors as $err){
      $do_errors[] = $err;
    }
    echo \json_encode([
      "status" => "4." . \count($do_errors),
      "errors" => $do_errors,
      "message" => "Request incomplete."
    ]);
    exit;
  } else {
    echo \json_encode([
      "status" => "0.1",
      "errors" => [],
      "message" => "No task was performed."
    ]);
    exit;
  }
}
// disconnect to dev
if ($http_auth) {
  $GLOBALS["database"]->closeConnection();
  $GLOBALS["database"] = new \TymFrontiers\MySQLDatabase(MYSQL_SERVER, MYSQL_GUEST_USERNAME, MYSQL_GUEST_PASS);
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
