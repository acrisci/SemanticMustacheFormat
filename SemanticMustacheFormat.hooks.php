<?php
/**
 * @file
 * @ingroup SemanticMustacheFormat
 */


/**
 * Static class for hooks handled by the Semantic Mustache Format extension.
 *
 * @author Tony Crisci <wikifoot@hikerplaces.org>
 * @ingroup SemanticMustacheFormat
 */

class SRFMHooks
{
	/**
	 * Hook to set the Mustache content handler for templates.
   *
	 * @param Title $title
	 * @param string &$model
   *
	 * @return bool
	 */
  public static function contentHandlerDefaultModelFor( $title, &$model ) {
    if ( $title->getNamespace() === SRFM_NS_MUSTACHE ) {
      $model = SRFM_CONTENT_MUSTACHE;
      return false;
    }

    return true;
  }
}
