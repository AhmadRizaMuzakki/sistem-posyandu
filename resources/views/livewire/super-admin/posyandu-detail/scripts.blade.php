<script>
    // Auto-hide pesan sukses setelah 5 detik
    document.addEventListener('livewire:init', () => {
        Livewire.on('message', () => {
            setTimeout(() => {
                const messageEl = document.querySelector('[role="alert"]');
                if (messageEl) {
                    messageEl.style.transition = 'opacity 0.5s';
                    messageEl.style.opacity = '0';
                    setTimeout(() => messageEl.remove(), 500);
                }
            }, 5000);
        });
    });
</script>

