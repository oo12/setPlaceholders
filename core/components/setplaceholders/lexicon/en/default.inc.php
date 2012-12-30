<?php
/**
 * setPlaceholders
 *
 *
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
$_lang['prop_sph.fields_desc'] = 'A list of fields to retrieve, separated by ||<br><br>Field names may be prefixed by:<br><strong>tv.</strong> – get a TV<br><strong>parent.</strong> – get a field from the resource&rsquo;s parent. Can be used multiple times to get a parent&rsquo;s parent, etc. and can be a tv (ex: parent.parent.tv.someTV).<br><strong>get.</strong> – get a variable from $_GET<br><strong>post.</strong> – get a variable from $_POST<br><br>Specify a default value by appending !! value to the field name. Ex: longtitle !! Some Title<br><br>Placeholder names are a prefix (see &amp;prefix) plus the field name. Ex: &amp;fields=`tv.someTV || parent.id` will set the placeholders [[+sph.tv.someTV]] and [[+sph.parent.id]]';
$_lang['prop_sph.id_desc'] = 'The resource id to use<br><strong>Default:</strong> current resource';
$_lang['prop_sph.output_desc'] = '<strong>No</strong> [default]: only set placeholders<br><strong>Yes</strong>: also output the value of any placeholders. Multiple values will be separated by &amp;delimiter.';
$_lang['prop_sph.placeholders_desc'] = 'An optional list of user-defined placeholders to set<br>Format: name1==value1 || name2==value2';
$_lang['prop_sph.prefix_desc'] = 'Prepended to all placeholder names from &amp;fields to reduce the likelihood of placeholder name conflicts. Not added to any user-specified placeholders in &amp;placeholders. Can be set to an empty string to eliminate the prefix.<br><strong>Default:</strong> sph.';