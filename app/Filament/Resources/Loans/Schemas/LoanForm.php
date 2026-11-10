<?php

namespace App\Filament\Resources\Loans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('book_id')
                    ->relationship('book', 'title')
                    ->required(),
                DatePicker::make('loan_date')
                    ->required(),
                DatePicker::make('due_date')
                    ->required(),
                DatePicker::make('returned_at'),
                TextInput::make('status')
                    ->required()
                    ->default('dipinjam'),
                TextInput::make('fine')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
