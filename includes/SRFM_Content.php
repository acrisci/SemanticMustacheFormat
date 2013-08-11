<?php
/**
 * @file
 * @ingroup SemanticMustacheFormat
 */


/**
 * SRFMContent is a class that defines the content model for Mustache template 
 * wiki pages.
 *
 * @author Tony Crisci <wikifoot@hikerplaces.org>
 * @ingroup SemanticMustacheFormat
 */
class SRFMContent extends TextContent {

  /**
   * @param string $text
   */
	function __construct( $text ) {
		parent::__construct( $text, SRFM_CONTENT_MUSTACHE );
	}

	/**
	 * Parse the Content object and generate a ParserOutput from the result.
	 *
	 * @param $title Title The page title to use as a context for rendering
	 * @param $revId null|int The revision being rendered (optional)
	 * @param $options null|ParserOptions Any parser options
	 * @param $generateHtml boolean Whether to generate HTML (default: true).
   *
	 * @return ParserOutput
	 */
	public function getParserOutput( Title $title, $revId = null, ParserOptions $options = null, $generateHtml = true ) {
		global $srfmUseGeSHi;

		$text = $this->getNativeData();
		$output = null;

		if ( !$options ) {
			//NOTE: use canonical options per default to produce cacheable output
			$options = $this->getContentHandler()->makeParserOptions( 'canonical' );
		}

		$output = new ParserOutput();

		if ( !$generateHtml ) {
			// We don't need the actual HTML
			$output->setText( '' );
			return $output;
		}

		// Add HTML for the actual script
		if( $srfmUseGeSHi ) {
      $language = 'html5';
			$geshi = SyntaxHighlight_GeSHi::prepare( $text, $language );
			$geshi->set_language( $language );
			if( $geshi instanceof GeSHi && !$geshi->error() ) {
				$code = $geshi->parse_code();
				if( $code ) {
					$output->addHeadItem( SyntaxHighlight_GeSHi::buildHeadItem( $geshi ), "source-{$language}" );
					$output->setText( $output->getText() . "<div dir=\"ltr\">{$code}</div>" );
					return $output;
				}
			}
		}

		// No GeSHi, or GeSHi can't parse it, use plain <pre>
		$output->setText( $output->getText() .
			"<pre class=\"mw-code mw-script\" dir=\"ltr\">\n" .
			htmlspecialchars( $text ) .
			"\n</pre>\n"
		);

		return $output;
	}

	/**
	 * Returns a Content object with pre-save transformations applied (or this
	 * object if no transformations apply).
	 *
	 * @param $title Title
	 * @param $user User
	 * @param $parserOptions null|ParserOptions
   *
	 * @return Content
	 */
	public function preSaveTransform( Title $title, User $user, ParserOptions $parserOptions ) {
		return $this;
	}
}
