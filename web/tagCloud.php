<?php
//
// Description
// -----------
//
// Arguments
// ---------
// ciniki:
// settings:		The web settings structure.
// business_id:		The ID of the business to get events for.
// type:			The type of the tag.
//
//
// Returns
// -------
//
function ciniki_links_web_tagCloud($ciniki, $settings, $business_id, $args) {

	//
	// Load the business settings
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
	$rc = ciniki_businesses_intlSettings($ciniki, $business_id);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$intl_timezone = $rc['settings']['intl-default-timezone'];
	$intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
	$intl_currency = $rc['settings']['intl-default-currency'];

	//
	// Build the query to get the tags
	$strsql = "SELECT ciniki_link_tags.tag_name, "
		. "ciniki_link_tags.permalink, "
		. "COUNT(ciniki_links.id) AS num_tags "
		. "FROM ciniki_link_tags, ciniki_links "
		. "WHERE ciniki_link_tags.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_link_tags.tag_type = '" . ciniki_core_dbQuote($ciniki, $args['tag_type']) . "' "
		. "AND ciniki_link_tags.link_id = ciniki_links.id "
		. "GROUP BY tag_name "
		. "ORDER BY tag_name "
		. "";
	//
	// Get the list of posts, sorted by publish_date for use in the web CI List Categories
	//
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.links', array(
		array('container'=>'tags', 'fname'=>'permalink', 
			'fields'=>array('name'=>'tag_name', 'permalink', 'num_tags')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['tags']) ) {
		$tags = $rc['tags'];
	} else {
		$tags = array();
	}

	return array('stat'=>'ok', 'tags'=>$tags);
}
?>