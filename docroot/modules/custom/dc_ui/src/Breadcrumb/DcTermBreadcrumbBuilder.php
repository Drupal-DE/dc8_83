<?php

namespace Drupal\dc_ui\Breadcrumb;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\dc_ui\Breadcrumb\DcBreadcrumbBuilderBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Base breadcrumb builder for terms.
 */
abstract class DcTermBreadcrumbBuilder extends DcBreadcrumbBuilderBase {

  /**
   * List of bundles the builder should apply to.
   *
   * @var array
   */
  protected $bundles = [];

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    if (($term = $route_match->getParameter('taxonomy_term')) === NULL) {
      return FALSE;
    }
    // Check if term has been loaded or is only the term id.
    if (is_numeric($term)) {
      $term = Term::load($term);
    }
    return in_array($term->getVocabularyId(), $this->bundles);
  }

}
