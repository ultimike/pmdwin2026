<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_notify\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\drupaleasy_repositories\Event\RepoUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * DrupalEasy Notify event subscriber for repository node events.
 */
final class DrupaleasyNotifySubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * Constructs a DrupaleasyNotifySubscriber object.
   */
  public function __construct(
    private readonly MessengerInterface $messenger,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      RepoUpdatedEvent::class => ['onRepoUpdated'],
    ];
  }

  /**
   * Handles RepoUpdateEvent.
   *
   * @param \Drupal\drupaleasy_repositories\Event\RepoUpdatedEvent $event
   *   The event to be handled.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function onRepoUpdated(RepoUpdatedEvent $event): void {
    $this->messenger->addStatus($this->t('The repo named @repo_name has been @action (@repo_url.) The repo node is owned by @author_name (@author_id.)', [
      '@repo_name' => $event->node->getTitle(),
      '@action' => $event->action,
      '@repo_url' => $event->node->toLink()->getUrl()->toString(),
      '@author_name' => $event->node->getOwner()->label(),
      '@author_id' => $event->node->getOwner()->id(),
    ]));
  }

}
