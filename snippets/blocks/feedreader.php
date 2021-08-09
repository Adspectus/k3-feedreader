<?php
/*
  The feedreader snippet is used in the feedreader plugin (`/site/plugins/k3-feedreader`).

*/

if ($block->url()->isEmpty()) { return; }

use Adspectus\FeedReader\FeedReader;

$url = $block->url()->toUrl();
$urlOptions['basicAuth'] =  $block->basicauth()->toString();
$feedOptions['type'] = $block->feedtype()->toString();
$feedOptions['useCache'] = $block->usecache()->toBool();
$feedOptions['cacheValidity'] = ($block->cachevalidity()->toInt()*60);
$feed = new FeedReader($url,$urlOptions,$feedOptions);
/**
 *if ($kirby->option('debug')) {
 *  var_dump($feed->debug());
 *  return;
 *}
 */
?>

<div class="feedreader-wrapper">
  <?php if (! empty($feed->error())): ?>
    <?php if ($kirby->option('debug')) { var_dump($feed->error()); } ?>
  <?php else: ?>
    <h2><img src="/media/plugins/adspectus/feedreader/icons/rss.png"><?= $feed->title() ?></h2>
    <?php if ($block->showfeeddesc()->toBool()): ?>
      <div class="feed-description"><?= $feed->description() ?></div>
    <?php endif ?>
    <div class="feed-articles">
    <?php foreach ($feed->articles($block->showall()->toBool() ? null : $block->limit()->toInt(),$block->order()->toString()) as $article): ?>
      <div class="feed-article">
        <time class="feed-article-date"><?= $article->pubdate($block->dateformat()->toString()) ?></time>
        <h3><a target="_blank" rel="noopener noreferrer" href="<?= $article->link() ?>"><?= $article->title() ?></a></h3>
        <?php if ($block->showartdesc()->toBool()): ?>
          <div class="feed-article-description"><?= $article->description() ?></div>
        <?php endif ?>
      </div>
    <?php endforeach ?>
    </div>
  <?php endif ?>
</div>