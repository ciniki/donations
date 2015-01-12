<?php
//
// Description
// -----------
// This method will return the list of donations for a business.  It is restricted
// to business owners and sysadmins.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get donations for.
//
// Returns
// -------
//
function ciniki_donations_donationList($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'year'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'year'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
    //  
    // Check access to business_id as owner, or sys admin. 
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'donations', 'private', 'checkAccess');
    $ac = ciniki_donations_checkAccess($ciniki, $args['business_id'], 'ciniki.donations.donationList');
    if( $ac['stat'] != 'ok' ) { 
        return $ac;
    }   

	//
	// Load the business intl settings
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
	$rc = ciniki_businesses_intlSettings($ciniki, $args['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$intl_timezone = $rc['settings']['intl-default-timezone'];
	$intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
	$intl_currency = $rc['settings']['intl-default-currency'];

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki, 'mysql');

	//
	// Get the range of years
	//
	$strsql = "SELECT DISTINCT DATE_FORMAT(date_received, '%Y') AS year "
		. "FROM ciniki_donations "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "ORDER BY year DESC "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQueryList');
	$rc = ciniki_core_dbQueryList($ciniki, $strsql, 'ciniki.donations', 'years', 'year');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$years = array();
	if( isset($rc['years']) ) {
		$years = $rc['years'];
	}

	if( (!isset($args['year']) || $args['year'] == '') && count($years) > 0 ) {
		$args['year'] = $years[0];
	}

	//
	// Load the donations for the year
	//
	$donations = array();
	$total_amount = 0;
	if( isset($args['year']) && $args['year'] != '' ) {
		$strsql = "SELECT id, "
			. "receipt_number, "
			. "name, "
			. "DATE_FORMAT(date_received, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS date_received, "
			. "amount "
			. "FROM ciniki_donations "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND date_received >= '" . ciniki_core_dbQuote($ciniki, $args['year']) . "-01-01' "
			. "AND date_received < '" . ciniki_core_dbQuote($ciniki, ($args['year']+1)) . "-01-01' "
			. "ORDER BY ciniki_donations.date_received DESC "
			. "";
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.donations', array(
			array('container'=>'donations', 'fname'=>'id', 'name'=>'donation',
				'fields'=>array('id', 'receipt_number', 'name', 'date_received', 'amount')),
				));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( isset($rc['donations']) ) {
			$donations = $rc['donations'];
			foreach($donations as $did => $donation) {
				$total_amount = bcadd($total_amount, $donation['donation']['amount'], 4);
				$donations[$did]['donation']['amount_display'] = numfmt_format_currency($intl_currency_fmt,
					$donation['donation']['amount'], $intl_currency);
			}
		} 
	}
	$total_amount_display = numfmt_format_currency($intl_currency_fmt, $total_amount, $intl_currency);

	return array('stat'=>'ok', 'years'=>implode(',', $years), 'donations'=>$donations, 'total_amount'=>$total_amount, 'total_amount_display'=>$total_amount_display);
}
?>
