<?php

namespace Drupal\bays\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

class AsLeagueOrgFormAlter {

  /**
   * Alters the as_league organization forms.
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id) {
      // Check if the form ID matches the as_league organization forms.
      if ($form_id == 'as_league_organization_add_form' || $form_id == 'as_league_organization_edit_form') {
          // Add the new fields to the form.
        $org_id = (isset($form['id']['#value'])) ? $form['id']['#value'] : NULL;

        $default_values = [
            'notify_ref' => 0,
            'ref_reject' => 0,
            'ztrt' => 0,
          ];

        // If an organization ID is present, fetch the existing values from the database.
        if ($org_id) {

            $connection = \Drupal::database();
            $query = $connection->select('as_league_orgs', 'a')
            ->fields('a', ['notify_ref', 'ref_reject', 'ztrt'])
            ->condition('org_id', $org_id)
            ->execute()
            ->fetchAssoc();

          if ($query) {
            $default_values['notify_ref'] = $query['notify_ref'];
            $default_values['ref_reject'] = $query['ref_reject'];
            $default_values['ztrt']       = $query['ztrt'];
          }
        }

        // Add new checkbox fields to the form.
        $form['notify_ref'] = [
          '#type' => 'checkbox',
          '#title' => t('Notify Ref'),
          '#description' => t('Enable Notify Ref.'),
          '#default_value' => $default_values['notify_ref'],
        ];

        $form['ref_reject'] = [
          '#type' => 'checkbox',
          '#title' => t('Ref Reject'),
          '#description' => t('Enable Ref Reject.'),
          '#default_value' => $default_values['ref_reject'],
        ];

        $form['ztrt'] = [
          '#type' => 'checkbox',
          '#title' => t('ZTRT'),
          '#description' => t('Enable ZTRT.'),
          '#default_value' => $default_values['ztrt'],
        ];
        
        if($form_id == 'as_league_organization_edit_form'){
           $form['#action'] = Url::fromRoute('bays.update_org')->toString();
        }

        if($form_id == 'as_league_organization_add_form'){
          $form['#action'] = Url::fromRoute('bays.insert_org')->toString();
        }
      }
  }

  /**
   * Form validation handler for the as_league organization forms.
   */
  /* public function validateForm(&$form, FormStateInterface $form_state) {
    // Validate new_column1 to ensure it is not empty.
    if (empty($form_state->getValue('new_column1'))) {
      $form_state->setErrorByName('new_column1', t('New Column 1 is required.'));
    }

    // Validate new_column2 to ensure it is a positive number.
    if (!is_numeric($form_state->getValue('new_column2')) || $form_state->getValue('new_column2') <= 0) {
      $form_state->setErrorByName('new_column2', t('New Column 2 must be a positive number.'));
    }

    // Validate new_column3 to ensure it is not empty.
    if (empty($form_state->getValue('new_column3'))) {
      $form_state->setErrorByName('new_column3', t('New Column 3 is required.'));
    }
  } */
}
