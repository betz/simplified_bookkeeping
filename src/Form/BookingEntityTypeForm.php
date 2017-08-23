<?php

namespace Drupal\simplified_bookkeeping\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BookingEntityTypeForm.
 */
class BookingEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $booking_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $booking_type->label(),
      '#description' => $this->t("Label for the Booking type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $booking_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\simplified_bookkeeping\Entity\BookingEntityType::load',
      ],
      '#disabled' => !$booking_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $booking_type = $this->entity;
    $status = $booking_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Booking type.', [
          '%label' => $booking_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Booking type.', [
          '%label' => $booking_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($booking_type->toUrl('collection'));
  }

}
