<?php

namespace Drupal\share_price\Controller;

use Drupal\block\Entity\Block;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class ShareController.
 */
class ShareController extends ControllerBase
{

  /**
   * Index.
   *
   * @return array
   *   Return render array.
   */
  public function index()
  {

    $config = $this->config('share_price.settings');

    $page = [];
    $page['#theme'] = 'share_price_full_page';
    $page['#attached']['library'] = [
      'share_price/share_price_page',
    ];
    $page['#euronext_url_charts'] = $config->get('euronext_url_charts');
    $page['#attached']['drupalSettings']['share_price']['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();;
    $page['#attached']['drupalSettings']['share_price']['euronext_url'] = $config->get('euronext_url');
    return $page;
  }
}
