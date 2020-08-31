<?php
namespace TymFrontiers;
require_once "../.appinit.php";

\require_login(true);
\check_access("/request-logs", true, "project-dev");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" manifest="/site.webmanifest">
  <head>
    <meta charset="utf-8">
    <title>Dev apps request log | <?php echo PRJ_TITLE; ?></title>
    <?php include PRJ_INC_ICONSET; ?>
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
    <meta name="author" content="<?php echo PRJ_AUTHOR; ?>">
    <meta name="creator" content="<?php echo PRJ_CREATOR; ?>">
    <meta name="publisher" content="<?php echo PRJ_PUBLISHER; ?>">
    <meta name="robots" content='nofollow'>
    <!-- Theming styles -->
    <link rel="stylesheet" href="/app/soswapp/font-awesome.soswapp/css/font-awesome.min.css">
    <link rel="stylesheet" href="/app/soswapp/theme.soswapp/css/theme.min.css">
    <link rel="stylesheet" href="/app/soswapp/theme.soswapp/css/theme-<?php echo PRJ_THEME; ?>.min.css">
    <!-- optional plugin -->
    <link rel="stylesheet" href="/app/soswapp/plugin.soswapp/css/plugin.min.css">
    <link rel="stylesheet" href="/app/soswapp/dnav.soswapp/css/dnav.min.css">
    <link rel="stylesheet" href="/app/soswapp/faderbox.soswapp/css/faderbox.min.css">
    <!-- Project styling -->
    <link rel="stylesheet" href="<?php echo \html_style("base.min.css"); ?>">
  </head>
  <body>
    <input type="hidden" id="setup-page" data-datapager="#data-pager" data-datacontainer="#log-list" data-datasearch="logs" data-datahandle="listLog">
    <?php \TymFrontiers\Helper\setup_page("/app/devman/request-logs", "project-dev", true, PRJ_HEADER_HEIGHT); ?>
    <?php include PRJ_INC_HEADER; ?>

    <section id="main-content">
      <div class="view-space">
        <br class="c-f">
          <div class="grid-8-tablet center-tablet">
            <form
              id="query-form"
              class="block-ui color asphalt"
              method="post"
              action="/app/tymfrontiers-cdn/devman.soswapp/src/GetAPPLog.php"
              data-validate="false"
              onsubmit="sos.form.submit(this, doFetch); return false;"
              >
              <input type="hidden" name="form" value="applog-query-form">
              <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("applog-query-form");?>">

              <div class="grid-6-tablet">
                <label for="search"> <i class="fas fa-search"></i> Search</label>
                <input type="search" name="search" value="<?php echo !empty($_GET['search']) ? $_GET['search'] :''; ?>" id="search" placeholder="Keyword search">
              </div>
              <div class="grid-4-phone grid-2-tablet">
                <label for="page"> <i class="fas fa-file-alt"></i> Page</label>
                <input type="number" name="page" id="page" class="page-val" placeholder="1" value="1">
              </div>
              <div class="grid-4-phone grid-2-tablet">
                <label for="limit"> <i class="fas fa-sort-numeric-up"></i> Limit</label>
                <input type="number" name="limit" id="limit" class="page-limit" placeholder="25" value="25">
              </div>
              <div class="grid-4-phone grid-2-tablet"> <br>
                <button type="submit" class="btn asphalt"> <i class="fas fa-search"></i></button>
              </div>
              <br class="c-f">
            </form>
            <p class="align-c">
              <b>Records:</b> <span class="records-text">00</span> |
              <b>Pages:</b> <span class="pages-text">00</span>
            </p>
          </div>

          <div class="sec-div padding -p10">
            <h2>App request log</h2>
            <table class="vertical color asphalt padding -pnone">
              <thead class="color-text align-l border -bthin -bbottom">
                <tr>
                  <th>App</th>
                  <th>Path</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody id="log-list"></tbody>
            </table>
            <div id="data-pager">
            </div>
            <br class="c-f">
          </div>


        <br class="c-f">
      </div>
    </section>
    <?php include PRJ_INC_FOOTER; ?>
    <!-- Required scripts -->
    <script src="/app/soswapp/jquery.soswapp/js/jquery.min.js">  </script>
    <script src="/app/soswapp/js-generic.soswapp/js/js-generic.min.js">  </script>
    <script src="/app/soswapp/theme.soswapp/js/theme.min.js"></script>
    <!-- optional plugins -->
    <script src="/app/soswapp/plugin.soswapp/js/plugin.min.js"></script>
    <script src="/app/soswapp/dnav.soswapp/js/dnav.min.js"></script>
    <script src="/app/soswapp/faderbox.soswapp/js/faderbox.min.js"></script>
    <!-- project scripts -->
    <script src="<?php echo \html_script ("base.min.js"); ?>"></script>
    <script src="/app/tymfrontiers-cdn/devman.soswapp/js/devman.min.js"></script>
    <script type="text/javascript">
      $(document).ready(function() {
        requery();
      });

    </script>
  </body>
</html>
