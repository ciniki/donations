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
function ciniki_donations_donationCustomer($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'customer_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Customer'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'donations', 'private', 'checkAccess');
    $rc = ciniki_donations_checkAccess($ciniki, $args['tnid'], 'ciniki.donations.donationCustomer'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $modules = $rc['modules'];

    //
    // Load the tenant intl settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    //
    // Get the settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_donation_settings', 'tnid', $args['tnid'],
        'ciniki.donations', 'settings', '');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['settings']) ) {
        $settings = $rc['settings'];
    } else {
        $settings = array();
    }
    
    //
    // Get the customer details
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'customers', 'hooks', 'customerDetails');
    $rc = ciniki_customers_hooks_customerDetails($ciniki, $args['tnid'], 
        array('customer_id'=>$args['customer_id'], 'addresses'=>'yes'));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $customer = $rc['customer'];

    //
    // Select the last receipt number
    //
    $strsql = "SELECT MAX(CAST(receipt_number AS UNSIGNED)) AS curmax "
        . "FROM ciniki_donations "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.donations', 'last');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $receipt_number = '1';
    if( isset($rc['last']['curmax']) ) {
        $receipt_number = intval($rc['last']['curmax']) + 1;
    }


    $today = new DateTime('now', new DateTimeZone($intl_timezone));

    $rsp = array('stat'=>'ok', 'donation'=>array(
        'customer_id'=>$customer['id'],
        'receipt_number'=>$receipt_number,
        'name'=>$customer['display_name'],
        'address1'=>'',
        'address2'=>'',
        'city'=>'',
        'province'=>'',
        'postal'=>'',
        'country'=>'',
        'date_received'=>$today->format('M d, Y'),
        'amount'=>'',
        'amount_display'=>'$0.00',
        'date_issued'=>$today->format('M d, Y'),
        'location_issued'=>(isset($settings['default-location-issued'])?$settings['default-location-issued']:''),
        'advantage_amount'=>'',
        'advantage_description'=>'',
        'property_description'=>'',
        'appraised_by'=>'',
        'appraised_address'=>'',
        ));

    if( isset($customer['addresses']) ) {
        $address = NULL;
        // Check for billing address
        foreach($customer['addresses'] as $addr) {
            if( ($addr['address']['flags']&0x02) > 0 ) {
                $address = $addr['address'];
                break;
            }
        }
        // Check for billing/mailing address
        if( $address == NULL ) {
            foreach($customer['addresses'] as $addr) {
                if( ($addr['address']['flags']&0x04) > 0 ) {
                    $address = $addr['address'];
                }
            }
        }
        // Check for any address
        if( $address == NULL ) {
            foreach($customer['addresses'] as $addr) {
                if( ($addr['address']['flags']&0x07) > 0 ) {
                    $address = $addr['address'];
                }
            }
        }
        // Update address fields
        if( $address != NULL ) {
            $rsp['donation']['address1'] = (isset($address['address1'])?$address['address1']:'');
            $rsp['donation']['address2'] = (isset($address['address2'])?$address['address2']:'');
            $rsp['donation']['city'] = (isset($address['city'])?$address['city']:'');
            $rsp['donation']['province'] = (isset($address['province'])?$address['province']:'');
            $rsp['donation']['postal'] = (isset($address['postal'])?$address['postal']:'');
            $rsp['donation']['country'] = (isset($address['country'])?$address['country']:'');
        }
    }

    return $rsp;
}
?>
