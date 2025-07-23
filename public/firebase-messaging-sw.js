// public/firebase-messaging-sw.js

importScripts(
    "https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"
);
importScripts(
    "https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js"
);

firebase.initializeApp({
    apiKey: "AIzaSyACaR4Znix8Tw3YiT_FE2DasPluCEXmtoE",
    authDomain: "e-commerce-fb183.firebaseapp.com",
    projectId: "e-commerce-fb183",
    storageBucket: "e-commerce-fb183.firebasestorage.app",
    messagingSenderId: "683824038399",
    appId: "1:683824038399:web:f30701460945005c3b27ca",
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
    console.log(
        "[firebase-messaging-sw.js] Received background message",
        payload
    );

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: "/icon.png",
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
