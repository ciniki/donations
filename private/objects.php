<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_donations_objects($ciniki) {
	
	$objects = array();
	$objects['donation'] = array(
		'name'=>'Donation',
		'sync'=>'yes',
		'table'=>'ciniki_donations',
		'fields'=>array(
			'customer_id'=>array('ref'=>'ciniki.customers.customer'),
			'receipt_number'=>array(),
			'name'=>array(),
			'address1'=>array(),
			'address2'=>array(),
			'city'=>array(),
			'province'=>array(),
			'postal'=>array(),
			'country'=>array(),
			'date_received'=>array(),
			'amount'=>array('ref'=>'ciniki.images.image'),
			'date_issued'=>array(),
			'location_issued'=>array(),
			'advantage_amount'=>array('default'=>'0'),
			'advantage_description'=>array('default'=>''),
			'property_description'=>array('default'=>''),
			'appraised_by'=>array('default'=>''),
			'appraiser_address'=>array('default'=>''),
			'notes'=>array('default'=>''),
			),
		'history_table'=>'ciniki_donation_history',
		);
	$objects['setting'] = array(
		'type'=>'settings',
		'name'=>'Donations Settings',
		'table'=>'ciniki_donation_settings',
		'history_table'=>'ciniki_donation_history',
		);
	
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
