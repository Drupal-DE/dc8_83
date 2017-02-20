<?php

namespace Drupal\dc_search\Plugin\search_api\processor\Property;

use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\search_api\Processor\ProcessorPropertyInterface;

/**
 * Provides a base class for mapped processor-defined properties.
 */
class MappedProcessorProperty extends MapDataDefinition implements ProcessorPropertyInterface {

  /**
   * {@inheritdoc}
   */
  public function getProcessorId() {
    return $this->definition['processor_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function isHidden() {
    return !empty($this->definition['hidden']);
  }

}
