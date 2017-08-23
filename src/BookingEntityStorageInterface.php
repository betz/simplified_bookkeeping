<?php

namespace Drupal\simplified_bookkeeping;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface BookingEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Booking revision IDs for a specific Booking.
   *
   * @param \Drupal\simplified_bookkeeping\Entity\BookingEntityInterface $entity
   *   The Booking entity.
   *
   * @return int[]
   *   Booking revision IDs (in ascending order).
   */
  public function revisionIds(BookingEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Booking author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Booking revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\simplified_bookkeeping\Entity\BookingEntityInterface $entity
   *   The Booking entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(BookingEntityInterface $entity);

  /**
   * Unsets the language for all Booking with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
