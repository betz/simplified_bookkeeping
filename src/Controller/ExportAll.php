<?php

namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;

use H2P\Converter\PhantomJS;
use H2P\TempFile;
use H2P\Request;
use H2P\Request\Cookie;

use PhantomPdf\PdfGenerator;

use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Link;



/**
 * An example controller.
 */
class ExportAll extends ControllerBase {

  public function content($year) {

    $start_date = new DrupalDateTime('1 january ' . $year);
    $start_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
    $start_date_storage_format = $start_date->format(DATETIME_DATETIME_STORAGE_FORMAT);

    $end_date = new DrupalDateTime('31 december' . $year);
    $end_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
    $end_date_storage_format = $end_date->format(DATETIME_DATETIME_STORAGE_FORMAT);


    $query = \Drupal::entityQuery('booking');
    $query->condition('status', 1);
    $query->condition('type', 'bankstatement');
    $query->condition('field_booking_date', $start_date_storage_format, '>=');
    $query->condition('field_booking_date', $end_date_storage_format, '<=');
    $query->sort('field_booking_date', 'ASC');
    $bankstatement_ids = $query->execute();
    $bankstatements = entity_load_multiple('booking', $bankstatement_ids);
    $bankstatement_rows = [];
    $bankstatement_nr = 0;
    foreach ($bankstatement_ids as $key => $bankstatement_id) {
      $bankstatement_nr++;
      $bankstatement_nrs[$bankstatement_id] = $bankstatement_nr;
    }
    ksort($bankstatement_nrs);
    $bank_header = ['nr', 'date', 'description', 'invoice', 'incoming', 'outgoing', 'total'];



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
    $query->condition('field_booking_date', $start_date_storage_format, '>=');
    $query->condition('field_booking_date', $end_date_storage_format, '<=');
    $query->sort('field_booking_date', 'ASC');
    $purchase_ids = $query->execute();
    $purchases = entity_load_multiple('booking', $purchase_ids);
    $purchase_header = ['nr', 'date', 'purchase description', 'bank nr', 'bank amount', 'cash nr', 'cash amount', 'total'];
    $purchase_rows = []; $purchase_row_nr = 0;




    $query = \Drupal::entityQuery('booking');
    $query->condition('status', 1);
    $query->condition('type', 'sale');
    //$query->condition('field_sale_date', $start_date_storage_format, '>=');
    //$query->condition('field_sale_date', $end_date_storage_format, '<=');
    //$query->sort('field_sale_date', 'ASC');
    $sale_ids = $query->execute();
    $sales = entity_load_multiple('booking', $sale_ids);
    $sale_header = ['nr', 'date', 'sale description', 'bank nr', 'bank amount', 'cash nr', 'cash amount', 'total'];
    $sale_rows = []; $sale_row_nr = 0;





    foreach($cashstatements as $key => $cashstatement) {
      $cashstatements_total = $cashstatements_total + $cashstatement->field_booking_amount->value;
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
        \Drupal::service('date.formatter')->format(strtotime($cashstatement->field_booking_date->value), 'bookkeeping_date'),
        $cashstatement->label(),
        '',
        $incoming,
        $outgoing,
        round($cashstatement->field_booking_amount->value, 2) . '€'
      ];
    }
    $cash_rows[] = [
      '', '', '', '', '', '', $cashstatements_total . '€'
    ];



    foreach($bankstatements as $key => $bankstatement) {
      $bankstatements_total = $bankstatements_total + $bankstatement->field_booking_amount->value;
      if($bankstatement->field_booking_amount->value < 0) {
        $incoming = '';
        $outgoing = $bankstatement->field_booking_amount->value . '€';
      }
      if($bankstatement->field_booking_amount->value > 0) {
        $outgoing = '';
        $incoming = $bankstatement->field_booking_amount->value . '€';
      }

      $bank_rows[] = [
        $bankstatement_nrs[$bankstatement->ID()],
        \Drupal::service('date.formatter')->format(strtotime($bankstatement->field_booking_date->value), 'bookkeeping_date'),
        $bankstatement->label(),
        '',
        $incoming,
        $outgoing,
        round($bankstatement->field_booking_amount->value, 2) . '€'
      ];
    }
    $bank_rows[] = [
      '', '', '', '', '', '', $bankstatements_total . '€'
    ];





