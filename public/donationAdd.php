<?php
//
// Description
// -----------
// This method will add a new donation for the business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to add the donation to.
// name:			The name of the donation.
// url:				(optional) The URL for the donation website.
// description:		(optional) The description for the donation.
// start_date:		(optional) The date the donation starts.  
// end_date:		(optional) The date the donation ends, if it's longer than one day.
//
// Returns
// -------
// <rsp stat="ok" id="42">
//
function ciniki_donations_donationAdd(&$ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'customer_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Customer'),
		'receipt_number'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Receipt Number'), 
		'category'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Category'), 
		'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
		'address1'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Address Line 1'), 
		'address2'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Address Line 2'), 
		'city'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'City'), 
		'province'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Province'), 
		'postal'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Postal'), 
		'country'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Country'), 
		'date_received'=>array('required'=>'yes', 'blank'=>'no', 'type'=>'date', 'name'=>'Date Received'), 
		'amount'=>array('required'=>'yes', 'blank'=>'no', 'type'=>'currency', 'name'=>'Amount'), 
		'date_issued'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'type'=>'date', 'name'=>'Date Issued'), 
		'location_issued'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Location Issued'), 
		'advantage_amount'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'type'=>'currency', 'name'=>'Advantage Amount'), 
		'advantage_description'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Advantage Description'), 
		'property_description'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Property Description'), 
		'appraised_by'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Appraised By'), 
		'appraiser_address'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Appraiser Address'), 
		'notes'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Notes'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner/employee
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'donations', 'private', 'checkAccess');
	$ac = ciniki_donations_checkAccess($ciniki, $args['business_id'], 'ciniki.donations.donationAdd');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Start transaction
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.donations');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Add the donation to the database
	//
	$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.donations.donation', $args, 0x04);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.donations');
		return $rc;
	}
	$donation_id = $rc['id'];

	//
	// Commit the transaction
	//
	$rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.donations');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'donations');

	//
	// Load donation
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'donations', 'private', 'donationLoad');
	$rc = ciniki_donations_donationLoad($ciniki, $args['business_id'], $donation_id);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok', 'id'=>$donation_id, 'donation'=>$rc['donation']);
}
?>
