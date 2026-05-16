<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlockResource\Pages;
use App\Filament\Support\TableLayoutConfigurator;
use App\Models\Block;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BlockResource extends Resource
{
    protected static ?string $model = Block::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Configuration';

    public static function getNavigationLabel(): string
    {
        return __('nav.blocks_management');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('admin.block_name'))
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->label(__('admin.description'))
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return self::configureTable($table);
    }

    public static function configureTable(Table $table, string $layout = TableLayoutConfigurator::LAYOUT_LIST): Table
    {
        return TableLayoutConfigurator::apply(
            $table,
            $layout,
            [
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.block_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.description'))
                    ->limit(80)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ],
            [
                TableLayoutConfigurator::cardStack([
                    Tables\Columns\TextColumn::make('name')
                        ->label(__('admin.block_name'))
                        ->searchable()
                        ->weight('bold'),
                    Tables\Columns\TextColumn::make('description')
                        ->label(__('admin.description'))
                        ->limit(80)
                        ->color('gray')
                        ->size('sm'),
                    Tables\Columns\TextColumn::make('created_at')
                        ->label(__('Date'))
                        ->dateTime('d/m/Y H:i')
                        ->color('gray')
                        ->size('sm'),
                ], 1),
            ],
            ['md' => 2, 'xl' => 4],
        )
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('Modifier'),
                Tables\Actions\DeleteAction::make()->label('Supprimer'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBlocks::route('/'),
            'create' => Pages\CreateBlock::route('/create'),
            'edit'   => Pages\EditBlock::route('/{record}/edit'),
        ];
    }
}