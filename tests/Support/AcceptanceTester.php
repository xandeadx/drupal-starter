<?php

namespace Tests\Support;

use PDO;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor {

  use _generated\AcceptanceTesterActions;

  /**
   * Clear test environment.
   */
  public function clearTestEnvironment(): void {
    $dump_timestamp = filemtime('tests/Support/Data/dump.sql');

    $this->maximizeWindow();

    // Delete new nodes
    $new_nodes_ids = $this->sqlQuery("
      SELECT n.nid
      FROM node_field_data n
      WHERE n.created > $dump_timestamp
    ")->fetchAll(PDO::FETCH_COLUMN);
    if ($new_nodes_ids) {
      $this->deleteNodes($new_nodes_ids);
    }

    // Delete new menu items
    $new_menu_links_ids = $this->sqlQuery("
      SELECT ml.id
      FROM menu_link_content_data ml
      WHERE ml.changed > $dump_timestamp
    ")->fetchAll(PDO::FETCH_COLUMN);
    if ($new_menu_links_ids) {
      $this->deleteEntities('menu_link_content', $new_menu_links_ids);
    }

    // Delete new files entity
    $test_products_images_ids = $this->grabProductsImagesIds(array_column($this->products, 'id'));
    $new_files_ids = $this->sqlQuery("
      SELECT f.fid
      FROM file_managed f
      WHERE f.created > $dump_timestamp
      " . ($test_products_images_ids ? "AND f.fid NOT IN (" . implode(', ', $test_products_images_ids) . ")"  : "") . "
    ")->fetchAll(PDO::FETCH_COLUMN);
    if ($new_files_ids) {
      $this->deleteEntities('file', $new_files_ids);
    }

    // Clear watchdog
    $this->clearWatchdog();

    // Clear queue
    $this->clearQueue();

    // Logout
    $this->logout();

    // Clear cookies
    $this->deleteAllCookies();
  }

  /**
   * Save page node.
   */
  public function savePage(array &$values = []): void {
    $operation = isset($values['id']) ? 'update' : 'create';

    if ($operation == 'create') {
      $values['title'] = $values['title'] ?? $this->generateLabel('Страница');
      $values['body'] = $values['body'] ?? $this->generateLabel('Текст страницы');
      $prev_added_id = $this->grabLastAddedNodeId('page');
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
    if (isset($values['alias'])) {
      $this->openVerticalTab('edit-path-0');
      $this->fillField('path[0][alias]', $values['alias']);
    }
    if (isset($values['status'])) {
      $this->openVerticalTab('edit-options');
      $this->fillCheckbox('status[value]', (bool)$values['status']);
    }

    $this->click('.form-actions .form-submit');
    $this->dontSeeDrupalErrors();
    $this->restoreRememberedSession();

    if ($operation == 'create') {
      $values['id'] = $this->grabLastAddedNodeId('page');
      $this->assertNotEquals($prev_added_id, $values['id'], 'Fail creating page.');
    }
  }

  /**
   * Save pages.
   */
  public function savePages(array &$values): void {
    foreach (array_keys($values) as $key) {
      $this->savePage($values[$key]);
    }
  }

  /**
   * Save term.
   */
  public function saveTerm(array &$values, bool $create_if_exists = FALSE): void {
    $operation = isset($values['id']) ? 'edit' : 'create';

    if ($operation == 'create') {
      $values['name'] = $values['name'] ?? $this->generateLabel('Термин');

      if (!$create_if_exists && ($term_id = $this->grabTermIdByName($values['vocabulary'], $values['name']))) {
        $values['id'] = $term_id;
        return;
      }

      $prev_added_id = $this->grabLastAddedTermId($values['vocabulary']);
      $url = "/admin/structure/taxonomy/manage/{$values['vocabulary']}/add";
    }
    else {
      $url = "/taxonomy/term/{$values['id']}/edit";
    }

    $this->rememberCurrentSession();
    $this->loginAsAdmin();
    $this->amOnDrupalPage($url);

    // Name
    if (isset($values['name'])) {
      $this->fillField('name[0][value]', $values['name']);
    }
    // Machine name
    if (isset($values['machine_name'])) {
      $this->fillField('field_machine_name[0][value]', $values['machine_name']);
    }
    // Parent
    if (isset($values['parent'])) {
      if ($values['parent'] && !is_int($values['parent'])) {
        $values['parent'] = $this->grabTermIdByName($values['vocabulary'], $values['parent']);
      }
      $this->selectOption('#edit-parent', (string)$values['parent']);
    }
    // Weight
    if (isset($values['weight'])) {
      $this->openDetails('#edit-relations');
      $this->fillField('weight', $values['weight']);
    }

    $this->click('#edit-submit');
    $this->dontSeeDrupalErrors();

    if ($operation == 'create') {
      $values['id'] = $this->grabLastAddedTermId($values['vocabulary']);
      $values['machine_name'] = $this->grabContentEntityMachineName('taxonomy_term', $values['id']);
      $this->assertNotEquals($prev_added_id, $values['id'], 'Fail creating term.');
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
   * Save menu item.
   */
  public function saveMenuItem(array &$values): void {
    $this->rememberCurrentSession();
    $this->loginAsAdmin();

    if (empty($values['id'])) {
      $prev_added_id = $this->grabLastAddedMenuItemId();
      $this->amOnDrupalPage("/admin/structure/menu/manage/{$values['menu']}/add");
    }
    else {
      $this->amOnDrupalPage("/admin/structure/menu/item/{$values['id']}/edit");
    }

    // Title
    $this->fillField('title[0][value]', $values['title']);

    // Uri
    if (!empty($values['uri'])) {
      $this->fillField('link[0][uri]', $values['uri']);
    }

    // Expanded
    $this->fillCheckbox('expanded[value]', $values['expanded'] ?? FALSE);

    // Parent
    if (!empty($values['parent'])) {
      $this->selectOption('menu_parent', $values['parent']);
    }

    // Weight
    if (!empty($values['weight'])) {
      $this->fillField('weight[0][value]', $values['weight']);
    }

    $this->click('#edit-submit');
    $this->dontSeeDrupalErrors();

    if (empty($values['id'])) {
      $values['id'] = $this->grabLastAddedMenuItemId();
      $this->assertNotEquals($prev_added_id, $values['id'], 'Fail creating menu item.');
    }

    $this->restoreRememberedSession();
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
   * Approve comment.
   */
  public function approveComment(int $comment_id): void {
    $this->rememberCurrentSession();
    $this->loginAsAdmin();
    $this->amOnDrupalPage('/comment/' . $comment_id . '/edit');
    $this->selectOption('input[name="status"]', '1');
    $this->scrollToWithoutAnimation('.form-actions');
    $this->click('.form-actions .form-submit');
    $this->dontSeeDrupalErrors();
    $this->restoreRememberedSession();
  }

  /**
   * Open xbase tab.
   */
  public function openXbaseTab(string $tabs_selector, string $tab_id): void {
    $this->click($tabs_selector . ' .tabs__nav-link[href="#tab-' . $tab_id . '"]');
  }

  /**
   * Spinner up.
   */
  public function spinnerUp(string $input_selector): void {
    $this->click("$input_selector ~ .ui-spinner-up");
  }

  /**
   * Spinner down.
   */
  public function spinnerDown(string $input_selector): void {
    $this->click("$input_selector ~ .ui-spinner-down");
  }

  /**
   * Wait ajax end.
   */
  public function waitAjax(): void {
    $this->wait(0.6);
  }

  /**
   * Enable menu content item.
   */
  public function enableMenuContentItem(int $menu_content_item_id): void {
    $this->sqlQuery("
      UPDATE menu_link_content_data
      SET enabled = 1
      WHERE id = $menu_content_item_id
    ");

    $this->sqlQuery("
      UPDATE menu_tree
      SET enabled = 1
      WHERE id = 'menu_link_content:" . $this->grabMenuItemUuidById($menu_content_item_id) . "'
    ");

    $this->runDrush('cache-rebuild');
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
