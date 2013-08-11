# Semantic Mustache Format
[Mustache][] integration into [Semantic MediaWiki][] queries.

[Mustache]: http://mustache.github.com/
[Semantic MediaWiki]: http://semantic-mediawiki.org/

## Usage

WikiText is a great markup language for an encyclopedia with many editors, but
organizing structured data into a view is not practical.
[Semantic Result Formats][] is a good choice in many cases. Semantic Mustache
Format can be used when users need a more powerful templating engine for
display of the wiki's semantic data.

  [Semantic Result Formats]: http://semantic-mediawiki.org/wiki/Semantic_Result_Formats

Mustache templates are defined in the `Mustache` namespace of the wiki. A
semantic query can use one of these templates to create a view for the results.
If needed, a [Scribunto][] helper module can be used in the template to add
functions and variables in Lua.

  [Scribunto]: http://www.mediawiki.org/wiki/Extension:Scribunto

## Example

Your users can create Mustache templates as pages in the Mustache namespace on
your wiki. For example, the contents of `/wiki/Mustache:Lottery` could be:

    == Winners ==
    {{#results}}

    Hello {{name}}

    You have just won ${{value}}!

    {{#in_ca}} Well, ${{taxed_value}}, after taxes.

    {{/in_ca}}

    {{/results}}

For more information on how Mustache templates work see the mustache.php [documentation](https://github.com/bobthecow/mustache.php/wiki).

Now you can use the template in an article using the Semantic MediaWiki `ask`
parser function to create the context:

    {{#ask: [[Concept:Players]] [[Has ticket::Winner]]
      |?Has name=name
      |?Category:Californians=in_ca
      |format=mustache
      |template=Lottery
      |helper module=ValueHelpers
    }}

With the Scribunto extension installed, your users can create helper
modules written in Lua to make functions and variables available in the
templates. In this example, if the page `/wiki/Module:ValueHelpers` has the
code:

    local p = {}
    p.value = 10000
    p.taxed_value = function() return p.value - (p.value * 0.4) end
    return p

And Chris and Arnold are selected as winners, with Arnold in the category of
Californians (`in_ca` is `true`), the `ask` parser function will output:

    == Winners ==
    Hello Chris
    You have just won $10000!

    Hello Arnold
    You have just won $10000!
    Well, $6000, after taxes.

Which will then be parsed as WikiText with the rest of the page.

## Defining Mustache templates and helpers

The context of the template is an associative array called `results`. The keys
of this array are the names of the properties specified by the query. The
values are either an array of the string printouts, or if the type of the
property is boolean, simply `true` or `false`.

Property tables passed to helpers by lambda functions will be decoded as a Lua
object (indexed at 1). The output of the helper function will be converted to a
string.

## Settings and parameters

### Using WikiText parser functions and templates

WikiText links, tables, and text styling will be available in Mustache
templates. However, the default Mustache tags will conflict with WikiText
parser functions and template transclusions. You can use a different set of
delimiters, like ERB style `<%` and `%>` by prepending `{{=<% %>=}}` to the top
of your template. Then set the parameter `has templates=true` to the `ask`
query and the WikiText returned from the Mustache template will be completely parsed.

### Define default helpers in LocalSettings.php

If your templates require a helper that needs to access MediaWiki core or
extension code, add a closure instance or invokable class to the global
`$srfmHelpers` array.

If you have a single Scribunto module that has helpers to be included in
Mustache templates by default, set `$srfmDefaultHelperModule` to the title text
of the page in the Module namespace.

### Formatting quantities and numbers

By default, SMW will format numbers and quantities for human readability. Set
`formatting=none` as a parameter in the query and they will be formatted as
plain numbers that can be decoded more easily in helpers.

## Contributing

Semantic Mustache Format is an experimental extension in the early stages of
development. Feature requests, bug reports, and pull requests are welcome from
anyone who wants to contribute.

### To Do

* API module to load and render Mustache templates in the browser.
