<?php

use CRM_NcnCiviZoom_ExtensionUtil as E;
use Civi\Test\EndToEndInterface;

require_once 'CRM\NcnCiviZoom\Utils.php';
require_once 'CRM\Core\Error.php';

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - The global variable $_CV has some properties which may be useful, such as:
 *    CMS_URL, ADMIN_USER, ADMIN_PASS, ADMIN_EMAIL, DEMO_USER, DEMO_PASS, DEMO_EMAIL.
 *  - To spawn a new CiviCRM thread and execute an API call or PHP code, use cv(), e.g.
 *      cv('api system.flush');
 *      $data = cv('eval "return Civi::settings()->get(\'foobar\')"');
 *      $dashboardUrl = cv('url civicrm/dashboard');
 *  - This template uses the most generic base-class, but you may want to use a more
 *    powerful base class, such as \PHPUnit_Extensions_SeleniumTestCase or
 *    \PHPUnit_Extensions_Selenium2TestCase.
 *    See also: https://phpunit.de/manual/4.8/en/selenium.html
 *
 * @group e2e
 * @see cv
 */
class CRM_Ncncivizoom_ZoomAPiPaginationTest extends CRM_NcnCiviZoom_TestCase_Utils implements EndToEndInterface {

  public static function setUpBeforeClass(): void {
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest

    // Example: Install this extension. Don't care about anything else.
    \Civi\Test::e2e()->installMe(__DIR__)->apply();

    // Example: Uninstall all extensions except this one.
    // \Civi\Test::e2e()->uninstall('*')->installMe(__DIR__)->apply();

    // Example: Install only core civicrm extensions.
    // \Civi\Test::e2e()->uninstall('*')->install('org.civicrm.*')->apply();
  }

  public function setUp(){
    parent::setUp();
  }

  public function testGetZoomAttendeesOrAbsentees001(){
    // Testing for zoom attendees/absentees
    $events = $this->getPastEventsByNoOfDays(40);
    // CRM_Core_Error::debug_var('testGetZoomAttendeesOrAbsentees001 eventIds', $eventIds);
    foreach($events as $event) {
      $eventId = $event['id'];
      // CRM_Core_Error::debug_var('testGetZoomAttendeesOrAbsentees001 eventId', $eventId);
      // Retriving the data with default page sizes
      $participantsList = CRM_CivirulesActions_Participant_AddToZoom::getZoomAttendeeOrAbsenteesList($eventId, null);
      if(!empty($participantsList)){
        // CRM_Core_Error::debug_var('testGetZoomAttendeesOrAbsentees001 participantsList', $participantsList);
        $this->assertIsArray($participantsList);
        foreach ($participantsList as $participant) {
          $this->assertNotEmpty($participant);
          $this->assertIsString($participant);
        }

        // Retriving the data with different page sizes
        foreach($this->pageSizes as $pageSize) {
          $participantsList = CRM_CivirulesActions_Participant_AddToZoom::getZoomAttendeeOrAbsenteesList($eventId, $pageSize);
          $this->assertEquals(TRUE, is_array($participantsList));
          foreach ($participantsList as $participant) {
            $this->assertNotEmpty($participant);
            $this->assertIsString($participant);
          }
        }
      }
    }
  }

  public function testGetZoomParticipantsData002(){
    // Testing for zoom registrants
    $events = $this->getPastEventsByNoOfDays(40);
    // CRM_Core_Error::debug_var('testGetZoomParticipantsData002 eventIds', $eventIds);
    foreach($events as $event) {
      $eventId = $event['id'];
      // CRM_Core_Error::debug_var('testGetZoomParticipantsData002 eventId', $eventId);
      // Retrieving the data with default page sizes
      $participantsList = CRM_CivirulesActions_Participant_AddToZoom::getZoomParticipantsData($eventId, null);
      // CRM_Core_Error::debug_var('testGetZoomParticipantsData002 participantsList', $participantsList);
      if(!empty($participantsList)){
        // CRM_Core_Error::debug_var('testGetZoomParticipantsData002 participantsList', $participantsList);
        $this->assertIsArray($participantsList);
        foreach ($participantsList as $participant) {
          $this->assertNotEmpty($participant);
          $this->assertIsArray($participant);
        }

        // Retrieving the data with different page sizes
        foreach ($this->pageSizes as $pageSize) {
          $participantsList = CRM_CivirulesActions_Participant_AddToZoom::getZoomParticipantsData($eventId, $pageSize);
          $this->assertIsArray($participantsList);
          foreach ($participantsList as $participant) {
            $this->assertNotEmpty($participant);
            $this->assertIsArray($participant);
          } 
        }
      }
    }
  }

