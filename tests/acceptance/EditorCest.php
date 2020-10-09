<?php

class EditorCest
{
  public function fillRichTextEditorById(AcceptanceTester $I)
  {
    $I->switchToAdmin();
    $I->amOnPage('/node/add/article');
    $content = file_get_contents(__DIR__ . '/../_data/basic.html');
    $nodeTitle = $I->generateNodeTitle();
    $I->fillField('#edit-title-0-value', $nodeTitle);
    $I->fillRichTextEditorById('edit-body-0-value', $content);
    $I->click('Save');
    $I->see("Article $nodeTitle has been created.");
  }

  public function fillRichTextEditorByName(AcceptanceTester $I)
  {
    $I->switchToAdmin();
    $I->amOnPage('/node/add/article');
    $content = file_get_contents(__DIR__ . '/../_data/basic.html');
    $nodeTitle = $I->generateNodeTitle();
    $I->fillField('#edit-title-0-value', $nodeTitle);
    $I->fillRichTextEditorByName('body[0][value]', $content);
    $I->click('Save');
    $I->see("Article $nodeTitle has been created.");
  }

}
