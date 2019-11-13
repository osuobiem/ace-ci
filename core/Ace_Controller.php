<?php


/**
 * Ace_Controller Class
 * 
 * Controller layer to house needed libraries,
 * helpers, and custom functions to be used
 * by child controllers.
 * 
 * @package Ace-CI
 * @author  Gabriel Osuobiem <osuobiem@gmail.com>
 * @link https://github.com/osuobiem
 * @link https://www.linkedin.com/in/gabriel-osuobiem-b22577176/
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Ace_Controller extends CI_Controller {

  public function __construct()
  {
    parent::__construct();
    
    // Load libraries and helpers here
  }
  

  /**
   * Data Dump
   * 
	 * Dump variable data and kill script
   * 
	 * @param mixed $var  Variable to dump
   * 
   * @return void
	 */
	public static function dd($var) {
		var_dump($var); die;
	}

  /**
   * Hash String
   * 
   * Hash a supplied string using the hash()
   * function and a salt specified in
   * config/config.php
   * 
   * @param string $string  String to be hashed
   * 
   * @return string   Hashed string
   */
  public function hash($string) {
		return hash("sha512", $string . config_item("encryption_key"));
  }

  /**
   * Session Checker
   * 
   * Check for the existence of a session
   * and redirect to a specified url if
   * the check test fails.
   * 
   * @param string $session           Session key to check for
   * @param string $redirect_url      Redirect url if the check fails
   * @param bool (optional) $reverse  If session exist then redirect
   * 
   * @return void
   */
  public function sech($session, $redirect_url, $reverse = false) {
    $redirect_url = base_url($redirect_url);

    $check = isset($_SESSION[$session]);
    
    if(!$check && !$reverse) {
      redirect($redirect_url);
    }
    elseif($check && $reverse) {
      redirect($redirect_url);
    }
  }
}

/* End of file Ace_Controller.php */
