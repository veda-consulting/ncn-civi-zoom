<?php

/**
 *  NcnCiviZoom utils functions
 *
 * @package CiviCRM
 */
class CRM_NcnCiviZoom_Utils {

  /*
   * Function to retrieve zoom settings
   * Full settings if account id is passed
   * Only common settings if id nt passed
   */
  public static function getZoomSettings($id=null){
    $settings = CRM_Core_BAO_Setting::getItem(ZOOM_SETTINGS, 'zoom_settings');
    if(!empty($id) && !empty($settings)){
      return array_merge($settings, self::getZoomAccountSettingsByIdOrName($id));
    } else{
      return $settings;
    }
  }

  public static function getWebinarCustomField(){
    $settings = self::getZoomSettings();
    $customId = CRM_Utils_Array::value('custom_field_id_webinar', $settings, NULL);
    $customField = (!empty($customId))? 'custom_'.$customId : NULL;
    return $customField;
  }

  public static function getMeetingCustomField(){
    $settings = self::getZoomSettings();
    $customId = CRM_Utils_Array::value('custom_field_id_meeting', $settings, NULL);
    $customField = (!empty($customId))? 'custom_'.$customId : NULL;
    return $customField;
  }

  public static function getAccountIdCustomField(){
    $settings = self::getZoomSettings();
    $customId = CRM_Utils_Array::value('custom_field_account_id', $settings, NULL);
    $customField = (!empty($customId))? 'custom_'.$customId : NULL;
    return $customField;
  }

  /*
   * Output will be an array of all zoom settings
   * as id => [zoom settings]
   */
  public function getAllZoomAccountSettings() {
    $tableName = CRM_NcnCiviZoom_Constants::ZOOM_ACCOUNT_SETTINGS;
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM {$tableName}");
    $zoomSettings = [];
    while ($dao->fetch()) {
      $zoomSettings[$dao->id] = $dao->toArray();
    }
    return $zoomSettings;
  }

  /*
   * Output will be an array as [zoom settings]
   */
  public function getZoomAccountSettingsByIdOrName($id=NULL, $name = null) {
    if(empty($id) && empty($name)){
      return null;
    }
    $zoomSettings = self::getAllZoomAccountSettings();
    if($id && !empty($zoomSettings[$id])){
      return $zoomSettings[$id];
    } elseif ($name) {
      foreach ($zoomSettings as $id => $settings) {
        if($settings[$name] == $name){
          return $settings;
        }
      }
    }

    return null;
  }

  /*
   * Output will be an array of all settings' ids and names * as id => 'name'
   */
  public function getAllZoomSettingsNamesAndIds(){
    $zoomSettings = self::getAllZoomAccountSettings();
    $zoomList[0] = "--select--";
    if(!empty($zoomSettings)){
      foreach ($zoomSettings as $key => $value) {
        $zoomList[$key] = $value['name'];
      }
    }

    return $zoomList;
  }

  public function getZoomAccountIdByEventId($eventId){
    $result = null;
    $customField = self::getAccountIdCustomField();
    try {
      $apiResult = civicrm_api3('Event', 'get', [
        'sequential' => 1,
        'return' => [$customField],
        'id' => $eventId,
      ]);
      // Remove any empty spaces
      $result = trim($apiResult['values'][0][$customField]);
      $result = str_replace(' ', '', $result);
    } catch (Exception $e) {
      throw $e;
    }

    return $result;
  }

  public function getZoomSettingsByEventId($eventId){
    $settings = [];
    $accountId = self::getZoomAccountIdByEventId($eventId);
    if(!empty($accountId)){
      $settings = self::getZoomSettings($accountId);
    }
    return $settings;
  }

  // Please Don't use this function now
  // public function getRegionalZoomAccountId(){
  //   $regionalAcountId = null;
  //   $id = 1;//Harcoded Regional zoom account Id
  //   $tableName = CRM_NcnCiviZoom_Constants::ZOOM_ACCOUNT_SETTINGS;
  //   $regionalAcountId = CRM_Core_DAO::singleValueQuery("SELECT id FROM ".$tableName." WHERE id = %1", [1=>[$id ,'Integer']]);
  //   return $regionalAcountId;
  // }

