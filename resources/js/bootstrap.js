window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
window.io = require('socket.io-client');

window.initEcho = function() {
    if (window.Echo) return;

    const token = localStorage.getItem('auth_token');
    const runtimeWebsocketUrl = window.tennisConfig?.websocket_url || null;
    const defaultHost = `${window.location.protocol}//${window.location.hostname}:6001`;
    const echoConfig = {
        broadcaster: 'socket.io',
        host: runtimeWebsocketUrl || process.env.MIX_WEBSOCKET_URL || defaultHost,
        authEndpoint: '/broadcasting/auth',
        reconnectionAttempts: 5 // 限制重試次數，避免無限失敗
    };

    if (token) {
        echoConfig.auth = {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        };
    }

    try {
        window.Echo = new Echo(echoConfig);
        console.log('Echo Initialized on-demand');
    } catch (e) {
        console.error('Failed to init Echo', e);
    }
};
