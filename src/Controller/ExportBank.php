<?php

namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\simplified_bookkeeping\Controller\BookingEntityController;
use Drupal\simplified_bookkeeping\Entity\BookingEntity;


/**
 * An example controller.
 */
class ExportBank extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content() {

    $query = \Drupal::entityQuery('booking');
    $query->condition('status', 1);
    $query->condition('type', 'bankstatement');

    $entity_ids = $query->execute();
    $bookings = entity_load_multiple('booking', $entity_ids);

    foreach ($bookings as $booking) {
      $view = entity_view($booking, 'full');
      $output[] = \Drupal::service('renderer')->renderRoot($view);
    }

    $build = array(
      '#type' => 'markup',
      '#markup' => implode('', $output),
    );
    return $build;
  }

}