$(document).ready(function(){var d={};d[EE.lang.close]=$(this).dialog("close");$('<div id="ajaxContent"></div>').dialog({autoOpen:false,resizable:false,modal:true,position:"center",minHeight:"0px",buttons:d});$("a.submenu").click(function(){if($(this).data("working")){return false}else{$(this).data("working",true)}var f=$(this).attr("href"),h=$(this).parent(),g=h.find("ul");if($(this).hasClass("accordion")){if(g.length>0){if(!h.hasClass("open")){h.siblings(".open").toggleClass("open").children("ul").slideUp("fast")}g.slideToggle("fast");h.toggleClass("open")}$(this).data("working",false)}else{$(this).data("working",false);var i=$(this).html();$("#ajaxContent").load(f+" .pageContents",function(){$("#ajaxContent").dialog("option","title",i);$("#ajaxContent").dialog("open")})}return false});if(EE.importantMessage){var e=EE.importantMessage.state,b=$("#ee_important_message");function c(){e=!e;document.cookie="exp_home_msg_state="+(e?"open":"closed")}function a(){$.ee_notice.show_info(function(){$.ee_notice.hide_info();b.removeClass("closed").show();c()})}b.find(".msg_open_close").click(function(){b.hide();a();c()});if(!e){a()}}});