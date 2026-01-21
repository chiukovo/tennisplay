/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@capacitor/app/dist/esm/definitions.js"
/*!*************************************************************!*\
  !*** ./node_modules/@capacitor/app/dist/esm/definitions.js ***!
  \*************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/// <reference types="@capacitor/cli" />

//# sourceMappingURL=definitions.js.map

/***/ },

/***/ "./node_modules/@capacitor/app/dist/esm/index.js"
/*!*******************************************************!*\
  !*** ./node_modules/@capacitor/app/dist/esm/index.js ***!
  \*******************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   App: () => (/* binding */ App)
/* harmony export */ });
/* harmony import */ var _capacitor_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @capacitor/core */ "./node_modules/@capacitor/core/dist/index.js");
/* harmony import */ var _definitions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./definitions */ "./node_modules/@capacitor/app/dist/esm/definitions.js");

const App = (0,_capacitor_core__WEBPACK_IMPORTED_MODULE_0__.registerPlugin)('App', {
    web: () => __webpack_require__.e(/*! import() */ "node_modules_capacitor_app_dist_esm_web_js").then(__webpack_require__.bind(__webpack_require__, /*! ./web */ "./node_modules/@capacitor/app/dist/esm/web.js")).then((m) => new m.AppWeb()),
});


//# sourceMappingURL=index.js.map

/***/ },

/***/ "./node_modules/@capacitor/camera/dist/esm/definitions.js"
/*!****************************************************************!*\
  !*** ./node_modules/@capacitor/camera/dist/esm/definitions.js ***!
  \****************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   CameraDirection: () => (/* binding */ CameraDirection),
/* harmony export */   CameraResultType: () => (/* binding */ CameraResultType),
/* harmony export */   CameraSource: () => (/* binding */ CameraSource)
/* harmony export */ });
var CameraSource;
(function (CameraSource) {
    /**
     * Prompts the user to select either the photo album or take a photo.
     */
    CameraSource["Prompt"] = "PROMPT";
    /**
     * Take a new photo using the camera.
     */
    CameraSource["Camera"] = "CAMERA";
    /**
     * Pick an existing photo from the gallery or photo album.
     */
    CameraSource["Photos"] = "PHOTOS";
})(CameraSource || (CameraSource = {}));
var CameraDirection;
(function (CameraDirection) {
    CameraDirection["Rear"] = "REAR";
    CameraDirection["Front"] = "FRONT";
})(CameraDirection || (CameraDirection = {}));
var CameraResultType;
(function (CameraResultType) {
    CameraResultType["Uri"] = "uri";
    CameraResultType["Base64"] = "base64";
    CameraResultType["DataUrl"] = "dataUrl";
})(CameraResultType || (CameraResultType = {}));
//# sourceMappingURL=definitions.js.map

/***/ },

/***/ "./node_modules/@capacitor/camera/dist/esm/index.js"
/*!**********************************************************!*\
  !*** ./node_modules/@capacitor/camera/dist/esm/index.js ***!
  \**********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Camera: () => (/* binding */ Camera),
/* harmony export */   CameraDirection: () => (/* reexport safe */ _definitions__WEBPACK_IMPORTED_MODULE_2__.CameraDirection),
/* harmony export */   CameraResultType: () => (/* reexport safe */ _definitions__WEBPACK_IMPORTED_MODULE_2__.CameraResultType),
/* harmony export */   CameraSource: () => (/* reexport safe */ _definitions__WEBPACK_IMPORTED_MODULE_2__.CameraSource)
/* harmony export */ });
/* harmony import */ var _capacitor_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @capacitor/core */ "./node_modules/@capacitor/core/dist/index.js");
/* harmony import */ var _web__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./web */ "./node_modules/@capacitor/camera/dist/esm/web.js");
/* harmony import */ var _definitions__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./definitions */ "./node_modules/@capacitor/camera/dist/esm/definitions.js");


const Camera = (0,_capacitor_core__WEBPACK_IMPORTED_MODULE_0__.registerPlugin)('Camera', {
    web: () => new _web__WEBPACK_IMPORTED_MODULE_1__.CameraWeb(),
});


//# sourceMappingURL=index.js.map

/***/ },

/***/ "./node_modules/@capacitor/camera/dist/esm/web.js"
/*!********************************************************!*\
  !*** ./node_modules/@capacitor/camera/dist/esm/web.js ***!
  \********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Camera: () => (/* binding */ Camera),
/* harmony export */   CameraWeb: () => (/* binding */ CameraWeb)
/* harmony export */ });
/* harmony import */ var _capacitor_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @capacitor/core */ "./node_modules/@capacitor/core/dist/index.js");
/* harmony import */ var _definitions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./definitions */ "./node_modules/@capacitor/camera/dist/esm/definitions.js");


