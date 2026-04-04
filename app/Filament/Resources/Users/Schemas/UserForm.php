<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Select::make('role')
                    ->options(['freelancer' => 'Freelancer', 'client' => 'Client', 'admin' => 'Admin'])
                    ->default('client')
                    ->required(),
                FileUpload::make('profile_image')
                    ->image(),
                TextInput::make('headline')
                    ->default(null),
                Textarea::make('skills')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('bio')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('country')
                    ->default(null),
                TextInput::make('city')
                    ->default(null),
                TextInput::make('freelancer_rating')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_projects_completed')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('excellent_projects_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_reviews')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('id_number')
                    ->default(null),
                FileUpload::make('id_image')
                    ->image(),
                FileUpload::make('id_image_back')
                    ->image(),
                Select::make('verification_status')
                    ->options([
            'unverified' => 'Unverified',
            'pending' => 'Pending',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
        ])
                    ->default('unverified')
                    ->required(),
                Toggle::make('is_profile_completed')
                    ->required(),
                Toggle::make('is_banned')
                    ->required(),
                DateTimePicker::make('last_seen'),
            ]);
    }
}
