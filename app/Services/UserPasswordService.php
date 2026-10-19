<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserPasswordService
{
    public function assign(User $user, string $password): void
    {
        // Persist both values in the same User save; authentication still uses the hash.
        $user->password = Hash::make($password);
        $user->password_encrypted = Crypt::encryptString($password);
    }

    public function reveal(User $user): ?string
    {
        if (!$user->password_encrypted) {
            return null;
        }

        try {
            $password = Crypt::decryptString($user->password_encrypted);

            // A password changed by another process must never reveal an obsolete value.
            return Hash::check($password, $user->password ?? '') ? $password : null;
        } catch (DecryptException | RuntimeException $exception) {
            return null;
        }
    }
}
