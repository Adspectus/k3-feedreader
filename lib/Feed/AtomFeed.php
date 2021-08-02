<?php
/**
 * This file contains the AtomFeed class for the FeedReader plugin.
 */

namespace Adspectus\FeedReader;

/**
 * The AtomFeed class represents an Atom feed.
 */
class AtomFeed extends XMLFeed {

  /**
   * The constructor will extract the feed properties from the content.
   * 
   * @param string $content
   */
  public function __construct(string $content) {
    $response = $this->getResponse($content);
    $this->response = $response;

    if (isset($response['@name']) && $response['@name'] == "feed") {

      /** The propereties of the feed will be set by the according methods. */
      $this->setTitle($response);
      $this->setDescription($response);
      $this->setLink($response);
      $this->setArticles($response);
      $this->setLanguage($response);
      $this->setBuildDate($response);
    }
    else {
      $this->error[] = "Error: URL does not return Atom content.";
    }
  }

  /**
   * Set the title of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setTitle(array $response): void {
    $this->title = $this->toText($response['title'] ?? '');
  }

  /**
   * Set the description of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setDescription(array $response): void {
    $this->description = $this->toText($response['subtitle'] ?? '');
  }

  /**
   * Set the URL of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setLink(array $response): void {
    if (isset($response['link']['@attributes'])) {
      if ($response['link']['@attributes']['rel'] === 'self') {
        $this->link = $this->toText($response['link']['@attributes']['href']);
      }
      elseif ($response['link']['@attributes']['rel'] === 'alternate') {
        $this->link = $this->toText($response['link']['@attributes']['href']);
      }
    }
    else {
      $this->link = $this->toText($response['id'] ?? '');
    }
  }

  /**
   * Set the items (articles) of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setArticles(array $response): void {
    $articles = [];
    if (isset($response['entry'])) {
      foreach ($response['entry'] as $item) {
        $itemProperty = [
          'title'        => $this->toText($item['title'] ?? ''),
          'description'  => $this->toText($item['summary'] ?? ''),
          'link'         => $item['link']['@attributes']['href'] ?? $this->toText($item['id'] ?? ''),
          'pubdate'      => isset($item['updated']) ? strtotime($item['updated']) : (isset($item['published']) ? strtotime($item['published']) : 0),
          'guid'         => $this->toText($item['id'] ?? ''),
        ];
        $articles[] = new Article($itemProperty);
      }

      /** Items/Articles will be sorted newest first by default. */
      if (count($articles) > 0) {
        if (count($articles) > 1) {
          usort($articles, "parent::sortArticles");
        }
        $this->articles = $articles;
      }
    }
    else {
      $this->error[] = "Error: In " . __METHOD__ . " at line " . __LINE__ . ": response does not contain <entry> element.";
    }
  }

  /**
   * Set the build date of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setBuildDate(array $response): void {
    if (isset($response['updated'])) {
      $this->builddate = strtotime($response['updated']);
    }
  }
  
}
