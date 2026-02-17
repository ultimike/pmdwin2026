<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\DrupaleasyRepositories;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for drupaleasy_repositories plugins.
 */
abstract class DrupaleasyRepositoriesPluginBase extends PluginBase implements DrupaleasyRepositoriesInterface {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function validate(string $uri): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateHelpText(): string {
    return (string) $this->pluginDefinition['url_help_text'];
  }

  /**
   * Build array of a single repository with common array key names.
   *
   * @param string $machine_name
   *   The repository machine name.
   * @param string $label
   *   The user-facing name of the repository.
   * @param string|null $description
   *   The repository description.
   * @param int $num_open_issues
   *   The number of open issues.
   * @param string $url
   *   The URL where the repository can be found.
   *
   * @return array<string, array<string, string|int>>
   *   A repository info array in a common format.
   */
  protected function mapToCommonFormat(string $machine_name, string $label, string|null $description, int $num_open_issues, string $url): array {
    $repo_info[$machine_name] = [
      'label' => $label,
      'description' => $description,
      'num_open_issues' => $num_open_issues,
      'source' => $this->getPluginId(),
      'url' => $url,
    ];
    return $repo_info;
  }

}