class CameraWeb extends _capacitor_core__WEBPACK_IMPORTED_MODULE_0__.WebPlugin {
    async getPhoto(options) {
        // eslint-disable-next-line no-async-promise-executor
        return new Promise(async (resolve, reject) => {
            if (options.webUseInput || options.source === _definitions__WEBPACK_IMPORTED_MODULE_1__.CameraSource.Photos) {
                this.fileInputExperience(options, resolve, reject);
            }
            else if (options.source === _definitions__WEBPACK_IMPORTED_MODULE_1__.CameraSource.Prompt) {
                let actionSheet = document.querySelector('pwa-action-sheet');
                if (!actionSheet) {
                    actionSheet = document.createElement('pwa-action-sheet');
                    document.body.appendChild(actionSheet);
                }
                actionSheet.header = options.promptLabelHeader || 'Photo';
                actionSheet.cancelable = false;
                actionSheet.options = [
                    { title: options.promptLabelPhoto || 'From Photos' },
                    { title: options.promptLabelPicture || 'Take Picture' },
                ];
                actionSheet.addEventListener('onSelection', async (e) => {
                    const selection = e.detail;
                    if (selection === 0) {
                        this.fileInputExperience(options, resolve, reject);
                    }
                    else {
                        this.cameraExperience(options, resolve, reject);
                    }
                });
            }
            else {
                this.cameraExperience(options, resolve, reject);
            }
        });
    }
    async pickImages(_options) {
        // eslint-disable-next-line no-async-promise-executor
        return new Promise(async (resolve, reject) => {
            this.multipleFileInputExperience(resolve, reject);
        });
    }
    async cameraExperience(options, resolve, reject) {
        if (customElements.get('pwa-camera-modal')) {
            const cameraModal = document.createElement('pwa-camera-modal');
            cameraModal.facingMode = options.direction === _definitions__WEBPACK_IMPORTED_MODULE_1__.CameraDirection.Front ? 'user' : 'environment';
            document.body.appendChild(cameraModal);
            try {
                await cameraModal.componentOnReady();
                cameraModal.addEventListener('onPhoto', async (e) => {
                    const photo = e.detail;
                    if (photo === null) {
                        reject(new _capacitor_core__WEBPACK_IMPORTED_MODULE_0__.CapacitorException('User cancelled photos app'));
                    }
                    else if (photo instanceof Error) {
                        reject(photo);
                    }
                    else {
                        resolve(await this._getCameraPhoto(photo, options));
                    }
                    cameraModal.dismiss();
                    document.body.removeChild(cameraModal);
                });
                cameraModal.present();
            }
            catch (e) {
                this.fileInputExperience(options, resolve, reject);
            }
        }
        else {
            console.error(`Unable to load PWA Element 'pwa-camera-modal'. See the docs: https://capacitorjs.com/docs/web/pwa-elements.`);
            this.fileInputExperience(options, resolve, reject);
        }
    }
    fileInputExperience(options, resolve, reject) {
        let input = document.querySelector('#_capacitor-camera-input');
        const cleanup = () => {
            var _a;
            (_a = input.parentNode) === null || _a === void 0 ? void 0 : _a.removeChild(input);
        };
        if (!input) {
            input = document.createElement('input');
            input.id = '_capacitor-camera-input';
            input.type = 'file';
            input.hidden = true;
            document.body.appendChild(input);
            input.addEventListener('change', (_e) => {
                const file = input.files[0];
                let format = 'jpeg';
                if (file.type === 'image/png') {
                    format = 'png';
                }
                else if (file.type === 'image/gif') {
                    format = 'gif';
                }
                if (options.resultType === 'dataUrl' || options.resultType === 'base64') {
                    const reader = new FileReader();
                    reader.addEventListener('load', () => {
                        if (options.resultType === 'dataUrl') {
                            resolve({
                                dataUrl: reader.result,
                                format,
                            });
                        }
                        else if (options.resultType === 'base64') {
                            const b64 = reader.result.split(',')[1];
                            resolve({
                                base64String: b64,
                                format,
                            });
                        }
                        cleanup();
                    });
                    reader.readAsDataURL(file);
                }
                else {
                    resolve({
                        webPath: URL.createObjectURL(file),
                        format: format,
                    });
                    cleanup();
                }
            });
            input.addEventListener('cancel', (_e) => {
                reject(new _capacitor_core__WEBPACK_IMPORTED_MODULE_0__.CapacitorException('User cancelled photos app'));
                cleanup();
            });
        }
        input.accept = 'image/*';
        input.capture = true;
        if (options.source === _definitions__WEBPACK_IMPORTED_MODULE_1__.CameraSource.Photos || options.source === _definitions__WEBPACK_IMPORTED_MODULE_1__.CameraSource.Prompt) {
            input.removeAttribute('capture');
        }
        else if (options.direction === _definitions__WEBPACK_IMPORTED_MODULE_1__.CameraDirection.Front) {
            input.capture = 'user';
        }
        else if (options.direction === _definitions__WEBPACK_IMPORTED_MODULE_1__.CameraDirection.Rear) {
            input.capture = 'environment';
        }
        input.click();
    }
    multipleFileInputExperience(resolve, reject) {
        let input = document.querySelector('#_capacitor-camera-input-multiple');
        const cleanup = () => {
            var _a;
            (_a = input.parentNode) === null || _a === void 0 ? void 0 : _a.removeChild(input);
        };
        if (!input) {
            input = document.createElement('input');
            input.id = '_capacitor-camera-input-multiple';
            input.type = 'file';
            input.hidden = true;
            input.multiple = true;
            document.body.appendChild(input);
            input.addEventListener('change', (_e) => {
                const photos = [];
                // eslint-disable-next-line @typescript-eslint/prefer-for-of
                for (let i = 0; i < input.files.length; i++) {
                    const file = input.files[i];
                    let format = 'jpeg';
                    if (file.type === 'image/png') {
                        format = 'png';
                    }
                    else if (file.type === 'image/gif') {
                        format = 'gif';
                    }
                    photos.push({
                        webPath: URL.createObjectURL(file),
                        format: format,
                    });
                }
                resolve({ photos });
                cleanup();
            });
            input.addEventListener('cancel', (_e) => {
                reject(new _capacitor_core__WEBPACK_IMPORTED_MODULE_0__.CapacitorException('User cancelled photos app'));
                cleanup();
            });
        }
        input.accept = 'image/*';
        input.click();
    }
    _getCameraPhoto(photo, options) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            const format = photo.type.split('/')[1];
            if (options.resultType === 'uri') {
                resolve({
                    webPath: URL.createObjectURL(photo),
                    format: format,
                    saved: false,
                });
            }
            else {
                reader.readAsDataURL(photo);
                reader.onloadend = () => {
                    const r = reader.result;
                    if (options.resultType === 'dataUrl') {
                        resolve({
                            dataUrl: r,
                            format: format,
                            saved: false,
                        });
                    }
                    else {
                        resolve({
                            base64String: r.split(',')[1],
                            format: format,
                            saved: false,
                        });
                    }
                };
                reader.onerror = (e) => {
                    reject(e);
                };
            }
        });
    }
    async checkPermissions() {
        if (typeof navigator === 'undefined' || !navigator.permissions) {
            throw this.unavailable('Permissions API not available in this browser');
        }
        try {
            // https://developer.mozilla.org/en-US/docs/Web/API/Permissions/query
            // the specific permissions that are supported varies among browsers that implement the
            // permissions API, so we need a try/catch in case 'camera' is invalid
            const permission = await window.navigator.permissions.query({
                name: 'camera',
            });
            return {
                camera: permission.state,
                photos: 'granted',
            };
        }
        catch (_a) {
            throw this.unavailable('Camera permissions are not available in this browser');
        }
    }
    async requestPermissions() {
        throw this.unimplemented('Not implemented on web.');
    }
    async pickLimitedLibraryPhotos() {
        throw this.unavailable('Not implemented on web.');
    }
    async getLimitedLibraryPhotos() {
        throw this.unavailable('Not implemented on web.');
    }
}
const Camera = new CameraWeb();

//# sourceMappingURL=web.js.map

/***/ },

/***/ "./node_modules/@capacitor/core/dist/index.js"
/*!****************************************************!*\
  !*** ./node_modules/@capacitor/core/dist/index.js ***!
  \****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Capacitor: () => (/* binding */ Capacitor),
/* harmony export */   CapacitorCookies: () => (/* binding */ CapacitorCookies),
/* harmony export */   CapacitorException: () => (/* binding */ CapacitorException),
/* harmony export */   CapacitorHttp: () => (/* binding */ CapacitorHttp),
/* harmony export */   ExceptionCode: () => (/* binding */ ExceptionCode),
/* harmony export */   SystemBarType: () => (/* binding */ SystemBarType),
/* harmony export */   SystemBars: () => (/* binding */ SystemBars),
/* harmony export */   SystemBarsStyle: () => (/* binding */ SystemBarsStyle),
/* harmony export */   WebPlugin: () => (/* binding */ WebPlugin),
/* harmony export */   WebView: () => (/* binding */ WebView),
/* harmony export */   buildRequestInit: () => (/* binding */ buildRequestInit),
/* harmony export */   registerPlugin: () => (/* binding */ registerPlugin)
/* harmony export */ });
/*! Capacitor: https://capacitorjs.com/ - MIT License */
var ExceptionCode;
(function (ExceptionCode) {
    /**
     * API is not implemented.
     *
     * This usually means the API can't be used because it is not implemented for
     * the current platform.
     */
    ExceptionCode["Unimplemented"] = "UNIMPLEMENTED";
    /**
     * API is not available.
     *
     * This means the API can't be used right now because:
     *   - it is currently missing a prerequisite, such as network connectivity
     *   - it requires a particular platform or browser version
     */
    ExceptionCode["Unavailable"] = "UNAVAILABLE";
})(ExceptionCode || (ExceptionCode = {}));
class CapacitorException extends Error {
    constructor(message, code, data) {
        super(message);
        this.message = message;
        this.code = code;
        this.data = data;
    }
}
const getPlatformId = (win) => {
    var _a, _b;
    if (win === null || win === void 0 ? void 0 : win.androidBridge) {
        return 'android';
    }
    else if ((_b = (_a = win === null || win === void 0 ? void 0 : win.webkit) === null || _a === void 0 ? void 0 : _a.messageHandlers) === null || _b === void 0 ? void 0 : _b.bridge) {
        return 'ios';
    }
    else {
        return 'web';
    }
};

