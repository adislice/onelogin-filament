<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\TaskResource\Pages;
use App\Filament\Employee\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components;
use Filament\Infolists\Infolist;
use Filament\Tables\Filters\SelectFilter;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('name')->required(),
                Components\Textarea::make('description'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\TextColumn::make('completed_at')->default('Not completed')->label('Completion Status')->badge()
                ->color(function ($state) {
                    return $state === 'Not completed' ? 'danger' : 'success';
                }),
                Tables\Columns\TextColumn::make('created_at')
            ])
            ->filters([
                SelectFilter::make('completion_status')
                ->label('Completion Status')
                ->options([
                    'completed' => 'Completed',
                    'ongoing' => 'Ongoing',
                ])
                ->query(function ($query, $state) {
                    if ($state === 'completed') {
                        $query->whereNotNull('completed_at');
                    } elseif ($state === 'ongoing') {
                        $query->whereNull('completed_at');
                    }
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_as_completed')
                ->label("Mark as Completed")
                ->icon('heroicon-o-check-circle')
                ->action(function ($record) {
                    $record->update(['completed_at' => now()]);
                })
                ->requiresConfirmation(true)
                ->modalDescription('Are you sure you\'d like to mark this task as completed? This cannot be undone.')
                ->color('success')
                ->visible(fn ($record) => is_null($record->completed_at)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->user()->id);
    }
}
