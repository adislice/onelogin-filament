<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TaskResource\Pages;
use App\Filament\Admin\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components;

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
            Components\Select::make('user_id')->relationship('user', 'name')
            ->searchable(['name', 'email'])
            ->preload()->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\TextColumn::make('user.name')->limit(50),
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
}
