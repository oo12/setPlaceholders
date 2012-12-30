setPlaceholders

Author: Jason Grant
Copyright 2012

Full documentation, bugs and feature requests:
https://github.com/oo12/setPlaceholders

A simple MODX 2.x snippet for getting fields and setting placeholders.

Placeholders have a number of useful applications. Essentially they can be
used as local variables in a template or chunk. They’ve always been simple to
set from within a snippet; setPlaceholders makes it easy to set them without
any PHP coding.

Additionally setPlaceholders provides a convenient way of gathering up various
data (fields and TVs from a resource and any of its ancestors, arbitrary and
fallback values, even $_GET and $_POST variables) and putting them into
placeholders or returning them as output. Most of this is achieved through a
handy syntax which allows fields names to be prefixed. For instance
parent.tv.myTV will get the value of myTV from the selected resource’s parent.

setPlaceholders may also be used as an output filter to store the result of an
expression or snippet call for use, say, farther down in the template.

Examples
--------

[[setPlaceholders?
  &id=`13`
  &fields=`pagetitle || tv.someTV !! [[snippetX]] || get.person`
  &placeholders=`color==#a35c0a || params == w=240&h=360&q=65`
 ]]

Sets the placeholders:
[[+sph.pagetitle]] Resource 13's pagetitle
[[+sph.tv.someTV]] value of resource 13's TV someTV, or the output from
  snippetX if not found
[[+sph.get.person]] value of $_GET['person']
[[+color]] #a35c0a
[[+params]] w=240&h=360&q=65



As a getResourceField replacement:

[[setPlaceholders?
  &fields=`parent.tv.someTV !! No such TV` &output=`1`
]]

Returns the value of someTV for the current resource's parent, or “No such TV”
if it’s empty or not found. It also puts this value in [[+sph.parent.tv.someTV]]
This is the equivalent of:
[[getResourceField?
  &id=`[[*parent]]`
  &field=`someTV`
  &isTV=`1`
  &processTV=`1`
  &default=`No such TV`
]]



As an output filter:

[[*someTV:eq=`foo`:then=`bar`:else=`nobar`:setPlaceholders=`fb`]]

Returns the value of the expression and also stores it in [[+fb]] for later
use.



One more example, with some lesser-used options:

[[setPlaceholders? &fields=`parent.pagetitle||parent.parent.pagetitle` &prefix=`` &output=`1` &delimiter=` > `]]

Returns: Page Title #1 > Page Title #2 (of course it’d be the actual pagetitles)
Sets the placeholders:
[[+parent.pagetitle]] The current resource’s parent’s pagetitle
[[+parent.parent.pagetitle]] The current resource’s grandparent’s pagetitle