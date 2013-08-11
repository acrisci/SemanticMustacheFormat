<?php

# The name of a page in the Module namespace that will be included as helpers 
# by default on every Mustache template. Requires Scribunto.
# See: (http://www.mediawiki.org/wiki/Extension:Scribunto).
$srfmDefaultHelperModule = null;

# Use geshi for syntax hilighting of content pages in the Mustache namespace.  
# Requires the SyntaxHighlight_GeSHi extension. If this is false, content pages 
# will show the template in a <pre/> tag with no colorful hilighting.
# See: (http://www.mediawiki.org/wiki/Extension:SyntaxHighlight_GeSHi).
$srfmUseGeSHi = false;

# This is an array of "helper" functions or variables that will be available in 
# all templates. If the helper is a function, it must be a closure instance or 
# an invokable class.
$srfmHelpers = array();
