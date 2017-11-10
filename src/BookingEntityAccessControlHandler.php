<?php

namespace Drupal\simplified_bookkeeping;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Booking entity.
 *
 * @see \Drupal\simplified_bookkeeping\Entity\BookingEntity.
 */
class BookingEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\simplified_bookkeeping\Entity\BookingEntityInterface $entity */
    switch ($operation) {
      case 'view':
        //return AccessResult::allowedIfHasPermission($account, 'access content');

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished booking entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published booking entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit booking entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete booking entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add booking entities');
  }

}
