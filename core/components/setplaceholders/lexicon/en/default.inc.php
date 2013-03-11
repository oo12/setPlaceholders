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
$_lang['prop_sph.fields_desc'] = 'Deprecated.<br>A comma-separated list of fields to retrieve. Use the &amp;ph property instead.';
$_lang['prop_sph.id_desc'] = 'The resource id to use<br><strong>Default:</strong> current resource';
$_lang['prop_sph.output_desc'] = '<strong>No</strong> [default]: only set placeholders<br><strong>Yes</strong>: also output the value of any placeholders. Multiple values will be separated by &amp;delimiter.';
$_lang['prop_sph.placeholders_desc'] = 'Prepended to any unspecified placeholder names, to reduce the likelihood of placeholder name conflicts. Not added to any user-specified placeholder names. Can be set to an empty string to eliminate the prefix.';
$_lang['prop_sph.prefix_desc'] = 'Prepended to all placeholder names from &amp;fields to reduce the likelihood of placeholder name conflicts. Not added to any user-specified placeholders in &amp;placeholders. Can be set to an empty string to eliminate the prefix.<br><strong>Default:</strong> sph.';
$_lang['prop_sph.ph_desc'] = 'A list of fields to retrieve, separated by ||<br>Format: placeholder_name == fieldname or "value" !! default (fielname or value)<br>Ex: color == parent.tv.color !! Uparent.tv.color<br><br>Available prefixes:<br><strong>ID #.</strong> – get something from a particular resource (overrides &amp;id). Ex: 13.pagetitle to get resource 13&rsquo;s pagetitle<br><strong>tv.</strong> – get a TV<br><strong>parent.</strong> – get a field from the resource&rsquo;s parent. Add a number to specify a parent higher up in the tree. Ex. parent2 is the parent&rsquo;s parent<br><strong>Uparent.</strong> – the resource&rsquo;s ultimate parent (highest in the tree). Add a number to work down towards the resource. Ex. Uparent2 is the resources next-highest parent.<br><strong>get.</strong> – get a variable from $_GET<br><strong>post.</strong> – get a variable from $_POST<br><br>If not specified, the placeholder name is the &amp;prefix + the fieldname.';
$_lang['prop_sph.ph_sortby'] = 'Sort by criterion. Used only when requesting child resources.<br><strong>Default:</strong> menuindex';
$_lang['prop_sph.ph_sortdir'] = 'Sort direction. Used only when requesting child resources.<br><strong>Default:</strong> ASC';
