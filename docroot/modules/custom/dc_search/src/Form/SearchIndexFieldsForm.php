<?php

namespace Drupal\dc_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dc_search\Plugin\SearchIndexFieldsInterface;
use Drupal\dc_search\SearchIndexFieldsPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form for editing search index field plugins.
 */
class SearchIndexFieldsForm extends FormBase {

  /**
   * The plugin to edit.
   *
   * @var \Drupal\dc_search\SearchIndexFieldsPluginManager $pluginManager
   */
  protected $pluginManager;

  /**
   * Constructs a ConfigTranslationFormBase.
   *
   * @param \Drupal\dc_search\SearchIndexFieldsPluginManager $plugin_manager
   *   The plugin manager.
   */
  public function __construct(SearchIndexFieldsPluginManager $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.dc_search_index_fields')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dc_search_search_index_fields_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = $this->t('Edit custom search index fields settings');
    $form['#tree'] = TRUE;

    $form['table'] = [
      '#type' => 'container',
      '#theme' => 'dc_search_search_index_fields_table',
      '#header' => $this->buildHeader(),
      'plugins' => [],
    ];
    foreach ($this->pluginManager->getPlugins() as $plugin_id => $plugin) {
      if ($row = $this->buildRow($plugin)) {
        $form['table']['plugins'][$plugin_id] = $row;
      }
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('dc_search.search_index_fields');
    $plugin_data = $form_state->getValue(['table', 'plugins'], []);
    foreach ($plugin_data as $plugin_id => $value) {
      foreach (['boost', 'function', 'query'] as $key) {
        if (empty($value[$key])) {
          $config->clear($plugin_id . '.' . $key);
        }
        else {
          $config->set($plugin_id . '.' . $key, $value[$key]);
        }
      }
    }
    $config->save();
    drupal_set_message($this->t('Successfully updated plugin settings.'));
  }

  /**
   * Builds a row for a plugin in the listing.
   *
   * @param \Drupal\dc_search\Plugin\SearchIndexFieldsInterface $plugin
   *   The plugin.
   *
   * @return array
   *   A render array structure of fields for this plugin.
   */
  public function buildRow(SearchIndexFieldsInterface $plugin) {
    $boost_values = dc_search_boosts();

    $row['label'] = [
      '#markup' => $plugin->getName(),
    ];
    $row['boost'] = [
      '#type' => 'select',
      '#options' => array_combine($boost_values, $boost_values),
      '#default_value' => sprintf('%.1f', $plugin->getBoost()),
    ];
    $row['query'] = [
      '#type' => 'textfield',
      '#default_value' => $plugin->getBoostQueryString(),
    ];
    $row['function'] = [
      '#type' => 'textfield',
      '#default_value' => $plugin->getBoostFunctionString(),
    ];
    return $row;
  }

  /**
   * Builds the header row for the plugin listing.
   *
   * @return array
   *   A render array structure of header strings.
   */
  public function buildHeader() {
    $row['label'] = $this->t('Name');
    $row['boost'] = $this->t('Boost');
    $row['query'] = $this->t('Boost query');
    $row['function'] = $this->t('Boost function');
    return $row;
  }

}