  public static function checkRequiredProfilesForAnEvent($profileIds = [], $checkFields = []) {
    if(empty($profileIds) || empty($checkFields)){
      return null;
    }
    if(!is_array($profileIds)){
      $profileIds = [$profileIds];
    }
    if(!is_array($checkFields)){
      $checkFields = [$checkFields];
    }
    foreach ($checkFields as $checkField) {
      $requiredFields[$checkField] = 0;
    }
    $missingFields = [];
    foreach ($profileIds as $profileId) {
      if(!empty($profileId)){
        try {
          $profileDetails = civicrm_api3('UFField', 'get', [
            'sequential' => 1,
            'uf_group_id' => $profileId,
          ]);
        } catch (Exception $e) {
          CRM_Core_Error::debug_var('CRM_NcnCiviZoom_Utils checkRequiredProfilesForAnEvent error', $e);
        }
        if(isset($profileDetails['values'])){
          foreach ($profileDetails['values'] as $profileDetail) {
            if(array_key_exists($profileDetail['field_name'], $requiredFields)){
              $requiredFields[$profileDetail['field_name']] = 1;
            }
          }//End of inner foreach
        }
      }
    }//End of outer foreach

    // Getting missing fields
    foreach ($requiredFields as $requiredField => $value) {
      if($value != 1){
        $missingFields[] = $requiredField;
      }
    }
    return $missingFields;
  }

  public function addZoomListToEventForm(&$form){

    $zoomList = self::getAllZoomSettingsNamesAndIds();

    $form->add(
      'select',
      'zoom_account_list',
      'Select the zoom account',
      $zoomList,
      FALSE,
      array('multiple' => FALSE, 'id' => 'zoom_account_list')
    );

    $customIds['Webinar'] = self::getWebinarCustomField();
    $customIds['Meeting'] = self::getMeetingCustomField();
    $customFieldZoomAccount = self::getAccountIdCustomField();
    $form->assign('customIdWebinar',$customIds['Webinar'].'_');
    $form->assign('customIdMeeting',$customIds['Meeting'].'_');
    $form->assign('accountId',$customFieldZoomAccount.'_');
    $eventId = null;
    if(!empty($form->_id)){
      $eventId = $form->_id;
    }elseif (!empty($form->_entityId)) {
      $eventId = $form->_entityId;
    }
    if(!empty($eventId)){
      if(($form->getAction() == CRM_Core_Action::UPDATE) && !empty($customFieldZoomAccount)){
        try {
          $apiResult = civicrm_api3('CustomValue', 'get', [
            'sequential' => 1,
            'entity_id' => $eventId,
            'return.'.$customFieldZoomAccount => 1,
          ]);
        } catch (Exception $e) {
          CRM_Core_Error::debug_var('Api error, Entity => CustomValue , action => get ', $e);
        }
        if(!empty($apiResult['values'][0]['latest'])){
          if(array_key_exists($apiResult['values'][0]['latest'], $zoomList)){
            $form->setDefaults(['zoom_account_list' => $apiResult['values'][0]['latest']]);
          }
        }
      }
    }
  }

  /**
   * Union two given arrays
   *
   * @param array1 - Array
   * @param array2 - Array
   * @return union - Array
   */
  public static function multiDimArrayUnion($array1 = [], $array2 = []){
    if(!is_array($array1)){
      return $array1;
    }

    if(!is_array($array2)){
      $array2 = [];
    }
    $merged = array_merge($array1 , $array2);
    foreach ($merged as $key => $value) {
      $serialized[$key] = serialize($value);
    }
    $serialized = array_unique($serialized);
    foreach ($serialized as $key => $value) {
      $union[$key] = unserialize($value);
    }

    return $union;
  }

  /**
   * Upcoming Events List
   *
   * @return Array of events
   */
  public static function getUpcomingEventsList(){
    $today = date("Y-m-d");

    $startDate = civicrm_api3('Event', 'get', [
      'start_date' => ['>=' => $today],
    ]);

    $endDate = civicrm_api3('Event', 'get', [
      'end_date' => ['>=' => $today],
    ]);

    return self::multiDimArrayUnion($startDate['values'], $endDate['values']);
  }

