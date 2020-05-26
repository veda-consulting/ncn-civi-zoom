<?php

use Firebase\JWT\JWT;
use Zttp\Zttp;

class CRM_CivirulesActions_Participant_AddToZoom extends CRM_Civirules_Action{

	/**
	 * Method processAction to execute the action
	 *
	 * @param CRM_Civirules_TriggerData_TriggerData $triggerData
	 * @access public
	 *
	 */
	public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
	  $contactId = $triggerData->getContactId();

	  $event = $triggerData->getEntityData('Event');

	  $webinar = $this->getWebinarID($event['id']);

	  $participant = $this->getContactData($contactId);

	  $this->addParticipant($participant, $webinar);
	}

	/**
	 * Get an event's webinar id
	 * @param  int $event The event's id
	 * @return string The event's webinar id
	 */
	private function getWebinarID($event) {
		$result;
		$customField = CRM_NcnCiviZoom_Utils::getCustomField();
		try {
			$result = civicrm_api3('Event', 'get', [
			  'sequential' => 1,
			  'return' => [$customField],
			  'id' => $event,
			])['values'][0][$customField];
		} catch (Exception $e) {
			throw $e;
		}

		return $result;
	}

	/**
	 * Get given contact's email, first_name, last_name,
	 * city, state/province, country, post code
	 *
	 * @param int $id An existing CiviCRM contact id
	 *
	 * @return array Retrieved contact info
	 */
	private function getContactData($id) {
		$result = [];

		try {
			$result = civicrm_api3('Contact', 'get', [
			  'sequential' => 1,
			  'return' => ["email", "first_name", "last_name", "street_address", "city", "state_province_name", "country", "postal_code"],
			  'id' => $id,
			])['values'][0];
		} catch (Exception $e) {
			watchdog(
			  'NCN-Civi-Zoom CiviRules Action (AddToZoom)',
			  'Something went wrong with getting contact data.',
			  array(),
			  WATCHDOG_INFO
			);
		}

		return $result;
	}

	/**
	 * Add's the given participant data as a single participant
	 * to a Zoom Webinar with the given id.
	 *
	 * @param array $participant participant data where email, first_name, and last_name are required
	 * @param int $webinar id of an existing Zoom webinar
	 */
	private function addParticipant($participant, $webinar, $triggerData) {
		$settings = CRM_NcnCiviZoom_Utils::getZoomSettings();
		$url = $settings['base_url'] . "/webinars/$webinar/registrants";
		$token = $this->createJWTToken();

		$response = Zttp::withHeaders([
			'Content-Type' => 'application/json;charset=UTF-8',
			'Authorization' => "Bearer $token"
		])->post($url, $participant);

		// Alert to user on success.
		if ($response->isOk()) {
			$firstName = $participant['first_name'];
			$lastName = $participant['last_name'];

			$this->logAction('Participant Added to Zoom', $triggerData, \PSR\Log\LogLevel::INFO);

			CRM_Core_Session::setStatus(
				"$firstName $lastName was added to Zoom Webinar $webinar.",
				ts('Participant added!'),
				'success'
			);
		} else {
			$this->logAction('Something went wrong when adding participant to Zoom', $triggerData, \PSR\Log\LogLevel::INFO);
		}
	}

	private function createJWTToken() {
		$settings = CRM_NcnCiviZoom_Utils::getZoomSettings();
		$key = $settings['secret_key'];
		$payload = array(
		    "iss" => $settings['api_key'],
		    "exp" => strtotime('+1 hour')
		);
		$jwt = JWT::encode($payload, $key);

		return $jwt;
	}

	/**
	 * Method to return the url for additional form processing for action
	 * and return false if none is needed
	 *
	 * @param int $ruleActionId
	 * @return bool
	 * @access public
	 */
	public function getExtraDataInputUrl($ruleActionId) {
  		return FALSE;
	}

}