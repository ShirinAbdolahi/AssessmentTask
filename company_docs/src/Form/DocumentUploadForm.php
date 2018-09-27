<?php
namespace Drupal\company_docs\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Class DocumentUploadForm.
 *
 * @package Drupal\company_docs\Form
 */
class DocumentUploadForm extends FormBase
{

  /**
   * The database connection.
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
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityTypeManager $entity_type_manager, Connection $connection)
  {
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'document_upload_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['doc_name'] = [
      '#title' => $this->t('Document Name'),
      '#type' => 'textfield',
      '#required' => TRUE,
    ];

    $form['file'] = [
      '#title' => $this->t('Document'),
      '#type' => 'managed_file',
      '#upload_location' => 'public://documents/',
      '#multiple' => FALSE,
      '#description' => t('Allowed extensions: pdf'),
      '#upload_validators' => array('file_validate_extensions' => array('pdf')),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and Upload'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser()->id());
    $company_id = $user->get('field_company')->getString();
    $file = $this->entityTypeManager->getStorage('file')->load($form_state->getValue('file')[0]);
    $file->set('status', FILE_STATUS_PERMANENT);
    $file->save();
    $this->connection->insert('company_docs')->fields([
      'fid' => $file->id(),
      'uid' => $this->currentUser()->id(),
      'company' => $company_id,
      'doc_name' => $form_state->getValue('doc_name')
    ])->execute();

    $url = Url::fromRoute('company_docs.list');
    $form_state->setRedirectUrl($url);
    $this->messenger()->addMessage('Document uploaded successfully');
  }


}
