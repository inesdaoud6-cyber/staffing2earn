<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestResource\Pages;
use App\Models\Test;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
        return 'Gestion des tests';
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
            Forms\Components\Section::make('Propriétés du test')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom du test')
                    ->required()
                    ->placeholder('Ex : Test PHP Développeur Senior'),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->placeholder('Compétences évaluées, contexte, etc.'),
                Forms\Components\TextInput::make('eligibility_threshold')
                    ->label('Seuil d’admissibilité (%)')
                    ->numeric()
                    ->default(50),
                Forms\Components\TextInput::make('talent_threshold')
                    ->label('Seuil talents (%)')
                    ->numeric()
                    ->default(80),
                Forms\Components\Toggle::make('whole_test_timer_enabled')
                    ->label('Activer une limite de temps sur tout le test')
                    ->default(false)
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state): void {
                        if (! $state) {
                            $set('whole_test_timer_minutes', null);
                            $set('_whole_test_time_display', '1:00');
                        }
                    }),
                Forms\Components\TextInput::make('_whole_test_time_display')
                    ->label('Temps total')
                    ->placeholder('1:00')
                    ->default('1:00')
                    ->maxLength(8)
                    ->markAsRequired(false)
                    ->extraInputAttributes(['class' => 'fi-input tabular-nums max-w-xs'])
                    ->visible(fn (Get $get): bool => (bool) $get('whole_test_timer_enabled')),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom du test')->searchable(),
                Tables\Columns\TextColumn::make('questions_count')->label('Questions')->counts('questions')->badge()->color('info'),
                Tables\Columns\TextColumn::make('eligibility_threshold')->label('Seuil admissibilité')->suffix('%'),
                Tables\Columns\TextColumn::make('talent_threshold')->label('Seuil talents')->suffix('%'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->date('d/m/Y'),
            ])
            ->actions([
                Tables\Actions\Action::make('attach_questions')
                    ->label('Associer les questions')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->url(fn (Test $record): string => static::getUrl('questions', ['record' => $record])),
                Tables\Actions\EditAction::make()->label('Modifier'),
                Tables\Actions\DeleteAction::make()->label('Supprimer'),
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mergeWholeTestTimerFromHourMinuteFields(array $data): array
    {
        if (! empty($data['whole_test_timer_enabled'])) {
            $data['whole_test_timer_minutes'] = self::parseWholeTestTimeDisplayToMinutes(
                (string) ($data['_whole_test_time_display'] ?? '')
            );
        } else {
            $data['whole_test_timer_minutes'] = null;
        }

        unset($data['_whole_test_time_display']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function splitWholeTestTimerIntoHourMinuteFields(array $data): array
    {
        $total = (int) ($data['whole_test_timer_minutes'] ?? 0);
        if ($total > 0) {
            $h = intdiv($total, 60);
            $m = $total % 60;
            $data['_whole_test_time_display'] = $h . ':' . str_pad((string) $m, 2, '0', STR_PAD_LEFT);
        } else {
            $data['_whole_test_time_display'] = '1:00';
        }

        return $data;
    }

    private static function parseWholeTestTimeDisplayToMinutes(string $raw): int
    {
        $raw = trim($raw);
        if ($raw === '') {
            return 60;
        }

        if (preg_match('/^(\d{1,3}):(\d{1,2})$/', $raw, $m)) {
            $hours = (int) $m[1];
            $mins = (int) $m[2];
            if ($mins > 59) {
                return 60;
            }

            return max(1, $hours * 60 + $mins);
        }

        return 60;
    }
}
