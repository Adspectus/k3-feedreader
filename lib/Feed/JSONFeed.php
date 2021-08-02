<?php
/**
 * This file contains the JSONFeed class for the FeedReader plugin.
 */

namespace Adspectus\FeedReader;

/**
 * The JSONFeed class represents a JSON feed.
 */
class JSONFeed extends Feed {

  /**
   * The constructor will extract the feed properties from the content.
   * 
   * @param string $content
   */
  public function __construct(string $content) {
    $response = $this->getResponse($content);
    $this->response = $response;

    /** The propereties of the feed will be set by the according methods. */
    $this->setTitle($response);
    $this->setDescription($response);
    $this->setLink($response);
    $this->setArticles($response);
    $this->setLanguage($response);
    $this->setBuildDate($response);
  }

  /**
   * Converts the raw HTTP content string into an array.
   * 
   * @param string $content
   * 
   * @return array The JSON content string converted to an array.
   */
  protected function getResponse(string $content): array {
    $response = json_decode($content, true);
    if (is_null($response)) {
      $this->error[] = "Error: In " . __METHOD__ . " at line " . __LINE__ . ": could not decode JSON content.";
      return [];
    }
    return $response;
  }

  /**
   * Set the title of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setTitle(array $response): void {
    $this->title = $response['title'] ?? '';
  }

  /**
   * Set the description of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setDescription(array $response): void {
    $this->description = $response['description'] ?? '';
  }

  /**
   * Set the URL of the feed from the response array.
   * 
   * @param array $response
   * 
   * @return void
   */
  protected function setLink(array $response): void {
    $this->link = $response['feed_url'] ?? $response['home_page_url'] ?? '';
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
    $items = $response['items'] ?? $response['news'] ?? null;
    if (isset($items)) {
      foreach ($items as $item) {
        $itemProperty = [
          'title'        => $item['title'] ?? $item['node']['title'] ?? '',
          'description'  => $item['content_text'] ?? $item['content_html'] ?? $item['node']['description'] ?? '',
          'link'         => $item['url'] ?? $item['external_url'] ?? $item['node']['path'] ?? '',
          'pubdate'      => isset($item['date_published']) ? strtotime($item['date_published']) : strtotime($item['node']['date']) ?? 0,
          'guid'         => $item['id'] ?? $item['node']['guid'] ?? '',
          'image'        => $item['node']['image'] ?? '',
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
      $this->error[] = "Error: In " . __METHOD__ . " at line " . __LINE__ . ": response does not contain 'items' or 'news' key.";
    }
  }

}
