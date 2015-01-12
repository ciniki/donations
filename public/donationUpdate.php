<?php
//
// Description
// ===========
// This method will update an donation in the database.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business the donation is attached to.
// name:			(optional) The new name of the donation.
// url:				(optional) The new URL for the donation website.
// description:		(optional) The new description for the donation.
// start_date:		(optional) The new date the donation starts.  
// end_date:		(optional) The new date the donation ends, if it's longer than one day.
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_donations_donationUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'donation_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Donation'), 
		'receipt_number'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Receipt Number'), 
		'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
		'address1'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Address Line 1'), 
		'address2'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Address Line 2'), 
		'city'=>array('required'=>'no', 'blank'=>'yes',  'name'=>'City'), 
		'province'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Province'), 
		'postal'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Postal'), 
		'country'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Country'), 
		'date_received'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Date Received'), 
		'amount'=>array('required'=>'no', 'blank'=>'no', 'type'=>'currency', 'name'=>'Amount'), 
		'date_issued'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Date Issued'), 
		'location_issued'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Location Issued'), 
		'advantage_amount'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'currency', 'name'=>'Advantage Amount'), 
		'advantage_description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Advantage Description'), 
		'property_description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Property Description'), 
		'appraised_by'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Appraised By'), 
		'appraiser_address'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Appraised Address'), 
		'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'donations', 'private', 'checkAccess');
    $rc = ciniki_donations_checkAccess($ciniki, $args['business_id'], 'ciniki.donations.donationUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Start transaction
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.donations');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Update the donation in the database
	//
	$rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.donations.donation', $args['donation_id'], $args, 0x04);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.donations');
		return $rc;
	}

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
	$rc = ciniki_donations_donationLoad($ciniki, $args['business_id'], $args['donation_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	return array('stat'=>'ok', 'donation'=>$rc['donation']);
}
?>
