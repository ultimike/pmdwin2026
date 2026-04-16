<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\Drush\Commands;

use Consolidation\OutputFormatters\FormatterManager;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Utility\Token;
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
   * @param \Drupal\Core\Utility\Token $token
   *   The Drupal core token service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The Psr logger service.
   * @param \Consolidation\OutputFormatters\FormatterManager $formatterManager
   *   The Formatter manager service.
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService $repositoriesService
   *   The DrupalEasy repositories general service class.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal core entity type manager service.
   * @param \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesBatch $batch
   *   The DrupalEasy repositories batch service class.
   */
  public function __construct(
    private readonly Token $token,
    private readonly LoggerInterface $logger,
    private readonly FormatterManager $formatterManager,
    private readonly DrupaleasyRepositoriesService $repositoriesService,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly DrupaleasyRepositoriesBatch $batch,
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
    $this->logger->debug('Hello from your custom Drush command.');
    return 'Hey now.';
  }

}
