<?php

namespace Drupal\simplified_bookkeeping;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\simplified_bookkeeping\Entity\BookingEntityInterface;

/**
 * Defines the storage handler class for Booking entities.
 *
 * This extends the base storage class, adding required special handling for
 * Booking entities.
 *
 * @ingroup simplified_bookkeeping
 */
class BookingEntityStorage extends SqlContentEntityStorage implements BookingEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(BookingEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {booking_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {booking_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(BookingEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {booking_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('booking_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
