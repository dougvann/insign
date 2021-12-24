<?php

namespace Drupal\share_price\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Provides a 'StocksInvestorsBlock' block.
 *
 * @Block(
 *  id = "stocks_investors_block",
 *  admin_label = @Translation("Stocks investors block"),
 * )
 */
class StocksInvestorsBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entityTypeManager,
    LanguageManagerInterface $languageManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['webservice_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Webservice url'),
      '#default_value' => isset($config['webservice_url']) ? $config['webservice_url'] : '',
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['currency'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Currency'),
      '#description' => $this->t('Currency code as describe here : https://www.currency-iso.org/en/home/tables/table-a1.html (example : EUR)'),
      '#default_value' => isset($config['currency']) ? $config['currency'] : 'EUR',
      '#required' => TRUE,
    ];

    $form['link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link'),
      '#default_value' => isset($config['link']) ? $config['link'] : '',
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['currency'] = $form_state->getValue('currency');
    $this->configuration['webservice_url'] = $form_state->getValue('webservice_url');
    $this->configuration['link'] = $form_state->getValue('link');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $config = $this->getConfiguration();

    $link = '#';
    if (!empty($config['link'])) {
      $link = $config['link'];
    }
    return [
      '#theme' => 'share_price_investors',
      '#link' => $link,
      '#attached' => [
        'library' => [
          'share_price/share_price_investors',
        ],
        'drupalSettings' => [
          'SharePriceInvestors' => [
            'language' => $this->languageManager->getCurrentLanguage()->getId(),
            'currencyCode' => $config['currency'],
            'webserviceUrl' => $config['webservice_url'],
          ],
        ],
      ],
    ];
  }

}
