<?php

/**
 * @file
 * Contains placelocator_entity.page.inc.
 *
 * Page callback for Placelocator entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Placelocator templates.
 *
 * Default template: placelocator_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_placelocator_entity(array &$variables) {
  // Fetch PlacelocatorEntity Entity Object.
  $placelocator_entity = $variables['elements']['#placelocator_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
