<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * DrupalEasy Repositories main service class.
 */
final class DrupaleasyRepositoriesService {

  /**
   * Constructs a DrupalEasy Repositories service class.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $pluginManagerDrupaleasyRepositories
   *   The DrupalEasy Repositories plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal core configuration factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The Drupal core logger factory.
   */
  public function __construct(
    protected PluginManagerInterface $pluginManagerDrupaleasyRepositories,
    protected ConfigFactoryInterface $configFactory,
    protected LoggerChannelFactoryInterface $loggerFactory,
  ) {}

  /**
   * Return help text based on enabled plugins.
   *
   * @return string
   *   The help text string.
   */
  public function getValidatorHelpText(): string {
    $repository_plugins = [];

    // Get list of plugins and their status from config.
    $repository_plugin_ids = $this->configFactory->get('drupaleasy_repositories.settings')->get('repositories_plugins') ?? [];

    // Loop around enabled plugins and instantiate each.
    foreach ($repository_plugin_ids as $repository_plugin_id) {
      if (!empty($repository_plugin_id)) {
        try {
          $repository_plugins[] = $this->pluginManagerDrupaleasyRepositories->createInstance($repository_plugin_id);
        }
        catch (PluginException) {
          // @todo Add t() to make fully safe.
          $this->loggerFactory->get('drupaleasy_repositories')->error("The plugin $repository_plugin_id failed to instantiate.");
          continue;
        }
      }
    }

    $help = [];
    // Loop around each plugin and call their validateHelpText() method.
    /** @var \Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesInterface $repository_plugin */
    foreach ($repository_plugins as $repository_plugin) {
      $help[] = $repository_plugin->validateHelpText();
    }

    // Concatenate and return help texts in a single string.
    return implode(' ', $help);
  }

}
