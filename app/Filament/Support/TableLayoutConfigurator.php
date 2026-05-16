<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;

class TableLayoutConfigurator
{
    public const LAYOUT_LIST = 'list';

    public const LAYOUT_CARDS = 'cards';

    public static function normalize(string $layout): string
    {
        return in_array($layout, [self::LAYOUT_LIST, self::LAYOUT_CARDS], true)
            ? $layout
            : self::LAYOUT_LIST;
    }

    /**
     * @param  array<int, mixed>  $listColumns
     * @param  array<int, mixed>|null  $cardColumns
     * @param  array<string, int>  $contentGrid
     */
    public static function apply(
        Table $table,
        string $layout,
        array $listColumns,
        ?array $cardColumns = null,
        array $contentGrid = ['md' => 2, 'xl' => 3],
    ): Table {
        $layout = self::normalize($layout);

        if ($layout === self::LAYOUT_CARDS) {
            return $table
                ->striped(false)
                ->contentGrid($contentGrid)
                ->columns($cardColumns ?? $listColumns);
        }

        return $table
            ->striped()
            ->columns($listColumns);
    }

    /**
     * @param  array<int, mixed>  $columns
     */
    public static function cardStack(array $columns, int $space = 2): Stack
    {
        return Stack::make($columns)
            ->space($space)
            ->extraAttributes(['class' => 'application-progress-card filament-table-card']);
    }

    /**
     * @return array<int, Action>
     */
    public static function toggleActions(object $page, string $property = 'tableLayout'): array
    {
        return [
            Action::make('layout_list')
                ->label(__('admin.applications_view_list'))
                ->icon('heroicon-o-bars-3')
                ->color(fn () => $page->{$property} === self::LAYOUT_LIST ? 'primary' : 'gray')
                ->outlined(fn () => $page->{$property} !== self::LAYOUT_LIST)
                ->action(fn () => $page->{$property} = self::LAYOUT_LIST),
            Action::make('layout_cards')
                ->label(__('admin.applications_view_cards'))
                ->icon('heroicon-o-squares-2x2')
                ->color(fn () => $page->{$property} === self::LAYOUT_CARDS ? 'primary' : 'gray')
                ->outlined(fn () => $page->{$property} !== self::LAYOUT_CARDS)
                ->action(fn () => $page->{$property} = self::LAYOUT_CARDS),
        ];
    }
}
