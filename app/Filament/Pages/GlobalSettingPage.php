<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Components\RichEditor\RichContentCustomBlocks\TitleAndSub;
use App\Models\GlobalSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;


/**
 * @property-read Schemaa $form
 */
class GlobalSettingPage extends Page
{
    protected string $view = 'filament.pages.global-setting';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getRecord()?->attributesToArray());
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    TextInput::make('exePath')
                        ->required()
                        ->maxLength(255),
                    Hidden::make('LiteExePath'),
                    RichEditor::make('message1')->customBlocks([
                        TitleAndSub::class,
                    ]),

                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->record($this->getRecord())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $record = $this->getRecord();

        if (! $record) {
            $record = new GlobalSetting();
            $record->exePath = 'C:\ERP\.cache\puppeteer\chrome\win64-131.0.6778.69\chrome-win64\chrome.exe';
        }
        info($data);
        info($record);
        $record->fill($data);
        $record->save();

        if ($record->wasRecentlyCreated) {
            $this->form->record($record)->saveRelationships();
        }

        Notification::make()
            ->success()
            ->title('Saved')
            ->send();
    }

    public function getRecord(): ?GlobalSetting
    {
        return GlobalSetting::query()->first();
    }
}
