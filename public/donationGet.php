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
function ciniki_donations_donationGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'donation_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Donation'), 
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
    $rc = ciniki_donations_checkAccess($ciniki, $args['tnid'], 'ciniki.donations.donationGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $modules = $rc['modules'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'donations', 'private', 'donationLoad');
    return ciniki_donations_donationLoad($ciniki, $args['tnid'], $args['donation_id']);
}
?>
