<?php
/**
 * setPlaceholders
 * Copyright 2013-2014 Jason Grant
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
 */

class sph {

private $modx;
private $r_cache;
private $id;
private $sortby;
private $sortdir;
private $processtvs;

function __construct(modX &$modx, &$cache, $id, $sb, $sd, $pt) {
	$this->modx =& $modx;
	$this->r_cache =& $cache;  // reference to resource/TV object cache
	$this->id = $id;  // the default resource ID
	$this->sortby = $sb;
	$this->sortdir = $sd;
	$this->processtvs = $pt;
}

/**
 * Parses a field name specifier, looks up and returns the indicated value
 *
 * @param string $fieldName
 * @return string
 */
function getVal($fieldName) {
	if ($fieldName[0] === '"') {  // check for a quoted value and skip parsing if found
		if ( substr($fieldName, -1) === '"' ) {  // remove a trailing " if present
			return substr($fieldName, 1, -1);
		}
		return substr($fieldName, 1);
	}

	$fieldPrefixes = explode('.', $fieldName);


/*  GET/POST/REQUEST variables  */
	if ($fieldPrefixes[0] === 'get') {
		return $_GET[substr($fieldName, 4)];
	}
	if ($fieldPrefixes[0] === 'post') {
		return $_POST[substr($fieldName, 5)];
	}
	if ($fieldPrefixes[0] === 'request') {
		return $_REQUEST[substr($fieldName, 8)];
	}


	static $sph_cache = array(); // cache for parent/child IDs
	$id = $this->id;
	$idx = 0;
	$fieldNameOffset = 0;


/*  Resource ID selector  */
	if ( is_numeric($fieldPrefixes[0]) ) {
		$id = (int) $fieldPrefixes[0];
		$fieldNameOffset += strlen($fieldPrefixes[0]) + 1;
		$idx = 1;
	}


/*  Ultimate parent selector  */
	if (strncmp('Uparent', $fieldPrefixes[$idx], 7) === 0) {
		$cacheKey = $id . 'p_';
		if (!isset($sph_cache[$cacheKey])) {
			$sph_cache[$cacheKey] = $this->modx->getParentIds($id, 50);
			$sph_cache[$cacheKey . 'c'] = count($sph_cache[$cacheKey]);
		}
		if (empty($sph_cache[$cacheKey])) {
			return;  // no parents at all?  Give up!
		}
		$level = 2;
		$prefixOffset = 7;
		if (isset($fieldPrefixes[$idx][7]) && $fieldPrefixes[$idx][7] === 'B') {  // bounded mode
			++$prefixOffset;
		}
		$r_index = substr($fieldPrefixes[$idx], $prefixOffset);
		if ($r_index) {  // ready any Uparent index
			$level = 1 + $r_index;
			$fieldNameOffset += strlen($r_index);
		}
		$tmp = $sph_cache[$cacheKey . 'c'] - $level;
		if ($tmp > -1) {  // if we're not out of bounds, update $id
			$id = $sph_cache[$cacheKey][$tmp];
		}
		elseif ($prefixOffset === 7) {
			return;  // return NULL if not in bounded mode or if there are no parents at all
		}

		$fieldNameOffset += $prefixOffset + 1;
		++$idx;
	}


/*  Parent/Parents selector  */
	if (strncmp('parent', $fieldPrefixes[$idx], 6) === 0) {
		$cacheKey = $id . 'p_';
		if (!isset($sph_cache[$cacheKey])) {
			$sph_cache[$cacheKey] = $this->modx->getParentIds($id, 50);
			$sph_cache[$cacheKey . 'c'] = count($sph_cache[$cacheKey]);
		}
		if (empty($sph_cache[$cacheKey])) {
			return;  // no parents at all?  Give up!
		}
		$parentsCount = $sph_cache[$cacheKey . 'c'];
		if (isset($fieldPrefixes[$idx][6]) && $fieldPrefixes[$idx][6] === 's') {  // parents
			$parentsList = $sph_cache[$cacheKey];
			array_pop($parentsList);  // the top-level parent is always 0, which we don't need
			$parentsList = array_reverse($parentsList);  // reorder from resource upwards
			if (isset($fieldPrefixes[$idx][7]) && $fieldPrefixes[$idx][7] === 'I') {
				$parentsList[] = $id;  // include the resource's id too
			}
			return implode(',', $parentsList);
		}
		$level = 0;
		$prefixOffset = 6;
		if (isset($fieldPrefixes[$idx][6]) && $fieldPrefixes[$idx][6] === 'B') {  // bounded mode
			++$prefixOffset;
		}
		$r_index = substr($fieldPrefixes[$idx], $prefixOffset);
		if ($r_index) {  // ready any parent index
			$level = $r_index - 1;
			$fieldNameOffset += strlen($r_index);
		}
		if ($level < $parentsCount && $parentsCount > 1) {  // if we're not out of bounds, update $id
			$id = $sph_cache[$cacheKey][$level];
		}
		elseif ($prefixOffset === 6) {
			return;  // return NULL if not in bounded mode
		}
		elseif ($parentsCount > 1) {  // bounded mode
			$id = $sph_cache[$cacheKey][ $parentsCount - 2 ];  // set id to ultimate parent
		}
		$fieldNameOffset += $prefixOffset + 1;
		++$idx;
	}


/*  Next / Prev sibling selectors, Index value  */
	$tmp = substr($fieldPrefixes[$idx], 0, 4);
	if ($tmp === 'prev' || $tmp === 'next' || $tmp === 'inde') {
		$cacheKey = $id . 'p_';
		if (!isset($sph_cache[$cacheKey])) {  // first get the parent...
			$sph_cache[$cacheKey] = $this->modx->getParentIds($id, 50);
			$sph_cache[$cacheKey . 'c'] = count($sph_cache[$cacheKey]);
		}
		if (empty($sph_cache[$cacheKey])) {  // return if no parents
			return;
		}
		$tmp_id = $sph_cache[$cacheKey][0];
		$cacheKey = $tmp_id . $this->sortby . $this->sortdir[0];
		if (!isset($sph_cache[$cacheKey]))  {  // ...then its children
			$q = $this->modx->newQuery('modResource');
			$q->where(array('parent'=> $tmp_id, 'published' => 1, 'deleted' => 0));
			$q->select('modResource.id');
			$q->sortby($this->sortby, $this->sortdir);
			$q->prepare();
			$q->stmt->execute();
			$sph_cache[$cacheKey] = $q->stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		}
		$pos = array_search($id, $sph_cache[$cacheKey]);
		if ($pos === FALSE) {
			return;
		}
		if ($tmp === 'inde')  {  // return id's position amongst its siblings
			return $pos + 1;
		}
		$r_index = substr($fieldPrefixes[$idx], 4);
		if ($r_index) {  // ready any next/prev offset
			$fieldNameOffset += strlen($r_index);
			if ($r_index !== 'M') {  // if it's not a "max" sibling
				$r_index = (int) $r_index;
			}
		}
		else {
			$r_index = 1;
		}
		if ($tmp === 'prev') {
			if ($r_index === 'M') {  // first sibling
				$pos = 0;
			}
			else {
				$pos -= $r_index;
				if ($pos < 0) {  // if we've gone out of bounds, return nothing
					return;
				}
			}
		}
		else {  // next
			$tmp = count($sph_cache[$cacheKey]) - 1;
			if ($r_index === 'M') {  // last sibling
				$pos = $tmp;
			}
			else {
				$pos += $r_index;
				if ( $pos > $tmp ) {  // if we've gone out of bounds, return nothing
					return;
				}
			}
		}
		$id = $sph_cache[$cacheKey][$pos];
		$fieldNameOffset += 5;
		++$idx;
	}


/*  Child selector  */
	while ( strncmp('child', $fieldPrefixes[$idx], 5) === 0 ) {
		$cacheKey = $id . $this->sortby . $this->sortdir[0];
		if ( !isset($sph_cache[$cacheKey]) ) {
			$q = $this->modx->newQuery('modResource');
			$q->where(array('parent'=> $id, 'published' => 1, 'deleted' => 0));
			$q->select('modResource.id');
			$q->sortby($this->sortby, $this->sortdir);
			$q->prepare();
			$q->stmt->execute();
			$sph_cache[$cacheKey] = $q->stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		}
		if ( empty($sph_cache[$cacheKey]) ) {
			return;
		}
		$cidsCount = count($sph_cache[$cacheKey]) - 1;
		$child = 0;
		$r_index = substr($fieldPrefixes[$idx], 5);
		if (!empty($r_index)) {  // ready any child index
			if ($r_index === 'C') {  // return number of children
				return $cidsCount + 1;
			}
			if ($r_index === 'R') {  // pick a random child
				if (!isset($this->r_cache["R$cacheKey"])) {
					$this->r_cache["R$cacheKey"] = rand(0, $cidsCount);
				}
				$child = $this->r_cache["R$cacheKey"];
				++$fieldNameOffset;
			}
			else {  // get the specified child
				$child = $r_index - 1;
				if ($child < 0) {
					$child += $cidsCount + 2;
					if ($child < 0) {  // return if index is out of bounds
						return;
					}
				}
				if ($child > $cidsCount) {  // return if index is out of bounds
					return;
				}
				$fieldNameOffset += strlen($r_index);
			}
		}
		$id = $sph_cache[$cacheKey][$child];
		$fieldNameOffset += 6;
		++$idx;
	}


/*  TV  */
	if ($fieldPrefixes[$idx] === 'tv') {
		$cacheKey = substr($fieldName, $fieldNameOffset + 3);
		if ( !isset($this->r_cache[$cacheKey]) ) {
			$this->r_cache[$cacheKey] = $this->modx->getObject('modTemplateVar', array( 'name' => $cacheKey ));
		}
		if (!$this->r_cache[$cacheKey]) {
			return;
		}
		return $this->processtvs ? $this->r_cache[$cacheKey]->renderOutput($id) : $this->r_cache[$cacheKey]->getValue($id);
	}


/*  Special processing for MIGX/JSON TVs (arrays of JSON objects)  */
	$tmp = substr($fieldPrefixes[$idx], 0, 4);
	if ($tmp === 'migx' || $tmp === 'json') {
		$migx_rows = substr($fieldPrefixes[$idx], 4);  // check for an object limit
		if ($migx_rows)  {
			$fieldNameOffset += strlen($migx_rows);
		}
		$cacheKey = substr($fieldName, $fieldNameOffset + 5);
		if ( !isset($this->r_cache[$cacheKey]) ) {
			$this->r_cache[$cacheKey] = $this->modx->getObject('modTemplateVar', array( 'name' => $cacheKey ));
		}
		if (!$this->r_cache[$cacheKey]) {
			return;
		}
		$migx = json_decode($this->r_cache[$cacheKey]->getValue($id), TRUE);
		if ($migx_rows && $migx) {
			if ($migx_rows === 'C') {
				return count($migx);
			}
			elseif ($migx_rows === 'R') {
				return array($migx[ rand(0, count($migx)-1) ]);
			}
			$migx = array_slice($migx, 0, (int) $migx_rows);
		}
		return $migx;
	}


	if ( $fieldPrefixes[$idx] === 'level' ) {  // return what level this is on
		$cacheKey = $id . 'p_';
		if (!isset($sph_cache[$cacheKey])) {
			$sph_cache[$cacheKey] = $this->modx->getParentIds($id, 50);
			$sph_cache[$cacheKey . 'c'] = count($sph_cache[$cacheKey]);
		}
		return $sph_cache[$cacheKey . 'c'];
	}


	$cacheKey = $id . 'i_';
	if ( !isset($this->r_cache[$cacheKey]) ) {
		$this->r_cache[$cacheKey] = $this->modx->getObject('modResource', $id);
	}
	if (!$this->r_cache[$cacheKey]) {
		return;
	}
	return $this->r_cache[$cacheKey]->get( substr($fieldName, $fieldNameOffset) );  // assume it's a field name
}


}