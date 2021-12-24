<?php

namespace Drupal\placelocator;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Placelocator entity.
 *
 * @see \Drupal\placelocator\Entity\PlacelocatorEntity.
 */
class PlacelocatorEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\placelocator\Entity\PlacelocatorEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished placelocator entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published placelocator entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit placelocator entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete placelocator entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add placelocator entities');
  }

}
