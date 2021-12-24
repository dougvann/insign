<?php

namespace Drupal\placelocator;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Placelocator entities.
 *
 * @ingroup placelocator
 */
class PlacelocatorEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Placelocator ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\placelocator\Entity\PlacelocatorEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.placelocator_entity.edit_form',
      ['placelocator_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
