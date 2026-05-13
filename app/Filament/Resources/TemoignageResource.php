<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemoignageResource\Pages;
use App\Models\Temoignage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TemoignageResource extends Resource
{
    protected static ?string $model = Temoignage::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationGroup = 'Recrutement';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('nav.testimonials_management');
    }

    public static function getModelLabel(): string
    {
        return __('nav.testimonial');
    }

    public static function getPluralModelLabel(): string
    {
        return __('nav.testimonials');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('nav.testimonial'))->schema([
                Forms\Components\TextInput::make('candidate_name')
                    ->label(__('admin.full_name'))
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->label(__('admin.content'))
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('rating')
                    ->label(__('admin.rating'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->default(5),
                Forms\Components\Toggle::make('is_published')
                    ->label(__('admin.published')),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('candidate_name')
                    ->label(__('admin.full_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->label(__('admin.content'))
                    ->limit(60),
                Tables\Columns\TextColumn::make('rating')
                    ->label(__('admin.rating'))
                    ->badge(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('admin.published'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime('d/m/Y'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('Edit')),
                Tables\Actions\DeleteAction::make()->label(__('admin.delete')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTemoignages::route('/'),
            'edit'   => Pages\EditTemoignage::route('/{record}/edit'),
        ];
    }
}