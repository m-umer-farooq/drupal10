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

class SeasonController extends ControllerBase {

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

  public function listRecords(Request $request) {

    $limit = 20;
    $current_page = $this->pagerManager->createPager($this->getTotalRecords(), $limit)->getCurrentPage();
    $offset = $current_page * $limit;

    $query = $this->database->select('as_league_season', 's')->fields('s')->orderBy('season_name', 'DESC')->range($offset, $limit);
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
      '#theme' => 'season-listing',
      '#header' => ['Season', 'Active', 'Display','Registration Open', 'Waiver Status', 'Presidents\' Cup Status', 'Actions'],
      '#rows' => $table_rows,
      '#pager' => $pager,
      '#add_url' => Url::fromRoute('as_league.season.add')->toString(),
      '#attached' => [
        'library' => [
          'as_league/organization-list',
        ],
      ],
      '#cache' => [
        'tags' => ['as_league_cache_tag'],
      ],
    ];
  }

  protected function getTotalRecords() {
    $count_query = $this->database->select('as_league_season', 's')->countQuery();
    return $count_query->execute()->fetchField();
  }


}