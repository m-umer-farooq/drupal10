<?php

namespace Drupal\as_league\Form\Season;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddForm extends FormBase {

  protected $database;

  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  public function getFormId() {
    return 'as_league_season_add_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $form = array();
    
    $form['instructions'] = array(
      '#type' => 'item',
      '#markup' => '<p>You are creating a new Season</p>'
    );
  
    $form['season_name'] = array(
      '#title' => t('Season Name'),
      '#type' => 'textfield',
      '#maxlength' => 15,
      '#required' => TRUE,
    );
  
    $form['start_date'] = array(
      '#title' => t('Season Start Date'),
      '#type' => 'date',
    );
  
    $form['end_date'] = array(
      '#title' => t('Season End Date'),
      '#type' => 'date',
    );
  
    $as_league_season_types = array('Fall' => 'Fall', 'Spring' => 'Spring');
  
    $form['season'] = array(
      '#title' => t('Season Type'),
      '#type' => 'select',
        '#options' => $as_league_season_types,
    );
  
    $as_league_group_types = array('grade' => 'Grade', 'age' => 'Age');
  
    $form['group'] = array(
      '#title' => t('Group Type'),
      '#type' => 'select',
        '#options' => $as_league_group_types,
    );
  
    $form['display'] = array(
      '#title' => t('Display'),
      '#type' => 'checkbox',
        '#description' => 'Should this season be displayed publicly?',
    );
  
    $form['registration'] = array(
      '#title' => t('Registration Open'),
      '#type' => 'checkbox',
        '#description' => 'Is this season open for registration?',
    );
  
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#name' => 'Submit',
      '#attributes' => array('class' => array('button')),
    );

    $back_url = Url::fromRoute('as_league.season.list');
    $back_link = Link::fromTextAndUrl($this->t('Back'), $back_url)->toRenderable();
    $back_link['#attributes'] = ['class' => ['button']];

    $form['actions']['back'] =  [
      '#markup' => '',
      'back_button' => $back_link
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    
    $season_name = $form_state->getValue('season_name');

    $season = as_league_season_load($season_name);

    if(!$season_name){
      $form_state->setErrorByName('season_name', $this->t('That season already exists.'));
    }
}


  public function submitForm(array &$form, FormStateInterface $form_state) {
   
      $as_league_season = [
        'season_name'       => $form_state->getValue('season_name'),
        'start_date'        => (new DrupalDateTime($form_state->getValue('start_date')))->format('Y-m-d'),
        'end_date'          => (new DrupalDateTime($form_state->getValue('end_date')))->format('Y-m-d'),
        'season'            => $form_state->getValue('season'),
        'group_type'        => $form_state->getValue('group'),
        'active'            => '0',
        'display'           => $form_state->getValue('display'),
        'registration'      => $form_state->getValue('registration'),
      ];

      $season_save_status = as_league_season_save($as_league_season);

      if($season_save_status){
            $this->messenger->addMessage($this->t('You have succesfully created the '.$form_state->getValue('season').' season record'), MessengerInterface::TYPE_STATUS);
      }else{
            $this->messenger->addMessage($this->t('There was an error creating the '.$form_state->getValue('season').' season record.'), MessengerInterface::TYPE_ERROR);
      }

      Cache::invalidateTags(['as_league_cache_tag']);

      $response = new RedirectResponse('/as_league/season/list');
      $response->send();
  }


  
}
