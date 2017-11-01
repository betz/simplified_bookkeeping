<?php

namespace Drupal\simplified_bookkeeping\Plugin\DsField;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Plugin that renders the terms inside a chosen taxonomy vocabulary.
 *
 * @DsField(
 *   id = "edit_booking",
 *   title = @Translation("Edit Booking"),
 *   entity_type = "booking",
 *   provider = "simplified_bookkeeping",
 *   ui_limit = {"sale|*"}
 * )
 */
class EditBooking extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $booking = $this->entity();

    return array(
      '#theme' => 'item_list',
      '#items' => array('hello'),
    );
  }

}