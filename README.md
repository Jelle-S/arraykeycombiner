# ArrayKeyCombiner

[![Build Status](https://travis-ci.org/Jelle-S/arraykeycombiner.svg?branch=develop)](https://travis-ci.org/Jelle-S/arraykeycombiner) [![Code Climate](https://codeclimate.com/github/Jelle-S/arraykeycombiner/badges/gpa.svg)](https://codeclimate.com/github/Jelle-S/arraykeycombiner) [![Test Coverage](https://codeclimate.com/github/Jelle-S/arraykeycombiner/badges/coverage.svg)](https://codeclimate.com/github/Jelle-S/arraykeycombiner/coverage) [![Issue Count](https://codeclimate.com/github/Jelle-S/arraykeycombiner/badges/issue_count.svg)](https://codeclimate.com/github/Jelle-S/arraykeycombiner)

Combines arrays by searching for intersections and adding them to the master array. Keys are combined using a delimiter.

```php
use Jelle_S\Util\Combiner\ArrayKeyCombiner;

// Search this array of arrays for intersections and extract them, using a
// delimiter to combine the keys. Limit the number of iterations to search for
// intersections to 10.000, limit the minimum size of intersections to 3, set
// the key delimiter to a comma.
$arrays = array(
 array(
   'a' => 1,
   'b' => 2,
   'c' => 3,
   'd' => 4,
   'e' => 9,
 ),
 array(
   'a' => 1,
   'b' => 2,
   'c' => 3,
   'e' => 9,
 ),
 array(
   'a' => 1,
   'b' => 42,
   'c' => 3,
   'd' => 4,

 ),
 array(
   'b' => 42,
   'c' => 3,
   'a' => 1,
 ),
 array(
   'z' => 26,
   'e' => 9,
   'a' => 1,
 ),
);
$combiner = new Jelle_S\Util\Combiner\ArrayKeyCombiner($arrays, 3, 10000, ',');
print_r($combiner->combine());
```

Output:
```
Array
(
    [4] => Array
        (
            [a] => 1
            [e] => 9
            [z] => 26
        )

    [0,1] => Array
        (
            [a] => 1
            [b] => 2
            [c] => 3
            [e] => 9
        )

    [2,3] => Array
        (
            [a] => 1
            [c] => 3
            [b] => 42
        )

    [2,0] => Array
        (
            [d] => 4
        )

)
```

The 'a' and 'z' keys of the array with key '4' were not combined because the threshold for combinations is 3, and combining them would result in a combined array with only two elements.

The arrays with keys '2' and '0' **were** combined because after extracting the combinations, these two arrays were identical, and the threshold has no effect for **identical** arrays, they are always combined.
