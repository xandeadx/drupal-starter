<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class Test010MainCest extends AcceptanceBase {

  public function testLoginAsAdmin(AcceptanceTester $I): void {
    $I->amOnDrupalPage('/user/1');
    $I->see('Доступ запрещён');

    $I->loginAsAdmin();
    $I->amOnDrupalPage('/user/1');
    $I->seePageTitle('admin');
  }

}
