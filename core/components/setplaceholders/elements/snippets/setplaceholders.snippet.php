<?php
/**
 * setPlaceholders
 * Copyright 2012-2013 Jason Grant
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
 * @version 1.1.0-pl
 */

/**
 * Documentation, examples, bug reports, etc.
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
 * @property vars - (string)
 * @property prefix - (string)
 * @property output - (boolean)
 * @property delimiter - (string)
 * @property placeholders - (string)
 * @property fields - (string)  Deprecated.
 *
 * See the default properties for a description of each.
 *
 * @package setPlaceholders
 **/

if ( isset($input) && $options ) {  // if we're being used as an output filter
	$modx->setPlaceholder($options, $input);
	return $input;
}

// handle options
$id = $id ? (int) $id : $modx->resource->get('id');
$ph = $ph ? explode('||', $ph) : array();
$prefix = isset($prefix) ? $prefix : 'sph.';
$delimiter = isset($delimiter) ? $delimiter : ',';
$placeholders = $placeholders ? explode('||', $placeholders) : array();

/* Deprecated as of v1.1 */
$fields = $fields ? explode(',', $fields) : array();

if ( !function_exists('getVal') ) {
	function getVal($fieldName, $id) {
		global $modx;
		static $cache = array(); // cache for resources and parent IDs

		if ($fieldName[0] === '"') {  // check for a quoted value and skip parsing if found
			if ( substr($fieldName, -1) === '"' ) {  // remove a trailing " if present
				return( substr($fieldName, 1, -1) );
			}
			else {
				return( $field[0] = substr($fieldName, 1) );
			}
		}

		$fieldPrefixes = explode('.', $fieldName);

		if ( $fieldPrefixes[0] === 'get' ) {
			return( $_GET[substr($fieldName, 4)] );
		}
		elseif ( $fieldPrefixes[0] === 'post' ) {
			return( $_POST[substr($fieldName, 5)] );
		}
		else {
			$idx = 0;
			$fieldNameOffset = 0;
			if ( is_numeric($fieldPrefixes[0]) ) {  // check for a resource ID
				$id = (int) $fieldPrefixes[0];
				$fieldNameOffset += strlen($fieldPrefixes[0]) + 1;
				$idx = 1;
			}
			if ( substr($fieldPrefixes[$idx], 0, 7) === 'Uparent' ) {  // Ultimate parent
				$uparent_level = 2;
				$uparent = substr($fieldPrefixes[$idx], 7);
				if ( $uparent ) {  // read any Uparent index
					$uparent_level = 1 + (int) $uparent;
					$fieldNameOffset += strlen($uparent);
				}
				$cacheKey = $id . 'pids';
				if ( !isset($cache[$cacheKey]) ) {
					$cache[$cacheKey] = $modx->getParentIds($id);
				}
				$tmp = count($cache[$cacheKey]) - $uparent_level;
				$id = $cache[$cacheKey][ $tmp > 0 ? $tmp : 0 ];  // don't go past the immediate parent
				$fieldNameOffset += 8;
				++$idx;
			}
			if ( substr($fieldPrefixes[$idx], 0, 6) === 'parent' ) {  // regular parent
				$parent_level = 0;
				$parent = substr($fieldPrefixes[$idx], 6);
				if ( $parent ) {  // ready any parent index
					$parent_level = -1 + (int) $parent;
					$fieldNameOffset += strlen($parent);
				}
				$cacheKey = $id . 'pids';
				if ( !isset($cache[$cacheKey]) ) {
					$cache[$cacheKey] = $modx->getParentIds($id);
				}
				$tmp = count($cache[$cacheKey]) - 2;
				$id = $cache[$cacheKey][ $parent_level < $tmp ? $parent_level : $tmp ];  // don't go past top-most parent
				$fieldNameOffset += 7;
				++$idx;
			}
			$cacheKey = $id . 'id';
			if ( isset($cache[$cacheKey]) ) {
				$doc = $cache[$cacheKey];
			}
			else {
				$doc = $cache[$cacheKey] = $modx->getObject('modResource', $id);
			}
			if ($doc) {  // if we've got a valid resource
				if ( $fieldPrefixes[$idx] === 'tv' ) {  // get a TV
					return( $doc->getTVValue( substr($fieldName, $fieldNameOffset + 3) ) );
				}
				else {  // assume it's a field name
					return( $doc->get( substr($fieldName, $fieldNameOffset) ) );
				}
			}
		}
	}
}


$p = array();  // placeholder storage

foreach ($ph as $field) {
	$field = explode('!!', $field);  // separate out any default value
	$varname = explode('==', $field[0]);  // separate out any user-defined placeholder name
	if ( count($varname) === 1 ) {  // if there isn't one..
		$varname = '';
	}
	else {  // store the placeholder name
		$field[0] = $varname[1];
		$varname = trim( $varname[0] );
	}
	$fieldName = $field[0] = trim( $field[0] );

	$value = getVal($fieldName, $id);

	if ( $value == '' && isset($field[1]) ) {  // if we didn't find a value, use the default
		$value = getVal( trim($field[1]), $id );
	}
	if ( $value != '') {
		$p[ $varname ? $varname : $prefix . $field[0] ] = $value;  // key: user-defined nam OR prefix + field name
	}
}

/*
 * Code for deprecated property &fields.
 * Retained for backwards compatibility.
 */
if ($fields)  {	$resource = $modx->getObject('modResource', $id); }
$parents = array();
foreach ($fields as $field) {
	$field = explode('!!', $field);
	$fieldName = $field[0] = trim( $field[0] );
	$fieldPrefixes = explode('.', $fieldName);

	$doc = $resource;
	$value = NULL;

	if ($fieldPrefixes[0] === 'get')  { $value = $_GET[substr($fieldName, 4)];	}
	elseif ($fieldPrefixes[0] === 'post')  { $value = $_POST[substr($fieldName, 5)]; }
	else {
		for ($idx = 0; $fieldPrefixes[$idx] === 'parent' && $doc; ++$idx) {
			if (!isset($parents[$idx]))  { $parents[] = $modx->getObject('modResource', $doc->get('parent')); }
			$doc = $parents[$idx];
			$fieldName = substr($fieldName, 7);
		}
		if ($doc) {
			if ($fieldPrefixes[$idx] === 'tv')  { $value = $doc->getTVValue( substr($fieldName, 3) ); }
			else { $value = $doc->get($fieldName); }
		}
	}

	if ($value == '' && isset($field[1]))  { $value = trim( $field[1] ); }
	if ($value != '')  { $p[ $prefix . $field[0] ] = $value; }
}
/*
 * End legacy code
 */
foreach ($placeholders as $placeholder) { // add any user-defined placeholders
	$ph = explode('==', $placeholder);
	$p[ trim($ph[0]) ] = trim($ph[1]);
}

// Output our results
$modx->setPlaceholders($p);
return ($output ? implode($delimiter, $p) : '');