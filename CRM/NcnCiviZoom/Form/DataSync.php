<?php



use CRM_NcnCiviZoom_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_NcnCiviZoom_Form_DataSync extends CRM_Core_Form {

  public $_id = NULL;
  public $_act = NULL;

  public function preProcess() {
    parent::preProcess();
  }

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Zoom Data Sync Settings'));

    // These data are avilable for both webinars and meetings
    $this->addElement('checkbox', 'user_id', ts('Zoom user Id'));
    $this->addElement('checkbox', 'name', ts('Display Name'));
    $this->addElement('checkbox', 'email', ts('Email'));
    $this->addElement('checkbox', 'join_time', ts('Joining time'));
    $this->addElement('checkbox', 'leave_time', ts('Leaving time'));
    $this->addElement('checkbox', 'duration', ts('Duration'));

    // This data is avilable only for meetings
    $this->addElement('checkbox', 'registrant_id', ts('Registrant Id'));

    // These data are avilable only for webinars
    $this->addElement('checkbox', 'first_name', ts('First Name'));
    $this->addElement('checkbox', 'last_name', ts('Last Name'));
    $this->addElement('checkbox', 'address', ts('Address'));
    $this->addElement('checkbox', 'city', ts('City'));
    $this->addElement('checkbox', 'country', ts('Country'));
    $this->addElement('checkbox', 'zip', ts('Zip'));
    $this->addElement('checkbox', 'state', ts('State'));
    $this->addElement('checkbox', 'indusrty', ts('Indusrty'));
    $this->addElement('checkbox', 'job_title', ts('Job title'));

    // Assigning the sync fields
    $this->assign('zoomFields', CRM_NcnCiviZoom_Constants::$allZoomParticipantDataFields);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts("Save"),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    ));

    //Set default Values
    $defaults = CRM_NcnCiviZoom_Utils::getSyncZoomDataFields();
    CRM_Core_Error::debug_var('buildQuickForm defaults', $defaults);
    $this->setDefaults($defaults);
    parent::buildQuickForm();
  }


  public function postProcess() {
    $buttonName = $this->controller->getButtonName();
    $values = $this->exportValues();
    CRM_Core_Error::debug_var('postProcess values', $values);

    // Retrieve the form data fields
    $allZoomFields = CRM_NcnCiviZoom_Constants::$allZoomParticipantDataFields;
    $allSelectedFields = [];
    foreach ($allZoomFields as $zoomField) {
        if(!empty($values[$zoomField])){
            $allSelectedFields[$zoomField] = $values[$zoomField];
        }
    }

    // Check and create the custom group if not exists
    $params = array(
        'sequential' => 1,
        'name' => CRM_NcnCiviZoom_Constants::CG_ZOOM_DATA_SYNC,
    );
    try {
        $cGDetails = civicrm_api3('CustomGroup', 'get', $params);
    } catch (Exception $e) {
        CRM_Core_Error::debug_var('CRM_NcnCiviZoom_Form_DataSync::postProcess Api:CustomGroup Action:get error details', $e);
        CRM_Core_Error::debug_var('CRM_NcnCiviZoom_Form_DataSync::postProcess Api:CustomGroup Action:get params', $params);
    }
    if(empty($cGDetails['values'])){
        $params = array(
            'title' => "Zoom Data Sync",
            'extends' => "Participant",
            'name' => CRM_NcnCiviZoom_Constants::CG_ZOOM_DATA_SYNC,
            'table_name' => "civicrm_value_zoom_data_sync",
        );
        try {
            $cGDetails = civicrm_api3('CustomGroup', 'create', $params);
        } catch (Exception $e) {
            CRM_Core_Error::debug_var('CRM_NcnCiviZoom_Form_DataSync::postProcess Api:CustomGroup Action:create error details', $e);
            CRM_Core_Error::debug_var('CRM_NcnCiviZoom_Form_DataSync::postProcess Api:CustomGroup Action:create params', $params);
        }
        CRM_Core_Error::debug_var('postProcess cGDetails', $cGDetails);
    }

    // Check and create the custom fields if not exists
    $cGId = $cGDetails['id'];
    foreach ($allSelectedFields as $key => $selectedField) {
        $params = array(
            'sequential' => 1,
            'custom_group_id' => $cGId,
            'name' => $key,
        );
        try {
            $cFDetails = civicrm_api3('CustomField', 'get', $params);
        } catch (Exception $e) {
            CRM_Core_Error::debug_var('CRM_NcnCiviZoom_Form_DataSync::postProcess Api:CustomGroup Action:get error details', $e);
            CRM_Core_Error::debug_var('CRM_NcnCiviZoom_Form_DataSync::postProcess Api:CustomGroup Action:get params', $params);

        }

        if(empty($cFDetails['values'])){
            $cfLabel = ucwords(str_replace( '_', ' ', $key));
            $params = array(
                'custom_group_id' => $cGId,
                'label' => $cfLabel,
                'name' => $key,
                'data_type' => "String",
                'html_type' => "Text",
                'column_name' => $key,
            );
            try {
                $cFDetails= civicrm_api3('CustomField', 'create', $params);
            } catch (Exception $e) {
                CRM_Core_Error::debug_var('CRM_NcnCiviZoom_Form_DataSync::postProcess Api:CustomGroup Action:create error details', $e);
                CRM_Core_Error::debug_var('CRM_NcnCiviZoom_Form_DataSync::postProcess Api:CustomGroup Action:create params', $params);

            }
            CRM_Core_Error::debug_var('postProcess allSelectedFields', $allSelectedFields);
        }
    }
    $zoomSettings = CRM_NcnCiviZoom_Utils::getZoomSettings();
    $zoomSettings['sync_zoom_data_fields'] = $allSelectedFields;
    CRM_Core_Error::debug_var('postProcess allSelectedFields', $allSelectedFields);
    CRM_Core_BAO_Setting::setItem($zoomSettings, ZOOM_SETTINGS, 'zoom_settings');
    $result['message'] = ts('Your Settings have been saved');
    $result['type'] = 'success';
    $redirectUrl    = CRM_Utils_System::url('civicrm/Zoom/zoomdatasync', 'reset=1');

    CRM_Core_Session::setStatus($result['message'], ts('Zoom Settings'), $result['type']);
    CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
