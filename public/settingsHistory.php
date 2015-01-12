<?php
//
// Description
// -----------
// This function will return the list of changes made to a field in donations settings.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the details for.
// setting:				The setting to get the history for.
//
// Returns
// -------
//
function ciniki_donations_settingsHistory($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'setting'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Setting'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'donations', 'private', 'checkAccess');
	$rc = ciniki_donations_checkAccess($ciniki, $args['business_id'], 'ciniki.donations.settingsHistory');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
	return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.donations', 'ciniki_donations_history', 
		$args['business_id'], 'ciniki_donation_settings', $args['setting'], 'detail_value');
}
?>
