<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Models\Block;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('admin.groups');
    }

    public static function getModelLabel(): string
    {
        return __('admin.group');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.groups');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('block_id')
                ->label('Block')
                ->options(Block::pluck('name', 'id'))
                ->required()
                ->searchable(),
            Forms\Components\TextInput::make('name')
                ->label(__('admin.group_name'))
                ->required(),
            Forms\Components\TextInput::make('order')
                ->label(__('admin.order'))
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 4,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('name')
                            ->label(__('admin.group_name'))
                            ->searchable()
                            ->weight('bold'),
                        Tables\Columns\TextColumn::make('block.name')
                            ->label('Block')
                            ->badge()
                            ->color('gray'),
                    ]),
                    Tables\Columns\TextColumn::make('order')
                        ->label(__('admin.order'))
                        ->badge()
                        ->color('info')
                        ->sortable(),
                ])->space(1),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('Edit')),
                Tables\Actions\DeleteAction::make()->label(__('admin.delete')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit'   => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}