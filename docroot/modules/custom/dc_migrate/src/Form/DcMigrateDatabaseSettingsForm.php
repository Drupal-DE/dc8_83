<?php

namespace Drupal\dc_migrate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form for food search settings.
 */
class DcMigrateDatabaseSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dc_migrate_database_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $state = \Drupal::state()->get('dc_migrate.database');

    $form['#title'] = $this->t('Edit database settings');
    $form['#tree'] = TRUE;

    $form['connection'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Database connection'),
      '#description' => $this->t('Note that settings made here will not be exported or stored in persistent configuration.'),
      '#open' => TRUE,
    ];
    $form['connection']['database'] = [
      '#title' => $this->t('Database name'),
      '#type' => 'textfield',
      '#default_value' => isset($state['database']) ? $state['database'] : '',
    ];
    $form['connection']['username'] = [
      '#title' => $this->t('Username'),
      '#type' => 'textfield',
      '#default_value' => isset($state['username']) ? $state['username'] : '',
      '#attributes' => [
        'autocomplete' => 'off',
      ],
    ];
    $form['connection']['password'] = [
      '#title' => $this->t('Password'),
      '#type' => 'password',
      '#default_value' => isset($state['password']) ? $state['password'] : '',
      '#attributes' => [
        'autocomplete' => 'off',
      ],
    ];
    $form['connection']['host'] = [
      '#title' => $this->t('Host'),
      '#type' => 'textfield',
      '#placeholder' => 'localhost',
      '#default_value' => isset($state['host']) ? $state['host'] : '',
    ];
    $form['connection']['port'] = [
      '#title' => $this->t('Port'),
      '#type' => 'number',
      '#min' => 0,
      '#placeholder' => '3306',
      '#default_value' => isset($state['port']) ? $state['port'] : '',
    ];

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
    $values = $form_state->getValues();
    // Set state.
    \Drupal::state()->set('dc_migrate.database', $values['connection']);

    drupal_set_message($this->t('Successfully updated database settings.'));
  }

}
