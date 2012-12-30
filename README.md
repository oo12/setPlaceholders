setPlaceholders
===============

A simple MODX 2.x snippet for getting fields and setting placeholders.

Placeholders have a number of useful applications.  Essentially they can be used as local variables in a template or chunk.  They&rsquo;ve always been simple to set from within a snippet; setPlaceholders makes it easy to set them without any PHP coding.

Additionally setPlaceholders provides a convenient way of gathering up various data (fields and TVs from a resource and any of its ancestors, arbitrary and fallback values, even $\_GET and $\_POST variables) and putting them into placeholders or returning them as output.  Most of this is achieved through a handy syntax which allows fields names to be prefixed. For instance parent.tv.myTV will get the value of myTV from the selected resource&rsquo;s parent.

setPlaceholders may also be used as an output filter to store the result of an expression or snippet call for use, say, farther down in the template.

Let&rsquo;s start with some examples.

Examples
--------

1. ```[[setPlaceholders? &id=`13` &fields=`pagetitle || tv.someTV !! [[snippetX]] || get.person`  
&placeholders=`color==#a35c0a || params == w=240&h=360&q=65`]]```<br>
Sets the placeholders:<br>
```[[+sph.pagetitle]]``` Resource 13's pagetitle<br>
```[[+sph.tv.someTV]]``` value of resource 13's TV someTV, or the output from snippetX if not found<br>
```[[+sph.get.person]]``` value of $_GET['person']<br>
```[[+color]]``` #a35c0a<br>
```[[+params]]``` w=240&h=360&q=65

2. As a getResourceField replacement:<br>
```[[setPlaceholders? &fields=`parent.tv.someTV !! No such TV` &output=`1`]]```<br>
Returns the value of someTV for the current resource's parent, or &ldquo;No such TV&rdquo; if it&rsquo;s empty or not found.  It also puts this value in ```[[+sph.parent.tv.someTV]]```<br>
This is the equivalent of:<br>```[[getResourceField? &id=`[[*parent]]` &field=`someTV` &isTV=`1` &processTV=`1` &default=`No such TV`]]```

3. As an output filter:<br>
```[[*someTV:eq=`foo`:then=`bar`:else=`nobar`:setPlaceholders=`fb`]]```<br>
Returns the value of the expression and also stores it in [[+fb]] for later use.

4. One more example, with some lesser-used options:<br>
```[[setPlaceholders? &fields=`parent.pagetitle||parent.parent.pagetitle` &prefix=`` &output=`1`  
&delimiter=` > `]]```<br>
Returns: Page Title #1 > Page Title #2 (of course it&rsquo;d be the actual pagetitles)<br>
Sets the placeholders:<br>
```[[+parent.pagetitle]]``` The current resource&rsquo;s parent&rsquo;s pagetitle<br>
```[[+parent.parent.pagetitle]]``` The current resource&rsquo;s grandparent&rsquo;s pagetitle


Properties
----------

*When called as a snippet:*

<table>
<tr><th>Name</th><th>Description</th><th>Default</th></tr>
<tr>
  <td>&id</td>
  <td>The resource id to use</td>
  <td>current resource</td>
</tr><tr>
  <td>&fields</td>
  <td>A list of fields to retrieve, separated by ||<br>
    <em>Field names may be prefixed by</em>:<br>
    <strong>tv.</strong> – to get a TV<br><strong>parent.</strong> – to get a field from the resource&rsquo;s parent. Can be used multiple times to get a parent&rsquo;s parent, etc. and can be a tv (ex: parent.parent.tv.someTV).<br>
    <strong>get.</strong> – to get a variable from $_GET<br>
    <strong>post.</strong> – to get a variable from $_POST<br><br>
    Specify a default value by appending !! value to the field name.  This will be used if the field is empty or not found. Ex: longtitle !! Default Title<br>
    Placeholder names are a prefix (see &amp;prefix) plus the field name. Ex: &amp;fields=`tv.someTV || parent.id` will set the placeholders [[+sph.tv.someTV]] and [[+sph.parent.id]]</td>
  <td></td>
</tr><tr>
  <td>&prefix</td>
  <td>Prepended to all placeholder names from &amp;fields to reduce the likelihood of variable name conflicts. Not added to any user-specified placeholders in &amp;placeholders. Can be set to an empty string to eliminate the prefix.</td>
  <td>sph.</td>
</tr><tr>
  <td>&placeholders</td>
  <td>An optional list of user-defined placeholders to set<br>Format: name1==value1 || name2==value2</td>
  <td></td>
</tr><tr>
  <td>&output</td><td>Output mode.<br>_No_: only set placeholders<br>_Yes_: also output the value of any placeholders. This allows the snippet to be used like getResourceField. Multiple values will be separated by &amp;delimiter.</td>
  <td>No</td>
</tr><tr>
  <td>&delimiter</td>
  <td>Separates values when results are returned as a string (i.e. &amp;output=`1`)</td>
  <td>,</td>
</tr>
</table>

It's fine to add white space around ||, ==, and !! for legibility. It'll get trimmed off.

*When used as an output filter:*

* setPlaceholders takes one argument: the name of the placeholder to set.

Notes
-----

If you&rsquo;re using get or post in &fields, you&rsquo;ll want to call setPlaceholders uncached. And since these variables can contain anything–including various hack attempts–be sure to validate and sanitize them properly before passing them on as input to something else, especially a SQL query.
