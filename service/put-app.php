<?php
namespace TymFrontiers;
require_once "../.appinit.php";

\require_login(false);
\check_access("/app", false, "project-dev");

$errors = [];
$gen = new Generic;
$app = false;
$required = [];
$pre_params = [
  "name" => ["name","username",5,55,[],'LOWER',['-',"."]],
  "callback" => ["callback","username",3,35,[],'MIXED']
];
// if( empty($_GET['id']) ) $required[] = 'owner';
$params = $gen->requestParam($pre_params,$_GET,$required);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
if( $params ):
  if( !empty($params['name']) ){
    $app = (new MultiForm(MYSQL_DEV_DB,'apps','name'))
      ->findBySql("SELECT name, prefix, user, request_timeout, domain, endpoint, title, description
                   FROM :db:.:tbl:
                   WHERE name = '{$database->escapeValue($params['name'])}'
                   LIMIT 1");
    if( !$app ){
      $errors[] = "No record found for given paa [name]: \"{$params['id']}\"";
    } else {
      $app = $app[0];
    }
  }
endif;
?>
<input
  type="hidden"
  id="rparam"
  <?php if($params){ foreach($params as $k=>$v){
    echo "data-{$k}=\"{$v}\" ";
  } }?>
  >
<div id="fader-flow">
  <div class="view-space">
    <div class="padding -p20">&nbsp;</div>
    <br class="c-f">
    <div class="grid-10-tablet grid-8-laptop center-tablet">
      <div class="sec-div color asphalt bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1 class="fw-lighter"> <i class="fas fa-layer-group"></i> App info</h1>
        </header>

        <div class="padding -p20">
          <?php if(!empty($errors)){ ?>
            <h3>Unresolved error(s)</h3>
            <ol>
              <?php foreach($errors as $err){
                echo " <li>{$err}</li>";
              } ?>
            </ol>
          <?php }else{ ?>
            <form
            id="do-post-form"
            class="block-ui"
            method="post"
            action="/app/tymfrontiers-cdn/devman.soswapp/src/<?php echo $app ? "PutApp" : "PostApp" ?>.php"
            data-validate="false"
            onsubmit="sos.form.submit(this,doPost); return false;"
            >
            <input type="hidden" name="form" value="app-form">
            <input type="hidden" name="CSRF_token" value="<?php echo ($session->createCSRFtoken("app-form"));?>">

            <div class="grid-5-tablet">
              <label for="user"> <i class="fas fa-asterisk fa-border"></i> Owner</label>
              <input type="text" maxlength="12" minlength="5" name="user" id="user" required placeholder="USERNAME/ID" <?php echo $app ? "readonly" : ""; ?> value="<?php echo $app ? $app->user : $session->name; ?>">
            </div>
            <div class="grid-9-tablet">
              <label for="name"> <i class="fas fa-asterisk fa-border"></i> App name [lowercase / numbers / hyphens / periods]</label>
              <input type="text" name="name" id="name" <?php echo $app ? "readonly" : ""; ?> required placeholder="app-name" value="<?php echo $app ? $app->name : ""; ?>">
            </div>
            <div class="grid-3-tablet">
              <label for="prefix"><i class="fas fa-asterisk fa-border"></i> Param prefix</label>
              <input type="text" minlength="3" maxlength="7" onkeyup="$(this).val($(this).val().toUpperCase());" name="prefix" id="prefix" <?php echo $app ? "readonly" : ""; ?> required placeholder="APPX" value="<?php echo $app ? $app->prefix : ""; ?>">
            </div>
            <div class="grid-7-tablet">
              <label for="domain"> Domain</label>
              <input type="text" name="domain" id="domain" placeholder="www.appname.com" value="<?php echo $app ? $app->domain : ""; ?>">
            </div>
            <div class="grid-5-tablet">
              <label for="endpoint"> Endpoint</label>
              <input type="text" name="endpoint" id="endpoint" placeholder="/app-service" value="<?php echo $app ? $app->endpoint : ""; ?>">
            </div>
            <div class="grid-8-tablet">
              <label for="title"><i class="fas fa-asterisk fa-border"></i> Title</label>
              <input type="text" maxlength="65" name="title" required id="title" placeholder="App Title" value="<?php echo $app ? $app->title : ""; ?>">
            </div>
            <div class="grid-12-tablet">
              <label for="description"> <i class="fas fa-asterisk fa-border"></i> App's description</label>
              <textarea maxlength="256" minlength="25" name="description" required placeholder="Program's descriptive information" class="autosize" required><?php echo $app ? $app->description : ""; ?></textarea>
            </div>

            <div class="grid-5-tablet">
              <label>Request timeout</label>
              <select name="request_timeout">
                <?php foreach ((new API\DevApp())->request_timeout_opt as $key => $value):
                  echo "<option value=\"{$key}\"";
                  echo $app && $app->request_timeout == $key ? " selected" : "";
                  echo ">{$value} </option>";
                 endforeach; ?>
              </select>
            </div>
            <div class="grid-4-tablet"> <br>
              <button id="submit-form" type="submit" class="btn asphalt"> <i class="fas fa-save"></i> Save </button>
            </div>

            <br class="c-f">
          </form>
        <?php } ?>
      </div>
    </div>
  </div>
  <br class="c-f">
</div>
</div>

<script type="text/javascript">
  var param = $('#rparam').data();
  (function(){
    $('textarea.autosize').autosize();
  })();
</script>
