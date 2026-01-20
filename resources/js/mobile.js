/**
 * Capacitor Mobile Integration
 * Handles: Push Notifications, Device Info, Status Bar
 */

import { PushNotifications } from '@capacitor/push-notifications';
import { Device } from '@capacitor/device';
import { StatusBar, Style } from '@capacitor/status-bar';
import { Geolocation } from '@capacitor/geolocation';
import { LineLogin } from 'aile-capacitor-line-login';

async function initMobile() {
    console.log('Initializing Mobile Features...');

    // Expose Geolocation to global window for Vue access
    window.MobileGeolocation = Geolocation;
    window.MobilePush = PushNotifications;
    window.MobileDevice = Device;
    window.MobileLineLogin = LineLogin;

    try {
        // Set Status Bar Style
        await StatusBar.setStyle({ style: Style.Dark });
        await StatusBar.setBackgroundColor({ color: '#0f172a' }); // matches bg-slate-950

        // Handle Push Notifications
        let permStatus = await PushNotifications.checkPermissions();

        if (permStatus.receive === 'prompt') {
            permStatus = await PushNotifications.requestPermissions();
        }

        if (permStatus.receive === 'granted') {
            await PushNotifications.register();
        }

        // Listen for token registration
        PushNotifications.addListener('registration', (token) => {
            console.log('Push registration success, token: ' + token.value);
            saveTokenToServer(token.value);
        });

        PushNotifications.addListener('registrationError', (error) => {
            console.error('Error on registration: ' + JSON.stringify(error));
        });

        // Listen for notifications
        PushNotifications.addListener('pushNotificationReceived', (notification) => {
            console.log('Push received: ' + JSON.stringify(notification));
            // Show custom toast or update UI
            if (window.vm && window.vm.showToast) {
                window.vm.showToast(notification.body, 'info');
            }
        });

        PushNotifications.addListener('pushNotificationActionPerformed', (notification) => {
            console.log('Push action performed: ' + JSON.stringify(notification));
        });

    } catch (e) {
        console.warn('Capacitor features not available or error occurred:', e);
    }
}

async function saveTokenToServer(token) {
    const device = await Device.getInfo();
    const battery = await Device.getBatteryInfo();

    axios.post('/api/mobile/register-token', {
        token: token,
        platform: device.platform,
        model: device.model,
        os_version: device.osVersion,
        is_virtual: device.isVirtual
    }).then(response => {
        console.log('Token saved to server');
    }).catch(error => {
        console.error('Failed to save token:', error);
    });
}

// Start initialization if running in Capacitor
if (window.Capacitor) {
    initMobile();
}

export { initMobile };
