<?php
//
// Description
// -----------
// This function will return the data for customer(s) to be displayed in the IFB display panel.
// The request might be for 1 individual, or multiple customer ids for a family.
//
// Arguments
// ---------
// ciniki:
// tnid:     The ID of the tenant to get events for.
//
// Returns
// -------
//
function ciniki_donations_hooks_uiCustomersData($ciniki, $tnid, $args) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');

    //
    // Default response
    //
    $rsp = array('stat'=>'ok', 'tabs'=>array());

    //
    // Get the list of exhibits
    //
    $strsql = "SELECT donations.id, "
        . "donations.name, "
        . "DATE_FORMAT(donations.date_received, '%b %m, %Y') AS date_received "
        . "FROM ciniki_donations AS donations "
        . "WHERE donations.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    if( isset($args['customer_id']) ) {
        $strsql .= "AND donations.customer_id = '" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "' ";
    } elseif( isset($args['customer_ids']) && count($args['customer_ids']) > 0 ) {
        $strsql .= "AND donations.customer_id IN (" . ciniki_core_dbQuoteIDs($ciniki, $args['customer_ids']) . ") ";
    } else {
        return $rsp;
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.donations', array(
        array('container'=>'donations', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'date_received')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.donations.9', 'msg'=>'Unable to load donations', 'err'=>$rc['err']));
    }
    $donations = isset($rc['donations']) ? $rc['donations'] : array();

    $sections = array(
        'ciniki.donations.donors' => array(
            'label' => 'Donations',
            'type' => 'simplegrid', 
            'num_cols' => 2,
            'headerValues' => array('Name', 'Date'),
            'cellClasses' => array('', ''),
            'noData' => 'No donations',
            'data' => $donations,
            'cellValues' => array(
                '0' => 'd.name;',
                '1' => 'd.date_received;',
                ),
            ),
        );

    //
    // Add a tab the customer UI data screen with the certificate list
    //
    $rsp['tabs'][] = array(
        'id' => 'ciniki.donations.donors',
        'label' => 'Donations',
        'sections' => $sections,
        );

    return $rsp;
}
?>
