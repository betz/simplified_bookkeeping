<?php

namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * An example controller.
 */
class PhantomjsPDFController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content() {

    $url = Url::fromUri('http://local.dashboard.hsbxl.be/bookkeeping/sales');
    $destination = '/var/www/drupalvm/web/sites/default/files/phantomjs/ohoh';
    $filename = 'test.png';

    //$pdf = \Drupal::service('phantomjs_capture.helper')
      //->capture($url, $destination, $filename, $element = NULL);

    $build = array(
      '#type' => 'markup',
      '#markup' => t('Hello World!'),
    );
    return $build;
  }

}