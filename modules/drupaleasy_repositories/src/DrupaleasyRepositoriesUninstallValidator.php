<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;

/**
 * Ensure that no repository content exists before disabling module.
 */
final class DrupaleasyRepositoriesUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * Constructs the object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function validate($module): array {
    $reasons = [];
    if ($module === 'drupaleasy_repositories') {
      if ($this->hasRepositoryNodes()) {
        $reasons[] = $this->t('To uninstall DrupalEasy Repositories, delete all content that has the Repository content type.');
      }
    }
    return $reasons;
  }

  /**
   * Determines if there are any repository nodes or not.
   *
   * @return bool
   *   TRUE if there are repository nodes, FALSE otherwise.
   */
  protected function hasRepositoryNodes(): bool {
    try {
      $nodes = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'repository')
        ->accessCheck(FALSE)
        ->range(0, 1)
        ->execute();
      return !empty($nodes);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException) {
      return FALSE;
    }
  }

}
