<?php

namespace Drupal\simplified_bookkeeping\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\simplified_bookkeeping\Entity\BookingEntityInterface;

/**
 * Class BookingEntityController.
 *
 *  Returns responses for Booking routes.
 */
class BookingEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Booking  revision.
   *
   * @param int $booking_revision
   *   The Booking  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($booking_revision) {
    $booking = $this->entityManager()->getStorage('booking')->loadRevision($booking_revision);
    $view_builder = $this->entityManager()->getViewBuilder('booking');

    return $view_builder->view($booking);
  }

  /**
   * Page title callback for a Booking  revision.
   *
   * @param int $booking_revision
   *   The Booking  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($booking_revision) {
    $booking = $this->entityManager()->getStorage('booking')->loadRevision($booking_revision);
    return $this->t('Revision of %title from %date', ['%title' => $booking->label(), '%date' => format_date($booking->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Booking .
   *
   * @param \Drupal\simplified_bookkeeping\Entity\BookingEntityInterface $booking
   *   A Booking  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(BookingEntityInterface $booking) {
    $account = $this->currentUser();
    $langcode = $booking->language()->getId();
    $langname = $booking->language()->getName();
    $languages = $booking->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $booking_storage = $this->entityManager()->getStorage('booking');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $booking->label()]) : $this->t('Revisions for %title', ['%title' => $booking->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all booking revisions") || $account->hasPermission('administer booking entities')));
    $delete_permission = (($account->hasPermission("delete all booking revisions") || $account->hasPermission('administer booking entities')));

    $rows = [];

    $vids = $booking_storage->revisionIds($booking);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\simplified_bookkeeping\BookingEntityInterface $revision */
      $revision = $booking_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $booking->getRevisionId()) {
          $link = $this->l($date, new Url('entity.booking.revision', ['booking' => $booking->id(), 'booking_revision' => $vid]));
        }
        else {
          $link = $booking->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => Url::fromRoute('entity.booking.revision_revert', ['booking' => $booking->id(), 'booking_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.booking.revision_delete', ['booking' => $booking->id(), 'booking_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['booking_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
