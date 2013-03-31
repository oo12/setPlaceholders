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
 * @version 1.3.0-beta
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
$sortby = $sortby ? $sortby : 'menuindex';
$sortdir = $sortdir ? $sortdir : 'ASC';

if ( !function_exists('sph_getVal') ) {
function sph_getVal($fieldName, $id, $sort_by, $sort_dir) {
	if ($fieldName[0] === '"') {  // check for a quoted value and skip parsing if found
		if ( substr($fieldName, -1) === '"' )  { return( substr($fieldName, 1, -1) ); }  // remove a trailing " if present
		return( $field[0] = substr($fieldName, 1) );
	}
	$fieldPrefixes = explode('.', $fieldName);
	if ( $fieldPrefixes[0] === 'get' )  { return( $_GET[substr($fieldName, 4)] ); }
	if ( $fieldPrefixes[0] === 'post' )  { return( $_POST[substr($fieldName, 5)] ); }
	global $modx;
	static $sph_cache = array(); // cache for resources and parent IDs
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
		if ( !isset($sph_cache[$cacheKey]) )  { $sph_cache[$cacheKey] = $modx->getParentIds($id); }
		$tmp = count($sph_cache[$cacheKey]) - $level;
		if ($tmp < 0)  { return; }
		$id = $sph_cache[$cacheKey][$tmp];
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
		if ( !isset($sph_cache[$cacheKey]) )  { $sph_cache[$cacheKey] = $modx->getParentIds($id); }
		$tmp = count($sph_cache[$cacheKey]) - 2;
		$id = $sph_cache[$cacheKey][ $level < $tmp ? $level : $tmp ];  // don't go past top-most parent
		$fieldNameOffset += 7;
		++$idx;
	}
	$tmp = substr($fieldPrefixes[$idx], 0, 4);
	if ($tmp === 'prev' || $tmp === 'next' || $tmp === 'inde') {  // siblings, or index
		$cacheKey = $id . 'p';
		if ( !isset($sph_cache[$cacheKey]) ) {  // first get the parent
			$sph_cache[$cacheKey] = $modx->getParentIds($id);
			if (!$sph_cache[$cacheKey])  { return; }
		}
		$tmp_id = $sph_cache[$cacheKey][0];
		$cacheKey = $tmp_id . $sort_by . $sort_dir[0];
		if ( !isset($sph_cache[$cacheKey]) )  {  // then its children
			$q = $modx->newQuery('modResource');
			$q->where(array('parent'=> $tmp_id, 'published' => 1, 'deleted' => 0));
			$q->select('modResource.id');
			$q->sortby($sort_by, $sort_dir);
			$q->prepare();
			$q->stmt->execute();
			$sph_cache[$cacheKey] = $q->stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		}
		$pos = array_search($id, $sph_cache[$cacheKey]);
		if ( $pos === FALSE )  { return; }
		if ( $tmp === 'inde')  { return $pos + 1; }  // return id's position amongst its siblings
		$r_index = substr($fieldPrefixes[$idx], 4);
		if ( $r_index ) {  // ready any next/prev offset
			$fieldNameOffset += strlen($r_index);
			$r_index = (int) $r_index;
		}
		else { $r_index = 1; }
		if ($tmp === 'prev') {
			$pos -= $r_index;
			if ($pos < 0)  { return; }  // if we've gone out of bounds, return nothing
		}
		else {
			$pos += $r_index;
			if ( $pos + 1 > count($sph_cache[$cacheKey]) )  { return; }
		}
		$id = $sph_cache[$cacheKey][$pos];
		$fieldNameOffset += 5;
		++$idx;
	}
	if ( substr($fieldPrefixes[$idx], 0, 5) === 'child' ) {  // child resource
		$cacheKey = $id . $sort_by . $sort_dir[0];
		if ( !isset($sph_cache[$cacheKey]) ) {
			$q = $modx->newQuery('modResource');
			$q->where(array('parent'=> $id, 'published' => 1, 'deleted' => 0));
			$q->select('modResource.id');
			$q->sortby($sort_by, $sort_dir);
			$q->prepare();
			$q->stmt->execute();
			$sph_cache[$cacheKey] = $q->stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		}
		if ( empty($sph_cache[$cacheKey]) )  { return; }
		$cidsCount = count($sph_cache[$cacheKey]) - 1;
		$child = 0;
		$r_index = substr($fieldPrefixes[$idx], 5);
		if (!empty($r_index)) {  // process any child index
			if ($r_index === 'C')  { return $cidsCount + 1; }  // return number of children
			if ($r_index === 'R')  {  // pick a random child
				$child = rand(0, $cidsCount);
				++$fieldNameOffset;
			}
			else {  // get the specified child
				$child = -1 + (int) $r_index;
				if ($child > $cidsCount || -$child > $cidsCount + 2)  { return; }  // return if index is out of bounds
				$fieldNameOffset += strlen($r_index);
			}
		}
		$id = $sph_cache[$cacheKey][$child];
		$fieldNameOffset += 6;
		++$idx;
	}
	if ( substr($fieldPrefixes[$idx], 0, 5) === 'level' ) {  // return what level this is on
		$cacheKey = $id . 'p';
		if ( !isset($sph_cache[$cacheKey]) )  { $sph_cache[$cacheKey] = $modx->getParentIds($id); }
		return count($sph_cache[$cacheKey]);
	}
	$cacheKey = $id . 'i';
	if ( !isset($sph_cache[$cacheKey]) )  { $sph_cache[$cacheKey] = $modx->getObject('modResource', $id); }
	if ( $doc = $sph_cache[$cacheKey] ) {  // if we've got a valid resource
		if ( $fieldPrefixes[$idx] === 'tv' ) {
			return $doc->getTVValue( substr($fieldName, $fieldNameOffset + 3) );
		}
		elseif ( substr($fieldPrefixes[$idx], 0, 4) === 'migx' ) {  // get a migx TV (array of JSON objects)
			$migx_rows = substr($fieldPrefixes[$idx], 4);  // check for an object limit
			$migx_rows &&   $fieldNameOffset += strlen($migx_rows);
			$migx = json_decode($doc->getTVValue( substr($fieldName, $fieldNameOffset + 5) ), TRUE);
			if ($migx_rows && $migx) {
				if ($migx_rows === 'C')  { return count($migx); }
				$migx = array_slice($migx, 0, (int) $migx_rows);
			}
			return $migx;
		}
		return $doc->get( substr($fieldName, $fieldNameOffset) );  // assume it's a field name
	}
}
}

