<?php


/**
 * File to manage, all constants and defined variables in one place
 *
 * @package CiviCRM
 */
class CRM_NcnCiviZoom_Constants {
        //Zoom account settings - table name
  CONST ZOOM_ACCOUNT_SETTINGS = 'zoom_account_settings',
  ID         = 'id', // id
  NAME       = 'name', //name of the account
  API_KEY    = 'api_key', //api_key of the account
  SECRET_KEY = 'secret_key' //secret key of the account
  ,CG_Event_Zoom_Notes = 'Event_Zoom_Notes' //Zoom notes Custom group name
  ,CF_Event_Zoom_Notes = 'Event_Zoom_Notes' //Zoom notes Custom field name
  ,SEND_ZOOM_REGISTRANTS_EMAIL_TEMPLATE_TITLE = 'send_recent_zoom_registrants'
  ,CG_ZOOM_DATA_SYNC = 'zoom_data_sync'  // Custom Group Name
  ,CF_Unmatched_Zoom_Participants = 'Unmatched_Zoom_Participants' //Zoom Exception notes Custom field name
  ,CF_ZOOM_JOIN_LINK = 'Zoom_Join_Link' //Zoom Join link Custom field name
  ,CF_ZOOM_PARTICIPANT_JOIN_LINK = 'Zoom_Participant_Join_Link' //Zoom Join link Custom field name for zoom participant
  ,ZOOM_REGISTRANTS_TABLE_NAME = 'civicrm_zoom_registrants' //Zoom registrants table name
  ;

  public static $allZoomParticipantDataFields = array('user_id', 'name', 'email', 'join_time', 'leave_time', 'duration', 'registrant_id', 'first_name', 'last_name', 'address', 'city', 'country', 'zip', 'state', 'industry', 'job_title');
}
