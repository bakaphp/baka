[![Build Status](https://travis-ci.com/bakaphp/support.svg?branch=1.1.0)](https://travis-ci.com/bakaphp/support) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bakaphp/support/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bakaphp/support/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/bakaphp/support/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bakaphp/support/?branch=master) [![Latest Stable Version](https://poser.pugx.org/baka/support/v/stable)](https://packagist.org/packages/baka/support) [![composer.lock](https://poser.pugx.org/baka/support/composerlock)](https://packagist.org/packages/baka/support) [![Total Downloads](https://poser.pugx.org/baka/support/downloads)](https://packagist.org/packages/baka/support) [![License](https://poser.pugx.org/baka/support/license)](https://packagist.org/packages/baka/support)

# Baka Support

A curated collection of useful PHP snippets.

## Requirements

This package requires PHP 7.2 or higher.

## Installation

You can install the package via composer:

``` bash
composer require baka/support
```

The package will automatically register itself.

---
 ## 📚 Array

### all
Returns `true` if the provided function returns `true` for all elements of an array, `false` otherwise.

```php
all([2, 3, 4, 5], function ($item) {
    return $item > 1;
}); // true
```

### any
Returns `true` if the provided function returns `true` for at least one element of an array, `false` otherwise.

```php
any([1, 2, 3, 4], function ($item) {
    return $item < 2;
}); // true
```

### deepFlatten
Deep flattens an array.

```php
deepFlatten([1, [2], [[3], 4], 5]); // [1, 2, 3, 4, 5]
```

### drop
Returns a new array with `n` elements removed from the left.

```php
drop([1, 2, 3]); // [2,3]
drop([1, 2, 3], 2); // [3]
```

### findLast
Returns the last element for which the provided function returns a truthy value.

```php
findLast([1, 2, 3, 4], function ($n) {
    return ($n % 2) === 1;
});
// 3
```

### findLastIndex
Returns the index of the last element for which the provided function returns a truthy value.

```php
findLastIndex([1, 2, 3, 4], function ($n) {
    return ($n % 2) === 1;
});
// 2
```

### flatten
Flattens an array up to the one level depth.

```php
flatten([1, [2], 3, 4]); // [1, 2, 3, 4]
```

### groupBy
Groups the elements of an array based on the given function.

```php
groupBy(['one', 'two', 'three'], 'strlen'); // [3 => ['one', 'two'], 5 => ['three']]
```

### hasDuplicates
Checks a flat list for duplicate values. Returns `true` if duplicate values exists and `false` if values are all unique.

```php
hasDuplicates([1, 2, 3, 4, 5, 5]); // true
```

### head
Returns the head of a list.

```php
head([1, 2, 3]); // 1
```

### last
Returns the last element in an array.

```php
last([1, 2, 3]); // 3
```

### pluck
Retrieves all of the values for a given key:

```php
pluck([
    ['product_id' => 'prod-100', 'name' => 'Desk'],
    ['product_id' => 'prod-200', 'name' => 'Chair'],
], 'name');
// ['Desk', 'Chair']
```

### pull
Mutates the original array to filter out the values specified.

```php
$items = ['a', 'b', 'c', 'a', 'b', 'c'];
pull($items, 'a', 'c'); // $items will be ['b', 'b']
```

### reject
Filters the collection using the given callback.

```php
reject(['Apple', 'Pear', 'Kiwi', 'Banana'], function ($item) {
    return strlen($item) > 4;
}); // ['Pear', 'Kiwi']
```

### remove
Removes elements from an array for which the given function returns false.

```php
remove([1, 2, 3, 4], function ($n) {
    return ($n % 2) === 0;
});
// [0 => 1, 2 => 3]
```

### tail
Returns all elements in an array except for the first one.

```php
tail([1, 2, 3]); // [2, 3]
```

### take
Returns an array with n elements removed from the beginning.

```php
take([1, 2, 3], 5); // [1, 2, 3]
take([1, 2, 3, 4, 5], 2); // [1, 2]
```

### without
Filters out the elements of an array, that have one of the specified values.

```php
without([2, 1, 2, 3], 1, 2); // [3]
```

### orderBy

Sorts a collection of arrays or objects by key.

```php
orderBy(
    [
        ['id' => 2, 'name' => 'Joy'],
        ['id' => 3, 'name' => 'Khaja'],
        ['id' => 1, 'name' => 'Raja']
    ],
    'id',
    'desc'
); // [['id' => 3, 'name' => 'Khaja'], ['id' => 2, 'name' => 'Joy'], ['id' => 1, 'name' => 'Raja']]
```

---
 ## 📜 String

### endsWith

Check if a string is ends with a given substring.

```php
endsWith('Hi, this is me', 'me'); // true
```

### firstStringBetween

Returns the first string there is between the strings from the parameter start and end.

```

```php
firstStringBetween('This is a [custom] string', '[', ']'); // custom
```

### isAnagram

Compare two strings and returns `true` if both strings are anagram, `false` otherwise.

```php
isAnagram('act', 'cat'); // true
```

### isLowerCase

Returns `true` if the given string is lower case, `false` otherwise.

```php
isLowerCase('Morning shows the day!'); // false
isLowerCase('hello'); // true
```

### isUpperCase

Returns `true` if the given string is upper case, false otherwise.

```php
isUpperCase('MORNING SHOWS THE DAY!'); // true
isUpperCase('qUick Fox'); // false
```

### palindrome

Returns `true` if the given string is a palindrome, `false` otherwise.

```php
palindrome('racecar'); // true
palindrome(2221222); // true
```

### startsWith

Check if a string starts with a given substring.

```php
startsWith('Hi, this is me', 'Hi'); // true
```

### countVowels

Returns number of vowels in provided string.

Use a regular expression to count the number of vowels (A, E, I, O, U) in a string.

```php
countVowels('sampleInput'); // 4
```

### decapitalize

Decapitalizes the first letter of a string.

Decapitalizes the first letter of the string and then adds it with rest of the string. Omit the ```upperRest``` parameter to keep the rest of the string intact, or set it to ```true``` to convert to uppercase.

```php
decapitalize('FooBar'); // 'fooBar'
```

### contains

Check if a word / substring exist in a given string input.
Using `strpos` to find the position of the first occurrence of a substring in a string. Returns either `true` or `false`

```php
contains('This is an example string', 'example'); // true
```

```php
contains('This is an example string', 'hello'); // false
```

## License

This project is licensed under the MIT License - see the [License File](LICENSE) for details

---
### Thanks
Thanks to [appzcoder](https://github.com/appzcoder) for the snippets!