  /**
   * Filter recent registrants list by time(in mins)
   *
   * @param registrantsList - Array
   * @param minsBack - Integer
   * @return recentRegistrants - Array
   */
  public static function filterZoomRegistrantsByTime($registrantsList = [], $minsBack = 60){
    if(empty($registrantsList) || !is_array($registrantsList)){
      return;
    }
    $recentRegistrants = [];
    foreach ($registrantsList as $registrant) {
      $registrationTime = $registrant['create_time'];

      $registrationTime = str_replace(['T','Z'], [' ',''], $registrationTime);
      $registrationTime = date($registrationTime);
      $now = date('Y-m-d h:i:s');
      $seconds = strtotime($now) - strtotime($registrationTime);
      $mins = ($seconds/60);
      if($mins < $minsBack){
        $recentRegistrants[] = $registrant;
      }
    }

    return $recentRegistrants;
  }

  /**
   * String of Registrants
   *
   * @param registrantsList - Array
   * @param glue - String
   * @return stringOfRegistrants - String
   */
  public static function stringOfRegistrants($registrantsList = [], $glue = ' , '){
    if(empty($registrantsList) || !is_array($registrantsList)){
      return;
    }
    $registrantsUpdateArray = [];
    foreach ($registrantsList as $registrant) {
      $registrantsUpdateArray[] = $registrant['first_name']." ".$registrant['last_name']." - ".$registrant['email'];
    }
    $stringOfRegistrants = implode($glue, $registrantsUpdateArray);
    return $stringOfRegistrants;
  }

  /**
   * Update the Zoom Registrants to event's notes
   *
   * @param eventId - Integer
   * @param registrantsList - Array
   */
  public static function updateZoomRegistrantsToNotes($eventId, $registrantsList = []){
    $updateResult = '';
    if(empty($eventId) || empty($registrantsList) || !is_array($registrantsList)){
      $updateResult = 'Params Missing';
      return $updateResult;
    }

    $updateString = self::stringOfRegistrants($registrantsList);
    $cFNameEventNotes = CRM_NcnCiviZoom_Constants::CF_Event_Zoom_Notes;

    try {
      $cFDetails = civicrm_api3('CustomField', 'get', [
        'sequential' => 1,
        'name' => $cFNameEventNotes,
      ]);
    } catch (Exception $e) {
      CRM_Core_Error::debug_var('Error in updateZoomRegistrantsToNotes', $e);
      CRM_Core_Error::debug_var('Error while calling api CustomField get', $cFNameEventNotes);
      $updateResult = "Couldn't retrieve the Custom Field ".$cFNameEventNotes." data";
    }
    if(!empty($cFDetails['id'])){
      try {
        $apiResult = civicrm_api3('CustomValue', 'create', [
          'entity_id' => $eventId,
          'custom_'.$cFDetails['id'] => $updateString.".",
        ]);
      } catch (Exception $e) {
        CRM_Core_Error::debug_var('Error in updateZoomRegistrantsToNotes', $e);
        CRM_Core_Error::debug_var('Error while calling api CustomField create', [
          'eventId' => $eventId,
          'cFDetails' => $cFDetails,
          'updateString' => $updateString
        ]);
      }
      if($apiResult['values']){
        $updateResult = 'Registrants have been updated to the event successfully.';
      }
    }

    return $updateResult;
  }

  /*
   * Function to get message template details
   */
  public static function getMessageTemplateDetails($title = null, $id = null) {
    if(!empty($title)){
      $result = civicrm_api3('MessageTemplate', 'get', array(
        'sequential' => 1,
        'msg_title' => $title,
      ));

      return $result ['values'][0];
    }elseif(!empty($id)){
      $result = civicrm_api3('MessageTemplate', 'get', array(
        'sequential' => 1,
        'id' => $id,
      ));

      return $result ['values'][0];
    }else{
      return [];
    }
  }

