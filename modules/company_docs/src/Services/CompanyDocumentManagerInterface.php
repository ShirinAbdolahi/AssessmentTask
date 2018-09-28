<?php
namespace Drupal\company_docs\Services;

/**
 * Interface CompanyDocumentManagerInterface
 * @package Drupal\company_docs\Services
 */
interface CompanyDocumentManagerInterface
{
  /**
   * Decide if the user has permission to view the file.
   * @param int $fid
   *   file id.
   * @return bool
   */
  public function hasViewFilePermission($fid);

  /**
   * Find list of files for the current users company.
   * @return array
   */
  public function listOfDocs();

  /**
   * The assigned company id to the user.
   * @return string
   */
  public function getUserCompany();

}
