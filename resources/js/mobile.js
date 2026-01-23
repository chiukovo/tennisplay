/**
 * Capacitor Mobile Integration
 * Handles: Push Notifications, Device Info, Status Bar
 */

import { Capacitor } from '@capacitor/core';
import { PushNotifications } from '@capacitor/push-notifications';
import { Device } from '@capacitor/device';
import { StatusBar, Style } from '@capacitor/status-bar';
import { Geolocation } from '@capacitor/geolocation';
import { LineLogin } from 'aile-capacitor-line-login';
import { App } from '@capacitor/app';
import { Camera, CameraResultType, CameraSource } from '@capacitor/camera';
import { Share } from '@capacitor/share';
import { Haptics, ImpactStyle, NotificationType } from '@capacitor/haptics';

async function initMobile() {
    console.log('Initializing Mobile Features...');

    // Expose Geolocation to global window for Vue access
    window.MobileGeolocation = Geolocation;
    window.MobilePush = PushNotifications;
    window.MobileDevice = Device;
    window.MobileLineLogin = LineLogin;
    window.MobileCamera = Camera;
    window.MobileShare = Share;
    window.MobileHaptics = Haptics;

    // Helper for easier photography
    window.takeAppPhoto = async () => {
        try {
            const image = await Camera.getPhoto({
                quality: 90,
                allowEditing: true,
                resultType: CameraResultType.DataUrl,
                source: CameraSource.Prompt // Prompt allows choice between Gallery or Camera
            });
            return image.dataUrl;
        } catch (e) {
            console.error('Camera Error:', e);
            return null;
        }
    };

    // Helper for Native Sharing
    window.appShare = async (options) => {
        try {
            await Share.share({
                title: options.title || 'LoveTennis',
                text: options.text || '',
                url: options.url || window.location.href,
                dialogTitle: options.dialogTitle || '分享給球友',
            });
        } catch (e) {
            console.error('Share Error:', e);
        }
    };

    // Helper for Haptics (Vibration)
    window.appHaptic = async (type = 'impact', style = 'light') => {
        try {
            if (type === 'impact') {
                const impactStyle = style === 'heavy' ? ImpactStyle.Heavy : (style === 'medium' ? ImpactStyle.Medium : ImpactStyle.Light);
                await Haptics.impact({ style: impactStyle });
            } else if (type === 'notification') {
                const notifType = style === 'error' ? NotificationType.Error : (style === 'warning' ? NotificationType.Warning : NotificationType.Success);
                await Haptics.notification({ type: notifType });
            }
        } catch (e) {
            // Ignore error if not supported
        }
    };

    try {
        // Native-only Features - Explicitly exclude 'web' platform
        const platform = Capacitor.getPlatform();
        console.log(`Current Platform: ${platform}`);

        if (platform !== 'web') {
            // App Info & Version Check
            const info = await App.getInfo();
            console.log(`App Version: ${info.version} (${info.build})`);
            window.AppInfo = info;
            // Set Status Bar Style
            await StatusBar.setStyle({ style: Style.Dark });
            await StatusBar.setBackgroundColor({ color: '#0f172a' }); // matches bg-slate-950

            const pushEnabled = window.tennisConfig && window.tennisConfig.enable_push === true;

            if (pushEnabled) {
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
                    const data = notification.notification.data;
                    if (data) {
                        if (window.AppNavigate) {
                            if (data.event_id) {
                                window.AppNavigate('events', true, data.event_id);
                            } else if (data.uid) {
                                window.AppNavigate('profile', true, data.uid);
                            } else if (data.chat_uid) {
                                window.AppNavigate('messages', true, data.chat_uid);
                            }
                        } else {
                            // Store for later consumption by Vue
                            window.PendingAppNavigate = data;
                        }
                    }
                });
            } else {
                console.log('PushNotifications disabled: tennisConfig.enable_push is not true');
            }

            // App State Change (Refresh data when app returns from background)
            App.addListener('appStateChange', ({ isActive }) => {
                if (isActive) {
                    console.log('App became active, triggering data refresh...');
                    if (window.vm && typeof window.vm.loadMessages === 'function') {
                        window.vm.loadMessages();
                    }
                    if (window.vm && typeof window.vm.loadEvents === 'function') {
                        window.vm.loadEvents();
                    }
                }
            });
        } else {
            console.log('Running on Web, skipping native status bar and notification setup.');
        }

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

// Start initialization if running in Capacitor environment
// Robust check: window.Capacitor exists AND we are not on web platform
if (window.Capacitor && Capacitor.getPlatform() !== 'web') {
    initMobile();
}

export { initMobile };
