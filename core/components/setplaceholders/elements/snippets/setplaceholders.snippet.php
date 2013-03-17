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
 * @package setPlaceholders
 * @author Jason Grant
 * @version 1.2.1-pl
 */

/**
 * Documentation, examples, bug reports, etc.
 * https://github.com/oo12/setPlaceholders
 *
 * Variables
 * ---------
 * @var modX $modx
 * @var input $input
 * @var options $options
 *
 * Properties
 * ----------
 * @property id - (integer)
 * @property ph - (string)
 * @property prefix - (string)
 * @property output - (boolean)
 * @property delimiter - (string)
 * @property placeholders - (string)
 * @property sortby - (string)
 * @property sortdir - (string)
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
global $sortby, $sortdir;
$sortby = $sortby ? $sortby : 'menuindex';
$sortdir = $sortdir ? $sortdir : 'ASC';
/* Deprecated as of v1.1 */
$fields = isset($fields) ? explode(',', $fields) : array();

if ( !function_exists('sph_getVal') ) {
function sph_getVal($fieldName, $id) {
	if ($fieldName[0] === '"') {  // check for a quoted value and skip parsing if found
		if ( substr($fieldName, -1) === '"' ) { return( substr($fieldName, 1, -1) ); }  // remove a trailing " if present
		else { return( $field[0] = substr($fieldName, 1) ); }
	}
	$fieldPrefixes = explode('.', $fieldName);
	if ( $fieldPrefixes[0] === 'get' ) { return( $_GET[substr($fieldName, 4)] ); }
	elseif ( $fieldPrefixes[0] === 'post' ) { return( $_POST[substr($fieldName, 5)] ); }
	else {
		global $modx;
		static $sph_cache = array(), $sph_resource_cache = array();  // set up caches for parent/child ids and the resources themselves
		$idx = 0;
		$fieldNameOffset = 0;
		if ( is_numeric($fieldPrefixes[0]) ) {  // check for a resource ID
			$id = (int) $fieldPrefixes[0];
			$fieldNameOffset += strlen($fieldPrefixes[0]) + 1;
			$idx = 1;
		}
		if ( substr($fieldPrefixes[$idx], 0, 7) === 'Uparent' ) {  // Ultimate parent
			$level = 2;
			$r_index = substr($fieldPrefixes[$idx], 7);
			if ( $r_index ) {  // read any Uparent index
				$level = 1 + (int) $r_index;
				$fieldNameOffset += strlen($r_index);
			}
			$cacheKey = $id . 'p';
			if ( !isset($sph_cache[$cacheKey]) ) {
				$sph_cache[$cacheKey] = $modx->getParentIds($id);
				$sph_cache[$cacheKey . 'c'] = count($sph_cache[$cacheKey]);
			}
			$tmp = $sph_cache[$cacheKey . 'c'] - $level;
			$id = $sph_cache[$cacheKey][ $tmp > 0 ? $tmp : 0 ];  // don't go past the immediate parent
			$fieldNameOffset += 8;
			++$idx;
		}
		if ( substr($fieldPrefixes[$idx], 0, 6) === 'parent' ) {  // regular parent
			$level = 0;
			$r_index = substr($fieldPrefixes[$idx], 6);
			if ( $r_index ) {  // ready any parent index
				$level = -1 + (int) $r_index;
				$fieldNameOffset += strlen($r_index);
			}
			$cacheKey = $id . 'p';
			if ( !isset($sph_cache[$cacheKey]) ) {
				$sph_cache[$cacheKey] = $modx->getParentIds($id);
				$sph_cache[$cacheKey . 'c'] = count($sph_cache[$cacheKey]);
			}
			$tmp = $sph_cache[$cacheKey . 'c'] - 2;
			$id = $sph_cache[$cacheKey][ $level < $tmp ? $level : $tmp ];  // don't go past top-most parent
			$fieldNameOffset += 7;
			++$idx;
		}
		if ( substr($fieldPrefixes[$idx], 0, 5) === 'child' ) {  // child resource
			$level = 0;
			$r_index = substr($fieldPrefixes[$idx], 5);
			if ( $r_index != 0 ) {  // ready any child index
				$level = -1 + (int) $r_index;
				$fieldNameOffset += strlen($r_index);
			}
			$cacheKey = $id . $GLOBALS['sortby'] . $GLOBALS['sortdir'][0];
			if ( !isset($sph_cache[$cacheKey]) ) {
				$q = $modx->newQuery('modResource');
				$q->where(array('parent'=> $id, 'published' => 1, 'deleted' => 0));
				$q->select('modResource.id');
				$q->sortby($GLOBALS['sortby'], $GLOBALS['sortdir']);
				$q->prepare();
				$q->stmt->execute();
				$cids = $q->stmt->fetchAll(PDO::FETCH_COLUMN, 0);
				if (empty($cids)) { return 0; }
				$sph_cache[$cacheKey] = $cids;  // cache the array
				$sph_cache[$cacheKey . 'c'] = count($cids) - 1;  // and its count
			}
			$cidsCount = $sph_cache[$cacheKey . 'c'];
			if ($level > $cidsCount) { $level = $cidsCount; }  // don't go past the last child
			elseif ($level < 0) {  // or the first
				$cidsCount += 2;
				$level = (-$level > $cidsCount) ? 0 : $level + $cidsCount;
			}
			$id = $sph_cache[$cacheKey][ $level ];
			$fieldNameOffset += 6;
			++$idx;
		}
		$cacheKey = $id . 'id';
		if ( !isset($sph_resource_cache[$cacheKey]) ) { $sph_resource_cache[$cacheKey] = $modx->getObject('modResource', $id); }
		if ( $doc = $sph_resource_cache[$cacheKey] ) {  // if we've got a valid resource
			if ( $fieldPrefixes[$idx] === 'tv' ) { return $doc->getTVValue( substr($fieldName, $fieldNameOffset + 3) ); }
			elseif ( substr($fieldPrefixes[$idx], 0, 4) === 'migx' ) {  // get a migx TV (array of JSON objects)
				$migx_rows = substr($fieldPrefixes[$idx], 4);  // check for an object limit
				$migx_rows &&   $fieldNameOffset += strlen($migx_rows);
				$migx = json_decode($doc->getTVValue( substr($fieldName, $fieldNameOffset + 5) ), TRUE);
				$migx_rows &&   $migx = array_slice($migx, 0, (int) $migx_rows);
				return $migx;
			}
			else { return $doc->get( substr($fieldName, $fieldNameOffset) ); }  // assume it's a field name
		}
	}
}
}