const createCapacitor = (win) => {
    const capCustomPlatform = win.CapacitorCustomPlatform || null;
    const cap = win.Capacitor || {};
    const Plugins = (cap.Plugins = cap.Plugins || {});
    const getPlatform = () => {
        return capCustomPlatform !== null ? capCustomPlatform.name : getPlatformId(win);
    };
    const isNativePlatform = () => getPlatform() !== 'web';
    const isPluginAvailable = (pluginName) => {
        const plugin = registeredPlugins.get(pluginName);
        if (plugin === null || plugin === void 0 ? void 0 : plugin.platforms.has(getPlatform())) {
            // JS implementation available for the current platform.
            return true;
        }
        if (getPluginHeader(pluginName)) {
            // Native implementation available.
            return true;
        }
        return false;
    };
    const getPluginHeader = (pluginName) => { var _a; return (_a = cap.PluginHeaders) === null || _a === void 0 ? void 0 : _a.find((h) => h.name === pluginName); };
    const handleError = (err) => win.console.error(err);
    const registeredPlugins = new Map();
    const registerPlugin = (pluginName, jsImplementations = {}) => {
        const registeredPlugin = registeredPlugins.get(pluginName);
        if (registeredPlugin) {
            console.warn(`Capacitor plugin "${pluginName}" already registered. Cannot register plugins twice.`);
            return registeredPlugin.proxy;
        }
        const platform = getPlatform();
        const pluginHeader = getPluginHeader(pluginName);
        let jsImplementation;
        const loadPluginImplementation = async () => {
            if (!jsImplementation && platform in jsImplementations) {
                jsImplementation =
                    typeof jsImplementations[platform] === 'function'
                        ? (jsImplementation = await jsImplementations[platform]())
                        : (jsImplementation = jsImplementations[platform]);
            }
            else if (capCustomPlatform !== null && !jsImplementation && 'web' in jsImplementations) {
                jsImplementation =
                    typeof jsImplementations['web'] === 'function'
                        ? (jsImplementation = await jsImplementations['web']())
                        : (jsImplementation = jsImplementations['web']);
            }
            return jsImplementation;
        };
        const createPluginMethod = (impl, prop) => {
            var _a, _b;
            if (pluginHeader) {
                const methodHeader = pluginHeader === null || pluginHeader === void 0 ? void 0 : pluginHeader.methods.find((m) => prop === m.name);
                if (methodHeader) {
                    if (methodHeader.rtype === 'promise') {
                        return (options) => cap.nativePromise(pluginName, prop.toString(), options);
                    }
                    else {
                        return (options, callback) => cap.nativeCallback(pluginName, prop.toString(), options, callback);
                    }
                }
                else if (impl) {
                    return (_a = impl[prop]) === null || _a === void 0 ? void 0 : _a.bind(impl);
                }
            }
            else if (impl) {
                return (_b = impl[prop]) === null || _b === void 0 ? void 0 : _b.bind(impl);
            }
            else {
                throw new CapacitorException(`"${pluginName}" plugin is not implemented on ${platform}`, ExceptionCode.Unimplemented);
            }
        };
        const createPluginMethodWrapper = (prop) => {
            let remove;
            const wrapper = (...args) => {
                const p = loadPluginImplementation().then((impl) => {
                    const fn = createPluginMethod(impl, prop);
                    if (fn) {
                        const p = fn(...args);
                        remove = p === null || p === void 0 ? void 0 : p.remove;
                        return p;
                    }
                    else {
                        throw new CapacitorException(`"${pluginName}.${prop}()" is not implemented on ${platform}`, ExceptionCode.Unimplemented);
                    }
                });
                if (prop === 'addListener') {
                    p.remove = async () => remove();
                }
                return p;
            };
            // Some flair ✨
            wrapper.toString = () => `${prop.toString()}() { [capacitor code] }`;
            Object.defineProperty(wrapper, 'name', {
                value: prop,
                writable: false,
                configurable: false,
            });
            return wrapper;
        };
        const addListener = createPluginMethodWrapper('addListener');
        const removeListener = createPluginMethodWrapper('removeListener');
        const addListenerNative = (eventName, callback) => {
            const call = addListener({ eventName }, callback);
            const remove = async () => {
                const callbackId = await call;
                removeListener({
                    eventName,
                    callbackId,
                }, callback);
            };
            const p = new Promise((resolve) => call.then(() => resolve({ remove })));
            p.remove = async () => {
                console.warn(`Using addListener() without 'await' is deprecated.`);
                await remove();
            };
            return p;
        };
        const proxy = new Proxy({}, {
            get(_, prop) {
                switch (prop) {
                    // https://github.com/facebook/react/issues/20030
                    case '$$typeof':
                        return undefined;
                    case 'toJSON':
                        return () => ({});
                    case 'addListener':
                        return pluginHeader ? addListenerNative : addListener;
                    case 'removeListener':
                        return removeListener;
                    default:
                        return createPluginMethodWrapper(prop);
                }
            },
        });
        Plugins[pluginName] = proxy;
        registeredPlugins.set(pluginName, {
            name: pluginName,
            proxy,
            platforms: new Set([...Object.keys(jsImplementations), ...(pluginHeader ? [platform] : [])]),
        });
        return proxy;
    };
    // Add in convertFileSrc for web, it will already be available in native context
    if (!cap.convertFileSrc) {
        cap.convertFileSrc = (filePath) => filePath;
    }
    cap.getPlatform = getPlatform;
    cap.handleError = handleError;
    cap.isNativePlatform = isNativePlatform;
    cap.isPluginAvailable = isPluginAvailable;
    cap.registerPlugin = registerPlugin;
    cap.Exception = CapacitorException;
    cap.DEBUG = !!cap.DEBUG;
    cap.isLoggingEnabled = !!cap.isLoggingEnabled;
    return cap;
};
const initCapacitorGlobal = (win) => (win.Capacitor = createCapacitor(win));

const Capacitor = /*#__PURE__*/ initCapacitorGlobal(typeof globalThis !== 'undefined'
    ? globalThis
    : typeof self !== 'undefined'
        ? self
        : typeof window !== 'undefined'
            ? window
            : typeof __webpack_require__.g !== 'undefined'
                ? __webpack_require__.g
                : {});
const registerPlugin = Capacitor.registerPlugin;

/**
 * Base class web plugins should extend.
 */
class WebPlugin {
    constructor() {
        this.listeners = {};
        this.retainedEventArguments = {};
        this.windowListeners = {};
    }
    addListener(eventName, listenerFunc) {
        let firstListener = false;
        const listeners = this.listeners[eventName];
        if (!listeners) {
            this.listeners[eventName] = [];
            firstListener = true;
        }
        this.listeners[eventName].push(listenerFunc);
        // If we haven't added a window listener for this event and it requires one,
        // go ahead and add it
        const windowListener = this.windowListeners[eventName];
        if (windowListener && !windowListener.registered) {
            this.addWindowListener(windowListener);
        }
        if (firstListener) {
            this.sendRetainedArgumentsForEvent(eventName);
        }
        const remove = async () => this.removeListener(eventName, listenerFunc);
        const p = Promise.resolve({ remove });
        return p;
    }
    async removeAllListeners() {
        this.listeners = {};
        for (const listener in this.windowListeners) {
            this.removeWindowListener(this.windowListeners[listener]);
        }
        this.windowListeners = {};
    }
    notifyListeners(eventName, data, retainUntilConsumed) {
        const listeners = this.listeners[eventName];
        if (!listeners) {
            if (retainUntilConsumed) {
                let args = this.retainedEventArguments[eventName];
                if (!args) {
                    args = [];
                }
                args.push(data);
                this.retainedEventArguments[eventName] = args;
            }
            return;
        }
        listeners.forEach((listener) => listener(data));
    }
    hasListeners(eventName) {
        var _a;
        return !!((_a = this.listeners[eventName]) === null || _a === void 0 ? void 0 : _a.length);
    }
    registerWindowListener(windowEventName, pluginEventName) {
        this.windowListeners[pluginEventName] = {
            registered: false,
            windowEventName,
            pluginEventName,
            handler: (event) => {
                this.notifyListeners(pluginEventName, event);
            },
        };
    }
    unimplemented(msg = 'not implemented') {
        return new Capacitor.Exception(msg, ExceptionCode.Unimplemented);
    }
    unavailable(msg = 'not available') {
        return new Capacitor.Exception(msg, ExceptionCode.Unavailable);
    }
    async removeListener(eventName, listenerFunc) {
        const listeners = this.listeners[eventName];
        if (!listeners) {
            return;
        }
        const index = listeners.indexOf(listenerFunc);
        this.listeners[eventName].splice(index, 1);
        // If there are no more listeners for this type of event,
        // remove the window listener
        if (!this.listeners[eventName].length) {
            this.removeWindowListener(this.windowListeners[eventName]);
        }
    }
    addWindowListener(handle) {
        window.addEventListener(handle.windowEventName, handle.handler);
        handle.registered = true;
    }
    removeWindowListener(handle) {
        if (!handle) {
            return;
        }
        window.removeEventListener(handle.windowEventName, handle.handler);
        handle.registered = false;
    }
    sendRetainedArgumentsForEvent(eventName) {
        const args = this.retainedEventArguments[eventName];
        if (!args) {
            return;
        }
        delete this.retainedEventArguments[eventName];
        args.forEach((arg) => {
            this.notifyListeners(eventName, arg);
        });
    }
}

const WebView = /*#__PURE__*/ registerPlugin('WebView');
/******** END WEB VIEW PLUGIN ********/
/******** COOKIES PLUGIN ********/
/**
 * Safely web encode a string value (inspired by js-cookie)
 * @param str The string value to encode
 */
const encode = (str) => encodeURIComponent(str)
    .replace(/%(2[346B]|5E|60|7C)/g, decodeURIComponent)
    .replace(/[()]/g, escape);
/**
 * Safely web decode a string value (inspired by js-cookie)
 * @param str The string value to decode
 */
