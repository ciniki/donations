<?php
//
// Description
// -----------
// This function will return the list of changes made to a field in donations settings.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the details for.
// setting:             The setting to get the history for.
//
// Returns
// -------
//
function ciniki_donations_settingsHistory($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'setting'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Setting'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner, or sys admin
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'donations', 'private', 'checkAccess');
    $rc = ciniki_donations_checkAccess($ciniki, $args['tnid'], 'ciniki.donations.settingsHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
    return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.donations', 'ciniki_donations_history', 
        $args['tnid'], 'ciniki_donation_settings', $args['setting'], 'detail_value');
}
?>
