<?php

declare(strict_types=1);

namespace Drupal\Tests\drupaleasy_repositories\Unit;

use Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories\Github;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\key\KeyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Drupal\key\KeyRepositoryInterface;
use Github\Api\Repo;
use Github\Client;

/**
 * Test description.
 */
#[Group('drupaleasy_repositories')]
final class GithubTest extends UnitTestCase {

  /**
   * The Github plugin.
   *
   * @var \Drupal\drupaleasy_repositories\Plugin\DrupaleasyRepositories\Github
   */
  protected Github $github;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected MessengerInterface|MockObject $messenger;

  /**
   * The Key repository service.
   *
   * @var \Drupal\key\KeyRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected KeyRepositoryInterface|MockObject $keyRepository;

  /**
   * The mocked Github client.
   *
   * @var \Github\Client|\PHPUnit\Framework\MockObject\MockObject
   */
  protected Client|MockObject $client;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->messenger = $this->createMock(MessengerInterface::class);
    $this->keyRepository = $this->createMock(KeyRepositoryInterface::class);

    $repo_test_array = [
      'full_name' => 'ddev/ddev',
      'name' => 'ddev',
      'description' => 'This is the ddev repository.',
      'open_issues_count' => 6,
      'html_url' => 'https://github.com/ddev/ddev',
    ];

    $repo = $this->createMock(Repo::class);
    $this->client = $this->createMock(Client::class);
    $this->client->expects($this->any())
      ->method('api')
      ->willReturn($repo);
    $repo->expects($this->any())
      ->method('show')
      ->willReturn($repo_test_array);

    $this->github = new Github([], 'github', [], $this->messenger, $this->keyRepository);
  }

  /**
   * Test the getRepo() method.
   */
  public function testGetRepo(): void {
    $repo = $this->github->getRepo('https://github.com/vendor/name', $this->client);
    $machine_name = array_key_first($repo);

    // Assertions to confirm that the $repo array is what we expect.
    self::assertEquals('ddev/ddev', $machine_name);
    $repo = reset($repo);
    self::assertEquals('ddev', $repo['label']);
    self::assertEquals('This is the ddev repository.', $repo['description']);
    self::assertEquals(6, $repo['num_open_issues']);
    self::assertEquals('github', $repo['source']);
    self::assertEquals('https://github.com/ddev/ddev', $repo['url']);
  }

}
