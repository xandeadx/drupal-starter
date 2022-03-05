<?php

use Codeception\Actor;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends Actor {

  use _generated\AcceptanceTesterActions;

  /**
   * Clear test environment.
   */
  public function clearTestEnvironment(): void {
    $dump_timestamp = filemtime('tests/_data/dump.sql');

    // Delete nodes
    $pages_ids = $this->sqlQuery("
      SELECT n.nid
      FROM node_field_data n
      WHERE n.created > $dump_timestamp
    ")->fetchAll(PDO::FETCH_COLUMN);
    $this->deleteNodes($pages_ids);

    // Delete menu items
    $menu_links_ids = $this->sqlQuery("
      SELECT ml.id
      FROM menu_link_content_data ml
      WHERE ml.changed > $dump_timestamp
    ")->fetchAll(PDO::FETCH_COLUMN);
    if ($menu_links_ids) {
      $this->runDrush('entity-delete menu_link_content ' . implode(',', $menu_links_ids));
    }

    // Clear wathdog
    $this->clearWatchdog();

    // Logout
    $this->logout();
  }

  /**
   * Create page node.
   */
  public function savePage(array &$values = []): void {
    $operation = isset($values['id']) ? 'update' : 'create';

    if ($operation == 'create') {
      $values['title'] = $values['title'] ?? $this->generateLabel('Страница');
      $values['body'] = $values['body'] ?? $this->generateLabel('Текст страницы');
    }

    $this->rememberCurrentSession();
    $this->loginAsAdmin();
    $this->amOnDrupalPage($operation == 'create' ? '/node/add/page' : "/node/{$values['id']}/edit");

    if (isset($values['title'])) {
      $this->fillField('title[0][value]', $values['title']);
    }
    if (isset($values['body'])) {
      $this->fillField('field_body[0][subform][field_text][0][value]', $values['body']);
    }

    $this->click('.form-actions .form-submit');
    $this->dontSeeDrupalErrors();
    $this->restoreRememberedSession();

    if ($operation == 'create') {
      $values['id'] = $this->grabLastAddedNodeId('page');
    }
  }

  /**
   * Save term.
   */
  public function saveTerm(array &$values, bool $create_if_exists = FALSE): void {
    $this->rememberCurrentSession();
    $this->loginAsAdmin();

    $operation = isset($values['id']) ? 'edit' : 'create';

    if ($operation == 'create') {
      $values['name'] = $values['name'] ?? $this->generateLabel('Термин');

      if (!$create_if_exists && ($term_id = $this->grabTermIdByName($values['vocabulary'], $values['name']))) {
        $values['id'] = $term_id;
        $this->restoreRememberedSession();
        return;
      }

      $this->amOnTermAddPage($values['vocabulary']);
    }
    else {
      $this->amOnTermEditPage($values['id']);
    }

    if (isset($values['name'])) {
      $this->fillField('name[0][value]', $values['name']);
    }
    if (isset($values['machine_name'])) {
      $this->fillField('field_machine_name[0][value]', $values['machine_name']);
    }
    if (isset($values['parent'])) {
      if ($values['parent'] && !is_int($values['parent'])) {
        $values['parent'] = $this->grabTermIdByName($values['vocabulary'], $values['parent']);
      }
      $this->openDetails('#edit-relations');
      $this->selectOption('#edit-parent', (string)$values['parent']);
    }
    if (isset($values['weight'])) {
      $this->openDetails('#edit-relations');
      $this->fillField('weight', $values['weight']);
    }

    $this->click('#edit-submit');
    $this->dontSeeDrupalErrors();

    if ($operation == 'create') {
      $values['id'] = $this->grabLastAddedTermId($values['vocabulary']);
      $values['machine_name'] = $this->grabContentEntityMachineName('taxonomy_term', $values['id']);
    }

    $this->restoreRememberedSession();
  }

  /**
   * Save terms.
   */
  public function saveTerms(array &$values, bool $create_if_exists = FALSE): void {
    foreach (array_keys($values) as $key) {
      $this->saveTerm($values[$key], $create_if_exists);
    }
  }

  /**
   * Return content entity machine name.
   */
  public function grabContentEntityMachineName(string $entity_type, int $entity_id): string {
    return $this->sqlQuery("
      SELECT field_machine_name_value
      FROM {$entity_type}__field_machine_name
      WHERE entity_id = $entity_id
    ")->fetchColumn();
  }

  /**
   * Return term machine name by term id.
   */
  public function grabTermMachineNameById(int $term_id): string {
    return $this->grabContentEntityMachineName('taxonomy_term', $term_id);
  }

  /**
   * Bulk add menu items.
   */
  public function bulkAddMenuItems(string $menu_name, string $text): void {
    $this->enableDrupalModule('menu_bulk_add_items');
    $this->rememberCurrentSession();
    $this->loginAsAdmin();
    $this->amOnDrupalPage("/admin/structure/menu/manage/$menu_name/bulk-add");
    $this->fillField('items', $text);
    $this->click('.form-submit');
    $this->dontSeeDrupalErrors();
    $this->restoreRememberedSession();
  }

  /**
   * Convert value to array.
   */
  private function toArray(&$value): void {
    if (!is_array($value)) {
      $value = [$value];
    }
  }

}
