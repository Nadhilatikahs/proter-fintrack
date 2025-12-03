<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!('Notification' in window)) {
            return;
        }

        if (Notification.permission === 'default') {
            // boleh kamu ganti jadi tombol manual, ini yg paling simple
            Notification.requestPermission();
        }
    });
</script>
