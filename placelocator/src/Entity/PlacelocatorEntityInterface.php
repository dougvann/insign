<?php

namespace Drupal\placelocator\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Placelocator entities.
 *
 * @ingroup placelocator
 */
interface PlacelocatorEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Placelocator name.
   *
   * @return string
   *   Name of the Placelocator.
   */
  public function getName();

  /**
   * Sets the Placelocator name.
   *
   * @param string $name
   *   The Placelocator name.
   *
   * @return \Drupal\placelocator\Entity\PlacelocatorEntityInterface
   *   The called Placelocator entity.
   */
  public function setName($name);

  /**
   * Gets the Placelocator creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Placelocator.
   */
  public function getCreatedTime();

  /**
   * Sets the Placelocator creation timestamp.
   *
   * @param int $timestamp
   *   The Placelocator creation timestamp.
   *
   * @return \Drupal\placelocator\Entity\PlacelocatorEntityInterface
   *   The called Placelocator entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Placelocator revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Placelocator revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\placelocator\Entity\PlacelocatorEntityInterface
   *   The called Placelocator entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Placelocator revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Placelocator revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\placelocator\Entity\PlacelocatorEntityInterface
   *   The called Placelocator entity.
   */
  public function setRevisionUserId($uid);

}
