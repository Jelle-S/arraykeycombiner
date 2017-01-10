<?php

namespace Jelle_S\Test\Util\Combiner;

use PHPUnit\Framework\TestCase;
use Jelle_S\Util\Combiner\ArrayKeyCombiner;

/**
 * Tests \Jelle_S\Util\Combiner\ArrayKeyCombiner.
 *
 * @author Jelle Sebreghts
 */
class ArrayKeyCombinerTest extends TestCase {

  /**
   * Test combine delimiter.
   */
  public function testDelimiter() {
    $arrays = array(
      'a' => array('a' => 1),
      'b' => array('a' => 1),
    );
    $combiner = new ArrayKeyCombiner($arrays, 3, 10000, ':');
    $result = $combiner->combine();
    $this->assertEquals(array('a:b' => array('a' => 1)), $result);
  }

  /**
   * Test combine threshold.
   */
  public function testThreshold() {
    $arrays = $this->getArrays();
    foreach (array(3, 4, 5) as $threshold) {
      $combiner = new ArrayKeyCombiner($arrays, $threshold, 10000);
      $result = $combiner->combine();
      $this->assertTrue($this->hasValidThresholds($result, $threshold));
    }
  }

  /**
   * Test that identicals are combined regardless of threshold.
   */
  public function testIdenticalsAreCombined() {
    $arrays = $this->getArrays();
    foreach (array(3, 4, 5) as $threshold) {
      $combiner = new ArrayKeyCombiner($arrays, $threshold, 10000);
      $result = $combiner->combine();
      $this->assertTrue(isset($result['identical1,identical2']));
      $this->assertEquals(array('abc' => 123), $result['identical1,identical2']);
    }
  }

  protected function hasValidThresholds($arrays, $threshold) {
    $combinedKeys = array_filter(array_keys($arrays, function($key) {
      return strpos($key, ',') !== FALSE;
    }));
    foreach ($combinedKeys as $combinedKey) {
      $keys = explode(',', $combinedKey);
      foreach ($keys as $key) {
        // There are leftovers for this array, so this is not a combination of
        // identical arrays, which means the threshold must be respected.
        if (isset($arrays[$key])) {
          if (!count($arrays[$combinedKey]) >= $threshold) {
            return FALSE;
          }
        }
      }
    }
    return TRUE;
  }

  protected function getArrays() {
    return array(
      0 => array(
        'a' => 1,
        'b' => 2,
        'c' => 3,
        'd' => 4,
        'e' => 9,
        'f' => 10,
      ),
      1 => array(
        'a' => 1,
        'b' => 2,
        'c' => 3,
        'd' => 4,
        'e' => 9,
        'g' => 17,
      ),
      2 => array(
        'a' => 1,
        'b' => 42,
        'c' => 3,
        'd' => 4,
      ),
      3 => array(
        'b' => 42,
        'c' => 3,
        'a' => 1,
      ),
      4 => array(
        'z' => 26,
        'e' => 9,
        'a' => 1,
      ),
      'identical1' => array(
        'abc' => 123,
      ),
      'identical2' => array(
        'abc' => 123,
      )
    );
  }

}
