<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('freelancer_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('final_price')
                    ->numeric()
                    ->default(null)
                    ->prefix('$'),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                TextInput::make('duration')
                    ->required(),
                FileUpload::make('image_url')
                    ->image(),
                TextInput::make('type')
                    ->required()
                    ->default('normal'),
                TextInput::make('status')
                    ->required()
                    ->default('open'),
                TextInput::make('admin_status')
                    ->required()
                    ->default('pending'),
                Textarea::make('attachments')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
