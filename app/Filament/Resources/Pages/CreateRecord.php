<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HasBackHeaderAction;
use Filament\Resources\Pages\CreateRecord as BaseCreateRecord;

abstract class CreateRecord extends BaseCreateRecord
{
    use HasBackHeaderAction;
}
