<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HasBackHeaderAction;
use Filament\Resources\Pages\EditRecord as BaseEditRecord;

abstract class EditRecord extends BaseEditRecord
{
    use HasBackHeaderAction;
}
