<?php

namespace Drupal\simplified_bookkeeping\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Process statement.
 *
 * @Action(
 *   id = "simplified_bookkeeping_process_statement",
 *   label = @Translation("Process selected statements"),
 *   type = "",
 *   confirm = FALSE
 * )
 */
class ProcessStatement extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $membership = \Drupal::service('hsbxl_members.membership');
    $membership->processStatement($entity->id());

    return $this->t('Process statements');
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

}
