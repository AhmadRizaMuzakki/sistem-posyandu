<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class ActivityLogger
{
    /**
     * Catat log hanya untuk user dengan role superadmin.
     */
    public static function log(string $action, string $description, array $properties = []): ?ActivityLog
    {
        try {
            $user = auth()->user();
            if (!$user || !$user->hasRole('superadmin')) {
                return null;
            }
            return ActivityLog::log($action, $description, $properties);
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /**
     * Catat login (untuk semua user: superadmin dan kader).
     * Untuk kader, isi id_posyandu agar log aktivitas per posyandu bisa difilter.
     */
    public static function login(int $userId, string $email, ?int $idPosyandu = null): ?ActivityLog
    {
        try {
            return ActivityLog::create([
                'user_id' => $userId,
                'id_posyandu' => $idPosyandu,
                'action' => 'login',
                'description' => "Login berhasil: {$email}",
                'subject_type' => 'User',
                'subject_id' => $userId,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'properties' => null,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /**
     * Catat logout (untuk semua user: superadmin dan kader).
     */
    public static function logout(int $userId, ?int $idPosyandu = null): ?ActivityLog
    {
        try {
            return ActivityLog::create([
                'user_id' => $userId,
                'id_posyandu' => $idPosyandu,
                'action' => 'logout',
                'description' => 'Logout',
                'subject_type' => 'User',
                'subject_id' => $userId,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'properties' => null,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    public static function create(string $model, string $label, $id = null): ?ActivityLog
    {
        return self::log('create', "Menambah data: {$label}", [
            'subject_type' => $model,
            'subject_id' => $id,
        ]);
    }

    public static function update(string $model, string $label, $id = null): ?ActivityLog
    {
        return self::log('update', "Mengubah data: {$label}", [
            'subject_type' => $model,
            'subject_id' => $id,
        ]);
    }

    public static function delete(string $model, string $label, $id = null): ?ActivityLog
    {
        return self::log('delete', "Menghapus data: {$label}", [
            'subject_type' => $model,
            'subject_id' => $id,
        ]);
    }
}
