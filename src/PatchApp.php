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
  "status" => ["status","option", ["ACTIVE","SUSPENDED","BANNED"]],

  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
];
$req = ["name","status"];
if (!$http_auth) {
  $req[] = 'form';
  $req[] = 'CSRF_token';
}

$params = $gen->requestParam($rqp,"post",$req);
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
include PRJ_ROOT . "/src/Pre-Process.php";
if (!$app = (new MultiForm(MYSQL_DEV_DB, "apps", "name"))->findById($params["name"])) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["App with [name]: '{$params['name']}' not found."],
    "message" => "Request halted."
  ]);
  exit;
}
$app->status = $params["status"];
if (!$app->update()) {
  $do_errors = [];
  $app->mergeErrors();
  $more_errors = (new InstanceError($app,true))->get('',true);
  if (!empty($more_errors)) {
    foreach ($more_errors as $method=>$errs) {
      foreach ($errs as $err){
        $do_errors[] = $err;
      }
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
      "message" => "No changes were made."
    ]);
    exit;
  }
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
