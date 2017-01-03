# Cache

PHPixie Cache library

[![Build Status](https://travis-ci.org/PHPixie/Cache.svg?branch=master)](https://travis-ci.org/PHPixie/Cache)
[![Author](http://img.shields.io/badge/author-@dracony-blue.svg?style=flat-square)](https://twitter.com/dracony)
[![Source Code](http://img.shields.io/badge/source-phpixie/cache-blue.svg?style=flat-square)](https://github.com/phpixie/cache)
[![Software License](https://img.shields.io/badge/license-BSD-brightgreen.svg?style=flat-square)](https://github.com/phpixie/cache/blob/master/LICENSE)

The PHPixie Cache component supports both PSR-6 and PSR-16 standards and also adds a few useful features.

## Initializing

If you are using the PHPixie framework the component is accessible via your builder and configured in `/assets/config/cache.php`.
 
```php
$cache = $builder->components->cache();
```

Like all the other PHPixie components you can use it without the framework, in that case initialize it like so:

```php
$slice = new \PHPixie\Slice();
$config = $slice->arrayData([
     // configuration
    'default' => [
         'driver' => 'memory'
    ]
]);


// Optional dependency if you want to use file cache.
// This defines the root folder for file based drivers.
$filesystem = new \PHPixie\Filesystem();
$root = $filesystem->root('/tmp/cache/');

$cache = new \PHPixie\Cache($config, $root);
```
 
## Configuration

Similarly to how you configure database connections, you can define multiple storage configurations using the available
drivers: *void*, *memory*, *phpfile*, *memcached* and *redis*:

```php
return [
    'default' => [
        // Doesn't store anything
        'driver' => 'void'
    ],

    'second' => [
        // Stores in a simple array
        'driver' => 'memory',

        /* Optional */

        /**
         * Default lifetime,
         * can be either number of seconds
         * or a DateInterval value, e.g. 'P1D' is one day.
         * Default is null, which is to store forever
         */
        'defaultExpiry' => 10,

        /**
         * A number between 1 and 1000 that
         * defines the frequency of garbage collection.
         * Defaults to 10, so 1% of all cache queries will
         * result in garbage collection to be run.
         */
        'cleanupProbability' => 10
    ],

    'third' => [
        // Values stored as .php files in folder
        'driver' => 'phpfile',

        // Relative to the /assets/cache folder
        'path'   => 'third',

        /* Optional */
        'defaultExpiry' => null,
        'cleanupProbability' => 10
    ],

    'fourth' => [
        'driver'  => 'memcached',

        /**
         * Same argument as to the Memcached::addServers() method,
         * but the port and weight parameters can be omitted and
         * default to 11211 and 1 respectively
         */
        'servers' => [
            ['127.0.0.1']
        ],

        /* Optional */
        'defaultExpiry' => null
    ],

    'fifth' => [
        'driver'  => 'redis',

        // Same argument as to the Predis\Client constructor
        'connection' => array(
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379
        ),

        /* Optional */
        'defaultExpiry' => null
    ]
];
```

## Usage

As mentioned PHPixie Cache supports both PSR-6 and the new simplified PSR-16. You will mostly use instances of 
the `PHPixie\Cache\Pool` interface:
 
```php
namespace PHPixie\Cache;

use PHPixie\Cache\Pool\Prefixed;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

// Inherits both PSR-6 and PSR-16
interface Pool extends CacheItemPoolInterface, CacheInterface
{
    /**
     * Creates a PSR-6 Item instance without trying to retrieve it from cache
     * @param string $key
     * @param mixed $value
     * @return Item
     */
    public function createItem($key, $value = null);

    /**
     * Creates a namespaced prefix pool.
     * We'll cover this later.
     * @param string $prefix
     * @return Prefixed
     */
    public function prefixedPool($prefix);
}
```

Some examples:

```php
// Getting one of the defined storages
$storage = $cache->storage('second');

// PSR-6
public function getFairies()
{
    $item = $this->storage->getItem('fairies');
    if (!$item->isHit()) {
        $fairies = $this->generateFairies();
        $item->set($fairies);
        $item->expiresAfter(100);
        $this->storage->save($item);
    }
    return $item->get();
}

// PSR-16
public function getFairies()
{
    $fairies = $this->storage->get('fairies');
    if($fairies === null) {
         $fairies = $this->buildFairies();
         $this->storage->set('fairies', $fairies, 100);
    }
    return $fairies;
}
```

There's no point to rewrite here all the usage examples for these PSRs which already have great documentation.
The additional methods like `delete`, `clear`, etc. are very intuitive easy to find in PSR docs and by using IDE hints.
Let's instead focus on some unique features.

## Prefixed pools

When multiple parts of the application use the same cash storage a common practice is to prefix keys. How often have you
seen code like this:

```php
$key = 'article-'.$id;
$article = $cache->get($key);
```

If these entities are cached in different parts of the application you have to make sure you are always using the same
prefix or even abstract this logic into a separate service. PHPixie Cache solves this problem using a prefixed pools that
proxy requests to the storage automatically prefixing the keys:

```php
$storage = $cache->storage('default');
$articlesPool = $storage->prefixedPool('article');

$articlesPool->set($article->id, $article->html());

// same as
$storage ->set('article.'.$article->id, $article->html());
```

As you probably guessed such pools also implement the same `PHPixie\Cache\Pool` interface as the storages and can themselves
be prefixed thus creating a hierarchy. They can be used to define different pools for different entities and also
make it easy to later switch some of them to actual separate storages if needed. For example you can start out with
one cache storage and multiple prefixed pools and expand easily when your application grows.

## Simple usage without storages

The `PHPixie\Cache` class itself also implements the `Pool` interface and will proxy requests to the default cache storage.
This makes the component easier to use in application with only one cache storage:

```php
// instead of
$cache->storage('default')->get('fairy');

// just use
$cache->get('fairy');
```

## Not hashing the keys

Most filesystem caches hash the key to generate the appropriate file name, this is done to protect you from using keys that
contain characters not supported by the filesystem. In reality most people use alphanumeric cache keys anyway and hashing
these provides no real value but makes cache files harder to inspect and makes a slight impact on the application performance.
PHPixie Cache uses raw keys for filenames, although hashing can easily be introduced if required.

## Optimized file cache

Most cache libraries serialize the value together with the expiry timestamp to store it in a file cache. This approach has
two drawbacks: everytime you read the value you have to deserialize it which impacts performance when used frequently, also
to just check if the cache has not yet expired you have to deserialize the entire file, which is wasteful if the actual
value is not used afterwards. So how does PHPixie Cache solve these? Let's look at an example of a cached file:
 
```php
<?php /*1483041355*/
return array(1,2,3);
```

The first line contains a comment with the expiry timestamp, which can be checked simply by reading this one line
ignoring the rest of the file. Also the cache is stored as PHP code which is retrieved using the `include` operator.
This means that your opcache will cache it and not actually read the file from disk every time it is requested. This
approach is especially great for usecases with singular writes and frequent reads like configuration cache etc. and
can actually outperform other storages.

## Contributing and adding drivers

Since PHPixie's social component got so much help from the community with adding new providers I decided to add a small
guide on how to contribute and add your own storage driver to PHPixie Cache.

1. Add a class `PHPixie\Cache\Drivers\Type\YourDriver` extending from `PHPixie\Cache\Drivers\Driver`.
2. Register it in `PHPixie\Cache\Builder::$driverMap`.
3. Create a test class `PHPixie\Tests\Cache\Driver\YourDriverTest` extending from `PHPixie\Tests\Cache\DriverTest`
4. Edit `.travis.yml` and `composer.json` to add any necessary dependencies and packages so that your tests runs on Travis CI.
5. Send in the Pull Request ;)

And of course our chatroom will be happy to help with any problems you encounter.