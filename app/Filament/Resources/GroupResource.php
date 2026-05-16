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
use Illuminate\Database\Eloquent\Builder;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('nav.groups_management');
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
            Forms\Components\Textarea::make('description')
                ->label(__('admin.description'))
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->withCount('questions')
            )
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
                            ->label(__('admin.block'))
                            ->badge()
                            ->color('gray'),
                    ]),
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('questions_count')
                            ->label(__('admin.questions_count'))
                            ->badge()
                            ->color('info'),
                        Tables\Columns\TextColumn::make('description')
                            ->label(__('admin.description'))
                            ->limit(50)
                            ->color('gray')
                            ->size('sm'),
                    ]),
                ])->space(1),
            ])
            ->defaultSort('name')
            ->filters([])
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