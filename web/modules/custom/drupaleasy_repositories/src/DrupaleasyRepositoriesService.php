<?php

declare(strict_types=1);

namespace Drupal\drupaleasy_repositories;

/**
 * @todo Add class description.
 */
final class DrupaleasyRepositoriesService {

  /**
   * Return help text based on enabled plugins.
   *
   * @return string
   *   The help text string.
   */
  public function getValidatorHelpText(): string {
    // Get list of available plugins from plugin manager (service).
    // Get list of enabled plugins based on config (~service).
    // Loop around enabled plugins and call their validateHelpText() methods.
    // Concatenate help texts into a single string.
    // Return the help text string.
    return '';
  }

}
