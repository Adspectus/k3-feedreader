<?php
/**
 * This file contains the main class FeedReader for the FeedReader plugin.
 */

namespace Adspectus\FeedReader;

use Kirby\Http\Remote;
use Kirby\Cache\Cache;

load(
  [
    'Adspectus\\FeedReader\\Feed' => 'Feed/Feed.php',
    'Adspectus\\FeedReader\\XMLFeed' => 'Feed/XMLFeed.php',
    'Adspectus\\FeedReader\\RSSFeed' => 'Feed/RSSFeed.php',
    'Adspectus\\FeedReader\\AtomFeed' => 'Feed/AtomFeed.php',
    'Adspectus\\FeedReader\\JSONFeed' => 'Feed/JSONFeed.php',
    'Adspectus\\FeedReader\\Article' => 'Feed/Article.php',
  ], __DIR__);

/**
 * The FeedReader class is the main class of this plugin which holds all information about a feed.
 */
class FeedReader {

  /**
   * These constants are used as flags for the debug() method to control the amount of output.
   */
  public const DEBUG_HEADER       = 0b0000001;
  public const DEBUG_CONTENT      = 0b0000010;
  public const DEBUG_REQUEST      = 0b0000100;
  public const DEBUG_RESPONSE     = 0b0001000;
  public const DEBUG_FEED         = 0b0010000;
  public const DEBUG_FEEDREADER   = 0b0100000;
  public const DEBUG_NOXDB_LIMIT  = 0b1000000;
  public const DEBUG_ALL          = 0b0111111;


  /**
   * @var string The URL of the feed
   */
  private $url;

  /**
   * @var array Options for the curl-request of the feed as defined in Kirby\Http\Remote.
   */
  private $urlOptions;

  /**
   * @var array Options for the FeedReader object.
   */
  private $feedOptions;

  /**
   * @var Cache A file cache for caching the feeds.
   */
  private $cache;

  /**
   * @var Remote Contains the request as it comes from the request method of the Remote class.
   */
  private $request;

  /**
   * @var array Contains only the header as returned by the method headers() in Kirby\Http\Remote.
   */
  private $header;

  /**
   * @var string Contains only the content as returned by the method content() in Kirby\Http\Remote.
   */
  private $content;

  /**
   * @var array Contains the response as returned by the individual feed classes.
   */
  private $response;

  /**
   * @var bool Indicates if a feed comes from the Cache or not.
   */
  private $fromCache;

  /**
   * @var Feed Contains the feed as a Feed objet.
   */
  private $feed;

  /**
   * @var array Contains the error messages if an error occured.
   */
  private $error;

  /**
   * Creates a new FeedReader object.
   * 
   * @param string $url
   * @param array $urlOptions 
   * @param array $feedOptions 
   */
  public function __construct(string $url, array $urlOptions = [], array $feedOptions = []) {
    $this->url = $url;
    $this->urlOptions = array_merge(kirby()->option('adspectus.feedreader.urlOptions'),$urlOptions);
    $this->feedOptions = array_merge(kirby()->option('adspectus.feedreader.feedOptions'),$feedOptions);

    $this->error = [];

  /**
   * Even if the cache is globally enabled, it can be disabled per feed.
   * The cached feed will only be used, if it is not expired (defaults to 1 day), and has either an etag or
   * a last-modified header, which will be tested in this order. The etag or the last-modified values will
   * be added to the urlOptions. If the feed cache will be disabled, an existing entry in the cache will be
   * removed.
   */
    $this->cache = kirby()->cache('adspectus.feedreader');
    $cachedRequest = [];
    if (kirby()->option('adspectus.feedreader.cache')) {
      if ($this->feedOptions['useCache']) {
        if ($cachedRequest = $this->cache->get(md5($this->url))) {
          if (isset($cachedRequest['header']['ETag'])) {
            $this->urlOptions['headers'] = ['If-None-Match' => $cachedRequest['header']['ETag']];
          }
          elseif (isset($cachedRequest['header']['etag'])) {
            $this->urlOptions['headers'] = ['If-None-Match' => $cachedRequest['header']['etag']];
          }
          elseif (isset($cachedRequest['header']['Last-Modified'])) {
            $this->urlOptions['headers'] = ['If-Modified-Since' => $cachedRequest['header']['Last-Modified']];
          }
          elseif (isset($cachedRequest['header']['last-modified'])) {
            $this->urlOptions['headers'] = ['If-Modified-Since' => $cachedRequest['header']['last-modified']];
          }
        }
      }
      else {
        if ($this->cache->exists(md5($this->url))) {
          $this->cache->remove(md5($this->url));
        }
      }
    }

    /**
     * The request is made by means of the Kirby\Http\Remote class.
     */
    $request = Remote::request($this->url,$this->urlOptions);
    $this->request = $request;

    $this->header     = null;
    $this->content    = null;
    $this->fromCache  = null;

    /**
     * If the request returns HTTP code 200 (OK), the header and the content of the request will be saved
     * and stored in the cache.
     */
    if ($request->code() === 200) {
      $this->header = $request->headers();
      $this->content = $request->content();
      $this->fromCache = false;
      if (kirby()->option('adspectus.feedreader.cache') && $this->feedOptions['useCache']) {
        $this->cache->set(md5($this->url),['header' => $this->header,'content' => $this->content],$this->feedOptions['cacheValidity']);
      }
    }
    /**
     * If the request returns HTTP code 304 (Not modified), the header and the content will be taken from
     * the cache.
     */
    elseif ($request->code() === 304) {
      $this->header = $cachedRequest['header'];
      $this->content = $cachedRequest['content'];
      $this->fromCache = true;
    }
    /**
     * If the URL needs authentication but no or wrong basicAuth credentials are supplied, the request
     * returns HTTP code 401 (Need authentication). This will be reported in the object variable $error.
     */
    elseif ($request->code() === 401) {
      if (isset($urlOptions['basicAuth'])) {
        $this->error[] = "Error: In " . __METHOD__ . " at line " . __LINE__ . ": Wrong credentials for 'basicAuth' option.";
      }
      else {
        $this->error[] = "Error: In " . __METHOD__ . " at line " . __LINE__ . ": $this->url needs authentication, but no 'basicAuth' option given.";
      }
    }
    /**
     * All other HTTP return codes will also be reported as an error.
     */
    else {
      $this->error[] = "Error: In " . __METHOD__ . " at line " . __LINE__ . ": $this->url returns HTTP status code " . $request->code() . ".";
    }

    /**
     * If the type of the feed is not supplied with the feedOptions, it will be autodetected by the
     * content-type header. If this fails, an error will be reported.
     */
    if ($this->feedOptions['type'] === 'auto') {
      if (isset($this->header)) {
        if (isset($this->header['Content-Type'])) {
          preg_match('/(rss|atom|json)/',$this->header['Content-Type'],$matches);
        }
        if (isset($this->header['content-type'])) {
          preg_match('/(rss|atom|json)/',$this->header['content-type'],$matches);
        }
        if (isset($matches[1])) {
          $this->feedOptions['type'] = $matches[1];
        }
        else {
          $this->error[] = "Error: In " . __METHOD__ . " at line " . __LINE__ . ": Could not determine feed type. Try to force type by setting type in feedOptions.";
        }
      }
      else {
        $this->error[] = "Error: In " . __METHOD__ . " at line " . __LINE__ . ": Feed type is set to autodetect, but no request header found.";
      }
    }

    /**
     * When the feed type is given or detected, the object variable $feed will contain the feed
     * information, either as RSSFeed object, AtomFeed object, or JSONFeed object.
     */
    $this->feed = null;
    if (isset($this->content)) {
      switch ($this->feedOptions['type']) {
        case 'rss':
          $this->feed = new RSSFeed($this->content);
          break;
        case 'atom':
          $this->feed = new AtomFeed($this->content);
          break;
        case 'json':
          $this->feed = new JSONFeed($this->content);
          break;
        default:
          $this->error[] = "Error: In " . __METHOD__ . " at line " . __LINE__ . ": The type does not match one of 'rss', 'atom' or 'json'.";
      }
    }
    else {
      $this->error[] = "Error: In " . __METHOD__ . " at line " . __LINE__ . ": No request content found.";
    }

    $this->response = null;
    if (isset($this->feed->response)) {
      $this->response = $this->feed->response;
      unset($this->feed->response);
    }

    if (isset($this->feed->error)) {
      $this->error = array_merge($this->error ?? [],$this->feed->error);
      unset($this->feed->error);
    }
  }

