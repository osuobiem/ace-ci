<?php

/**
 * Random Class
 * 
 * Library to generate random strings and numbers
 * The maximum length is 48 characters
 * 
 * @package Ace-CI
 * @author  Gabriel Osuobiem <osuobiem@gmail.com>
 * @link https://github.com/osuobiem
 * @link https://www.linkedin.com/in/gabriel-osuobiem-b22577176/
 */

class Random {
  
  // String of capital English letters
  private static $caps = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

  // String of small English letters
  private static $small = 'abcdefghijklmnopqrstuvwxyz';

  // String of numbers from 0-9
  private static $num = '0123456789';

  /**
   * Verify Request
   * 
   * Verify if supplied length is less than 10
   * and if the type {caps, small, num, or mix}
   * is supplied. 
   * 
   * @param int $length   Length of random string required
   * @param string $type  Type of string to generate
   * 
   * @return true|false
   */
  private static function verifyRequest($length, $type) {
    return ($length < 10 && !$type) ? false : true;
  }

  /**
   * Compose Random String
   * 
   * Generate random string given a specific
   * type and length
   * 
   * @param string (optional) $type  Type of string to generate
   * @param int (optional) $length   Length of random string required
   * 
   * @return string   Randomly generated string
   */
  private static function compose($type = 'mix', $length = 30) {
    if($type == 'mix') {
      $shuffle = str_shuffle(Random::$caps.Random::$small.Random::$num);
      $result = substr($shuffle, 0, $length);
    }
    else {
      $shuffle = str_shuffle(Random::${$type});
      $result = substr($shuffle, 0, $length);
    }

    return $result;
  }

  /**
   * Generate Random String
   * 
   * Get results from compose() and shorten it
   * according to the user's apecified length (if any).
   * Add timestamp to make it unique
   * 
   * @param int (optional) $length   Length of random string required
   * @param string (optional) $type  Type of string to generate
   * 
   * @return string   Randomly generated string or Error message
   */
  public static function generate($length = 30, $type = false) {
    
    if(Random::verifyRequest($length, $type)) {
      $result = '';
      if(!$type) {
        if($length % 4 != 0) {
          $shuffle = Random::compose();
          
          $result = str_shuffle(substr($shuffle, 0, $length-5).substr(Date('AdYAdYmHsimHsi'), 0, 5));
        }
        else {
          $caps = Random::compose('caps', $length/4);
          $small = Random::compose('small', $length/4);
          $num = Random::compose('num', $length/4);
          
          $result = str_shuffle($caps.$num.substr(Date('AdYAdYmHsimHsi'), 0, $length/4).$small);
        }
      }
      else {
        $result = Random::compose($type, $length);
      }

      return $result;
    }
    else {
      return 'Length should be at least 10 characters';
    }
  }

}

/* End of file Random.php */