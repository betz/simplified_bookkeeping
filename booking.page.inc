<?php

/**
 * @file
 * Contains booking.page.inc.
 *
 * Page callback for Booking entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Booking templates.
 *
 * Default template: booking.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_booking(array &$variables) {
  // Fetch BookingEntity Entity Object.
  $booking = $variables['elements']['#booking'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
