<?php

function get_fields_with_action_tag( $project_id, $instrument, $action_tag_regex ) {
	//Search for all fields in given instrument in given project.
	$sql = sprintf(
		"SELECT `field_name`,`misc` FROM `redcap_metadata` WHERE `project_id`='%s' AND `form_name`='%s';",
		db_real_escape_string($project_id),
		db_real_escape_string($instrument)
	);
	$q = db_query($sql);

	//Walk through each question and look if it matches given action tag regex.
	$action_tag_matches = array();
	while( $row = db_fetch_assoc($q) ) {
		if ( !is_null($row['misc']) && preg_match( $action_tag_regex, $row['misc'] ) )
			array_push( $action_tag_matches, $row['field_name'] );
	}
	return $action_tag_matches;
}

function isInstrumentUsedInEvent( $instrument, $event_id ) {
	//Search for all instruments in given event.
	$sql = sprintf( 
		"SELECT `form_name` FROM `redcap_events_forms` WHERE `event_id`='%s';",
		db_real_escape_string($event_id)
	);
	$q = db_query( $sql );
	//Walk through each instrument and test if it is the given one. If it is, then it is used in this event.
	while ( $row = db_fetch_assoc($q) ) {
		if( $instrument == $row['form_name'] ) return true;
	}
	return false;
}

function copy_from_last_event($project_id, $record, $instrument, $event_id, $group_id) {
	//Get all fields in this project and this instrument with the action tag @COPYFROMLASTEVENT.
	$has_given_action_tag = get_fields_with_action_tag( $project_id, $instrument, '/@COPYFROMLASTEVENT/' );

	//Search for last event that this isnstrument is used in.
	$events = REDCap::getEventNames(true);
	$previous_event_id = 0;
	foreach ( array_keys($events) as $id ) {
		if ($id == $event_id) break;
		else if ( isInstrumentUsedInEvent($instrument,$id) ) $previous_event_id = $id;
	}

	//Run only if there is previous event and if there are any fields with the action tag @COPYFROMLASTEVENT
	if ($previous_event_id && (!empty($has_given_action_tag)) ) {
		//get data for this project, this record and this instrument
		//from previous event for the fields that has the action tag @COPYFROMLASTEVENT
		$data = REDCap::getData( $project_id, 'array', $record, $has_given_action_tag, $previous_event_id );
		$data_for_this_record = array_pop($data);
		$data_for_this_record_and_event = array_pop($data_for_this_record);

		echo "<script type='text/javascript'>\n";
		echo "\tfunction getDataFromLastEvent () {\n";
		foreach ( $data_for_this_record_and_event as $field_name => $field_value ) {
			echo "\t\talert('$field_name: $field_value');\n";
		}
		echo "\t}\n";
		echo "</script>\n";
		echo "<button onClick='getDataFromLastEvent();'>Copy from last event</button>\n";
	}
}


function redcap_data_entry_form_top($project_id, $record, $instrument, $event_id, $group_id) {
	if ( REDCap::isLongitudinal() ) copy_from_last_event($project_id, $record, $instrument, $event_id, $group_id);
}
?>
