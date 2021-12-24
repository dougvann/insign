<?php

namespace Drupal\job;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

// @codingStandardsIgnoreStart
/**
 * Prevents job module from being uninstalled whilst any job nodes
 * exist.
 *
 * In order to auto-generate the required ProxyClass, you can run :
 *
 * php core/scripts/generate-proxy-class.php 'Drupal\job\JobUninstallValidator' "modules/custom/job/src"
 * If you get "The specified database connection is not defined: default"
 * Then you can temporarily hack the initialise function in Settings.php class
 * by explicitly adding the site path (multisite issue):
 *   public static function initialize($app_root, $site_path, &$class_loader) {
 *   $site_path = '/sites/drupaldemo';
 * Or find a proper way to generate this class.
 */
// @codingStandardsIgnoreStop

class JobUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new JobUninstallValidator.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];
    if ($module == 'job') {

      if ($this->hasJobNodes()) {
        $reasons[] = $this->t('To uninstall Job, delete all content that has the Job content type');
      }

      if ($this->hasJobSubmissions()) {
        $reasons[] = $this->t('To uninstall Job, delete all submissions of the webform "job_application_form"');
      }

      if ($this->hasJobTerms()) {
        $reasons[] = $this->t(
          'To uninstall Job, delete all terms that are in those vocabularies : @vocabularies',
          ['@vocabularies' => implode(', ', $this->getJobRelatedVocabularies())]
        );
      }

    }
    return $reasons;
  }

  protected function getJobRelatedVocabularies() {
    return ['job_contract_type', 'job_place', 'job_profession'];
  }

  /**
   * Determines if there is any job nodes or not.
   *
   * @return bool
   *   TRUE if there are job nodes, FALSE otherwise.
   */
  protected function hasJobNodes() {
    $nodes = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('type', 'job')
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    return !empty($nodes);
  }

  /**
   * Determines if there is any job submissions.
   *
   * @return bool
   *   TRUE if there are job submissions, FALSE otherwise.
   */
  protected function hasJobSubmissions() {
    $nodes = $this->entityTypeManager->getStorage('webform_submission')
      ->getQuery()
      ->condition('webform_id', 'job_application_form')
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    return !empty($nodes);
  }

  /**
   * Determines if there is any job terms or not.
   *
   * @return bool
   *   TRUE if there are job terms, FALSE otherwise.
   */
  protected function hasJobTerms() {
    $nodes = $this->entityTypeManager->getStorage('taxonomy_term')
      ->getQuery()
      ->condition(
        'vid',
        $this->getJobRelatedVocabularies(),
        'IN'
      )
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();
    return !empty($nodes);
  }

}
