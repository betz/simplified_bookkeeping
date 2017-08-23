<?php

namespace Drupal\simplified_bookkeeping\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Booking entities.
 */
class BookingEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