  public function testGetZoomRegistrants003(){
    // Testing for zoom registrants
    $events = CRM_NcnCiviZoom_Utils::getUpcomingEventsList();
    foreach($events as $event) {
      $eventId = $event['id'];
      // CRM_Core_Error::debug_var('testGetZoomRegistrants003 eventId', $eventId);
      // Retriving the data with default page sizes
      $registrantsList = CRM_CivirulesActions_Participant_AddToZoom::getZoomRegistrants($eventId, null);
      if(!empty($registrantsList)){
        // CRM_Core_Error::debug_var('testGetZoomRegistrants003 registrantsList', $registrantsList);
        $this->assertIsArray($registrantsList);
        foreach ($registrantsList as $registrant) {
          $this->assertNotEmpty($registrant);
          $this->assertIsArray($registrant);
        }

        // Retriving the data with different page sizes
        foreach ($this->pageSizes as $pageSize) {
          $registrantsList = CRM_CivirulesActions_Participant_AddToZoom::getZoomRegistrants($eventId, $pageSize);
          $this->assertIsArray($registrantsList);
          foreach ($registrantsList as $registrant) {
            $this->assertNotEmpty($registrant);
            $this->assertIsArray($registrant);
          } 
        }
      }
    }
  }

  public function testPaginationUtils004(){
    CRM_NcnCiviZoom_Utils::checkPageSize($pageSize);
    $this->assertEquals(150, $pageSize);
    $pageSize = null;
    CRM_NcnCiviZoom_Utils::checkPageSize($pageSize);
    $this->assertEquals(150, $pageSize);
    $pageSize = 'qwerty';
    CRM_NcnCiviZoom_Utils::checkPageSize($pageSize);
    $this->assertEquals(150, $pageSize);
    $pageSize = -999.9999;
    CRM_NcnCiviZoom_Utils::checkPageSize($pageSize);
    $this->assertEquals(150, $pageSize);
    $pageSize = 99999.999;
    CRM_NcnCiviZoom_Utils::checkPageSize($pageSize);
    $this->assertEquals(300, $pageSize);
    $pageSize = 0.999;
    CRM_NcnCiviZoom_Utils::checkPageSize($pageSize);
    $this->assertEquals(150, $pageSize);
    $pageSize = 99.99;
    CRM_NcnCiviZoom_Utils::checkPageSize($pageSize);
    $this->assertEquals(99, $pageSize);
    $pageSize = 1;
    CRM_NcnCiviZoom_Utils::checkPageSize($pageSize);
    $this->assertEquals(1, $pageSize);
    $pageSize = 300;
    CRM_NcnCiviZoom_Utils::checkPageSize($pageSize);
    $this->assertEquals(300, $pageSize);
  }

  public function tearDown(): void {
    parent::tearDown();
  }

  /**
   * Example: Test that a version is returned.
   */
  public function testWellFormedVersion(): void {
    $this->assertNotEmpty(E::SHORT_NAME);
    $this->assertRegExp('/^([0-9\.]|alpha|beta)*$/', \CRM_Utils_System::version());
  }

  /**
   * Example: Test that we're using a real CMS (Drupal, WordPress, etc).
   */
  public function testWellFormedUF(): void {
    $this->assertRegExp('/^(Drupal|Backdrop|WordPress|Joomla)/', CIVICRM_UF);
  }

}
