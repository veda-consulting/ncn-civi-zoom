<?php
/**
 *  @file
 *  File for the BtoUnitTestCase class
 *
 */
/**
 *  Base class for Regional event tickets unit tests
 *
 *  Common functions for unit tests
 * @package CiviCRM
 */

require_once 'CRM\NcnCiviZoom\Utils.php';

class CRM_NcnCiviZoom_TestCase_Utils extends PHPUnit_Framework_TestCase {
	protected $pageSizes = array(0, '100', -999.99, 99999, 5);

	protected function getPastEventsByNoOfDays($days = 10){
    $pastDateTimeFull = new DateTime();
    $pastDateTimeFull = $pastDateTimeFull->modify("-".$days." days");
    $pastDate = $pastDateTimeFull->format('Y-m-d');
    $currentDate = date('Y-m-d');

    $apiResult = civicrm_api3('Event', 'get', [
      'sequential' => 1,
      'end_date' => ['BETWEEN' => [$pastDate, $currentDate]],
    ]);
    $allEvents = $apiResult['values'];
    $eventIds = [];
    foreach($allEvents as $key => $value) {
      $eventIds[] = $value['id'];
    }
    return $allEvents;
	}

  protected function assertIsArray($param = array()){
    $this->assertEquals(TRUE, is_array($param));
  }

  protected function assertIsString($param = ''){
    $this->assertEquals(TRUE, is_string($param));
  }
}