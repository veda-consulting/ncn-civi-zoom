<table>
	<tbody>
		<tr id="zoom_account_list_full" class="custom_field-row">
			<td class="label">
				{$form.zoom_account_list.label}
			</td>
			<td class="html-adjust" href="#">
				{$form.zoom_account_list.html}
			</td>
		</tr>
		<tr id='zoom_registrants_link' class='custom_field-row'>
			<td class='label'></td>
			<td class='html-adjust'>
				<span id='span_zoom_registrants_link'>There are  <a class='crm-hover-button' id='href_zoom_registrants_link'></a> zoom contacts to be processed</span>
			</td>
		</tr>
	</tbody>
</table>


{literal}
<script>
CRM.$(function($) {
   var customIdWeb = $('{/literal}{$customIdWebinar}{literal}');
   var customIdMeet = $('{/literal}{$customIdMeeting}{literal}');

		$( document ).ajaxComplete(function(event, xhr, settings) {
			var Url = settings.url;
			if (Url.indexOf("custom") >= 0) {
				// Moving the zoom_registrants_link below the unmatched participants field
				var no_of_unmatched = {/literal}{$noOfUnmatched}{literal};
				if(no_of_unmatched > 0){
					if(no_of_unmatched == 1){
						$('#span_zoom_registrants_link').html("There is <a class='crm-hover-button' id='href_zoom_registrants_link'></a> zoom contact to be processed");
					}
					var eventId = {/literal}{$event_id}{literal};
					var zoom_registrants_link = CRM.url('civicrm/zoom/zoomregistrants', {reset: 1, event_id: eventId});
					$("#href_zoom_registrants_link").text(no_of_unmatched);
			        $("#href_zoom_registrants_link").attr("href", zoom_registrants_link);
			        $("#href_zoom_registrants_link").attr("target", "_blank");
					$("#zoom_registrants_link").insertAfter($("input[name^='{/literal}{$customIdUnmatched}{literal}']").parent().parent().parent());
				}else{
					$("#zoom_registrants_link").hide();
				}

				// Move zoom account list drop down before webinar field.
				$("#zoom_account_list_full").insertBefore($("input[name^='{/literal}{$customIdWebinar}{literal}']").parent().parent());

				//Hiding the Account Id field
				$("label[for^='{/literal}{$accountId}{literal}']").parent().parent().hide();

				//Adding message box to webinar custom field
				$("<span id='msgbox_webinar' style='display:none'></span>").insertAfter($("input[name^='{/literal}{$customIdWebinar}{literal}']"));

				//Adding message box to meeting custom field
				$("<span id='msgbox_meeting' style='display:none'></span>").insertAfter($("input[name^='{/literal}{$customIdMeeting}{literal}']"));
			}
		});
});
</script>
{/literal}

<script>
	{include file="CRM/NcnCiviZoom/Event/Form/ManageEvent/CheckZoomAccountWithEvent.tpl"}
</script>
