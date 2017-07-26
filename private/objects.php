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
        'o_name'=>'donation',
        'o_container'=>'donations',
        'sync'=>'yes',
        'table'=>'ciniki_donations',
        'fields'=>array(
            'customer_id'=>array('name'=>'Customer', 'ref'=>'ciniki.customers.customer'),
            'receipt_number'=>array('name'=>'Receipt Number'),
            'category'=>array('name'=>'Category', 'default'=>''),
            'name'=>array('name'=>'Name'),
            'address1'=>array('name'=>'Address 1'),
            'address2'=>array('name'=>'Address 2'),
            'city'=>array('name'=>'City'),
            'province'=>array('name'=>'Province'),
            'postal'=>array('name'=>'Postal'),
            'country'=>array('name'=>'Country'),
            'date_received'=>array('name'=>'Date Received'),
            'amount'=>array('name'=>'Amount', 'ref'=>'ciniki.images.image'),
            'date_issued'=>array('name'=>'Date Issued'),
            'location_issued'=>array('name'=>'Location Issued'),
            'advantage_amount'=>array('name'=>'Advantage Amount', 'default'=>'0'),
            'advantage_description'=>array('name'=>'Advantage Description', 'default'=>''),
            'property_description'=>array('name'=>'Property Description', 'default'=>''),
            'appraised_by'=>array('name'=>'Appraised By', 'default'=>''),
            'appraiser_address'=>array('name'=>'Appraiser Address', 'default'=>''),
            'notes'=>array('name'=>'', 'default'=>''),
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
