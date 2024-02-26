<?php

namespace Drupal\randstad_annual\Plugin\Block;

use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Randstad Registration Count block' block.
 *
 * @Block(
 *   id = "randstad_registration_count_block",
 *   admin_label = @Translation("Registration Count block"),
 *   category = @Translation("Randstad Annual blocks")
 * )
 */
class RegistrationCountBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node_query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery();

    // Get count of all available registrations.
    $query = $node_query
      ->condition('status', 1)
      ->condition('type', 'registration');

    $count = $query->count()->execute();

    $build['content'] = [
      '#type' => 'inline_template',
      '#template' => '<div class="block-filter-text-source"><span style="font-size: medium">Current count of registrations: <b>{{ count }}</b></span></div>',
      '#context' => [
        'count' => $count,
      ],
      '#cache' => [
        'tags' => [
          'node_type:registration',
        ],
      ],
    ];

    // Other modules can alter the block build process.
    $this->moduleHandler->alter('randstad_annual_registration_count_block', $count, $node_query, $query);

    return $build;
  }

}
