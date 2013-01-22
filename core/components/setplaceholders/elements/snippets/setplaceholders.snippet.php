<?php
/**
 * setPlaceholders
 * Copyright 2012 Jason Grant
 *
 * setPlaceholders is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * setPlaceholders is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * setPlaceholders; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 *
 * @package setPlaceholders
 * @author Jason Grant
 * @version 1.0.1-pl
 */

/**
 * Full documentation, bug reports, etc.
 * https://github.com/oo12/setPlaceholders
 *
 * Variables
 * ---------
 *
 * @var modX $modx
 * @var input $input
 * @var options $options
 *
 *
 * Properties
 * ----------
 *
 * @property id - (integer)
 * @property fields - (string)
 * @property prefix - (string)
 * @property placeholders - (string)
 * @property output - (boolean)
 * @property delimiter - (string)
 *
 * See the default properties for a description of each.
 * Parameters are all optional, but you'll want to specify either &fields
 * or &placeholders or both to accomplish anything.
 *
 *
 * Examples
 * --------
 *
 * [[setPlaceholders? &id=`13` &fields=`pagetitle || tv.someTV || get.person`
 *   &placeholders=`color==#a35c0a || params == w=240&h=360&q=65`]]
 *
 * Sets the placeholders:
 *   [[+sph.pagetitle]] - Resource 13's pagetitle
 *   [[+sph.tv.someTV]] - value of resource 13's TV someTV
 *   [[+sph.get.person]] - value of $_GET['person'] (from the URL)
 *   [[+color]] - #a35c0a
 *   [[+params]] - w=240&h=360&q=65
 *
 *
 * As a getResourceField replacement:
 *
 * [[setPlaceholders? &fields=`parent.tv.someTV !! No such TV` &output=`1`]]
 *
 * Returns the value of someTV for the current resource's parent, or
 * "No such TV" if it's not found.  It also puts this value in
 * [[+sph.parent.tv.someTV]]
 *
 *
 * As an output filter:
 *
 * [[*someTV:eq=`foo`:then=`bar`:else=`nobar`:setPlaceholders=`fb`]]
 *
 * Returns the value of the expression and also stores it in [[+fb]]
 *
 *
 * Whitespace around || and !! and == is fine; it'll be trimmed off if present.
 *
 * @package setPlaceholders
 **/

if ( isset($input) && $options ) {  // if we're being used as an output filter
	$modx->setPlaceholder($options, $input);
	return $input;
}

// handle options
$id = $id ? intval($id) : $modx->resource->get('id');
$fields = $fields ? explode('||', $fields) : array();
$prefix = isset($prefix) ? $prefix : 'sph.';
$placeholders = $placeholders ? explode('||', $placeholders) : array();
$delimiter = isset($delimiter) ? $delimiter : ',';

if ($fields) {  // if we'll need to be looking up anything..
	$resource = $modx->getObject('modResource', $id);
}

$p = array();  // placeholder storage
$parents = array(); // cache for parent resources

foreach ($fields as $field) {
	// prep $field, separate out any default and prefixes
	$field = explode('!!', $field);
	$fieldName = $field[0] = trim( $field[0] );
	$fieldPrefixes = explode('.', $fieldName);

	$doc = $resource;
	$value = NULL;

	if ( $fieldPrefixes[0] === 'get' ) {
		$value = $_GET[substr($fieldName, 4)];
	}
	elseif ( $fieldPrefixes[0] === 'post' ) {
		$value = $_POST[substr($fieldName, 5)];
	}
	else {
		for ($idx = 0; $fieldPrefixes[$idx] === 'parent' && $doc; ++$idx) {
			if ( !$parents[$idx] )  {
				$parents[] = $modx->getObject('modResource', $doc->get('parent'));  // Get the parent object and cache it
			}
			$doc = $parents[$idx];
			$fieldName = substr($fieldName, 7);
		}
		if ($doc) {  // if we've got a valid resource
			if ( $fieldPrefixes[$idx] === 'tv' ) {
				$value = $doc->getTVValue( substr($fieldName, 3) );
			}
			else {
				$value = $doc->get($fieldName);
			}
		}
	}

	if ( $value == '' && $field[1] ) {  // if we didn't find a value, use the default
		$value = trim( $field[1] );
	}
	if ( $value != '') {
		$p[ $prefix . $field[0] ] = $value;  // key: prefix + field name
	}
}

foreach ($placeholders as $placeholder) { // add any user-defined placeholders
	$ph = explode('==', $placeholder);
	$p[ trim($ph[0]) ] = trim($ph[1]);
}

// Output our results
$modx->setPlaceholders($p);
return ($output ? implode($delimiter, $p) : '');