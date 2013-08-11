<?php

/**
 * @see http://www.semantic-mediawiki.org/wiki/Writing_result_printers
 *
 * @file SRFM_ResultPrinter.php
 * @ingroup SemanticMustacheFormat
 */

/**
 * Class that extends the SMW ask parser function to display queries with 
 * Mustache templates.
 *
 * @ingroup SemanticMustacheFormat
 * @author Tony Crisci < wikifoot@hikerplaces.org >
 */
class SRFMResultPrinter extends SMWResultPrinter {

	/**
	 * @see SMWResultPrinter::getName
	 * @return string
	 */
	public function getName() {
		return wfMessage( 'srfm-printername-mustache' )->text();
	}

	/**
	 * @see SMWResultPrinter::getResultText
	 *
	 * @param SMWQueryResult $result
	 * @param $outputMode
	 *
	 * @return string
	 */
	protected function getResultText( SMWQueryResult $result, $outputMode ) {
		// Data processing
		$data = $this->getResultData( $result, $outputMode );

		// Check if the data processing returned any results otherwise just bailout
		if ( $data === array() ) {
			// Add an error message to return method
			return $result->addErrors( array( wfMessage( 'srfm-no-results' )->inContentLanguage()->text() ) );
		} else {
			// Add options if needed to format the output
      if ( !empty($this->params['has templates']) ) {
        $this->hasTemplates = true;
      }

      // TODO: Safely implement outputting template results as HTML
      /*
      if ( $outputMode === SMW_OUTPUT_WIKI ) {
        // Do a second parse as WikiText
        $this->isHtml = false;
      } else if ( $outputMode === SMW_OUTPUT_HTML ) {
        // Skip WikiText parsing for speed
        $this->isHtml = true;
        $this->hasTemplates = false;
      }
       */

      $options = array(
        'mode' => $outputMode,
        'template' => $this->params['template'],
        'tags' => $this->params['tags'],
        'helper module' => $this->params['helper module'],
        'formatting' => $this->params['formatting'],
      );

      // Return formatted results
      try {
        return $this->getFormatOutput( $data, $options );
      } catch ( Mustache_Exception_UnknownTemplateException $e ) {
        $err = array( wfMessage( 'srfm-error-unknown-template', $e->getMessage() )->inContentLanguage()->text() );
        return $result->addErrors( $err );
      } catch ( Mustache_Exception $e ) {
        $err = array( wfMessage( 'srfm-error-engine', $e->getMessage() )->inContentLanguage()->text() );
        return $result->addErrors( $err );
      } catch ( ScribuntoException $e ) {
        $err = array(
          wfMessage( 'scribunto-lua-error-location', $e->messageArgs )->inContentLanguage()->text()
        );
        return $result->addErrors( $err );
      }
    }
  }

  /**
   * Returns an array with data
   *
   * @param SMWQueryResult $result
   * @param $outputMode
   *
   * @return array
   */
  protected function getResultData( SMWQueryResult $result, $outputMode ) {
    $data = array();

    /**
     * Get all values for all rows that belong to the result set
     * @var SMWResultArray $rows
     */
    while ( $rows = $result->getNext() ) {

      /**
       * @var SMWResultArray $field
       * @var SMWDataValue $dataValue
       */
      foreach ( $rows as $field ) {

        // Initialize the array each time it passes a new row to avoid data from
        // a previous row is remaining
        $rowData = array();

        // Get the label for the current property
        $propertyLabel = $field->getPrintRequest()->getLabel();

        // No blank labels in templates
        if ( $propertyLabel === '' ) {
          continue;
        }

        // Replace spaces with underscores to make dot notation work
        $propertyLabel = str_replace( ' ', '_', trim($propertyLabel) );

        // TODO: Find out if subobjects work
        $subjectLabel = $field->getResultSubject()->getTitle()->getFullText();

        while ( ( $dataValue = $field->getNextDataValue() ) !== false ) {

          // Get the data value item
          $dataValueItem = $this->getDataValueItem( $dataValue->getDataItem()->getDIType(), $dataValue );
          if ( $dataValueItem === true || $dataValueItem === false ) {
            $rowData = $dataValueItem;
            break;
          } else {
            $rowData[] = $this->getDataValueItem( $dataValue->getDataItem()->getDIType(), $dataValue );
          }
        }

        // Build a hierarchical array by collecting all values
        // belonging to one subject/row using labels as array key representation
        $data[$subjectLabel][$propertyLabel] = $rowData;
      }
    }

    // Return the data
    return $data;
  }

