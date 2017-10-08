<?php


namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\charts\Services\ChartsSettingsService;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DrupalDateTime;

class SimplifiedBookkeepingGraphs extends ControllerBase implements ContainerInjectionInterface {
  private $chartSettings;

  public function __construct(ChartsSettingsService $chartSettings) {
    $this->chartSettings = $chartSettings->getChartsSettings();
  }

  public function graph($start, $end) {

    $library = $this->chartSettings['library'];
    if (!isset($library)) {
      drupal_set_message(t('You need to first configure Charts default settings'));
    }

    $start_date = new DrupalDateTime('1 january ' . $start);
    $start_date->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
    $start_date_storage_format = $start_date->format(DATETIME_DATETIME_STORAGE_FORMAT);

    $end_date = new DrupalDateTime('31 december' . $end);
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

    $banktotal = 0;
    foreach($bankstatements as $key => $bankstatement) {
      $bankheaders[] = \Drupal::service('date.formatter')->format(strtotime($bankstatement->field_bankstatement_date->value), 'bookkeeping_date');

      $bankdata[] = $banktotal = $banktotal + (int)$bankstatement->field_booking_amount->value;
    }

    $options = [];
    $options['type'] = $this->chartSettings['type'];
    $options['title'] = $this->t('Bankaccount');
    $options['yaxis_title'] = $this->t('Euro');
    $options['yaxis_min'] = '';
    $options['yaxis_max'] = '';
    $options['xaxis_title'] = $this->t('X-Axis');

    // Sample data format.
    $categories = $bankheaders;
    $seriesData = [
      ["name" => "Argenta", "color" => "#0d233a", "type" => null, "data" => $bankdata]
    ];

    $element = [
      '#theme' => 'charts_api_example',
      '#library' => $this->t($library),
      '#categories' => $categories,
      '#seriesData' => $seriesData,
      '#options' => $options,
    ];
    return $element;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('charts.settings')
    );
  }
}