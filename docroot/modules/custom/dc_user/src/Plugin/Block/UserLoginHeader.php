<?php

namespace Drupal\dc_user\Plugin\Block;

use Drupal\user\Plugin\Block\UserLoginBlock;

/**
 * Provides a 'UserLoginHeader' block.
 *
 * @Block(
 *  id = "user_login_header",
 *  admin_label = @Translation("User login header"),
 * )
 */
class UserLoginHeader extends UserLoginBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = parent::build();
    // Rewrite form id for theming.
    $form['user_login_form']['#form_id'] = 'user_login_form_header';

    // Hide user_links.
    $form['user_links']['#access'] = FALSE;

    // Put form labels as placeholders.
    $form['user_login_form']['name']['#attributes']['placeholder'] = $form['user_login_form']['name']['#title'];
    $form['user_login_form']['pass']['#attributes']['placeholder'] = $form['user_login_form']['pass']['#title'];

    // Remove theme wrappers
    $form['user_login_form']['name']['#theme_wrappers'] = [];
    $form['user_login_form']['pass']['#theme_wrappers'] = [];
    $form['user_login_form']['actions']['#theme_wrappers'] = [];

    // Remove size attributes - will be handled by ui-kit.
    unset($form['user_login_form']['name']['#size']);
    unset($form['user_login_form']['pass']['#size']);

    // Add some special classes for styling.
    $form['user_login_form']['name']['#attributes']['class'] = ['uk-form-width-small'];
    $form['user_login_form']['pass']['#attributes']['class'] = ['uk-form-width-small'];
    $form['user_login_form']['actions']['submit']['#attributes']['class'] = [
      'uk-button-primary',
      'uk-button-small'
    ];

    // Hide name & pass title.
    unset($form['user_login_form']['name']['#title']);
    unset($form['user_login_form']['pass']['#title']);

    return $form;
  }
}
