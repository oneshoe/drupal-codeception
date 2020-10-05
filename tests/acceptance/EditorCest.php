<?php

class EditorCest
{
  public function fillCkEditorById(AcceptanceTester $I)
  {
    $I->switchToAdmin();
    $I->amOnPage('/node/add/article');
    $content = readfile(__DIR__ . '/../_data/basic.html');
    $nodeTitle = $I->generateNodeTitle();
    $I->fillField('#edit-title-0-value', $nodeTitle);
    $I->fillCkEditorById('edit-body-0-value', $content);
    $I->click('Save');
    $I->see("Article $nodeTitle has been created.");
  }

  public function fillCkEditorByName(AcceptanceTester $I)
  {
    $I->switchToAdmin();
    $I->amOnPage('/node/add/article');
    $content = readfile(__DIR__ . '/../_data/basic.html');
    $nodeTitle = $I->generateNodeTitle();
    $I->fillField('#edit-title-0-value', $nodeTitle);
    $I->fillCkEditorByName('edit-body-0-value', $content);
    $I->click('Save');
    $I->see("Article $nodeTitle has been created.");
  }

}
