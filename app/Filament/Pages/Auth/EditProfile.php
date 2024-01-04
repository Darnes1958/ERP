<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
  public function form(Form $form): Form
  {
    return $form
      ->schema([

        $this->getNameFormComponent(),
        $this->getPasswordFormComponent(),
        $this->getPasswordConfirmationFormComponent(),
      ]);
  }
}
