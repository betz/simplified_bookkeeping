<?php

namespace Drupal\simplified_bookkeeping;

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\simplified_bookkeeping\Entity\BookingEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\hsbxl_members\Entity\Membership;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;


/**
 * Class BookkeepingService.
 */
class BookkeepingService {
  protected $entity_query;
  protected $entityTypeManager;
  protected $statement;
  protected $amount;
  protected $name;
  protected $date;


  public function __construct(QueryFactory $entity_query, EntityTypeManagerInterface $entityTypeManager) {
    $this->entity_query = $entity_query;
    $this->entityTypeManager = $entityTypeManager;
    $this->bookingStorage = $this->entityTypeManager->getStorage('booking');
  }


  public function setStatement($statement) {
    if(is_object($statement)) {
      $this->statement = $statement;
    }
    if(is_int($statement)) {
      $this->statement = $this->bookingStorage->load($statement);
    }
    return FALSE;
  }

  public function getStatement() {
    return $this->statement;
  }

  public function setAmount($amount) {
    $this->amount = $amount;
  }

  public function getAmount() {
    return $this->amount;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }

  public function setDate($date) {
    $this->date = $date;
  }

  public function getDate() {
    return $this->date;
  }


  public function isCompleted() {
    $total_amount = $this->statement->field_booking_amount->value;
    // TODO: get all sale amounts and compare to the parent statement amount.

    if($this->statement->field_completed->value == TRUE) {
      return TRUE;
    }

    return FALSE;
  }


  public function genSale() {
    if($this->isCompleted()) {
      return;
    }

    $sale_data = [
      'type' => 'sale',
      'name' => $this->name,
      'field_booking_amount' => $this->amount,
      'field_booking_date' => $this->date,
      'field_booking' => $this->statement->id(),
      'uid' => 1
    ];

    $sale = BookingEntity::create($sale_data);
    $sale->save();
  }

  public function genSalePurchaseFull($booking_id) {
    $booking_storage = $this
      ->entityTypeManager
      ->getStorage('booking');

    $booking = $booking_storage->load($booking_id);
    if($booking->field_completed->value == TRUE) {
      return;
    }

    if($booking->get('field_booking_amount')->getValue()[0]['value'] > 0) {
      $sale_data = [
        'type' => 'sale',
        'name' => $booking->get('name')->getValue()[0]['value'],
        'field_sale_total_amount' => $booking->get('field_booking_amount')->getValue()[0]['value'],
        'field_booking_date' => $booking->get('field_booking_date')->getValue()[0]['value'],
        'field_booking' => $booking_id,
        //'field_payment_method' => $booking->bundle(),
        'uid' => 1
      ];

      $sale = BookingEntity::create($sale_data);
      $sale->save();

      $booking->field_booking->value = $sale->id();
      $booking->field_completed->value = TRUE;
      $booking->save();
    }

    if($booking->get('field_booking_amount')->getValue()[0]['value'] < 0) {
      $purchase_data = [
        'type' => 'purchase',
        'name' => $booking->get('name')->getValue()[0]['value'],
        'field_purchase_total_amount' => $booking->get('field_booking_amount')->getValue()[0]['value'],
        'field_booking_date' => $booking->get('field_booking_date')->getValue()[0]['value'],
        'field_booking' => $booking_id,
        //'field_payment_method' => $booking->bundle(),
        'uid' => 1
      ];

      $purchase = BookingEntity::create($purchase_data);
      $purchase->save();

      $booking->field_booking->value = $purchase->id();
      $booking->field_completed->value = TRUE;
      $booking->save();
    }

  }

  public function queueStatements() {
    $query = $this->entity_query->get('booking');
    $query->condition('status', 1);
    $query->condition('field_completed', FALSE);
    $query->condition('type', ['bankstatement', 'cashstatement'], 'IN');
    $query->sort('field_booking_date' , 'ASC');

    $queue_factory = \Drupal::service('queue');
    $queue = $queue_factory->get('statements_queue_processor');

    foreach ($query->execute() as $bid) {
      $queue->createItem($bid);
    }

    return;
  }

  public function genPurchases() {
    $query = $this->entity_query->get('booking');
    $query->condition('status', 1);
    //$query->condition('field_processed', FALSE);
    $query->condition('type', ['bankstatement', 'cashstatement'], 'IN');
    $query->sort('field_booking_date' , 'ASC');

    $booking_storage = $this
      ->entityManager
      ->getStorage('booking');

    foreach ($query->execute() as $bid) {
      $output[] = $bid;
      $booking = $booking_storage->load($bid);

      if($booking->get('field_booking_amount')->getValue()[0]['value'] < 0) {
        $sale_data = [
          'type' => 'purchase',
          'name' => $booking->get('name')->getValue()[0]['value'],
          'field_purchase_total_amount' => $booking->get('field_booking_amount')->getValue()[0]['value'],
          'field_booking_date' => $booking->get('field_booking_date')->getValue()[0]['value'],
          'field_booking' => $booking->id(),
          //'field_payment_method' => $booking->bundle(),
          'uid' => 1
        ];

        //$sale = BookingEntity::create($sale_data);
        //$sale->save();
      }
    }

    return $output;
  }

}