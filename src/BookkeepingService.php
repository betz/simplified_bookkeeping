<?php

namespace Drupal\simplified_bookkeeping;

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\hsbxl_members\Entity\Membership;


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
    $query->condition('type', ['bankstatement', 'cashstatement'], 'IN');
    $query->sort('field_date' , 'ASC');
    //$query->sort('field_month' , 'ASC');

    foreach ($query->execute() as $bid) {
      $output[] = $bid;
    }

    return $output;
  }

  public function genFoodDrinks() {

  }

}