<?php

namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;


/**
 * An example controller.
 */
class ExportAll extends ControllerBase {

  public function content() {

    $start_date = new DrupalDateTime('1 january 2016 00:00:00');
    $start_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
    $start_date_storage_format = $start_date->format(DATETIME_DATETIME_STORAGE_FORMAT);

    $end_date = new DrupalDateTime('31 december 2016 23:11:59');
    $end_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
    $end_date_storage_format = $end_date->format(DATETIME_DATETIME_STORAGE_FORMAT);


    $query = \Drupal::entityQuery('booking');
    $query->condition('status', 1);
    $query->condition('type', 'bankstatement');
    $query->condition('field_bankstatement_date', $start_date_storage_format, '>=');
    $query->condition('field_bankstatement_date', $end_date_storage_format, '<=');
    $query->sort('field_bankstatement_date', 'ASC');
    $bankstatement_ids = $query->execute();
    $bankstatements = entity_load_multiple('booking', $bankstatement_ids);
    $bankstatement_rows = [];
    $bankstatement_nr = 0;
    foreach ($bankstatement_ids as $key => $bankstatement_id) {
      $bankstatement_nr++;
      $bankstatement_nrs[$bankstatement_id] = $bankstatement_nr;
    }
    ksort($bankstatement_nrs);

    $query = \Drupal::entityQuery('booking');
    $query->condition('status', 1);
    $query->condition('type', 'cashstatement');
    $query->condition('field_booking_date', $start_date_storage_format, '>=');
    $query->condition('field_booking_date', $end_date_storage_format, '<=');
    $query->sort('field_booking_date', 'ASC');
    $cashstatement_ids = $query->execute();
    $cashstatements = entity_load_multiple('booking', $cashstatement_ids);
    $cashstatement_rows = [];
    $cashstatement_nr = 0;
    foreach ($cashstatement_ids as $key => $cashstatement_id) {
      $cashstatement_nr++;
      $cashstatement_nrs[$cashstatement_id] = $cashstatement_nr;
    }
    ksort($cashstatement_nrs);
    $cash_header = ['nr', 'date', 'description', 'invoice', 'incoming', 'outgoing', 'total'];

    $query = \Drupal::entityQuery('booking');
    $query->condition('status', 1);
    $query->condition('type', 'purchase');
    $query->condition('field_purchase_date', $start_date_storage_format, '>=');
    $query->condition('field_purchase_date', $end_date_storage_format, '<=');
    $query->sort('field_booking_date', 'ASC');
    $purchase_ids = $query->execute();
    $purchases = entity_load_multiple('booking', $purchase_ids);
    $purchase_header = ['nr', 'date', 'purchase description', 'bank nr', 'bank amount', 'cash nr', 'cash amount', 'total'];
    $purchase_rows = []; $purchase_row_nr = 0;

    $query = \Drupal::entityQuery('booking');
    $query->condition('status', 1);
    $query->condition('type', 'sale');
    $query->condition('field_sale_date', $start_date_storage_format, '>=');
    $query->condition('field_sale_date', $end_date_storage_format, '<=');
    $query->sort('field_sale_date', 'ASC');
    $sale_ids = $query->execute();
    $sales = entity_load_multiple('booking', $sale_ids);
    $sale_header = ['nr', 'date', 'sale description', 'bank nr', 'bank amount', 'cash nr', 'cash amount', 'total'];
    $sale_rows = []; $sale_row_nr = 0;





    foreach($cashstatements as $key => $cashstatement) {
      if($cashstatement->field_booking_amount->value < 0) {
        $incoming = '';
        $outgoing = $cashstatement->field_booking_amount->value . '€';
      }
      if($cashstatement->field_booking_amount->value > 0) {
        $outgoing = '';
        $incoming = $cashstatement->field_booking_amount->value . '€';
      }

      $cash_rows[] = [
        $cashstatement_nrs[$cashstatement->ID()],
        $cashstatement->field_booking_date->value,
        $cashstatement->label(),
        '',
        $incoming,
        $outgoing,
        round($cashstatement->field_booking_amount->value, 2) . '€'
      ];
    }





    foreach($purchases as $key => $purchase) {
      $statement = current($purchase->get('field_booking')->referencedEntities());
      $statement_bundle = $statement->bundle();

      if($statement_bundle == 'bankstatement') {
        $bank_amount = $statement->field_booking_amount->value . '€';
        $cash_amount = '';
        $bank_total = $bank_total + $statement->field_booking_amount->value;
        $bankstatement_id = $bankstatement_nrs[$statement->ID()];
        unset($cashstatement_id);
      }

      if($statement_bundle == 'cashstatement') {
        $cash_amount = $statement->field_booking_amount->value . '€';
        $bank_amount = '';
        $cash_total = $cash_total + $statement->field_booking_amount->value;
        $cashstatement_id = $cashstatement_nrs[$statement->ID()];
        unset($bankstatement_id);
      }

      $purchase_row_nr++;
      $purchase_row_nrs[$key] = $purchase_row_nr;
      $purchase_rows[] = [
        $purchase_row_nr,
        $purchase->field_purchase_date->value,
        $purchase->label(),
        $bankstatement_id,
        $bank_amount,
        $cashstatement_id,
        $cash_amount,
        round($booking->field_purchase_total_amount->value, 2) . '€'
      ];
    }





    foreach($sales as $key => $sale) {
      $statement = current($sale->get('field_booking')->referencedEntities());
      $statement_bundle = $statement->bundle();

      if($statement_bundle == 'bankstatement') {
        $bank_amount = $statement->field_booking_amount->value . '€';
        $cash_amount = '';
        $bank_total = $bank_total + $statement->field_booking_amount->value;
        $bankstatement_id = $bankstatement_nrs[$statement->ID()];
        unset($cashstatement_id);
      }

      if($statement_bundle == 'cashstatement') {
        $cash_amount = $statement->field_booking_amount->value . '€';
        $bank_amount = '';
        $cash_total = $cash_total + $statement->field_booking_amount->value;
        $cashstatement_id = $cashstatement_nrs[$statement->ID()];
        unset($bankstatement_id);
      }

      $sale_row_nr++;
      $sale_rows[] = [
        $sale_row_nr,
        $sale->field_sale_date->value,
        $sale->label(),
        $bankstatement_id,
        $bank_amount,
        $cashstatement_id,
        $cash_amount,
        round($sale->field_sale_total_amount->value, 2) . '€'
      ];
      unset($bankstatement_id); unset($cashstatement_id);
    }


    $build[] = [
      '#type' => 'table',
      '#header' => $purchase_header,
      '#caption' => 'Purchase diary',
      '#rows' => $purchase_rows,
    ];

    $build[] = [
      '#type' => 'table',
      '#header' => $sale_header,
      '#caption' => 'Sale diary',
      '#rows' => $sale_rows,
    ];

    $build[] = [
      '#type' => 'table',
      '#header' => $cash_header,
      '#caption' => 'Cash statements',
      '#rows' => $cash_rows,
    ];

    return $build;
  }

}