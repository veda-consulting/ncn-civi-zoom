{* HEADER *}
<div class="crm-block crm-form-block">
{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}

<div class="block block-system">
	<div class="custom-group crm-accordion-wrapper crm-custom-accordion">
		<div class="crm-accordion-header">Selet the Zoom Data to be synced with the Participants</div>
		<table class="form-layout">
			<tbody>
				{foreach from=$zoomFields item=zoomField}
					<tr class="crm-event-manage-eventinfo-form-block-is_map">
						<td class="label">{$form.$zoomField.html}</td>
						<td class="crm-form-block-from_name">{$form.$zoomField.label} &nbsp;</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
<div class="spacer"></div>