<?php

declare(strict_types=1);

namespace Drupal\Tests\drupaleasy_repositories\Kernel;

use Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginManager;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories\YmlRemote;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;
use Drupal\Tests\drupaleasy_repositories\Traits\RepositoryContentTypeTrait;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Test description.
 */
#[Group('drupaleasy_repositories')]
final class DrupaleasyRepositoriesServiceTest extends KernelTestBase {
  use RepositoryContentTypeTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'drupaleasy_repositories',
    'node',
    'field',
    'user',
    'link',
    'text',
  ];

  /**
   * The DrupalEasy repositories service class.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService
   */
  protected DrupaleasyRepositoriesService $drupaleasyRepositoriesService;

  /**
   * The DrupalEasy repositories plugin manager.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositories\DrupaleasyRepositoriesPluginManager
   */
  protected DrupaleasyRepositoriesPluginManager $manager;

  /**
   * The Yml remote plugin.
   *
   * @var \Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories\YmlRemote
   */
  protected YmlRemote $ymlRemote;

  /**
   * The admin user property.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupaleasyRepositoriesService = $this->container->get('drupaleasy_repositories.service');
    // $this->manager = $this->container->get('plugin.manager.drupaleasy_repositories');
    // $this->ymlRemote = $this->manager->createInstance('yml_remote');
    // Enable the .yml repository plugin.
    $config = $this->config('drupaleasy_repositories.settings');
    $config->set('repositories_plugins', ['yml_remote' => 'yml_remote']);
    $config->save();

    $this->createRepositoryContentType();

    $aquaman_repo = $this->getTestRepo('aquaman-repository');

    $this->adminUser = User::create();
    $this->adminUser->save();

    $node = Node::create([
      'type' => 'repository',
      'uid' => $account->id(),
      'title' => $repo_info['label'],
      'field_description' => $repo_info['description'],
      'field_machine_name' => $key,
      'field_number_of_open_issues' => $repo_info['num_open_issues'],
      'field_source' => $repo_info['source'],
      'field_url' => $repo_info['url'],
      'field_hash' => $hash,
    ]);
    $node->save();
  }

  /**
   * Test callback.
   */
  public function testValidateHelpText(): void {
    self::assertEquals($this->drupaleasyRepositoriesService->getValidatorHelpText(), 'https://anything.anything/anything/anything.yml (or http or yaml.)');
  }

}
