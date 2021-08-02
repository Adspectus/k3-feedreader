<?php
/**
 * This file contains the RSSFeed class for the FeedReader plugin.
 */

namespace Adspectus\FeedReader;

/**
 * The RSSFeed class represents a RSS feed.
 */
class RSSFeed extends XMLFeed {

  /**
   * The constructor will extract the feed properties from the content.
   * 
   * @param string $content
   */
  public function __construct(string $content) {
    $response = $this->getResponse($content); /** The getResponse method is defined in XMLFeed class. */
    $this->response = $response;

    if (isset($response['@name']) && $response['@name'] == "rss") {

      /** In RSS, the feed is wrapped into an additional <channel> element. Thus, the 
       * $reponse variable will be overwritten by the content of this element only.
       */
      if (isset($response['channel'])) {
        $response = $response['channel'];

        /** The propereties of the feed will be set by the according methods. */
        $this->setTitle($response);
        $this->setDescription($response);
        $this->setLink($response);
        $this->setArticles($response);
        $this->setLanguage($response);
        $this->setBuildDate($response);
      }
      else {
        $this->error[] = "Error: RSS content does not contain <channel> element.";
      }
    }
    else {
      $this->error[] = "Error: URL does not return RSS content.";
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
    $this->description = $this->toText($response['description'] ?? '');
  }

  /**
   * Set the URL of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setLink(array $response): void {
    $this->link = $this->toText($response['link'] ?? '');
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
    if (isset($response['item'])) {
      foreach ($response['item'] as $item) {
        $itemProperty = [
          'title'        => $this->toText($item['title'] ?? ''),
          'description'  => $this->toText($item['description'] ?? ''),
          'link'         => $this->toText($item['link'] ?? ''),
          'pubdate'      => isset($item['pubDate']) ? strtotime($item['pubDate']) : 0,
          'guid'         => $this->toText($item['guid'] ?? ''),
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
      $this->error[] = "Error: In " . __METHOD__ . " at line " . __LINE__ . ": response does not contain <item> element.";
    }
  }

  /**
   * Set the language of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setLanguage(array $response): void {
    if (isset($response['language'])) {
      $this->language = $this->toText($response['language']);
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
    if (isset($response['lastBuildDate'])) {
      $this->builddate = strtotime($response['lastBuildDate']);
    }
  }
  
}
