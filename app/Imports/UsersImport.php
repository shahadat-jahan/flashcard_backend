<?php

namespace App\Imports;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Password;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToCollection, WithBatchInserts, WithChunkReading, WithHeadingRow
{
    private int $insertedCount = 0;

    public function collection(Collection $rows): void
    {
        $usersToInsert = [];

        foreach ($rows as $row) {
            if (! isset($row['email']) || User::where('email', $row['email'])->exists()) {
                continue;
            }

            $usersToInsert[] = [
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'password' => null,
                'avatar' => null,
                'designation_id' => $row['designation_id'] ?? null,
                'status' => UserStatus::INACTIVE,
                'role' => UserRole::tryFrom($row['role']) ?? UserRole::USER,
                'created_at' => now(),
            ];
        }

        if (! empty($usersToInsert)) {
            User::insert($usersToInsert);
            $this->insertedCount += count($usersToInsert);
            $insertedUsers = User::whereIn('email', array_column($usersToInsert, 'email'))->get();

            foreach ($insertedUsers as $user) {
                $token = Password::broker()->createToken($user);
                $user->notify(new SetPasswordNotification($token));
            }
        }
    }

    public function getInsertedCount(): int
    {
        return $this->insertedCount;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
