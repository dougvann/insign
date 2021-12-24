<?php

namespace Drupal\share_price\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form configuring BSPP.
 */
class PriceSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'share_price_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'share_price.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('share_price.settings');
    $form['euronext_url'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Euronext Url API'),
      '#default_value' => $config->get('euronext_url'),
    ];
    $form['euronext_url_charts'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Euronext Url Image charts'),
      '#default_value' => $config->get('euronext_url_charts'),
    ];
    $form['stocks_cache'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Stocks cachability'),
    ];
    $form['stocks_cache']['default_cache_age_validity'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default cache validity'),
      '#default_value' => $config->get('default_cache_age_validity'),
      '#description' => $this->t("This value is use to avoid multiple Stocks webservice call during this seconds. Must be fill in seconds.<br>You can leave it blank if you don't want any cache."),
    ];
    $form['stocks_cache']['error_cache_age_validity'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error cache validity'),
      '#default_value' => $config->get('error_cache_age_validity'),
      '#description' => $this->t('This value will be use in case of Stocks webservice error. Must be fill in seconds.<br>You can leave it blank for no cache.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory
      ->getEditable('share_price.settings')
      ->set('euronext_url', $form_state->getValue('euronext_url'))
      ->set('euronext_url_charts', $form_state->getValue('euronext_url_charts'))
      ->set('default_cache_age_validity', (int) $form_state->getValue('default_cache_age_validity'))
      ->set('error_cache_age_validity', (int) $form_state->getValue('error_cache_age_validity'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