  /**
   * For debugging purposes, this method returns variables and objects.
   * The output can be limited or extended by using one or more flags.
   * 
   * @param int $flags
   * 
   * @return array
   */
  public function debug(int $flags = self::DEBUG_FEEDREADER | self::DEBUG_FEED): array {
    ini_set('xdebug.var_display_max_data', 256);
    ini_set('xdebug.var_display_max_depth', 5);

    if ($flags === 64) {
      $flags = self::DEBUG_NOXDB_LIMIT | self::DEBUG_ALL;
    }
    if ($flags & self::DEBUG_NOXDB_LIMIT) {
      ini_set('xdebug.var_display_max_children', -1);
      ini_set('xdebug.var_display_max_data', -1);
      ini_set('xdebug.var_display_max_depth', 10);
    }

    $debug = [];

    if ($flags & self::DEBUG_REQUEST) {
      $debug['request']  = $this->request;
    }
    else {
      if ($flags & self::DEBUG_HEADER) {
        $debug['header']  = $this->header;
      }
      if ($flags & self::DEBUG_CONTENT) {
        $debug['content']  = $this->content;
      }
    }
    if ($flags & self::DEBUG_RESPONSE) {
      $debug['response'] = $this->response;
    }
    if ($flags & self::DEBUG_FEEDREADER) {
      $debug['feedreader'] = [
        'url'         => $this->url,
        'urlOptions'  => $this->urlOptions,
        'feedOptions' => $this->feedOptions,
        'fromCache'   => $this->fromCache,
        'error'       => $this->error,
      ];
    }
    if ($flags & self::DEBUG_FEED) {
      $debug['feed'] = $this->feed;
    }

    return $debug;
  }

  /**
   * @return array The errors which might have occured by fetching the feed.
   */
  public function error(): array {
    return $this->error;
  }

  /**
   * @return string The title of the feed.
   */
  public function title(): string {
    return $this->feed->title();
  }

  /**
   * @return string The description of the feed.
   */
  public function description(): string {
    return $this->feed->description();
  }

  /**
   * @return string The URL of the feed.
   */
  public function link(): string {
    return $this->feed->link();
  }

  /**
   * @param int|null $count The number of feed items to return
   * @param string $order The order of the items. Default is newest first. Set to 'reverse' to get oldest first.
   * 
   * @return array The list of feed items.
   */
  public function articles(int $count = null, string $order = 'standard'): array {
    return array_slice($order === 'reverse' ? array_reverse($this->feed->articles()) : $this->feed->articles(), 0, $count);
  }

    /**
   * @return string The language of the feed.
   */
  public function language(): string {
    return $this->feed->language();
  }

  /**
   * @return int The build date of the feed.
   */
  public function builddate(string $format = '%c'): string {
    return strftime($format, $this->feed->builddate());
  }

  /**
   * @return bool Indicates if the feed has been fetched from cache or not.
   */
  public function fromCache(): bool {
    return $this->fromCache;
  }

}
