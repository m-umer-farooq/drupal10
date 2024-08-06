<?php

namespace Drupal\as_league\Form\Season;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;


class EditForm extends FormBase {

  protected $database;

  public function __construct(Connection $database, MessengerInterface $messenger) {
    $this->database = $database;
    $this->messenger = $messenger;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('messenger')
    );
  }

  public function getFormId() {
    return 'as_league_season_edit_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $season_name = NULL) {

    $edit_season = NULL;
    
    if ($season_name != NULL) {
      $edit_season = as_league_season_load($season_name);
    }

    $form['instructions'] = array(
        '#type' => 'item',
        '#markup' => '<p>You are editing a BAYS Season</p>'//TODO, enhance these instructions
    );

    $form['season_name'] = array(
        '#title' => t('Season Name'),
        '#type' => 'textfield',
        '#maxlength' => 15,
        '#required' => TRUE,
        '#disabled' => TRUE,
        '#default_value' => $edit_season->season_name,
    );

    $form['start_date'] = array(
        '#title' => t('Season Start Date'),
        '#type' => 'date',
        '#default_value' => $edit_season->start_date,
    );

	$form['end_date'] = array(
		'#title' => t('Season End Date'),
		'#type' => 'date',
        '#default_value' => $edit_season->end_date,
	);

	$season_types = array(
		'Fall' 		=> 'Fall',
		'Spring' 	=> 'Spring',
	);

	$form['season'] = array(
		'#title' => t('Season Type'),
		'#type' => 'select',
	  	'#options' => $season_types,
	  	'#default_value' => $edit_season->season,
	);

	$group_types = array('grade' => 'Grade', 'age' => 'Age');

	$form['group'] = array(
		'#title' => t('Group Type'),
		'#type' => 'select',
    	'#options' => $group_types,
    	'#default_value' => $edit_season->group_type,
	);

	$form['active'] = array(
		'#title' => t('Active'),
		'#type' => 'checkbox',
    	'#description' => 'Would you like to activate this season? Selecting this <strong>WILL</strong> de-activate the currently active season.',
    	'#default_value' => $edit_season->active,
	);

	//Do not allow anyone to de-activate the currently active season, only by selecting a new season can this happen.
	if($edit_season->active == 1){
		$form['active']['#disabled'] = TRUE;
	}
	
	$waiver_status = array(
        'open' => 'Open',
        'closed' => 'Closed'
    );

	$form['waiver_status'] = array(
		'#title' => t('Waiver Status'),
		'#type' => 'select',
    	'#options' => $waiver_status,
    	'#default_value' => $edit_season->waiver_status,
	);
	
	$form['display'] = array(
		'#title' => t('Display'),
		'#type' => 'checkbox',
	    '#description' => 'Should this season be displayed publicly?',
	    '#default_value' => $edit_season->display,
	);

	$grade_groups = array(
		'0' => 'Closed',
		'1' => 'Youth',
		'2' => 'Spring High School',
		'3'	=> 'Fall High School'
	);

	$form['registration'] = array(
		'#title' 			=> t('Registration Open'),
		'#type' 			=> 'select',
		'#options' 			=> $grade_groups,
	    '#description' 		=> 'Is this season open for registration?',
	    '#default_value' 	=> $edit_season->registration,
	);

	$form['placement_status'] = array(
		'#title' 			=> t('Placement Status'),
		'#type' 			=> 'select',
		'#options' 			=> $grade_groups,
	    '#description' 		=> '<em>Requires Database Update by Admin</em>',
	    '#default_value' 	=> $edit_season->placement_status,
	);

    $form['pres_cup'] = array(
		'#title' => t('President Cup Registration Open'),
		'#type' => 'checkbox',
		'#description' => 'Is this season open for registration?',
		'#default_value' => $edit_season->pres_cup,
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

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $season_start_date = (new DrupalDateTime($form_state->getValue('start_date')))->format('Y-m-d');
    $season_end_date   = (new DrupalDateTime($form_state->getValue('end_date')))->format('Y-m-d');

	//Begin the save routine for the organization object
	$as_league_season_load = as_league_season_load($form_state->getValue('season_name'));

	$active_old_stat  = $as_league_season_load->active;
	
    // Create the logic here where we check if the placement status is changed and if it is then we need to update the database
	// $bays_season->placement_status 	= $form_state['values']['placement_status'];
    // we need a switch statement for the placement status and then we need to update the database accordingly
    if($as_league_season_load->placement_status != $form_state->getValue('placement_status')){
        switch($form_state->getValue('placement_status')){
            case 0:
                // update the database to closed               
                $this->messenger->addMessage($this->t('Updating Teams to <strong>PLACEMENT CLOSED</strong>'), MessengerInterface::TYPE_STATUS);
                break;
            case 1:
                // update the database to youth
                $this->messenger->addMessage($this->t('Updating Youth Teams from <strong>REGISTRATION</strong> to <strong>PLACEMENT OPEN</strong>'), MessengerInterface::TYPE_STATUS);
                break;
            case 2:
                // update the database to spring high school
                $this->messenger->addMessage($this->t('Updating High School Teams from <strong>REGISTRATION</strong> to <strong>PLACEMENT OPEN</strong>'), MessengerInterface::TYPE_STATUS);
                break;
            case 3:
                // update the database to fall high school
                $this->messenger->addMessage($this->t('Updating High School Teams from <strong>REGISTRATION</strong> to <strong>PLACEMENT OPEN</strong>'), MessengerInterface::TYPE_STATUS);
                break;
        }
    }

    $as_league_season = [
        'season_name'       => $form_state->getValue('season_name'),
        'start_date'        => $season_start_date,
        'end_date'          => $season_end_date,
        'season'            => $form_state->getValue('season'),
        'group_type'        => $form_state->getValue('group'),
        'active'            => $form_state->getValue('active'),
        'display'           => $form_state->getValue('display'),
        'registration'      => $form_state->getValue('registration'),
        'placement_status'  => $form_state->getValue('placement_status'),
        'pres_cup'          => $form_state->getValue('pres_cup'),
        'waiver_status'     => $form_state->getValue('waiver_status'),
    ];


	//If this is the new active season, de-activate the old one.
	if($as_league_season_load->active == 1 && $active_old_stat == 0){
        $this->database->update('as_league_orgs')->fields(['active' => 1])->condition('active', '1')->execute();
	}

	$season_save_status = as_league_season_save($as_league_season);

	if($season_save_status){
        $this->messenger->addMessage($this->t('You have succesfully updated the '.$form_state->getValue('season').' season record'), MessengerInterface::TYPE_STATUS);
	}else{
        $this->messenger->addMessage($this->t('There was an error updating the '.$form_state->getValue('season').' season record.'), MessengerInterface::TYPE_ERROR);
	}
	
    Cache::invalidateTags(['as_league_cache_tag']);

    $response = new RedirectResponse('/as_league/season/list');
    $response->send();

  }
}
