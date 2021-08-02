<?php
/**
 * This file contains the Article class for the FeedReader plugin.
 */

namespace Adspectus\FeedReader;

/**
 * The Article class provides an unified object for the items in a feed.
 * 
 * Each item/article consists of a certain set of properties which will be
 * provided upon creation of a new Article object, and the object provides
 * the methods to access these properties from the FeedReader object.
 */
class Article {
  
  /**
   * @var string The title of the item/article.
   */
  private $title;

  /**
   * @var string The description of the item/article.
   */
  private $description;

  /**
   * @var string The URL to the full item/article.
   */
  private $link;

  /**
   * @var int The date of the item/article (as epoch).
   */
  private $pubdate;

  /**
   * @var string The GUID (short URL) of the item/article.
   */
  private $guid;

  /**
   * @var string The URL to the image of the item/article.
   */
  private $image;

  /**
   * The constructor for the Article class.
   * 
   * Takes an array of named properties and converts them to properties of the
   * class.
   * 
   * @param array $itemProperty
   */
  public function __construct(array $itemProperty) {
    $this->title        = $itemProperty['title'] ?? '';
    $this->description  = $itemProperty['description'] ?? '';
    $this->link         = $itemProperty['link'] ?? '';
    $this->pubdate      = $itemProperty['pubdate'] ?? 0;
    $this->guid         = $itemProperty['guid'] ?? '';
    $this->image        = $itemProperty['image'] ?? '';
  }

  /**
   * @return string The title of the item/article.
   */
  public function title(): string {
    return $this->title;
  }

  /**
   * @return string  The description of the item/article.
   */
  public function description(): string {
    return $this->description;
  }

  /**
   * @return string The URL to the full item/article.
   */
  public function link(): string {
    return $this->link;
  }

  /**
   * @param string $format
   * 
   * @return string The date of the item/article formatted by a strftime format string.
   */
  public function pubdate(string $format = '%c'): string {
    return strftime($format, $this->pubdate);
  }

  /**
   * @return string The GUID (short URL) of the item/article.
   */
  public function guid(): string {
    return $this->guid;
  }

  /**
   * @return string The URL to the image of the item/article.
   */
  public function image(): string {
    return $this->image;
  }

}