  /**
   * A quick getway method to find all SMWDIWikiPage objects that make up the results
   *
   * @param SMWQueryResult $result
   *
   * @return array
   */
  private function getSubjects( $result ) {
    $subjects = array();

    foreach ( $result as $wikiDIPage ) {
      $subjects[] = $wikiDIPage->getTitle()->getText();
    }
    return $subjects;
  }

  /**
   * Get all print requests property labels
   *
   * @param SMWQueryResult $result
   *
   * @return array
   */
  private function getLabels( $result ) {
    $printRequestsLabels = array();

    foreach ( $result as $printRequests ) {
      $printRequestsLabels[] = $printRequests->getLabel();
    }
    return $printRequestsLabels;
  }

  /**
   * Get a single data value item
   *
   * @param integer $type
   * @param SMWDataValue $dataValue
   *
   * @return mixed
   */
  private function getDataValueItem( $type, SMWDataValue $dataValue ) {
    if ( $type == SMWDataItem::TYPE_NUMBER ){
      // Check if unit is available and return the converted value otherwise
      // just return a plain number. Decode html entities so we don't need a 
      // special case in templates.
      $wikiTextVal = $dataValue->getUnit() !== '' ? html_entity_decode($dataValue->getShortWikiText()) : $dataValue->getNumber() ;

      // If 'formatting' param is set, just return plain numbers that can be 
      // decoded easily in Lua helpers.
      if ( $this->params['formatting'] === 'none' ) {
        $wikiTextVal = preg_replace( '/[^\d.]/', '', $wikiTextVal );
      }

      return $wikiTextVal;
    } else if ( $type === SMWDataItem::TYPE_BOOLEAN ) {
      return $dataValue->getBoolean();
    } else {
      // For all other data types return the wikivalue
      // TODO: strip formatting from other data types when formatting param is 
      // 'none'.
      return html_entity_decode($dataValue->getWikiValue());
    }
  }

  /**
   * Prepare data for the output
   *
   * @param array $data
   * @param array $options
   *
   * @return string
   */
  protected function getFormatOutput( $data, $options ) {
    global $srfmHelpers, $srfmDefaultHelperModule;
    $helperModule = ( empty($options['helper module']) ? $srfmDefaultHelperModule : $options['helper module'] );

    $mustacheOptions = array(
      'helpers' => $srfmHelpers,
      'module' => $helperModule,
      'tags' => $options['tags'],
    );

    $templateData = array( 'results' => array_values($data) );

    $templater = new SRFMTemplater( $mustacheOptions );
    $compiledTemplate = $templater->render( $options['template'], $templateData );

    return $compiledTemplate;
  }

  /**
   * @see SMWResultPrinter::getParamDefinitions
   *
   * @param $definitions array of IParamDefinition
   *
   * @return array of IParamDefinition|array
   */
  public function getParamDefinitions( array $definitions ) {
    $params = parent::getParamDefinitions( $definitions );

    // Add your parameters here

    # TODO: Add messages
    $params['template'] = array(
      'message' => 'srfm-paramdesc-template',
      'default' => '',
    );

    $params['helper module'] = array(
      'message' => 'srfm-paramdesc-helper-module',
      'default' => '',
    );

    $params['tags'] = array(
      'message' => 'srfm-paramdesc-tags',
      'default' => 'mustache',
      'values' => array( 'mustache', 'asp', 'erb' ),
    );

    $params['has templates'] = array(
      'type' => 'boolean',
      'message' => 'srfm-paramdesc-has-templates',
      'default' => false,
    );

    $params['formatting'] = array(
      'message' => 'srfm-paramdesc-formatting',
      'default' => 'all',
      'values' => array( 'all', 'none' ),
    );

    return $params;
  }
}