const decode = (str) => str.replace(/(%[\dA-F]{2})+/gi, decodeURIComponent);
class CapacitorCookiesPluginWeb extends WebPlugin {
    async getCookies() {
        const cookies = document.cookie;
        const cookieMap = {};
        cookies.split(';').forEach((cookie) => {
            if (cookie.length <= 0)
                return;
            // Replace first "=" with CAP_COOKIE to prevent splitting on additional "="
            let [key, value] = cookie.replace(/=/, 'CAP_COOKIE').split('CAP_COOKIE');
            key = decode(key).trim();
            value = decode(value).trim();
            cookieMap[key] = value;
        });
        return cookieMap;
    }
    async setCookie(options) {
        try {
            // Safely Encoded Key/Value
            const encodedKey = encode(options.key);
            const encodedValue = encode(options.value);
            // Clean & sanitize options
            const expires = `; expires=${(options.expires || '').replace('expires=', '')}`; // Default is "; expires="
            const path = (options.path || '/').replace('path=', ''); // Default is "path=/"
            const domain = options.url != null && options.url.length > 0 ? `domain=${options.url}` : '';
            document.cookie = `${encodedKey}=${encodedValue || ''}${expires}; path=${path}; ${domain};`;
        }
        catch (error) {
            return Promise.reject(error);
        }
    }
    async deleteCookie(options) {
        try {
            document.cookie = `${options.key}=; Max-Age=0`;
        }
        catch (error) {
            return Promise.reject(error);
        }
    }
    async clearCookies() {
        try {
            const cookies = document.cookie.split(';') || [];
            for (const cookie of cookies) {
                document.cookie = cookie.replace(/^ +/, '').replace(/=.*/, `=;expires=${new Date().toUTCString()};path=/`);
            }
        }
        catch (error) {
            return Promise.reject(error);
        }
    }
    async clearAllCookies() {
        try {
            await this.clearCookies();
        }
        catch (error) {
            return Promise.reject(error);
        }
    }
}
const CapacitorCookies = registerPlugin('CapacitorCookies', {
    web: () => new CapacitorCookiesPluginWeb(),
});
// UTILITY FUNCTIONS
/**
 * Read in a Blob value and return it as a base64 string
 * @param blob The blob value to convert to a base64 string
 */
const readBlobAsBase64 = async (blob) => new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => {
        const base64String = reader.result;
        // remove prefix "data:application/pdf;base64,"
        resolve(base64String.indexOf(',') >= 0 ? base64String.split(',')[1] : base64String);
    };
    reader.onerror = (error) => reject(error);
    reader.readAsDataURL(blob);
});
/**
 * Normalize an HttpHeaders map by lowercasing all of the values
 * @param headers The HttpHeaders object to normalize
 */
const normalizeHttpHeaders = (headers = {}) => {
    const originalKeys = Object.keys(headers);
    const loweredKeys = Object.keys(headers).map((k) => k.toLocaleLowerCase());
    const normalized = loweredKeys.reduce((acc, key, index) => {
        acc[key] = headers[originalKeys[index]];
        return acc;
    }, {});
    return normalized;
};
/**
 * Builds a string of url parameters that
 * @param params A map of url parameters
 * @param shouldEncode true if you should encodeURIComponent() the values (true by default)
 */
const buildUrlParams = (params, shouldEncode = true) => {
    if (!params)
        return null;
    const output = Object.entries(params).reduce((accumulator, entry) => {
        const [key, value] = entry;
        let encodedValue;
        let item;
        if (Array.isArray(value)) {
            item = '';
            value.forEach((str) => {
                encodedValue = shouldEncode ? encodeURIComponent(str) : str;
                item += `${key}=${encodedValue}&`;
            });
            // last character will always be "&" so slice it off
            item.slice(0, -1);
        }
        else {
            encodedValue = shouldEncode ? encodeURIComponent(value) : value;
            item = `${key}=${encodedValue}`;
        }
        return `${accumulator}&${item}`;
    }, '');
    // Remove initial "&" from the reduce
    return output.substr(1);
};
/**
 * Build the RequestInit object based on the options passed into the initial request
 * @param options The Http plugin options
 * @param extra Any extra RequestInit values
 */
const buildRequestInit = (options, extra = {}) => {
    const output = Object.assign({ method: options.method || 'GET', headers: options.headers }, extra);
    // Get the content-type
    const headers = normalizeHttpHeaders(options.headers);
    const type = headers['content-type'] || '';
    // If body is already a string, then pass it through as-is.
    if (typeof options.data === 'string') {
        output.body = options.data;
    }
    // Build request initializers based off of content-type
    else if (type.includes('application/x-www-form-urlencoded')) {
        const params = new URLSearchParams();
        for (const [key, value] of Object.entries(options.data || {})) {
            params.set(key, value);
        }
        output.body = params.toString();
    }
    else if (type.includes('multipart/form-data') || options.data instanceof FormData) {
        const form = new FormData();
        if (options.data instanceof FormData) {
            options.data.forEach((value, key) => {
                form.append(key, value);
            });
        }
        else {
            for (const key of Object.keys(options.data)) {
                form.append(key, options.data[key]);
            }
        }
        output.body = form;
        const headers = new Headers(output.headers);
        headers.delete('content-type'); // content-type will be set by `window.fetch` to includy boundary
        output.headers = headers;
    }
    else if (type.includes('application/json') || typeof options.data === 'object') {
        output.body = JSON.stringify(options.data);
    }
    return output;
};
// WEB IMPLEMENTATION
class CapacitorHttpPluginWeb extends WebPlugin {
    /**
     * Perform an Http request given a set of options
     * @param options Options to build the HTTP request
     */
    async request(options) {
        const requestInit = buildRequestInit(options, options.webFetchExtra);
        const urlParams = buildUrlParams(options.params, options.shouldEncodeUrlParams);
        const url = urlParams ? `${options.url}?${urlParams}` : options.url;
        const response = await fetch(url, requestInit);
        const contentType = response.headers.get('content-type') || '';
        // Default to 'text' responseType so no parsing happens
        let { responseType = 'text' } = response.ok ? options : {};
        // If the response content-type is json, force the response to be json
        if (contentType.includes('application/json')) {
            responseType = 'json';
        }
        let data;
        let blob;
        switch (responseType) {
            case 'arraybuffer':
            case 'blob':
                blob = await response.blob();
                data = await readBlobAsBase64(blob);
                break;
            case 'json':
                data = await response.json();
                break;
            case 'document':
            case 'text':
            default:
                data = await response.text();
        }
        // Convert fetch headers to Capacitor HttpHeaders
        const headers = {};
        response.headers.forEach((value, key) => {
            headers[key] = value;
        });
        return {
            data,
            headers,
            status: response.status,
            url: response.url,
        };
    }
    /**
     * Perform an Http GET request given a set of options
     * @param options Options to build the HTTP request
     */
    async get(options) {
        return this.request(Object.assign(Object.assign({}, options), { method: 'GET' }));
    }
    /**
     * Perform an Http POST request given a set of options
     * @param options Options to build the HTTP request
     */
    async post(options) {
        return this.request(Object.assign(Object.assign({}, options), { method: 'POST' }));
    }
    /**
     * Perform an Http PUT request given a set of options
     * @param options Options to build the HTTP request
     */
    async put(options) {
        return this.request(Object.assign(Object.assign({}, options), { method: 'PUT' }));
    }
    /**
     * Perform an Http PATCH request given a set of options
     * @param options Options to build the HTTP request
     */
    async patch(options) {
        return this.request(Object.assign(Object.assign({}, options), { method: 'PATCH' }));
    }
    /**
     * Perform an Http DELETE request given a set of options
     * @param options Options to build the HTTP request
     */
    async delete(options) {
        return this.request(Object.assign(Object.assign({}, options), { method: 'DELETE' }));
    }
}
const CapacitorHttp = registerPlugin('CapacitorHttp', {
    web: () => new CapacitorHttpPluginWeb(),
});
/******** END HTTP PLUGIN ********/
/******** SYSTEM BARS PLUGIN ********/
/**
 * Available status bar styles.
 */
var SystemBarsStyle;
(function (SystemBarsStyle) {
    /**
     * Light system bar content on a dark background.
     *
     * @since 8.0.0
     */
    SystemBarsStyle["Dark"] = "DARK";
    /**
     * For dark system bar content on a light background.
     *
     * @since 8.0.0
     */
    SystemBarsStyle["Light"] = "LIGHT";
    /**
     * The style is based on the device appearance or the underlying content.
     * If the device is using Dark mode, the system bars content will be light.
     * If the device is using Light mode, the system bars content will be dark.
     *
     * @since 8.0.0
     */
    SystemBarsStyle["Default"] = "DEFAULT";
})(SystemBarsStyle || (SystemBarsStyle = {}));
/**
 * Available system bar types.
 */
