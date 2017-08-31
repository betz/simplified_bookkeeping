<?php

namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\simplified_bookkeeping\ServiceBankstatements;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\simplified_bookkeeping\Controller\BookingEntityController;
use Drupal\simplified_bookkeeping\Entity\BookingEntity;


/**
 * An example controller.
 */
class ExportBank extends ControllerBase {

  /*public function __construct(ServiceBankstatements $ServiceBankstatements) {
    $this->ServiceBankstatements = $ServiceBankstatements;
  }*/

  /*public static function create(ServiceBankstatements $ServiceBankstatements) {
    return new static($container->get('simplified_bookkeeping.bankstatements'));
  }*/

  /**
   * {@inheritdoc}
   */
  public function content() {

    $start_date = new DrupalDateTime('1 january 2000 00:00:00');
    $start_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
    $start_date_storage_format = $start_date->format(DATETIME_DATETIME_STORAGE_FORMAT);

    $end_date = new DrupalDateTime('31 december 2017 23:11:59');
    $end_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
    $end_date_storage_format = $end_date->format(DATETIME_DATETIME_STORAGE_FORMAT);


    $query = \Drupal::entityQuery('booking');
    //$query->condition('field_bankstatement_date', $startdate, '>=');
    $query->condition('status', 1);
    $query->condition('type', 'bankstatement');
    $query->sort('field_bankstatement_date', 'ASC');

    $entity_ids = $query->execute();
    $bookings = entity_load_multiple('booking', $entity_ids);
    $nr = 1;

    $header = [
      'Nr',
      'Date',
      'Description',
      'Invoice Nr',
      'Incoming',
      'Outgoing',
      'Total'
    ];

    $startrow = TRUE;
    foreach ($bookings as $booking) {

      $booking_date = new DrupalDateTime($booking->field_bankstatement_date->value);
      $booking_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
      $booking_date = $booking_date->format(DATETIME_DATETIME_STORAGE_FORMAT);

      $incoming = $outgoing = '';
      $amount = round($booking->field_bankstatement_amount->value, 2);

      if($amount > 0) {
        $incoming = (empty($amount)) ? '' : $amount . '€';
      }
      else {
        $outgoing = (empty($amount)) ? '' : $amount . '€';
      }

      $total = round($total, 2) + $booking->field_bankstatement_amount->value;

      $memo = (strpos($booking->field_bankstatement_memo->value, "+++") === 0) ? 'Membership' : $booking->field_bankstatement_memo->value;
      if(empty($memo)) {
        $memo = '';
      }

      if($booking_date < $start_date_storage_format) { continue; }
      if($booking_date > $end_date_storage_format) { continue; }

      // Only one the first loop iteration that is being exported, add the zero line (amount on bank account)
      if ($startrow === TRUE) {
        $rows[] = [
          '0',
          $booking->field_bankstatement_date->value,
          'Start amount on bankaccount',
          '',
          $total - $amount,
          '',
          $total - $amount
        ];
        $startrow = FALSE;
      }

      $a = 0;


      $rows[] = [
        $nr,
        $booking->field_bankstatement_date->value,
        $memo,
        'invoice nr',
        $incoming,
        $outgoing,
        round($total, 2) . '€'
      ];

      $nr++;
    }

    $build = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $build;

  }

}