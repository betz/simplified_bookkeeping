<?php

namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\file\Entity\File;

use Drupal\payment_form\Entity\Payment;



/**
 * An example controller.
 */
class MembershipPayments extends ControllerBase {

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   */
  public function bookkeeper_access(AccountInterface $account, AccountInterface $user = NULL) {

    // If a member has 'access bookkeeping' permission, always grant access.
    if($account->hasPermission('access bookkeeping')) {
      return AccessResult::allowedIf(TRUE);
    }

    // Deny access for all others requests.
    return AccessResult::allowedIf(FALSE);
  }

  function user_payments(AccountInterface $user = NULL) {
    //kint($user);
    $query = \Drupal::entityQuery('booking');
    $query->condition('type', 'sale');
    $query->condition('field_membership_member', $user->id(), '=');
    $query->condition('status', 1);
    $query->sort('field_sale_date', 'DESC');
    $paymentids = $query->execute();
    kint($paymentids);
    return entity_load_multiple('booking', $paymentids);
  }

  public function user_payments_table(UserInterface $user = NULL) {
    $sales = $this->user_payments($user);
    //kint($sales);
    $total = 0; $bank_rows = [];

    // go over and create the table rows.
    foreach($sales as $sale) {
      $bank_rows[] = [
        $sale->get('field_sale_date')->getValue()[0]['value'],
        $sale->get('field_sale_payment_method')->getValue()[0]['value'],
        $sale->get('field_sale_total_amount')->getValue()[0]['value'] . '€',
      ];
      $total = $total + $sale->get('field_sale_total_amount')->getValue()[0]['value'];
    }

    // Check if the total is above zero
    // if not, create an empty message row.
    if($total > 0) {
      $bank_rows[] = [
        'TOTAL',
        '',
        '= ' . $total . '€',
      ];
    }
    else {
      $bank_rows[] = [
        '', 'No membership payments found.', ''
      ];
    }

    // Define the table render array
    $build[] = [
      '#type' => 'table',
      '#header' => [
        'Date',
        'Method',
        'Amount',
      ],
      '#rows' => $bank_rows,
      '#attributes' => [
        'class' => ['table', 'table-striped', 'table-condensed', 'table-bordered']
      ],
    ];

    return $build;
  }
}