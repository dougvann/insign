<?php

namespace Drupal\entity_clone_template\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\entity_clone\Event\EntityCloneEvents;
use Drupal\entity_clone\Event\EntityCloneEvent;

/**
 * Add an event subscriber to alter data after an entity is cloned.
 */
class EntityCloneTemplateSubscriber implements EventSubscriberInterface {

  /**
   * Subscribed Events trigger.
   */
  public static function getSubscribedEvents() {

    $events[EntityCloneEvents::POST_CLONE][] = ['postEntityClone'];

    return $events;
  }

  /**
   * Alter entity before being created when cloned to alter some values.
   *
   * @param \Drupal\entity_clone\Event\EntityCloneEvent $event
   *   An event.
   */
  public function postEntityClone(EntityCloneEvent $event) {

    $clonedEntity = $event->getClonedEntity();
    if ($clonedEntity->hasField('entity_clone_template_active')) {
      $clonedEntity->set('entity_clone_template_active', 0);
      $clonedEntity->set('entity_clone_template_image', []);
      $clonedEntity->save();
    }
  }

}
