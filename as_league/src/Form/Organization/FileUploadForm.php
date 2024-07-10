<?php

namespace Drupal\as_league\Form\Organization;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Cache\Cache;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FileUploadForm extends FormBase {

  public function getFormId() {
    return 'as_league_organization_image_upload_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $form['file_upload'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Upload a file'),
        '#upload_location' => 'public://as_league/org/logos/',
        '#default_value' => '',
        '#upload_validators' => [
          'file_validate_extensions' => ['jpg jpeg png gif'],
          'file_validate_size' => [25600000], // 25 MB
        ],
      ];
  
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
      ];
  
      return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $file = $form_state->getValue('file_upload');

    if (!empty($file)) {
        $file = File::load($file[0]);
        dpm($file);
        $file->setPermanent();
        $file->save();

        \Drupal::messenger()->addStatus($this->t('The file has been uploaded successfully.'));
    }
    else {
      \Drupal::messenger()->addError($this->t('No file was uploaded.'));
    }
  }
}
