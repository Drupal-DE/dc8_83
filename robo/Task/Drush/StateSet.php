<?php

namespace DrupalCenter\Robo\Task\Drush;

/**
 * Robo task: Set a state value.
 */
class StateSet extends DrushTask {

  /**
   * Value format.
   *
   * @var string
   */
  protected $format;

  /**
   * State key.
   *
   * @var string
   */
  protected $key;

  /**
   * State value.
   *
   * @var mixed
   */
  protected $value;

  /**
   * Constructor.
   *
   * @param string $key
   *   The state key.
   * @param mixed $value
   *   The value to assign to the state key.
   * @param string $format
   *   The type for the value. Use 'auto' to detect format from value. Other
   *   recognized values are 'string', 'integer', 'float' or 'boolean' for
   *   corresponding primitive type, or 'json', 'yaml' for complex types.
   */
  public function __construct($key, $value, $format = 'auto') {
    $this->format = $format;
    $this->key = $key;
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    return $this->exec()
      ->arg('state-set')
      ->arg(escapeshellarg($this->key))
      ->arg(escapeshellarg($this->value))
      ->option('format=' . $this->format)
      ->run();
  }

}
