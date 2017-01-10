<?php

namespace Drupal\dc_discussion\Plugin\views\field;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\views\Plugin\views\field\NumericField;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to display the number of discussion answers.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("node_dc_answer_count")
 */
class DcAnswerCount extends NumericField {

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * Database Service Object.
   *
   * @var Connection
   */
  protected $database;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Connection $database
   *   Database Service Object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('database'));
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['link_to_answers'] = ['default' => TRUE];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['link_to_answers'] = array(
      '#title' => $this->t('Link this field to new answers'),
      '#description' => $this->t("Enable to override this field's links."),
      '#type' => 'checkbox',
      '#default_value' => $this->options['link_to_answers'],
    );

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->field_alias = 'num_answers';
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$values) {
    $user = \Drupal::currentUser();
    if ($user->isAnonymous() || empty($values)) {
      return;
    }

    $nids = [];
    $ids = [];
    foreach ($values as $id => $result) {
      $nids[] = $result->nid;
      $values[$id]->{$this->field_alias} = 0;
      // Create a reference so we can find this record in the values again.
      if (empty($ids[$result->nid])) {
        $ids[$result->nid] = [];
      }
      $ids[$result->nid][] = $id;
    }

    if ($nids) {
      $query = $this->database->select('node__field_topic', 'p');
      $query->condition('p.field_topic_target_id', $nids, 'IN');
      $query->addExpression('COUNT(p.field_topic_target_id)', 'num_answers');
      $query->addField('p', 'field_topic_target_id', 'nid');
      $query->groupBy('p.field_topic_target_id');

      $result = $query->execute()->fetchAll();
      foreach ($result as $node) {
        foreach ($ids[$node->nid] as $id) {
          $values[$id]->{$this->field_alias} = $node->num_answers;
        }
      }
    }
  }

  /**
   * Prepares the link to the first new answer.
   *
   * @param string $data
   *   The XSS safe string for the link text.
   * @param ResultRow $values
   *   The values retrieved from a single row of a view's query result.
   *
   * @return string
   *   Returns a string for the link text.
   */
  protected function renderLink($data, ResultRow $values) {
    // Disable link generation until we find a good way.
    if (FALSE && !empty($this->options['link_to_answers']) && $data !== NULL && $data !== '') {
      $node_type = $this->getValue($values, 'type');
      $node = Node::create([
          'nid' => $this->getValue($values, 'nid'),
          'type' => $node_type,
      ]);
      // Because there is no support for selecting a specific answer field to
      // reference, we arbitrarily use the first such field name we find.
      // @todo Provide a means for selecting the answer field.
      //   https://www.drupal.org/node/2594201
      $entity_manager = \Drupal::entityManager();
      $field_map = $entity_manager->getFieldMapByFieldType('answer');
      $answer_field_name = 'answer';
      foreach ($field_map['node'] as $field_name => $field_data) {
        foreach ($field_data['bundles'] as $bundle_name) {
          if ($node_type == $bundle_name) {
            $answer_field_name = $field_name;
            break 2;
          }
        }
      }
//      $page_number = $entity_manager->getStorage('answer')
//        ->getNewCommentPageNumber($this->getValue($values, 'answer_count'), $this->getValue($values), $node, $answer_field_name);
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['url'] = $node->urlInfo();
//      $this->options['alter']['query'] = $page_number ? array('page' => $page_number) : NULL;
      $this->options['alter']['fragment'] = 'new';
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $this->getValue($values);
    if (!empty($value)) {
      return $this->renderLink(parent::render($values), $values);
    }
    else {
      $this->options['alter']['make_link'] = FALSE;
      return $value;
    }
  }

}
