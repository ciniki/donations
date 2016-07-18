<?php
//
// Description
// -----------
// The module flags
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_donations_flags($ciniki, $modules) {
    $flags = array(
        // 0x01
        array('flag'=>array('bit'=>'1', 'name'=>'Categories')),
//      array('flag'=>array('bit'=>'2', 'name'=>'')),
//      array('flag'=>array('bit'=>'3', 'name'=>'')),
//      array('flag'=>array('bit'=>'4', 'name'=>'')),
        );

    return array('stat'=>'ok', 'flags'=>$flags);
}
?>
