<?php
//
// Description
// ===========
// This method will produce a PDF receipt of the donation.
//
// Arguments
// ---------
// 
// Returns
// -------
//
function ciniki_donations_donationReceipt(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'donation_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Donation'), 
//      'output'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'pdf', 'name'=>'Output Type'),
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
    $rc = ciniki_donations_checkAccess($ciniki, $args['tnid'], 'ciniki.donations.donationReceipt'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

    //
    // Load tenant details
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'tenantDetails');
    $rc = ciniki_tenants_tenantDetails($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['details']) && is_array($rc['details']) ) {   
        $tenant_details = $rc['details'];
    } else {
        $tenant_details = array();
    }

    //
    // Load the donation settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQueryDash');
    $rc = ciniki_core_dbDetailsQueryDash($ciniki, 'ciniki_donation_settings', 'tnid', $args['tnid'],
        'ciniki.donations', 'settings', 'receipt');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['settings']) ) {
        $donation_settings = $rc['settings'];
    } else {
        $donation_settings = array();
    }
    
    //
    // check for receipt-default-template
    //
    if( !isset($donations_settings['receipt-default-template']) 
        || $donations_settings['receipt-default-template'] == '' ) {
        $receipt_template = 'canadaDefault';
    } else {
        $receipt_template = $donations_settings['receipt-default-template'];
    }
    
    $rc = ciniki_core_loadMethod($ciniki, 'ciniki', 'donations', 'templates', $receipt_template);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $fn = $rc['function_call'];

    return $fn($ciniki, $args['tnid'], $args['donation_id'],
        $tenant_details, $donation_settings);
}
?>
