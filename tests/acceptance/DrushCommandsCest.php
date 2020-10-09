<?php

class DrushCommandsCest
{
  public function executeDrushCommand(AcceptanceTester $I)
  {
    $output = $I->executeDrushCommand('status');
    $I->assertStringContainsString('Drupal bootstrap : Successful', $output);
  }

  public function executeDrushCommands(AcceptanceTester $I)
  {
    $commands = [
      ['status', []],
    ];
    $output = $I->executeDrushCommands($commands);
    $I->assertIsArray($output);
    $I->assertCount(count($commands), $output);
  }

}
