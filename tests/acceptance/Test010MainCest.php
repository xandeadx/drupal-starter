<?php

class Test010MainCest {

  public function testLoginAsAdmin(AcceptanceTester $I): void {
    $I->clearTestEnvironment();
    $I->loginAsAdmin();
    $I->amOnDrupalPage('/user/1');
    $I->seePageTitle('admin');
  }

  public function testAddPage(AcceptanceTester $I): void {
    $I->clearTestEnvironment();
    $page = [];
    $I->savePage($page);
    $I->amOnDrupalPage('/node/' . $page['id']);
    $I->seePageTitle($page['title']);
    $I->deleteNode($page['id'], true);
  }

}
