<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HasBackHeaderAction;
use Filament\Resources\Pages\ViewRecord as BaseViewRecord;

abstract class ViewRecord extends BaseViewRecord
{
    use HasBackHeaderAction;
}
