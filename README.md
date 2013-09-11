setPlaceholders 2.2
===============

A MODX Revolution snippet for getting fields and setting placeholders. Download from the MODX [Extras Repository](http://modx.com/extras/package/setplaceholders).

setPlaceholders combines the usefulness of placeholders—which are rather like local variables—with a fast and versatile engine for gathering up various data: fields and TVs from a resource or any of its ancestors, children or siblings, plus several other useful things.  It provides the functionality of getResourceField, UltimateParent, and getUrlParam—in addition to quite a bit more—all in one lightweight package.

New and changed in this version
-----------------------------

Please see the [changelog](https://github.com/oo12/setPlaceholders/blob/master/core/components/setplaceholders/docs/changelog.txt) for a summary.  Version 2.x has a few changes which affect backwards compatibility with 1.x, so please look through it if you're upgrading.

Examples
--------

* Showing the main functionality:<br>
<pre>
[[setPlaceholders?
&nbsp;	&ph=`parent.pagetitle ||
&nbsp; &nbsp; &nbsp; 13.introtext !! "No Summary Available" ||
&nbsp; &nbsp; &nbsp; section == Uparent.tv.section ||
&nbsp; &nbsp; &nbsp; parent.tv.columns !! parent2.tv.columns ||
&nbsp; &nbsp; &nbsp; color == "#a35c0a"`
]]
</pre>
Sets the placeholders:<br>
```[[+sph.parent.pagetitle]]``` the current resource's parent's pagetitle<br>
```[[+sph.13.introtext]]``` resource 13's introtext, or _No Summary Available_ if not found<br>
```[[+section]]``` the value of a TV named section for the current resource's ultimate parent<br>
```[[+sph.parent.tv.columns]]``` the value of the columns TV for the resource's parent, or if it's not found, for the same TV on the resource's grandparent<br>
```[[+color]]``` #a35c0a
<br>

* As a getResourceField replacement:<br>
<pre>[[setPlaceholders? &ph=`Uparent2.tv.someTV !! "No such TV"` &output=`1`]]</pre>
Returns the value of someTV for the second-highest parent of the current resource, or &ldquo;No such TV&rdquo; if that TV is empty or not found.  It also puts this value in ```[[+sph.Uparent2.tv.someTV]]```<br>
This is the equivalent of:<br>
<pre>[[getResourceField? &id=`[[UltimateParent? &topLevel=`3`]]`
&nbsp; &field=`someTV` &isTV=`1` &default=`No such TV`]]</pre>

* Simple next / previous, first / last navigation:
<pre>[[setPlaceholders?
&nbsp; &ph=`next == next.uri || prev == prev.uri || first == prevM.uri || last == nextM.uri`
]]<br>
&lt;a href="[[+first]]"&gt;First&lt;a&gt;
[[+prev:!empty=`&lt;a href="[[+prev]]"&gt;Previous&lt;a&gt;`]]
[[+next:!empty=`&lt;a href="[[+next]]"&gt;Next&lt;a&gt;`]]
&lt;a href="[[+last]]"&gt;Last&lt;a&gt;
</pre>

* Getting some URL parameters:<br>
<pre>[[!setPlaceholders? &ph=`get.type !! "1" || person == get.person`]]</pre>
Sets the placeholders:<br>
```[[+sph.get.type]]``` $\_GET\['type'\] (or _1_ if there's no type given)<br>
```[[+person]]``` $\_GET\['person'\]
<br>

* One more example, with some other options:<br>
<pre>[[setPlaceholders? &id=`13`
&nbsp; &ph=`parent.longtitle || parent2.longtitle !! "[[someSnippet]]"`
&nbsp; &prefix=`` &output=`1` &delimiter=` > `
]]</pre>
Returns: _Long Title #1 > Long Title #2_ (of course it'd be the actual longtitles)<br>
Sets the placeholders:<br>
```[[+parent.longtitle]]``` Resource 13's parent's longtitle<br>
```[[+parent2.longtitle]]``` Resource 13's grandparent's longtitle, or if it's empty, the output from someSnippet


Properties
----------

<table>
<tr><th>Property</th><th>Description</th><th>Default</th></tr>
<tr>
  <td>&amp;id</td>
  <td>The resource id to use. Can be overridden for individual items.</td>
  <td>current resource</td>
</tr><tr>
  <td>&amp;ph</td>
  <td>A list of placeholders to set, separated by ||<br>
  	This property offers a fairly rich syntax; see the <a href="#ph-property-syntax">special section</a> on it below for a complete explanation.
    </td>
  <td></td>
</tr><tr>
  <td>&amp;placeholders</td>
  <td>A simple list of user-defined placeholders to set. Unlike with the <em>&amp;ph</em> property, values aren't processed in any way and so don't need to be quoted. If you just need to set a few placeholders and don't need <em>&amp;ph</em>'s special getter abilities, this is the fastest way to do it.<br>Format: name1 == value 1 || name2 == value 2</td>
  <td></td>
</tr><tr>
  <td>&amp;prefix</td>
  <td>Prepended to any unspecified placeholder names to reduce the likelihood of placeholder name conflicts. Not added to any user-specified placeholder names. Can be set to an empty string to eliminate the prefix.</td>
  <td>sph.</td>
</tr><tr>
  <td>&amp;output</td><td>Output mode.<br><em>No</em> (0): only set placeholders<br><em>Yes</em> (1): also output the value of any placeholders. This allows the snippet to be used like getResourceField. Multiple values will be separated by <em>&amp;delimiter</em>.</td>
  <td>No (0)</td>
</tr><tr>
  <td>&amp;delimiter</td>
  <td>Separates values when results are returned as a string (i.e. &amp;output=`1`)</td>
  <td>,</td>
</tr><tr>
  <td>&amp;sortby</td>
  <td>Sort by criterion. Used when selecting child or sibling resources.</td>
  <td>menuindex</td>
</tr><tr>
  <td>&amp;sortdir</td>
  <td>Sort direction. Used when selecting child or sibling resources.</td>
  <td>ASC</td>
</tr><tr><td>&amp;processTVs</td>
  <td>Whether or not to process TV values. If the TV has special output options or needs a path from its media source, turn processing on.  Otherwise leave it off for faster TV performance.</td>
  <td>No (0)</td>
</tr><tr><td>&amp;staticCache</td>
  <td>Determines whether the resource/TV object cache is cleared on snippet exit or remains available for subsequent calls on the same page.<br><b>On</b> potentially uses more memory while MODX builds a page. Use it only if you call setPlaceholders several times on the same page and use the same resources/TVs in the different calls.</td>
  <td>Off (0)</td>
</tr>
</table>

<br>

&amp;ph Property Syntax
-----------------------

&amp;ph is where you specify all the placeholders you want set.  Separate multiple ones by ```||```.

A placeholder consists of 1&ndash;3 parts:

1. _placeholder_name ==_ (optional). If specified, this will be the placeholder name.  If left off, the placeholder name will be formed from a prefix (_&amp;prefix_) + the field name.<br><strong>Examples</strong>: ```pid == parent.id``` – parent.id will be stored in [[+pid]]<br> ```parent.id``` – parent.id will be stored in [[+sph.parent.id]] since no specific placeholder name was given and _sph._ is the default prefix.

2. A value or a field name.  Values are in quotes and are simply passed on without being evaluated further (though the quotes are trimmed off).  A value might be a bit of text or the output from another snippet.  Field names are parsed and evaluated, and represent some bit of data you'd like retrieved.  They can have multiple selector prefixes.<br><strong>Examples</strong>: _Values_ – ```"A text message"```, ```"[[someSnippet]]"``` (MODX will evaluate someSnippet; setPlaceholders won't do anything further to the result)<br>_Fieldnames_ – ```pagetitle```, ```13.Uparent2.tv.someTV```

3. _!! fallback_ (optional).  If the fieldname wasn't found or was empty, the fallback will be used.  Multiple fallbacks are allowed (i.e. field !! fallback1 !! fallback2) and may be fieldnames or values.

<strong>Fieldname selector prefixes</strong>

These are evaluated in the order listed.  Items ending with a ▣ return a value; those ending with a . require a further selector or a field name.  _[square brackets]_ indicates an optional parameter, _{curly braces}_ — a required one.  Prefixes may be chained where it makes sense, but—except for ```child```*—may not be repeated.  For instance: ```parent.pagetitle``` or ```42.parent.childR.tv.someTV``` (not that you'd want to do that :-)

* <strong>get.<em>{variable name}</em></strong> ▣ – a variable from $_GET (be sure to call setPlaceholders uncached if you're using either get, post or request)<br>_Example_: ```get.page``` – the value of $_GET['page']

* <strong>post.<em>{variable name}</em></strong> ▣ – a variable from $_POST

* <strong>request.<em>{variable name}</em></strong> ▣ – a variable from $_REQUEST (be sure to call setPlaceholders uncached if you're using either get or post)

* <strong>_resource_id_.</strong> – Selects a specific resource. Otherwise the value of _&amp;id_ (by default the current resource) is used.<br>_Example_: ```12.pagetitle``` – get the pagetitle of resource 12.

* <strong>Uparent<em>[level]</em>.</strong> – selects the resource's ultimate parent, that is, its top-level ancestor in the resource tree. (<strong>Uparent.</strong> and <strong>parent.</strong> are essentially mirror images of one another.) Use the optional level number to move further down the tree. <br>_Examples_: ```Uparent.id``` – the resource's ultimate parent's id<br>```Uparent2.id``` – the resource's 2nd top-most parent's id

* <strong>parent<em>[level]</em>.</strong> – selects the resource's parent. Use the optional level number to move further up the tree.<br>_Examples_: ```parent.id``` – the resource's parent's id<br>```parent2.id``` – the resource's grandparent's id

* <strong>next<em>[index]</em>.</strong> – selects the resource's next sibling. Use _&amp;sortby_ and _&amp;sortdir_ to control the sort order. Add a numeric index to jump ahead by that many. An index of <b>M</b> (max) selects the last sibling.<br>_Example_: ```next2.id``` – returns the id of the resource's sibling-after-next.

* <strong>prev<em>[index]</em>.</strong> – selects the resource's previous sibling. Use _&amp;sortby_ and _&amp;sortdir_ to control the sort order. Add a numeric index to jump back by that many. An index of <b>M</b> (max) selects the first sibling.

* <strong>index</strong> ▣ – Returns a resource's index within a list of its siblings. The first sibling will return 1, the second — 2, and so on.

* <strong>child<em>[child #]</em>.</strong> – selects one of the resource's children. Use _&amp;sortby_ and _&amp;sortdir_ to control the sort order and the optional child number to specify a particular child. Negative child numbers start with the last child and move towards the first. Unlike other selectors, ```child```* may be repeated multiple times to move further down the tree.<br>_Examples_: ```child.id``` – id of the resource's first child<br>```child3.id``` – id of the resource's third child<br>```child-1.id``` – id of the resource's last child<br>```child-2.id``` – id of the resource's second-to-last child<br>```child.child-1.id``` — id of the first child's last child

* <strong>childR.</strong> – selects a random child. This selected child is cached, so you may reuse the selector with the same parent multiple times within a setPlaceholders call to get different values from the same random child. (Setting <em>&amp;staticCache</em> will leave it available for the next setPlacholders call as well.)<br>_Example_: ```12.childR.id || 12.childR.pagetitle``` — returns the id number and pagetitle of the same randomly selected child of resource 12.

* <strong>childC</strong> ▣ – returns a count of the resource's immediate children.

* <strong>tv.<em>{TV name}</em></strong> ▣ – returns the value of the specified TV.  By default TV values are unprocessed.  Use _&amp;processTVs_ to change this.

* <strong>migx<em>[object limit]</em>.<em>{MIGX TV name}</em></strong> ▣ – special processing for MIGX TVs (or for other arrays of JSON objects).  If you use this selector, setPlaceholders will loop through the array and create placeholders for each key/value pair (it skips MIGX\_id), plus a total. The placeholder names are in the format _[main placeholder name].[key][item #]_. Adding an optional number after _migx_ limits the results to the first _N_ objects in the TV.<br>_Example_: The parent resource has a MIGX TV called imagestv with two fields: title and image.  The resource has three items stored in this tv. ```photos == parent.migx.imagestv``` will set 7 placeholders: ```[[+photos.title1]]``` ```[[+photos.image1]]``` ```[[+photos.title2]]``` ```[[+photos.image2]]``` ```[[+photos.title3]]``` ```[[+photos.image3]]``` and ```[[+photos.total]]``` (the number of items processed: 3)<br>```photos == parent.migx1.imagestv``` will set 3 placeholders: ```[[+photos.title1]]``` ```[[+photos.image1]]``` and ```[[+photos.total]]``` (1).

* <strong>migxC.<em>{MIGX TV name}</em></strong> ▣ – returns a count of the items in a MIGX TV.

* <strong>migxR.<em>{MIGX TV name}</em></strong> ▣ – returns a random item from a MIGX TV.  Using the MIGX TV from the example above, ```photos == parent.migxR.imagestv``` will set the placeholders ```[[+photos.title1]]``` ```[[+photos.image1]]``` (with values from a random row in the MIGX TV) and ```[[+photos.total]]``` will be 1.

* <strong>json<em>[object limit]</em>.<em>{JSON TV name}</em></strong> ▣ – an alias for <b>migx</b>. And <b>jsonC</b> and <b>jsonR</b> are aliases for <b>migxC</b> and <b>migxR</b>.

* <strong>level</strong> ▣ – returns the resource's level number in the resouce tree.  A top-level resource would return 1, its child — 2, etc.

* <strong><em>field name</em></strong> ▣ — return the value of the specified field for the selected resource. Basically anything you could get with the [[* ]] tag.

setPlaceholders caches the results of the MODX API calls it makes, so getting multiple fields from the same resource or from various parents or children of the same resource is quite efficient.

Notes
------

* If you're using setPlaceholders inside a chunk used multiple times on a page, like as a tpl for getResources, and are calling it uncached—which you shouldn't be in most cases—then you may run into an interesting aspect of evaluation order by the MODX parser.  If you've got uncached snippets storing values in cached placeholders you can get unexpected results from the placeholders. See this [issue](https://github.com/oo12/setPlaceholders/issues/3) for an in-depth discussion.

[![githalytics.com alpha](https://cruel-carlota.pagodabox.com/34a8457ccfdcdec3f456f0e0b2d45395 "githalytics.com")](http://githalytics.com/oo12/setPlaceholders)
