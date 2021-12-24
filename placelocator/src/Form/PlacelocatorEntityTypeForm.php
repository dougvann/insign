<?php

namespace Drupal\placelocator\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PlacelocatorEntityTypeForm.
 */
class PlacelocatorEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $placelocator_entity_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $placelocator_entity_type->label(),
      '#description' => $this->t("Label for the Placelocator type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $placelocator_entity_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\placelocator\Entity\PlacelocatorEntityType::load',
      ],
      '#disabled' => !$placelocator_entity_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $placelocator_entity_type = $this->entity;
    $status = $placelocator_entity_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Placelocator type.', [
          '%label' => $placelocator_entity_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Placelocator type.', [
          '%label' => $placelocator_entity_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($placelocator_entity_type->toUrl('collection'));
  }

}