var SystemBarType;
(function (SystemBarType) {
    /**
     * The top status bar on both Android and iOS.
     *
     * @since 8.0.0
     */
    SystemBarType["StatusBar"] = "StatusBar";
    /**
     * The navigation bar (or gesture bar on iOS) on both Android and iOS.
     *
     * @since 8.0.0
     */
    SystemBarType["NavigationBar"] = "NavigationBar";
})(SystemBarType || (SystemBarType = {}));
class SystemBarsPluginWeb extends WebPlugin {
    async setStyle() {
        this.unavailable('not available for web');
    }
    async setAnimation() {
        this.unavailable('not available for web');
    }
    async show() {
        this.unavailable('not available for web');
    }
    async hide() {
        this.unavailable('not available for web');
    }
}
const SystemBars = registerPlugin('SystemBars', {
    web: () => new SystemBarsPluginWeb(),
});
/******** END SYSTEM BARS PLUGIN ********/


//# sourceMappingURL=index.js.map


/***/ },

/***/ "./node_modules/@capacitor/device/dist/esm/definitions.js"
/*!****************************************************************!*\
  !*** ./node_modules/@capacitor/device/dist/esm/definitions.js ***!
  \****************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);

//# sourceMappingURL=definitions.js.map

/***/ },

/***/ "./node_modules/@capacitor/device/dist/esm/index.js"
/*!**********************************************************!*\
  !*** ./node_modules/@capacitor/device/dist/esm/index.js ***!
  \**********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Device: () => (/* binding */ Device)
/* harmony export */ });
/* harmony import */ var _capacitor_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @capacitor/core */ "./node_modules/@capacitor/core/dist/index.js");
/* harmony import */ var _definitions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./definitions */ "./node_modules/@capacitor/device/dist/esm/definitions.js");

const Device = (0,_capacitor_core__WEBPACK_IMPORTED_MODULE_0__.registerPlugin)('Device', {
    web: () => __webpack_require__.e(/*! import() */ "node_modules_capacitor_device_dist_esm_web_js").then(__webpack_require__.bind(__webpack_require__, /*! ./web */ "./node_modules/@capacitor/device/dist/esm/web.js")).then((m) => new m.DeviceWeb()),
});


//# sourceMappingURL=index.js.map

/***/ },

/***/ "./node_modules/@capacitor/geolocation/dist/esm/definitions.js"
/*!*********************************************************************!*\
  !*** ./node_modules/@capacitor/geolocation/dist/esm/definitions.js ***!
  \*********************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);

//# sourceMappingURL=definitions.js.map

/***/ },

/***/ "./node_modules/@capacitor/geolocation/dist/esm/index.js"
/*!***************************************************************!*\
  !*** ./node_modules/@capacitor/geolocation/dist/esm/index.js ***!
  \***************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Geolocation: () => (/* binding */ Geolocation)
/* harmony export */ });
/* harmony import */ var _capacitor_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @capacitor/core */ "./node_modules/@capacitor/core/dist/index.js");
/* harmony import */ var _capacitor_synapse__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @capacitor/synapse */ "./node_modules/@capacitor/synapse/dist/synapse.mjs");
/* harmony import */ var _definitions__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./definitions */ "./node_modules/@capacitor/geolocation/dist/esm/definitions.js");


const Geolocation = (0,_capacitor_core__WEBPACK_IMPORTED_MODULE_0__.registerPlugin)('Geolocation', {
    web: () => __webpack_require__.e(/*! import() */ "node_modules_capacitor_geolocation_dist_esm_web_js").then(__webpack_require__.bind(__webpack_require__, /*! ./web */ "./node_modules/@capacitor/geolocation/dist/esm/web.js")).then((m) => new m.GeolocationWeb()),
});
(0,_capacitor_synapse__WEBPACK_IMPORTED_MODULE_1__.exposeSynapse)();


//# sourceMappingURL=index.js.map

/***/ },

/***/ "./node_modules/@capacitor/haptics/dist/esm/definitions.js"
/*!*****************************************************************!*\
  !*** ./node_modules/@capacitor/haptics/dist/esm/definitions.js ***!
  \*****************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ImpactStyle: () => (/* binding */ ImpactStyle),
/* harmony export */   NotificationType: () => (/* binding */ NotificationType)
/* harmony export */ });
var ImpactStyle;
(function (ImpactStyle) {
    /**
     * A collision between large, heavy user interface elements
     *
     * @since 1.0.0
     */
    ImpactStyle["Heavy"] = "HEAVY";
    /**
     * A collision between moderately sized user interface elements
     *
     * @since 1.0.0
     */
    ImpactStyle["Medium"] = "MEDIUM";
    /**
     * A collision between small, light user interface elements
     *
     * @since 1.0.0
     */
    ImpactStyle["Light"] = "LIGHT";
})(ImpactStyle || (ImpactStyle = {}));
var NotificationType;
(function (NotificationType) {
    /**
     * A notification feedback type indicating that a task has completed successfully
     *
     * @since 1.0.0
     */
    NotificationType["Success"] = "SUCCESS";
    /**
     * A notification feedback type indicating that a task has produced a warning
     *
     * @since 1.0.0
     */
    NotificationType["Warning"] = "WARNING";
    /**
     * A notification feedback type indicating that a task has failed
     *
     * @since 1.0.0
     */
    NotificationType["Error"] = "ERROR";
})(NotificationType || (NotificationType = {}));
//# sourceMappingURL=definitions.js.map

/***/ },

/***/ "./node_modules/@capacitor/haptics/dist/esm/index.js"
/*!***********************************************************!*\
  !*** ./node_modules/@capacitor/haptics/dist/esm/index.js ***!
  \***********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Haptics: () => (/* binding */ Haptics),
/* harmony export */   ImpactStyle: () => (/* reexport safe */ _definitions__WEBPACK_IMPORTED_MODULE_1__.ImpactStyle),
/* harmony export */   NotificationType: () => (/* reexport safe */ _definitions__WEBPACK_IMPORTED_MODULE_1__.NotificationType)
/* harmony export */ });
/* harmony import */ var _capacitor_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @capacitor/core */ "./node_modules/@capacitor/core/dist/index.js");
/* harmony import */ var _definitions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./definitions */ "./node_modules/@capacitor/haptics/dist/esm/definitions.js");

const Haptics = (0,_capacitor_core__WEBPACK_IMPORTED_MODULE_0__.registerPlugin)('Haptics', {
    web: () => __webpack_require__.e(/*! import() */ "node_modules_capacitor_haptics_dist_esm_web_js").then(__webpack_require__.bind(__webpack_require__, /*! ./web */ "./node_modules/@capacitor/haptics/dist/esm/web.js")).then((m) => new m.HapticsWeb()),
});


//# sourceMappingURL=index.js.map

/***/ },

/***/ "./node_modules/@capacitor/push-notifications/dist/esm/definitions.js"
/*!****************************************************************************!*\
  !*** ./node_modules/@capacitor/push-notifications/dist/esm/definitions.js ***!
  \****************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/// <reference types="@capacitor/cli" />

//# sourceMappingURL=definitions.js.map

/***/ },

/***/ "./node_modules/@capacitor/push-notifications/dist/esm/index.js"
/*!**********************************************************************!*\
  !*** ./node_modules/@capacitor/push-notifications/dist/esm/index.js ***!
  \**********************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PushNotifications: () => (/* binding */ PushNotifications)
/* harmony export */ });
/* harmony import */ var _capacitor_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @capacitor/core */ "./node_modules/@capacitor/core/dist/index.js");
/* harmony import */ var _definitions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./definitions */ "./node_modules/@capacitor/push-notifications/dist/esm/definitions.js");

const PushNotifications = (0,_capacitor_core__WEBPACK_IMPORTED_MODULE_0__.registerPlugin)('PushNotifications', {});


//# sourceMappingURL=index.js.map

/***/ },

/***/ "./node_modules/@capacitor/share/dist/esm/definitions.js"
/*!***************************************************************!*\
  !*** ./node_modules/@capacitor/share/dist/esm/definitions.js ***!
  \***************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);

//# sourceMappingURL=definitions.js.map

/***/ },

/***/ "./node_modules/@capacitor/share/dist/esm/index.js"
/*!*********************************************************!*\
  !*** ./node_modules/@capacitor/share/dist/esm/index.js ***!
  \*********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Share: () => (/* binding */ Share)
/* harmony export */ });
/* harmony import */ var _capacitor_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @capacitor/core */ "./node_modules/@capacitor/core/dist/index.js");
/* harmony import */ var _definitions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./definitions */ "./node_modules/@capacitor/share/dist/esm/definitions.js");

