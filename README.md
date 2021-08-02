## k3-feedreader
[![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/Adspectus/k3-feedreader?style=flat-square&label=Version)](https://github.com/Adspectus/k3-feedreader/releases)
[![GitHub issues](https://img.shields.io/github/issues/Adspectus/k3-feedreader?style=flat-square&label=Issues)](https://github.com/Adspectus/k3-feedreader/issues)
[![GitHub license](https://img.shields.io/github/license/Adspectus/k3-feedreader?label=License&style=flat-square)](https://github.com/Adspectus/k3-feedreader/blob/master/LICENSE)
[![Kirby version](https://img.shields.io/static/v1?label=Kirby&message=3&color=yellow&style=flat-square)](https://getkirby.com/)
[![PHP version](https://img.shields.io/static/v1?label=PHP&message=7.3%2B&color=8892bf&style=flat-square)](https://php.net/)


Kirby 3 plugin to show feeds.

The FeedReader plugin provides an unified access to the elements of a feed. A feed might be of type RSS, Atom or JSON and is accessed by its URL. A snippet and a blocks blueprint are provided as a starting point.

## Getting Started

### Prerequisites

* Kirby 3

### Dependencies

* `Kirby\Cache\Cache`
* `Kirby\Http\Remote`
* `Kirby\Toolkit\Xml`

### Installation

You can install this plugin via one of the following methods:

1. Composer

       composer require adspectus/feedreader

2. Manual

   Clone this repository or download the current release and extract at least the files `index.php` and `index.js`, as well as the directories `lib`, `blueprints`, and `snippets` into a new folder `site/plugins/k3-feedreader` of your Kirby installation.

## Basic Usage

The basic usage is to import the `FeedReader` class and create a new `FeedReader` object out of it anywhere in your template or snippet. The URL of the feed must be given as first and possibly only parameter:

```php
use Adspectus\FeedReader\FeedReader;

$feed = new FeedReader('https://www.heise.de/security/rss/news.rdf');
```

In case the URL is valid and can be successfully retrieved, the content of the feed is accessible through various methods of the instance of the `FeedReader` object which is now stored in the `$feed` variable (see [Reference](doc#reference) for a full description of all methods).

### Errors

If something went wrong during the creation of the object, the error message(s) will be saved and is/are accessible by the `error()` method. Just check if the return value of `error()` is empty or not. If it is not empty, the error(s) will be returned as an array. Hence

```php
if (! empty($feed->error())) {
  dump($feed->error()); /* or var_dump($feed->error()) */
}
```

will show you the error(s).

If you need more information about the `FeedReader` object - irrespective of an error - you can dump it by the `debug()` method. This method returns all properties of the `FeedReader` object as an array:

```php
dump($feed->debug()); /* or var_dump($feed->debug()) */
```

See [Debugging](doc#debugging) in the docs how you can extend or limit the amount of information this method returns.

Of course, it would be wise to output the value of both methods only when the global debug option of Kirby is set to `true`. Otherwise, you may confront your website visitors with strange and bizarre messages.

### Options

The plugin makes use of Kirby's caching mechanism. You can disable this by setting the key/value pair

```php
`adspectus.feedreader.cache` => false
```

in the `return` statement of your `config.php`. See [Caching](doc#caching) in the docs how caching works in this plugin.

## Extended Usage

This plugin comes with a blocks blueprint and a corresponding snippet to work with Kirby's panel. See [Extended Usage](doc#extended-usage) in the docs how you can use and adapt this blueprint and snippet.


## Contributing

The plugin has been tested with a variety of feeds. It is, however, almost impossible to take every variation in feed format into account for proper sourcing. If you encounter a feed with which this plugin is not working as intended, open an issue with the feed URL. Of course, you can also clone this repo and provide a fix.

## License

[GNU General Public License v3.0](LICENSE)

## Acknowledgements

* [texnixe](https://forum.getkirby.com/u/texnixe/) for code review and tips.
