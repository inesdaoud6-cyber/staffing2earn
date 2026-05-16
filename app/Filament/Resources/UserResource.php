<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Support\TableLayoutConfigurator;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\StaticAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): ?string
    {
        return __('nav.system_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('nav.users_management');
    }

    public static function getModelLabel(): string
    {
        return __('nav.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('nav.users');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage-users') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can('manage-users') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('manage-users') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        if (! auth()->user()?->can('manage-users')) {
            return false;
        }

        if ($record->id === auth()->id()) {
            return false;
        }

        if ($record->hasRole('admin') && User::role('admin')->count() <= 1) {
            return false;
        }

        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.user_account'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.full_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('role')
                            ->label(__('admin.user_role'))
                            ->options([
                                'admin'     => __('nav.role_admin'),
                                'candidate' => __('nav.role_candidate'),
                            ])
                            ->required()
                            ->native(false)
                            ->dehydrated(false),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return self::configureTable($table);
    }

    public static function configureTable(Table $table, string $layout = TableLayoutConfigurator::LAYOUT_LIST): Table
    {
        return TableLayoutConfigurator::apply(
            $table
                ->modifyQueryUsing(fn ($query) => $query->with(['roles', 'candidate']))
                ->hiddenFilterIndicators()
                ->searchDebounce('150ms')
                ->searchPlaceholder(__('admin.search_users_placeholder')),
            $layout,
            [
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.full_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('admin.user_role'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'admin'     => __('nav.role_admin'),
                        'candidate' => __('nav.role_candidate'),
                        default     => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'admin' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ],
            [
                TableLayoutConfigurator::cardStack([
                    Tables\Columns\TextColumn::make('name')
                        ->label(__('admin.full_name'))
                        ->searchable()
                        ->weight(FontWeight::Bold),
                    Tables\Columns\TextColumn::make('email')
                        ->label(__('Email'))
                        ->icon('heroicon-o-envelope')
                        ->color('gray')
                        ->size('sm'),
                    Tables\Columns\TextColumn::make('roles.name')
                        ->label(__('admin.user_role'))
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                            'admin'     => __('nav.role_admin'),
                            'candidate' => __('nav.role_candidate'),
                            default     => $state ?? '—',
                        })
                        ->color(fn (?string $state): string => match ($state) {
                            'admin' => 'danger',
                            default => 'gray',
                        }),
                    Tables\Columns\TextColumn::make('created_at')
                        ->label(__('Date'))
                        ->dateTime('d/m/Y H:i')
                        ->color('gray')
                        ->size('sm'),
                ]),
            ],
        )
            ->searchable(false)
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view_more')
                    ->iconButton()
                    ->tooltip(__('admin.view_more'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (User $record): string => __('admin.user_details_heading', ['name' => $record->name]))
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (StaticAction $action) => $action->label(__('admin.close')))
                    ->infolist(fn (Infolist $infolist): Infolist => $infolist->schema(self::userDetailsInfolistSchema()))
                    ->authorize('manage-users'),
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip(__('Edit')),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip(__('Delete'))
                    ->visible(fn (User $record): bool => static::canDelete($record)),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit'  => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<\Filament\Infolists\Components\Component>
     */
    public static function userDetailsInfolistSchema(): array
    {
        return [
            Section::make(__('admin.user_account'))
                ->columns(2)
                ->schema([
                    TextEntry::make('id')
                        ->label('ID'),
                    TextEntry::make('name')
                        ->label(__('admin.full_name'))
                        ->weight(FontWeight::Medium),
                    TextEntry::make('email')
                        ->label(__('Email'))
                        ->copyable(),
                    TextEntry::make('roles_display')
                        ->label(__('admin.user_role'))
                        ->getStateUsing(function (User $record): string {
                            return $record->roles
                                ->pluck('name')
                                ->map(fn (string $name): string => match ($name) {
                                    'admin'     => __('nav.role_admin'),
                                    'candidate' => __('nav.role_candidate'),
                                    default     => $name,
                                })
                                ->join(', ') ?: '—';
                        }),
                    TextEntry::make('email_verified_at')
                        ->label(__('admin.email_verified_at'))
                        ->dateTime('d/m/Y H:i')
                        ->placeholder(__('admin.not_verified')),
                    TextEntry::make('created_at')
                        ->label(__('admin.registered_at'))
                        ->dateTime('d/m/Y H:i'),
                    TextEntry::make('updated_at')
                        ->label(__('admin.updated_at'))
                        ->dateTime('d/m/Y H:i'),
                ]),
            Section::make(__('admin.candidate_profile_section'))
                ->description(__('admin.candidate_profile_description'))
                ->visible(fn (User $record): bool => $record->candidate === null)
                ->schema([
                    TextEntry::make('no_candidate_placeholder')
                        ->hiddenLabel()
                        ->getStateUsing(fn (): string => __('admin.no_candidate_record')),
                ]),
            Section::make(__('admin.candidate_profile_section'))
                ->description(__('admin.candidate_profile_description'))
                ->visible(fn (User $record): bool => $record->candidate !== null)
                ->columns(2)
                ->schema([
                    TextEntry::make('candidate.first_name')
                        ->label(__('First Name')),
                    TextEntry::make('candidate.last_name')
                        ->label(__('Last Name')),
                    TextEntry::make('candidate.phone')
                        ->label(__('admin.phone'))
                        ->placeholder('—'),
                    TextEntry::make('candidate.birth_date')
                        ->label(__('admin.birth_date'))
                        ->date('d/m/Y')
                        ->placeholder('—'),
                    TextEntry::make('candidate.address')
                        ->label(__('admin.address'))
                        ->columnSpanFull()
                        ->placeholder('—'),
                    TextEntry::make('candidate.email')
                        ->label(__('Email'))
                        ->placeholder('—'),
                    TextEntry::make('candidate.status')
                        ->label(__('Status'))
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                            'pending'     => __('Pending'),
                            'in_progress' => __('In Progress'),
                            'validated'   => __('Validated'),
                            'rejected'    => __('Rejected'),
                            default       => $state ?? '—',
                        })
                        ->color(fn (?string $state): string => match ($state) {
                            'validated'   => 'success',
                            'rejected'    => 'danger',
                            'in_progress' => 'info',
                            default       => 'warning',
                        }),
                    TextEntry::make('candidate.primary_score')
                        ->label(__('admin.primary_score'))
                        ->placeholder('—'),
                    TextEntry::make('candidate.secondary_score')
                        ->label(__('admin.secondary_score'))
                        ->placeholder('—'),
                    TextEntry::make('applications_count')
                        ->label(__('admin.applications_count'))
                        ->getStateUsing(fn (User $record): int => $record->candidate->applicationProgresses()->count()),
                    TextEntry::make('candidate.cv_path')
                        ->label(__('admin.cv'))
                        ->formatStateUsing(fn (?string $state): string => $state
                            ? __('admin.cv_uploaded')
                            : __('admin.cv_missing'))
                        ->url(fn (User $record): ?string => $record->candidate?->cv_path
                            ? asset('storage/' . $record->candidate->cv_path)
                            : null)
                        ->openUrlInNewTab(),
                ]),
        ];
    }
}
