<?php

namespace Drupal\custom_scorm;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserDataInterface;

/**
 * Holds information on a user status on a partiular SCORM file
 */
class UserStatus {

  /**
   * @var int
   */
  public $sco_id;

  /**
   * @var int
   */
  public $location;

  /**
   * @var string
   */
  public $completion_status;

  /**
   * @var int
   */
  public $total_items;

  /**
   * @var \DateTime
   */
  public $created;

  /**
   * @var \DateTime
   */
  public $updated;
}
