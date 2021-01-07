<?php
use CRM_Ncnciviapi_ExtensionUtil as E;

use Firebase\JWT\JWT;
use Zttp\Zttp;


/**
 * Participant.GenerateWebinarAttendance specification
 *
 * Makes sure that the verification token is provided as a parameter
 * in the request to make sure that request is from a reliable source.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_zoomevent_generatezoomattendance_spec(&$spec) {
	$spec['days'] = [
    'title' => 'Select Events ended in past x Days',
    'description' => 'Events ended how many days before you need to select?',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  ];
}

/**
 * Participant.GenerateWebinarAttendance API
 *
 * Designed to be called by a Zoom Event Subscription (event: webinar.ended).
 * Once invoked, it gets the absent registrants from the webinar that just ended.
 *
 * Then, it gets the event associated with the webinar, as well as, the
 * registered participants of the event.
 *
 * Absent registrants are then subtracted from registered participants and,
 * the remaining participants' statuses are set to Attended.
 *
 * @param array $params
 *
 * @return array
 *   Array containing data of found or newly created contact.
 *
 * @see civicrm_api3_create_success
 *
 */
function civicrm_api3_zoomevent_generatezoomattendance($params) {
	$allAttendees = [];
	$days = $params['days'];
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
	foreach ($allEvents as $key => $value) {
		$eventIds[] = $value['id'];
	}
	foreach ($eventIds as $eventId) {
		CRM_Core_Error::debug_var('eventId', $eventId);
		$list = CRM_CivirulesActions_Participant_AddToZoom::getZoomAttendeeOrAbsenteesList($eventId);
		if(empty($list)){
			continue;
		}
		$webinarId = getWebinarID($eventId);
		$meetingId = getMeetingID($eventId);
		if(!empty($webinarId)){
			$attendees = selectAttendees($list, $eventId, "Webinar");
		}elseif(!empty($meetingId)){
			$attendees = selectAttendees($list, $eventId, "Meeting");
		}
		updateAttendeesStatus($attendees, $eventId);
		$allAttendees[$eventId] = $attendees;
		// $zoomAccountId = CRM_NcnCiviZoom_Utils::getZoomAccountIdByEventId($eventId);
		// if(empty($zoomAccountId)){
		// 	continue;
		// }
		// $settings = CRM_NcnCiviZoom_Utils::getZoomSettings($zoomAccountId);
		// $key = $settings['secret_key'];
		// $payload = array(
		//     "iss" => $settings['api_key'],
		//     "exp" => strtotime('+1 hour')
		// );
		// $jwt = JWT::encode($payload, $key);
		// $webinarId = getWebinarID($eventId);
		// $meetingId = getMeetingID($eventId);
		// $page = 0;
		// if(!empty($webinarId)){
		// 	$entityId = $webinarId;
		// 	$url = $settings['base_url'] . "/past_webinars/$webinar/absentees?page=$page";
		// 	$entity = "Webinar";
		// }elseif(!empty($meetingId)){
		// 	$entityId = $meetingId;
		// 	$url = $settings['base_url'] . "/past_meetings/$meetingId/participants?page=$page";
		// 	$entity = "Meeting";
		// }else{
		// 	continue;
		// }

		// $token = $jwt;
		// // Get absentees from Zoom API
		// $response = Zttp::withHeaders([
		// 	'Content-Type' => 'application/json;charset=UTF-8',
		// 	'Authorization' => "Bearer $token"
		// ])->get($url);

		// $attendees = [];
		// if($entity == "Webinar"){
		// 	$pages = $response->json()['page_count'];

		// 	// Store registrants who did not attend the webinar
		// 	$absentees = $response->json()['registrants'];

		// 	$absenteesEmails = [];

		// 	while($page < $pages) {
		// 		foreach($absentees as $absentee) {
		// 			$email = $absentee['email'];

		// 			array_push($absenteesEmails, "'$email'");
		// 		}

		// 		$attendees = array_merge($attendees, selectAttendees($absenteesEmails, $eventId));

		// 		$page++;

		// 		// Get and loop through all of webinar registrants
		// 		$url = $settings['base_url'] . "/past_webinars/$webinar/absentees?page=$page";

		// 		// Get absentees from Zoom API
		// 		$response = Zttp::withHeaders([
		// 			'Content-Type' => 'application/json;charset=UTF-8',
		// 			'Authorization' => "Bearer $token"
		// 		])->get($url);

		// 		// Store registrants who did not attend the webinar
		// 		$absentees = $response->json()['registrants'];

		// 		$absenteesEmails = [];
		// 	}
		// }elseif ($entity == "Meeting") {
		// 	$attendeesEmails = [];
		// 	$page = 1;
		// 	do {
		// 		$url = $settings['base_url'] . "/past_meetings/$meetingId/participants?page=$page";
		// 		// Get absentees from Zoom API
		// 		$response = Zttp::withHeaders([
		// 			'Content-Type' => 'application/json;charset=UTF-8',
		// 			'Authorization' => "Bearer $token"
		// 		])->get($url);
		// 		$participants = $response->json()['participants'];
		// 		foreach ($participants as $key => $value) {
		// 			$attendeesEmails[] = $value['user_email'];
		// 		}
		// 		$page++;
		// 		$pageCount = $response->json()['page_count'];
		// 	} while ($page <= $pageCount);
		// 	$attendees = selectAttendees($attendeesEmails, $eventId, "Meeting");
		// }
		// updateAttendeesStatus($attendees, $eventId);
		// $allAttendees[] = $attendees;
	}
	$return['allAttendees'] = $allAttendees;

	return civicrm_api3_create_success($return, $params, 'Event');
}

