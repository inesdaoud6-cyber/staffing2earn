<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestResource\Pages;
use App\Models\Test;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestResource extends Resource
{
    protected static ?string $model = Test::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Évaluations';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('nav.tests_management');
    }

    public static function getModelLabel(): string
    {
        return 'Test';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Tests';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.test_properties'))->schema([
                Forms\Components\TextInput::make('name')->label(__('admin.test_name'))->required()->placeholder('Ex : Test PHP Développeur Senior'),
                Forms\Components\Textarea::make('description')->label(__('admin.description'))->placeholder(__('admin.describe_skills')),
                Forms\Components\TextInput::make('eligibility_threshold')->label(__('admin.eligibility_threshold'))->numeric()->default(50),
                Forms\Components\TextInput::make('talent_threshold')->label(__('admin.talent_threshold'))->numeric()->default(80),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('admin.test_name'))->searchable(),
                Tables\Columns\TextColumn::make('questions_count')->label(__('Questions'))->counts('questions')->badge()->color('info'),
                Tables\Columns\TextColumn::make('eligibility_threshold')->label(__('admin.eligibility_threshold'))->suffix('%'),
                Tables\Columns\TextColumn::make('talent_threshold')->label(__('admin.talent_threshold'))->suffix('%'),
                Tables\Columns\TextColumn::make('created_at')->label(__('Date'))->date('d/m/Y'),
            ])
            ->actions([
                Tables\Actions\Action::make('attach_questions')
                    ->label(__('test.manage-questions'))
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->url(fn (Test $record): string => static::getUrl('questions', ['record' => $record])),
                Tables\Actions\EditAction::make()->label(__('Edit')),
                Tables\Actions\DeleteAction::make()->label(__('admin.delete')),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTests::route('/'),
            'create' => Pages\CreateTest::route('/create'),
            'edit' => Pages\EditTest::route('/{record}/edit'),
            'questions' => Pages\ManageTestQuestions::route('/{record}/questions'),
            'view' => Pages\ViewTest::route('/{record}'),
        ];
    }
}
