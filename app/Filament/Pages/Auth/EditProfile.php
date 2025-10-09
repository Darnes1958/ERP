<?php

namespace App\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class EditProfile extends \Filament\Auth\Pages\EditProfile
{
  public function form(Schema $schema): Schema
  {
    return $schema
      ->components([

        $this->getNameFormComponent(),
        $this->getPasswordFormComponent(),
        $this->getPasswordConfirmationFormComponent(),
      ]);
  }
}
