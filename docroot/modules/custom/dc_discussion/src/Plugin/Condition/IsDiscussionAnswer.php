<?php

namespace Drupal\dc_discussion\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;

/**
 * Provides a 'Is discussion answer' condition to enable a condition based in module selected status.
 *
 * @Condition(
 *   id = "is_discussion_answer",
 *   label = @Translation("Is discussion answer"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", required = TRUE , label = @Translation("node"))
 *   }
 * )
 *
 */
class IsDiscussionAnswer extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * Creates a new ExampleCondition instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }


  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    /** @var TermInterface $term */
    $node = $this->getContextValue('node');
    // Check if term has needed fields.
    if (!$node->hasField('field_parent') && empty($node->get('field_parent')->getValue())) {
      return FALSE;
    }
    // Check for correct landingpage type
    $field_parent = $node->get('field_parent')->getValue();
    $parent = reset($field_parent);
    if ($parent['target_id'] > 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
    if ($this->isNegated()) {
      return $this->t("Node is not a discussion answer");
    }
    else {
      return $this->t("Node is a discussion answer");
    }
  }

}