$p = array();  // placeholder storage
foreach ($ph as $field) {
	$field = explode('!!', $field);  // separate out any default value
	$varname = explode('==', $field[0]);  // separate out any user-defined placeholder name
	if ( count($varname) === 1 )  { $varname = ''; }  // if there isn't one..
	else {  // store the placeholder name
		$field[0] = $varname[1];
		$varname = trim( $varname[0] );
	}
	$fieldName = $field[0] = trim( $field[0] );
	$value = sph_getVal($fieldName, $id, $sortby, $sortdir);
	if ( empty($value) && isset($field[1]) ) {  // if we didn't find a value, use the default
		$value = sph_getVal(trim($field[1]), $id, $sortby, $sortdir);
	}
	$varname = $varname ? $varname : $prefix . $field[0]; // key: user-defined name OR prefix + field name
	if (is_array($value)) {  // special processing for migx
		$varname .= '.';
		$migx_idx = 1;
		foreach ($value as $migx_row) {
			if (is_array($migx_row)) {
				$migx_notfirst = FALSE;
				foreach ($migx_row as $k=>$v) {  // set key:value pairs but ignore MIGX_id
					if ($migx_notfirst || $k !== 'MIGX_id')  { $p[$varname . $k . $migx_idx] = $v;}
					$migx_notfirst = TRUE;
				}
				++$migx_idx;
			}
		}
		$p[$varname . 'total'] = $migx_idx - 1;  // set a placeholder with the total # of objects processed
	}
	else { $p[$varname] = ($value === NULL) ? '' : $value; }  // set any not found items to '' so that placeholders will be fully parsed
}

foreach ($placeholders as $placeholder) { // add any user-defined placeholders
	$ph = explode('==', $placeholder);
	$p[ trim($ph[0]) ] = trim($ph[1]);
}

// Output our results
$modx->setPlaceholders($p);
return $output ? implode($delimiter, $p) : '';
