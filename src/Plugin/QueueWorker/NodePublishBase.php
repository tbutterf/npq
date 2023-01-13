<?php

/**
 * @file
 * Contains Drupal\npq\Plugin\QueueWorker\NodePublishBase.php
 */

namespace Drupal\npq\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides base functionality for the NodePublish Queue Workers.
 */
abstract class NodePublishBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new NodePublishBase.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The node storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
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
    );
  }

  /**
   * Publishes a node.
   *
   * @param NodeInterface $node
   * @return int
   */
  protected function publishNode($node) {
    $node->setPublished(TRUE);
    return $node->save();
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    /** @var NodeInterface $node */
    $node = $this->entityTypeManager->getStorage('node')->load($data->nid);
    if (!$node->isPublished() && $node instanceof NodeInterface) {
      return $this->publishNode($node);
    }
  }

}
