<?php

/**
 * @file
 * Contains batch operations for DrupalEasy repositories.
 */

declare(strict_types=1);

/**
 * Callback function for updating user repositories in a Batch.
 *
 * @param int $uid
 *   The User ID.
 * @param array|\ArrayAccess $context
 *   The Batch API context.
 */
function drupaleasy_update_repositories_batch_operation(int $uid, array|\ArrayAccess &$context): void {
  /** @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch $drupaleasy_repositories_batch */
  $drupaleasy_repositories_batch = \Drupal::service('drupaleasy_repositories.batch');
  $drupaleasy_repositories_batch->updateRepositoriesBatch($uid, $context);
}

/**
 * The "finished" callback for updating user repositories in a Batch.
 *
 * @param bool $success
 *   TRUE is successful.
 * @param array $results
 *   The Batch results array.
 * @param array $operations
 *   The Batch operations array.
 */
function drupaleasy_update_all_repositories_finished(bool $success, array $results, array $operations): void {
  /** @var \Drupal\Core\Messenger\MessengerInterface $messenger */
  $messenger = \Drupal::service('messenger');
  $count = count($results['uids']);
  if ($success) {
    if ($count > 0) {
      $translation = \Drupal::translation();
      $messenger->addStatus($translation->formatPlural($count, 'Updated repository nodes for one user.', 'Updated repository nodes for @count users.'));
    }
    else {
      $messenger->addWarning(t('Zero repositories updated.'));
    }
  }
  else {
    $messenger->addError(t('Finished with an error.'));
  }

}
