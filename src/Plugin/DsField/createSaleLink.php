<?php

namespace Drupal\simplified_bookkeeping\Plugin\DsField;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\taxonomy\Entity\Vocabulary;
use \Drupal\Core\Link;
use \Drupal\Core\Url;

/**
 * Plugin that renders the terms inside a chosen taxonomy vocabulary.
 *
 * @DsField(
 *   id = "create_sale_link",
 *   title = @Translation("'Create Sale' Link"),
 *   entity_type = "booking",
 *   provider = "simplified_bookkeeping",
 *   ui_limit = {"*|*"}
 * )
 */
class createSaleLink extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $url = Url::fromUri('https://google.com');
    $link = Link::fromTextAndUrl('Create new sale', $url);

    return array(
      '#theme' => 'item_list',
      '#items' => array($link),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $names = taxonomy_vocabulary_get_names();
    $vocabularies = Vocabulary::loadMultiple($names); // Should use dependency injection rather.
    $options = array();
    foreach ($vocabularies as $vocabulary) {
      $options[$vocabulary->id()] = $vocabulary->label();
    }
    $settings['vocabulary'] = array(
      '#type' => 'select',
      '#title' => t('Vocabulary'),
      '#default_value' => $config['vocabulary'],
      '#options' => $options,
    );

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $config = $this->getConfiguration();
    $no_selection = array('No vocabulary selected.');

    if (isset($config['vocabulary']) && $config['vocabulary']) {
      $vocabulary = Vocabulary::load($config['vocabulary']);
      return $vocabulary ? array('Vocabulary: ' . $vocabulary->label()) : $no_selection;
    }

    return $no_selection;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {

    $configuration = array(
      'vocabulary' => 'bookkeeping_tags',
    );

    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function formatters() {
    return array('linked' => 'Linked', 'unlinked' => 'Unlinked');
  }

}