<?php

namespace Drupal\example_users\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for render example user table.
 */
class ListController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function index() {
    $header = [
      'name' => $this->t('Name'),
      'identification' => $this->t('Identification'),
      'birthdate' => $this->t('Birth Date'),
      'position' => $this->t('Position'),
      'state' => $this->t('State'),
    ];
    $database = \Drupal::database();
    $query = $database->select('example_users', 'eu');
    $query->fields('eu', ['name', 'identification', 'birthdate', 'position', 'state']);
    $result = $query->execute();
    $records = $result->fetchAll();
    $rows = [];
    foreach ($records as $record) {
      $record->birthdate = date('d/m/Y', $record->birthdate);
      $rows[] = (array) $record;
    }
    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#suffix' => '<a href="/example-module/form">' . $this->t('Add') . '</a>',
    ];
  }

}
