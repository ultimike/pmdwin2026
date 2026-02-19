<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\drupaleasy_repositories\Attribute\DrupaleasyRepositories;
use Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginBase;
use Drupal\Component\Serialization\Yaml;

/**
 * Plugin implementation of the drupaleasy_repositories.
 */
#[DrupaleasyRepositories(
  id: 'yml_remote',
  label: new TranslatableMarkup('Remote .yml file'),
  description: new TranslatableMarkup('Remote .yml file that includes repository metadata.'),
  url_help_text: new TranslatableMarkup('https://anything.anything/anything/anything.yml (or http or yaml.)'),
)]
final class YmlRemote extends DrupaleasyRepositoriesPluginBase {

  /**
   * {@inheritdoc}
   */
  public function validate(string $uri): bool {
    $pattern = '|^https?://[a-zA-Z0-9.\-]+/[a-zA-Z0-9_\-.%/]+\.ya?ml$|';
    return preg_match($pattern, $uri) === 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getRepo(string $uri): array {
    // Get amd read the file.
    if ($file_contents = file_get_contents($uri)) {
      // Parse the file.
      $repo_info = Yaml::decode($file_contents);
      $machine_name = array_key_first($repo_info);
      $repo_metadata = reset($repo_info);
      $label = $repo_metadata['label'];
      $description = $repo_metadata['description'];
      $number_of_open_issues = $repo_metadata['num_open_issues'];
      return $this->mapToCommonFormat($machine_name, $label, $description, $number_of_open_issues, $uri);
    }
    return [];
  }

}
