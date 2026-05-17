<?php

namespace App\Livewire\Traits;

trait NotificationModal
{
    public $showNotificationModal = false;
    public $notificationType = 'success'; // 'success', 'error', or 'warning'
    public $notificationMessage = '';
    public $notificationTitle = null;

    /**
     * Tampilkan notifikasi modal
     */
    public function showNotification($message, $type = 'success', $title = null)
    {
        $this->notificationMessage = $message;
        $this->notificationType = $type;
        $this->notificationTitle = $title ?? match ($type) {
            'success' => 'Berhasil',
            'warning' => 'Peringatan',
            default => 'Terjadi Kesalahan',
        };
        $this->showNotificationModal = true;
    }

    /**
     * Tampilkan notifikasi sukses
     */
    public function showSuccessNotification($message, $title = 'Berhasil')
    {
        $this->showNotification($message, 'success', $title);
    }

    /**
     * Tampilkan notifikasi error
     */
    public function showErrorNotification($message, $title = 'Terjadi Kesalahan')
    {
        $this->showNotification($message, 'error', $title);
    }

    public function showWarningNotification($message, $title = 'Peringatan')
    {
        $this->showNotification($message, 'warning', $title);
    }

    /**
     * Tutup modal notifikasi
     */
    public function closeNotificationModal()
    {
        $this->showNotificationModal = false;
        $this->notificationMessage = '';
        $this->notificationType = 'success';
        $this->notificationTitle = null;
    }

    /**
     * Helper method untuk mengganti session flash dengan modal notification
     * Gunakan method ini untuk backward compatibility
     */
    protected function flashNotification($message, $type = 'success', $title = null)
    {
        // Jika method showNotification tersedia, gunakan modal
        if (method_exists($this, 'showNotification')) {
            $this->showNotification($message, $type, $title);
        } else {
            // Fallback ke session flash jika method tidak tersedia
            session()->flash('message', $message);
            session()->flash('messageType', $type);
        }
    }
}

