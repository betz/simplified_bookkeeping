<?php
namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class Dashboard extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function page() {
    $element = array(
      '#markup' => 'Hello, world',
      '#attached' => [
        'library' => [
          'simplified_bookkeeping/simplified_bookkeeping',
        ]
      ],
    );
    return $element;
  }

}