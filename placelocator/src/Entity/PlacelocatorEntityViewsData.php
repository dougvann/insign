<?php

namespace Drupal\placelocator\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Placelocator entities.
 */
class PlacelocatorEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
