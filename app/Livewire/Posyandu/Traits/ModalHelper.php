<?php

namespace App\Livewire\Posyandu\Traits;

trait ModalHelper
{
    /**
     * Open modal with automatic posyandu assignment
     */
    protected function openModalWithPosyandu(callable $openModalCallback, string $editMethod, string $resetMethod, $id = null)
    {
        if ($id) {
            $this->$editMethod($id);
        } else {
            $this->$resetMethod();
            // Set posyandu otomatis dari kader
            if (property_exists($this, 'id_posyandu_sasaran')) {
                $this->id_posyandu_sasaran = $this->posyanduId;
            }
            $openModalCallback();
        }
    }

    /**
     * Reset pagination when search changes
     */
    protected function resetPaginationOnSearch(string $searchProperty, string $pageProperty)
    {
        $this->$pageProperty = 1;
    }
}

