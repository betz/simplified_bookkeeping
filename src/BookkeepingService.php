<?php

namespace Drupal\simplified_bookkeeping;

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;
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
  protected $entityManager;
  protected $statement;


  public function __construct(QueryFactory $entity_query, EntityManagerInterface $entityManager) {
    $this->entity_query = $entity_query;
    $this->entityManager = $entityManager;
  }


  public function setStatement($statement) {
    $this->statement = $statement;
  }


  public function genSale() {

  }

  public function genSalePurchaseFull($booking_id) {
    $booking_storage = $this
      ->entityManager
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