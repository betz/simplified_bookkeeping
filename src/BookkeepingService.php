<?php

namespace Drupal\simplified_bookkeeping;

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\simplified_bookkeeping\Entity\BookingEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\hsbxl_members\Entity\Membership;
use Drupal\core\Queue;


/**
 * Class BookkeepingService.
 */
class BookkeepingService {
  protected $entity_query;
  protected $entityManager;

  public function __construct(QueryFactory $entity_query, EntityManagerInterface $entityManager) {
    $this->entity_query = $entity_query;
    $this->entityManager = $entityManager;
  }

  public function genSales() {
    $query = $this->entity_query->get('booking');
    $query->condition('status', 1);
    $query->condition('field_processed', FALSE);
    $query->condition('type', ['bankstatement', 'cashstatement'], 'IN');
    $query->sort('field_booking_date' , 'ASC');

    $booking_storage = $this
      ->entityManager
      ->getStorage('booking');

    foreach ($query->execute() as $bid) {
      $output[] = $bid;
      $booking = $booking_storage->load($bid);

      if($booking->get('field_booking_amount')->getValue()[0]['value'] > 0) {
        $sale_data = [
          'type' => 'sale',
          'name' => $booking->get('name')->getValue()[0]['value'],
          'field_sale_total_amount' => $booking->get('field_booking_amount')->getValue()[0]['value'],
          'field_booking_date' => $booking->get('field_booking_date')->getValue()[0]['value'],
          'field_booking' => $booking->id(),
          'field_payment_method' => $booking->bundle(),
          'uid' => 1
        ];

        //$sale = BookingEntity::create($sale_data);
        //$sale->save();
      }
    }

    return $output;
  }

  public function genPurchases() {
    $query = $this->entity_query->get('booking');
    $query->condition('status', 1);
    $query->condition('field_processed', FALSE);
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
          'field_payment_method' => $booking->bundle(),
          'uid' => 1
        ];

        //$sale = BookingEntity::create($sale_data);
        //$sale->save();
      }
    }

    return $output;
  }

}