<?php
//
// Description
// ===========
// This method will return all the information about an donation.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant the donation is attached to.
// donation_id:     The ID of the donation to get the details for.
// 
// Returns
// -------
//
function ciniki_donations_donationLoad($ciniki, $tnid, $donation_id) {
    //
    // Load the tenant intl settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'mysql');

    $strsql = "SELECT ciniki_donations.id, "
        . "ciniki_donations.customer_id, "
        . "ciniki_donations.receipt_number, "
        . "ciniki_donations.category, "
        . "ciniki_donations.name, "
        . "ciniki_donations.address1, "
        . "ciniki_donations.address2, "
        . "ciniki_donations.city, "
        . "ciniki_donations.province, "
        . "ciniki_donations.postal, "
        . "ciniki_donations.country, "
        . "DATE_FORMAT(ciniki_donations.date_received, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS date_received, "
        . "DATE_FORMAT(ciniki_donations.date_received, '" . ciniki_core_dbQuote($ciniki, '%Y') . "') AS donation_year, "
        . "ciniki_donations.amount, "
        . "DATE_FORMAT(ciniki_donations.date_issued, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS date_issued, "
        . "ciniki_donations.location_issued, "
        . "ciniki_donations.advantage_amount, "
        . "ciniki_donations.advantage_description, "
        . "ciniki_donations.property_description, "
        . "ciniki_donations.appraised_by, "
        . "ciniki_donations.appraiser_address, "
        . "ciniki_donations.notes "
        . "FROM ciniki_donations "
        . "WHERE ciniki_donations.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_donations.id = '" . ciniki_core_dbQuote($ciniki, $donation_id) . "' "
        . "";
    
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.donations', array(
        array('container'=>'donations', 'fname'=>'id', 'name'=>'donation',
            'fields'=>array('id', 'receipt_number', 'category', 'name', 
                'address1', 'address2', 'city', 'province', 'postal', 'country',
                'date_received', 'donation_year', 'amount', 'date_issued', 'location_issued',
                'advantage_amount', 'advantage_description',
                'property_description', 'appraised_by', 'appraiser_address', 'notes')),
    ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['donations']) || !isset($rc['donations'][0]) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.donations.4', 'msg'=>'Unable to find donation'));
    }
    $donation = $rc['donations'][0]['donation'];

    $donation['amount_display'] = numfmt_format_currency($intl_currency_fmt, 
        $donation['amount'], $intl_currency);
    $donation['advantage_amount_display'] = numfmt_format_currency($intl_currency_fmt, 
        $donation['advantage_amount'], $intl_currency);

    return array('stat'=>'ok', 'donation'=>$donation);
}
?>
