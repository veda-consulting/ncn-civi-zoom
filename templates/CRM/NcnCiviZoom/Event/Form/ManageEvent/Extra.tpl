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
