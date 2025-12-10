<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Profile extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Profile';
    protected static ?string $navigationGroup = 'Profile'; // biar masuk group Profile di sidebar
    protected static ?int    $navigationSort  = 1;

    protected static string $view = 'filament.pages.profile';

    /**
     * State form disimpan di sini.
     */
    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->form->fill([
            'name'  => $user->name,
            'email' => $user->email,
        ]);
    }

    /**
     * Definisi form Filament.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Change password')
                    ->description('Kosongkan kalau tidak ingin mengubah password.')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Current password')
                            ->password()
                            ->revealable()
                            ->rule('nullable')
                            ->dehydrated(false), // tidak disimpan ke state user

                        Forms\Components\TextInput::make('password')
                            ->label('New password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->rule('nullable'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm new password')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->rule('nullable')
                            ->dehydrated(false),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    /**
     * Aksi saat tombol Save ditekan.
     */
    public function save(): void
    {
        $user = Auth::user();

        // Validasi dasar manual (bisa juga via Rules, ini versi simpel)
        $data = $this->form->getState();

        // Update name & email
        $user->name  = $data['name'] ?? $user->name;
        $user->email = $data['email'] ?? $user->email;

        // Kalau user isi password baru, cek current_password dulu
        if (! empty($data['password'])) {
            if (empty($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                Notification::make()
                    ->title('Current password salah')
                    ->danger()
                    ->body('Password lama yang kamu masukkan tidak cocok.')
                    ->send();

                return;
            }

            $user->password = Hash::make($data['password']);
        }

        $user->save();

        Notification::make()
            ->title('Profile updated')
            ->success()
            ->body('Profil kamu berhasil diperbarui.')
            ->send();
    }

    /**
     * Title halaman (di header).
     */
    public function getTitle(): string
    {
        return 'Profile';
    }
}
