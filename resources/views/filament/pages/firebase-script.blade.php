<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
    import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging.js";

    const firebaseConfig = {
        apiKey: "AIzaSyD5HgY2bI9Y9MFwLWsBHTiExwDkf7pZM6k",
        authDomain: "talaa-2bd5f.firebaseapp.com",
        projectId: "talaa-2bd5f",
        storageBucket: "talaa-2bd5f.firebasestorage.app",
        messagingSenderId: "996327384646",
        appId: "1:996327384646:web:ac61584d89e06684fccc47",
        vapidKey: "BJ6e7A28LAEgpImnx9kTvvPVX7GGQ78Qub_A-pI_WO6pbKjXwYGdIp-PV7E804PJ42xiTEE84cn_asPoSl1ohjo",
    };

    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);

    // جلب FCM Token
    getToken(messaging, { vapidKey: firebaseConfig.vapidKey }).then((token) => {
        if (token) {
            console.log("FCM Token:", token);
            fetch("{{ route('admin.save-fcm-token') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ 
                    fcm_token: token,
                    user_id: {{ auth('web')->user()?->id }} 
                 })
            });
        }
    });    // استقبال Notification
    onMessage(messaging, (payload) => {
        console.log("New order notification", payload);
        Swal.fire({
            title:  '{{ __("message.New Order") }}',
            text:  `{{ __("message.You have a new order") }}`,
            icon: 'success',
            confirmButtonText: '{{ __("message.View Orders") }}',
            confirmButtonColor: '#22C55E',
            showCancelButton: true,
            cancelButtonText: '{{ __("message.Close") }}',
            cancelButtonColor: '#FAFAFA',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("filament.admin.resources.orders.index") }}';
            }
        });
    });
</script>
    