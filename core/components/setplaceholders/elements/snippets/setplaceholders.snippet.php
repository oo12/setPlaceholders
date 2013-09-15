<?php
/**
 * setPlaceholders
 * Copyright 2013 Jason Grant
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
 *
 * Documentation, examples, bug reports, etc.
 * https://github.com/oo12/setPlaceholders
 *
 * Variables
 * ---------
 * @var modX $modx
 *
 * Properties
 * ----------
 * @property integer id
 * @property string  ph
 * @property string  prefix
 * @property boolean output
 * @property string  delimiter
 * @property string  placeholders
 * @property string  sortby
 * @property string  sortdir
 * @property boolean processTVs
 *
 * See the default properties for a description of each.
 *
 * @package setPlaceholders
 **/

// check and initialize essential properties
$ph = empty($ph) ? array() : explode('||', $ph);
$placeholders = empty($placeholders) ? array() : explode('||', $placeholders);
$delimiter = isset($delimiter) ? $delimiter : ',';
$output = empty($output) ? FALSE : TRUE;

$p = array();  // placeholder storage
if ($ph) {
	$id = empty($id) ? $modx->resource->get('id') : (int) $id;
	$prefix = isset($prefix) ? $prefix : 'sph.';
	$processTVs = empty($processTVs) ? FALSE : TRUE;
	$sortby = empty($sortby) ? 'menuindex' : $sortby;
	$sortdir = empty($sortdir) ? 'ASC' : $sortdir;
	$staticCache = empty($staticCache) ? FALSE : TRUE;

	require_once MODX_CORE_PATH . 'components/setplaceholders/model/setplaceholders.class.php';
	static $sph_r_cache = array();  // cache for resource and TV objects
	$sph = new sph($modx, $sph_r_cache, $id, $sortby, $sortdir, $processTVs);

	foreach ($ph as $field) {
		$field = explode('!!', $field);  // separate out any default value
		$varname = explode('==', $field[0]);  // separate out any user-defined placeholder name
		if (isset($varname[1])) {  // if there is one, store the placeholder name
			$field[0] = $varname[1];
			$varname = trim($varname[0]);
		}
		else {
			$varname = $prefix . trim($field[0]);  // go with prefix + field name
		}
		foreach ($field as $f) {  // run through the field and all fallbacks till we get a non-empty one
			$value = $sph->getVal(trim($f));
			if (!empty($value) || $value === 0)  { break; }  // quit as soon as we get something
		}
		if (is_array($value)) {  // special processing for migx
			$varname .= '.';
			$migx_idx = 1;
			foreach ($value as $migx_row) {
				if (is_array($migx_row)) {
					$migx_notfirst = FALSE;
					foreach ($migx_row as $k=>$v) {  // set key:value pairs but ignore MIGX_id
						if ($migx_notfirst || $k !== 'MIGX_id') {
							$p[$varname . $k . $migx_idx] = $v;
						}
						$migx_notfirst = TRUE;
					}
					++$migx_idx;
				}
			}
			$p[$varname . 'total'] = $migx_idx - 1;  // set a placeholder with the total # of objects processed
		}
		else {  // set any not found items to '' so that placeholders will be fully parsed
			$p[$varname] = ($value === NULL) ? '' : $value;
		}
	}
	if (!$staticCache)  { $sph_r_cache = array(); }
}

foreach ($placeholders as $placeholder) { // add any user-defined placeholders
	$ph = explode('==', $placeholder);
	$p[ trim($ph[0]) ] = trim($ph[1]);
}

// Output our results
$modx->setPlaceholders($p);
return $output ? implode($delimiter, $p) : '';