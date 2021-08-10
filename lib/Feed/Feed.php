<?php
/**
 * This file contains the abstract Feed class for the FeedReader plugin.
 */

namespace Adspectus\FeedReader;

/**
 * The Feed class is an abstract class to provide the concepts for a real feed class.
 * 
 * All properties will be derived from the feed by the protected functions
 * "set...", i.e. the title will be set by the setTitle function, etc.
 */
abstract class Feed {

  /**
   * @var array The response of the feed which will be decoded from the requested
   *            HTTP content. The decode method depends on the type of the feed.
   */
  public $response = [];

  /**
   * @var string The title of the feed.
   */
  protected $title = '';

  /**
   * @var string The description of the feed.
   */
  protected $description = '';

  /**
   * @var string The URL of the feed (might differ from the URL of the request).
   */
  protected $link = '';

  /**
   * @var array The list of feed items (as objects of class Article).
   */
  protected $articles = [];

  /**
   * @var string The language of the feed.
   */
  protected $language = '';

  /**
   * @var int The build date of the feed (as epoch).
   */
  protected $builddate = 0;

  /**
   * @var array  Errors will be reported here.
   */
  public $error = [];

  /**
   * Converts the raw HTTP content string into an array.
   * 
   * This method should convert the raw HTTP content from the request into an
   * parseable response array. All remaining methods work on this response
   * array to get the needed properties of the feed.
   * 
   * @param string $content
   * 
   * @return array
   */
  abstract protected function getResponse(string $content): array;

  /**
   * Set the title of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
   protected function setTitle(array $response): void {}

  /**
   * Set the description of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setDescription(array $response): void {}

  /**
   * Set the URL of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setLink(array $response): void {}

  /**
   * Set the items (articles) of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setArticles(array $response): void {}

  /**
   * Set the language of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setLanguage(array $response): void {}

  /**
   * Set the build date of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setBuildDate(array $response): void {}

  /**
   * @return string The title of the feed.
   */
  public function title(): string {
    return $this->title;
  }

  /**
   * @return string The description of the feed.
   */
  public function description(): string {
    return $this->description;
  }

  /**
   * @return string The URL of the feed.
   */
  public function link(): string {
    return $this->link;
  }

  /**
   * @return array The list of feed items.
   */
  public function articles(): array {
    return $this->articles;
  }

  /**
   * @return string The language of the feed.
   */
  public function language(): string {
    return $this->language;
  }

  /**
   * @return int The build date of the feed.
   */
  public function builddate(): int {
    return $this->builddate;
  }

 /**
  * Function to be used in usort to sort articles newest first.

  * @param Article $a
  * @param Article $b
  * 
  * @return int
  */
  static function sortArticles(Article $a, Article $b): int {
    return $b->pubdate('%s') <=> $a->pubdate('%s');
  }
}
