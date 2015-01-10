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
			'name'=>array(),
			'address1'=>array(),
			'address2'=>array(),
			'city'=>array(),
			'province'=>array(),
			'postal'=>array(),
			'country'=>array(),
			'donation_date'=>array(),
			'amount'=>array('ref'=>'ciniki.images.image'),
			),
		'history_table'=>'ciniki_donation_history',
		);
	
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
