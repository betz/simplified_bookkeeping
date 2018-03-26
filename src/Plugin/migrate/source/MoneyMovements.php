<?php

namespace Drupal\simplified_bookkeeping\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Connection to the moneymovements table of hsbprod.
 *
 * @MigrateSource(
 *   id = "hsbprod_moneymovements",
 *   source_module = "simplified_bookeeping",
 * )
 */
class MoneyMovements extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('moneymovements', 'm')
      ->fields('m', [
        'id',
        'date_val',
        'date_account',
        'this_account',
        'other_account',
        'amount',
        'currency',
        'message',
        'other_account_name',
        'transaction_id',
        'raw_csv_line'
      ])
      ->orderBy('id');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'id' => $this->t('id'),
      'date_val'   => $this->t('date_val'),
      'date_account'    => $this->t('date_account'),
      'this_account'    => $this->t('this_account'),
      'other_account'   => $this->t('other_account'),
      'amount'   => $this->t('amount'),
      'currency'   => $this->t('currency'),
      'message'   => $this->t('message'),
      'other_account_name'   => $this->t('other_account_name'),
      'transaction_id'   => $this->t('transaction_id'),
      'raw_csv_line'   => $this->t('raw_csv_line')
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'transaction_id' => [
        'type' => 'text',
        'alias' => 'id',
      ],
    ];
  }
}