<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class AcceptanceBase {

  public function _before(AcceptanceTester $I): void {
    $I->clearTestEnvironment();
  }

}
