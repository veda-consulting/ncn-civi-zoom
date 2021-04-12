<?php

use CRM_NcnCiviZoom_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_NcnCiviZoom_Form_ZoomRegistrants extends CRM_Core_Form {

  public $_event_id = NULL;

  public function preProcess() {
    CRM_Utils_System::setTitle(ts("Zoom Registrants"));
    $this->_event_id = CRM_Utils_Request::retrieve('event_id', 'Int', $this);
    //setting the user context to zoom accounts list page
    $session = CRM_Core_Session::singleton();
    $urlParams = "reset=1";
    if($this->_event_id){
      $urlParams .= "&event_id=".$this->_event_id;
    }
    $session->pushUserContext(CRM_Utils_System::url('civicrm/zoom/zoomregistrants', $urlParams));
    parent::preProcess();
  }

  public function buildQuickForm() {
    $this->_event_id = CRM_Utils_Request::retrieve('event_id', 'Int', $this);
    if(!empty($this->_event_id)){
      $event_title = CRM_Core_DAO::singleValueQuery('SELECT title FROM civicrm_event WHERE id = '.$this->_event_id);
      $this->assign('event_id', $this->_event_id);
      $this->assign('event_title', $event_title);
    }
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    if($this->_event_id){
      $urlParams .= "&event_id=".$this->_event_id;
    }
    $redirectUrl = CRM_Utils_System::url('civicrm/zoom/zoomregistrants', $urlParams);
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
