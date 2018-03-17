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
use Drupal\taxonomy\Entity\Term;


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
    if(is_numeric($statement)) {
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

    if($this->statement->field_booking_status->value == 'completed') {
      return TRUE;
    }

    return FALSE;
  }

  public function getStatements() {
    $query = $this->entity_query
      ->get('booking')
      ->condition('type', ['bankstatement', 'cashstatement'], 'IN')
      ->condition('field_booking_status', 'unprocessed');
      return $query->execute();
  }


  public function genSale($tag) {
    if($this->isCompleted()) {
      return;
    }

    $sale_data = [
      'type' => 'sale',
      'name' => $tag->label(),
      'field_booking_amount' => $this->statement->field_booking_amount->value,
      'field_booking_date' => $this->statement->field_booking_date->value,
      'field_booking_tags' => $tag,
      'uid' => 1
    ];

    $sale = BookingEntity::create($sale_data);
    $sale->save();

    $this->statement->field_booking[] = $sale;
    $this->statement->field_booking_status = 'completed';
    $this->statement->save();
  }


  public function genPurchase($tag) {
    if($this->isCompleted()) {
      return;
    }

    $purchase_data = [
      'type' => 'purchase',
      'name' => $tag->label(),
      'field_booking_amount' => $this->statement->field_booking_amount->value,
      'field_booking_date' => $this->statement->field_booking_date->value,
      'field_booking_tags' => $tag,
      'uid' => 1
    ];

    $purchase = BookingEntity::create($purchase_data);
    $purchase->save();

    $this->statement->field_booking[] = $purchase;
    $this->statement->field_booking_status = 'completed';
    $this->statement->save();
  }


  public function genSalePurchaseFull($booking_id) {
    $booking_storage = $this
      ->entityTypeManager
      ->getStorage('booking');

    $booking = $booking_storage->load($booking_id);
    if($booking->field_booking_status->value == 'completed' ||
      $booking->field_booking_status->value == 'unfinished') {
      return;
    }

    if($booking->get('field_booking_amount')->getValue()[0]['value'] > 0) {
      $sale_data = [
        'type' => 'sale',
        'name' => $booking->get('name')->getValue()[0]['value'],
        'field_booking_amount' => $booking->get('field_booking_amount')->getValue()[0]['value'],
        'field_booking_date' => $booking->get('field_booking_date')->getValue()[0]['value'],
        'field_booking' => $booking_id,
        'uid' => 1
      ];

      $sale = BookingEntity::create($sale_data);
      $sale->save();

      $booking->field_booking->value = $sale->id();
      $booking->field_booking_status->value = 'completed';
      $booking->save();
    }

    if($booking->get('field_booking_amount')->getValue()[0]['value'] < 0) {
      $purchase_data = [
        'type' => 'purchase',
        'name' => $booking->get('name')->getValue()[0]['value'],
        'field_booking_amount' => $booking->get('field_booking_amount')->getValue()[0]['value'],
        'field_booking_date' => $booking->get('field_booking_date')->getValue()[0]['value'],
        'field_booking' => $booking_id,
        'uid' => 1
      ];

      $purchase = BookingEntity::create($purchase_data);
      $purchase->save();

      $booking->field_booking->value = $purchase->id();
      $booking->field_booking_status->value = 'completed';
      $booking->save();
    }

  }

  public function getMembershipTag() {
    $tag = current(taxonomy_term_load_multiple_by_name('membership', 'bookkeeping_tags'));
    if(!$tag) {
      $tag = Term::create(array(
        'parent' => array(),
        'name' => 'membership',
        'vid' => 'bookkeeping_tags',
      ));
      $tag->save();
    }
    return $tag;
  }

  public function getDonationTag() {
    $tag = current(taxonomy_term_load_multiple_by_name('donation', 'bookkeeping_tags'));
    if(!$tag) {
      $tag = Term::create(array(
        'parent' => array(),
        'name' => 'donation',
        'vid' => 'bookkeeping_tags',
      ));
      $tag->save();
    }
    return $tag;
  }

  public function getFoodDrinksTag() {
    $tag = current(taxonomy_term_load_multiple_by_name('food & drinks', 'bookkeeping_tags'));
    if(!$tag) {
      $tag = Term::create(array(
        'parent' => array(),
        'name' => 'food & drinks',
        'vid' => 'bookkeeping_tags',
      ));
      $tag->save();
    }
    return $tag;
  }

  public function getFixedCostsTag() {
    $tag = current(taxonomy_term_load_multiple_by_name('fixed costs', 'bookkeeping_tags'));
    if(!$tag) {
      $tag = Term::create(array(
        'parent' => array(),
        'name' => 'fixed costs',
        'vid' => 'bookkeeping_tags',
      ));
      $tag->save();
    }
    return $tag;
  }

  public function getMaterialTag() {
    $tag = current(taxonomy_term_load_multiple_by_name('material', 'bookkeeping_tags'));
    if(!$tag) {
      $tag = Term::create(array(
        'parent' => array(),
        'name' => 'material',
        'vid' => 'bookkeeping_tags',
      ));
      $tag->save();
    }
    return $tag;
  }

}