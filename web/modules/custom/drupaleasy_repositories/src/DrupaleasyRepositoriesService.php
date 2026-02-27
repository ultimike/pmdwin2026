<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * DrupalEasy Repositories main service class.
 */
final class DrupaleasyRepositoriesService {
  use StringTranslationTrait;

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

  /**
   * Validate repository URLs.
   *
   * Validate the URLs are valid based on the enabled plugins and ensure they
   * haven't been added by another user.
   *
   * @param array $urls
   *   The urls to be validated.
   * @param int $uid
   *   The user id of the user submitting the URLs.
   *
   * @return string
   *   Errors reported by plugins.
   */
  public function validateRepositoryUrls(array $urls, int $uid): string {
    $errors = [];
    $repository_plugins = [];

    // Get IDs all DrupaleasyRepository plugins (enabled or not).
    $repository_plugin_ids = $this->configFactory->get('drupaleasy_repositories.settings')->get('repositories_plugins') ?? [];

    // Instantiate each enabled DrupaleasyRepository plugin (and confirm that
    // at least one is enabled).
    $atLeastOne = FALSE;
    foreach ($repository_plugin_ids as $repository_plugin_id) {
      if (!empty($repository_plugin_id)) {
        $atLeastOne = TRUE;
        $repository_plugins[] = $this->pluginManagerDrupaleasyRepositories->createInstance($repository_plugin_id);
      }
    }
    if (!$atLeastOne) {
      return 'There are no enabled repository plugins';
    }

    // Loop around each Repository URL and attempt to validate.
    foreach ($urls as $url) {
      if (is_array($url)) {
        if ($uri = trim($url['uri'])) {
          $is_valid_url = FALSE;
          // Check to see if the URI is valid for any enabled plugins.
          /** @var \Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesInterface $repository_plugin */
          foreach ($repository_plugins as $repository_plugin) {
            if ($repository_plugin->validate($uri)) {
              $is_valid_url = TRUE;
            }
          }
          if (!$is_valid_url) {
            $errors[] = $this->t('The repository url %uri is not valid.', ['%uri' => $uri]);
          }
        }
      }
    }

    if ($errors) {
      return implode(' ', $errors);
    }
    // No errors found.
    return '';
  }

}
