<?php

namespace Baka\Support;

class Arr
{
    /**
     *Returns true if the provided function returns true for all elements of an array, false otherwise.
     *
     * @param array $items
     * @param mixed $func
     */
    public static function all(array $items, $func): bool
    {
        return \count(array_filter($items, $func)) === \count($items);
    }

    /**
     * Returns true if the provided function returns true for at least one element of an array, false otherwise.
     *
     * @param array $items
     * @param mixed $func
     */
    public static function any(array $items, $func): bool
    {
        return \count(array_filter($items, $func)) > 0;
    }

    /**
     * Chunks an array into smaller arrays of a specified size.
     *
     * @param array $items
     * @param int $size
     */
    public static function chunk(array $items, int $size): array
    {
        return array_chunk($items, $size);
    }

    /**
     * Deep flattens an array.
     *
     * @param array $items
     */
    public static function deepFlatten(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            if (!\is_array($item)) {
                $result[] = $item;
            } else {
                array_push($result, ...self::deepFlatten($item));
            }
        }

        return $result;
    }

    /**
     * Returns a new array with n elements removed from the left.
     *
     * @param array $items
     * @param int $n
     */
    public static function drop(array $items, int $n = 1): array
    {
        return \array_slice($items, $n);
    }

    /**
     * Returns the last element for which the provided function returns a truthy value.
     *
     * @param array $items
     * @param mixed $func
     *
     * @return mixed
     */
    public static function findLast(array $items, $func)
    {
        $filteredItems = array_filter($items, $func);

        return array_pop($filteredItems);
    }

    /**
     * Returns the index of the last element for which the provided function returns a truthy value.
     *
     * @param array $items
     * @param mixed $func
     */
    public static function findLastIndex(array $items, $func)
    {
        $keys = array_keys(array_filter($items, $func));

        return array_pop($keys);
    }

    /**
     * Flattens an array up to the one level depth.
     *
     * @param array $items
     */
    public static function flatten(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            if (!\is_array($item)) {
                $result[] = $item;
            } else {
                array_push($result, ...array_values($item));
            }
        }

        return $result;
    }

    /**
     * Groups the elements of an array based on the given function.
     *
     * @param array $items
     * @param mixed $func
     */
    public static function groupBy(array $items, $func): array
    {
        $group = [];
        foreach ($items as $item) {
            if ((!\is_string($func) && \is_callable($func)) || \function_exists($func)) {
                $key = \call_user_func($func, $item);
                $group[$key][] = $item;
            } elseif (\is_object($item)) {
                $group[$item->{$func}][] = $item;
            } elseif (isset($item[$func])) {
                $group[$item[$func]][] = $item;
            }
        }

        return $group;
    }

    /**
     * Sorts a collection of arrays or objects by key.
     *
     * @param array $items
     * @param mixed $attr
     * @param string $order
     *
     * @return array
     */
    public static function orderBy(array $items, $attr, string $order): array
    {
        $sortedItems = [];
        foreach ($items as $item) {
            $key = \is_object($item) ? $item->{$attr} : $item[$attr];
            $sortedItems[$key] = $item;
        }
        if ('desc' === $order) {
            krsort($sortedItems);
        } else {
            ksort($sortedItems);
        }

        return array_values($sortedItems);
    }

    /**
     * Checks a flat list for duplicate values. Returns true if duplicate values exists and false if values are all unique.
     *
     * @param array $items
     *
     * @return bool
     */
    public static function hasDuplicates(array $items): bool
    {
        return \count($items) > \count(array_unique($items));
    }

    /**
     * Returns the head of a list.
     *
     * @param array $items
     *
     * @return mixed
     */
    public static function head(array $items)
    {
        return reset($items);
    }

    /**
     * Returns the last element in an array.
     *
     * @param array $items
     *
     * @return mixed
     */
    public static function last(array $items)
    {
        return end($items);
    }

    /**
     * Retrieves all of the values for a given key:.
     *
     * @param array $items
     * @param mixed $key
     */
    public static function pluck(array $items, $key)
    {
        return array_map(function ($item) use ($key) {
            return \is_object($item) ? $item->$key : $item[$key];
        }, $items);
    }

    /**
     * Mutates the original array to filter out the values specified.
     *
     * @param array $items
     * @param mixed ...$params
     */
    public static function pull(&$items, ...$params)
    {
        $items = array_values(array_diff($items, $params));

        return $items;
    }

    /**
     * Filters the collection using the given callback.
     *
     * @param array $items
     * @param mixed $func
     */
    public static function reject(array $items, $func)
    {
        return array_values(array_diff($items, array_filter($items, $func)));
    }

    /**
     * Removes elements from an array for which the given function returns false.
     *
     * @param array $items
     * @param mixed $func
     */
    public static function remove(array $items, $func)
    {
        $filtered = array_filter($items, $func);

        return array_diff_key($items, $filtered);
    }

    /**
     * Returns all elements in an array except for the first one.
     *
     * @param array $items
     */
    public static function tail(array $items)
    {
        return \count($items) > 1 ? \array_slice($items, 1) : $items;
    }

    /**
     * Returns an array with n elements removed from the beginning.
     *
     * @param array $items
     * @param int $n
     */
    public static function take(array $items, int $n = 1): array
    {
        return \array_slice($items, 0, $n);
    }

    /**
     * Filters out the elements of an array, that have one of the specified values.
     *
     * @param array $items
     * @param mixed ...$params
     */
    public static function without(array $items, ...$params)
    {
        return array_values(array_diff($items, $params));
    }
}
