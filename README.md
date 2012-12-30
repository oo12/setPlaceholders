setPlaceholders
===============

A simple MODX 2.x snippet for getting fields and setting placeholders.

Placeholders have a number of useful applications; essentially they can be used as local variables in a template or chunk.  setPlaceholders provides an easy way of gathering up various data (fields and TVs from a resource and any of its ancestors, arbitrary and fallback values, even $\_GET and $_POST variables) and putting them into placeholders or returning them as output.  Most of this is achieved through a handy syntax which allows fields names to be prefixed. For instance parent.tv.myTV will get the value of myTV from the selected resource&rsquo;s parent.

Properties
----------

* <strong>```&id```</strong> – The resource id to use<br><strong>Default:</strong> current resource
* <strong>```&fields```</strong> – A list of fields to retrieve, separated by ||<br>_Field names may be prefixed by_:<br>```tv.``` – to get a TV<br>```parent.``` – to get a field from the resource&rsquo;s parent. Can be used multiple times to get a parent&rsquo;s parent, etc. and can be a tv (ex: parent.parent.tv.someTV).<br>```get.``` – to get a variable from $\_GET<br>```post.``` – to get a variable from $\_POST<br>Specify a default value by appending !! value to the field name. Ex: longtitle !! Default Title<br>Placeholder names are a prefix (see &amp;prefix) plus the field name. Ex: &amp;fields=\`tv.someTV || parent.id\` will set the placeholders [[+ph.tv.someTV]] and [[+ph.parent.id]]
* <strong>```&prefix```</strong> – Prepended to all placeholder names from &amp;fields to reduce the likelihood of variable name conflicts. Not added to any user-specified placeholders in &amp;placeholders. Can be set to an empty string to eliminate the prefix.
* <strong>```&placeholders```</strong> – An optional list of user-defined placeholders to set<br>Format: name1==value1 || name2==value2
* <strong>```&output```</strong> – Output mode.<br>_No_: only set placeholders<br>_Yes_: also output the value of any placeholders. This allows the snippet to be used like getResourceField. Multiple values will be separated by &amp;delimiter.<br><strong>Default:</strong> No
* <strong>```&delimiter```</strong> – Separates values when results are returned as a string (i.e. &amp;output=\`1\`)<br><strong>Default: ,</strong>

It's fine to add white space around ||, ==, and !! for legibility. It'll get trimmed off.

Examples
--------

1. ```[[setPlaceholders? &id=`13` &fields=`pagetitle || tv.someTV !! [[snippetX]] || get.person`   &placeholders=`color==#a35c0a || params == w=240&h=360&q=65`]]```
Sets the placeholders:

  * ```[[+ph.pagetitle]]``` Resource 13's pagetitle
  * ```[[+ph.tv.someTV]]``` value of resource 13's TV someTV, or the output from snippetX if not found<br>
  * ```[[+ph.get.person]]``` value of $_GET['person']<br>
  * ```[[+color]]``` #a35c0a<br>
  * ```[[+params]]``` w=240&h=360&q=65


2. As a getResourceField replacement:
```[[setPlaceholders? &fields=`parent.tv.someTV !! No such TV` &output=`1`]]```<br>
Returns the value of someTV for the current resource's parent, or &ldquo;No such TV&rdquo; if it's not found.  It also puts this value in ```[[+ph.parent.tv.someTV]]```
