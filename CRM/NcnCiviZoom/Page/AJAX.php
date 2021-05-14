<?php

use Firebase\JWT\JWT;
use Zttp\Zttp;

use CRM_NcnCiviZoom_ExtensionUtil as E;

/**
 * This class contains all the function that are called using AJAX (jQuery)
 */
class CRM_NcnCiviZoom_Page_AJAX {

	public static function checkEventWithZoom(){

		if(empty($_POST) || empty($_POST["account_id"])
			|| empty($_POST["entityID"])
			|| empty($_POST["entity"])){
			$result = ['status' => null , 'message' => "Parameters missing"];
			CRM_Utils_JSON::output($result);
		}
		$result = CRM_CivirulesActions_Participant_AddToZoom::checkEventWithZoom($_POST);
		if(!empty($result)){
			CRM_Utils_JSON::output($result);
		}else{
			$result = ['status' => null, 'message' => 'Error occured please try again'];
			CRM_Utils_JSON::output($result);
		}
	}

  /*
  * function to get all Zoom Registrants for an event id from civi
  */
  public static function getZoomRegistrants() {
    $event_id = CRM_Utils_Request::retrieve('event_id', 'Int', CRM_Core_DAO::$_nullObject);
    $zoomRegistrants = CRM_NcnCiviZoom_Utils::getZoomRegistrantsFromCivi($event_id);
    $returnData = array();
    foreach ($zoomRegistrants as $zoomRegistrant) {
	    $actionUrl = '';
	    $participantRecordPresent = $contactRecordPresent = FALSE;
	   	$participantRecordPresent = CRM_NcnCiviZoom_Utils::checkForParticipantRecordInCivi($zoomRegistrant['email'], $event_id);
	    if(!$participantRecordPresent){
	    	$contactRecordPresent = CRM_NcnCiviZoom_Utils::checkForContactRecordInCivi($zoomRegistrant['email']);
	    	if(!$contactRecordPresent){
	    		// If no contact record found add import contact action
	    		$actionUrl = "<button>Import Contact</button>";
	    	}else{
	    		// If no participant record found add import participant action
	    		$url = CRM_Utils_System::url('civicrm/zoom/importparticipant', "reset=1&id=".$zoomRegistrant['id']);
	    		$actionUrl = "<a href={$url} class='action-item crm-hover-button crm-popup medium-popup'>Add Participant</a>";
	    	}
	    }
    	$returnData[] = array($zoomRegistrant['id'], $zoomRegistrant['event_id'], $zoomRegistrant['first_name'], $zoomRegistrant['last_name'], $zoomRegistrant['email'], $actionUrl);
    }

    CRM_Utils_JSON::output(array('data' => $returnData));
    CRM_Utils_System::civiExit( );
  }

  /*
  * function to import Contact From Zoom Registrant
  */
  public static function importContactFromZoomRegistrant() {
    $id = CRM_Utils_Request::retrieve('id', 'Int', CRM_Core_DAO::$_nullObject);
  	$zoomRegistrant = array();
  	if(!empty($id)){
  		$zoomRegistrant = CRM_NcnCiviZoom_Utils::getZoomRegistrantDetailsById($id);
  		CRM_Utils_System::setTitle(ts("Import Contact"));
  		$createContactApiParams = array(
  				'contact_type' => "Individual",
  				'first_name'   => empty($zoomRegistrant['first_name'])? "": $zoomRegistrant['first_name'],
  				'last_name'    => empty($zoomRegistrant['last_name'])? "": $zoomRegistrant['last_name'],
  				'email'        => empty($zoomRegistrant['email'])? "": $zoomRegistrant['email'],
				);
  		try {
  			$contactDetails = civicrm_api3('Contact', 'create', $createContactApiParams);
  		} catch (Exception $e) {
  			CRM_Core_Error::debug_log_message('Error while calling api in '.__CLASS__.'::'.__FUNCTION__);
  			CRM_Core_Error::debug_log_message('Api Entity: Contact , Action: create');
  			CRM_Core_Error::debug_var('Api Params', $createContactApiParams);
  		}
  		$settings = CRM_NcnCiviZoom_Utils::getZoomSettings();
  		// Update the email location type id as in the settings
  		if(!empty($settings['import_email_location_type']) && !empty($contactDetails['id'])){
  			CRM_Core_Error::debug_var('update email type', 'hits');
  			$qParams = array(
  				1 => array($settings['import_email_location_type'], 'Integer'),
  				2 => array($zoomRegistrant['email'], 'String'),
  				3 => array($contactDetails['id'], 'Integer'),
  			);
  			$updateQuery = "UPDATE civicrm_email SET location_type_id = %1 WHERE email = %2 AND contact_id = %3";
  			CRM_Core_DAO::executeQuery($updateQuery, $qParams);
  		}
	  }
    echo 'success';
    CRM_Utils_System::civiExit( );
  }

  /*
  * function to get Contact Details
  * Along with some other entity details of that contact
  */
  public static function getContactDetails(){
  	$cId = CRM_Utils_Request::retrieve('id', 'Int', CRM_Core_DAO::$_nullObject);
  	$returnData = array();
		$contactDetails = civicrm_api3('Contact', 'get', array(
		  'sequential' => 1,
		  'id' => $cId,
		));
		$returnData['email'] = $contactDetails['values'][0]['email'];
		$returnData['display_name'] = $contactDetails['values'][0]['display_name'];
		$memDetails = civicrm_api3('Membership', 'get', array(
		  'sequential' => 1,
		  'contact_id' => $cId,
		));
		$returnData['memerbships'] = $memDetails['count'];
		$contribDetails = civicrm_api3('Contribution', 'get', array(
		  'sequential' => 1,
		  'contact_id' => $cId,
		));
		$returnData['contributions'] = $contribDetails['count'];
		$participantDetails = civicrm_api3('Participant', 'get', array(
		  'sequential' => 1,
		  'contact_id' => $cId,
		));
		$returnData['event_registrations'] = $participantDetails['count'];
		$returnData['contactUrl'] = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid='.$cId);

		CRM_Utils_JSON::output(array('data' => $returnData));
  	CRM_Utils_System::civiExit( );
  }
}
