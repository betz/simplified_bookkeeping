<?php

namespace Drupal\simplified_bookkeeping\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Booking entities.
 *
 * @ingroup simplified_bookkeeping
 */
interface BookingEntityInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Booking name.
   *
   * @return string
   *   Name of the Booking.
   */
  public function getName();

  /**
   * Sets the Booking name.
   *
   * @param string $name
   *   The Booking name.
   *
   * @return \Drupal\simplified_bookkeeping\Entity\BookingEntityInterface
   *   The called Booking entity.
   */
  public function setName($name);

  /**
   * Gets the Booking creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Booking.
   */
  public function getCreatedTime();

  /**
   * Sets the Booking creation timestamp.
   *
   * @param int $timestamp
   *   The Booking creation timestamp.
   *
   * @return \Drupal\simplified_bookkeeping\Entity\BookingEntityInterface
   *   The called Booking entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Booking published status indicator.
   *
   * Unpublished Booking are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Booking is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Booking.
   *
   * @param bool $published
   *   TRUE to set this Booking to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\simplified_bookkeeping\Entity\BookingEntityInterface
   *   The called Booking entity.
   */
  public function setPublished($published);

  /**
   * Gets the Booking revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Booking revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\simplified_bookkeeping\Entity\BookingEntityInterface
   *   The called Booking entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Booking revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Booking revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\simplified_bookkeeping\Entity\BookingEntityInterface
   *   The called Booking entity.
   */
  public function setRevisionUserId($uid);

}
