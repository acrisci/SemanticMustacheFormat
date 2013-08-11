<?php
/**
 * Internationalization file for the Semantic Mustache Format extension
 *
 * @file
 * @ingroup Extensions
 */

/** English
 */
$messages['en'] = array(
  // Extension info
  'srfm-name' => 'Semantic Mustache Format',
  'srfm-desc' => 'Display semantic query results with [http://mustache.github.io/ Mustache] templates.',
  'srfm-printername-mustache' => 'Mustache Template',
  // Errors
  'srfm-no-results' => 'The query returned no results.',
  'srfm-error-unknown-template' => 'Could not find template: $1.',
  'srfm-error-engine' => 'Mustache engine returned error: $1.',
  // Parameter descriptions
  'srfm-paramdesc-template' => 'The page in the Mustache namespace to use as a template.',
  'srfm-paramdesc-helper-module' => 'The page in the Module namespace which contains public methods that can be used as helper functions.',
  'srfm-paramdesc-tags' => 'Alternative tag to use instead of Mustache curly braces. Set to "erb" to use <% and %> as delimiters so they are not confused with WikiText template tags. If your template has WikiText templates and parser functions and you do not change delimiters, the WikiText will be confused with Mustache markup.',
  'srfm-paramdesc-has-wiki-templates' => 'If your Mustache template has Wiki templates and parser functions, you must set this to true for them to render. To use both Mustache and WikiText templates on the same page, you must set the delimeter to something that will not be confused with WikiText delimiters in your Mustache markup or with the "tags" parameter.',
  'srfm-paramdesc-formatting' => 'Set to "none" and all your properties of type "number" (including quantities) will not be formatted by the parser function so you can easily decode them in your helper functions. Quantities will be converted (by appending "#[unit]" to the property in the query string) but the unit will be left out.',
);
