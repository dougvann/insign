<?php

namespace Drupal\placelocator;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\placelocator\Entity\PlacelocatorEntityInterface;

/**
 * Defines the storage handler class for Placelocator entities.
 *
 * This extends the base storage class, adding required special handling for
 * Placelocator entities.
 *
 * @ingroup placelocator
 */
interface PlacelocatorEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Placelocator revision IDs for a specific Placelocator.
   *
   * @param \Drupal\placelocator\Entity\PlacelocatorEntityInterface $entity
   *   The Placelocator entity.
   *
   * @return int[]
   *   Placelocator revision IDs (in ascending order).
   */
  public function revisionIds(PlacelocatorEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Placelocator author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Placelocator revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\placelocator\Entity\PlacelocatorEntityInterface $entity
   *   The Placelocator entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(PlacelocatorEntityInterface $entity);

  /**
   * Unsets the language for all Placelocator with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
