<?php

namespace App\Filament\Resources\Books\Tables;

use App\Models\Book;
use App\Models\Loan;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('author')
                    ->searchable(),
                TextColumn::make('publisher')
                    ->searchable(),
                TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('pinjam')
                    ->label('Pinjam')
                    ->icon('heroicon-o-book-open')
                    ->requiresConfirmation()
                    ->visible(fn () => ! self::isAdmin())
                    ->disabled(fn (Book $record) => $record->stock < 1)
                    ->action(function (Book $record) {
                        $aktif = Loan::where('user_id', Auth::id())
                            ->where('status', 'dipinjam')
                            ->count();

                        if ($aktif >= 3) {
                            Notification::make()
                                ->title('Maksimal pinjam 3 buku')
                                ->danger()
                                ->send();
                            return;
                        }

                        $berhasil = DB::transaction(function () use ($record) {
                            $buku = Book::lockForUpdate()->find($record->id);

                            if (! $buku || $buku->stock < 1) {
                                return false;
                            }

                            Loan::create([
                                'user_id'   => Auth::id(),
                                'book_id'   => $buku->id,
                                'loan_date' => now(),
                                'due_date'  => now()->addDays(7),
                            ]);

                            $buku->decrement('stock');

                            return true;
                        });

                        if (! $berhasil) {
                            Notification::make()
                                ->title('Stok buku habis')
                                ->danger()
                                ->send();
                            return;
                        }

                        Notification::make()
                            ->title('Buku berhasil dipinjam')
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->visible(fn () => self::isAdmin()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => self::isAdmin()),
                ]),
            ]);
    }

    private static function isAdmin(): bool
    {
        $user = Auth::user();

        return $user instanceof \App\Models\User && $user->isAdmin();
    }
}