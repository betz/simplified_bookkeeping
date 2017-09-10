<?php

namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\file\Entity\File;



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
  public function access(AccountInterface $account, AccountInterface $user = NULL) {

    // If a member has 'access bookkeeping' permission, always grant access.
    if($account->hasPermission('access bookkeeping')) {
      return AccessResult::allowedIf(TRUE);
    }

    // Check if the logged in member is the same as the visited member page.
    // If so, grant access.
    if($account->id() == $user->id()) {
      return AccessResult::allowedIf(TRUE);
    }

    // Deny access for all others requests.
    return AccessResult::allowedIf(FALSE);
  }


  public function content(UserInterface $user = NULL) {

    //$user = \Drupal::currentUser();

    $query = \Drupal::entityQuery('booking');
    $query->condition('status', 1);
    $query->condition('type', 'sale');
    $query->condition('user_id', $user->id(), '=');
    $query->sort('field_sale_date', 'DESC');

    $sale_ids = $query->execute();
    $sales = entity_load_multiple('booking', $sale_ids);
    $total = 0; $bank_rows = [];

    foreach($sales as $sale) {
      $bank_rows[] = [
        $sale->get('field_sale_date')->getValue()[0]['value'],
        $sale->get('field_sale_payment_method')->getValue()[0]['value'],
        $sale->get('field_sale_total_amount')->getValue()[0]['value'] . '€',
      ];
      $total = $total + $sale->get('field_sale_total_amount')->getValue()[0]['value'];
    }

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

    $a = 0;

    $bank_header = [
      'Date',
      'Method',
      'Amount',
    ];

    $build[] = [
      '#type' => 'table',
      '#header' => $bank_header,
      '#rows' => $bank_rows,
      '#attributes' => [
        'class' => ['table', 'table-striped', 'table-condensed', 'table-bordered']
      ],
    ];

    return $build;
  }
}