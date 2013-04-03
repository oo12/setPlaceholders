<?php

/**
 * Class for setPlaceholders
 *
 * @package setplaceholders
 * @version 2.0.0-pl
 * @author Jason Grant <dadima@gmail.com>
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
		return $field[0] = substr($fieldName, 1);
	}

	$fieldPrefixes = explode('.', $fieldName);

/*  GET/POST variables  */
	if ( $fieldPrefixes[0] === 'get' ) {
		return $_GET[substr($fieldName, 4)];
	}
	if ( $fieldPrefixes[0] === 'post' ) {
		return $_POST[substr($fieldName, 5)];
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
	if ( strncmp('Uparent', $fieldPrefixes[$idx], 7) === 0 ) {
		$level = 2;
		$r_index = substr($fieldPrefixes[$idx], 7);
		if ( $r_index ) {  // ready any Uparent index
			$level = 1 + (int) $r_index;
			$fieldNameOffset += strlen($r_index);
		}
		$cacheKey = $id . 'p_';
		if ( !isset($sph_cache[$cacheKey]) ) {
			$sph_cache[$cacheKey] = $this->modx->getParentIds($id);
		}
		$tmp = count($sph_cache[$cacheKey]) - $level;
		if ($tmp < 0) {  // return NULL if we're out of bounds
			return;
		}
		$id = $sph_cache[$cacheKey][$tmp];
		$fieldNameOffset += 8;
		++$idx;
	}


/*  Parent selector  */
	if ( strncmp('parent', $fieldPrefixes[$idx], 6) === 0 ) {
		$level = 0;
		$r_index = substr($fieldPrefixes[$idx], 6);
		if ( $r_index ) {  // ready any parent index
			$level = (int) $r_index;
			$fieldNameOffset += strlen($r_index);
		}
		$cacheKey = $id . 'p_';
		if ( !isset($sph_cache[$cacheKey]) ) {
			$sph_cache[$cacheKey] = $this->modx->getParentIds($id);
		}
		if ( $level > count($sph_cache[$cacheKey]) ) {  // return NULL if out of bounds
			return;
		}
		$id = $sph_cache[$cacheKey][$level - 1];
		$fieldNameOffset += 7;
		++$idx;
	}


/*  Next / Prev sibling selectors, Index value  */
	$tmp = substr($fieldPrefixes[$idx], 0, 4);
	if ($tmp === 'prev' || $tmp === 'next' || $tmp === 'inde') {
		$cacheKey = $id . 'p_';
		if ( !isset($sph_cache[$cacheKey]) ) {  // first get the parent
			$sph_cache[$cacheKey] = $this->modx->getParentIds($id);
		}
		if (!$sph_cache[$cacheKey]) {
			return;
		}
		$tmp_id = $sph_cache[$cacheKey][0];
		$cacheKey = $tmp_id . $this->sortby . $this->sortdir[0];
		if ( !isset($sph_cache[$cacheKey]) )  {  // then its children
			$q = $this->modx->newQuery('modResource');
			$q->where(array('parent'=> $tmp_id, 'published' => 1, 'deleted' => 0));
			$q->select('modResource.id');
			$q->sortby($this->sortby, $this->sortdir);
			$q->prepare();
			$q->stmt->execute();
			$sph_cache[$cacheKey] = $q->stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		}
		$pos = array_search($id, $sph_cache[$cacheKey]);
		if ( $pos === FALSE ) {
			return;
		}
		if ( $tmp === 'inde')  {  // return id's position amongst its siblings
			return $pos + 1;
		}
		$r_index = substr($fieldPrefixes[$idx], 4);
		if ( $r_index ) {  // ready any next/prev offset
			$fieldNameOffset += strlen($r_index);
			$r_index = (int) $r_index;
		}
		else {
			$r_index = 1;
		}
		if ($tmp === 'prev') {
			$pos -= $r_index;
			if ($pos < 0) {  // if we've gone out of bounds, return nothing
				return;
			}
		}
		else {  // next
			$pos += $r_index;
			if ( $pos + 1 > count($sph_cache[$cacheKey]) ) {
				return;
			}
		}
		$id = $sph_cache[$cacheKey][$pos];
		$fieldNameOffset += 5;
		++$idx;
	}


/*  Child selector  */
	if ( strncmp('child', $fieldPrefixes[$idx], 5) === 0 ) {
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
				$child = rand(0, $cidsCount);
				++$fieldNameOffset;
			}
			else {  // get the specified child
				$child = -1 + (int) $r_index;
				if ($child > $cidsCount || -$child > $cidsCount + 2) {  // return if index is out of bounds
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


/*  Special processing for MIGX TVs (arrays of JSON objects)  */
	if (strncmp('migx', $fieldPrefixes[$idx], 4) === 0) {
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
			$migx = array_slice($migx, 0, (int) $migx_rows);
		}
		return $migx;
	}


	if ( $fieldPrefixes[$idx] === 'level' ) {  // return what level this is on
		$cacheKey = $id . 'p_';
		if ( !isset($sph_cache[$cacheKey]) ) {
			$sph_cache[$cacheKey] = $this->modx->getParentIds($id);
		}
		return count($sph_cache[$cacheKey]);
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