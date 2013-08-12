<?php

/**
 * Initialization file for the Semantic Mustache Format extension.
 *
 * @see https://www.mediawiki.org/wiki/Extension:SemanticMustacheFormat
 * @see http://mustache.github.io/
 *
 * @licence GNU GPL v2+
 * @author Tony Crisci < wikifoot@hikerplaces.org >
 *
 * @defgroup SemanticMustacheFormat
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

$dir = __DIR__;

$wgExtensionMessagesFiles['SemanticMustacheFormat'] = "$dir/SemanticMustacheFormat.i18n.php";

$wgExtensionCredits['semantic'][] = array(
  'path' => __FILE__,
  'name' => 'Semantic Mustache Format',
  'author' => '[http://hikerplaces.org/User:Wikifoot Tony Crisci]',
  'url' => 'https://github.com/Hikerplaces/SemanticMustacheFormat',
  'descriptionmsg' => 'srfm-desc',
  'version' => '0.01',
);

# Load Mustache classes
if ( class_exists('Mustache_Engine') && strpos(Mustache_Engine::VERSION, '2.') !== 0 ) {
  throw new Exception('SemanticMustacheFormat extension requires mustache.php version 2.*');
} else {
  require "$dir/vendor/mustache/src/Mustache/Autoloader.php";
  Mustache_Autoloader::register();
}

# Define Mustache namespace
define( "SRFM_NS_MUSTACHE", 806 );
define( "SRFM_NS_MUSTACHE_TALK", 807 );
$wgExtraNamespaces[SRFM_NS_MUSTACHE] = "Mustache";
$wgExtraNamespaces[SRFM_NS_MUSTACHE_TALK] = "Mustache_talk";

# Autoload extension includes
$wgAutoloadClasses['SRFMTemplateLoader'] = "$dir/includes/SRFM_TemplateLoader.php";
$wgAutoloadClasses['SRFMTemplater'] = "$dir/includes/SRFM_Templater.php";
$wgAutoloadClasses['SRFMResultPrinter'] = "$dir/includes/SRFM_ResultPrinter.php";
$wgAutoloadClasses['SRFMHelpers'] = "$dir/includes/SRFM_Helpers.php";

$wgAutoloadClasses['SRFMContent'] = "$dir/includes/SRFM_Content.php";
$wgAutoloadClasses['SRFMContentHandler'] = "$dir/includes/SRFM_ContentHandler.php";

# Define Mustache content handler
define( "SRFM_CONTENT_MUSTACHE", 'SRFMMustache' );
$wgContentHandlers[SRFM_CONTENT_MUSTACHE] = 'SRFMContentHandler';

# Hooks
$wgAutoloadClasses['SRFMHooks'] = "$dir/SemanticMustacheFormat.hooks.php";
$wgHooks['ContentHandlerDefaultModelFor'][] = 'SRFMHooks::contentHandlerDefaultModelFor';

# Add result format to SMW ask parser function
$smwgResultFormats['mustache'] = 'SRFMResultPrinter';