$p = array();  // placeholder storage
foreach ($ph as $field) {
	$field = explode('!!', $field);  // separate out any default value
	$varname = explode('==', $field[0]);  // separate out any user-defined placeholder name
	if ( count($varname) === 1 ) { $varname = ''; }  // if there isn't one..
	else {  // store the placeholder name
		$field[0] = $varname[1];
		$varname = trim( $varname[0] );
	}
	$fieldName = $field[0] = trim( $field[0] );
	$value = sph_getVal($fieldName, $id);
	if ( $value == '' && isset($field[1]) ) { $value = sph_getVal( trim($field[1]), $id ); }  // if we didn't find a value, use the default
	if ($value != '') {
		$varname = $varname ? $varname : $prefix . $field[0];
		if (is_array($value)) {  // special processing for migx
			$varname .= '.';
			$migx_idx = 1;
			foreach ($value as $migx_row) {
				if (is_array($migx_row)) {
					$migx_notfirst = FALSE;
					foreach ($migx_row as $k=>$v) {  // set key:value pairs but ignore MIGX_id
						if ($migx_notfirst || $k !== 'MIGX_id') { $p[$varname . $k . $migx_idx] = $v;}
						$migx_notfirst = TRUE;
					}
					++$migx_idx;
				}
			}
			$p[$varname . 'total'] = $migx_idx - 1;  // set a placeholder with the total # of objects processed
		}
		else { $p[$varname] = $value; }  // key: user-defined nam OR prefix + field name
	}
}
// try killing the resource cache now to prevent "Argument 1 passed to xPDOObject::load() must be an instance of xPDO..." warning?
unset($sph_resource_cache);

/* Code for deprecated property &fields. Retained for backwards compatibility. */
if ($fields)  {
	$resource = $modx->getObject('modResource', $id);
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
}
/* End legacy code */

foreach ($placeholders as $placeholder) { // add any user-defined placeholders
	$ph = explode('==', $placeholder);
	$p[ trim($ph[0]) ] = trim($ph[1]);
}

// Output our results
$modx->setPlaceholders($p);

return $output ? implode($delimiter, $p) : '';
