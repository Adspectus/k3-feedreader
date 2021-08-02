<?php
/**
 * This file contains the abstract XMLFeed class for the FeedReader plugin.
 */

namespace Adspectus\FeedReader;

use Kirby\Toolkit\Xml;

/**
 * The XMLFeed class is an abstract class to provide the concepts for all real feed classes based on XML.
 */
abstract class XMLFeed extends Feed {
  
  /**
   * Converts the raw HTTP content string into an array.
   * 
   * All XML based feeds should get the decoded response array by means of an XML parser.
   * This parser is from the Kirby toolkit.
   * 
   * @param string $content
   * 
   * @return array The XML string converted to an array.
   */
  protected function getResponse(string $content): array {
    return Xml::parse($content);
  }

  /**
   * Converts a potentially nested value of a tag in a plain text string. Eventually also
   * removes all HTML/XML tags and encoded characters from the value.
   * 
   * @param mixed $property
   * 
   * @return string The value of the property.
   */
  public function toText($property): string {
    if (is_null($property)) { return ''; }
    if (is_string($property)) { return $property; }
    if (is_array($property)) {
      $type = '';
      if (isset($property['@attributes'])) {
        $type = $property['@attributes']['type'] ?? '';
      }
      switch ($type) {
        case 'html':
          return Xml::decode($property['@value']);
          break;
        case 'text':
          return $property['@value'];
          break;
        case '':
          if (is_string($property['@value'])) {
            return $property['@value'];
          }
          break;
      }
    }
  }

}