const Share = (0,_capacitor_core__WEBPACK_IMPORTED_MODULE_0__.registerPlugin)('Share', {
    web: () => __webpack_require__.e(/*! import() */ "node_modules_capacitor_share_dist_esm_web_js").then(__webpack_require__.bind(__webpack_require__, /*! ./web */ "./node_modules/@capacitor/share/dist/esm/web.js")).then((m) => new m.ShareWeb()),
});


//# sourceMappingURL=index.js.map

/***/ },

/***/ "./node_modules/@capacitor/status-bar/dist/esm/definitions.js"
/*!********************************************************************!*\
  !*** ./node_modules/@capacitor/status-bar/dist/esm/definitions.js ***!
  \********************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Animation: () => (/* binding */ Animation),
/* harmony export */   StatusBarAnimation: () => (/* binding */ StatusBarAnimation),
/* harmony export */   StatusBarStyle: () => (/* binding */ StatusBarStyle),
/* harmony export */   Style: () => (/* binding */ Style)
/* harmony export */ });
/// <reference types="@capacitor/cli" />
var Style;
(function (Style) {
    /**
     * Light text for dark backgrounds.
     *
     * @since 1.0.0
     */
    Style["Dark"] = "DARK";
    /**
     * Dark text for light backgrounds.
     *
     * @since 1.0.0
     */
    Style["Light"] = "LIGHT";
    /**
     * The style is based on the device appearance.
     * If the device is using Dark mode, the statusbar text will be light.
     * If the device is using Light mode, the statusbar text will be dark.
     *
     * @since 1.0.0
     */
    Style["Default"] = "DEFAULT";
})(Style || (Style = {}));
var Animation;
(function (Animation) {
    /**
     * No animation during show/hide.
     *
     * @since 1.0.0
     */
    Animation["None"] = "NONE";
    /**
     * Slide animation during show/hide.
     * It doesn't work on iOS 15+.
     *
     * @deprecated Use Animation.Fade or Animation.None instead.
     *
     * @since 1.0.0
     */
    Animation["Slide"] = "SLIDE";
    /**
     * Fade animation during show/hide.
     *
     * @since 1.0.0
     */
    Animation["Fade"] = "FADE";
})(Animation || (Animation = {}));
/**
 * @deprecated Use `Animation`.
 * @since 1.0.0
 */
const StatusBarAnimation = Animation;
/**
 * @deprecated Use `Style`.
 * @since 1.0.0
 */
const StatusBarStyle = Style;
//# sourceMappingURL=definitions.js.map

/***/ },

/***/ "./node_modules/@capacitor/status-bar/dist/esm/index.js"
/*!**************************************************************!*\
  !*** ./node_modules/@capacitor/status-bar/dist/esm/index.js ***!
  \**************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Animation: () => (/* reexport safe */ _definitions__WEBPACK_IMPORTED_MODULE_1__.Animation),
/* harmony export */   StatusBar: () => (/* binding */ StatusBar),
/* harmony export */   StatusBarAnimation: () => (/* reexport safe */ _definitions__WEBPACK_IMPORTED_MODULE_1__.StatusBarAnimation),
/* harmony export */   StatusBarStyle: () => (/* reexport safe */ _definitions__WEBPACK_IMPORTED_MODULE_1__.StatusBarStyle),
/* harmony export */   Style: () => (/* reexport safe */ _definitions__WEBPACK_IMPORTED_MODULE_1__.Style)
/* harmony export */ });
/* harmony import */ var _capacitor_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @capacitor/core */ "./node_modules/@capacitor/core/dist/index.js");
/* harmony import */ var _definitions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./definitions */ "./node_modules/@capacitor/status-bar/dist/esm/definitions.js");

const StatusBar = (0,_capacitor_core__WEBPACK_IMPORTED_MODULE_0__.registerPlugin)('StatusBar');


//# sourceMappingURL=index.js.map

/***/ },

/***/ "./node_modules/@capacitor/synapse/dist/synapse.mjs"
/*!**********************************************************!*\
  !*** ./node_modules/@capacitor/synapse/dist/synapse.mjs ***!
  \**********************************************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   exposeSynapse: () => (/* binding */ f)
/* harmony export */ });
function s(t) {
  t.CapacitorUtils.Synapse = new Proxy(
    {},
    {
      get(e, n) {
        return new Proxy({}, {
          get(w, o) {
            return (c, p, r) => {
              const i = t.Capacitor.Plugins[n];
              if (i === void 0) {
                r(new Error(`Capacitor plugin ${n} not found`));
                return;
              }
              if (typeof i[o] != "function") {
                r(new Error(`Method ${o} not found in Capacitor plugin ${n}`));
                return;
              }
              (async () => {
                try {
                  const a = await i[o](c);
                  p(a);
                } catch (a) {
                  r(a);
                }
              })();
            };
          }
        });
      }
    }
  );
}
function u(t) {
  t.CapacitorUtils.Synapse = new Proxy(
    {},
    {
      get(e, n) {
        return t.cordova.plugins[n];
      }
    }
  );
}
function f(t = !1) {
  typeof window > "u" || (window.CapacitorUtils = window.CapacitorUtils || {}, window.Capacitor !== void 0 && !t ? s(window) : window.cordova !== void 0 && u(window));
}



/***/ },

/***/ "./node_modules/aile-capacitor-line-login/dist/esm/definitions.js"
/*!************************************************************************!*\
  !*** ./node_modules/aile-capacitor-line-login/dist/esm/definitions.js ***!
  \************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);

//# sourceMappingURL=definitions.js.map

/***/ },

/***/ "./node_modules/aile-capacitor-line-login/dist/esm/index.js"
/*!******************************************************************!*\
  !*** ./node_modules/aile-capacitor-line-login/dist/esm/index.js ***!
  \******************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   LineLogin: () => (/* binding */ LineLogin),
/* harmony export */   LineLoginHelpers: () => (/* binding */ LineLoginHelpers)
/* harmony export */ });
/* harmony import */ var _capacitor_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @capacitor/core */ "./node_modules/@capacitor/core/dist/index.js");
/* harmony import */ var _definitions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./definitions */ "./node_modules/aile-capacitor-line-login/dist/esm/definitions.js");

const LineLogin = (0,_capacitor_core__WEBPACK_IMPORTED_MODULE_0__.registerPlugin)('LineLogin', {
    web: () => __webpack_require__.e(/*! import() */ "node_modules_aile-capacitor-line-login_dist_esm_web_js").then(__webpack_require__.bind(__webpack_require__, /*! ./web */ "./node_modules/aile-capacitor-line-login/dist/esm/web.js")).then((m) => new m.LineLoginWeb()),
});


