<?php

namespace DrupalCenter\Robo\Task\Drush;

/**
 * Robo task: Install Drupal site.
 */
class SiteInstall extends DrushTask {

  /**
   * {@inheritdoc}
   */
  public function run() {
    return $this->exec()
      ->arg('site-install')
      ->arg('minimal')
      ->option('yes')
      ->option('notify')
      ->option('account-name=admin')
      ->option('account-pass=admin')
      ->option('site-mail=admin@example.com')
      ->run();
  }

}
