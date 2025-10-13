<?php

namespace App\Filament\Forms\Components\RichEditor\RichContentCustomBlocks;

use App\Enums\MyTextSize;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\RichEditor\RichContentCustomBlock;
use Filament\Forms\Components\RichEditor\TextColor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\TextSize;

class TitleAndSub extends RichContentCustomBlock
{
    public static function getId(): string
    {
        return 'title_and_sub';
    }

    public static function getLabel(): string
    {
        return 'Title and sub';
    }

    public static function configureEditorAction(Action $action): Action
    {
        return $action
            ->modalDescription('Configure the title and sub')
            ->schema([
                TextInput::make('title'),
                TextInput::make('subTitle'),
                ColorPicker::make('titleColor')->hsl(),
                ColorPicker::make('subTitleColor')->hsl(),
                Select::make('titleSize')->options(MyTextSize::class),
                Select::make('subTitleSize')->options(MyTextSize::class),
            ]);
    }

    public static function toPreviewHtml(array $config): string
    {
        return view('filament.forms.components.rich-editor.rich-content-custom-blocks.title-and-sub.preview', [
            'title' => $config['title'],
            'subTitle' => $config['subTitle'],
            'titleColor' => $config['titleColor'],
            'subTitleColor' => $config['subTitleColor'],
            'titleSize' => $config['titleSize'],
            'subTitleSize' => $config['subTitleSize'],
        ])->render();
    }

    public static function toHtml(array $config, array $data): string
    {
        return view('filament.forms.components.rich-editor.rich-content-custom-blocks.title-and-sub.index', [
            'title' => $config['title'],
            'subTitle' => $config['subTitle'],
            'titleColor' => $config['titleColor'],
            'subTitleColor' => $config['subTitleColor'],
            'titleSize' => $config['titleSize'],
            'subTitleSize' => $config['subTitleSize'],
        ])->render();
    }
}
