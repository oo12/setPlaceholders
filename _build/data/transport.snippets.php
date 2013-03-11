<?php
/**
 * setPlaceholders transport snippets
 * Copyright 2012-2013 Jason Grant
 * @author Jason Grant
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
 * setPlaceholders; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package setplaceholders
 */
/**
 * Description:  Array of snippet objects for setPlaceholders package
 * @package setplaceholders
 * @subpackage build
 */

if (! function_exists('getSnippetContent')) {
    function getSnippetContent($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<?php','',$o);
        $o = str_replace('?>','',$o);
        $o = trim($o);
        return $o;
    }
}
$snippets = array();

$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 1,
    'name' => 'setPlaceholders',
    'description' => 'A snippet for getting fields and setting placeholders. Also works as an output filter. Documentation: https://github.com/oo12/setPlaceholders',
    'snippet' => getSnippetContent($sources['source_core'].'/elements/snippets/setplaceholders.snippet.php'),
),'',true,true);
$properties = include $sources['data'].'/properties/properties.setplaceholders.php';
$snippets[1]->setProperties($properties);
unset($properties);

return $snippets;
