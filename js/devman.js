const pConf = sos.config.page;
function checkPost (resp = {}) {
  if( resp && resp.status === "0.0"){
    setTimeout(function(){
      removeAlert();
      requery();
    },1800);
  }
}
const appGoLive = (app, live = true) => {
  if (confirm(`Do you want to set this app live?`)) {
    $("#post-form input[name=name]").val(app);
    $("#post-form input[name=live]").val(1);
    $("#post-form").attr("action","/app/tymfrontiers-cdn/devman.soswapp/src/GoLive.php").submit();
  }
};
const doAppStatus = (app, status) => {
  if (confirm(`Do you want to set app status to ${status}?`)) {
    $("#post-form input[name=name]").val(app);
    $("#post-form input[name=status]").val(status);
    $("#post-form").attr("action","/app/tymfrontiers-cdn/devman.soswapp/src/PatchApp.php").submit();
  }
};
// const delPPKG = (id) => {
//   if (id && confirm("Do you want to delete this package?")) {
//     $("#post-form input[name=id]").val(id);
//     $("#post-form").submit();
//   }
// };
const requery = () => {
  if ($('#query-form').length > 0 ) $('#query-form').submit();
}
function listLog (logs) {
  let html = "";
  $.each(logs, function(_i, dvlog) {
    html += "<tr>";
      html += `<td> <i class="fas fa-info-circle"></i> <a href="#" onclick="sos.faderBox.url('/app/tymfrontiers-cdn/devman.soswapp/service/view-app.php',{name :'${dvlog.app}'},{exitBtn : true});">${dvlog.app_name} (${dvlog.app})</a></td>`;
      html += `<td><a href="#" onclick="sos.faderBox.url('/app/tymfrontiers-cdn/devman.soswapp/service/view-applog.php',{id :'${dvlog.id}'},{exitBtn : true});"> <i class="fas fa-window-maximize"></i> ${dvlog.path.replace('| '+dvlog.app,'').replaceAll(dvlog.app,'')}</a></td>`;
      html += `<td title="${dvlog.created_date}">${dvlog.created}</td>`;
    html += "</tr>";
  });
  $(`${pConf.datacontainer}`).html(html);
};
function listApp (apps) {
  let html = "";
  $.each(apps, function(_i, app) {
    html += "<tr>";
    html += `<td> <span class="${app.status === 'ACTIVE' ? 'color-green' : 'color-red'}">[${(app.live === true ? 'LIVE | ' : '') + app.status}]</span> <a href="#" onclick="sos.faderBox.url('/app/tymfrontiers-cdn/devman.soswapp/service/put-app.php', {name : '${app.name}',callback : 'requery'}, {exitBtn : true});" class='blue color-blue'><i class="fas fa-edit"></i> ${app.name}</a></td>`;

      html += `<td><a href="#" class="color-blue blue" onclick="sos.faderBox.url('/app/tymfrontiers-cdn/devman.soswapp/service/view-app.php',{name :'${app.name}'},{exitBtn : true});"> <i class="fas fa-window-maximize"></i> ${app.title}</a></td>`;
      html += `<td>${app.user_name} (${app.user})</td>`;
      html += `<td>${app.requests}</td>`;
      html += `<td>`;
      let out_arr = [];
      if (!app.live && !in_array(app.status, ['BANNED', 'PENDING', 'SUSPENDED'])) {
        out_arr.push(`<a class="green" href="#" onclick="appGoLive('${app.name}', true);"><i class="fas fa-bullseye"></i>&nbsp;Go live</a>`);
      }
      if (in_array(app.status,['PENDING', 'SUSPENDED'])) {
        out_arr.push(`<a class="blue color-blue" href="#" onclick="doAppStatus('${app.name}', 'ACTIVE');"> <i class="fas fa-check-circle"></i>&nbsp;Activate</a>`);
      }
      if (in_array(app.status,['ACTIVE', 'PENDING'])) {
        out_arr.push(`<a class="asphalt color-asphalt" href="#" onclick="doAppStatus('${app.name}', 'SUSPENDED');"><i class="fas fa-hand-paper"></i>&nbsp;Suspend</a>`);
        out_arr.push(`<a class="red color-red" href="#" onclick="doAppStatus('${app.name}', 'BANNED');"><i class="fas fa-ban"></i>&nbsp;Ban</a>`);
      }
      html += out_arr.join(' | ');
      html += `</td>`;
    html += "</tr>";
  });
  $(`${pConf.datacontainer}`).html(html);
};
const doFetch = (resp) => {
  if( resp && resp.status == "0.0" && object_length(resp[pConf.datasearch]) > 0){
    $('.pages-text').text(resp.pages); $('.pages-val').val(resp.pages); sos.config.page["pages"] = resp.pages;
    $('.records-text').text(resp.records); $('.records-val').val(resp.records); sos.config.page["records"] = resp.records;
    $('.page-val').val(resp.page); $('.page-text').text(resp.page); sos.config.page["page"] = resp.page;
    $('.limit-val').val(resp.limit); $('.limit-text').text(resp.limit); sos.config.page["limit"] = resp.limit;
    if( resp.has_next_page ) $('#next-page').data('page',resp.next_page); sos.config.page["hasNextPage"] = resp.has_next_page;
    if( resp.has_previous_page ) $('#previous-page').data('page',resp.previous_page); sos.config.page["hasPreviousPage"] = resp.has_previous_page;
    if (typeof window[pConf.datahandle] === "function") {
      window[pConf.datahandle](resp[pConf.datasearch]);
    }
    removeAlert();
    pageNatr();
  } else {
    $(`${pConf.datacontainer}`).html('');
  }
  pageNatr();
};
const pageNatr = () => {
  let elem = $(`${pConf.datapager}`);
  if (pConf.hasPreviousPage) {
    $(document).find("button.prev-page-btn").remove();
    elem.append($(`<button class='sos-btn face-secondary prev-page-btn' onclick="pageTo(${pConf.page - 1});"> <i class="fas fa-lg fa-angle-left"></i></button>`));
  } else {
    $(document).find("button.prev-page-btn").remove();
  }
  if (pConf.hasNextPage) {
    $(document).find("button.next-page-btn").remove();
    elem.append($(`<button class='sos-btn face-secondary next-page-btn' onclick="pageTo(${pConf.page + 1});"> <i class="fas fa-lg fa-angle-right"></i></button>`));
  } else {
    $(document).find("button.next-page-btn").remove();
  }
};
const pageTo = (page = 0) => {
  if (page > 0) {
    $(`.page-val`).val(page);
    requery();
  }
};
function doPost(resp = {}) {
  if( resp && resp.status == '0.0' || resp.errors.length < 1 ){
    if( ('callback' in param) && typeof window[param.callback] === 'function' ){
      faderBox.close();
      window[param.callback](data);
    }else{
      setTimeout(function(){
        faderBox.close();
        removeAlert();
        requery();
      },1500);
    }
  }
}
