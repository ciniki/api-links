<?php
//
// Description
// ===========
// This method will return the existing categories and tags for links.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id: 		The ID of the business to get the item from.
// 
// Returns
// -------
//
function ciniki_links_linkTags($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'links', 'private', 'checkAccess');
    $rc = ciniki_links_checkAccess($ciniki, $args['business_id'], 'ciniki.links.linkTags'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
	$modules = $rc['modules'];

	$rsp = array('stat'=>'ok');

	//
	// Get the tags and link counts for each
	//
	$strsql = "SELECT ciniki_link_tags.tag_type, ciniki_link_tags.tag_name, "
		. "COUNT(ciniki_links.id) AS num_tags "
		. "FROM ciniki_link_tags "
		. "LEFT JOIN ciniki_links ON ("
			. "ciniki_link_tags.link_id = ciniki_links.id "
			. "AND ciniki_links.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") "
		. "WHERE ciniki_link_tags.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "GROUP BY ciniki_link_tags.tag_type, ciniki_link_tags.tag_name "
		. "ORDER BY ciniki_link_tags.tag_type, ciniki_link_tags.tag_name "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.links', array(
		array('container'=>'types', 'fname'=>'tag_type', 'name'=>'type',
			'fields'=>array('type'=>'tag_type')),
		array('container'=>'tags', 'fname'=>'tag_name', 'name'=>'tag',
			'fields'=>array('name'=>'tag_name', 'count'=>'num_tags')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$rsp = array('stat'=>'ok', 'tags'=>array(), 'categories'=>array());
	if( isset($rc['types']) ) {
		foreach($rc['types'] as $type) {
			if( $type['type']['type'] == '10' ) {
				$rsp['categories'] = $type['type']['tags'];
			} elseif( $type['type']['type'] == '40' ) {
				$rsp['tags'] = $type['type']['tags'];
			} 
		}
	}
	return $rsp;
}
?>