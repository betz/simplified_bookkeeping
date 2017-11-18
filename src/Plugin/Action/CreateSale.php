<?php

namespace Drupal\simplified_bookkeeping\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Create sale of statement.
 *
 * @Action(
 *   id = "simplified_bookkeeping_statement_sale",
 *   label = @Translation("Create sale"),
 *   type = "",
 *   confirm = FALSE
 * )
 */
class CreateSale extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {

    $bookkeeping = \Drupal::service('simplified_bookkeeping.bookkeeping');
    $bookkeeping->setStatement($entity->id());

    $term = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->load($this->configuration['tag']);

    $bookkeeping->genSale($term);

    return $this->t('Create sale');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $access = $object->access('delete', $account, TRUE);

    if ($object->getEntityType() === 'node') {
      $access->andIf($object->status->access('delete', $account, TRUE));
    }

    return $return_as_object ? $access : $access->isAllowed();
  }

  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('bookkeeping_tags');
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }

    $form['tag'] = [
      '#title' => t('Sale tag'),
      '#type' => 'select',
      '#options' => $term_data,
      '#description' => 'Select a tag to attach to the created sale.',
      '#default_value' => $form_state->getValue('tag'),
    ];
    return $form;
  }
}