/**
 * Queries for the registered participants that weren't absent
 * during the webinar.
 * @param  array $absenteesEmails emails of registrants absent from the webinar
 * @param  int $event the id of the webinar's associated event
 * @return array participants (email, participant_id, contact_id) who weren't absent
 */
function selectAttendees($emails, $event, $entity = "Webinar") {
	if($entity == "Webinar"){
		$absenteesEmails = join("','",$emails);

		$selectAttendees = "
			SELECT
				e.email,
				p.contact_id,
				p.id AS participant_id
			FROM civicrm_participant p
			LEFT JOIN civicrm_email e ON p.contact_id = e.contact_id
			WHERE
				e.email NOT IN ('$absenteesEmails') AND
		    	p.event_id = {$event}";
	}elseif($entity == "Meeting"){
		$attendeesEmails = join("','",$emails);

		$selectAttendees = "
			SELECT
				e.email,
				p.contact_id,
				p.id AS participant_id
			FROM civicrm_participant p
			LEFT JOIN civicrm_email e ON p.contact_id = e.contact_id
			WHERE
				e.email IN ('$attendeesEmails') AND
		    	p.event_id = {$event}";
	}
	// Run query
	$query = CRM_Core_DAO::executeQuery($selectAttendees);

	$attendees = [];

	while($query->fetch()) {
		array_push($attendees, [
			'email' => $query->email,
			'contact_id' => $query->contact_id,
			'participant_id' => $query->participant_id
		]);
	}

	return $attendees;
}

/**
 * Set the status of the registrants who weren't absent to Attended.
 * @param  array $attendees registrants who weren't absent
 * @param  int $event the event associated with the webinar
 *
 */
function updateAttendeesStatus($attendees, $event) {
	foreach($attendees as $attendee) {
		$rr = civicrm_api3('Participant', 'create', [
		  'event_id' => $event,
		  'id' => $attendee['participant_id'],
		  'status_id' => "Attended",
		]);
	}
}


/**
 * Get an event's webinar id
 * @param  int $event The event's id
 * @return string The event's webinar id
 */
function getWebinarID($eventId) {
	$result;
	$customField = CRM_NcnCiviZoom_Utils::getWebinarCustomField();
	try {
		$apiResult = civicrm_api3('Event', 'get', [
		  'sequential' => 1,
		  'return' => [$customField],
		  'id' => $eventId,
		]);
		$result = null;
		if(!empty($apiResult['values'][0][$customField])){
			// Remove any empty spaces
			$result = trim($apiResult['values'][0][$customField]);
			$result = str_replace(' ', '', $result);
		}
	} catch (Exception $e) {
		throw $e;
	}

	return $result;
}

/**
 * Get an event's Meeting id
 * @param  int $event The event's id
 * @return string The event's Meeting id
 */
function getMeetingID($eventId) {
	$result;
	$customField = CRM_NcnCiviZoom_Utils::getMeetingCustomField();
	try {
		$apiResult = civicrm_api3('Event', 'get', [
		  'sequential' => 1,
		  'return' => [$customField],
		  'id' => $eventId,
		]);
		$result = null;
		if(!empty($apiResult['values'][0][$customField])){
			// Remove any empty spaces
			$result = trim($apiResult['values'][0][$customField]);
			$result = str_replace(' ', '', $result);
		}
	} catch (Exception $e) {
		throw $e;
	}

	return $result;
}


