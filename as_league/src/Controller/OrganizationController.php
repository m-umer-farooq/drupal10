<?php

namespace Drupal\as_league\Controller;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Pager\PagerManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrganizationController extends ControllerBase {

  protected $database;
  protected $pagerManager;
  protected $renderer;

  public function __construct(Connection $database, PagerManagerInterface $pagerManager, RendererInterface $renderer) {
    $this->database = $database;
    $this->pagerManager = $pagerManager;
    $this->renderer = $renderer;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('pager.manager'),
      $container->get('renderer')
    );
  }

  public function list() {

    $results = Database::getConnection()->select('as_league_orgs', 'org')
      ->fields('org', 
      [ 
        'org_id',
        'org_full_name',
        'org_name'
      ]
      )->execute()->fetchAll();

    $add_new_url = Url::fromRoute('as_league.org.add');
    $add_new_link = Link::fromTextAndUrl($this->t('Add'), $add_new_url)->toRenderable();
    $add_new_link['#attributes'] = ['class' => ['button']];

    $output['add_new'] =  [
      '#markup' => '',
      'back_button' => $add_new_link
    ];
    
    $output = [
      '#type' => 'markup',
      '#markup' => '<a href="/as_league/org/add" class="button">Add New</a>'
    ];
    

    // Table header
    $header = [
      'org_id'        => $this->t('ORG ID'),
      'org_full_name' => $this->t('Org Full Name'),
      'org_name'      => $this->t('Org Name'),
      'operations'    => $this->t('Operations'),
    ];

    // Table rows
    $rows = [];
    foreach ($results as $record) {
      //$edit_url = Url::fromRoute('as_league.edit', ['org_id' => $record->org_id])->toString();
      $rows[] = [
        'org_id' => $record->org_id,
        'org_full_name' => $record->org_full_name,
        'org_name' => $record->org_name,
        'operations' => [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'edit' => [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute('as_league.org.edit', ['org_id' => $record->org_id]),
              ],
            ],
          ],
        ],
      ];
    }

    $build = [
      '#type'       => 'table',
      '#header'     => $header,
      '#rows'       => $rows,
      '#empty'      => $this->t('No records found'),
      '#attributes' => [
        'class'     => ['as-league-table'],
      ],
    ];

    // Attach the CSS library
    $build['#attached']['library'][] = 'as_league/organization_list';

    $cache_metadata = new CacheableMetadata();
    $cache_metadata->setCacheTags(['as_league_cache_tag']);
    $cache_metadata->applyTo($build);

    $output['table'] = $build;

    return $output;
  }


  public function listRecords(Request $request) {

    $limit = 10;
    $current_page = $this->pagerManager->createPager($this->getTotalRecords(), $limit)->getCurrentPage();
    $offset = $current_page * $limit;

    $query = $this->database->select('as_league_orgs', 'o')->fields('o')->range($offset, $limit);
    $results = $query->execute()->fetchAll();

    $table_rows = [];
    foreach ($results as $result) {
      $table_rows[] = (array) $result;
    }

    $pager = [
      '#type' => 'pager',
      '#element' => 0,
    ];

    return [
      '#theme' => 'organization_table',
      '#header' => ['Org ID', 'Org Name', 'Org Full Name','Website', 'Logo', 'Org Color', 'Street', 'City', 'State', 'Zip Code','Notify Ref','Ref Reject', 'Actions'],
      '#rows' => $table_rows,
      '#pager' => $pager,
      '#add_url' => Url::fromRoute('as_league.org_add')->toString(),
      '#attached' => [
        'library' => [
          'as_league/organization_list',
        ],
      ],
      '#cache' => [
        'tags' => ['as_league_cache_tag'],
      ],
    ];
  }

  protected function getTotalRecords() {
    $count_query = $this->database->select('as_league_orgs', 'o')->countQuery();
    return $count_query->execute()->fetchField();
  }


}