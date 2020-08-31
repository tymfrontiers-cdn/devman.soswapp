<?php
namespace TymFrontiers;
require_once "../.appinit.php";

\header("Content-Type: application/json");
\require_login(false);
\check_access("/apps", false, "project-dev");

$post = \json_decode( \file_get_contents('php://input'), true); // json data
$post = !empty($post) ? $post : (
  !empty($_POST) ? $_POST : []
);
$gen = new Generic;
$auth = new API\Authentication ($api_sign_patterns);
$http_auth = $auth->validApp ();
if ( !$http_auth && ( empty($post['form']) || empty($post['CSRF_token']) ) ){
  HTTP\Header::unauthorized (false,'', Generic::authErrors ($auth,"Request [Auth-App]: Authetication failed.",'self',true));
}
$params = $gen->requestParam(
  [
    "name" => ["name","username",5,55,[],'LOWER',['-',"."]],
    "search" => ["search","text",3,25],
    "page" =>["page","int",1,0],
    "limit" =>["limit","int",1,0],

    "form" => ["form","text",2,55],
    "CSRF_token" => ["CSRF_token","text",5,500]
  ],
  $post,
  []
);
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
      "message" => "Request failed."
    ]);
    exit;
  }
}
$count = 0;
$data = new MultiForm(MYSQL_DEV_DB, 'apps','name');
$data->current_page = $page = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
$adm_db = MYSQL_ADMIN_DB;
$query =
"SELECT dvapp.name, dvapp.live, dvapp.status, dvapp.user, dvapp.prefix, dvapp.request_timeout,
        dvapp.domain, dvapp.endpoint, dvapp.title, dvapp.description, dvapp._created, dvapp._updated,
        (
          SELECT COUNT(*)
          FROM :db:.`request_history`
          WHERE app = dvapp.name
        ) AS requests,
        CONCAT(adm.name, ' ', adm.surname) AS user_name
 FROM :db:.:tbl: AS dvapp ";
 $join = " LEFT JOIN `{$adm_db}`.`user` AS adm ON adm._id = dvapp.user";

$cond = " WHERE 1=1 ";
if (!empty($params['name'])) {
  $cond .= " AND dvapp.name='{$database->escapeValue($params['name'])}' ";
} else {
  if( !empty($params['search']) ){
    $params['search'] = $db->escapeValue(\strtolower($params['search']));
    $cond .= " AND (
      dvapp.name = '{$params['search']}'
      OR LOWER(dvapp.domain) LIKE '%{$params['search']}%'
      OR LOWER(dvapp.title) LIKE '%{$params['search']}%'
    ) ";
  }
}

$count = $data->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS dvapp {$cond} ");
// echo $db->last_query;
$count = $data->total_count = $count ? $count[0]->cnt : 0;

$data->per_page = $limit = !empty($params['name']) ? 1 : (
    (int)$params['limit'] > 0 ? (int)$params['limit'] : 35
  );
$query .= $join;
$query .= $cond;
$sort = " ORDER BY dvapp._created DESC ";

$query .= $sort;
$query .= " LIMIT {$data->per_page} ";
$query .= " OFFSET {$data->offset()}";

// echo \str_replace(':tbl:','work_path',\str_replace(':db:',MYSQL_BASE_DB,$query));
// exit;
$found = $data->findBySql($query);
$tym = new \TymFrontiers\BetaTym;

if( !$found ){
  die( \json_encode([
    "message" => "No app(s) found for your query.",
    "errors" => [],
    "status" => "0.2"
    ]) );
}
// process result
$result = [
  'records' => (int)$count,
  'page'  => $data->current_page,
  'pages' => $data->totalPages(),
  'limit' => $limit,
  'has_previous_page' => $data->hasPreviousPage(),
  'has_next_page' => $data->hasNextPage(),
  'previous_page' => $data->hasPreviousPage() ? $data->previousPage() : 0,
  'next_page' => $data->hasNextPage() ? $data->nextPage() : 0
];
foreach($found as $k=>$obj){
  unset($found[$k]->errors);
  unset($found[$k]->current_page);
  unset($found[$k]->per_page);
  unset($found[$k]->total_count);

  $found[$k]->live = (bool)$found[$k]->live;
  $found[$k]->requests = (int)$found[$k]->requests;
  $found[$k]->min_desc = Data::getLen($found[$k]->description, 128);

  $found[$k]->created_date = $found[$k]->created();
  $found[$k]->created = $tym->MDY($found[$k]->created());

  $found[$k]->updated_date = $found[$k]->updated();
  $found[$k]->updated = $tym->MDY($found[$k]->updated());
}

$result["message"] = "Request completed.";
$result["errors"] = [];
$result["status"] = "0.0";
$result["apps"] = $found;

echo \json_encode($result);
exit;
