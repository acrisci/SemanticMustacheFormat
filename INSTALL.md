# Installing Semantic Mustache Format

## Dependencies

[MediaWiki](http://mediawiki.org/) v1.21+

[Semantic MediaWiki](http://semantic-mediawiki.org/) v1.8+

(optional) [Scribunto](http://www.mediawiki.org/wiki/Scribunto) v1.21+

## Installation

[Download](https://github.com/Hikerplaces/SemanticMustacheResult/tarball/master) a snapshot and extract in your extensions directory, or clone from Github.

    $ git clone --recursive https://github.com/Hikerplaces/SemanticMustacheResult.git

Add the following code to your `LocalSettings.php`

    require_once( "$IP/extensions/SemanticMustacheFormat/SemanticMustacheFormat.php" );

**Done -** Navigate to "Special:Version" on your wiki to verify that the
extension is successfully installed.
