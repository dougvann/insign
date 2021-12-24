<?php

namespace Drupal\placelocator\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Placelocator entity.
 *
 * @ingroup placelocator
 *
 * @ContentEntityType(
 *   id = "placelocator_entity",
 *   label = @Translation("Placelocator"),
 *   bundle_label = @Translation("Placelocator type"),
 *   handlers = {
 *     "storage" = "Drupal\placelocator\PlacelocatorEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\placelocator\PlacelocatorEntityListBuilder",
 *     "views_data" = "Drupal\placelocator\Entity\PlacelocatorEntityViewsData",
 *     "translation" = "Drupal\placelocator\PlacelocatorEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\placelocator\Form\PlacelocatorEntityForm",
 *       "add" = "Drupal\placelocator\Form\PlacelocatorEntityForm",
 *       "edit" = "Drupal\placelocator\Form\PlacelocatorEntityForm",
 *       "delete" = "Drupal\placelocator\Form\PlacelocatorEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\placelocator\PlacelocatorEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\placelocator\PlacelocatorEntityAccessControlHandler",
 *   },
 *   base_table = "placelocator_entity",
 *   data_table = "placelocator_entity_field_data",
 *   revision_table = "placelocator_entity_revision",
 *   revision_data_table = "placelocator_entity_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer placelocator entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/placelocator_entity/{placelocator_entity}",
 *     "add-page" = "/admin/structure/placelocator_entity/add",
 *     "add-form" = "/admin/structure/placelocator_entity/add/{placelocator_entity_type}",
 *     "edit-form" = "/admin/structure/placelocator_entity/{placelocator_entity}/edit",
 *     "delete-form" = "/admin/structure/placelocator_entity/{placelocator_entity}/delete",
 *     "version-history" = "/admin/structure/placelocator_entity/{placelocator_entity}/revisions",
 *     "revision" = "/admin/structure/placelocator_entity/{placelocator_entity}/revisions/{placelocator_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/placelocator_entity/{placelocator_entity}/revisions/{placelocator_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/placelocator_entity/{placelocator_entity}/revisions/{placelocator_entity_revision}/delete",
 *     "translation_revert" = "/admin/structure/placelocator_entity/{placelocator_entity}/revisions/{placelocator_entity_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/placelocator_entity",
 *   },
 *   bundle_entity_type = "placelocator_entity_type",
 *   field_ui_base_route = "entity.placelocator_entity_type.edit_form"
 * )
 */
class PlacelocatorEntity extends RevisionableContentEntityBase implements PlacelocatorEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly,
    // make the placelocator_entity owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }

    if (!empty($this->get('geolocation')->value)) {
      $geolocation = $this->get('geolocation')->value;
      $this->set('field_meta_tags', serialize([
        'geo_position' => $geolocation,
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Placelocator entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Placelocator entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // Custom fields.
    // TODO: Improve.
    $fields['address'] = BaseFieldDefinition::create('address')
      ->setLabel(t('Address'))
      ->setDescription(t('Address of the placelocator'))
      ->setRevisionable(TRUE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('form', array(
        'type' => 'address_default',
        'settings' => array(
          'display_label' => TRUE,
        ),
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['geolocation'] = BaseFieldDefinition::create('geolocation')
      ->setLabel(t('Geolocation'))
      ->setDescription(t('Lat / long of the Placelocator'))
      ->setRevisionable(TRUE)
      ->setTranslatable(FALSE)
      ->setDisplayOptions('form', array(
        'type' => 'geolocation_latlng',
        'settings' => array(
          'display_label' => TRUE,
        ),
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // End custom fields.

    $fields['status']->setDescription(t('A boolean indicating whether the Placelocator is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
