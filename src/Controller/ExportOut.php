<?php

namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;


/**
 * An example controller.
 */
class ExportOut extends ControllerBase {

  public function content() {

    $start_date = new DrupalDateTime('1 january 2016 00:00:00');
    $start_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
    $start_date_storage_format = $start_date->format(DATETIME_DATETIME_STORAGE_FORMAT);

    $end_date = new DrupalDateTime('31 december 2016 23:11:59');
    $end_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
    $end_date_storage_format = $end_date->format(DATETIME_DATETIME_STORAGE_FORMAT);

    $query = \Drupal::entityQuery('booking');
    $query->condition('status', 1);
    $query->condition('type', 'purchase');
    $query->condition('field_purchase_date', $start_date_storage_format, '>=');
    $query->sort('field_bankstatement_date', 'ASC');

    $entity_ids = $query->execute();
    $bookings = entity_load_multiple('booking', $entity_ids);
    $nr = 1; $rows = [];

    $header = [
      'Nr',
      'Date',
      'Description',
      'Banknr',
      'Bank amount',
      'Cashnr',
      'Cash amount',
      'Total'
    ];

    foreach ($bookings as $booking) {

      $statement = current($booking->get('field_booking')->referencedEntities());
      $statement_bundle = $statement->bundle();

      if($statement_bundle == 'bankstatement') {
        $bank_amount = $statement->field_booking_amount->value . '€';
        $cash_amount = '';
        $bank_total = $bank_total + $statement->field_booking_amount->value;
      }

      if($statement_bundle == 'cashstatement') {
        $cash_amount = $statement->field_booking_amount->value . '€';
        $bank_amount = '';
        $cash_total = $cash_total + $statement->field_booking_amount->value;
      }


      $booking_date = new DrupalDateTime($booking->field_purchase_date->value);
      $booking_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
      $booking_date = $booking_date->format(DATETIME_DATETIME_STORAGE_FORMAT);

      $amount = round($booking->field_purchase_total_amount->value, 2);

      $rows[] = [
        $nr,
        $booking->field_purchase_date->value,
        $booking->label(),
        '',
        $bank_amount,
        '',
        $cash_amount,
        $amount . '€'
      ];

      $amount_total = $amount_total + $amount;
      $nr++;
    }


    $rows[] = [
      '',
      '',
      '',
      '',
      $bank_total . '€',
      '',
      $cash_total . '€',
      $amount_total . '€'
    ];


    $build = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#footer' => $footer
    ];

    return $build;

  }

}