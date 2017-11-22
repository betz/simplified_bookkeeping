<?php
namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\hsbxl_numbers\NumbersService;
use Drupal\User\Entity\User;
use Drupal\hsbxl_members\MembershipService;
use Drupal\simplified_bookkeeping\BookkeepingService;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides route responses for the Example module.
 */
class Dashboard extends ControllerBase {

  public function __construct(MembershipService $membership, BookkeepingService $bookkeeping, NumbersService $numbersService) {
    $this->membership = $membership;
    $this->bookkeeping = $bookkeeping;
    $this->numbers = $numbersService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hsbxl_members.membership'),
      $container->get('simplified_bookkeeping.bookkeeping'),
      $container->get('hsbxl_numbers.numbers')
    );
  }

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function page() {

    $month = 11;
    $year = 2017;

    $this->numbers->setYear($year);
    $this->numbers->setMonth($month);

    dpm($month . '-' . $year, 'date');

    dpm($this->numbers->getIncomeMemberships(), 'getIncomeMemberships');
    dpm($this->numbers->getIncomeDonations(), 'getIncomeDonations');
    dpm($this->numbers->getIncomeFoodDrinks(), 'getIncomeFoodDrinks');
    dpm($this->numbers->getIncomeFixedCosts(), 'getIncomeFixedCosts');
    $income_total = $this->numbers->getIncomeDonations()
      + $this->numbers->getIncomeFoodDrinks()
      + $this->numbers->getIncomeMemberships()
      + $this->numbers->getIncomeFixedCosts();
    dpm($income_total, '=> income_total');

    dpm($this->numbers->getPurchasesFoodDrinks(), 'getPurchasesFoodDrinks');
    dpm($this->numbers->getPurchasesMaterial(), 'getPurchasesMaterial');
    dpm($this->numbers->getPurchasesFixedCosts(), 'getPurchasesFixedCosts');
    $purchases_total = $this->numbers->getPurchasesFoodDrinks()
      + $this->numbers->getPurchasesMaterial()
      + $this->numbers->getPurchasesFixedCosts();
    dpm($purchases_total, '=> purchases_total');

    $element = array(
      '#markup' => 'Hello, world',
      '#attached' => [
        'library' => [
          'simplified_bookkeeping/simplified_bookkeeping',
        ]
      ],
    );
    return $element;
  }

}