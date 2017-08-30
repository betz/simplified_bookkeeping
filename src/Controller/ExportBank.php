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

    $date = new DrupalDateTime('1 august 2017');
    $date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
    $startdate = $date->format(DATETIME_DATETIME_STORAGE_FORMAT);


    $query = \Drupal::entityQuery('booking');
    //$query->condition('field_bankstatement_date', $startdate, '>=');
    $query->condition('status', 1);
    $query->condition('type', 'bankstatement');
    $query->sort('field_bankstatement_date', 'ASC');

    $entity_ids = $query->execute();
    $bookings = entity_load_multiple('booking', $entity_ids);
    $start_amount = $total = 0; $nr = 1;

    $a = 0;

    $header = [
      'Nr',
      'Date',
      'Description',
      'Invoice Nr',
      'Incoming',
      'Outgoing',
      'Total'
    ];

    $rows[] = ['0', '', 'Already in bank', '', $start_amount, '', ''];

    foreach ($bookings as $booking) {
      $booking_date = new DrupalDateTime($booking->field_bankstatement_date->value);
      $booking_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
      $booking_date = $booking_date->format(DATETIME_DATETIME_STORAGE_FORMAT);

      $a = 0;

      if($booking_date < $start_date) {break;}

      $entity_id = $booking->getOriginalId();
      $incoming = $outgoing = '';
      $amount = $booking->field_bankstatement_amount->value;

      $a = 0;

      if($amount > 0) {
        $incoming = (empty($amount)) ? '' : $amount . '€';
      }
      else {
        $outgoing = (empty($amount)) ? '' : $amount . '€';
      }

      $total = $total + $booking->field_bankstatement_amount->value;

      $memo = (strpos($booking->field_bankstatement_memo->value, "+++") === 0) ? 'Membership' : $booking->field_bankstatement_memo->value;
      if(empty($memo)) {
        $memo = '';
      }


      $rows[] = [
        $nr,
        $booking->field_bankstatement_date->value,
        $memo,
        'invoice nr',
        $incoming,
        $outgoing,
        $total . '€'
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