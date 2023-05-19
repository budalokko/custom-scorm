<?php

namespace Drupal\custom_scorm;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserDataInterface;

/**
 * UserStatusHandler service.
 */
class UserStatusHandler {

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs an UserStatusHandler object.
   *
   * @param \Drupal\user\UserDataInterface $user_data
   *   The user data service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(UserDataInterface $user_data, AccountInterface $current_user) {
    $this->userData = $user_data;
    $this->currentUser = $current_user;
  }

  /**
   * Returns all SCORM statuses for current user.
   *
   * @return UserStatus[]
   */
  public function getAllForCurrentUser(): array {

    // No data stored for anonymous user
    if ($this->currentUser->isAnonymous()) {
      return [];
    }

    $user_statuses = $this->userData->get('custom_scorm', $this->currentUser->id(), 'user_status');

    return is_null($user_statuses) ? [] : $user_statuses;
  }

  /**
   * Set SCORM status for current user
   *
   * @param int $opigno_scorm_sco_id
   * @param object $data
   */
  public function setUserStatusFromScormCommitData(int $opigno_scorm_sco_id, object $data): void {

    if (!isset($data->scorm_version) || $data->scorm_version != '2004') {
      throw new \Exception(sprintf('Unsupported SCORM version %s.', $data->scorm_version));
    }

    $current_date = date_create();

    if (!$scorm_user_status = $this->findByScoId($opigno_scorm_sco_id)) {
      $scorm_user_status = new UserStatus();
      $scorm_user_status->sco_id = $opigno_scorm_sco_id;
      $scorm_user_status->created = $current_date;
    }

    $scorm_user_status->location = $data->cmi->location;
    $scorm_user_status->completion_status = $data->cmi->completion_status;
    $scorm_user_status->total_items  = count(get_object_vars($data->cmi->suspend_items));
    $scorm_user_status->updated = $current_date;

    $this->saveUserStatus($scorm_user_status);
  }

  /**
   * Converts a user status object in a renderable array
   *
   * @param $user_status
   *
   * @return array
   */
  public function getRenderableRow(UserStatus $user_status): array {
    return [
      $user_status->sco_id,
      $user_status->location,
      $user_status->completion_status,
      $user_status->total_items,
      $user_status->created->format('m/d/Y'),
      $user_status->updated->format('m/d/Y'),
    ];
  }

  /**
   * Returns current user status in passed SCORM
   *
   * @param int $sco_id
   *
   * @return \Drupal\custom_scorm\UserStatus|void
   */
  protected function findByScoId(int $sco_id) {
    $scorm_user_statuses = $this->getAllForCurrentUser();

    if (isset($scorm_user_statuses[$sco_id])) {
      return $scorm_user_statuses[$sco_id];
    }
  }

  /**
   * Store passed status into current user object
   *
   * @param \Drupal\custom_scorm\UserStatus $scorm_user_status
   */
  protected function saveUserStatus(UserStatus $scorm_user_status): void {
    $scorm_user_statuses = $this->getAllForCurrentUser();
    $scorm_user_statuses[$scorm_user_status->sco_id] = $scorm_user_status;
    $this->userData->set('custom_scorm', $this->currentUser->id(), 'user_status', $scorm_user_statuses);
  }
}
