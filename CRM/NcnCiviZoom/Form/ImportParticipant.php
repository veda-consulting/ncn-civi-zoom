<?php

use CRM_NcnCiviZoom_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_NcnCiviZoom_Form_ImportParticipant extends CRM_Core_Form {
  public $_id = NULL;

  public function preProcess() {
    CRM_Utils_System::setTitle(ts("Import Participant"));
    $this->_id = CRM_Utils_Request::retrieve('id', 'Int', CRM_Core_DAO::$_nullObject);
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/zoom/importparticipant',"reset=1"));
    parent::preProcess();
  }

  public function buildQuickForm() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Int', CRM_Core_DAO::$_nullObject);
    $zoomRegistrant = CRM_NcnCiviZoom_Utils::getZoomRegistrantDetailsById($this->_id);
    CRM_Core_Error::debug_var('buildQuickForm zoomRegistrant', $zoomRegistrant);
  	$getContactsQuery = "
  		SELECT
				c.id,
				c.display_name as d_name
			FROM civicrm_contact c
			LEFT JOIN civicrm_email e ON e.contact_id = c.id
			WHERE
				e.email = %1";
		$qParams = array(
			1 => array($zoomRegistrant['email'], 'String'),
		);
		CRM_Core_Error::debug_var('buildQuickForm getContactsQuery', $getContactsQuery);
		CRM_Core_Error::debug_var('buildQuickForm qParams', $qParams);
    $dao = CRM_Core_DAO::executeQuery($getContactsQuery, $qParams);
    $selectOptions = array('' => '-- select --');
    while ($dao->fetch()) {
    	$selectOptions[$dao->id] = $dao->d_name;
    }
    $this->add(
      'select',
      'change_contact_id',
      'Select Contact',
      $selectOptions,
      TRUE,
      array('class' => 'medium', 'multiple' => FALSE, 'id' => 'change_contact_id')
    );
    $defaults = array();
    if(count($selectOptions) == 2){
    	$defaults['change_contact_id'] = key( array_slice( $selectOptions, -1, 1, TRUE ) );
    }
    CRM_Core_Error::debug_var('buildQuickForm default', $default);

    $event_title = CRM_Core_DAO::singleValueQuery('SELECT title FROM civicrm_event WHERE id = '.$zoomRegistrant['event_id']);
    $this->assign('current_event', $event_title);
    $this->assign('id', $this->_id);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));
    $this->setDefaults($defaults);

    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    $zoomRegistrant = CRM_NcnCiviZoom_Utils::getZoomRegistrantDetailsById($this->_id);

    try {
      $default_role_options = civicrm_api3('Event', 'getoptions', [
        'field' => "default_role_id",
      ]);
    } catch (Exception $e) {
      CRM_Core_Error::debug_log_message('Error while calling api in '.__CLASS__.'::'.__FUNCTION__);
      CRM_Core_Error::debug_log_message('Api Entity: Event , Action: getoptions');
    }

    try {
      $eventDetails = civicrm_api3('Event', 'get', [
        'sequential' => 1,
        'id' => $zoomRegistrant['event_id'],
      ]);
    } catch (Exception $e) {
      CRM_Core_Error::debug_log_message('Error while calling api in '.__CLASS__.'::'.__FUNCTION__);
      CRM_Core_Error::debug_log_message('Api Entity: Event , Action: get');
    }

    $createParticipantApiParams = array(
    	'event_id' => $zoomRegistrant['event_id'],
    	'contact_id' => $values['change_contact_id'],
      'status_id' => 'Registered',
    );
    if(!empty($eventDetails['values'][0]['default_role_id'])){
      $createParticipantApiParams['role_id'] = $eventDetails['values'][0]['default_role_id'];
    }

    CRM_Core_Error::debug_var('Api createParticipantApiParams', $createParticipantApiParams);
    $result['is_error'] = TRUE;
    try {
    	$result = civicrm_api3('Participant', 'create', $createParticipantApiParams);
    } catch (Exception $e) {
			CRM_Core_Error::debug_log_message('Error while calling api in '.__CLASS__.'::'.__FUNCTION__);
			CRM_Core_Error::debug_log_message('Api Entity: Participant , Action: create');
			CRM_Core_Error::debug_var('Api Params', $createParticipantApiParams);
    }

    if (!$result['is_error']) {
      CRM_Core_Session::setStatus(ts('Participant added successfully.'), ts('Added'), 'success');
    } else {
      CRM_Core_Session::setStatus(ts('Unable add the participant.'), ts('Error'), 'error');
    }

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
    $elementNames = array();
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
