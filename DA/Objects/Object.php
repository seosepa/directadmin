<?php
/**
 * DA_DirectAdmin
 * (c) Omines Internetbureau B.V.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Basic wrapper around a DA_DirectAdmin object as observed within a specific context.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
abstract class DA_Objects_Object
{
    /** @var string */
    private $name;

    /** @var DA_Context_UserContext */
    private $context;

    /** @var array */
    private $cache = [];

    /**
     * @param string $name Canonical name for the object.
     * @param DA_Context_UserContext $context Context within which the object is valid.
     */
    protected function __construct($name, DA_Context_UserContext $context)
    {
        $this->name = $name;
        $this->context = $context;
    }

    /**
     * Clear the object's internal cache.
     */
    public function clearCache()
    {
        $this->cache = [];
    }

    /**
     * Retrieves an item from the internal cache.
     *
     * @param string $key Key to retrieve.
     * @param Callable|mixed $default Either a callback or an explicit default value.
     * @return mixed Cached value.
     */
    protected function getCache($key, $default)
    {
        if(!isset($this->cache[$key]))
            $this->cache[$key] = is_callable($default) ? $default() : $default;
        return $this->cache[$key];
    }

    /**
     * Retrieves a keyed item from inside a cache item.
     *
     * @param string $key
     * @param string $item
     * @param Callable|mixed $defaultKey
     * @param mixed|null $defaultItem
     * @return mixed Cached value.
     */
    protected function getCacheItem($key, $item, $defaultKey, $defaultItem = null)
    {
        $cache = $this->getCache($key, $defaultKey);
        if(empty($cache))
            return $defaultItem;
        if(!is_array($cache))
            throw new DA_DirectAdminException("Cache item $key is not an array");
        return isset($cache[$item]) ? $cache[$item] : $defaultItem;
    }

    /**
     * Sets a specific cache item, for when a cacheable value was a by-product.
     *
     * @param string $key
     * @param mixed $value
     */
    protected function setCache($key, $value)
    {
        $this->cache[$key] = $value;
    }

    /**
     * @return DA_Context_UserContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Protected as a derived class may want to offer the name under a different name.
     *
     * @return string
     */
    protected function getName()
    {
        return $this->name;
    }

    /**
     * Converts an array of string items to an associative array of objects of the specified type.
     *
     * @param array $items
     * @param string $class
     * @param DA_Context_UserContext $context
     * @return array
     */
    public static function toObjectArray(array $items, $class, DA_Context_UserContext $context)
    {
        return array_combine($items, array_map(function($item) use ($class, $context) {
            return new $class($item, $context);
        }, $items));
    }

    /**
     * Converts an associative array of descriptors to objects of the specified type.
     *
     * @param array $items
     * @param string $class
     * @param DA_Context_UserContext $context
     * @return array
     */
    public static function toRichObjectArray(array $items, $class, DA_Context_UserContext $context)
    {
        array_walk($items, function(&$value, $name) use ($class, $context) {
            $value = new $class($name, $context, $value);
        });
        return $items;
    }
}
