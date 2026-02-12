<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\drupaleasy_repositories\Attribute\DrupaleasyRepositories;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesPluginBase;

/**
 * Plugin implementation of the drupaleasy_repositories.
 */
#[DrupaleasyRepositories(
  id: 'yml_remote',
  label: new TranslatableMarkup('Yml remote'),
  description: new TranslatableMarkup('Foo description.'),
)]
final class Foo extends DrupaleasyRepositoriesPluginBase {

}
