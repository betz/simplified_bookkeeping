<?php

/**
 * @file
 * Contains \Drupal\simplified_bookkeeping\ServiceBankstatements.
 */

namespace Drupal\service_example;

class ServiceBankstatements {

  protected $service_example_value;

  /**
   * When the service is created, set a value for the example variable.
   */
  public function __construct() {
    $this->service_example_value = 'Hello world!';
  }

  /**
   * Return the value of the example variable.
   */
  public function getServiceExampleValue() {
    return $this->service_example_value;
  }

}