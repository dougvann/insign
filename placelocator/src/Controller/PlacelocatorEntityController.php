<?php

namespace Drupal\placelocator\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\placelocator\Entity\PlacelocatorEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PlacelocatorEntityController.
 *
 *  Returns responses for Placelocator routes.
 */
class PlacelocatorEntityController extends ControllerBase implements ContainerInjectionInterface {


  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a new PlacelocatorEntityController.
   *
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer.
   */
  public function __construct(DateFormatter $date_formatter, Renderer $renderer) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * Displays a Placelocator revision.
   *
   * @param int $placelocator_entity_revision
   *   The Placelocator revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($placelocator_entity_revision) {
    $placelocator_entity = $this->entityTypeManager()->getStorage('placelocator_entity')
      ->loadRevision($placelocator_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('placelocator_entity');

    return $view_builder->view($placelocator_entity);
  }

  /**
   * Page title callback for a Placelocator revision.
   *
   * @param int $placelocator_entity_revision
   *   The Placelocator revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($placelocator_entity_revision) {
    $placelocator_entity = $this->entityTypeManager()->getStorage('placelocator_entity')
      ->loadRevision($placelocator_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $placelocator_entity->label(),
      '%date' => $this->dateFormatter->format($placelocator_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Placelocator.
   *
   * @param \Drupal\placelocator\Entity\PlacelocatorEntityInterface $placelocator_entity
   *   A Placelocator object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(PlacelocatorEntityInterface $placelocator_entity) {
    $account = $this->currentUser();
    $placelocator_entity_storage = $this->entityTypeManager()->getStorage('placelocator_entity');

    $langcode = $placelocator_entity->language()->getId();
    $langname = $placelocator_entity->language()->getName();
    $languages = $placelocator_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $placelocator_entity->label()]) : $this->t('Revisions for %title', ['%title' => $placelocator_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all placelocator revisions") || $account->hasPermission('administer placelocator entities')));
    $delete_permission = (($account->hasPermission("delete all placelocator revisions") || $account->hasPermission('administer placelocator entities')));

    $rows = [];

    $vids = $placelocator_entity_storage->revisionIds($placelocator_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\placelocator\PlacelocatorEntityInterface $revision */
      $revision = $placelocator_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $placelocator_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.placelocator_entity.revision', [
            'placelocator_entity' => $placelocator_entity->id(),
            'placelocator_entity_revision' => $vid,
          ]));
        }
        else {
          $link = $placelocator_entity->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
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
              'url' => $has_translations ?
              Url::fromRoute('entity.placelocator_entity.translation_revert', [
                'placelocator_entity' => $placelocator_entity->id(),
                'placelocator_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.placelocator_entity.revision_revert', [
                'placelocator_entity' => $placelocator_entity->id(),
                'placelocator_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.placelocator_entity.revision_delete', [
                'placelocator_entity' => $placelocator_entity->id(),
                'placelocator_entity_revision' => $vid,
              ]),
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

    $build['placelocator_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
