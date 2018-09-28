<?php
namespace Drupal\company_docs\Services;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class CompanyDocumentManager
 * @package Drupal\company_docs\Services
 */
class CompanyDocumentManager implements CompanyDocumentManagerInterface
{
  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Current user.
   *
   * @var AccountProxyInterface
   */
  protected $currentUser;

  /**
   * CompanyDocumentManager constructor.
   * @param AccountProxyInterface $current_user
   *   Current user.
   * @param EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param Connection $connection
   *   Database connection.
   */
  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_type_manager, Connection $connection)
  {
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function hasViewFilePermission($fid)
  {
    $file = $this->connection->select('company_docs', 'cd')
      ->fields('cd')
      ->condition('fid', $fid)
      ->execute()
      ->fetchObject();
    // If the record exist and this file is uploaded in company docs.
    if ($file) {
      // If the file belong to the same company as the user.user has permission
      if ($file->company == $this->getUserCompany()) {
        return TRUE;
      }
    }
    // User does not have permission otherwise.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function listOfDocs()
  {
    return $this->connection->select('company_docs', 'cd')
      ->fields('cd')
      ->condition('company', $this->getUserCompany())
      ->execute()
      ->fetchAll();
  }

  /**
   * {@inheritdoc}
   */
  public function getUserCompany()
  {
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    return $user->hasField('field_company') ? $user->get('field_company')->getString() : FALSE;
  }
}
