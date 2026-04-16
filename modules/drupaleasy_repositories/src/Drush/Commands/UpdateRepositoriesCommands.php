<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\Drush\Commands;

use Consolidation\OutputFormatters\FormatterManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Formatters\FormatterTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\queue_ui\QueueUIBatch;

/**
 * Custom Drush (Symfony) command to update DrupalEasy repositories.
 */
#[AsCommand(
  name: self::NAME,
  description: 'Update user repositories',
  aliases: ['ur'],
)]
#[CLI\Formatter(returnType: 'string', defaultFormatter: 'string')]
final class UpdateRepositoriesCommands extends Command {

  use AutowireTrait;
  use FormatterTrait;

  const string NAME = 'der:update-repositories';

  /**
   * Constructs an UpdateRepositories object.
   *
   * @param \Consolidation\OutputFormatters\FormatterManager $formatterManager
   *   The formatter manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The Psr logger service.
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService $repositoriesService
   *   The DrupalEasy repositories general service class.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal core entity type manager service.
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch $batch
   *   The DrupalEasy repositories batch service class.
   * @param \Drupal\queue_ui\QueueUIBatch $queueUiBatch
   *   The Queue UI contrib module service class.
   */
  public function __construct(
    private readonly FormatterManager $formatterManager,
    private readonly LoggerInterface $logger,
    private readonly DrupaleasyRepositoriesService $repositoriesService,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    //private readonly DrupaleasyRepositoriesBatch $batch,
    private readonly QueueUIBatch $queueUiBatch,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    $this
      ->addOption(name: 'uid', mode: InputOption::VALUE_OPTIONAL, description: 'The user ID of the user to update.')
      ->addUsage(usage: 'der:update-repositories')
      ->addUsage(usage: 'der:update-repositories --uid=2')
      ->addUsage(usage: 'ur')
      ->addUsage(usage: 'ur --uid=42');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $data = $this->doExecute($input, $output);
    $this->writeFormattedOutput($input, $output, $data);
    return self::SUCCESS;
  }

  /**
   * Non-boilerplate execution code for the command.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The command input stuff.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The command output stuff.
   *
   * @return string
   *   The text to be output by the command.
   */
  protected function doExecute(InputInterface $input, OutputInterface $output): string {
    $uid = NULL;
    $raw_uid = $input->getOption('uid');
    if (!is_null($raw_uid)) {
      $uid = (int) $raw_uid;
    }
    if ($uid > 0) {
      $account = $this->entityTypeManager->getStorage('user')->load($uid);
      if ($account) {
        // Update the user's repository nodes directly.
        $this->repositoriesService->updateRepositories($account);
        return (string) dt('Repositories for uid=@uid updated.', ['@uid' => $uid]);
      }
      $this->logger->error(dt('uid=@uid doesn\'t exist.', ['@uid' => $raw_uid]));
      return '';
    }

    if ($uid === 0) {
      $this->logger->error(dt('Invalid uid=@uid provided.', ['@uid' => $raw_uid]));
      return '';
    }

    // Create the batch and submit for processing.
    //$this->batch->updateAllRepositories(TRUE);

    // Create queue items.
    $this->repositoriesService->createQueueItems();
    // Call Queue UI Batch to run the queue items as a batch process.
    $this->queueUiBatch->batch(['drupaleasy_repositories_node_updater']);
    // Ask Drush to process the batch.
    drush_backend_batch_process();

    return 'Multiple repositories updated.';
  }

}
