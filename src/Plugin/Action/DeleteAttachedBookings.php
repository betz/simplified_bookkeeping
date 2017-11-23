<?php

namespace Drupal\simplified_bookkeeping\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Delete attached bookings.
 *
 * @Action(
 *   id = "simplified_bookkeeping_delete_attached_bookings",
 *   label = @Translation("Delete attached bookings"),
 *   type = "",
 *   confirm = FALSE
 * )
 */
class DeleteAttachedBookings extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {

    $bookings = $entity->get('field_booking')->referencedEntities();
    $storage_handler = \Drupal::entityTypeManager()->getStorage("booking");
    $storage_handler->delete($bookings);
    $entity->field_booking = NULL;
    $entity->save();

    return $this->t('Delete attached bookings');
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