  /**
   * Send Registrants as Email
   *
   * @param toEmails - String
   * @param registrantsList - Array
   * @param event - Integer
   */
  public static function sendZoomRegistrantsToEmail($toEmails, $registrantsList = [], $event){
    if(empty($toEmails) || empty($registrantsList)){
      return;
    }

    // $msgTitle = CRM_NcnCiviZoom_Constants::SEND_ZOOM_REGISTRANTS_EMAIL_TEMPLATE_TITLE;
    $msgId = CRM_NcnCiviZoom_Utils::getEmailTemplateIdToSendZoomRegistrants();
    $emailContent = self::getMessageTemplateDetails(null, $msgId);
    if(empty($emailContent)){
      return 'Email Template Not found.';
    }
    $return = [];
    // Replacing the tokens
    $emailContent['subject'] = str_replace('{event_title}' ,$event, $emailContent['msg_subject']);
    $registrantsString = self::stringOfRegistrants($registrantsList, '<br>');
    $emailContent['html'] = str_replace(['{registrants}', '{event_title}'], [$registrantsString, $event], $emailContent['msg_html']);
    $emailIds = explode(',', $toEmails);
    foreach ($emailIds as $emailId) {
      $emailSent = self::sendEmail($emailId, $emailContent);
      if($emailSent){
        $return['email_message'][] = 'Email has been Sent to '.$emailId;
      }else{
        $return['email_message'][] = "Email couldn't be Sent to ".$emailId;
      }
    }

    return $return;
  }

  /**
   * Function to send email
   */
  public static function sendEmail($email, $emailContent) {
    $emailSent = FALSE;
    if (empty($email) || empty($emailContent)) {
      return $emailSent;
    }

    $mailParams['toName'] = $email;
    $mailParams['toEmail'] = $email;

    $mailParams['text'] = !empty($emailContent['text']) ? $emailContent['text'] : '';
    $mailParams['html'] = !empty($emailContent['html']) ? $emailContent['html'] : '';
    $mailParams['subject'] = !empty($emailContent['subject']) ? $emailContent['subject'] : '';
    $defaultAddress = CRM_Core_OptionGroup::values('from_email_address', NULL, NULL, NULL, ' AND is_default = 1');
    $mailParams['from'] = reset($defaultAddress);

    require_once 'CRM/Utils/Mail.php';
    $emailSent = CRM_Utils_Mail::send($mailParams);

    return $emailSent;
  }

  public static function forUpgrade1003(){
    $customGroupName = CRM_NcnCiviZoom_Constants::CG_Event_Zoom_Notes;
    $customFieldName = CRM_NcnCiviZoom_Constants::CF_Event_Zoom_Notes;

    $customGroupDetails = civicrm_api3('CustomGroup', 'create', [
      'sequential' => 1,
      'title' => "Event Zoom Notes",
      'extends' => "Event",
      'name' => $customGroupName,
    ]);

    civicrm_api3('CustomField', 'create', [
      'sequential' => 1,
      'custom_group_id' => $customGroupDetails['values'][0]['id'],
      'label' => "Event Zoom Notes",
      'name' => $customFieldName,
      'data_type' => "Memo",
      'html_type' => "TextArea",
      'is_view' => 1,
    ]);

    $sendZoomRegistrantsEmailTemplateTitle = CRM_NcnCiviZoom_Constants::SEND_ZOOM_REGISTRANTS_EMAIL_TEMPLATE_TITLE;
    $msgHtml = "<br> {event_title} <br> {registrants} <br>";
    $msgSubject = "Recently Joined to the zoom event: {event_title}";
    civicrm_api3('MessageTemplate', 'create', [
      'msg_title' => $sendZoomRegistrantsEmailTemplateTitle,
      'msg_html' => $msgHtml,
      'msg_subject' => $msgSubject,
    ]);
  }

  public static function forUpgrade1004(){
    $sendZoomRegistrantsEmailTemplateTitle = CRM_NcnCiviZoom_Constants::SEND_ZOOM_REGISTRANTS_EMAIL_TEMPLATE_TITLE;
    $templateDetails = civicrm_api3('MessageTemplate', 'get', [
      'sequential' => 1,
      'msg_title' => $sendZoomRegistrantsEmailTemplateTitle,
    ]);
    $zoomSettings = self::getZoomSettings();
    if(!empty($templateDetails['id'])){
      $zoomSettings['registrants_email_template_id'] = $templateDetails['id'];
    }
    CRM_Core_BAO_Setting::setItem($zoomSettings, ZOOM_SETTINGS, 'zoom_settings');
  }

  public static function getEmailTemplateIdToSendZoomRegistrants(){
    $settings = self::getZoomSettings();
    $templateId = CRM_Utils_Array::value('registrants_email_template_id', $settings, NULL);
    return $templateId;
  }
}
