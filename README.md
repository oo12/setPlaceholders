setPlaceholders 1.2
===============

A MODX Revolution snippet for getting fields and setting placeholders.  Download from the MODX [Extras Repository](http://modx.com/extras/package/setplaceholders).

Placeholders have a number of useful applications.  Essentially they can be used as local variables in a template or chunk.  They've always been simple to set from within a snippet; setPlaceholders makes it easy to set them without any PHP coding.

Additionally setPlaceholders provides a fast and versatile engine for gathering up various data: fields and TVs from a resource or any of its ancestors, as well as $\_GET and $\_POST variables.  It provides most of the functionality of getResourceField, UltimateParent, and getUrlParam all in one lightweight package.

setPlaceholders may also be used as an output filter to store the result of an expression or snippet call for further use elsewhere in the template.

Let's start with some examples.

Examples
--------


* Showing the main functionality:<br>
<pre>
[[setPlaceholders?
&nbsp;	&ph=\`parent.pagetitle ||
&nbsp; &nbsp; &nbsp; 13.introtext !! "No Summary Available" ||
&nbsp; &nbsp; &nbsp; section == Uparent.tv.section ||
&nbsp; &nbsp; &nbsp; parent.tv.columns !! parent2.tv.columns ||
&nbsp; &nbsp; &nbsp; color == "#a35c0a"\`
]]
</pre><br>
Sets the placeholders:<br>
```[[+sph.parent.pagetitle]]``` the current resource's parent's pagetitle<br>
```[[+sph.13.introtext]]``` resource 13's introtext, or _No Summary Available_ if not found<br>
```[[+section]]``` the value of a TV named section for the current resource's ultimate parent<br>
```[[+sph.parent.tv.columns]]``` the value of the columns TV for the resource's parent, or if it's not found, for the same TV on the resource's grandparent<br>
```[[+color]]``` #a35c0a

* As an output filter:<br>
<pre>[[*someTV:eq=`foo`:then=`bar`:else=`nobar`:setPlaceholders=`fb`]] </pre>
Returns the value of the expression and also stores it in [[+fb]] for later use on the page.

* As a getResourceField replacement:<br>
<pre>[[setPlaceholders? &ph=`Uparent2.tv.someTV !! "No such TV"` &output=`1`]]</pre><br>
Returns the value of someTV for the second-highest parent of the current resource, or &ldquo;No such TV&rdquo; if that TV is empty or not found.  It also puts this value in ```[[+sph.Uparent2.tv.someTV]]```<br>
This is the equivalent of:<br>
<pre>[[getResourceField? &id=`[[UltimateParent? &topLevel=`2`]]`
&nbsp;	&field=`someTV` &isTV=`1` &processTV=`1` &default=`No such TV`]]</pre>

* Getting some URL parameters:<br>
<pre>[[!setPlaceholders? &ph=`get.type !! "1" || person == get.person`]]</pre>
Sets the placeholders:<br>
```[[+sph.get.type]]``` $\_GET\['type'\] (or _1_ if there's no type given)<br>
```[[+person]]``` $\_GET\['person'\]

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

*When used as an output filter:*

* setPlaceholders takes one argument: the name of the placeholder to set.

*When called as a snippet:*

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
  <td>&amp;prefix</td>
  <td>Prepended to any unspecified placeholder names, to reduce the likelihood of placeholder name conflicts. Not added to any user-specified placeholder names. Can be set to an empty string to eliminate the prefix.</td>
  <td>sph.</td>
</tr><tr>
  <td>&amp;output</td><td>Output mode.<br><em>No</em> (0): only set placeholders<br><em>Yes</em> (1): also output the value of any placeholders. This allows the snippet to be used like getResourceField. Multiple values will be separated by <em>&amp;delimiter</em>.</td>
  <td>No</td>
</tr><tr>
  <td>&amp;delimiter</td>
  <td>Separates values when results are returned as a string (i.e. &amp;output=`1`)</td>
  <td>,</td>
</tr><tr>
  <td>&amp;placeholders</td>
  <td>A list of user-defined placeholders to set<br>Format: name1 == value 1 || name2 == value 2<br>This property is a leftover from setPlaceholders 1.0.  Unlike in the <em>&amp;ph</em> property, values don't need to be quoted.</td>
  <td></td>
</tr><tr>
  <td>&amp;sortby</td>
  <td>Sort by criterion. Used only when requesting child resources.</td>
  <td>menuindex</td>
</tr><tr>
  <td>&amp;sortdir</td>
  <td>Sort direction. Used only when requesting child resources.</td>
  <td>ASC</td>
</tr><tr><td>&amp;fields</td>
  <td>[deprecated] &nbsp; A list of fields to retrieve.<br>
  	Replaced by the <em>&amp;ph</em> property in v1.1 but remains for backwards compatibility</td>
  <td></td>
</tr>
</table>

<br>

&amp;ph Property Syntax
-----------------------

&amp;ph is where you specify all the placeholders you want set.  Separate multiple ones by ```||```.

A placeholder consists of 1&ndash;3 parts:

1. _placeholder_name ==_ (optional). If specified, this will be the placeholder name.  If left off, the placeholder name will be formed from a prefix (_&amp;prefix_) + the field name.<br><strong>Examples</strong>: ```pid == parent.id``` – parent.id will be stored in [[+pid]]<br> ```parent.id``` – parent.id will be stored in [[+sph.parent.id]] since no specific placeholder name was given and _sph._ is the default prefix.

2. A value or a fieldname.  Values are in quotes and are simply passed on without being evaluated further (though the quotes are trimmed off).  A value might be a bit of text or the output from another snippet.  Fieldnames are parsed and evaluated, and represent some bit of data you'd like retrieved.  They can have multiple selector prefixes.<br><strong>Examples</strong>: _Values_ – ```"A text message"```, ```"[[someSnippet]]"``` (MODX will evaluate someSnippet; setPlaceholders won't do anything further to the result)<br>_Fieldnames_ – ```pagetitle```, ```13.Uparent2.tv.someTV```

3. _!! default_ (optional).  If the fieldname wasn't found or was empty, the default will be used.  Defaults may be values or fieldnames.

<strong>Fieldname selector prefixes</strong>

* <strong>_resource_id_.</strong> – If specified, get a field from this resource ID. Otherwise use the value of _&amp;id_ (by default the current resource).<br>_Example_: ```12.pagetitle``` – get the pagetitle of resource 12.

* <strong>parent<em>[level]</em>.</strong> – get a field from the resource's parent. Use the optional level number to move further up the tree.  If you specify more levels than exist in your document tree, setPlaceholders will simply stop at the resource's top-most parent (i.e. the ultimate parent)<br>_Examples_: ```parent.id``` – the resource's parent's id<br>```parent2.id``` – the resource's grandparent's id<br>```parent99.id``` – the resource's "ultimate parent's" id

* <strong>Uparent<em>[level]</em>.</strong> – get a field from the resource's ultimate parent. (<strong>Uparent.</strong> and <strong>parent.</strong> are essentially mirror images of one another.) Use the optional level number to move further down the tree. If you specify more levels than exist in your document tree, setPlaceholders will simply stop at the resource's immediate parent. <br>_Examples_: ```Uparent.id``` – the resource's top-most parent's id<br>```Uparent2.id``` – the resource's 2nd top-most parent's id<br>```Uparent99.id``` – the resource's parent's id

* <strong>child<em>[child #]</em>.</strong> – get a field from one of the resource's children. Use _&amp;sortby_ and _&amp;sortdir_ to control the sort order and the optional child number to specify a particular child. Negative child numbers start with the last child and move towards the first. Out-of-range child numbers will stop either at the last child or the first child. <br>_Examples_: ```child.id``` – id of the resource's first child<br>```child3.id``` – id of the resource's third child<br>```child-2.id``` – id of the resource's second-to-last child

* <strong>tv.</strong> – get a TV. (setPlaceholders returns processed TV values)

* <strong>migx<em>[object limit]</em>.</strong> – special processing for MIGX TVs (or for other arrays of JSON objects).  If you use this selector, setPlaceholders will loop through the array and create placeholders for each key/value pair (it skips MIGX\_id), plus a total. The placeholder names are in the format _[main placeholder name].[key][item #]_. Adding an optional number after _migx_ limits the results to the first _N_ objects in the TV.<br>_Example_: The parent resource has a MIGX TV called imagestv with two fields: title and image.  The resource has three items stored in this tv. ```photos == parent.migx.imagestv``` will set 7 placeholders: ```[[+photos.title1]]``` ```[[+photos.image1]]``` ```[[+photos.title2]]``` ```[[+photos.image2]]``` ```[[+photos.title3]]``` ```[[+photos.image3]]``` and ```[[+photos.total]]``` (the number of items processed: 3)<br>```photos == parent.migx1.imagestv``` will set 3 placeholders: ```[[+photos.title1]]``` ```[[+photos.image1]]``` and ```[[+photos.total]]``` (1).

* <strong>get.</strong> – a variable from $_GET<br>_Example_: ```get.page``` – the value of $_GET['page']

* <strong>post.</strong> – a variable from $_POST (be sure to call setPlaceholders uncached if you're using either get or post)

Prefixes may be chained where it makes sense.  For instance: ```parent.pagetitle``` or ```42.parent3.tv.someTV```.

setPlaceholders caches the results of some MODX API calls it makes, so getting multiple fields from the same resource or from various parents or children of the same resource is quite efficient, even if the calls occur in different places in the template.

[![githalytics.com alpha](https://cruel-carlota.pagodabox.com/34a8457ccfdcdec3f456f0e0b2d45395 "githalytics.com")](http://githalytics.com/oo12/setPlaceholders)
