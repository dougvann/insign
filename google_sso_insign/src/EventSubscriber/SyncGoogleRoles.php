<?php

namespace Drupal\google_sso_insign\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\social_api\Plugin\NetworkManager;
use Drupal\social_auth\AuthManager\OAuth2ManagerInterface;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\UserEvent;
use Drupal\social_auth\SettingsTrait;
use Drupal\social_auth\SocialAuthDataHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\user\Entity\Role;

/**
 * Manage "drupal roles" based on "google drupal roles".
 *
 * This example shows how to use the OAuth2 Manager provided by Social Auth
 * implementers to request more data.
 *
 * @package Drupal\google_sso_insign\EventSubscriber
 */
class SyncGoogleRoles implements EventSubscriberInterface {

  use SettingsTrait;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The data handler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  protected $dataHandler;

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  protected $networkManager;

  /**
   * The provider auth manager.
   *
   * @var \Drupal\social_auth\AuthManager\OAuth2ManagerInterface
   */
  protected $providerAuth;

  /**
   * SocialAuthSubscriber constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   Used to manage session variables.
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of the social auth implementer network plugin.
   * @param \Drupal\social_auth\AuthManager\OAuth2ManagerInterface $provider_auth
   *   Used to get the provider auth manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Used for accessing Drupal configuration.
   */
  public function __construct(MessengerInterface $messenger,
                              SocialAuthDataHandler $data_handler,
                              NetworkManager $network_manager,
                              OAuth2ManagerInterface $provider_auth,
                              ConfigFactoryInterface $config_factory) {

    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
    $this->dataHandler = $data_handler;
    $this->networkManager = $network_manager;
    $this->providerAuth = $provider_auth;
  }

  /**
   * {@inheritdoc}
   *
   * Returns an array of event names this subscriber wants to listen to.
   * For this case, we are going to subscribe for user login event and call the
   * methods to react on these events.
   */
  public static function getSubscribedEvents() {
    $events[SocialAuthEvents::USER_LOGIN] = ['onUserLogin'];
    $events[SocialAuthEvents::USER_CREATED] = ['onUserCreated'];

    return $events;
  }

  /**
   * Auto-activate new user based on "google drupal roles".
   *
   * @param \Drupal\social_auth\Event\UserEvent $event
   *   The Social Auth user event object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function onUserCreated(UserEvent $event) {

    // If user creation requires "administrator approval".
    if ($this->isApprovalRequired()) {

      // Get google drupal roles.
      $google_drupal_roles = $this->getGoogleDrupalRoles($event);

      // If we get some drupal roles from google, we automatically activate the
      // drupal account.
      // Unfortunately if the option "Who can register accounts?" is set to
      // "Visitors, but administrator approval is required" (here :
      // /admin/config/people/accounts) on the first login (ie account creation),
      // the user will be automatically disconnected. He will have to connect a
      // second time.
      // TODO: decide if we want to verify too if the "google drupal roles" match existing drupal roles.
      // TODO: decide if we want to auto enabled only some roles ("administrator" for example)
      // TODO: auto-redirect to user/login/google instead of asking user to do it ?
      if (!empty($google_drupal_roles)) {
        /*
         * @var Drupal\user\UserInterface $user
         */
        $user = $event->getUser();

        $user->activate();
        $user->save();

        $markup = new TranslatableMarkup('Your account has been created. It has been activated automatically because of your google informations.<br>'.
          'You have to <a href="@link">log in again</a>.', array('@link' => Url::fromRoute('social_auth_google.redirect_to_google')->toString()));

        $this->messenger->addStatus($markup);
      }
    }
  }

  /**
   * Retrieve "google drupal roles" on each user login and update "drupal roles"
   * according to.
   *
   * @param \Drupal\social_auth\Event\UserEvent $event
   *   The Social Auth user event object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function onUserLogin(UserEvent $event) {

    // Get google drupal roles.
    $google_drupal_roles = $this->getGoogleDrupalRoles($event);

    $this->messenger->addStatus('Google drupal Roles: ' . implode(', ', $google_drupal_roles));

    /*
     * @var Drupal\user\UserInterface $user
     */
    $drupal_user = $event->getUser();

    // Remove all existing roles.
    // TODO: Decide if we want to remove old roles or not ?
    $old_roles = $drupal_user->getRoles(true);
    foreach($old_roles as $role) {
      $drupal_user->removeRole($role);
    }
    $this->messenger->addStatus('Removed old Roles: ' . implode(', ', $old_roles));

    // Try to load drupal roles based on google roles.
    // TODO: Decide if we want to raise an error if there is "google drupal roles" without correlation with "drupal roles".
    $roles =  Role::loadMultiple($google_drupal_roles);
    // Add roles from google.
    foreach ($roles as $role) {
      $drupal_user->addRole($role->id());
    }
    $drupal_user->save();

    $this->messenger->addStatus('Added drupal Roles: ' . implode(', ', $drupal_user->getRoles()));
  }

  /**
   * Get "google drupal roles" by making an API call based on
   * current google user.
   *
   * @param \Drupal\social_auth\Event\UserEvent $event
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getGoogleDrupalRoles(UserEvent $event) {

    // Sets prefix.
    $this->dataHandler->setSessionPrefix($event->getPluginId());

    // Gets client object.
    $client = $this->networkManager->createInstance($event->getPluginId())->getSdk();

    // Create provider OAuth2 manager.
    // Can also use $client directly and request data using the library/SDK.
    $this->providerAuth->setClient($client)
      ->setAccessToken($this->dataHandler->get('access_token'));

    // Get user info.
    $userInfo = $this->providerAuth->getUserInfo();

    // Test this API call here : https://developers.google.com/admin-sdk/directory/v1/reference/users/get?apix_params=%7B%22userKey%22%3A%22joris.mulliez%40insign.fr%22%2C%22projection%22%3A%22full%22%2C%22viewType%22%3A%22domain_public%22%7D
    $data = $this->providerAuth->requestEndPoint(
      'GET',
      '/admin/directory/v1/users/' . urlencode($userInfo->getEmail()) .
      '?' . http_build_query([
        'projection' => 'full',
        'viewType' => 'domain_public',
      ])
    );

    $google_drupal_roles = [];
    // TODO: Make this "custom schema path" configurable ? (if we want to change this custom schema on google side, avoid re-ploy this module on every websites)
    if (!empty($data['customSchemas']['Drupal']['Roles'])) {

      foreach ($data['customSchemas']['Drupal']['Roles'] as $google_role) {
        $google_drupal_roles[] = $google_role['value'];
      }
    }

    return $google_drupal_roles;
  }

}
