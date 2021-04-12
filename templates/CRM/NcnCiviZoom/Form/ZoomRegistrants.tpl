{* HEADER *}
<div class="crm-block crm-form-block">
{* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}

<h3>Event Title: {$event_title}</h3>
<h3>Event Id: {$event_id}</h3>
<div class="view-content" id="tableDiv">
<div>
    <table id="zoom_registrants" class="report-layout compact display">
        <thead>
            <tr>
                <th>Id</th>
                <th>Event ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th class="action" style="width:70px"></th>
            </tr>
        </thead>
    <tbody>

    </tbody>
    </table>
 </div>
</div>


{literal}
<script type="text/javascript">
	CRM.$(function($) {
    //Preparing the search params
    var searchParams = "";
    var pevent_id = '{/literal}{$event_id}{literal}';
    if(pevent_id){
    	searchParams ="&event_id="+pevent_id;
    }
    // ajax url to get all mobile users with user pin
    var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_NcnCiviZoom_Page_AJAX&fnName=getZoomRegistrants'}"{literal}
    dataUrl += searchParams;

    // define data table with first column id as hidden by default and last column no sortable as action column
    var table = $('#zoom_registrants').DataTable( {
        ajax: dataUrl,
        searching: true,
        columnDefs: [
          {
            "targets": 'nosort',
            "visible": false,
            "searchable": false
          },
        ],
    });

    $('#zoom_registrants tbody').on( 'click', 'button', function (e) {
        e.preventDefault();
      var data = table.row( $(this).parents('tr') ).data();
      var dialogText = $(this).text().toLowerCase();

        // handling clear registration details of a mobile user
        var message = "Do you want this registrant as contact?";
        var  boxtitle = 'Import Zoom Registrant';
          var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_NcnCiviZoom_Page_AJAX&fnName=importContactFromZoomRegistrant'}"{literal}
          dataUrl += "&id="+data[0];
	        CRM.confirm({
	          title: ts(boxtitle),
	          message: message,
	          options: {no: "No", yes: "Yes"}
	        })
	        .on('crmConfirm:yes', function() {

	           $.ajax({
	            url: dataUrl,
	            async: false,
	            success: function(data) {
	              table.ajax.reload();
	            }
	          });
	        })
	        .on('crmConfirm:no', function() {

	        });
      });
	});
</script>
{/literal}