/**
 * Get Recent Zoom registrants specs
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_zoomevent_getrecentzoomregistrants_spec(&$spec) {
	$spec['mins'] = [
    'title' => 'How many minutes before?',
    'description' => 'Enter the minutes, as you want the notification of the zoom registrants. By default it will be 60 minutes.',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 0,
  ];

	$spec['to_emails'] = [
    'title' => 'Email address',
    'description' => 'Enter the Email addresses(seperated by comma) to which you want the regitrants list to be sent.',
    'type' => CRM_Utils_Type::T_TEXT,
    'api.required' => 0,
  ];
}



/**
 * Get Recent Zoom registrants
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function civicrm_api3_zoomevent_getrecentzoomregistrants($params) {
	if(empty($params['mins'])){
		$params['mins'] = 60;
	}

	$result = [];

	$events = CRM_NcnCiviZoom_Utils::getUpcomingEventsList();
	foreach ($events as $key => $event) {
		$registrantsList = CRM_CivirulesActions_Participant_AddToZoom::getZoomRegistrants($event['id']);
		if(!empty($registrantsList)){
			$recentRegistrants = CRM_NcnCiviZoom_Utils::filterZoomRegistrantsByTime($registrantsList, $params['mins']);
			if(!empty($recentRegistrants)){
				$notesUpdateMessage = CRM_NcnCiviZoom_Utils::updateZoomRegistrantsToNotes($event['id'], $registrantsList);
				$result[$event['id']]['Notes Update Message'] = $notesUpdateMessage;
				if(!empty($params['to_emails'])){
					$emailSentMessage = CRM_NcnCiviZoom_Utils::sendZoomRegistrantsToEmail($params['to_emails'], $recentRegistrants, $event['title']);
					$result[$event['id']]['Email Update Message'] = $emailSentMessage;
				}
			}else{
				$result[$event['id']]['Notes Update Message'] = 'No recent registrants to update.';
			}
		}else{
			$result[$event['id']]['Message'] = 'No Registrants to Update';
		}
	}

	return civicrm_api3_create_success($result, $params, 'Event');
}


/**
 * Participant.Sync Zoom Data specification
 *
 * Makes sure that the verification token is provided as a parameter
 * in the request to make sure that request is from a reliable source.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_zoomevent_synczoomdata_spec(&$spec) {
	$spec['days'] = [
    'title' => 'Select Events ended in past x Days',
    'description' => 'Events ended how many days before you need to select?',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  ];
}


/**
 * Sync Zoom Webinar Participants Data
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function civicrm_api3_zoomevent_synczoomdata($params) {
	$allAttendees = [];
	$days = $params['days'];
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
	foreach ($allEvents as $key => $value) {
		$eventIds[] = $value['id'];
	}
	$allUpdatedParticpants = [];
	foreach ($eventIds as $eventId) {
		$updatedParticpants = [];
		$list = CRM_CivirulesActions_Participant_AddToZoom::getZoomParticipantsData($eventId);
		if(empty($list)){
			continue;
		}

		$emails = [];
		foreach ($list as $key => $value) {
			$emails[] = $key;
		}
		$webinarId = getWebinarID($eventId);
		$meetingId = getMeetingID($eventId);
		if(!empty($webinarId)){
			$attendees = selectZoomParticipants($emails, $eventId);
		}elseif(!empty($meetingId)){
			$attendees = selectZoomParticipants($emails, $eventId);
		}
		foreach ($attendees as $attendee) {
			$updatedParticpants[$attendee['participant_id']] = CRM_NcnCiviZoom_Utils::updateZoomParticipantData($attendee['participant_id'], $list[$attendee['email']]);
		}
		$allUpdatedParticpants[$eventId] = $updatedParticpants;
	}

	$return['all_updated_participants'] = $allUpdatedParticpants;

	return civicrm_api3_create_success($return, $params, 'Event');
}


/**
 * Selects the zoom participants for for the event(webinar/meeting) using the given array of emails
 *
 * @param  array emails of registrants from the webinar/meeting
 * @param  int $event the id of the webinar's/meeting's associated event
 * @return array of zoom webinar/meeting registrants in the civi (email, participant_id, contact_id)
 */
function selectZoomParticipants($emails, $event) {

	$participantsEmails = join("','",$emails);

	$selectAttendees = "
		SELECT
			e.email,
			p.contact_id,
			p.id AS participant_id
		FROM civicrm_participant p
		LEFT JOIN civicrm_email e ON p.contact_id = e.contact_id
		WHERE
			e.email IN ('$participantsEmails') AND
	    	p.event_id = {$event}";

	// Run query
	$query = CRM_Core_DAO::executeQuery($selectAttendees);

	$attendees = [];

	while($query->fetch()) {
		array_push($attendees, [
			'email' => $query->email,
			'contact_id' => $query->contact_id,
			'participant_id' => $query->participant_id
		]);
	}

	return $attendees;
}