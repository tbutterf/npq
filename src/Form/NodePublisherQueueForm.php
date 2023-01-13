<?php

/**
 * @file
 * Contains \Drupal\npq\Form\NodePublisherQueueForm.
 */

namespace Drupal\npq\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Queue\QueueWorkerInterface;
use Drupal\Core\Queue\QueueWorkerManagerInterface;
use Drupal\Core\Queue\SuspendQueueException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

class NodePublisherQueueForm extends FormBase {

  /**
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * @var \Drupal\Core\Queue\QueueWorkerManagerInterface
   */
  protected $queueManager;

  /**
   * The database object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;


  /**
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   * @param \Drupal\Core\Queue\QueueWorkerManagerInterface $queue_manager
   * @param \Drupal\Core\Database\Connection $database
   */
  public function __construct(QueueFactory $queue_factory, QueueWorkerManagerInterface $queue_manager, Connection $database) {
    $this->queueFactory = $queue_factory;
    $this->queueManager = $queue_manager;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('queue'),
      $container->get('plugin.manager.queue_worker'),
      $container->get('database'),
    );
  }
  
  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'demo_form';
  }
  
  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $queue = $this->queueFactory->get('manual_node_publisher');

    $form['help'] = array(
      '#type' => 'markup',
      '#markup' => $this->t('Submitting this form will process the Manual Queue which contains @number items.', array('@number' => $queue->numberOfItems())),
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Process queue'),
      '#button_type' => 'primary',
    );
    
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var QueueInterface $queue */
    $queue = $this->queueFactory->get('manual_node_publisher', TRUE);
    /** @var QueueWorkerInterface $queue_worker */
    $queue->createQueue();
    $queue_worker = $this->queueManager->createInstance('manual_node_publisher');

    $items = $queue->numberOfItems();

    while($item = $queue->claimItem()) {
      try {
        $queue_worker->processItem($item->data);
        $queue->deleteItem($item);
      }
      catch (SuspendQueueException $e) {
        // If the worker indicates there is a problem with the whole queue,
        // release the item and skip to the next queue.
        $queue->releaseItem($item);
        break;
      }
      catch (\Exception $e) {
        // In case of any other kind of exception, log it and leave the item
        // in the queue to be processed again later.
        watchdog_exception('npq', $e);
      }
    }
  }

}
