// Get variables from page
var url = spp_ddl_vars.fileurl;

// JavaScript For Download Redirect
jQuery(function($){
var count = spp_ddl_vars.duration;
var countdown = setInterval(function(){
  jQuery("span#spp-ddl-countdown").html(count);
  if (count == 0) {
    window.location = url;
    $('#spp-ddl-dl-link').show();
    clearInterval(countdown);    
  }
  count--;
}, 1000);
});