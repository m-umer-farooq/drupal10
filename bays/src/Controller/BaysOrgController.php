<?php

namespace Drupal\bays\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Database;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BaysOrgController extends ControllerBase {

  public function BaysUpdateOrg(Request $request) {

      // Collect all form values.
      $values = $request->request->all();

      $org_id = $values['id'];

      // Ensure you include all the fields that need to be saved.
      $fields_to_save = [  
          'org_full_name' => $values['org_full_name'],
          'org_name'      => $values['org_name'],
          'website'       => $values['website'],
          'logo_path'     => $values['logo_path'],
          'org_color'     => $values['org_color'],
          'street'        => $values['street'],
          'city'          => $values['city'],
          'state'         => $values['state'],
          'zip_code'      => $values['zip_code'], 
          'notify_ref'    => !empty($values['notify_ref']) ? 1 : 0,
          'ref_reject'    => !empty($values['ref_reject']) ? 1 : 0,
          'ztrt'          => !empty($values['ztrt']) ? 1 : 0,
          // Add other fields from the form if necessary.
      ];

      // Update the fields in the database.
      $connection = \Drupal::database();
      $connection->update('as_league_orgs')->fields($fields_to_save)->condition('org_id', $org_id)->execute();

      Cache::invalidateTags(['as_league_cache_tag']);

      $response = new RedirectResponse('/as_league/orgs/list');
      $response->send();

  }

  public function BaysInsertOrg(Request $request) {

      // Collect all form values.
      $values = $request->request->all();

      $fields_to_save = [ 
        'org_id'        => $values['org_id'],
        'org_full_name' => $values['org_full_name'],
        'org_name'      => $values['org_name'],
        'website'       => $values['website'],
        'logo_path'     => $values['logo_path'],
        'org_color'     => $values['org_color'],
        'street'        => $values['street'],
        'city'          => $values['city'],
        'state'         => $values['state'],
        'zip_code'      => $values['zip_code'],   
        'notify_ref'    => !empty($values['notify_ref']) ? 1 : 0,
        'ref_reject'    => !empty($values['ref_reject']) ? 1 : 0,
        'ztrt'          => !empty($values['ztrt']) ? 1 : 0,
        // Add other fields from the form if necessary.
    ];

    // Insert the new organization and get the new organization ID.
    $connection = \Drupal::database();
    $connection->insert('as_league_orgs')->fields($fields_to_save)->execute();

    Cache::invalidateTags(['as_league_cache_tag']);

    $response = new RedirectResponse('/as_league/orgs/list');
    $response->send();

  }

}
