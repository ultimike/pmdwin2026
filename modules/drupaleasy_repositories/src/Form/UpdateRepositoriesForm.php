<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Drupal\queue_ui\QueueUIBatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a DrupalEasy repositories form.
 */
final class UpdateRepositoriesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('drupaleasy_repositories.service'),
      $container->get('drupaleasy_repositories.batch'),
      $container->get('entity_type.manager'),
      $container->get('queue_ui.batch')
    );
  }

  /**
   * Constructs an UpdateRepositoriesForm object.
   *
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService $repositoriesService
   *   The DrupalEasy repositories service class.
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch $repositoriesBatch
   *   The DrupalEasy repositories batch service class.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal core entity type manager.
   * @param \Drupal\queue_ui\QueueUIBatchInterface $queueUIBatch
   *   The Drupal contrib module Queue UI Batch service class.
   */
  public function __construct(
    protected DrupaleasyRepositoriesService $repositoriesService,
    protected DrupaleasyRepositoriesBatch $repositoriesBatch,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected QueueUIBatchInterface $queueUIBatch,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'drupaleasy_repositories_update_repositories';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['uid'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Username'),
      '#description' => $this->t('Leave blank to update all repository nodes for all users.'),
      '#required' => FALSE,
      '#target_type' => 'user',
      '#selection_settings' => [
        'include_anonymous' => FALSE,
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Go'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Check if a UID has been entered.
    if ($uid = $form_state->getValue('uid')) {
      $account = $this->entityTypeManager->getStorage('user')->load($uid);
      if ($account) {
        // Update the user's repository nodes directly.
        $this->repositoriesService->updateRepositories($account);
        $this->messenger()->addMessage($this->t('Repositories updated.'));
      }
    }
    else {
      // Create the batch and submit for processing.
      //$this->repositoriesBatch->updateAllRepositories();

      $this->repositoriesService->createQueueItems();

      // Call Queue UI Batch to run the queue items as a batch process.
      $this->queueUIBatch->batch(['drupaleasy_repositories_node_updater']);
    }
  }

}
