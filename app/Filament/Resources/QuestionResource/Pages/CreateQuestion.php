<?php
namespace App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
class CreateQuestion extends CreateRecord {
    protected static string $resource = QuestionResource::class;
    protected function getCreatedNotification(): ?Notification {
        return Notification::make()->success()->title('Question created successfully.');
    }
    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }
}