    foreach($purchases as $key => $purchase) {

      $purchases_total = $purchases_total + $purchase->field_purchase_total_amount->value;
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
        \Drupal::service('date.formatter')->format(strtotime($purchase->field_booking_date->value), 'bookkeeping_date'),
        $purchase->label(),
        $bankstatement_id,
        $bank_amount,
        $cashstatement_id,
        $cash_amount,
        round($purchase->field_purchase_total_amount->value, 2) . '€'
      ];
    }
    $purchase_rows[] = [
      '', '', '', '', '', '', '', $purchases_total . '€'
    ];






    foreach($sales as $key => $sale) {
      $sales_total = $sales_total + $sale->field_sale_total_amount->value;
      $statement = current($sale->get('field_booking')->referencedEntities());
      $statement_bundle = $statement->bundle();

      if($statement_bundle == 'bankstatement') {
        $bank_amount = $statement->field_booking_amount->value . '€';
        $cash_amount = '';
        $bank_total = $bank_total + $statement->field_booking_amount->value;
        $bankstatement_id = $bankstatement_nrs[$statement->ID()];
        $cashstatement_id = '';
      }

      if($statement_bundle == 'cashstatement') {
        $cash_amount = $statement->field_booking_amount->value . '€';
        $bank_amount = '';
        $cash_total = $cash_total + $statement->field_booking_amount->value;
        $cashstatement_id = $cashstatement_nrs[$statement->ID()];
        $bankstatement_id = '';
      }

      $a = 0;

      $sale_row_nr++;
      $sale_rows[] = [
        $sale_row_nr,
        //\Drupal::service('date.formatter')->format(strtotime($sale->field_sale_date->value), 'bookkeeping_date'),
        !empty($sale->field_sale_memo->value) ? $sale->field_sale_memo->value : $sale->label,
        $bankstatement_id,
        $bank_amount,
        $cashstatement_id,
        $cash_amount,
        round($sale->field_sale_total_amount->value, 2) . '€'
      ];
      $bankstatement_id = ''; $cashstatement_id = '';
    }
    $sale_rows[] = [
      '', '', '', '', '', '', '', $sales_total . '€'
    ];





    $build['purchases'] = $build_purchasediary = [
      '#type' => 'table',
      '#header' => $purchase_header,
      '#rows' => $purchase_rows,
      '#attributes' => [
        'class' => ['table', 'table-striped', 'table-condensed', 'table-bordered'],
        'style' => 'page-break-after: always',
      ],
    ];
    $build['purchases']['#caption'] = 'Purchase Diary';

    $build['sales'] = $build_salediary = [
      '#type' => 'table',
      '#header' => $sale_header,
      '#rows' => $sale_rows,
      '#attributes' => [
        'class' => ['table', 'table-striped', 'table-condensed', 'table-bordered'],
        'style' => 'page-break-after: always',
      ],
    ];
    $build['sales']['#caption'] = 'Sales Diary';

    $build['cash'] = $build_cashstatements = [
      '#type' => 'table',
      '#header' => $cash_header,
      '#rows' => $cash_rows,
      '#attributes' => [
        'class' => ['table', 'table-striped', 'table-condensed', 'table-bordered'],
        'style' => 'page-break-after: always',
      ],
    ];
    $build['cash']['#caption'] = 'Cash Statements';

    $build['bank'] = $build_bankstatements = [
      '#type' => 'table',
      '#header' => $bank_header,
      '#rows' => $bank_rows,
      '#attributes' => [
        'class' => ['table', 'table-striped', 'table-condensed', 'table-bordered'],
        'style' => 'page-break-after: always',
      ],
    ];
    $build['bank']['#caption'] = 'Bank Statements';


    $pdf_purchasediary = \Drupal::service('renderer')->render($build_purchasediary);
    $pdf_salediary = \Drupal::service('renderer')->render($build_salediary);
    $pdf_cashstatements = \Drupal::service('renderer')->render($build_cashstatements);
    $pdf_bankstatements = \Drupal::service('renderer')->render($build_bankstatements);

    $template = [
      '#theme' => 'simplified_booking_pdf_all',
      '#title' => 'Books Better Be Balanced: '  . $year,
      '#purchasediary' => $pdf_purchasediary,
      '#salediary' => $pdf_salediary,
      '#cashstatements' => $pdf_cashstatements,
      '#bankstatements' => $pdf_bankstatements,
    ];

    $build['pdflink'] = [
      '#markup' => file_create_url('private://booksbetterbebalanced/' . $year . '.pdf'),
    ];

    $pdf_html = \Drupal::service('renderer')->render($template);

    $pdf = new PdfGenerator();
    $pdf->setOrientation('landscape');
    $pdf->setStoragePath('/var/www/drupalvm/pdf');
    $pdf->setBinaryPath('xvfb-run phantomjs');
    $pdf->saveFromView($pdf_html, '/var/www/drupalvm/pdf/' . $year . '.pdf');

    $tmp_pdf = file_get_contents('/var/www/drupalvm/pdf/' . $year . '.pdf');
    $directory = 'private://booksbetterbebalanced';
    $prepare = file_prepare_directory($directory);

    $file = file_save_data($tmp_pdf, $directory . '/' . $year . '.pdf', FILE_EXISTS_REPLACE);

    $a = 0;

    $path = drupal_realpath($file->getFileUri());

    $a = 0;

    return $build;
  }
}