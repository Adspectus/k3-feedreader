<?php
/**
 * This file registers the FeedReader Plugin.
 * 
 * The FeedReader plugin provides an unified access to the elements of a feed.
 * A feed might be of type RSS, Atom or JSON and is accessed by its URL. A snippet
 * and a blocks blueprint are provided as a starting point.
 * 
 * @version    1.0.0
 * @author     Uwe Gehring <uwe@imap.cc
 * @copyright  Uwe Gehring <uwe@imap.cc
 * @license    GNU General Public License v3.0
 * @link       
 */


use Kirby\Cms\App as Kirby;

load(['Adspectus\\FeedReader\\FeedReader' => 'lib/FeedReader.php'], __DIR__);

Kirby::plugin('adspectus/feedreader', [
  'options' => [
    'cache' => true,
    'feedOptions' => ['type' => 'auto','useCache' => true,'cacheValidity' => (24*60)],
    'urlOptions' => ['basicAuth' => ''],
  ],
  'blueprints' => [
    'blocks/feedreader' => __DIR__ . '/blueprints/blocks/feedreader.yml',
  ],
  'snippets' => [
    'blocks/feedreader' => __DIR__ . '/snippets/blocks/feedreader.php'
  ]
]);
