setPlaceholders

Author: Jason Grant
Copyright 2012-2013

Full documentation, bugs and feature requests:
https://github.com/oo12/setPlaceholders

A MODX Revolution snippet for getting fields and setting placeholders.

Placeholders have a number of useful applications. Essentially they can
be used as local variables in a template or chunk. They’ve always been
simple to set from within a snippet; setPlaceholders makes it easy
to set them without any PHP coding.

Additionally setPlaceholders provides a fast and versatile engine for
gathering up various data: fields and TVs from a resource or any of its
ancestors, as well as $_GET and $_POST variables. It provides most of
the functionality of getResourceField, UltimateParent, and getUrlParam
all in one lightweight package.

setPlaceholders may also be used as an output filter to store the result
of an expression or snippet call for further use elsewhere in the template.

For documentation and examples, please see the setPlaceholders readme
on Github: https://github.com/oo12/setPlaceholders