<?php

namespace Drupal\drupaleasy_repositories\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Render\Element;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Drupal\user\Entity\User;

/**
 * Hook implementations for drupal.
 */
class DrupaleasyRepositoriesHooks {
  use StringTranslationTrait;

  public function __construct(
    protected readonly DrupaleasyRepositoriesService $repositoryService,
  ) {}

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_user_form_alter')]
  public function formUserFormAlter(array &$form, FormStateInterface $form_state): void {
    if (!empty($form['field_repository_url']['widget'])) {
      foreach (Element::children($form['field_repository_url']['widget']) as $el_index) {
        $form['field_repository_url']['widget'][$el_index]['#process'][] = [$this, 'urlHelpText'];
      }
    }

    $form['#validate'][] = [$this, 'urlValidate'];
    $form['actions']['submit']['#submit'][] = [$this, 'urlSubmit'];
  }

  /**
   * Custom function to populate Repository URL descriptions.
   *
   * @param array<mixed> $element
   *   A render element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array<mixed> $form
   *   The form array.
   *
   * @return array<mixed>
   *   A render element.
   */
  public function urlHelpText(array $element, FormStateInterface $form_state, array &$form): array {
    $help_text = $this->repositoryService->getValidatorHelpText();
    if ($help_text) {
      $element['uri']['#description'] = $this->t('Valid URLs are: %help_text', ['%help_text' => $help_text]);
    }
    else {
      $element['uri']['#description'] = $this->t('No repository plugins are enabled. Contact site administrator.');
    }
    $element['uri']['#description_display'] = 'before';
    return $element;
  }

  /**
   * Helper method to validate repository URLs.
   *
   * @param array<mixed> $element
   *   The form element being validated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function urlValidate(array &$element, FormStateInterface $form_state): void {
    /** @var \Drupal\Core\Entity\EntityFormInterface $theFormObject */
    $theFormObject = $form_state->getFormObject();
    $uid = $theFormObject->getEntity()->id();
    // If the user doesn't exist, then use the anonymous user ID (0).
    $uid = is_null($uid) ? 0 : $uid;

    $error = $this->repositoryService->validateRepositoryUrls($form_state->getValue('field_repository_url'), $uid);

    if ($error) {
      $form_state->setErrorByName(
        'field_repository_url',
        $error,
      );
    }
  }

  /**
   * Helper method when submitting repository URLs.
   *
   * @param array<mixed> $element
   *   The form element being validated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function urlSubmit(array &$element, FormStateInterface $form_state): void {
    $account = User::load($form_state->getValue('uid'));
    if (!is_null($account)) {
      $this->repositoryService->updateRepositories($account);
    }
  }

}
