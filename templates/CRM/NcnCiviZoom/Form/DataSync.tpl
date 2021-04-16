{* HEADER *}
<div class="crm-block crm-form-block">
{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}

<div class="block block-system">
	<div class="custom-group crm-accordion-wrapper crm-custom-accordion">
		<div class="crm-accordion-header">Select the Zoom Data to be synced with the Participants</div>
		<h2>Data available for both webinars and meetings apis</h2>
		<h4>Webinar api name: Get Webinar Participants Report</h4>
		<h4>Meeting api name: Get Meeting Participants Report</h4>
		<table class="form-layout">
			<tbody>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.user_id.html}</td>
					<td class="crm-form-block-from_name">{$form.user_id.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.name.html}</td>
					<td class="crm-form-block-from_name">{$form.name.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.email.html}</td>
					<td class="crm-form-block-from_name">{$form.email.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.join_time.html}</td>
					<td class="crm-form-block-from_name">{$form.join_time.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.leave_time.html}</td>
					<td class="crm-form-block-from_name">{$form.leave_time.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.duration.html}</td>
					<td class="crm-form-block-from_name">{$form.duration.label} &nbsp;</td>
				</tr>
			</tbody>
		</table>
		<h2>Data available for meetings only</h2>
		<h4>Meeting api name: Get Meeting Participants Report</h4>
		<table class="form-layout">
			<tbody>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.registrant_id.html}</td>
					<td class="crm-form-block-from_name">{$form.registrant_id.label} &nbsp;</td>
				</tr>
			</tbody>
		</table>
		<h2>Data available for webinars only</h2>
		<h4>Webinar api name: Get Past Webinar Absentees</h4>
		<table class="form-layout">
			<tbody>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.first_name.html}</td>
					<td class="crm-form-block-from_name">{$form.first_name.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.last_name.html}</td>
					<td class="crm-form-block-from_name">{$form.last_name.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.address.html}</td>
					<td class="crm-form-block-from_name">{$form.address.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.city.html}</td>
					<td class="crm-form-block-from_name">{$form.city.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.country.html}</td>
					<td class="crm-form-block-from_name">{$form.country.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.zip.html}</td>
					<td class="crm-form-block-from_name">{$form.zip.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.state.html}</td>
					<td class="crm-form-block-from_name">{$form.state.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.indusrty.html}</td>
					<td class="crm-form-block-from_name">{$form.indusrty.label} &nbsp;</td>
				</tr>
				<tr class="crm-event-manage-eventinfo-form-block-is_map">
					<td class="label">{$form.job_title.html}</td>
					<td class="crm-form-block-from_name">{$form.job_title.label} &nbsp;</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
<div class="spacer"></div>