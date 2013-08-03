<?php
/**
 * setPlaceholders
 * Copyright 2013 Jason Grant
 *
 * setPlaceholders is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * setPlaceholders is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * setPlaceholders; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package setplaceholders
 */
/**
 * Default Lexicon Topic
 *
 * @package setplaceholders
 * @subpackage lexicon
 */

//properties
$_lang['prop_sph.delimiter_desc'] = 'Separates values when results are returned as a string (i.e. &amp;output=`1`)<br><strong>Default: ,</strong>';
$_lang['prop_sph.id_desc'] = 'The resource id to use<br><strong>Default:</strong> current resource';
$_lang['prop_sph.output_desc'] = '<strong>No</strong> [default]: only set placeholders<br><strong>Yes</strong>: also output the value of all placeholders. Multiple values will be separated by &amp;delimiter.';
$_lang['prop_sph.placeholders_desc'] = 'A simple list of placeholder key/value pairs to set. Unlike with &amp;ph, placeholder values will not be evaluated.<br>Ex: ph1 == value 1 || ph2 == value 2';
$_lang['prop_sph.prefix_desc'] = 'Prepended to any auto-generated placeholder names from &amp;ph to reduce the likelihood of name conflicts. Not added to any user-specified placeholder names. Can be set to an empty string to eliminate the prefix.<br><strong>Default:</strong> sph.';
$_lang['prop_sph.ph_desc'] = 'A list of fields to retrieve, separated by ||<br>Format: placeholder_name == fieldname or "value" !! default (fieldname or value)<br>For the full list of fieldname selectors see <a href="https://github.com/oo12/setPlaceholders#ph-property-syntax" target="_blank">the documentation</a>.<br>Ex: color == parent.tv.color !! Uparent.tv.color<br><br>If not specified, the placeholder name is &amp;prefix + the fieldname.';
$_lang['prop_sph.sortby_desc'] = 'Sort by criterion. Used with child and sibling selectors.<br><strong>Default:</strong> menuindex';
$_lang['prop_sph.sortdir_desc'] = 'Sort direction. Used with child and sibling selectors.<br><strong>Default:</strong> ASC';
$_lang['prop_sph.processTVs_desc'] = '<strong>Default:</strong> No';
$_lang['prop_sph.staticCache_desc'] = 'Determines whether the resource/TV object cache is cleared on snippet exit or remains available for subsequent calls on the same page.<br><b>On</b> potentially uses more memory while MODX builds a page. Use it only if you call setPlaceholders several times on the same page and use the same resources/TVs in the different calls.<br><strong>Default:</strong> Off';