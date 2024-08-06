<?php

namespace Drupal\as_league\Form\Organization;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EditForm extends FormBase {


  protected $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function getFormId() {
    return 'as_league_organization_edit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $org_id = NULL) {
    $record = NULL;
    
    if ($org_id != NULL) {

      $record = $this->database->select('as_league_orgs', 'org')
        ->fields('org', 
        [
            'org_id', 
            'org_full_name', 
            'org_name',
            'website',
            'logo_path',
            'org_color',
            'street',
            'city',
            'state',
            'zip_code',
            'notify_ref',
            'ref_reject'            
            ])
        ->condition('org_id', $org_id)->execute()->fetchObject();
    }

    $form['id'] = [
      '#type' => 'hidden',
      '#value' => $org_id,
    ];

    $form['org_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Org ID'),
      '#required' => TRUE,
      '#maxlength' => 4,
      '#disabled' => TRUE,
      '#default_value' => $record ? $record->org_id : '',
    ];

    $form['org_full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Org Full Name'),
      '#required' => TRUE,
      '#default_value' => $record ? $record->org_full_name : '',
    ];

    $form['org_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Org Name'),
      '#required' => TRUE,
      '#default_value' => $record ? $record->org_name : '',
    ];

    $form['website'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Website'),
      '#required' => TRUE,
      '#default_value' => $record ? $record->website : '',
    ];

    $form['logo_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Logo Path'),
      '#required' => TRUE,
      '#default_value' => $record ? $record->logo_path : '',
    ];

    $form['org_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Org Color'),
      '#required' => TRUE,
      '#default_value' => $record ? $record->org_color : '',
    ];

    $form['street'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Street'),
      '#required' => TRUE,
      '#default_value' => $record ? $record->street : '',
    ];

    $form['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#required' => TRUE,
      '#default_value' => $record ? $record->city : '',
    ];

    $form['state'] = [
      '#type' => 'textfield',
      '#title' => $this->t('State'),
      '#required' => TRUE,
      '#default_value' => $record ? $record->state : '',
    ];

    $form['zip_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zip Code'),
      '#required' => TRUE,
      '#default_value' => $record ? $record->zip_code : '',
    ];
    

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#attributes' => array('class' => array('button')),
    ];

    $back_url = Url::fromRoute('as_league.orgs.list');
    $back_link = Link::fromTextAndUrl($this->t('Back'), $back_url)->toRenderable();
    $back_link['#attributes'] = ['class' => ['button']];

    $form['actions']['back'] =  [
      '#markup' => '',
      'back_button' => $back_link
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $fields = [
      'org_full_name' => $form_state->getValue('org_full_name'),
      'org_name'      => $form_state->getValue('org_name'),
      'website'       => $form_state->getValue('website'),
      'logo_path'     => $form_state->getValue('logo_path'),
      'org_color'     => $form_state->getValue('org_color'),
      'street'        => $form_state->getValue('street'),
      'city'          => $form_state->getValue('city'),
      'state'         => $form_state->getValue('state'),
      'zip_code'      => $form_state->getValue('zip_code'),
    ];

    $this->database->update('as_league_orgs')->fields($fields)->condition('org_id', $form_state->getValue('org_id'))->execute();
    
    Cache::invalidateTags(['as_league_cache_tag']);

    $response = new RedirectResponse('/as_league/orgs/list');
    $response->send();
  }
}
