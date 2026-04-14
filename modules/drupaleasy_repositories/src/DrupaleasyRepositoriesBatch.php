<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Batch service class to integration with Batch API.
 */
final class DrupaleasyRepositoriesBatch {
  use StringTranslationTrait;

  /**
   * The constructor.
   *
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService $repositoriesService
   *   The DrupalEasy repositories service class.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal core entity type manager service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extensionListModule
   *   The Drupal core module extension list service.
   */
  public function __construct(
    private readonly DrupaleasyRepositoriesService $repositoriesService,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ModuleExtensionList $extensionListModule,
  ) {}

  /**
   * Updates all user repositories using Batch API.
   */
  public function updateAllRepositories(): void {
    // $operations = [];
    // Get a list of all active users with field_repository_url data.
    $query = $this->entityTypeManager->getStorage('user')->getQuery();
    $query->condition('status', 1);
    $query->condition('field_repository_url', 0, 'IS NOT NULL');
    $users = $query->accessCheck(FALSE)->execute();

    $batch_builder = (new BatchBuilder())
      ->setTitle($this->t('Update all repository nodes'))
      // setFile() needs to come before setFinishCallback!!!!!
      ->setFile($this->extensionListModule->getPath('drupaleasy_repositories') . '/drupaleasy_repositories.batch.inc')
      ->setFinishCallback('drupaleasy_update_all_repositories_finished')
      ->setInitMessage($this->t('Here we go!'));

    foreach ($users as $user) {
      // $operations[] = ['drupaleasy_update_repositories_batch_operation', [$user]];
      $batch_builder->addOperation('drupaleasy_update_repositories_batch_operation', [$user]);
    }

    // $batch = [
    //   'operations' => $operations,
    //   'finished' => 'drupaleasy_update_all_repositories_finished',
    //   'file' => $this->extensionListModule->getPath('drupaleasy_repositories') . '/drupaleasy_repositories.batch.inc',
    // ];
    $batch = $batch_builder->toArray();

    batch_set($batch);
  }

  /**
   * Batch process method for updating user repositories.
   *
   * @param int $uid
   *   The User ID.
   * @param array|\ArrayAccess $context
   *   The Batch API context.
   */
  public function updateRepositoriesBatch(int $uid, array|\ArrayAccess &$context): void {
    // Ensure that we have a UID.
    if (isset($uid)) {
      // Load the user object.
      $user = $this->entityTypeManager->getStorage('user')->load($uid);
      // Ensure the user exists.
      if (isset($user)) {
        // Update the user's repository nodes!
        $this->repositoriesService->updateRepositories($user);
        $context['results']['uids'][] = $uid;
        $context['message'] = $this->t('Updated repositories belonging to "@username".', ['@username' => $user->label()]);
      }
    }
  }

}
