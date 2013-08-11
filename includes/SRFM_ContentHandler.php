<?php
/**
 * @file
 * @ingroup SemanticMustacheFormat
 */


/**
 * SRFMContentHandler is a class that handles the content model for Mustache 
 * template wiki pages.
 *
 * @author Tony Crisci <wikifoot@hikerplaces.org>
 * @ingroup SemanticMustacheFormat
 */
class SRFMContentHandler extends TextContentHandler {

  /*
   * @param int $modelId
   * @param Array $formats
   */
	public function __construct( $modelId = SRFM_CONTENT_MUSTACHE, $formats = array( CONTENT_FORMAT_TEXT ) ) {
		parent::__construct( $modelId, $formats );
	}

	public function isSupportedFormat( $format ) {
		if ( $format === 'CONTENT_FORMAT_TEXT' ) {
			$format = CONTENT_FORMAT_TEXT;
		}
		return parent::isSupportedFormat( $format );
	}

	/**
	 * Unserializes a SRFM Content object.
	 *
	 * @param  $text    string       Serialized form of the content
	 * @param  $format  null|string  The format used for serialization
   *
	 * @return Content  the SRFM Content object wrapping $text
	 */
	public function unserializeContent( $text, $format = null ) {
		$this->checkFormat( $format );
		return new SRFMContent( $text );
	}

	/**
	 * Creates an empty SRFM Content object.
	 *
	 * @return  Content
	 */
	public function makeEmptyContent() {
		return new SRFMContent( '' );
	}
}
