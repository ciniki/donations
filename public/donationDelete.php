<?php
//
// Description
// -----------
// This method will delete a donation from the tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the donation is attached to.
// donation_id:         The ID of the donation to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_donations_donationDelete(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'donation_id'=>array('required'=>'yes', 'default'=>'', 'blank'=>'yes', 'name'=>'Delete'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'donations', 'private', 'checkAccess');
    $ac = ciniki_donations_checkAccess($ciniki, $args['tnid'], 'ciniki.donations.donationDelete');
    if( $ac['stat'] != 'ok' ) {
        return $ac;
    }

    //
    // Get the uuid of the donation to be deleted
    //
    $strsql = "SELECT uuid FROM ciniki_donations "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['donation_id']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.donations', 'donation');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['donation']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.donations.5', 'msg'=>'The donation does not exist'));
    }
    $donation_uuid = $rc['donation']['uuid'];

    //
    // Start transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.donations');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Remove the donation
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.donations.donation', 
        $args['donation_id'], $donation_uuid, 0x04);
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
    // Update the last_change date in the tenant modules
    // Ignore the result, as we don't want to stop user updates if this fails.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'updateModuleChangeDate');
    ciniki_tenants_updateModuleChangeDate($ciniki, $args['tnid'], 'ciniki', 'donations');

    return array('stat'=>'ok');
}
?>
