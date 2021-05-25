{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{* This included tpl checks if a given username is taken or available. *}


{literal}
CRM.$(function($) {
$( document ).ajaxComplete(function(event, xhr, settings) {
   var Url = settings.url;
   if (Url.indexOf("custom") >= 0) {
      var count = 0;
      //blur event function for Webinar id
      $("input[name^='{/literal}{$customIdWebinar}{literal}']").on('blur', function(){
         var webinarId = $.trim($("input[name^='{/literal}{$customIdWebinar}{literal}']").val());
         if(!webinarId || (webinarId==0)){
            $("#msgbox_webinar").text("");
            $("#msgbox_webinar").css({"display":"none"});
            return;
         }
         x = checkEventZoom("msgbox_webinar", count);
         count = count + x;
         return;
      });

      //blur event function for Meeting id
      $("input[name^='{/literal}{$customIdMeeting}{literal}']").on('blur', function(){
         var meetingId = $.trim($("input[name^='{/literal}{$customIdMeeting}{literal}']").val());
         if(!meetingId || (meetingId==0)){
            $("#msgbox_meeting").text("");
            $("#msgbox_meeting").css({"display":"none"});
            return;
         }
         x = checkEventZoom("msgbox_meeting", count);
         count = count + x;
         return;
      });

      //change event for zoom account select
      $("#zoom_account_list").change(function(){
         var zoomAccountId = $.trim($("#zoom_account_list").val());
         if(zoomAccountId > 0){
            $("input[name^='{/literal}{$accountId}{literal}']").val(zoomAccountId);
         }else{
            $("input[name^='{/literal}{$accountId}{literal}']").val(null);
         }
         var webinarId = $.trim($("input[name^='{/literal}{$customIdWebinar}{literal}']").val());
         var meetingId = $.trim($("input[name^='{/literal}{$customIdMeeting}{literal}']").val());
         if(meetingId>0){
            x = checkEventZoom("msgbox_meeting", count);
            count = count + x;
         }else if(webinarId>0){
            x = checkEventZoom("msgbox_webinar", count);
            count = count + x;
         }
         return;
      });
   }
});

//Function to do the Ajax validation
function checkEventZoom(msgboxid, count = 0){

   var zoomAccountId = $.trim($("#zoom_account_list").val());
   var webinarId = $.trim($("input[name^='{/literal}{$customIdWebinar}{literal}']").val());
   var meetingId = $.trim($("input[name^='{/literal}{$customIdMeeting}{literal}']").val());
   webinarId = webinarId.replace(/\s/g,'');
   meetingId = meetingId.replace(/\s/g,'');
   $("input[name^='{/literal}{$customIdWebinar}{literal}']").val(webinarId);
   $("input[name^='{/literal}{$customIdMeeting}{literal}']").val(meetingId);

   if((webinarId <= 0) && (meetingId <= 0)){
      if(zoomAccountId == 0){
       return 0;
      }
      $("#"+msgboxid).removeClass().text("");
      alert('{/literal}{ts escape="js"}Please enter a webinarId or meetingId{/ts}{literal}');
      return 0;
   }

   if(!webinarId && !meetingId){
      if(zoomAccountId == 0){
       return;
      }
      $("#"+msgboxid).removeClass().text("");
      alert('{/literal}{ts escape="js"}Please enter a webinarId or meetingId{/ts}{literal}');
      return 0;
   }

   if((webinarId > 0) && (meetingId > 0)){
      $("#"+msgboxid).removeClass().text("");
      if(count == 0){
         alert('{/literal}{ts escape="js"}Please enter either webinarId or meetingId, don't enter both{/ts}{literal}');
         return 1;
      }
      return 0;
   }

   if (zoomAccountId > 0) {
      //take all messages in javascript variable
      var check = "{/literal}{ts escape='js'}Checking...{/ts}{literal}";
      $("#"+msgboxid).removeClass().addClass('cmsmessagebox').css({"color":"#000","backgroundColor":"#FFC","border":"1px solid #c93"}).text(check).fadeIn("slow");
   } else {
      $("#"+msgboxid).removeClass().text("");
      alert('{/literal}{ts escape="js"}Please select an zoom account{/ts}{literal}');
      return;
   }

   if(webinarId > 0){
      var entityId = webinarId;
      var entityName = 'Webinar';
   }
   if(meetingId > 0){
      var entityId = meetingId;
      var entityName = 'Meeting';
   }

   var checkZoomParams = {
       account_id: zoomAccountId,
       entity: entityName,
       entityID: entityId,
       for: 'civicrm/ajax/checkeventwithzoom'
   };

   //check the username exists or not from ajax
   var checkUrl = {/literal}"{crmURL p='civicrm/ajax/checkeventwithzoom' h=0 }"{literal};

   $.post(checkUrl, checkZoomParams ,function(data) {
      if(data.status == 1){
         $("#"+msgboxid).fadeTo(200,0.1,function() {
            $(this).html(data.message).addClass('cmsmessagebox').css({"color":"#008000","backgroundColor":"#C9FFCA", "border": "1px solid #349534"}).fadeTo(900,1);
         });
      }else if(data.status == -1){
         $("#"+msgboxid).fadeTo(200,0.1,function() {
            $(this).html(data.message).addClass('cmsmessagebox').css({"color":"#000","backgroundColor":"#FFC", "border": "1px solid #c93"}).fadeTo(900,1);
         });
      }else{
         $("#"+msgboxid).fadeTo(200,0.1,function() {
            $(this).html(data.message).addClass('cmsmessagebox').css({"color":"#008000","backgroundColor":"#F7CBCA", "border": "1px solid #FF0000"}).fadeTo(900,1);
         });
      }
   }, "json");
   return 0;
}
});

{/literal}