// 导出便于使用的辅助函数
const LineLoginHelpers = {
    /**
     * 检查当前平台是否支持Line登录
     */
    isPlatformSupported() {
        var _a;
        const platform = (_a = window.Capacitor) === null || _a === void 0 ? void 0 : _a.getPlatform();
        return ['web', 'ios', 'android'].includes(platform);
    },
    /**
     * 获取当前平台信息
     */
    getCurrentPlatform() {
        var _a;
        return ((_a = window.Capacitor) === null || _a === void 0 ? void 0 : _a.getPlatform()) || 'web';
    },
    /**
     * 检查是否为Web平台
     */
    isWebPlatform() {
        return this.getCurrentPlatform() === 'web';
    },
    /**
     * 检查是否为原生平台
     */
    isNativePlatform() {
        const platform = this.getCurrentPlatform();
        return platform === 'ios' || platform === 'android';
    }
};
//# sourceMappingURL=index.js.map

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/ensure chunk */
/******/ 	(() => {
/******/ 		__webpack_require__.f = {};
/******/ 		// This file contains only the entry chunk.
/******/ 		// The chunk loading function for additional chunks
/******/ 		__webpack_require__.e = (chunkId) => {
/******/ 			return Promise.all(Object.keys(__webpack_require__.f).reduce((promises, key) => {
/******/ 				__webpack_require__.f[key](chunkId, promises);
/******/ 				return promises;
/******/ 			}, []));
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/get javascript chunk filename */
/******/ 	(() => {
/******/ 		// This function allow to reference async chunks
/******/ 		__webpack_require__.u = (chunkId) => {
/******/ 			// return url for filenames not based on template
/******/ 			if ({"node_modules_capacitor_device_dist_esm_web_js":1,"node_modules_capacitor_geolocation_dist_esm_web_js":1,"node_modules_aile-capacitor-line-login_dist_esm_web_js":1,"node_modules_capacitor_app_dist_esm_web_js":1,"node_modules_capacitor_share_dist_esm_web_js":1,"node_modules_capacitor_haptics_dist_esm_web_js":1}[chunkId]) return "js/" + chunkId + ".js";
/******/ 			// return url for filenames based on template
/******/ 			return undefined;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/get mini-css chunk filename */
/******/ 	(() => {
/******/ 		// This function allow to reference all chunks
/******/ 		__webpack_require__.miniCssF = (chunkId) => {
/******/ 			// return url for filenames based on template
/******/ 			return undefined;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/global */
/******/ 	(() => {
/******/ 		__webpack_require__.g = (function() {
/******/ 			if (typeof globalThis === 'object') return globalThis;
/******/ 			try {
/******/ 				return this || new Function('return this')();
/******/ 			} catch (e) {
/******/ 				if (typeof window === 'object') return window;
/******/ 			}
/******/ 		})();
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/load script */
/******/ 	(() => {
/******/ 		var inProgress = {};
/******/ 		// data-webpack is not used as build has no uniqueName
/******/ 		// loadScript function to load a script via script tag
/******/ 		__webpack_require__.l = (url, done, key, chunkId) => {
/******/ 			if(inProgress[url]) { inProgress[url].push(done); return; }
/******/ 			var script, needAttach;
/******/ 			if(key !== undefined) {
/******/ 				var scripts = document.getElementsByTagName("script");
/******/ 				for(var i = 0; i < scripts.length; i++) {
/******/ 					var s = scripts[i];
/******/ 					if(s.getAttribute("src") == url) { script = s; break; }
/******/ 				}
/******/ 			}
/******/ 			if(!script) {
/******/ 				needAttach = true;
/******/ 				script = document.createElement('script');
/******/ 		
/******/ 				script.charset = 'utf-8';
/******/ 				if (__webpack_require__.nc) {
/******/ 					script.setAttribute("nonce", __webpack_require__.nc);
/******/ 				}
/******/ 		
/******/ 		
/******/ 				script.src = url;
/******/ 			}
/******/ 			inProgress[url] = [done];
/******/ 			var onScriptComplete = (prev, event) => {
/******/ 				// avoid mem leaks in IE.
/******/ 				script.onerror = script.onload = null;
/******/ 				clearTimeout(timeout);
/******/ 				var doneFns = inProgress[url];
/******/ 				delete inProgress[url];
/******/ 				script.parentNode && script.parentNode.removeChild(script);
/******/ 				doneFns && doneFns.forEach((fn) => (fn(event)));
/******/ 				if(prev) return prev(event);
/******/ 			}
/******/ 			var timeout = setTimeout(onScriptComplete.bind(null, undefined, { type: 'timeout', target: script }), 120000);
/******/ 			script.onerror = onScriptComplete.bind(null, script.onerror);
/******/ 			script.onload = onScriptComplete.bind(null, script.onload);
/******/ 			needAttach && document.head.appendChild(script);
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/publicPath */
/******/ 	(() => {
/******/ 		__webpack_require__.p = "/";
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/js/mobile": 0
/******/ 		};
/******/ 		
/******/ 		__webpack_require__.f.j = (chunkId, promises) => {
/******/ 				// JSONP chunk loading for javascript
/******/ 				var installedChunkData = __webpack_require__.o(installedChunks, chunkId) ? installedChunks[chunkId] : undefined;
/******/ 				if(installedChunkData !== 0) { // 0 means "already installed".
/******/ 		
/******/ 					// a Promise means "currently loading".
/******/ 					if(installedChunkData) {
/******/ 						promises.push(installedChunkData[2]);
/******/ 					} else {
/******/ 						if(true) { // all chunks have JS
/******/ 							// setup Promise in chunk cache
/******/ 							var promise = new Promise((resolve, reject) => (installedChunkData = installedChunks[chunkId] = [resolve, reject]));
/******/ 							promises.push(installedChunkData[2] = promise);
/******/ 		
/******/ 							// start chunk loading
/******/ 							var url = __webpack_require__.p + __webpack_require__.u(chunkId);
/******/ 							// create error before stack unwound to get useful stacktrace later
/******/ 							var error = new Error();
/******/ 							var loadingEnded = (event) => {
/******/ 								if(__webpack_require__.o(installedChunks, chunkId)) {
/******/ 									installedChunkData = installedChunks[chunkId];
/******/ 									if(installedChunkData !== 0) installedChunks[chunkId] = undefined;
/******/ 									if(installedChunkData) {
/******/ 										var errorType = event && (event.type === 'load' ? 'missing' : event.type);
/******/ 										var realSrc = event && event.target && event.target.src;
/******/ 										error.message = 'Loading chunk ' + chunkId + ' failed.\n(' + errorType + ': ' + realSrc + ')';
/******/ 										error.name = 'ChunkLoadError';
/******/ 										error.type = errorType;
/******/ 										error.request = realSrc;
/******/ 										installedChunkData[1](error);
/******/ 									}
/******/ 								}
/******/ 							};
/******/ 							__webpack_require__.l(url, loadingEnded, "chunk-" + chunkId, chunkId);
/******/ 						}
/******/ 					}
/******/ 				}
/******/ 		};
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		// no on chunks loaded
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 		
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunk"] = self["webpackChunk"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!********************************!*\
  !*** ./resources/js/mobile.js ***!
  \********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initMobile: () => (/* binding */ initMobile)
/* harmony export */ });
/* harmony import */ var _capacitor_core__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @capacitor/core */ "./node_modules/@capacitor/core/dist/index.js");
/* harmony import */ var _capacitor_push_notifications__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @capacitor/push-notifications */ "./node_modules/@capacitor/push-notifications/dist/esm/index.js");
/* harmony import */ var _capacitor_device__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @capacitor/device */ "./node_modules/@capacitor/device/dist/esm/index.js");
/* harmony import */ var _capacitor_status_bar__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @capacitor/status-bar */ "./node_modules/@capacitor/status-bar/dist/esm/index.js");
/* harmony import */ var _capacitor_geolocation__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @capacitor/geolocation */ "./node_modules/@capacitor/geolocation/dist/esm/index.js");
/* harmony import */ var aile_capacitor_line_login__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! aile-capacitor-line-login */ "./node_modules/aile-capacitor-line-login/dist/esm/index.js");
/* harmony import */ var _capacitor_app__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @capacitor/app */ "./node_modules/@capacitor/app/dist/esm/index.js");
/* harmony import */ var _capacitor_camera__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @capacitor/camera */ "./node_modules/@capacitor/camera/dist/esm/index.js");
/* harmony import */ var _capacitor_share__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @capacitor/share */ "./node_modules/@capacitor/share/dist/esm/index.js");
/* harmony import */ var _capacitor_haptics__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @capacitor/haptics */ "./node_modules/@capacitor/haptics/dist/esm/index.js");
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
/**
 * Capacitor Mobile Integration
 * Handles: Push Notifications, Device Info, Status Bar
 */











function initMobile() {
  return _initMobile.apply(this, arguments);
}
function _initMobile() {
  _initMobile = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee4() {
    var platform, info, permStatus, _t4;
    return _regenerator().w(function (_context4) {
      while (1) switch (_context4.p = _context4.n) {
        case 0:
          console.log('Initializing Mobile Features...');

          // Expose Geolocation to global window for Vue access
          window.MobileGeolocation = _capacitor_geolocation__WEBPACK_IMPORTED_MODULE_4__.Geolocation;
          window.MobilePush = _capacitor_push_notifications__WEBPACK_IMPORTED_MODULE_1__.PushNotifications;
          window.MobileDevice = _capacitor_device__WEBPACK_IMPORTED_MODULE_2__.Device;
          window.MobileLineLogin = aile_capacitor_line_login__WEBPACK_IMPORTED_MODULE_5__.LineLogin;
          window.MobileCamera = _capacitor_camera__WEBPACK_IMPORTED_MODULE_7__.Camera;
          window.MobileShare = _capacitor_share__WEBPACK_IMPORTED_MODULE_8__.Share;
          window.MobileHaptics = _capacitor_haptics__WEBPACK_IMPORTED_MODULE_9__.Haptics;

          // Helper for easier photography
          window.takeAppPhoto = /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
            var image, _t;
            return _regenerator().w(function (_context) {
              while (1) switch (_context.p = _context.n) {
                case 0:
                  _context.p = 0;
                  _context.n = 1;
                  return _capacitor_camera__WEBPACK_IMPORTED_MODULE_7__.Camera.getPhoto({
                    quality: 90,
                    allowEditing: true,
                    resultType: _capacitor_camera__WEBPACK_IMPORTED_MODULE_7__.CameraResultType.DataUrl,
                    source: _capacitor_camera__WEBPACK_IMPORTED_MODULE_7__.CameraSource.Prompt // Prompt allows choice between Gallery or Camera
                  });
                case 1:
                  image = _context.v;
                  return _context.a(2, image.dataUrl);
                case 2:
                  _context.p = 2;
                  _t = _context.v;
                  console.error('Camera Error:', _t);
                  return _context.a(2, null);
              }
            }, _callee, null, [[0, 2]]);
          }));

          // Helper for Native Sharing
          window.appShare = /*#__PURE__*/function () {
            var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(options) {
              var _t2;
              return _regenerator().w(function (_context2) {
                while (1) switch (_context2.p = _context2.n) {
                  case 0:
                    _context2.p = 0;
                    _context2.n = 1;
                    return _capacitor_share__WEBPACK_IMPORTED_MODULE_8__.Share.share({
                      title: options.title || 'LoveTennis',
                      text: options.text || '',
                      url: options.url || window.location.href,
                      dialogTitle: options.dialogTitle || '分享給球友'
                    });
                  case 1:
                    _context2.n = 3;
                    break;
                  case 2:
                    _context2.p = 2;
                    _t2 = _context2.v;
                    console.error('Share Error:', _t2);
                  case 3:
                    return _context2.a(2);
                }
              }, _callee2, null, [[0, 2]]);
            }));
            return function (_x2) {
              return _ref2.apply(this, arguments);
            };
          }();

          // Helper for Haptics (Vibration)
          window.appHaptic = /*#__PURE__*/_asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3() {
            var type,
              style,
              impactStyle,
              notifType,
              _args3 = arguments,
              _t3;
            return _regenerator().w(function (_context3) {
              while (1) switch (_context3.p = _context3.n) {
                case 0:
                  type = _args3.length > 0 && _args3[0] !== undefined ? _args3[0] : 'impact';
                  style = _args3.length > 1 && _args3[1] !== undefined ? _args3[1] : 'light';
                  _context3.p = 1;
                  if (!(type === 'impact')) {
                    _context3.n = 3;
                    break;
                  }
                  impactStyle = style === 'heavy' ? _capacitor_haptics__WEBPACK_IMPORTED_MODULE_9__.ImpactStyle.Heavy : style === 'medium' ? _capacitor_haptics__WEBPACK_IMPORTED_MODULE_9__.ImpactStyle.Medium : _capacitor_haptics__WEBPACK_IMPORTED_MODULE_9__.ImpactStyle.Light;
                  _context3.n = 2;
                  return _capacitor_haptics__WEBPACK_IMPORTED_MODULE_9__.Haptics.impact({
                    style: impactStyle
                  });
                case 2:
                  _context3.n = 4;
                  break;
                case 3:
                  if (!(type === 'notification')) {
                    _context3.n = 4;
                    break;
                  }
                  notifType = style === 'error' ? _capacitor_haptics__WEBPACK_IMPORTED_MODULE_9__.NotificationType.Error : style === 'warning' ? _capacitor_haptics__WEBPACK_IMPORTED_MODULE_9__.NotificationType.Warning : _capacitor_haptics__WEBPACK_IMPORTED_MODULE_9__.NotificationType.Success;
                  _context3.n = 4;
                  return _capacitor_haptics__WEBPACK_IMPORTED_MODULE_9__.Haptics.notification({
                    type: notifType
                  });
                case 4:
                  _context3.n = 6;
                  break;
                case 5:
                  _context3.p = 5;
                  _t3 = _context3.v;
                case 6:
                  return _context3.a(2);
              }
            }, _callee3, null, [[1, 5]]);
          }));
          _context4.p = 1;
          // Native-only Features - Explicitly exclude 'web' platform
          platform = _capacitor_core__WEBPACK_IMPORTED_MODULE_0__.Capacitor.getPlatform();
          console.log("Current Platform: ".concat(platform));
          if (!(platform !== 'web')) {
            _context4.n = 9;
            break;
          }
          _context4.n = 2;
          return _capacitor_app__WEBPACK_IMPORTED_MODULE_6__.App.getInfo();
        case 2:
          info = _context4.v;
          console.log("App Version: ".concat(info.version, " (").concat(info.build, ")"));
          window.AppInfo = info;
          // Set Status Bar Style
          _context4.n = 3;
          return _capacitor_status_bar__WEBPACK_IMPORTED_MODULE_3__.StatusBar.setStyle({
            style: _capacitor_status_bar__WEBPACK_IMPORTED_MODULE_3__.Style.Dark
          });
        case 3:
          _context4.n = 4;
          return _capacitor_status_bar__WEBPACK_IMPORTED_MODULE_3__.StatusBar.setBackgroundColor({
            color: '#0f172a'
          });
        case 4:
          _context4.n = 5;
          return _capacitor_push_notifications__WEBPACK_IMPORTED_MODULE_1__.PushNotifications.checkPermissions();
        case 5:
          permStatus = _context4.v;
          if (!(permStatus.receive === 'prompt')) {
            _context4.n = 7;
            break;
          }
          _context4.n = 6;
          return _capacitor_push_notifications__WEBPACK_IMPORTED_MODULE_1__.PushNotifications.requestPermissions();
        case 6:
          permStatus = _context4.v;
        case 7:
          if (!(permStatus.receive === 'granted')) {
            _context4.n = 8;
            break;
          }
          _context4.n = 8;
          return _capacitor_push_notifications__WEBPACK_IMPORTED_MODULE_1__.PushNotifications.register();
        case 8:
          // Listen for token registration
          _capacitor_push_notifications__WEBPACK_IMPORTED_MODULE_1__.PushNotifications.addListener('registration', function (token) {
            console.log('Push registration success, token: ' + token.value);
            saveTokenToServer(token.value);
          });
          _capacitor_push_notifications__WEBPACK_IMPORTED_MODULE_1__.PushNotifications.addListener('registrationError', function (error) {
            console.error('Error on registration: ' + JSON.stringify(error));
          });

          // Listen for notifications
          _capacitor_push_notifications__WEBPACK_IMPORTED_MODULE_1__.PushNotifications.addListener('pushNotificationReceived', function (notification) {
            console.log('Push received: ' + JSON.stringify(notification));
            // Show custom toast or update UI
            if (window.vm && window.vm.showToast) {
              window.vm.showToast(notification.body, 'info');
            }
          });
          _capacitor_push_notifications__WEBPACK_IMPORTED_MODULE_1__.PushNotifications.addListener('pushNotificationActionPerformed', function (notification) {
            console.log('Push action performed: ' + JSON.stringify(notification));
            var data = notification.notification.data;
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

          // App State Change (Refresh data when app returns from background)
          _capacitor_app__WEBPACK_IMPORTED_MODULE_6__.App.addListener('appStateChange', function (_ref4) {
            var isActive = _ref4.isActive;
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
          _context4.n = 10;
          break;
        case 9:
          console.log('Running on Web, skipping native status bar and notification setup.');
        case 10:
          _context4.n = 12;
          break;
        case 11:
          _context4.p = 11;
          _t4 = _context4.v;
          console.warn('Capacitor features not available or error occurred:', _t4);
        case 12:
          return _context4.a(2);
      }
    }, _callee4, null, [[1, 11]]);
  }));
  return _initMobile.apply(this, arguments);
}
function saveTokenToServer(_x) {
  return _saveTokenToServer.apply(this, arguments);
} // Start initialization if running in Capacitor environment
// Robust check: window.Capacitor exists AND we are not on web platform
function _saveTokenToServer() {
  _saveTokenToServer = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee5(token) {
    var device, battery;
    return _regenerator().w(function (_context5) {
      while (1) switch (_context5.n) {
        case 0:
          _context5.n = 1;
          return _capacitor_device__WEBPACK_IMPORTED_MODULE_2__.Device.getInfo();
        case 1:
          device = _context5.v;
          _context5.n = 2;
          return _capacitor_device__WEBPACK_IMPORTED_MODULE_2__.Device.getBatteryInfo();
        case 2:
          battery = _context5.v;
          axios.post('/api/mobile/register-token', {
            token: token,
            platform: device.platform,
            model: device.model,
            os_version: device.osVersion,
            is_virtual: device.isVirtual
          }).then(function (response) {
            console.log('Token saved to server');
          })["catch"](function (error) {
            console.error('Failed to save token:', error);
          });
        case 3:
          return _context5.a(2);
      }
    }, _callee5);
  }));
  return _saveTokenToServer.apply(this, arguments);
}
if (window.Capacitor && _capacitor_core__WEBPACK_IMPORTED_MODULE_0__.Capacitor.getPlatform() !== 'web') {
  initMobile();
}

})();

/******/ })()
;