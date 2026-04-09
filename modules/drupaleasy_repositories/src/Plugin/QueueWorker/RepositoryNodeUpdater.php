<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\Attribute\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines 'drupaleasy_repositories_node_updater' queue worker.
 */
#[QueueWorker(
  id: 'drupaleasy_repositories_node_updater',
  title: new TranslatableMarkup('Repository node updater'),
  cron: ['time' => 60],
)]
final class RepositoryNodeUpdater extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('drupaleasy_repositories.service'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * The constructor.
   *
   * @param array<mixed> $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin ID for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService $repositoriesService
   *   The DrupalEasy repositories service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal core entity type manager service.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    mixed $pluginDefinition,
    protected DrupaleasyRepositoriesService $repositoriesService,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    // Ensure that we have a UID.
    if (isset($data['uid'])) {
      // Load the user object.
      $user = $this->entityTypeManager->getStorage('user')->load($data['uid']);
      // Ensure the user exists.
      if (isset($user)) {
        // Update the user's repository nodes!
        $this->repositoriesService->updateRepositories($user);
      }
    }
  }

}
