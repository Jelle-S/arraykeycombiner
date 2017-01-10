<?php

namespace Jelle_S\Util\Combiner;

use Jelle_S\Util\Intersections\ArrayIntersections;

/**
 * Combines arrays by searching for intersections and adding them to the master
 * array. Keys are combined using a delimiter.
 *
 * @author Jelle Sebreghts
 */
class ArrayKeyCombiner {

  /**
   * The master array (an array of arrays to combine).
   *
   * @var array
   */
  protected $arrays;

  /**
   * Helper array. Arrays from the master array are grouped by their length.
   *
   * @var array
   */
  protected $groupedArrays = [];

  /**
   * The threshold. The minimum size of the intersections to search for.
   *
   * @var int
   */
  protected $threshold;

  /**
   * The maximum amount of iterations to do while searching for intersections.
   *
   * @var int
   */
  protected $intersectionIterationsLimit;

  /**
   * The delimiter to use when combining the array keys.
   *
   * @var string
   */
  protected $keyDelimiter;

  /**
   * Creates an ArrayCombiner.
   *
   * @param array $arrays
   *   The master array (an array of arrays to combine).
   * @param int $threshold
   *   The threshold. The minimum size of the intersections to search for.
   * @param int $intersectionIterationsLimit
   *   The maximum amount of iterations to do while searching for intersections.
   * @param string $keyDelimiter
   *   The delimiter to use when combining the array keys.
   */
  public function __construct($arrays, $threshold, $intersectionIterationsLimit, $keyDelimiter = ',') {
    $this->arrays = $arrays;
    $this->threshold = $threshold;
    foreach ($this->arrays as $key => $array) {
      $size = count($array);
      $this->groupedArrays[$size][$key] = $array;
    }
    ksort($this->groupedArrays);
    $this->intersectionIterationsLimit = $intersectionIterationsLimit;
    $this->keyDelimiter = $keyDelimiter;
  }

  /**
   * Combine the arrays.
   *
   * @return array
   *   The altered master array with the combined arrays added.
   */
  public function combine() {
    foreach (array_keys($this->groupedArrays) as $size) {
      $this->combineLevel($size);
    }
    $masterArray = [];
    $leftovers = [];
    foreach ($this->groupedArrays as $size => $arrays) {
      if ($size >= $this->threshold) {
        $masterArray += $arrays;
        continue;
      }
      $leftovers += $arrays;
    }
    return $this->combineIdentical($this->combineArrays($masterArray) + $leftovers);
  }

  /**
   * Combine arrays of the same size.
   *
   * @param int $level
   *   The size of the arrays that are combined.
   */
  protected function combineLevel($level) {
    $combined = $this->combineArrays($this->groupedArrays[$level]);
    unset($this->groupedArrays[$level]);
    foreach ($combined as $key => $combo) {
      $this->groupedArrays[count($combo)][$key] = $combo;
    }
  }

  /**
   * Combine the arrays.
   *
   * @param array $arrays
   *   An array of arrays to combine.
   *
   * @return array
   *   The altered master array with the combined arrays added.
   */
  protected function combineArrays($arrays) {
    if (count($arrays) <= 1) {
      return $arrays;
    }
    do {
      $i = new ArrayIntersections($arrays, $this->threshold, $this->intersectionIterationsLimit);
      $intersections = $i->getAll();
      if (!$intersections) {
        break;
      }
      $changed = $this->combineIntersections($arrays, $intersections);
    } while ($changed && count($arrays) > 1);

    return $this->combineIdentical($arrays);
  }

  /**
   * Combines intersections of arrays into the arrays.
   *
   * @param array $arrays
   *   The arrays of which the intersections were found.
   * @param array $intersections
   *   The intersections that were found.
   *
   * @return bool
   *   Whether or not the arrays have changed.
   */
  protected function combineIntersections(&$arrays, $intersections) {
    $changed = FALSE;
    foreach ($intersections as $intersection) {
      $size = count($intersection);
      $keys = [];
      foreach ($arrays as $key => $arr) {
        if (count(array_intersect_assoc($arr, $intersection)) !== $size) {
          continue;
        }
        $arrays[$key] = array_diff_key($arr, $intersection);
        if (empty($arrays[$key])) {
          unset($arrays[$key]);
        }
        $keys[] = $key;
      }
      if ($keys) {
        sort($keys);
        $arrays[implode($this->keyDelimiter, array_unique($keys))] = $intersection;
        $changed = TRUE;
      }
    }
    return $changed;
  }

  /**
   * Combines identical arrays.
   *
   * @param array $arrays
   *   An array of arrays to combine.
   *
   * @return array
   *   The altered master array with the combined arrays added.
   */
  protected function combineIdentical($arrays) {
    // Check for identical items in the array and merge them.
    foreach ($arrays as &$sort) {
      asort($sort);
    }
    $serialized = array_map('serialize', $arrays);
    $unique = array_intersect_key($arrays, array_unique($serialized));
    if (count($unique) !== count($arrays)) {
      $keys = array_diff_key($arrays, $unique);
      foreach ($keys as $arr) {
        $keysToMerge = [];
        $serializedVal = serialize($arr);
        while (($keyToMerge = array_search($serializedVal, $serialized)) !== FALSE) {
          $keysToMerge[] = $keyToMerge;
          unset($serialized[$keyToMerge]);
        }
        if (count($keysToMerge) > 1) {
          foreach ($keysToMerge as $keyToMerge) {
            unset($arrays[$keyToMerge]);
            $arrays[implode($this->keyDelimiter, $keysToMerge)] = $arr;
          }
        }
      }
    }
    return $arrays;
  }

}
