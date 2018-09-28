<?php
namespace Drupal\company_docs\Controller;

use Drupal\company_docs\Services\CompanyDocumentManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Controller that handles Company Docs.
 */
class CompanyDocsController extends ControllerBase
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
   * Company Document Manager.
   * @var CompanyDocumentManagerInterface
   */
  protected $companyDocManager;

  /**
   * CompanyDocsController constructor.
   * @param EntityTypeManager $entity_type_manager
   *   Entity Type manager.
   * @param Connection $connection
   *   Database Connection
   * @param CompanyDocumentManagerInterface $company_doc_manager
   *   Company Doc manager.
   */
  public function __construct(EntityTypeManager $entity_type_manager, Connection $connection, CompanyDocumentManagerInterface $company_doc_manager)
  {
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->companyDocManager = $company_doc_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('company_docs.manager')
    );
  }

  /**
   * Returns a table of files for user's company.
   */
  public function lists()
  {
    // Get the docs of current user company.
    $docs = $this->companyDocManager->listOfDocs();
    // Create the table.
    $header = array($this->t('Document Name'), $this->t('Document'));
    $rows = [];
    foreach ($docs as $doc) {
      $rows[] = [$doc->doc_name, Link::createFromRoute($this->t('View'), 'company_docs.file', ['fid' => $doc->fid])];
    }
    // Add the link for upload form.
    $link = Link::createFromRoute('Add new Document', 'company_docs.form');
    $link = $link->toRenderable();
    $build = [
      ['#markup' => render($link)],
      [
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#cache' => ['max-age' => 0]
      ],
      '#cache' => ['max-age' => 0]
    ];
    return $build;
  }

  /**
   * Return the file if user has permission to view it.
   * @param int $fid
   * @return array|BinaryFileResponse
   */
  public function file(int $fid)
  {
    $file = $this->entityTypeManager->getStorage('file')->load($fid);
    // Check if the file exist and user has permission to view it.
    if ($file && $this->companyDocManager->hasViewFilePermission($fid)) {
      // Return the file.
      return new BinaryFileResponse($file->getFileUri());
    } else {
      $build = ['#markup' => 'Requested File not found', '#cache' => ['max-age' => 0]];
      return $build;
    }
  }

  /**
   * Check if the user have access for current route.
   */
  public function access()
  {
    // Access is granted if only user has a company.
    return AccessResult::allowedIf($this->companyDocManager->getUserCompany() != '');
  }
}
