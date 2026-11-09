<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create 
                            {--name= : Nama lengkap admin atau petugas}
                            {--email= : Alamat email login}
                            {--password= : Kata sandi (minimal 8 karakter)}
                            {--phone= : Nomor HP/WhatsApp}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat atau memperbarui akun Administrator / Petugas Kecamatan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('  Pembuatan Akun Admin / Petugas Kecamatan Cikampek ');
        $this->info('====================================================');

        $name = $this->option('name') ?: $this->ask('Nama Lengkap Admin / Petugas', 'Admin Kecamatan');
        $email = $this->option('email') ?: $this->ask('Alamat Email Login', 'admin@cikampek.go.id');
        $phone = $this->option('phone') ?: $this->ask('Nomor HP / WhatsApp (opsional)', '081234567890');
        
        $password = $this->option('password');
        if (!$password) {
            $password = $this->secret('Kata Sandi (minimal 8 karakter, tekan Enter untuk default "password")');
            if (empty($password)) {
                $password = 'password';
            }
        }

        // Validasi input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            $this->error('Validasi gagal:');
            foreach ($validator->errors()->all() as $err) {
                $this->error("- $err");
            }
            return self::FAILURE;
        }

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            if ($this->input->isInteractive() && !$this->confirm("User dengan email {$email} sudah ada. Perbarui akun menjadi Admin?", true)) {
                $this->warn('Operasi dibatalkan.');
                return self::SUCCESS;
            }

            $existingUser->update([
                'name' => $name,
                'password' => Hash::make($password),
                'no_hp' => $phone,
                'role' => 'admin',
                'email_verified_at' => $existingUser->email_verified_at ?: now(),
            ]);

            $this->info("\n[BERHASIL] Akun admin berhasil diperbarui!");
        } else {
            $existingUser = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'no_hp' => $phone,
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->info("\n[BERHASIL] Akun admin berhasil dibuat!");
        }

        $this->table(
            ['ID', 'Nama', 'Email', 'Role', 'No. HP', 'Status'],
            [[
                $existingUser->id,
                $existingUser->name,
                $existingUser->email,
                $existingUser->role,
                $existingUser->no_hp ?? '-',
                'Aktif'
            ]]
        );

        $this->line("\nKredensial Login:");
        $this->line("URL Login : " . url('/login'));
        $this->line("Email     : " . $email);
        $this->line("Password  : " . $password);

        return self::SUCCESS;
    }
}

