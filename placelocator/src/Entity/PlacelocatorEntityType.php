<?php

namespace Drupal\placelocator\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Placelocator type entity.
 *
 * @ConfigEntityType(
 *   id = "placelocator_entity_type",
 *   label = @Translation("Placelocator type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\placelocator\PlacelocatorEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\placelocator\Form\PlacelocatorEntityTypeForm",
 *       "edit" = "Drupal\placelocator\Form\PlacelocatorEntityTypeForm",
 *       "delete" = "Drupal\placelocator\Form\PlacelocatorEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\placelocator\PlacelocatorEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "placelocator_entity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "placelocator_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/placelocator_entity_type/{placelocator_entity_type}",
 *     "add-form" = "/admin/structure/placelocator_entity_type/add",
 *     "edit-form" = "/admin/structure/placelocator_entity_type/{placelocator_entity_type}/edit",
 *     "delete-form" = "/admin/structure/placelocator_entity_type/{placelocator_entity_type}/delete",
 *     "collection" = "/admin/structure/placelocator_entity_type"
 *   }
 * )
 */
class PlacelocatorEntityType extends ConfigEntityBundleBase implements PlacelocatorEntityTypeInterface {

  /**
   * The Placelocator type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Placelocator type label.
   *
   * @var string
   */
  protected $label;

}
