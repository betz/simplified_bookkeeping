<?php

namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Core\Controller\ControllerBase;


/**
 * An example controller.
 */
class ExportOut extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content() {

    $build = array(
      '#type' => 'markup',
      '#markup' => t('Hello World!'),
    );
    return $build;
  }

}