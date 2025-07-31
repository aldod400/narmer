<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
    import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging.js";

  
    const firebaseConfig = {
            apiKey: "AIzaSyBMg64r8DjE98HJdy1uecrFCXYafeHVJyY",
            authDomain: "narmer-app.firebaseapp.com",
            projectId: "narmer-app",
            storageBucket: "narmer-app.firebasestorage.app",
            messagingSenderId: "283533596344",
            appId: "1:283533596344:web:1f11d300dbda9ee2a7864d",
            measurementId: "G-F329HN03Y1",
            vapidKey: "BB43Sjgi3tjn_Gi4nIjK0pYIJemvW6_esbrjwmgm5crR-jcB9mVHIbxGmKUBY51wfv5mwNLPTt4JfbkjJg-WtcY",
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
    