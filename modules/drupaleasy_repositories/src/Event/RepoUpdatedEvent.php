<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\node\NodeInterface;

/**
 * Event that is fired when a repository is created/updated/deleted.
 */
class RepoUpdatedEvent extends Event {

  /**
   * Constructs the object.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node that was created/updated/deleted.
   * @param string $action
   *   The action performed on the node.
   */
  public function __construct(
    public NodeInterface $node,
    public string $action,
  ) {}

}
