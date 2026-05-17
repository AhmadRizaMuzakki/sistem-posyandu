{{-- Menghubungkan Livewire dispatch('show-alert') ke modal Alpine --}}
<script>
    document.addEventListener('livewire:init', function () {
        Livewire.on('show-alert', function (payload) {
            var data = payload;
            if (Array.isArray(payload) && payload.length > 0) {
                data = payload[0];
            }
            if (!data || typeof data !== 'object') {
                data = {};
            }
            window.dispatchEvent(new CustomEvent('show-alert', {
                detail: {
                    message: data.message || '',
                    type: data.type || 'warning',
                },
            }));
        });
    });
</script>
