<?php
/**
 * @file
 * @ingroup SemanticMustacheFormat
 */

/**
 * SRFMTemplater is a class that provides a gateway to the Mustache PHP 
 * template engine. It performs the loading and rendering of templates and the 
 * coordination of Scribunto helpers.
 *
 * @author Tony Crisci <wikifoot@hikerplaces.org>
 * @ingroup SemanticMustacheFormat
 */
class SRFMTemplater implements Mustache_Loader
{
  private $engine;
  private $scribunto;
  protected $templateCache = array();
  protected $options = array();

  /**
    * @param Array $options
    * @return null
   */
  public function __construct( $options = array() ) {
    $this->options = $options;
  }

  /*
   * @return Scribunto_LuaEngine
   */
  protected function getScribunto() {
    global $wgParser;
    if ( !$this->scribunto ) {
      $this->scribunto = Scribunto::getParserEngine( $wgParser );
    }
    
    return $this->scribunto;
  }

  /**
   * @param string $module
   * @param string $question
   * @param Array $prevQuestions
   *
   * @return string
   */
  protected function runConsole( $module, $question, $prevQuestions = array() ) {
    $title = RequestContext::getMain()->getTitle();
    $result = $this->getScribunto()->runConsole(
      array(
        'title' => $title,
        'content' => 'return require("Module:' . $module . '")',
        'question' => $question,
        'prevQuestions' => $prevQuestions,
      )
    );

    return trim($result['print']);
  }

  /**
   * @param string $module
   *
   * @return Array
   */
  protected function getModuleHelpers($module) {
    $helpers = array();
    $prevQuestions = array(
      'local methods = {}',
      'for k,v in pairs(p) do table.insert(methods, k.."@"..type(v)) end',
    );
    $question = 'print( table.concat(methods, ";") )';
    $result = $this->runConsole($module, $question, $prevQuestions);

    foreach ( explode( ';', $result ) as $method ) {
      $method = explode( '@', $method );
      $name = $method[0];
      $type = $method[1];

      if ( $type === 'function' ) {
        $helpers[$name] = function( $value = array() ) use ( $module, $name ) {
          if ( is_array($value) ) {
            $serialized = array();
            foreach ( $value as $element ) {
              // Booleans get passed as booleans.
              if ( $value === true ) {
                $serialized[] = 'true';
              } else if ( $value === false ) {
                $serialized[] = 'false';
              } else {
                // Everything that isn't a boolean is passed as a string.
                $element = str_replace( '"', '\"', $element );
                $serialized[] = '"' . (string)$element . '"';
              }
            }

            $value = '{' . implode($serialized, ',') . '}';
          }

          return $this->runConsole( $module, "print(p.$name($value))" );
        };
      } else {
        $helpers[$name] = $this->runConsole( $module, "print(p.$name)" );
      }
    }

    return $helpers;
  }

  /**
   * @return Mustache_Engine
   */
  protected function getEngine() {
    if ( !$this->engine ) {
      $helpers = ( isset($this->options['helpers']) ? $this->options['helpers'] : array() );

      if ( isset($this->options['module']) ) {
        $helpers = array_merge( $helpers, $this->getModuleHelpers( $this->options['module'] ) );
      }

      $engineOptions = array(
        'loader' => $this,
        'helpers' => $helpers,
        'escape' => array( $this, 'escape' ),
        'strict_callables' => true,
      );

      $this->engine = new Mustache_Engine( $engineOptions );
    }

    return $this->engine;
  }

  /**
   * @param string $template
   * @param Array $context
   *
   * @return string
   */
  public function render( $template, $context = array() ) {
    $renderedHtml = $this->getEngine()->render( $template, $context );
    return $renderedHtml;
  }

  /**
   * Load a Template by name.
   *
   * @throws Mustache_Exception_UnknownTemplateException If a template file is not found.
   *
   * @param string $name
   *
   * @return string Mustache Template source
   */
  public function load( $name ) {
    $templateText = '';

    if ( isset($this->templateCache[$name]) ) {
      $templateText = $this->templateCache[$name];
    } else {
      $title = Title::newFromText( "Mustache:$name" );
      $wikiPage = WikiPage::factory( $title );

      if ( !$wikiPage->exists() ) {
        throw new Mustache_Exception_UnknownTemplateException($name);
      }

      $templateText = $wikiPage->getContent( Revision::FOR_PUBLIC, null )->mText;
      $this->templateCache[$name] = $templateText;
    }

    return $templateText;
  }

  /**
   * @param mixed $value
   *
   * returns string
   */
  public function escape($value) {
    $escapedValue;
    if ( is_array($value) ) {
      $escapedValue = implode( $value, ',' );
    } else {
      $escapedValue = $value;
    }

    return htmlspecialchars($escapedValue);
  }
}
