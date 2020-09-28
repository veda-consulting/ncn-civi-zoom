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
    $zoomList = [];
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

    $zoomList = CRM_NcnCiviZoom_Utils::getAllZoomSettingsNamesAndIds();

    $form->add(
      'select',
      'zoom_account_list',
      'Select the zoom account',
      array_merge(['0' => "--select--"] , $zoomList),
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
}
