<?php

namespace Drupal\simplified_bookkeeping\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Booking type entity.
 *
 * @ConfigEntityType(
 *   id = "booking_type",
 *   label = @Translation("Booking type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\simplified_bookkeeping\BookingEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\simplified_bookkeeping\Form\BookingEntityTypeForm",
 *       "edit" = "Drupal\simplified_bookkeeping\Form\BookingEntityTypeForm",
 *       "delete" = "Drupal\simplified_bookkeeping\Form\BookingEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\simplified_bookkeeping\BookingEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "booking_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "booking",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/booking_type/{booking_type}",
 *     "add-form" = "/admin/structure/booking_type/add",
 *     "edit-form" = "/admin/structure/booking_type/{booking_type}/edit",
 *     "delete-form" = "/admin/structure/booking_type/{booking_type}/delete",
 *     "collection" = "/admin/structure/booking_type"
 *   }
 * )
 */
class BookingEntityType extends ConfigEntityBundleBase implements BookingEntityTypeInterface {

  /**
   * The Booking type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Booking type label.
   *
   * @var string
   */
  protected $label;

}
