/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./mini/ui/MainView.ts");
/******/ })
/************************************************************************/
/******/ ({

/***/ "../../libs/ff-browser/source/ManipTarget.ts":
/*!**************************************************!*\
  !*** /app/libs/ff-browser/source/ManipTarget.ts ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
class ManipTarget {
    constructor() {
        this.onPointerDown = this.onPointerDown.bind(this);
        this.onPointerMove = this.onPointerMove.bind(this);
        this.onPointerUpOrCancel = this.onPointerUpOrCancel.bind(this);
        this.onDoubleClick = this.onDoubleClick.bind(this);
        this.onContextMenu = this.onContextMenu.bind(this);
        this.onWheel = this.onWheel.bind(this);
        this.next = null;
        this.activePositions = [];
        this.activeType = "";
        this.centerX = 0;
        this.centerY = 0;
    }
    onPointerDown(event) {
        // only events of a single pointer type can be handled at a time
        if (this.activeType && event.pointerType !== this.activeType) {
            return;
        }
        this.activeType = event.pointerType;
        this.activePositions.push({
            id: event.pointerId,
            clientX: event.clientX,
            clientY: event.clientY
        });
        event.currentTarget.setPointerCapture(event.pointerId);
        const manipEvent = this.createManipPointerEvent(event, "pointer-down");
        if (this.sendPointerEvent(manipEvent)) {
            event.stopPropagation();
        }
        event.preventDefault();
    }
    onPointerMove(event) {
        const activePositions = this.activePositions;
        for (let i = 0, n = activePositions.length; i < n; ++i) {
            const position = activePositions[i];
            if (event.pointerId === position.id) {
                position.clientX = event.clientX;
                position.clientY = event.clientY;
            }
        }
        const eventType = activePositions.length ? "pointer-move" : "pointer-hover";
        const manipEvent = this.createManipPointerEvent(event, eventType);
        if (this.sendPointerEvent(manipEvent)) {
            event.stopPropagation();
        }
        event.preventDefault();
    }
    onPointerUpOrCancel(event) {
        const activePositions = this.activePositions;
        let found = false;
        for (let i = 0, n = activePositions.length; i < n; ++i) {
            if (event.pointerId === activePositions[i].id) {
                activePositions.splice(i, 1);
                found = true;
                break;
            }
        }
        if (!found) {
            //console.warn("orphan pointer up/cancel event #id", event.pointerId);
            return;
        }
        const manipEvent = this.createManipPointerEvent(event, "pointer-up");
        if (activePositions.length === 0) {
            this.activeType = "";
        }
        if (this.sendPointerEvent(manipEvent)) {
            event.stopPropagation();
        }
        event.preventDefault();
    }
    onDoubleClick(event) {
        const consumed = this.sendTriggerEvent(this.createManipTriggerEvent(event, "double-click"));
        if (consumed) {
            event.preventDefault();
        }
    }
    onContextMenu(event) {
        this.sendTriggerEvent(this.createManipTriggerEvent(event, "context-menu"));
        // prevent default context menu regardless of whether event was consumed or not
        event.preventDefault();
    }
    onWheel(event) {
        const consumed = this.sendTriggerEvent(this.createManipTriggerEvent(event, "wheel"));
        if (consumed) {
            event.preventDefault();
        }
    }
    createManipPointerEvent(event, type) {
        // calculate center and movement
        let centerX = 0;
        let centerY = 0;
        let localX = 0;
        let localY = 0;
        let movementX = 0;
        let movementY = 0;
        const positions = this.activePositions;
        const count = positions.length;
        if (count > 0) {
            for (let i = 0; i < count; ++i) {
                centerX += positions[i].clientX;
                centerY += positions[i].clientY;
            }
            centerX /= count;
            centerY /= count;
            if (type === "pointer-move" || type === "pointer-hover") {
                movementX = centerX - this.centerX;
                movementY = centerY - this.centerY;
            }
            this.centerX = centerX;
            this.centerY = centerY;
        }
        else {
            centerX = this.centerX;
            centerY = this.centerY;
        }
        const element = event.currentTarget;
        if (element instanceof Element) {
            const rect = element.getBoundingClientRect();
            localX = event.clientX - rect.left;
            localY = event.clientY - rect.top;
        }
        return {
            originalEvent: event,
            type: type,
            source: event.pointerType,
            isPrimary: event.isPrimary,
            activePositions: positions,
            pointerCount: count,
            centerX,
            centerY,
            localX,
            localY,
            movementX,
            movementY,
            shiftKey: event.shiftKey,
            ctrlKey: event.ctrlKey,
            altKey: event.altKey,
            metaKey: event.metaKey
        };
    }
    createManipTriggerEvent(event, type) {
        let wheel = 0;
        if (type === "wheel") {
            wheel = event.deltaY;
        }
        let localX = 0;
        let localY = 0;
        const element = event.currentTarget;
        if (element instanceof Element) {
            const rect = element.getBoundingClientRect();
            localX = event.clientX - rect.left;
            localY = event.clientY - rect.top;
        }
        return {
            originalEvent: event,
            type,
            wheel,
            centerX: event.clientX,
            centerY: event.clientY,
            localX,
            localY,
            shiftKey: event.shiftKey,
            ctrlKey: event.ctrlKey,
            altKey: event.altKey,
            metaKey: event.metaKey
        };
    }
    sendPointerEvent(event) {
        return this.next && this.next.onPointer(event);
    }
    sendTriggerEvent(event) {
        return this.next && this.next.onTrigger(event);
    }
}
exports.default = ManipTarget;


/***/ }),

/***/ "../../libs/ff-browser/source/parseUrlParameter.ts":
/*!********************************************************!*\
  !*** /app/libs/ff-browser/source/parseUrlParameter.ts ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
/**
 * Returns the value of the variable in the URL query string with the given name.
 * Source: https://stackoverflow.com/questions/901115
 * @param {string} name Name of the variable to look for.
 * @param {string} url URL to search. If omitted, the browser's current location is used.
 * @returns {any} undefined if not found, "" if empty, string value of variable otherwise
 */
function default_1(name, url) {
    if (!url) {
        url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, '\\$&');
    const regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
    const results = regex.exec(url);
    if (!results) {
        return undefined;
    }
    if (!results[2]) {
        return "";
    }
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}
exports.default = default_1;


/***/ }),

/***/ "../../libs/ff-core/source/Command.ts":
/*!*******************************************!*\
  !*** /app/libs/ff-core/source/Command.ts ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const text_1 = __webpack_require__(/*! ./text */ "../../libs/ff-core/source/text.ts");
class Command {
    constructor(args, props) {
        this._args = args;
        this._props = props;
        this._args = args;
        this._state = null;
    }
    get name() {
        return this._props.name || text_1.normalize(this._props.do.name);
    }
    do() {
        if (this._state) {
            throw new Error("undo should be called before execute can be applied again");
        }
        this._state = this._props.do.apply(this._props.target, this._args);
    }
    undo() {
        if (!this._props.undo) {
            throw new Error("can't undo this command");
        }
        if (!this._state) {
            throw new Error("execute should be called before undo can be applied");
        }
        this._props.undo.call(this._props.target, this._state);
        this._state = null;
    }
    canDo() {
        return this._props.canDo ? this._props.canDo() : true;
    }
    canUndo() {
        return !!this._props.undo;
    }
}
exports.default = Command;


/***/ }),

/***/ "../../libs/ff-core/source/Commander.ts":
/*!*********************************************!*\
  !*** /app/libs/ff-core/source/Commander.ts ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Publisher_1 = __webpack_require__(/*! ./Publisher */ "../../libs/ff-core/source/Publisher.ts");
const Command_1 = __webpack_require__(/*! ./Command */ "../../libs/ff-core/source/Command.ts");
class Commander extends Publisher_1.default {
    constructor(capacity) {
        super();
        this.addEvent("change");
        this.stack = [];
        this.pointer = -1;
        this.capacity = capacity !== undefined ? capacity : Commander.defaultCapacity;
    }
    register(propsOrFactory) {
        let factory;
        if (typeof propsOrFactory === "function") {
            factory = propsOrFactory;
        }
        else {
            factory = (args) => new Command_1.default(args, propsOrFactory);
        }
        const action = (...args) => {
            const command = factory(args);
            this.do(command);
        };
        return action;
    }
    setCapacity(capacity) {
        this.capacity = capacity;
        while (this.stack.length > capacity) {
            this.stack.shift();
            this.pointer--;
        }
        if (this.pointer < 0) {
            this.stack = [];
            this.pointer = -1;
        }
    }
    do(command) {
        console.log(`Commander.do - '${command.name}'`);
        command.do();
        if (command.canUndo()) {
            this.stack.splice(this.pointer + 1);
            this.stack.push(command);
            if (this.stack.length > this.capacity) {
                this.stack.shift();
            }
            this.pointer = this.stack.length - 1;
            this.emit("change");
        }
    }
    undo() {
        if (this.pointer >= 0) {
            const command = this.stack[this.pointer];
            command.undo();
            this.pointer--;
            this.emit("change");
        }
    }
    redo() {
        if (this.pointer < this.stack.length - 1) {
            this.pointer++;
            const command = this.stack[this.pointer];
            command.do();
            this.emit("change");
        }
    }
    clear() {
        if (this.stack.length > 0) {
            this.stack = [];
            this.pointer = -1;
            this.emit("change");
        }
    }
    canUndo() {
        return this.pointer >= 0;
    }
    canRedo() {
        return this.pointer < this.stack.length - 1;
    }
    getUndoText() {
        if (this.pointer >= 0) {
            return "Undo " + this.stack[this.pointer].name;
        }
        return "Can't Undo";
    }
    getRedoText() {
        if (this.pointer < this.stack.length - 1) {
            return "Redo " + this.stack[this.pointer + 1].name;
        }
        return "Can't Redo";
    }
}
Commander.defaultCapacity = 30;
exports.default = Commander;


/***/ }),

/***/ "../../libs/ff-core/source/Publisher.ts":
/*!*********************************************!*\
  !*** /app/libs/ff-core/source/Publisher.ts ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const _pd = Symbol("Publisher private data");
const _strict = Symbol("Publisher strict option");
/**
 * Provides subscription services for events.
 */
class Publisher {
    constructor(options) {
        const knownEvents = options ? options.knownEvents : true;
        this[_pd] = { [_strict]: knownEvents };
    }
    on(type, callback, context) {
        if (Array.isArray(type)) {
            type.forEach(type => {
                this.on(type, callback, context);
            });
            return;
        }
        if (!callback) {
            throw new Error("missing callback function");
        }
        let subscribers = this[_pd][type];
        if (!subscribers) {
            if (this[_pd][_strict]) {
                throw new Error(`can't subscribe; unknown event: '${type}'`);
            }
            subscribers = this[_pd][type] = [];
        }
        let subscriber = { callback, context };
        subscribers.push(subscriber);
    }
    /**
     * Subscribes to an event. You may find using the .on() method more handy and more flexible.
     * @param type
     * @param callback
     * @param context
     */
    addEventListener(type, callback, context) {
        this.on(type, callback, context);
    }
    once(type, callback, context) {
        if (Array.isArray(type)) {
            type.forEach(type => {
                this.once(type, callback, context);
            });
            return;
        }
        const redirect = event => {
            this.off(type, redirect, context);
            callback.call(context, event);
        };
        redirect.cb = callback;
        this.on(type, redirect, context);
    }
    off(type, callback, context) {
        if (typeof type === "object") {
            if (Array.isArray(type)) {
                // if first parameter is an array, call function for all elements of the array
                type.forEach((type) => {
                    this.off(type, callback, context);
                });
            }
            else {
                // if first parameter is an object, unsubscribe all subscriptions where the context matches the object.
                const events = this[_pd];
                const types = Object.keys(events);
                for (let i = 0, ni = types.length; i < ni; ++i) {
                    const subscribers = events[type];
                    const remainingSubscribers = [];
                    for (let j = 0, nj = subscribers.length; j < nj; ++j) {
                        const subscriber = subscribers[j];
                        if (type && subscriber.context !== type) {
                            remainingSubscribers.push(subscriber);
                        }
                    }
                    events[type] = remainingSubscribers;
                }
            }
            return;
        }
        const subscribers = this[_pd][type];
        if (!subscribers) {
            throw new Error(`can't unsubscribe; unknown event type: '${type}'`);
        }
        const remainingSubscribers = [];
        for (let i = 0, n = subscribers.length; i < n; ++i) {
            const subscriber = subscribers[i];
            if ((callback && callback !== subscriber.callback && callback !== subscriber.callback.cb)
                || (context && context !== subscriber.context)) {
                remainingSubscribers.push(subscriber);
            }
        }
        this[_pd][type] = remainingSubscribers;
    }
    /**
     * Unsubscribes from an event. You may find using the .off() method more handy and more flexible.
     * @param type Type name of the event.
     * @param callback Callback function, invoked when the event is emitted.
     * @param context Optional: this context for the callback invocation.
     */
    removeEventListener(type, callback, context) {
        this.off(type, callback, context);
    }
    emit(eventOrType, message) {
        let type, payload;
        if (typeof eventOrType === "string") {
            type = eventOrType;
            payload = message;
        }
        else {
            type = eventOrType.type;
            payload = eventOrType;
        }
        if (!type) {
            throw new Error(`empty or invalid event type: '${type}'`);
        }
        const data = this[_pd];
        const subscribers = data[type];
        if (!subscribers) {
            if (data[_strict]) {
                throw new Error(`can't emit; unknown event type: '${type}'`);
            }
            return;
        }
        for (let i = 0, n = subscribers.length; i < n; ++i) {
            const subscriber = subscribers[i];
            if (subscriber.context) {
                subscriber.callback.call(subscriber.context, payload);
            }
            else {
                subscriber.callback(payload);
            }
        }
    }
    /**
     * Registers a new event type.
     * @param name Name of the event type.
     */
    addEvent(name) {
        if (!this[_pd][name]) {
            this[_pd][name] = [];
        }
    }
    /**
     * Registers multiple new event types.
     * @param names Names of the event types.
     */
    addEvents(...names) {
        names.forEach(name => {
            if (!this[_pd][name]) {
                this[_pd][name] = [];
            }
        });
    }
    /**
     * Tests whether an event type has been registered.
     * @param name Name of the event type.
     * @returns true if an event type with the given name has been added.
     */
    hasEvent(name) {
        return !!this[_pd][name];
    }
    /**
     * Lists all registered event types.
     * @returns an array with the names of all added event types.
     */
    listEvents() {
        return Object.getOwnPropertyNames(this[_pd]);
    }
}
exports.default = Publisher;


/***/ }),

/***/ "../../libs/ff-core/source/easing.ts":
/*!******************************************!*\
  !*** /app/libs/ff-core/source/easing.ts ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const PI = Math.PI;
const HALF_PI = PI * 0.5;
var EEasingCurve;
(function (EEasingCurve) {
    EEasingCurve[EEasingCurve["Linear"] = 0] = "Linear";
    EEasingCurve[EEasingCurve["EaseQuad"] = 1] = "EaseQuad";
    EEasingCurve[EEasingCurve["EaseInQuad"] = 2] = "EaseInQuad";
    EEasingCurve[EEasingCurve["EaseOutQuad"] = 3] = "EaseOutQuad";
    EEasingCurve[EEasingCurve["EaseCubic"] = 4] = "EaseCubic";
    EEasingCurve[EEasingCurve["EaseInCubic"] = 5] = "EaseInCubic";
    EEasingCurve[EEasingCurve["EaseOutCubic"] = 6] = "EaseOutCubic";
    EEasingCurve[EEasingCurve["EaseQuart"] = 7] = "EaseQuart";
    EEasingCurve[EEasingCurve["EaseInQuart"] = 8] = "EaseInQuart";
    EEasingCurve[EEasingCurve["EaseOutQuart"] = 9] = "EaseOutQuart";
    EEasingCurve[EEasingCurve["EaseQuint"] = 10] = "EaseQuint";
    EEasingCurve[EEasingCurve["EaseInQuint"] = 11] = "EaseInQuint";
    EEasingCurve[EEasingCurve["EaseOutQuint"] = 12] = "EaseOutQuint";
    EEasingCurve[EEasingCurve["EaseSine"] = 13] = "EaseSine";
    EEasingCurve[EEasingCurve["EaseInSine"] = 14] = "EaseInSine";
    EEasingCurve[EEasingCurve["EaseOutSine"] = 15] = "EaseOutSine";
})(EEasingCurve = exports.EEasingCurve || (exports.EEasingCurve = {}));
function getEasingFunction(curve) {
    return exports.easingFunctions[EEasingCurve[curve]];
}
exports.getEasingFunction = getEasingFunction;
exports.easingFunctions = {
    Linear: function (t) { return t; },
    EaseQuad: function (t) { return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t; },
    EaseInQuad: function (t) { return t * t; },
    EaseOutQuad: function (t) { return t * (2 - t); },
    EaseCubic: function (t) { return t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1; },
    EaseInCubic: function (t) { return t * t * t; },
    EaseOutCubic: function (t) { return (--t) * t * t + 1; },
    EaseQuart: function (t) { return t < 0.5 ? 8 * t * t * t * t : 1 - 8 * (--t) * t * t * t; },
    EaseInQuart: function (t) { return t * t * t * t; },
    EaseOutQuart: function (t) { return 1 - (--t) * t * t * t; },
    EaseQuint: function (t) { return t < 0.5 ? 16 * t * t * t * t * t : 1 + 16 * (--t) * t * t * t * t; },
    EaseInQuint: function (t) { return t * t * t * t * t; },
    EaseOutQuint: function (t) { return 1 + (--t) * t * t * t * t; },
    EaseSine: function (t) { return -0.5 * (Math.cos(t * PI) - 1); },
    EaseInSine: function (t) { return 1 - Math.cos(t * HALF_PI); },
    EaseOutSine: function (t) { return Math.sin(t * HALF_PI); },
};


/***/ }),

/***/ "../../libs/ff-core/source/isSubclass.ts":
/*!**********************************************!*\
  !*** /app/libs/ff-core/source/isSubclass.ts ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
function isSubclass(derived, base) {
    if (!derived || !base) {
        return false;
    }
    let prototype = derived.prototype;
    while (prototype) {
        if (prototype === base.prototype) {
            return true;
        }
        prototype = prototype.prototype;
    }
    return false;
}
exports.default = isSubclass;


/***/ }),

/***/ "../../libs/ff-core/source/math.ts":
/*!****************************************!*\
  !*** /app/libs/ff-core/source/math.ts ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const math = {
    PI: 3.1415926535897932384626433832795,
    DOUBLE_PI: 6.283185307179586476925286766559,
    HALF_PI: 1.5707963267948966192313216916398,
    QUARTER_PI: 0.78539816339744830961566084581988,
    DEG2RAD: 0.01745329251994329576923690768489,
    RAD2DEG: 57.295779513082320876798154814105,
    limit: (v, min, max) => v < min ? min : (v > max ? max : v),
    limitInt: function (v, min, max) {
        v = Math.trunc(v);
        return v < min ? min : (v > max ? max : v);
    },
    normalize: (v, min, max) => (v - min) / (max - min),
    normalizeLimit: (v, min, max) => {
        v = (v - min) / (max - min);
        return v < 0.0 ? 0.0 : (v > 1.0 ? 1.0 : v);
    },
    denormalize: (t, min, max) => (min + t) * (max - min),
    scale: (v, minIn, maxIn, minOut, maxOut) => minOut + (v - minIn) / (maxIn - minIn) * (maxOut - minOut),
    scaleLimit: (v, minIn, maxIn, minOut, maxOut) => {
        v = v < minIn ? minIn : (v > maxIn ? maxIn : v);
        return minOut + (v - minIn) / (maxIn - minIn) * (maxOut - minOut);
    },
    deg2rad: function (degrees) {
        return degrees * 0.01745329251994329576923690768489;
    },
    rad2deg: function (radians) {
        return radians * 57.295779513082320876798154814105;
    },
    deltaRadians: function (radA, radB) {
        radA %= math.DOUBLE_PI;
        radA = radA < 0 ? radA + math.DOUBLE_PI : radA;
        radB %= math.DOUBLE_PI;
        radB = radB < 0 ? radB + math.DOUBLE_PI : radB;
        if (radB - radA > math.PI) {
            radA += math.DOUBLE_PI;
        }
        return radB - radA;
    },
    deltaDegrees: function (degA, degB) {
        degA %= math.DOUBLE_PI;
        degA = degA < 0 ? degA + math.DOUBLE_PI : degA;
        degB %= math.DOUBLE_PI;
        degB = degB < 0 ? degB + math.DOUBLE_PI : degB;
        if (degB - degA > math.PI) {
            degA += math.DOUBLE_PI;
        }
        return degB - degA;
    },
    curves: {
        linear: t => t,
        easeIn: t => Math.sin(t * math.HALF_PI),
        easeOut: t => Math.cos(t * math.HALF_PI - math.PI) + 1.0,
        ease: t => Math.cos(t * math.PI - math.PI) * 0.5 + 0.5,
        easeInQuad: t => t * t,
        easeOutQuad: t => t * (2 - t),
        easeQuad: t => t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t,
        easeInCubic: t => t * t * t,
        easeOutCubic: t => (--t) * t * t + 1,
        easeCubic: t => t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1,
        easeInQuart: t => t * t * t * t,
        easeOutQuart: t => 1 - (--t) * t * t * t,
        easeQuart: t => t < 0.5 ? 8 * t * t * t * t : 1 - 8 * (--t) * t * t * t
    }
};
exports.default = math;


/***/ }),

/***/ "../../libs/ff-core/source/text.ts":
/*!****************************************!*\
  !*** /app/libs/ff-core/source/text.ts ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
function camelize(text) {
    return text.replace(/(?:^\w|[A-Z]|\b\w)/g, (letter, index) => index == 0 ? letter.toLowerCase() : letter.toUpperCase()).replace(/\s+/g, '');
}
exports.camelize = camelize;
function normalize(text) {
    return text.replace(/([A-Z])/g, ' $1')
        .replace(/^./, str => str.toUpperCase());
}
exports.normalize = normalize;


/***/ }),

/***/ "../../libs/ff-core/source/types.ts":
/*!*****************************************!*\
  !*** /app/libs/ff-core/source/types.ts ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
////////////////////////////////////////////////////////////////////////////////
// ENUM HELPER FUNCTIONS
exports.enumToArray = function (e) {
    return Object.keys(e).filter(key => isNaN(Number(key)));
};


/***/ }),

/***/ "../../libs/ff-core/source/uniqueId.ts":
/*!********************************************!*\
  !*** /app/libs/ff-core/source/uniqueId.ts ***!
  \********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
let _chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
/**
 * Creates a base64 encoded globally unique identifier with a default length of 12 characters.
 * The identifier only uses letters and digits and can safely be used for file names.
 * Unique combinations: 62 ^ 12 > 2 ^ 64
 * @param length Number of base64 characters in the identifier.
 * @param dictionary Optional object with ids. Function ensures generated id is not equal to a key of dictionary.
 * @returns Globally unique identifier
 */
function uniqueId(length, dictionary) {
    if (!length || typeof length !== "number") {
        length = 12;
    }
    let id;
    do {
        id = "";
        for (let i = 0; i < length; ++i) {
            id += _chars[Math.random() * 62 | 0];
        }
    } while (dictionary && dictionary[id]);
    return id;
}
exports.default = uniqueId;


/***/ }),

/***/ "../../libs/ff-graph/source/Component.ts":
/*!**********************************************!*\
  !*** /app/libs/ff-graph/source/Component.ts ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Publisher_1 = __webpack_require__(/*! @ff/core/Publisher */ "../../libs/ff-core/source/Publisher.ts");
const Property_1 = __webpack_require__(/*! ./Property */ "../../libs/ff-graph/source/Property.ts");
exports.types = Property_1.types;
const PropertyGroup_1 = __webpack_require__(/*! ./PropertyGroup */ "../../libs/ff-graph/source/PropertyGroup.ts");
const ComponentTracker_1 = __webpack_require__(/*! ./ComponentTracker */ "../../libs/ff-graph/source/ComponentTracker.ts");
const ComponentReference_1 = __webpack_require__(/*! ./ComponentReference */ "../../libs/ff-graph/source/ComponentReference.ts");
/** Returns the type string of the given [[ComponentOrType]]. */
function componentTypeName(componentOrType) {
    return componentOrType ? (typeof componentOrType === "string" ? componentOrType : componentOrType.type) : Component.type;
}
exports.componentTypeName = componentTypeName;
////////////////////////////////////////////////////////////////////////////////
/**
 * Base class for components in a node-component system.
 *
 * ### Events
 * - *"change"* - emits [[IComponentChangeEvent]] after the component's state has changed.
 * - *"dispose"* - emits [[IComponentDisposeEvent]] if the component is about to be disposed.
 *
 * ### See also
 * - [[ComponentTracker]]
 * - [[ComponentLink]]
 * - [[ComponentType]]
 * - [[ComponentOrType]]
 * - [[Node]]
 * - [[Graph]]
 * - [[System]]
 */
class Component extends Publisher_1.default {
    /**
     * Protected constructor. Use [[Node.createComponent]] to create component instances.
     * @param id Unique id for the component. A unique id is usually created automatically,
     * do not specify except while de-serializing the component.
     *
     * Note that during execution of the constructor, the component is not yet attached
     * to a node/graph/system. Do not try to get access to other components,
     * the parent node, graph, or the system here.
     */
    constructor(id) {
        super({ knownEvents: false });
        this.ins = new PropertyGroup_1.default(this);
        this.outs = new PropertyGroup_1.default(this);
        this.changed = true;
        this.updated = false;
        this._node = null;
        this._name = "";
        this._trackers = [];
        this._firstAttached = false;
        this.id = id;
    }
    /**
     * Returns the type identifier of this component.
     * @returns {string}
     */
    get type() {
        return this.constructor.type;
    }
    get text() {
        return this.constructor.text;
    }
    get icon() {
        return this.constructor.icon;
    }
    /**
     * Returns the system this component and its node belong to.
     */
    get system() {
        return this._node.system;
    }
    /**
     * Returns the graph this component and its node belong to.
     */
    get graph() {
        return this._node.graph;
    }
    /**
     * Returns the node this component belongs to.
     */
    get node() {
        return this._node;
    }
    /**
     * Returns the set of sibling components of this component.
     * Sibling components are components belonging to the same node.
     */
    get components() {
        return this._node.components;
    }
    /**
     * Returns the sibling hierarchy component if available.
     */
    get hierarchy() {
        return this._node.components.get("CHierarchy");
    }
    /**
     * True if the component is a node singleton, i.e. can only be added once per node.
     */
    get isNodeSingleton() {
        return this.constructor.isNodeSingleton;
    }
    /**
     * True if the component is a graph singleton, i.e. can only be added once per graph.
     */
    get isGraphSingleton() {
        return this.constructor.isGraphSingleton;
    }
    /**
     * True if the component is a system singleton, i.e. can only be added once per system.
     */
    get isSystemSingleton() {
        return this.constructor.isSystemSingleton;
    }
    /**
     * Returns the name of this component.
     * @returns {string}
     */
    get name() {
        return this._name;
    }
    get displayName() {
        return this._name || this.text || this.type;
    }
    /**
     * Sets the name of this component.
     * This emits an [[IComponentChangeEvent]].
     * @param {string} value
     */
    set name(value) {
        this._name = value;
        this.emit({ type: "change", component: this, what: "name" });
    }
    /**
     * Adds the component to the given node.
     * @param node Node to attach the new component to.
     */
    attach(node) {
        if (this._node) {
            this.detach();
        }
        this._node = node;
        if (!this._firstAttached) {
            this._firstAttached = true;
            this.create();
        }
        // note: adding the component informs subscribers, this must happen after create()
        node._addComponent(this);
    }
    /**
     * Called after the component has been constructed and attached to a node.
     * Override to perform initialization tasks where you need access to other components.
     */
    create() {
    }
    /**
     * Called during each cycle if the component's input properties have changed.
     * Override to update the status of the component based on the input properties.
     * @param context Information about the current update cycle.
     * @returns True if the state of the component has been changed during the update.
     */
    update(context) {
        throw new Error("this should never be called");
    }
    /**
     * Called during each cycle, after the component has been updated.
     * Override to let the component perform regular tasks.
     * @param context Information about the current update cycle.
     */
    tick(context) {
        throw new Error("this should never be called");
    }
    /**
     * Called after rendering is completed.
     * Override to perform update operations which need to happen
     * only after all rendering is done.
     * @param context Information about the current update cycle.
     */
    finalize(context) {
        throw new Error("this should never be called");
    }
    /**
     * Removes the component from its node.
     */
    detach() {
        if (this._node) {
            this._node._removeComponent(this);
            this._node = null;
        }
    }
    /**
     * Removes the component from its node and deletes it.
     * Override to perform cleanup tasks (remove event listeners, etc.).
     * Must call super implementation if overridden!
     */
    dispose() {
        // remove all links and trackers
        this.ins.dispose();
        this.outs.dispose();
        this._trackers.forEach(tracker => tracker.dispose());
        // remove component from node
        this.detach();
        // emit dispose event
        this.emit({ type: "dispose", component: this });
    }
    requestSort() {
        this.graph.requestSort();
    }
    /**
     * Returns true if this component has or inherits from the given type.
     * @param componentOrType
     */
    is(componentOrType) {
        const type = componentTypeName(componentOrType);
        let prototype = this;
        do {
            prototype = Object.getPrototypeOf(prototype);
            if (prototype.type === type) {
                return true;
            }
        } while (prototype.type !== Component.type);
        return false;
    }
    /**
     * Removes links from all input and output properties.
     */
    unlinkAllProperties() {
        this.ins.unlinkAllProperties();
        this.outs.unlinkAllProperties();
    }
    /**
     * Sets the changed flags of this component and of all input properties to false;
     */
    resetChanged() {
        this.changed = false;
        const ins = this.ins.properties;
        for (let i = 0, n = ins.length; i < n; ++i) {
            ins[i].changed = false;
        }
    }
    /**
     * Tracks the given component type. If a component of this type is added
     * to or removed from the node, it will be added or removed from the tracker.
     * @param {ComponentOrType} componentOrType
     * @param {(component: T) => void} didAdd
     * @param {(component: T) => void} willRemove
     */
    trackComponent(componentOrType, didAdd, willRemove) {
        const tracker = new ComponentTracker_1.default(this._node.components, componentOrType, didAdd, willRemove);
        this._trackers.push(tracker);
        return tracker;
    }
    /**
     * Returns a weak reference to a component.
     * The reference is set to null after the linked component is removed.
     * @param componentOrType The type of component this reference accepts, or the component to link.
     */
    referenceComponent(componentOrType) {
        return new ComponentReference_1.default(this.system, componentOrType);
    }
    /**
     * Propagates and emits an event as follows, until event.stopPropagation is set to true.
     * 1. this component
     * 2. sibling components of this
     * 3. parent hierarchy component if available
     * 4. siblings of parent hierarchy component
     * 5. repeat 3/4 until at root
     * 6. emits event on system
     * @param event
     */
    propagateUp(event) {
        let target = this;
        while (target) {
            target.emit(event);
            if (event.stopPropagation) {
                return;
            }
            const components = target.components.getArray();
            for (let i = 0, n = components.length; i < n; ++i) {
                const component = components[i];
                if (component !== target) {
                    component.emit(event);
                    if (event.stopPropagation) {
                        return;
                    }
                }
            }
            const hierarchy = target.components.get("CHierarchy");
            target = hierarchy ? hierarchy.parent : null;
            // TODO: Should event propagate to parent graph?
            // if (!target) {
            //     target = hierarchy.graph.parent;
            // }
        }
        if (!event.stopPropagation) {
            this.system.emit(event);
        }
    }
    /**
     * Propagates and emits an event as follows, until event.stopPropagation is set to true.
     * 1. all children of the sibling hierarchy of this
     * 2. for each child, repeat 1 until reaching leaf components with no children
     * @param event
     */
    propagateDown(event) {
        const hierarchy = this.components.get("CHierarchy");
        const children = hierarchy ? hierarchy.children : null;
        for (let i = 0, n = children.length; i < n; ++i) {
            const components = children[i].components.getArray();
            for (let j = 0, m = components.length; j < m; ++i) {
                components[j].emit(event);
                if (event.stopPropagation) {
                    return;
                }
            }
            for (let j = 0, m = components.length; j < m; ++i) {
                components[j].propagateDown(event);
                if (event.stopPropagation) {
                    return;
                }
            }
        }
    }
    /**
     * Returns a text representation of the component.
     * @returns {string}
     */
    toString() {
        return `${this.type}${this.name ? " (" + this.name + ")" : ""}`;
    }
    deflate() {
        let json = {};
        const jsonIns = this.ins.deflate();
        if (jsonIns) {
            json.ins = jsonIns;
        }
        const jsonOuts = this.outs.deflate();
        if (jsonOuts) {
            json.outs = jsonOuts;
        }
        return json;
    }
    inflate(json) {
        if (json.ins) {
            this.ins.inflate(json.ins);
        }
        if (json.outs) {
            this.outs.inflate(json.outs);
        }
    }
    inflateReferences(json) {
        const dict = this.system.components.getDictionary();
        if (json.ins) {
            this.ins.inflateLinks(json.ins, dict);
        }
        if (json.outs) {
            this.outs.inflateLinks(json.outs, dict);
        }
    }
    /**
     * Adds input properties to the component, specified by the provided property templates.
     * @param templates A plain object with property templates.
     * @param index Optional index at which to insert the new properties.
     */
    addInputs(templates, index) {
        return this.ins.createPropertiesFromTemplates(templates, index);
    }
    /**
     * Adds output properties to the component, specified by the provided property templates.
     * @param templates A plain object with property templates.
     * @param index Optional index at which to insert the new properties.
     */
    addOutputs(templates, index) {
        return this.outs.createPropertiesFromTemplates(templates, index);
    }
}
Component.type = "Component";
Component.text = "";
Component.icon = "";
Component.isNodeSingleton = true;
Component.isGraphSingleton = false;
Component.isSystemSingleton = false;
exports.default = Component;
Component.prototype.update = null;
Component.prototype.tick = null;
Component.prototype.finalize = null;


/***/ }),

/***/ "../../libs/ff-graph/source/ComponentReference.ts":
/*!*******************************************************!*\
  !*** /app/libs/ff-graph/source/ComponentReference.ts ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Component_1 = __webpack_require__(/*! ./Component */ "../../libs/ff-graph/source/Component.ts");
////////////////////////////////////////////////////////////////////////////////
/**
 * Maintains a weak reference to a component.
 * The reference is set to null after the linked component is removed.
 */
class ComponentReference {
    constructor(system, componentOrType) {
        this._type = componentOrType ? Component_1.componentTypeName(componentOrType) : null;
        this._id = componentOrType instanceof Component_1.default ? componentOrType.id : undefined;
        this._system = system;
    }
    get component() {
        return this._id ? this._system.components.getById(this._id) || null : null;
    }
    set component(component) {
        if (component && this._type && !(component instanceof this._system.registry.getComponentType(this._type))) {
            throw new Error(`can't assign component of type '${component.type || "unknown"}' to link of type '${this._type}'`);
        }
        this._id = component ? component.id : undefined;
    }
}
exports.default = ComponentReference;


/***/ }),

/***/ "../../libs/ff-graph/source/ComponentSet.ts":
/*!*************************************************!*\
  !*** /app/libs/ff-graph/source/ComponentSet.ts ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Publisher_1 = __webpack_require__(/*! @ff/core/Publisher */ "../../libs/ff-core/source/Publisher.ts");
const Component_1 = __webpack_require__(/*! ./Component */ "../../libs/ff-graph/source/Component.ts");
////////////////////////////////////////////////////////////////////////////////
const _EMPTY_ARRAY = [];
class ComponentSet extends Publisher_1.default {
    constructor() {
        super({ knownEvents: false });
        this._typeLists = { [Component_1.default.type]: [] };
        this._idDict = {};
    }
    /**
     * Adds a new component to the set.
     * @param component
     * @private
     */
    _add(component) {
        if (this._idDict[component.id] !== undefined) {
            throw new Error("component already in set");
        }
        // add component to id dictionary
        this._idDict[component.id] = component;
        let prototype = component;
        const event = { type: "", add: true, remove: false, component };
        // add all types in prototype chain
        do {
            prototype = Object.getPrototypeOf(prototype);
            const type = prototype.type;
            (this._typeLists[type] || (this._typeLists[type] = [])).push(component);
            event.type = type;
            this.emit(event);
        } while (prototype.type !== Component_1.default.type);
    }
    /**
     * Removes a component from the set.
     * @param component
     * @private
     */
    _remove(component) {
        if (this._idDict[component.id] !== component) {
            throw new Error("component not in set");
        }
        // remove component
        delete this._idDict[component.id];
        let prototype = component;
        const event = { type: "", add: false, remove: true, component };
        // remove all types in prototype chain
        do {
            prototype = Object.getPrototypeOf(prototype);
            const type = prototype.type;
            const components = this._typeLists[type];
            components.splice(components.indexOf(component), 1);
            event.type = type;
            this.emit(event);
        } while (prototype.type !== Component_1.default.type);
    }
    /**
     * Removes all components from the set.
     * @private
     */
    _clear() {
        const components = this.cloneArray();
        components.forEach(component => this._remove(component));
    }
    get length() {
        return this._typeLists[Component_1.default.type].length;
    }
    /**
     * Returns true if there are components (of a certain type if given) in this set.
     * @param componentOrType
     */
    has(componentOrType) {
        const components = this._typeLists[Component_1.componentTypeName(componentOrType)];
        return components && components.length > 0;
    }
    /**
     * Returns true if the given component is part of this set.
     * @param component
     */
    contains(component) {
        return !!this._idDict[component.id];
    }
    /**
     * Returns the number of components (of a certain type if given) in this set.
     * @param componentOrType
     */
    count(componentOrType) {
        const components = this._typeLists[Component_1.componentTypeName(componentOrType)];
        return components ? components.length : 0;
    }
    getDictionary() {
        return this._idDict;
    }
    /**
     * Returns an array of components in this set of a specific type if given.
     * @param componentOrType If given only returns components of the given type.
     */
    getArray(componentOrType) {
        return (this._typeLists[Component_1.componentTypeName(componentOrType)] || _EMPTY_ARRAY);
    }
    cloneArray(componentOrType) {
        return this.getArray(componentOrType).slice();
    }
    /**
     * Returns the first found component in this set of the given type.
     * @param componentOrType Type of component to return.
     */
    get(componentOrType) {
        const components = this._typeLists[Component_1.componentTypeName(componentOrType)];
        return components ? components[0] : undefined;
    }
    /**
     * Returns the first found component in this set of the given type.
     * Throws an exception if there is no component of the specified type.
     * @param componentOrType Type of component to return.
     */
    safeGet(componentOrType) {
        const type = Component_1.componentTypeName(componentOrType);
        const components = this._typeLists[type];
        const component = components ? components[0] : undefined;
        if (!component) {
            throw new Error(`no components of type '${type}' in set`);
        }
        return component;
    }
    /**
     * Returns a component by its identifier.
     * @param id A component's identifier.
     */
    getById(id) {
        return this._idDict[id] || null;
    }
    /**
     * Returns the first component of the given type with the given name, or null if no component
     * with the given name exists. Performs a linear search, returns the first matching component found.
     * @param name Name of the component to find.
     * @param componentOrType Optional type restriction.
     */
    findByName(name, componentOrType) {
        const components = this.getArray(componentOrType);
        for (let i = 0, n = components.length; i < n; ++i) {
            if (components[i].name === name) {
                return components[i];
            }
        }
        return null;
    }
    /**
     * Adds a listener for a component add/remove event.
     * @param componentOrType Type name of the component, or component constructor.
     * @param callback Callback function, invoked when the event is emitted.
     * @param context Optional: this context for the callback invocation.
     */
    on(componentOrType, callback, context) {
        super.on(Component_1.componentTypeName(componentOrType), callback, context);
    }
    /**
     * Adds a one-time listener for a component add/remove event.
     * @param componentOrType Type name of the component, or component constructor.
     * @param callback Callback function, invoked when the event is emitted.
     * @param context Optional: this context for the callback invocation.
     */
    once(componentOrType, callback, context) {
        super.once(Component_1.componentTypeName(componentOrType), callback, context);
    }
    /**
     * Removes a listener for a component add/remove event.
     * @param componentOrType Type name of the component, or component constructor.
     * @param callback Callback function, invoked when the event is emitted.
     * @param context Optional: this context for the callback invocation.
     */
    off(componentOrType, callback, context) {
        super.off(Component_1.componentTypeName(componentOrType), callback, context);
    }
    toString(verbose = false) {
        if (verbose) {
            return this.getArray().map(component => component.displayName).join("\n");
        }
        return `components: ${this.length}, types: ${Object.keys(this._typeLists).length}`;
    }
}
exports.default = ComponentSet;


/***/ }),

/***/ "../../libs/ff-graph/source/ComponentTracker.ts":
/*!*****************************************************!*\
  !*** /app/libs/ff-graph/source/ComponentTracker.ts ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Component_1 = __webpack_require__(/*! ./Component */ "../../libs/ff-graph/source/Component.ts");
////////////////////////////////////////////////////////////////////////////////
/**
 * Tracks components of a specific type in the same node.
 * Maintains a reference to the component if found and executes
 * callbacks if the component of the tracked type is added or removed.
 */
class ComponentTracker {
    constructor(set, componentOrType, didAdd, willRemove) {
        this.type = Component_1.componentTypeName(componentOrType);
        this.didAdd = didAdd;
        this.willRemove = willRemove;
        this._set = set;
        set.on(this.type, this.onComponent, this);
        this.component = set.get(componentOrType);
        if (this.component && didAdd) {
            didAdd(this.component);
        }
    }
    dispose() {
        this._set.off(this.type, this.onComponent, this);
        this.component = null;
        this.didAdd = null;
        this.willRemove = null;
    }
    onComponent(event) {
        if (event.add) {
            this.component = event.component;
            this.didAdd && this.didAdd(event.component);
        }
        else if (event.remove) {
            this.willRemove && this.willRemove(event.component);
            this.component = null;
        }
    }
}
exports.default = ComponentTracker;


/***/ }),

/***/ "../../libs/ff-graph/source/Graph.ts":
/*!******************************************!*\
  !*** /app/libs/ff-graph/source/Graph.ts ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const uniqueId_1 = __webpack_require__(/*! @ff/core/uniqueId */ "../../libs/ff-core/source/uniqueId.ts");
const Publisher_1 = __webpack_require__(/*! @ff/core/Publisher */ "../../libs/ff-core/source/Publisher.ts");
const LinkableSorter_1 = __webpack_require__(/*! ./LinkableSorter */ "../../libs/ff-graph/source/LinkableSorter.ts");
const ComponentSet_1 = __webpack_require__(/*! ./ComponentSet */ "../../libs/ff-graph/source/ComponentSet.ts");
const Node_1 = __webpack_require__(/*! ./Node */ "../../libs/ff-graph/source/Node.ts");
const NodeSet_1 = __webpack_require__(/*! ./NodeSet */ "../../libs/ff-graph/source/NodeSet.ts");
/**
 * Graph in a graph/node/component system. A graph contains a collection of nodes.
 * Graphs can be nested, i.e. a graph can be a subgraph of another graph, the parent graph.
 *
 * ### See also
 * - [[Component]]
 * - [[Node]]
 * - [[System]]
 */
class Graph extends Publisher_1.default {
    /**
     * Creates a new graph instance.
     * @param system System this graph belongs to.
     * @param parent Optional parent component of this graph.
     */
    constructor(system, parent) {
        super({ knownEvents: false });
        /** Collection of all nodes in this graph. */
        this.nodes = new NodeSet_1.default();
        /** Collection of all components in this graph. */
        this.components = new ComponentSet_1.default();
        this._sorter = new LinkableSorter_1.default();
        this._sortRequested = true;
        this._sortedList = null;
        this._finalizeList = [];
        this.system = system;
        this.parent = parent;
    }
    // TODO: This should use a tracker for the root component
    /** Sets the root hierarchy component of this graph. */
    set root(root) {
        this._root = root;
        if (this.parent) {
            this.parent.innerRoot = root;
        }
    }
    /** Returns the root hierarchy component of this graph. */
    get root() {
        return this._root;
    }
    /**
     * Called at the begin of each frame cycle. Calls update() on all components
     * in the graph whose changed flag is set, then calls tick() on all components.
     * Returns true if at least one component changed its state.
     * @param context Context-specific information such as time, etc.
     * @returns true if at least one component was updated.
     */
    tick(context) {
        if (this._sortRequested) {
            this._sortRequested = false;
            this.sort();
        }
        // call update on components in topological sort order
        const components = this._sortedList;
        let updated = false;
        for (let i = 0, n = components.length; i < n; ++i) {
            const component = components[i];
            component.updated = false;
            if (component.changed) {
                if (component.update && component.update(context)) {
                    updated = component.updated = true;
                }
                component.resetChanged();
            }
            if (component.tick && component.tick(context)) {
                updated = true;
            }
        }
        return updated;
    }
    /**
     * Calls finalize on all components in the graph.
     * The finalize call happens at the end of a frame cycle.
     * @param context Context-specific information such as time, etc.
     */
    finalize(context) {
        const components = this._finalizeList;
        for (let i = 0, n = components.length; i < n; ++i) {
            components[i].finalize(context);
        }
    }
    clear() {
        const nodes = this.nodes.cloneArray();
        for (let i = 0, n = nodes.length; i < n; ++i) {
            nodes[i].dispose();
        }
    }
    /**
     * Requests a topological sort of the list of components based on how they are interlinked.
     * The sort is executed before the next update.
     */
    requestSort() {
        this._sortRequested = true;
    }
    sort() {
        this._sortedList = this._sorter.sort(this.components.getArray());
        const name = this.parent ? this.parent.name || this.parent.type : "System";
        console.log("Graph.sort - %s: sorted %s components", name, this._sortedList.length);
    }
    /**
     * Creates a new node of the given type. Adds it to the graph.
     * @param nodeOrType Type of the node to create.
     * @param name Optional name for the node.
     * @param id Optional unique identifier for the node (must omit unless serializing).
     */
    createCustomNode(nodeOrType, name, id) {
        const type = this.system.registry.getNodeType(Node_1.nodeTypeName(nodeOrType));
        const node = new type(id || uniqueId_1.default(12, this.system.nodes.getDictionary()));
        node.attach(this);
        if (name) {
            node.name = name;
        }
        if (!id) {
            node.createComponents();
        }
        return node;
    }
    /**
     * Creates a new, plain, empty node (of base type [[Node]]). Adds it to the graph.
     * @param name Optional name for the node.
     * @param id Optional unique identifier for the node (must omit unless serializing).
     */
    createNode(name, id) {
        const node = new Node_1.default(id || uniqueId_1.default(12, this.system.nodes.getDictionary()));
        node.attach(this);
        if (name) {
            node.name = name;
        }
        return node;
    }
    /**
     * Returns a text representation of the graph.
     * @param verbose
     */
    toString(verbose = false) {
        const nodes = this.nodes.getArray();
        const numComponents = this.components.count();
        const text = `Graph - ${nodes.length} nodes, ${numComponents} components.`;
        if (verbose) {
            return text + "\n" + nodes.map(node => node.toString(true)).join("\n");
        }
        return text;
    }
    /**
     * Serializes the graph, its nodes and components.
     * Returns graph serialization data, which must be cloned or stringified immediately.
     */
    deflate() {
        const json = {};
        const jsonNodes = [];
        const nodes = this.nodes.getArray();
        for (let i = 0, n = nodes.length; i < n; ++i) {
            const node = nodes[i];
            const jsonNode = this.deflateNode(node);
            jsonNode.type = node.type;
            jsonNode.id = node.id;
            if (node.name) {
                jsonNode.name = node.name;
            }
            jsonNodes.push(jsonNode);
        }
        if (jsonNodes.length > 0) {
            json.nodes = jsonNodes;
        }
        return json;
    }
    /**
     * Deserializes the graph, its nodes and components.
     * @param json serialized graph data.
     */
    inflate(json) {
        if (json.nodes) {
            json.nodes.forEach(jsonNode => {
                const node = this.createCustomNode(jsonNode.type, jsonNode.name, jsonNode.id);
                node.inflate(jsonNode);
            });
        }
    }
    /**
     * Deserializes references between graphs, nodes, and components
     * @param json serialized graph data.
     */
    inflateReferences(json) {
        if (json.nodes) {
            json.nodes.forEach(jsonNode => {
                const node = this.nodes.getById(jsonNode.id);
                node.inflateReferences(jsonNode);
            });
        }
    }
    /**
     * Override to control how nodes are serialized.
     * Return serialization data or null if the node should be excluded from serialization.
     * @param node The node to be serialized.
     */
    deflateNode(node) {
        return node.deflate();
    }
    /**
     * Adds a node to the graph and the system. Called by [[Node.attach]], do not call directly.
     * @param node
     * @private
     */
    _addNode(node) {
        this.system._addNode(node);
        this.nodes._add(node);
    }
    /**
     * Removes a node from the graph and the system. Called by [[Node.detach]], do not call directly.
     * @param node
     * @private
     */
    _removeNode(node) {
        this.nodes._remove(node);
        this.system._removeNode(node);
    }
    /**
     * Adds a component to the graph and the system. Called by [[Component.attach]], do not call directly.
     * @param component
     * @private
     */
    _addComponent(component) {
        if (component.isGraphSingleton && this.components.has(component)) {
            throw new Error(`only one component of type '${component.type}' allowed per graph`);
        }
        this.system._addComponent(component);
        this.components._add(component);
        if (component.finalize) {
            this._finalizeList.push(component);
        }
        this._sortRequested = true;
    }
    /**
     * Removes a component from the graph and the system. Called by [[Component.detach]], do not call directly.
     * @param component
     * @private
     */
    _removeComponent(component) {
        this.components._remove(component);
        this.system._removeComponent(component);
        if (component.finalize) {
            this._finalizeList.splice(this._finalizeList.indexOf(component), 1);
        }
        this._sortRequested = true;
    }
}
exports.default = Graph;


/***/ }),

/***/ "../../libs/ff-graph/source/LinkableSorter.ts":
/*!***************************************************!*\
  !*** /app/libs/ff-graph/source/LinkableSorter.ts ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
////////////////////////////////////////////////////////////////////////////////
/**
 * Sorts an array of [[ILinkable]] such that if a is linked to b, a comes before b.
 */
class LinkableSorter {
    constructor() {
        this.visited = {};
        this.visiting = {};
        this.sorted = [];
    }
    sort(linkables) {
        for (let i = 0, n = linkables.length; i < n; ++i) {
            this.visit(linkables[i]);
        }
        const sorted = this.sorted;
        this.visited = {};
        this.visiting = {};
        this.sorted = [];
        return sorted;
    }
    visit(linkable) {
        const visited = this.visited;
        const visiting = this.visiting;
        if (visited[linkable.id] || visiting[linkable.id]) {
            return;
        }
        visiting[linkable.id] = true;
        // for each output property, follow all outgoing links
        const outProps = linkable.outs.properties;
        for (let i0 = 0, n0 = outProps.length; i0 < n0; ++i0) {
            const outLinks = outProps[i0].outLinks;
            for (let i1 = 0, n1 = outLinks.length; i1 < n1; ++i1) {
                const ins = outLinks[i1].destination.group;
                // follow outgoing links at input properties
                const inProps = ins.properties;
                for (let i2 = 0, n2 = inProps.length; i2 < n2; ++i2) {
                    const links = inProps[i2].outLinks;
                    for (let i3 = 0, n3 = links.length; i3 < n3; ++i3) {
                        const linkedIns = links[i3].destination.group;
                        this.visit(linkedIns.linkable);
                    }
                }
                this.visit(ins.linkable);
            }
        }
        visiting[linkable.id] = undefined;
        visited[linkable.id] = true;
        this.sorted.unshift(linkable);
    }
}
exports.default = LinkableSorter;


/***/ }),

/***/ "../../libs/ff-graph/source/Node.ts":
/*!*****************************************!*\
  !*** /app/libs/ff-graph/source/Node.ts ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const uniqueId_1 = __webpack_require__(/*! @ff/core/uniqueId */ "../../libs/ff-core/source/uniqueId.ts");
const Publisher_1 = __webpack_require__(/*! @ff/core/Publisher */ "../../libs/ff-core/source/Publisher.ts");
const Component_1 = __webpack_require__(/*! ./Component */ "../../libs/ff-graph/source/Component.ts");
const ComponentSet_1 = __webpack_require__(/*! ./ComponentSet */ "../../libs/ff-graph/source/ComponentSet.ts");
/** Returns the type string of the given [[NodeOrType]]. */
function nodeTypeName(nodeOrType) {
    return nodeOrType ? (typeof nodeOrType === "string" ? nodeOrType : nodeOrType.type) : Node.type;
}
exports.nodeTypeName = nodeTypeName;
/**
 * Node in an graph/node/component system.
 *
 * ### Events
 * - *"change"* - emits [[INodeChangeEvent]] after the node's state has changed.
 * - *"dispose"* - emits [[INodeDisposeEvent]] if the node is about to be disposed.
 *
 * ### See also
 * - [[Component]]
 * - [[Graph]]
 * - [[System]]
 */
class Node extends Publisher_1.default {
    /**
     * Protected constructor. Please use [[Graph.createNode]] / [[Graph.createCustomNode]] to create node instances.
     * @param id Unique id for the node. A unique id is usually created automatically,
     * do not specify except while de-serializing the component.
     *
     * Note that during execution of the constructor, the node is not yet attached to a graph/system.
     * Do not try to get access to other nodes, components, the parent graph, or the system here.
     */
    constructor(id) {
        super({ knownEvents: false });
        /** Collection of all components in this node. */
        this.components = new ComponentSet_1.default();
        this._graph = null;
        this._name = "";
        this.id = id;
    }
    /**
     * Returns the type identifier of this component.
     * @returns {string}
     */
    get type() {
        return this.constructor.type;
    }
    get text() {
        return this.constructor.text;
    }
    get icon() {
        return this.constructor.icon;
    }
    /**
     * Returns the system this node and its graph belong to.
     */
    get system() {
        return this._graph.system;
    }
    /**
     * Returns the graph this node is part of.
     */
    get graph() {
        return this._graph;
    }
    /**
     * Returns the name of this node.
     * @returns {string}
     */
    get name() {
        return this._name;
    }
    get displayName() {
        return this._name || this.text || this.type;
    }
    /**
     * Sets the name of this node.
     * This emits an [[INodeChangeEvent]]
     * @param {string} value
     */
    set name(value) {
        this._name = value;
        this.emit({ type: "change", what: "name", node: this });
    }
    /**
     * Adds this node to the given graph and the system.
     * @param graph
     */
    attach(graph) {
        if (this._graph) {
            this.detach();
        }
        this._graph = graph;
        graph._addNode(this);
    }
    /**
     * Removes this node from its graph and system.
     */
    detach() {
        if (this._graph) {
            this._graph._removeNode(this);
            this._graph = null;
        }
    }
    /**
     * Override to create an initial set of components for the node.
     * Note that this function is not called if a node is restored from serialization data.
     */
    createComponents() {
    }
    clear() {
        // dispose components
        const componentList = this.components.getArray().slice();
        componentList.forEach(component => component.dispose());
    }
    /**
     * Must be called to delete/destroy the node. This unregisters the node
     * and all its components from the system.
     */
    dispose() {
        // dispose components
        const componentList = this.components.getArray().slice();
        componentList.forEach(component => component.dispose());
        // remove node from system and graph
        this.detach();
        // emit dispose event
        this.emit({ type: "dispose", node: this });
    }
    /**
     * Creates a new component of the given type. Adds it to this node.
     * @param componentOrType Component constructor, type name, or instance.
     * @param name Optional name for the component.
     * @param id Optional unique identifier for the component (must omit unless serializing).
     */
    createComponent(componentOrType, name, id) {
        const type = this.system.registry.getComponentType(Component_1.componentTypeName(componentOrType));
        const component = new type(id || uniqueId_1.default(12, this.system.components.getDictionary()));
        component.attach(this);
        if (name) {
            component.name = name;
        }
        return component;
    }
    /**
     * Tests whether the node is of or descends from the given type.
     * @param nodeOrType Node constructor, type name, or instance.
     */
    is(nodeOrType) {
        const type = nodeTypeName(nodeOrType);
        let prototype = this;
        do {
            prototype = Object.getPrototypeOf(prototype);
            if (prototype.type === type) {
                return true;
            }
        } while (prototype.type !== Node.type);
        return false;
    }
    /**
     * Returns a text representation of the node.
     * @param verbose
     */
    toString(verbose = false) {
        const components = this.components.getArray();
        const text = `Node '${this.name}' - ${components.length} components`;
        if (verbose) {
            return text + "\n" + components.map(component => "  " + component.toString()).join("\n");
        }
        return text;
    }
    /**
     * Serializes the node and its components.
     * Return node serialization data, or null if the node should be excluded from serialization.
     */
    deflate() {
        const json = {};
        const jsonComponents = [];
        const components = this.components.getArray();
        for (let i = 0, n = components.length; i < n; ++i) {
            const component = components[i];
            const jsonComp = this.deflateComponent(component);
            jsonComp.type = component.type;
            jsonComp.id = component.id;
            if (component.name) {
                jsonComp.name = component.name;
            }
            jsonComponents.push(jsonComp);
        }
        if (jsonComponents.length > 0) {
            json.components = jsonComponents;
        }
        return json;
    }
    /**
     * Deserializes the node and its components.
     * @param json serialized node data.
     * @param linkableDict dictionary mapping component ids to components.
     */
    inflate(json, linkableDict) {
        if (json.components) {
            json.forEach(jsonComp => {
                const component = this.createComponent(jsonComp.type, jsonComp.name, jsonComp.id);
                component.inflate(jsonComp);
            });
        }
    }
    /**
     * Deserializes the links of all components.
     * @param json serialized component data.
     */
    inflateReferences(json) {
        if (json.components) {
            json.components.forEach(jsonComp => {
                const component = this.components.getById(jsonComp.id);
                component.inflateReferences(jsonComp);
            });
        }
    }
    /**
     * Override to control how components are serialized.
     * Return serialization data or null if the component should be excluded from serialization.
     * @param component The component to be serialized.
     */
    deflateComponent(component) {
        return component.deflate();
    }
    /**
     * Adds a component to the node, the node's graph and the system. Called by [[Component.attach]],
     * do not call directly.
     * @param component
     * @private
     */
    _addComponent(component) {
        if (component.isNodeSingleton && this.components.has(component)) {
            throw new Error(`only one component of type '${component.type}' allowed per node`);
        }
        this.graph._addComponent(component);
        this.components._add(component);
    }
    /**
     * Removes a component from the node, the node's graph and the system. Called by [[Component.detach]],
     * do not call directly.
     * @param component
     * @private
     */
    _removeComponent(component) {
        this.components._remove(component);
        this.graph._removeComponent(component);
    }
}
Node.type = "Node";
Node.text = "";
Node.icon = "";
exports.default = Node;


/***/ }),

/***/ "../../libs/ff-graph/source/NodeSet.ts":
/*!********************************************!*\
  !*** /app/libs/ff-graph/source/NodeSet.ts ***!
  \********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Publisher_1 = __webpack_require__(/*! @ff/core/Publisher */ "../../libs/ff-core/source/Publisher.ts");
const Node_1 = __webpack_require__(/*! ./Node */ "../../libs/ff-graph/source/Node.ts");
////////////////////////////////////////////////////////////////////////////////
const _EMPTY_ARRAY = [];
class NodeSet extends Publisher_1.default {
    constructor() {
        super({ knownEvents: false });
        this._typeLists = { [Node_1.default.type]: [] };
        this._idDict = {};
    }
    /**
     * Adds a node to the set. Automatically called by the node constructor.
     * @param node
     * @private
     */
    _add(node) {
        if (this._idDict[node.id] !== undefined) {
            throw new Error("node already in set");
        }
        // add node to id dictionary
        this._idDict[node.id] = node;
        let prototype = node;
        const event = { type: "", add: true, remove: false, node };
        // add all types in prototype chain
        do {
            prototype = Object.getPrototypeOf(prototype);
            const type = prototype.type;
            (this._typeLists[type] || (this._typeLists[type] = [])).push(node);
            event.type = type;
            this.emit(event);
        } while (prototype.type !== Node_1.default.type);
    }
    /**
     * Removes a node from the set. Automatically called by the node's dispose method.
     * @param node
     * @private
     */
    _remove(node) {
        if (this._idDict[node.id] !== node) {
            throw new Error("node not in set");
        }
        // remove node from id dictionary
        delete this._idDict[node.id];
        let prototype = node;
        const event = { type: "", add: false, remove: true, node };
        // remove all types in prototype chain
        do {
            prototype = Object.getPrototypeOf(prototype);
            const type = prototype.type;
            const nodes = this._typeLists[type];
            nodes.splice(nodes.indexOf(node), 1);
            event.type = type;
            this.emit(event);
        } while (prototype.type !== Node_1.default.type);
    }
    /**
     * Removes all nodes from the set.
     * @private
     */
    _clear() {
        const nodes = this.cloneArray();
        nodes.forEach(node => this._remove(node));
    }
    get length() {
        return this._typeLists[Node_1.default.type].length;
    }
    /**
     * Returns true if there are nodes (of a certain type if given) in this set.
     * @param nodeOrType
     */
    has(nodeOrType) {
        const nodes = this._typeLists[Node_1.nodeTypeName(nodeOrType)];
        return nodes && nodes.length > 0;
    }
    /**
     * Returns true if the given node is part of this set.
     * @param node
     */
    contains(node) {
        return !!this._idDict[node.id];
    }
    /**
     * Returns the number of nodes (of a certain type if given) in this set.
     * @param nodeOrType
     */
    count(nodeOrType) {
        const nodes = this._typeLists[Node_1.nodeTypeName(nodeOrType)];
        return nodes ? nodes.length : 0;
    }
    getDictionary() {
        return this._idDict;
    }
    /**
     * Returns an array of nodes in this set of a specific type if given.
     * @param nodeOrType If given only returns nodes of the given type.
     */
    getArray(nodeOrType) {
        return (this._typeLists[Node_1.nodeTypeName(nodeOrType)] || _EMPTY_ARRAY);
    }
    cloneArray(nodeOrType) {
        return this.getArray(nodeOrType).slice();
    }
    /**
     * Returns the first found node in this set of the given type.
     * @param nodeOrType Type of node to return.
     */
    get(nodeOrType) {
        const nodes = this._typeLists[Node_1.nodeTypeName(nodeOrType)];
        return nodes ? nodes[0] : undefined;
    }
    /**
     * Returns the first found node in this set of the given type.
     * Throws an exception if there is no node of the specified type.
     * @param nodeOrType Type of node to return.
     */
    safeGet(nodeOrType) {
        const type = Node_1.nodeTypeName(nodeOrType);
        const nodes = this._typeLists[type];
        const node = nodes ? nodes[0] : undefined;
        if (!node) {
            throw new Error(`no nodes of type '${type}' in set`);
        }
        return node;
    }
    /**
     * Returns a node by its identifier.
     * @param {string} id An node's identifier.
     */
    getById(id) {
        return this._idDict[id] || null;
    }
    /**
     * Returns the first node with the given name, or null if no node with
     * the given name exists. Performs a linear search, returns the first matching component found.
     * @param name Name of the node to find.
     * @param nodeOrType Optional type restriction.
     */
    findByName(name, nodeOrType) {
        const nodes = this.getArray(nodeOrType);
        for (let i = 0, n = nodes.length; i < n; ++i) {
            if (nodes[i].name === name) {
                return nodes[i];
            }
        }
        return null;
    }
    /**
     * Returns all nodes not containing a hierarchy component with a parent.
     * Performs a linear search; don't use in time-critical code.
     */
    findRoots(nodeOrType) {
        const nodes = this._typeLists[Node_1.nodeTypeName(nodeOrType)];
        const result = [];
        for (let i = 0, n = nodes.length; i < n; ++i) {
            const hierarchy = nodes[i].components.get("CHierarchy");
            if (!hierarchy || !hierarchy.parent) {
                result.push(nodes[i]);
            }
        }
        return result;
    }
    /**
     * Adds a listener for a node add/remove event.
     * @param nodeOrType Type name of the node, or node constructor.
     * @param callback Callback function, invoked when the event is emitted.
     * @param context Optional: this context for the callback invocation.
     */
    on(nodeOrType, callback, context) {
        super.on(Node_1.nodeTypeName(nodeOrType), callback, context);
    }
    /**
     * Adds a one-time listener for a node add/remove event.
     * @param nodeOrType Type name of the node, or node constructor.
     * @param callback Callback function, invoked when the event is emitted.
     * @param context Optional: this context for the callback invocation.
     */
    once(nodeOrType, callback, context) {
        super.once(Node_1.nodeTypeName(nodeOrType), callback, context);
    }
    /**
     * Removes a listener for a node add/remove event.
     * @param nodeOrType Type name of the node, or node constructor.
     * @param callback Callback function, invoked when the event is emitted.
     * @param context Optional: this context for the callback invocation.
     */
    off(nodeOrType, callback, context) {
        super.off(Node_1.nodeTypeName(nodeOrType), callback, context);
    }
    toString(verbose = false) {
        if (verbose) {
            return this.getArray().map(node => node.displayName).join("\n");
        }
        return `nodes: ${this.length}, types: ${Object.keys(this._typeLists).length}`;
    }
}
exports.default = NodeSet;


/***/ }),

/***/ "../../libs/ff-graph/source/Property.ts":
/*!*********************************************!*\
  !*** /app/libs/ff-graph/source/Property.ts ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const isSubclass_1 = __webpack_require__(/*! @ff/core/isSubclass */ "../../libs/ff-core/source/isSubclass.ts");
const Publisher_1 = __webpack_require__(/*! @ff/core/Publisher */ "../../libs/ff-core/source/Publisher.ts");
const convert_1 = __webpack_require__(/*! ./convert */ "../../libs/ff-graph/source/convert.ts");
const PropertyLink_1 = __webpack_require__(/*! ./PropertyLink */ "../../libs/ff-graph/source/PropertyLink.ts");
const propertyTypes_1 = __webpack_require__(/*! ./propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
exports.schemas = propertyTypes_1.schemas;
exports.types = propertyTypes_1.types;
/**
 * Linkable property.
 */
class Property extends Publisher_1.default {
    /**
     * Creates a new linkable property.
     * @param path Name and group(s) the property is displayed under.
     * @param schema Property schema definition.
     * @param user Marks the property as user-defined if set to true.
     */
    constructor(path, schema, user) {
        super();
        this.addEvents("value", "link", "change", "dispose");
        if (!schema || schema.preset === undefined) {
            throw new Error("missing schema/preset");
        }
        const preset = schema.preset;
        const isArray = Array.isArray(preset);
        this.type = typeof (isArray ? preset[0] : preset);
        this.schema = schema;
        this.user = user || false;
        this.elementCount = isArray ? preset.length : 1;
        this.inLinks = [];
        this.outLinks = [];
        this._group = null;
        this._key = "";
        this._path = path;
        this.value = null;
        this.reset();
        this.changed = !schema.event;
    }
    get group() {
        return this._group;
    }
    get key() {
        return this._key;
    }
    get path() {
        return this._path;
    }
    set path(path) {
        this._path = path;
        this.emit({ type: "change", what: "path", property: this });
    }
    /**
     * Adds the property to the given group.
     * @param group The property group this property should be added to.
     * @param key An optional key under which the property is accessible in the property group.
     * @param index An optional index position where the property should be inserted in the group.
     */
    attach(group, key, index) {
        group._addProperty(this, key, index);
    }
    /**
     * Removes the property from the group it was previously added to.
     * Does nothing if the property is not member of a group.
     */
    detach() {
        if (this._group) {
            this._group._removeProperty(this);
        }
    }
    /**
     * Removes the property from its group, removes all links.
     * Emits a [[IPropertyDisposeEvent]] event.
     */
    dispose() {
        this.unlink();
        this.detach();
        this.emit({ type: "dispose", property: this });
    }
    setValue(value, silent) {
        this.value = value;
        if (!silent) {
            this.changed = true;
            if (this.isInput()) {
                this._group.linkable.changed = true;
            }
        }
        this.emit("value", value);
        const outLinks = this.outLinks;
        for (let i = 0, n = outLinks.length; i < n; ++i) {
            outLinks[i].push();
        }
    }
    copyValue(value, silent) {
        if (Array.isArray(value)) {
            value = value.slice();
        }
        this.setValue(value, silent);
    }
    set(silent) {
        if (!silent) {
            this.changed = true;
            if (this.isInput()) {
                this._group.linkable.changed = true;
            }
        }
        this.emit("value", this.value);
        const outLinks = this.outLinks;
        for (let i = 0, n = outLinks.length; i < n; ++i) {
            outLinks[i].push();
        }
    }
    cloneValue() {
        const value = this.value;
        return Array.isArray(value) ? value.slice() : value;
    }
    /**
     * Returns the property value, validated against the property schema.
     * @param result Optional array to write the validated values into.
     */
    getValidatedValue(result) {
        const value = this.value;
        if (this.isArray()) {
            result = result || [];
            for (let i = 0, n = value.length; i < n; ++i) {
                result[i] = this.validateValue(value[i]);
            }
            return result;
        }
        return this.validateValue(value);
    }
    linkTo(destination, sourceIndex, destinationIndex) {
        destination.linkFrom(this, sourceIndex, destinationIndex);
    }
    linkFrom(source, sourceIndex, destinationIndex) {
        if (!this.canLinkFrom(source, sourceIndex, destinationIndex)) {
            throw new Error("can't link");
        }
        const link = new PropertyLink_1.default(source, this, sourceIndex, destinationIndex);
        source.addOutLink(link);
        this.addInLink(link);
    }
    unlinkTo(destination, sourceIndex, destinationIndex) {
        destination.unlinkFrom(this, sourceIndex, destinationIndex);
    }
    unlinkFrom(source, sourceIndex, destinationIndex) {
        const link = this.inLinks.find(link => link.source === source
            && link.sourceIndex === sourceIndex
            && link.destinationIndex === destinationIndex);
        if (!link) {
            return false;
        }
        source.removeOutLink(link);
        this.removeInLink(link);
        return true;
    }
    unlink() {
        const inLinks = this.inLinks.slice();
        inLinks.forEach(link => {
            link.source.removeOutLink(link);
            this.removeInLink(link);
        });
        const outLinks = this.outLinks.slice();
        outLinks.forEach(link => {
            this.removeOutLink(link);
            link.destination.removeInLink(link);
        });
        if (this.inLinks.length !== 0 || this.outLinks.length !== 0) {
            throw new Error("fatal: leftover links");
        }
    }
    addInLink(link) {
        if (link.destination !== this) {
            throw new Error("input link's destination must equal this");
        }
        this.inLinks.push(link);
        this.requestSort();
        this.emit({
            type: "link", add: true, remove: false, link
        });
    }
    addOutLink(link) {
        if (link.source !== this) {
            throw new Error("output link's source must equal this");
        }
        this.outLinks.push(link);
        this.requestSort();
        // push value through added link
        link.push();
    }
    removeInLink(link) {
        const index = this.inLinks.indexOf(link);
        if (index < 0) {
            throw new Error("input link not found");
        }
        this.inLinks.splice(index, 1);
        this.requestSort();
        // if last link is removed and if object, reset to default (usually null) values
        if (this.inLinks.length === 0 && this.type === "object") {
            this.reset();
        }
        this.emit({
            type: "link", add: false, remove: true, link
        });
    }
    removeOutLink(link) {
        const index = this.outLinks.indexOf(link);
        if (index < 0) {
            throw new Error("output link not found");
        }
        this.outLinks.splice(index, 1);
        this.requestSort();
    }
    canLinkTo(destination, sourceIndex, destinationIndex) {
        return destination.canLinkFrom(this, sourceIndex, destinationIndex);
    }
    canLinkFrom(source, sourceIndex, destinationIndex) {
        // can't link to an output property
        if (this.isOutput()) {
            return false;
        }
        const hasSrcIndex = sourceIndex >= 0;
        const hasDstIndex = destinationIndex >= 0;
        if (!source.isArray() && hasSrcIndex) {
            throw new Error("non-array source property; can't link to element");
        }
        if (!this.isArray() && hasDstIndex) {
            throw new Error("non-array destination property; can't link to element");
        }
        const srcIsArray = source.isArray() && !hasSrcIndex;
        const dstIsArray = this.isArray() && !hasDstIndex;
        if (srcIsArray !== dstIsArray) {
            return false;
        }
        if (srcIsArray && source.elementCount !== this.elementCount) {
            return false;
        }
        if (source.type === "object" && this.type === "object") {
            if (!isSubclass_1.default(source.schema.objectType, this.schema.objectType)) {
                return false;
            }
        }
        return convert_1.canConvert(source.type, this.type);
    }
    reset() {
        let value;
        if (this.isMulti()) {
            let multiArray = this.value;
            if (!multiArray) {
                value = multiArray = [];
            }
            else {
                multiArray.length = 1;
            }
            multiArray[0] = this.clonePreset();
        }
        else {
            value = this.clonePreset();
        }
        // set changed flag and push to output links
        this.setValue(value);
    }
    setMultiChannelCount(count) {
        if (!this.isMulti()) {
            throw new Error("can't set multi channel count on non-multi property");
        }
        const multiArray = this.value;
        const currentCount = multiArray.length;
        multiArray.length = count;
        for (let i = currentCount; i < count; ++i) {
            multiArray[i] = this.clonePreset();
        }
        this.changed = true;
    }
    requestSort() {
        if (this._group && this._group.linkable) {
            this._group.linkable.requestSort();
        }
    }
    setOptions(options) {
        if (!this.schema.options) {
            throw new Error(`property type mismatch, can't set options on '${this.path}'`);
        }
        this.schema.options = options.slice();
        this.emit({ type: "change", what: "options", property: this });
    }
    getOptionText() {
        const options = this.schema.options;
        if (this.type === "number" && options) {
            const i = Math.trunc(this.value);
            return options[i < 0 ? 0 : (i >= options.length ? 0 : i)] || "";
        }
    }
    isInput() {
        return this._group && this._group === this._group.linkable.ins;
    }
    isOutput() {
        return this._group && this._group === this._group.linkable.outs;
    }
    isArray() {
        return Array.isArray(this.schema.preset);
    }
    isMulti() {
        return !!this.schema.multi;
    }
    isDefault() {
        const value = this.schema.multi ? this.value[0] : this.value;
        const preset = this.schema.preset;
        const valueLength = Array.isArray(value) ? value.length : -1;
        const presetLength = Array.isArray(preset) ? preset.length : -1;
        if (valueLength !== presetLength) {
            return false;
        }
        if (valueLength >= 0) {
            for (let i = 0; i < valueLength; ++i) {
                if (value[i] !== preset[i]) {
                    return false;
                }
            }
            return true;
        }
        return value === preset;
    }
    hasInLinks(index) {
        const links = this.inLinks;
        if (!(index >= 0)) {
            return links.length > 0;
        }
        for (let i = 0, n = links.length; i < n; ++i) {
            if (links[i].destinationIndex === index) {
                return true;
            }
        }
        return false;
    }
    hasMainInLinks() {
        const links = this.inLinks;
        for (let i = 0, n = links.length; i < n; ++i) {
            if (!(links[i].destinationIndex >= 0)) {
                return true;
            }
        }
        return false;
    }
    hasOutLinks(index) {
        const links = this.outLinks;
        if (!(index >= 0)) {
            return links.length > 0;
        }
        for (let i = 0, n = links.length; i < n; ++i) {
            if (links[i].sourceIndex === index) {
                return true;
            }
        }
        return false;
    }
    inLinkCount() {
        return this.inLinks.length;
    }
    outLinkCount() {
        return this.outLinks.length;
    }
    deflate() {
        let json = this.user ? {
            path: this.path,
            schema: Object.assign({}, this.schema)
        } : null;
        if (!this.isOutput() && !this.hasMainInLinks() && !this.isDefault() && this.type !== "object") {
            json = json || {};
            json.value = this.value;
        }
        if (this.outLinks.length > 0) {
            json = json || {};
            json.links = this.outLinks.map(link => {
                const jsonLink = {
                    id: link.destination._group.linkable.id,
                    key: link.destination.key
                };
                if (link.sourceIndex >= 0) {
                    jsonLink.srcIndex = link.sourceIndex;
                }
                if (link.destinationIndex >= 0) {
                    jsonLink.dstIndex = link.destinationIndex;
                }
                return jsonLink;
            });
        }
        return json;
    }
    inflate(json, linkableDict) {
        if (json.value !== undefined) {
            this.value = json.value;
        }
        if (json.links !== undefined) {
            json.links.forEach(link => {
                const target = linkableDict[link.id];
                const property = target.ins[link.key];
                property.linkFrom(this, link.srcIndex, link.dstIndex);
            });
        }
    }
    /**
     * Returns a text representation.
     */
    toString() {
        const schema = this.schema;
        const typeName = schema.event ? "event" : (schema.options ? "enum" : this.type);
        return `${this.path} [${typeName}]`;
    }
    /**
     * Validates the given value against the property schema.
     * @param value
     */
    validateValue(value) {
        const schema = this.schema;
        if (schema.enum) {
            const i = Math.trunc(value);
            return schema.enum[i] ? i : 0;
        }
        if (schema.options) {
            const i = Math.trunc(value);
            return i < 0 ? 0 : (i >= schema.options.length ? 0 : i);
        }
        if (this.type === "number") {
            value = schema.min ? Math.max(schema.min, value) : value;
            value = schema.max ? Math.min(schema.max, value) : value;
            return value;
        }
        return value;
    }
    clonePreset() {
        const preset = this.schema.preset;
        return Array.isArray(preset) ? preset.slice() : preset;
    }
}
exports.default = Property;


/***/ }),

/***/ "../../libs/ff-graph/source/PropertyGroup.ts":
/*!**************************************************!*\
  !*** /app/libs/ff-graph/source/PropertyGroup.ts ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Publisher_1 = __webpack_require__(/*! @ff/core/Publisher */ "../../libs/ff-core/source/Publisher.ts");
const Property_1 = __webpack_require__(/*! ./Property */ "../../libs/ff-graph/source/Property.ts");
/**
 * A set of properties. Properties can be linked, such that one property updates another.
 * After adding properties to the set, they are available on the set using their key.
 * To make use of linkable properties, classes must implement the [[ILinkable]] interface.
 *
 * ### Events
 * - *"change"* - emits [[IPropertiesChangeEvent]] after properties have been added, removed, or renamed.
 */
class PropertyGroup extends Publisher_1.default {
    constructor(linkable) {
        super();
        this.addEvent("property");
        this.linkable = linkable;
        this.properties = [];
    }
    dispose() {
        this.unlinkAllProperties();
    }
    isInputGroup() {
        return this === this.linkable.ins;
    }
    isOutputGroup() {
        return this === this.linkable.outs;
    }
    /**
     * Appends properties to the set.
     * @param templates plain object with property templates.
     * @param index Optional index at which to insert the properties.
     */
    createPropertiesFromTemplates(templates, index) {
        Object.keys(templates).forEach((key, i) => {
            const ii = index === undefined ? undefined : index + i;
            this.createPropertyFromTemplate(templates[key], key, ii);
        });
        return this;
    }
    createPropertyFromTemplate(template, key, index) {
        const property = new Property_1.default(template.path, template.schema);
        this._addProperty(property, key, index);
    }
    /**
     * Returns a property by key.
     * @param {string} key The key of the property to be returned.
     * @returns {Property}
     */
    getProperty(key) {
        const property = this[key];
        if (!property) {
            throw new Error(`no property found with key '${key}'`);
        }
        return property;
    }
    getKeys(includeObjects = false) {
        const keys = [];
        this.properties.forEach(property => {
            if (includeObjects || property.type !== "object") {
                keys.push(property.key);
            }
        });
        return keys;
    }
    getValues(includeObjects = false) {
        const values = [];
        this.properties.map(property => {
            if (includeObjects || property.type !== "object") {
                values.push(property.value);
            }
        });
        return values;
    }
    cloneValues(includeObjects = false) {
        const values = [];
        this.properties.map(property => {
            if (includeObjects || property.type !== "object") {
                values.push(property.cloneValue());
            }
        });
        return values;
    }
    setValues(values) {
        Object.keys(values).forEach(key => this.getProperty(key).value = values[key]);
    }
    /**
     * Sets the values of multiple properties. Properties are identified by key.
     * @param values Dictionary of property key/value pairs.
     */
    copyValues(values) {
        Object.keys(values).forEach(key => this.getProperty(key).copyValue(values[key]));
    }
    unlinkAllProperties() {
        this.properties.forEach(property => property.unlink());
    }
    deflate() {
        let json = null;
        this.properties.forEach(property => {
            const jsonProp = property.deflate();
            if (jsonProp) {
                json = json || {};
                json[property.key] = jsonProp;
            }
        });
        return json;
    }
    inflate(json) {
        Object.keys(json).forEach(key => {
            const jsonProp = json[key];
            if (jsonProp.schema) {
                const property = new Property_1.default(jsonProp.path, jsonProp.schema, true);
                property.attach(this, key);
            }
        });
    }
    inflateLinks(json, linkableDict) {
        Object.keys(json).forEach(key => {
            this[key].inflate(json[key], linkableDict);
        });
    }
    _addProperty(property, key, index) {
        if (property.group) {
            property.detach();
        }
        if (key && this[key]) {
            throw new Error(`key '${key}' already exists in group`);
        }
        property._group = this;
        property._key = key;
        if (index === undefined) {
            this.properties.push(property);
        }
        else {
            this.properties.splice(index, 0, property);
        }
        if (key) {
            this[key] = property;
        }
        this.emit({
            type: "property", add: true, remove: false, property
        });
    }
    /**
     * Removes the given property from the set.
     * @param {Property} property The property to be removed.
     */
    _removeProperty(property) {
        if (property.group === this) {
            if (this[property.key] !== property) {
                throw new Error(`property not found in group: ${property.key}`);
            }
            this.properties.slice(this.properties.indexOf(property), 1);
            if (property.key) {
                delete this[property.key];
            }
            property._group = null;
            property._key = "";
            this.emit({
                type: "property", add: false, remove: true, property
            });
        }
    }
}
exports.default = PropertyGroup;


/***/ }),

/***/ "../../libs/ff-graph/source/PropertyLink.ts":
/*!*************************************************!*\
  !*** /app/libs/ff-graph/source/PropertyLink.ts ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const convert_1 = __webpack_require__(/*! ./convert */ "../../libs/ff-graph/source/convert.ts");
class PropertyLink {
    constructor(source, destination, sourceIndex, destinationIndex) {
        if (source.elementCount === 1 && sourceIndex >= 0) {
            throw new Error("non-array source property; can't link to element");
        }
        if (destination.elementCount === 1 && destinationIndex >= 0) {
            throw new Error("non-array destination property; can't link to element");
        }
        this.source = source;
        this.destination = destination;
        this.sourceIndex = sourceIndex;
        this.destinationIndex = destinationIndex;
        const srcIndex = sourceIndex === undefined ? -1 : sourceIndex;
        const dstIndex = destinationIndex === undefined ? -1 : destinationIndex;
        const isArray = source.elementCount > 1 && srcIndex < 0 && dstIndex < 0;
        this.fnConvert = convert_1.getConversionFunction(source.type, destination.type, isArray);
        const fnElementCopy = convert_1.getElementCopyFunction(srcIndex, dstIndex, this.fnConvert);
        this.fnCopy = convert_1.getMultiCopyFunction(source.isMulti(), destination.isMulti(), fnElementCopy);
    }
    push() {
        this.destination.setValue(this.fnCopy(this.source.value, this.destination.value, this.fnConvert));
    }
}
exports.default = PropertyLink;


/***/ }),

/***/ "../../libs/ff-graph/source/Registry.ts":
/*!*********************************************!*\
  !*** /app/libs/ff-graph/source/Registry.ts ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
////////////////////////////////////////////////////////////////////////////////
/**
 * Registry for component types. Each component type should register itself
 * with the registry. The registry is used to construct subtypes of components
 * during inflation (de-serialization) of a node-component systems.
 */
class Registry {
    constructor() {
        this.nodeTypes = {};
        this.componentTypes = {};
    }
    getNodeType(type) {
        const nodeType = this.nodeTypes[type];
        if (!nodeType) {
            throw new Error(`node type not found for type id: '${type}'`);
        }
        return nodeType;
    }
    getComponentType(type) {
        const componentType = this.componentTypes[type];
        if (!componentType) {
            throw new Error(`component type not found for type id: '${type}'`);
        }
        return componentType;
    }
    registerNodeType(nodeType) {
        if (Array.isArray(nodeType)) {
            nodeType.forEach(nodeType => this.registerNodeType(nodeType));
        }
        else {
            if (this.nodeTypes[nodeType.type]) {
                console.warn(nodeType);
                throw new Error(`node type already registered: '${nodeType.type}'`);
            }
            this.nodeTypes[nodeType.type] = nodeType;
        }
    }
    registerComponentType(componentType) {
        if (Array.isArray(componentType)) {
            componentType.forEach(componentType => this.registerComponentType(componentType));
        }
        else {
            if (this.componentTypes[componentType.type]) {
                console.warn(componentType);
                throw new Error(`component type already registered: '${componentType.type}'`);
            }
            this.componentTypes[componentType.type] = componentType;
        }
    }
}
exports.default = Registry;


/***/ }),

/***/ "../../libs/ff-graph/source/System.ts":
/*!*******************************************!*\
  !*** /app/libs/ff-graph/source/System.ts ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Publisher_1 = __webpack_require__(/*! @ff/core/Publisher */ "../../libs/ff-core/source/Publisher.ts");
const ComponentSet_1 = __webpack_require__(/*! ./ComponentSet */ "../../libs/ff-graph/source/ComponentSet.ts");
const NodeSet_1 = __webpack_require__(/*! ./NodeSet */ "../../libs/ff-graph/source/NodeSet.ts");
const Graph_1 = __webpack_require__(/*! ./Graph */ "../../libs/ff-graph/source/Graph.ts");
const Registry_1 = __webpack_require__(/*! ./Registry */ "../../libs/ff-graph/source/Registry.ts");
class System extends Publisher_1.default {
    constructor(registry) {
        super({ knownEvents: false });
        this.registry = registry || new Registry_1.default();
        this.nodes = new NodeSet_1.default();
        this.components = new ComponentSet_1.default();
        this.graph = new Graph_1.default(this, null);
    }
    /**
     * Serializes the content of the system, ready to be stringified.
     */
    deflate() {
        return this.graph.deflate();
    }
    /**
     * Deserializes the given JSON object.
     * @param json
     */
    inflate(json) {
        this.graph.inflate(json);
    }
    toString(verbose = false) {
        const nodes = this.nodes.getArray();
        const numComponents = this.components.count();
        const text = `System - ${nodes.length} nodes, ${numComponents} components.`;
        if (verbose) {
            return text + "\n" + nodes.map(node => node.toString(true)).join("\n");
        }
        return text;
    }
    _addNode(node) {
        this.nodes._add(node);
    }
    _removeNode(node) {
        this.nodes._remove(node);
    }
    _addComponent(component) {
        if (component.isSystemSingleton && this.components.has(component)) {
            throw new Error(`only one component of type '${component.type}' allowed per system`);
        }
        this.components._add(component);
    }
    _removeComponent(component) {
        this.components._remove(component);
    }
}
exports.default = System;


/***/ }),

/***/ "../../libs/ff-graph/source/components/CController.ts":
/*!***********************************************************!*\
  !*** /app/libs/ff-graph/source/components/CController.ts ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Commander_1 = __webpack_require__(/*! @ff/core/Commander */ "../../libs/ff-core/source/Commander.ts");
exports.Commander = Commander_1.default;
const Component_1 = __webpack_require__(/*! ../Component */ "../../libs/ff-graph/source/Component.ts");
exports.types = Component_1.types;
class CController extends Component_1.default {
    createActions(commander) {
        return {};
    }
}
CController.type = "CController";
CController.isSystemSingleton = true;
exports.default = CController;


/***/ }),

/***/ "../../libs/ff-graph/source/components/CGraph.ts":
/*!******************************************************!*\
  !*** /app/libs/ff-graph/source/components/CGraph.ts ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Component_1 = __webpack_require__(/*! ../Component */ "../../libs/ff-graph/source/Component.ts");
const Graph_1 = __webpack_require__(/*! ../Graph */ "../../libs/ff-graph/source/Graph.ts");
////////////////////////////////////////////////////////////////////////////////
class CGraph extends Component_1.default {
    constructor() {
        super(...arguments);
        this._innerGraph = null;
        this._innerRoot = null;
    }
    get innerGraph() {
        return this._innerGraph;
    }
    get innerRoot() {
        return this._innerRoot;
    }
    set innerRoot(root) {
        this._innerRoot = root;
    }
    create() {
        this._innerGraph = new Graph_1.default(this.system, this);
    }
    update(context) {
        // TODO: Evaluate interface ins/outs
        return false;
    }
    tick(context) {
        return this._innerGraph.tick(context);
    }
    finalize(context) {
        return this._innerGraph.finalize(context);
    }
    dispose() {
        this._innerGraph.clear();
        this._innerGraph = null;
        this._innerRoot = null;
        super.dispose();
    }
    inflate(json) {
        super.inflate(json);
        this._innerGraph.inflate(json.graph);
    }
    deflate() {
        const json = super.deflate();
        json.graph = this._innerGraph.deflate();
        return json;
    }
}
CGraph.type = "CGraph";
exports.default = CGraph;


/***/ }),

/***/ "../../libs/ff-graph/source/components/CHierarchy.ts":
/*!**********************************************************!*\
  !*** /app/libs/ff-graph/source/components/CHierarchy.ts ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Component_1 = __webpack_require__(/*! ../Component */ "../../libs/ff-graph/source/Component.ts");
const Node_1 = __webpack_require__(/*! ../Node */ "../../libs/ff-graph/source/Node.ts");
exports.Node = Node_1.default;
const _getChildComponent = (hierarchy, componentOrType, recursive) => {
    let component;
    const children = hierarchy.children;
    for (let i = 0, n = children.length; i < n; ++i) {
        component = children[i].components.get(componentOrType);
        if (component) {
            return component;
        }
    }
    if (recursive) {
        for (let i = 0, n = children.length; i < n; ++i) {
            component = _getChildComponent(children[i], componentOrType, true);
            if (component) {
                return component;
            }
        }
    }
    return null;
};
const _getChildComponents = (hierarchy, componentOrType, recursive) => {
    let components = [];
    const children = hierarchy.children;
    for (let i = 0, n = children.length; i < n; ++i) {
        components = components.concat(children[i].components.getArray(componentOrType));
    }
    if (recursive) {
        for (let i = 0, n = children.length; i < n; ++i) {
            components = components.concat(_getChildComponents(children[i], componentOrType, true));
        }
    }
    return components;
};
/**
 * Allows arranging components in a hierarchical structure.
 *
 * ### Events
 * - *"change"* - emits [[IHierarchyChangeEvent]] after the instance's state has changed.
 */
class CHierarchy extends Component_1.default {
    constructor() {
        super(...arguments);
        this._parent = null;
        this._children = [];
    }
    /**
     * Returns the parent component of this.
     * @returns {CHierarchy}
     */
    get parent() {
        return this._parent;
    }
    /**
     * Returns an array of child components of this.
     * @returns {Readonly<CHierarchy[]>}
     */
    get children() {
        return this._children || [];
    }
    dispose() {
        // detach this from its parent
        if (this._parent) {
            this._parent.removeChild(this);
        }
        // dispose of children
        this._children.slice().forEach(child => child.node.dispose());
        super.dispose();
    }
    /**
     * Returns a component at the root of the hierarchy.
     * @returns A component of the given type that is a sibling of the root hierarchy component.
     */
    getRoot(componentOrType) {
        let root = this;
        while (root._parent) {
            root = root._parent;
        }
        return root ? root.node.components.get(componentOrType) : null;
    }
    /**
     * Returns a component from the parent node of the node of this component.
     * @param componentOrType
     * @param recursive If true, extends search to entire chain of ancestors,
     * including parent graphs.
     */
    getParent(componentOrType, recursive) {
        let parent = this._parent;
        if (!parent) {
            return null;
        }
        let component = parent.node.components.get(componentOrType);
        if (component) {
            return component;
        }
        if (recursive) {
            parent = parent._parent;
            // if at root, continue search at parent graph
            if (!parent) {
                const parentGraphComponent = this.graph.parent;
                parent = parentGraphComponent ? parentGraphComponent.hierarchy : null;
            }
            while (parent) {
                component = parent.node.components.get(componentOrType);
                if (component) {
                    return component;
                }
            }
        }
        return null;
    }
    /**
     * Returns the child component of the given type.
     * @param componentOrType
     * @param recursive If true, extends search to entire subtree (breadth-first).
     */
    getChild(componentOrType, recursive) {
        return _getChildComponent(this, componentOrType, recursive);
    }
    /**
     * Returns all child components of the given type.
     * @param componentOrType
     * @param recursive If true, extends search to entire subtree (breadth-first).
     */
    getChildren(componentOrType, recursive) {
        return _getChildComponents(this, componentOrType, recursive);
    }
    /**
     * Returns true if there is a child component of the given type.
     * @param componentOrType
     * @param recursive If true, extends search to entire subtree (breadth-first).
     */
    hasChildren(componentOrType, recursive) {
        return !!_getChildComponent(this, componentOrType, recursive);
    }
    /**
     * Adds another hierarchy component as a child to this component.
     * Emits a hierarchy event at this component, its node and all their parents.
     * @param {CHierarchy} component
     */
    addChild(component) {
        if (component._parent) {
            throw new Error("can't add as child: component already has a parent");
        }
        if (component === this.graph.root) {
            throw new Error("can't add as child: component is root of graph");
        }
        component._parent = this;
        this._children.push(component);
        const event = {
            type: "hierarchy", add: true, remove: false, parent: this, child: component
        };
        while (component) {
            component.emit(event);
            component.node.emit(event);
            component = component._parent;
        }
        this.graph.emit(event);
        this.system.emit(event);
    }
    /**
     * Removes a child component from this hierarchy component.
     * Emits a hierarchy event at this component, its node and all their parents.
     * @param {CHierarchy} component
     */
    removeChild(component) {
        if (component._parent !== this) {
            throw new Error("component not a child of this");
        }
        const index = this._children.indexOf(component);
        this._children.splice(index, 1);
        component._parent = null;
        const event = {
            type: "hierarchy", add: false, remove: true, parent: this, child: component
        };
        while (component) {
            component.emit(event);
            component.node.emit(event);
            component = component._parent;
        }
        this.graph.emit(event);
        this.system.emit(event);
    }
    deflate() {
        const json = super.deflate();
        if (this._children.length > 0) {
            json.children = this._children.map(child => child.id);
        }
        return json;
    }
    inflateReferences(json) {
        super.inflateReferences(json);
        const dict = this.system.components.getDictionary();
        if (json.children) {
            json.children.forEach(childId => {
                const child = dict[childId];
                this.addChild(child);
            });
        }
    }
    /**
     * Returns a text representation of this object.
     * @returns {string}
     */
    toString() {
        return super.toString() + ` - children: ${this.children.length}`;
    }
}
CHierarchy.type = "CHierarchy";
exports.default = CHierarchy;


/***/ }),

/***/ "../../libs/ff-graph/source/components/COscillator.ts":
/*!***********************************************************!*\
  !*** /app/libs/ff-graph/source/components/COscillator.ts ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const easing_1 = __webpack_require__(/*! @ff/core/easing */ "../../libs/ff-core/source/easing.ts");
const propertyTypes_1 = __webpack_require__(/*! ../propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const Component_1 = __webpack_require__(/*! ../Component */ "../../libs/ff-graph/source/Component.ts");
////////////////////////////////////////////////////////////////////////////////
const offsetSchema = { preset: 0, min: 0, max: 1, bar: true };
var ETimeBase;
(function (ETimeBase) {
    ETimeBase[ETimeBase["Relative"] = 0] = "Relative";
    ETimeBase[ETimeBase["Absolute"] = 1] = "Absolute";
})(ETimeBase = exports.ETimeBase || (exports.ETimeBase = {}));
var EInterpolationMode;
(function (EInterpolationMode) {
    EInterpolationMode[EInterpolationMode["Forward"] = 0] = "Forward";
    EInterpolationMode[EInterpolationMode["Backward"] = 1] = "Backward";
    EInterpolationMode[EInterpolationMode["Alternate"] = 2] = "Alternate";
})(EInterpolationMode = exports.EInterpolationMode || (exports.EInterpolationMode = {}));
const ins = {
    run: propertyTypes_1.types.Boolean("Control.Run"),
    start: propertyTypes_1.types.Event("Control.Start"),
    pause: propertyTypes_1.types.Event("Control.Pause"),
    stop: propertyTypes_1.types.Event("Control.Stop"),
    min: propertyTypes_1.types.Number("Value.Min"),
    max: propertyTypes_1.types.Number("Value.Max", 1),
    curve: propertyTypes_1.types.Enum("Interpolation.Curve", easing_1.EEasingCurve),
    mode: propertyTypes_1.types.Enum("Interpolation.Mode", EInterpolationMode),
    duration: propertyTypes_1.types.Number("Time.Duration", 1),
    base: propertyTypes_1.types.Enum("Time.Base", ETimeBase),
    offset: propertyTypes_1.types.Number("Time.Offset", offsetSchema),
    repetitions: propertyTypes_1.types.Natural("Time.Repetitions")
};
const outs = {
    value: propertyTypes_1.types.Number("Value"),
    repetition: propertyTypes_1.types.Natural("Repetition"),
    repeat: propertyTypes_1.types.Event("Repeat")
};
class COscillator extends Component_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
        this.outs = this.addOutputs(outs);
        this.lastTime = 0;
        this.lastT = 0;
        this.easingFunction = null;
        this.isAbsolute = false;
        this.isBackward = false;
        this.isAlternate = false;
    }
    update(pulse) {
        const { ins, outs } = this;
        if (ins.curve.changed) {
            this.easingFunction = easing_1.getEasingFunction(ins.curve.getValidatedValue());
        }
        if (ins.mode.changed) {
            this.isBackward = ins.mode.value === EInterpolationMode.Backward;
            this.isAlternate = ins.mode.value === EInterpolationMode.Alternate;
        }
        if (ins.base.changed) {
            this.isAbsolute = ins.base.value === ETimeBase.Absolute;
        }
        if (ins.start.changed) {
            this.lastTime = 0;
            ins.run.setValue(true);
            outs.repetition.setValue(0);
        }
        else if (ins.pause.changed) {
            if (ins.run.value) {
                ins.run.setValue(false);
            }
            else if (ins.repetitions.value <= 0 || outs.repetition.value < ins.repetitions.value) {
                ins.run.setValue(true);
            }
        }
        else if (ins.stop.changed) {
            this.lastTime = 0;
            ins.run.setValue(false);
            outs.value.setValue(this.isBackward ? ins.max.value : ins.min.value);
            outs.repetition.setValue(0);
        }
        if (ins.run.changed && ins.run.value) {
            this.lastT = 0;
            if (ins.repetitions.value > 0 && outs.repetition.value >= ins.repetitions.value) {
                outs.repetition.setValue(0);
            }
        }
        return false;
    }
    tick(pulse) {
        const { ins, outs } = this;
        if (ins.run.value) {
            const duration = ins.duration.value;
            if (duration === 0) {
                return false;
            }
            // absolute/relative base
            let t;
            if (this.isAbsolute) {
                t = pulse.secondsElapsed / duration;
            }
            else {
                t = this.lastTime = this.lastTime + pulse.secondsDelta / duration;
            }
            // modulo cycle
            t = t < 0 ? 1 - (-t % 1) : t % 1;
            // repetitions
            if (t < this.lastT) {
                const repetition = outs.repetition.value + 1;
                outs.repetition.setValue(repetition);
                outs.repeat.set();
                if (ins.repetitions.value > 0 && repetition >= ins.repetitions.value) {
                    ins.run.setValue(false);
                    t = 1;
                }
            }
            this.lastT = t;
            // offset
            t = t + ins.offset.value;
            t = t > 1 ? t % 1 : t;
            // alternate, easing curve
            t = this.isAlternate ? (t > 0.5 ? 1 - t : t) * 2 : (this.isBackward ? 1 - t : t);
            const v = ins.min.value + this.easingFunction(t) * (ins.max.value - ins.min.value);
            outs.value.setValue(v);
            return true;
        }
        return false;
    }
}
COscillator.type = "COscillator";
exports.default = COscillator;


/***/ }),

/***/ "../../libs/ff-graph/source/components/CPulse.ts":
/*!******************************************************!*\
  !*** /app/libs/ff-graph/source/components/CPulse.ts ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Component_1 = __webpack_require__(/*! ../Component */ "../../libs/ff-graph/source/Component.ts");
class CPulse extends Component_1.default {
    constructor(id) {
        super(id);
        this.addEvent("pulse");
        this.onAnimationFrame = this.onAnimationFrame.bind(this);
        this.context = {
            time: new Date(),
            secondsElapsed: 0,
            secondsDelta: 0,
            frameNumber: 0
        };
        this._secondsStarted = Date.now() * 0.001;
        this._secondsStopped = this._secondsStarted;
        this._animHandler = 0;
        this._pulseEvent = { type: "pulse", context: this.context };
    }
    start() {
        if (this._animHandler === 0) {
            if (this._secondsStopped > 0) {
                this._secondsStarted += (Date.now() * 0.001 - this._secondsStopped);
                this._secondsStopped = 0;
            }
            this._animHandler = window.requestAnimationFrame(this.onAnimationFrame);
        }
    }
    stop() {
        if (this._animHandler !== 0) {
            if (this._secondsStopped === 0) {
                this._secondsStopped = Date.now() * 0.001;
            }
            window.cancelAnimationFrame(this._animHandler);
            this._animHandler = 0;
        }
    }
    // reset()
    // {
    //     const context = this.context;
    //     context.time = new Date();
    //     context.secondsElapsed = 0;
    //     context.secondsDelta = 0;
    //     context.frameNumber = 0;
    //
    //     this._secondsStarted = Date.now() * 0.001;
    //     this._secondsStopped = this._secondsStarted;
    // }
    pulse(milliseconds) {
        const context = this.context;
        context.time.setTime(milliseconds);
        const elapsed = milliseconds * 0.001 - this._secondsStarted;
        context.secondsDelta = elapsed - context.secondsElapsed;
        context.secondsElapsed = elapsed;
        context.frameNumber++;
        this.system.graph.tick(this.context);
        this.emit(this._pulseEvent);
        this.system.graph.finalize(this.context);
    }
    onAnimationFrame() {
        this.pulse(Date.now());
        this._animHandler = window.requestAnimationFrame(this.onAnimationFrame);
    }
}
CPulse.type = "CPulse";
CPulse.isSystemSingleton = true;
exports.default = CPulse;


/***/ }),

/***/ "../../libs/ff-graph/source/components/CSelection.ts":
/*!**********************************************************!*\
  !*** /app/libs/ff-graph/source/components/CSelection.ts ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const propertyTypes_1 = __webpack_require__(/*! ../propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const Component_1 = __webpack_require__(/*! ../Component */ "../../libs/ff-graph/source/Component.ts");
const ComponentSet_1 = __webpack_require__(/*! ../ComponentSet */ "../../libs/ff-graph/source/ComponentSet.ts");
const Node_1 = __webpack_require__(/*! ../Node */ "../../libs/ff-graph/source/Node.ts");
const NodeSet_1 = __webpack_require__(/*! ../NodeSet */ "../../libs/ff-graph/source/NodeSet.ts");
const CGraph_1 = __webpack_require__(/*! ./CGraph */ "../../libs/ff-graph/source/components/CGraph.ts");
const CController_1 = __webpack_require__(/*! ./CController */ "../../libs/ff-graph/source/components/CController.ts");
const outs = {
    selNodeCount: propertyTypes_1.types.Integer("Selection.Nodes"),
    selComponentCount: propertyTypes_1.types.Integer("Selection.Components")
};
class CSelection extends CController_1.default {
    constructor(id) {
        super(id);
        this.outs = this.addOutputs(outs);
        this.multiSelect = false;
        this.exclusiveSelect = true;
        this.selectedNodes = new NodeSet_1.default();
        this.selectedComponents = new ComponentSet_1.default();
        this._activeGraph = null;
        this.addEvents("select-node", "select-component", "active-graph", "update");
        this.selectedNodes.on(Node_1.default, e => this.onSelectNode(e.node, e.add));
        this.selectedComponents.on(Component_1.default, e => this.onSelectComponent(e.component, e.add));
    }
    get activeGraph() {
        return this._activeGraph;
    }
    set activeGraph(graph) {
        if (graph !== this.activeGraph) {
            this.clearSelection();
            const previous = this._activeGraph;
            this._activeGraph = graph;
            this.onActiveGraph(graph);
            this.emit({ type: "active-graph", previous, next: graph });
        }
    }
    hasParentGraph() {
        return this._activeGraph && this._activeGraph.parent;
    }
    activateParentGraph() {
        if (this._activeGraph && this._activeGraph.parent.graph) {
            this.activeGraph = this._activeGraph.parent.graph;
        }
    }
    hasChildGraph() {
        return this.selectedComponents.has(CGraph_1.default);
    }
    activateChildGraph() {
        const graphComponent = this.selectedComponents.get(CGraph_1.default);
        if (graphComponent) {
            this.activeGraph = graphComponent.innerGraph;
        }
    }
    create() {
        super.create();
        this._activeGraph = this.system.graph;
        this.system.nodes.on(Node_1.default, this.onSystemNode, this);
        this.system.components.on(Component_1.default, this.onSystemComponent, this);
    }
    dispose() {
        this.system.nodes.off(Node_1.default, this.onSystemNode, this);
        this.system.components.off(Component_1.default, this.onSystemComponent, this);
        super.dispose();
    }
    createActions(commander) {
        return {
            selectNode: commander.register({
                name: "Select Node", do: this.selectNode, target: this
            }),
            selectComponent: commander.register({
                name: "Select Component", do: this.selectComponent, target: this
            }),
            clearSelection: commander.register({
                name: "Clear Selection", do: this.clearSelection, target: this
            })
        };
    }
    nodeContainsSelectedComponent(node) {
        const components = node.components.getArray();
        for (let i = 0, n = components.length; i < n; ++i) {
            if (this.selectedComponents.contains(components[i])) {
                return true;
            }
        }
        return false;
    }
    selectNode(node, toggle = false) {
        this.activeGraph = node.graph;
        const selectedNodes = this.selectedNodes;
        const multiSelect = this.multiSelect && toggle;
        if (node && selectedNodes.contains(node)) {
            if (multiSelect) {
                selectedNodes._remove(node);
            }
        }
        else {
            if (this.exclusiveSelect) {
                this.selectedComponents._clear();
            }
            if (!multiSelect) {
                selectedNodes._clear();
            }
            if (node) {
                selectedNodes._add(node);
            }
        }
        this.updateStats();
    }
    selectComponent(component, toggle = false) {
        this.activeGraph = component.graph;
        const selectedComponents = this.selectedComponents;
        const multiSelect = this.multiSelect && toggle;
        if (component && selectedComponents.contains(component)) {
            if (multiSelect) {
                selectedComponents._remove(component);
            }
        }
        else {
            if (this.exclusiveSelect) {
                this.selectedNodes._clear();
            }
            if (!multiSelect) {
                selectedComponents._clear();
            }
            if (component) {
                selectedComponents._add(component);
            }
        }
        this.updateStats();
    }
    clearSelection() {
        this.selectedNodes._clear();
        this.selectedComponents._clear();
        this.updateStats();
    }
    onSelectNode(node, selected) {
    }
    onSelectComponent(component, selected) {
    }
    onActiveGraph(graph) {
    }
    onSystemNode(event) {
        if (event.remove && this.selectedNodes.contains(event.node)) {
            this.selectedNodes._remove(event.node);
        }
    }
    onSystemComponent(event) {
        if (event.remove && this.selectedComponents.contains(event.component)) {
            this.selectedComponents._remove(event.component);
        }
    }
    updateStats() {
        const outs = this.outs;
        outs.selNodeCount.setValue(this.selectedNodes.length);
        outs.selComponentCount.setValue(this.selectedComponents.length);
    }
}
CSelection.type = "CSelection";
exports.default = CSelection;


/***/ }),

/***/ "../../libs/ff-graph/source/components/index.ts":
/*!*****************************************************!*\
  !*** /app/libs/ff-graph/source/components/index.ts ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const CController_1 = __webpack_require__(/*! ./CController */ "../../libs/ff-graph/source/components/CController.ts");
exports.CController = CController_1.default;
const CGraph_1 = __webpack_require__(/*! ./CGraph */ "../../libs/ff-graph/source/components/CGraph.ts");
exports.CGraph = CGraph_1.default;
const CHierarchy_1 = __webpack_require__(/*! ./CHierarchy */ "../../libs/ff-graph/source/components/CHierarchy.ts");
exports.CHierarchy = CHierarchy_1.default;
const COscillator_1 = __webpack_require__(/*! ./COscillator */ "../../libs/ff-graph/source/components/COscillator.ts");
exports.COscillator = COscillator_1.default;
const CPulse_1 = __webpack_require__(/*! ./CPulse */ "../../libs/ff-graph/source/components/CPulse.ts");
exports.CPulse = CPulse_1.default;
const CSelection_1 = __webpack_require__(/*! ./CSelection */ "../../libs/ff-graph/source/components/CSelection.ts");
exports.CSelection = CSelection_1.default;
exports.componentTypes = [
    CGraph_1.default,
    CHierarchy_1.default,
    COscillator_1.default,
    CPulse_1.default,
    CSelection_1.default
];


/***/ }),

/***/ "../../libs/ff-graph/source/convert.ts":
/*!********************************************!*\
  !*** /app/libs/ff-graph/source/convert.ts ***!
  \********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const _identity = [
    function (srcVal) {
        return srcVal;
    },
    function (srcVal, dstVal) {
        for (let i = 0, n = dstVal.length; i < n; ++i) {
            dstVal[i] = srcVal[i];
        }
        return dstVal;
    }
];
const _toBoolean = [
    function (srcVal) {
        return !!srcVal;
    },
    function (srcVal, dstVal) {
        for (let i = 0, n = dstVal.length; i < n; ++i) {
            dstVal[i] = !!srcVal[i];
        }
        return dstVal;
    }
];
const _toString = [
    function (srcVal) {
        return String(srcVal);
    },
    function (srcVal, dstVal) {
        for (let i = 0, n = dstVal.length; i < n; ++i) {
            dstVal[i] = String(srcVal[i]);
        }
        return dstVal;
    }
];
const _parseFloat = [
    function (srcVal) {
        return parseFloat(srcVal) || 0;
    },
    function (srcVal, dstVal) {
        for (let i = 0, n = dstVal.length; i < n; ++i) {
            dstVal[i] = parseFloat(srcVal[i]) || 0;
        }
        return dstVal;
    }
];
const _booleanToNumber = [
    function (srcVal) {
        return srcVal ? 1 : 0;
    },
    function (srcVal, dstVal) {
        for (let i = 0, n = dstVal.length; i < n; ++i) {
            dstVal[i] = srcVal[i] ? 1 : 0;
        }
        return dstVal;
    }
];
const _illegalThrow = [
    function (srcVal, dstVal) {
        throw new Error(`illegal value conversion from ${typeof srcVal} to ${typeof dstVal}`);
    },
    function (srcVal, dstVal, elements) {
        throw new Error(`illegal array conversion from ${typeof srcVal[0]} to ${typeof dstVal[0]}`);
    }
];
const _conversionFunctions = {
    "number": {
        "number": _identity,
        "boolean": _toBoolean,
        "string": _toString,
        "object": _illegalThrow
    },
    "boolean": {
        "number": _booleanToNumber,
        "boolean": _identity,
        "string": _toString,
        "object": _illegalThrow
    },
    "string": {
        "number": _parseFloat,
        "boolean": _toBoolean,
        "string": _identity,
        "object": _illegalThrow
    },
    "object": {
        "number": _illegalThrow,
        "boolean": _toBoolean,
        "string": _toString,
        "object": _identity
    }
};
const _conversionTable = {
    "number": {
        "number": true,
        "boolean": true,
        "string": true,
        "object": false
    },
    "boolean": {
        "number": true,
        "boolean": true,
        "string": true,
        "object": false
    },
    "string": {
        "number": true,
        "boolean": true,
        "string": true,
        "object": false
    },
    "object": {
        "number": false,
        "boolean": true,
        "string": true,
        "object": true
    }
};
const _copyFunctions = [
    [
        function (srcVal, dstVal, fnConvert) {
            return fnConvert(srcVal, dstVal); // value > value
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[0] = fnConvert(srcVal); // value > [0]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[1] = fnConvert(srcVal); // value > [1]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[2] = fnConvert(srcVal); // value > [2]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[3] = fnConvert(srcVal); // value > [3]
            return dstVal;
        }
    ],
    [
        function (srcVal, dstVal, fnConvert) {
            return fnConvert(srcVal[0]); // [0] > value
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[0] = fnConvert(srcVal[0]); // [0] > [0]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[1] = fnConvert(srcVal[0]); // [0] > [1]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[2] = fnConvert(srcVal[0]); // [0] > [2]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[3] = fnConvert(srcVal[0]); // [0] > [3]
            return dstVal;
        }
    ],
    [
        function (srcVal, dstVal, fnConvert) {
            return fnConvert(srcVal[1]); // [1] > value
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[0] = fnConvert(srcVal[1]); // [1] > [0]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[1] = fnConvert(srcVal[1]); // [1] > [1]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[2] = fnConvert(srcVal[1]); // [1] > [2]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[3] = fnConvert(srcVal[1]); // [1] > [3]
            return dstVal;
        }
    ],
    [
        function (srcVal, dstVal, fnConvert) {
            return fnConvert(srcVal[2]); // [2] > value
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[0] = fnConvert(srcVal[2]); // [2] > [0]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[1] = fnConvert(srcVal[2]); // [2] > [1]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[2] = fnConvert(srcVal[2]); // [2] > [2]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[3] = fnConvert(srcVal[2]); // [2] > [3]
            return dstVal;
        }
    ],
    [
        function (srcVal, dstVal, fnConvert) {
            return fnConvert(srcVal[3]); // [3] > value
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[0] = fnConvert(srcVal[3]); // [3] > [0]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[1] = fnConvert(srcVal[3]); // [3] > [1]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[2] = fnConvert(srcVal[3]); // [3] > [2]
            return dstVal;
        },
        function (srcVal, dstVal, fnConvert) {
            dstVal[3] = fnConvert(srcVal[3]); // [3] > [3]
            return dstVal;
        }
    ]
];
function getConversionFunction(sourceType, destinationType, isArray) {
    const index = isArray ? 1 : 0;
    return _conversionFunctions[sourceType][destinationType][index];
}
exports.getConversionFunction = getConversionFunction;
function canConvert(sourceType, destinationType) {
    return _conversionTable[sourceType][destinationType];
}
exports.canConvert = canConvert;
function getElementCopyFunction(sourceIndex, destinationIndex, fnConvert) {
    if (sourceIndex === -1 && destinationIndex === -1) {
        return fnConvert;
    }
    if (sourceIndex <= 3 && destinationIndex <= 3) {
        return _copyFunctions[sourceIndex + 1][destinationIndex + 1];
    }
    return function (srcVal, dstVal, fnConvert) {
        dstVal[destinationIndex] = fnConvert(srcVal[sourceIndex]);
        return dstVal;
    };
}
exports.getElementCopyFunction = getElementCopyFunction;
function getMultiCopyFunction(sourceIsMulti, destinationIsMulti, fnCopy) {
    if (sourceIsMulti === false) {
        if (destinationIsMulti === false) {
            // single > single
            return fnCopy;
        }
        else {
            // single > multi
            return function (srcVal, dstVal, fnConvert) {
                for (let i = 0, n = dstVal.length; i < n; ++i) {
                    dstVal[i] = fnCopy(srcVal, dstVal[i]);
                }
                return dstVal;
            };
        }
    }
    else {
        if (destinationIsMulti === false) {
            // multi > single
            return function (srcVal, dstVal, fnConvert) {
                if (srcVal.length > 0) {
                    dstVal = fnCopy(srcVal[0], dstVal);
                }
                return dstVal;
            };
        }
        else {
            // multi > multi
            return function (srcVal, dstVal, fnConvert) {
                for (let i = 0, m = srcVal.length, n = dstVal.length; i < n; ++i) {
                    dstVal[i] = fnCopy(srcVal[i % m], dstVal[i]);
                }
                return dstVal;
            };
        }
    }
}
exports.getMultiCopyFunction = getMultiCopyFunction;


/***/ }),

/***/ "../../libs/ff-graph/source/propertyTypes.ts":
/*!**************************************************!*\
  !*** /app/libs/ff-graph/source/propertyTypes.ts ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const types_1 = __webpack_require__(/*! @ff/core/types */ "../../libs/ff-core/source/types.ts");
exports.labels = {
    xyzw: ["X", "Y", "Z", "W"],
    rgba: ["R", "G", "B", "A"],
};
const parseProps = function (props) {
    if (props === undefined || (typeof props === "object" && !Array.isArray(props))) {
        return props;
    }
    return { preset: props };
};
exports.makeType = function (schema, path, props) {
    props = parseProps(props);
    return { path, schema: props ? Object.assign({}, schema, props) : schema };
};
exports.makeEnumType = function (enumeration, path, props) {
    props = parseProps(props);
    const schema = { enum: enumeration, options: types_1.enumToArray(enumeration), preset: 0 };
    return { path, schema: props ? Object.assign({}, schema, props) : schema };
};
exports.makeOptionType = function (options, path, props) {
    props = parseProps(props);
    const schema = { options, preset: 0 };
    return { path, schema: props ? Object.assign({}, schema, props) : schema };
};
exports.makeObjectType = function (type, path, props) {
    props = parseProps(props);
    const schema = { preset: null, objectType: type };
    return { path, schema: props ? Object.assign({}, schema, props) : schema };
};
exports.schemas = {
    Number: { preset: 0 },
    Integer: { preset: 0, step: 1, speed: 0.34, precision: 0 },
    Natural: { preset: 0, step: 1, speed: 0.34, precision: 0, min: 0 },
    Vector2: { preset: [0, 0] },
    Vector3: { preset: [0, 0, 0] },
    Vector4: { preset: [0, 0, 0, 0] },
    Matrix2: { preset: [1, 0, 0, 1] },
    Matrix3: { preset: [1, 0, 0, 0, 1, 0, 0, 0, 1] },
    Matrix4: { preset: [1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1] },
    Scale2: { preset: [1, 1] },
    Scale3: { preset: [1, 1, 1] },
    IntVec2: { preset: [0, 0], step: 1, speed: 0.34, precision: 0 },
    IntVec3: { preset: [0, 0, 0], step: 1, speed: 0.34, precision: 0 },
    ColorRGB: { preset: [1, 1, 1], semantic: "color", labels: exports.labels.rgba, min: 0, max: 1, bar: true },
    ColorRGBA: { preset: [1, 1, 1, 1], semantic: "color", labels: exports.labels.rgba, min: 0, max: 1, bar: true },
    Boolean: { preset: false },
    String: { preset: "" },
    Object: { preset: null, objectType: Object },
    Event: { preset: 0, event: true }
};
exports.types = {
    Property: (path, props) => exports.makeType(undefined, path, props),
    Number: (path, props) => exports.makeType(exports.schemas.Number, path, props),
    Integer: (path, props) => exports.makeType(exports.schemas.Integer, path, props),
    Natural: (path, props) => exports.makeType(exports.schemas.Natural, path, props),
    Vector2: (path, props) => exports.makeType(exports.schemas.Vector2, path, props),
    Vector3: (path, props) => exports.makeType(exports.schemas.Vector3, path, props),
    Vector4: (path, props) => exports.makeType(exports.schemas.Vector4, path, props),
    IntVec2: (path, props) => exports.makeType(exports.schemas.IntVec2, path, props),
    IntVec3: (path, props) => exports.makeType(exports.schemas.IntVec3, path, props),
    Matrix2: (path, props) => exports.makeType(exports.schemas.Matrix2, path, props),
    Matrix3: (path, props) => exports.makeType(exports.schemas.Matrix3, path, props),
    Matrix4: (path, props) => exports.makeType(exports.schemas.Matrix4, path, props),
    Scale2: (path, props) => exports.makeType(exports.schemas.Scale2, path, props),
    Scale3: (path, props) => exports.makeType(exports.schemas.Scale3, path, props),
    ColorRGB: (path, props) => exports.makeType(exports.schemas.ColorRGB, path, props),
    ColorRGBA: (path, props) => exports.makeType(exports.schemas.ColorRGBA, path, props),
    Boolean: (path, props) => exports.makeType(exports.schemas.Boolean, path, props),
    String: (path, props) => exports.makeType(exports.schemas.String, path, props),
    Enum: (path, enumeration, props) => exports.makeEnumType(enumeration, path, props),
    Option: (path, options, props) => exports.makeOptionType(options, path, props),
    Object: (path, type, props) => exports.makeObjectType(type, path, props),
    Event: (path, props) => exports.makeType(exports.schemas.Event, path, props)
};


/***/ }),

/***/ "../../libs/ff-scene/source/RenderQuadView.ts":
/*!***************************************************!*\
  !*** /app/libs/ff-scene/source/RenderQuadView.ts ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const UniversalCamera_1 = __webpack_require__(/*! @ff/three/UniversalCamera */ "../../libs/ff-three/source/UniversalCamera.ts");
const RenderView_1 = __webpack_require__(/*! ./RenderView */ "../../libs/ff-scene/source/RenderView.ts");
var EQuadViewLayout;
(function (EQuadViewLayout) {
    EQuadViewLayout[EQuadViewLayout["Single"] = 0] = "Single";
    EQuadViewLayout[EQuadViewLayout["HorizontalSplit"] = 1] = "HorizontalSplit";
    EQuadViewLayout[EQuadViewLayout["VerticalSplit"] = 2] = "VerticalSplit";
    EQuadViewLayout[EQuadViewLayout["Quad"] = 3] = "Quad";
})(EQuadViewLayout = exports.EQuadViewLayout || (exports.EQuadViewLayout = {}));
class RenderQuadView extends RenderView_1.default {
    constructor(system, canvas, overlay) {
        super(system, canvas, overlay);
        this._horizontalSplit = 0.5;
        this._verticalSplit = 0.5;
        this._layout = EQuadViewLayout.Quad;
        this.addEvent("layout");
        this.addViewports(4);
        this.viewports[1].setBuiltInCamera(UniversalCamera_1.EProjection.Orthographic, UniversalCamera_1.EViewPreset.Top);
        this.viewports[1].enableCameraManip(true).orientationEnabled = false;
        this.viewports[2].setBuiltInCamera(UniversalCamera_1.EProjection.Orthographic, UniversalCamera_1.EViewPreset.Left);
        this.viewports[2].enableCameraManip(true).orientationEnabled = false;
        this.viewports[3].setBuiltInCamera(UniversalCamera_1.EProjection.Orthographic, UniversalCamera_1.EViewPreset.Front);
        this.viewports[3].enableCameraManip(true).orientationEnabled = false;
        this.layout = EQuadViewLayout.Single;
    }
    set layout(layout) {
        if (this._layout !== layout) {
            this._layout = layout;
            this.updateConfiguration();
            this.emit({ type: "layout", layout });
        }
    }
    get layout() {
        return this._layout;
    }
    set horizontalSplit(value) {
        this._horizontalSplit = value;
        this.updateSplitPositions();
    }
    get horizontalSplit() {
        return this._horizontalSplit;
    }
    set verticalSplit(value) {
        this._verticalSplit = value;
        this.updateSplitPositions();
    }
    get verticalSplit() {
        return this._verticalSplit;
    }
    updateConfiguration() {
        this.updateSplitPositions();
        this.viewports[0].enabled = true;
        switch (this._layout) {
            case EQuadViewLayout.Single:
                this.viewports[1].enabled = false;
                this.viewports[2].enabled = false;
                this.viewports[3].enabled = false;
                break;
            case EQuadViewLayout.HorizontalSplit:
            case EQuadViewLayout.VerticalSplit:
                this.viewports[1].enabled = true;
                this.viewports[2].enabled = false;
                this.viewports[3].enabled = false;
                break;
            case EQuadViewLayout.Quad:
                this.viewports[1].enabled = true;
                this.viewports[2].enabled = true;
                this.viewports[3].enabled = true;
                break;
        }
    }
    updateSplitPositions() {
        const h = this._horizontalSplit;
        const v = this._verticalSplit;
        switch (this._layout) {
            case EQuadViewLayout.Single:
                this.viewports[0].setSize(0, 0, 1, 1);
                break;
            case EQuadViewLayout.HorizontalSplit:
                this.viewports[0].setSize(0, 0, h, 1);
                this.viewports[1].setSize(h, 0, 1 - h, 1);
                break;
            case EQuadViewLayout.VerticalSplit:
                this.viewports[0].setSize(0, 0, 1, v);
                this.viewports[1].setSize(0, v, 1, 1 - v);
                break;
            case EQuadViewLayout.Quad:
                this.viewports[0].setSize(0, 0, h, v);
                this.viewports[1].setSize(h, 0, 1 - h, v);
                this.viewports[2].setSize(0, v, h, 1 - v);
                this.viewports[3].setSize(h, v, 1 - h, 1 - v);
                break;
        }
    }
}
exports.default = RenderQuadView;


/***/ }),

/***/ "../../libs/ff-scene/source/RenderView.ts":
/*!***********************************************!*\
  !*** /app/libs/ff-scene/source/RenderView.ts ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const Publisher_1 = __webpack_require__(/*! @ff/core/Publisher */ "../../libs/ff-core/source/Publisher.ts");
const GPUPicker_1 = __webpack_require__(/*! @ff/three/GPUPicker */ "../../libs/ff-three/source/GPUPicker.ts");
const Viewport_1 = __webpack_require__(/*! @ff/three/Viewport */ "../../libs/ff-three/source/Viewport.ts");
exports.Viewport = Viewport_1.default;
const CRenderer_1 = __webpack_require__(/*! ./components/CRenderer */ "../../libs/ff-scene/source/components/CRenderer.ts");
class RenderView extends Publisher_1.default {
    constructor(system, canvas, overlay) {
        super();
        this.viewports = [];
        this.rendererComponent = null;
        this.activeViewport = null;
        this.activeObject3D = null;
        this.activeComponent = null;
        this.shouldResize = false;
        this.system = system;
        this.canvas = canvas;
        this.overlay = overlay;
        this.renderer = new THREE.WebGLRenderer({
            canvas,
            antialias: true
        });
        this.renderer.autoClear = false;
        this.renderer.setClearColor("#0090c0");
        this.picker = new GPUPicker_1.default(this.renderer);
    }
    dispose() {
        this.renderer.dispose();
    }
    get canvasWidth() {
        return this.canvas.width;
    }
    get canvasHeight() {
        return this.canvas.height;
    }
    attach() {
        const width = this.canvasWidth;
        const height = this.canvasHeight;
        this.viewports.forEach(viewport => viewport.setCanvasSize(width, height));
        this.renderer.setSize(width, height, false);
        this.rendererComponent = this.system.components.safeGet(CRenderer_1.default);
        this.rendererComponent.attachView(this);
    }
    detach() {
        this.rendererComponent = this.system.components.safeGet(CRenderer_1.default);
        this.rendererComponent.detachView(this);
        this.rendererComponent = null;
    }
    renderImage(width, height, format, quality) {
        console.log("RenderView.renderImage - width: %s, height: %s, format: %s, quality: %s", width, height, format, quality);
        const canvasWidth = this.canvas.width;
        const canvasHeight = this.canvas.height;
        this.setRenderSize(width, height);
        this.render();
        const dataURL = this.canvas.toDataURL(format, quality);
        this.setRenderSize(canvasWidth, canvasHeight);
        return dataURL;
    }
    render() {
        const sceneComponent = this.rendererComponent.activeSceneComponent;
        if (!sceneComponent) {
            return;
        }
        const scene = sceneComponent.scene;
        const camera = sceneComponent.activeCamera;
        if (!scene || !camera) {
            return;
        }
        const renderer = this.renderer;
        if (this.shouldResize) {
            this.shouldResize = false;
            this.setRenderSize(this.canvas.clientWidth, this.canvas.clientHeight);
        }
        renderer.clear();
        renderer["__view"] = this;
        const viewports = this.viewports;
        for (let i = 0, n = viewports.length; i < n; ++i) {
            const viewport = viewports[i];
            if (viewport.enabled) {
                renderer["__viewport"] = viewport;
                const currentCamera = viewport.updateCamera(camera);
                viewport.applyViewport(this.renderer);
                renderer.render(scene, currentCamera);
            }
        }
    }
    setRenderSize(width, height) {
        this.canvas.width = width;
        this.canvas.height = height;
        this.viewports.forEach(viewport => viewport.setCanvasSize(width, height));
        this.renderer.setSize(width, height, false);
    }
    resize() {
        this.shouldResize = true;
    }
    addViewport() {
        const viewport = new Viewport_1.default();
        this.viewports.push(viewport);
        return viewport;
    }
    addViewports(count) {
        for (let i = 0; i < count; ++i) {
            this.viewports.push(new Viewport_1.default());
        }
    }
    removeViewport(viewport) {
        const index = this.viewports.indexOf(viewport);
        if (index < 0) {
            throw new Error("viewport not found");
        }
        this.viewports.slice(index, 1);
    }
    enableViewport(index, enabled) {
        this.viewports[index].enabled = enabled;
    }
    getViewportCount() {
        return this.viewports.length;
    }
    onPointer(event) {
        const system = this.system;
        if (!system) {
            return false;
        }
        let doPick = false;
        let doHitTest = false;
        if (event.type === "pointer-hover") {
            doHitTest = true;
        }
        else if (event.isPrimary && event.type === "pointer-down") {
            doHitTest = true;
            doPick = true;
        }
        const viewEvent = this.routeEvent(event, doHitTest, doPick);
        if (viewEvent) {
            if (viewEvent.component) {
                viewEvent.component.propagateUp(viewEvent);
            }
            else {
                this.system.emit(viewEvent);
            }
            if (!viewEvent.stopPropagation) {
                viewEvent.viewport.onPointer(viewEvent);
            }
            return true;
        }
        return false;
    }
    onTrigger(event) {
        const system = this.system;
        if (!system) {
            return false;
        }
        const viewEvent = this.routeEvent(event, true, true);
        if (viewEvent) {
            if (viewEvent.component) {
                viewEvent.component.propagateUp(viewEvent);
            }
            else {
                this.system.emit(viewEvent);
            }
            if (!viewEvent.stopPropagation) {
                viewEvent.viewport.onTrigger(viewEvent);
            }
            return true;
        }
        return false;
    }
    routeEvent(event, doHitTest, doPick) {
        let viewport = this.activeViewport;
        let object3D = this.activeObject3D;
        let component = this.activeComponent;
        // if no active viewport, perform a hit test against all viewports
        if (doHitTest) {
            viewport = null;
            const viewports = this.viewports;
            for (let i = 0, n = viewports.length; i < n; ++i) {
                const vp = viewports[i];
                if (vp.enabled && vp.isPointInside(event.localX, event.localY)) {
                    viewport = vp;
                    break;
                }
            }
        }
        // without an active viewport, return null to cancel the event
        if (!viewport) {
            return null;
        }
        // if we have an active viewport now, augment event with viewport/view information
        const viewEvent = event;
        viewEvent.view = this;
        viewEvent.viewport = viewport;
        viewEvent.deviceX = viewport.getDeviceX(event.localX);
        viewEvent.deviceY = viewport.getDeviceY(event.localY);
        viewEvent.stopPropagation = false;
        // perform 3D pick
        if (doPick) {
            const sceneComponent = this.rendererComponent.activeSceneComponent;
            const scene = sceneComponent && sceneComponent.scene;
            const camera = sceneComponent && sceneComponent.activeCamera;
            object3D = null;
            component = null;
            if (scene && camera) {
                let object3D = this.picker.pickObject(scene, camera, event);
                if (object3D === undefined) {
                    console.log("Pick Index - Background");
                }
                else {
                    while (object3D && !component) {
                        component = object3D.userData["component"];
                        if (!component) {
                            object3D = object3D.parent;
                        }
                    }
                    if (component) {
                        console.log("Pick Index - Component: %s", component.type);
                    }
                    else {
                        console.warn("Pick Index - Object without component");
                    }
                }
            }
        }
        viewEvent.object3D = object3D;
        viewEvent.component = component;
        this.activeViewport = viewport;
        this.activeObject3D = object3D;
        this.activeComponent = component;
        return viewEvent;
    }
}
exports.default = RenderView;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CBasicMaterial.ts":
/*!**************************************************************!*\
  !*** /app/libs/ff-scene/source/components/CBasicMaterial.ts ***!
  \**************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CMaterial_1 = __webpack_require__(/*! ./CMaterial */ "../../libs/ff-scene/source/components/CMaterial.ts");
////////////////////////////////////////////////////////////////////////////////
const ins = {
    color: propertyTypes_1.types.ColorRGB("Color")
};
class CBasicMaterial extends CMaterial_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
    }
    create() {
        this.material = new THREE.MeshBasicMaterial();
    }
    update() {
        const material = this.material;
        const { color } = this.ins;
        material.color.setRGB(color.value[0], color.value[1], color.value[2]);
        return true;
    }
}
CBasicMaterial.type = "CBasicMaterial";
exports.default = CBasicMaterial;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CBox.ts":
/*!****************************************************!*\
  !*** /app/libs/ff-scene/source/components/CBox.ts ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CGeometry_1 = __webpack_require__(/*! ./CGeometry */ "../../libs/ff-scene/source/components/CGeometry.ts");
////////////////////////////////////////////////////////////////////////////////
const ins = {
    size: propertyTypes_1.types.Vector3("Size", [10, 10, 10]),
    segments: propertyTypes_1.types.Vector3("Segments", [1, 1, 1])
};
class CBox extends CGeometry_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
    }
    create() {
        super.create();
        this.on("pointer", this.onPointer, this);
    }
    update() {
        const { size, segments } = this.ins;
        if (size.changed || segments.changed) {
            this.geometry = new THREE.BoxBufferGeometry(size.value[0], size.value[1], size.value[2], segments.value[0], segments.value[1], segments.value[2]);
        }
        return true;
    }
    onPointer(event) {
    }
}
CBox.type = "CBox";
exports.default = CBox;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CCamera.ts":
/*!*******************************************************!*\
  !*** /app/libs/ff-scene/source/components/CCamera.ts ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const UniversalCamera_1 = __webpack_require__(/*! @ff/three/UniversalCamera */ "../../libs/ff-three/source/UniversalCamera.ts");
exports.EProjection = UniversalCamera_1.EProjection;
const CObject3D_1 = __webpack_require__(/*! ./CObject3D */ "../../libs/ff-scene/source/components/CObject3D.ts");
const CScene_1 = __webpack_require__(/*! ./CScene */ "../../libs/ff-scene/source/components/CScene.ts");
const ins = {
    activate: propertyTypes_1.types.Event("Activate"),
    position: propertyTypes_1.types.Vector3("Transform.Position"),
    rotation: propertyTypes_1.types.Vector3("Transform.Rotation"),
    projection: propertyTypes_1.types.Enum("Projection.Type", UniversalCamera_1.EProjection, UniversalCamera_1.EProjection.Perspective),
    fov: propertyTypes_1.types.Number("Projection.FovY", 52),
    size: propertyTypes_1.types.Number("Projection.Size", 20),
    zoom: propertyTypes_1.types.Number("Projection.Zoom", 1),
    near: propertyTypes_1.types.Number("Frustum.ZNear", 0.01),
    far: propertyTypes_1.types.Number("Frustum.ZFar", 10000)
};
class CCamera extends CObject3D_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
    }
    get camera() {
        return this.object3D;
    }
    get scene() {
        return this.graph.components.get(CScene_1.default);
    }
    create() {
        super.create();
        this.object3D = new UniversalCamera_1.default();
        const scene = this.scene;
        if (scene && !scene.activeCameraComponent) {
            scene.activeCameraComponent = this;
        }
    }
    update() {
        const { activate, position, rotation, projection, fov, size, zoom, near, far } = this.ins;
        if (activate.changed) {
            const scene = this.scene;
            if (scene) {
                scene.activeCameraComponent = this;
            }
        }
        const camera = this.camera;
        if (position.changed || rotation.changed) {
            camera.position.fromArray(position.value);
            camera.rotation.fromArray(rotation.value);
            camera.updateMatrix();
        }
        if (projection.changed) {
            camera.setProjection(projection.getValidatedValue());
        }
        camera.fov = fov.value;
        camera.size = size.value;
        camera.zoom = zoom.value;
        camera.near = near.value;
        camera.far = far.value;
        camera.updateProjectionMatrix();
        return true;
    }
    dispose() {
        const scene = this.scene;
        if (scene && scene.activeCameraComponent === this) {
            scene.activeCameraComponent = null;
        }
        super.dispose();
    }
}
CCamera.type = "CCamera";
exports.default = CCamera;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CDirectionalLight.ts":
/*!*****************************************************************!*\
  !*** /app/libs/ff-scene/source/components/CDirectionalLight.ts ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CLight_1 = __webpack_require__(/*! ./CLight */ "../../libs/ff-scene/source/components/CLight.ts");
////////////////////////////////////////////////////////////////////////////////
const ins = {
    position: propertyTypes_1.types.Vector3("Position", [0, 1, 0]),
    target: propertyTypes_1.types.Vector3("Target")
};
class CDirectionalLight extends CLight_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
    }
    get light() {
        return this.object3D;
    }
    create() {
        super.create();
        this.object3D = new THREE.DirectionalLight();
    }
    update() {
        const light = this.light;
        const { color, intensity, position, target } = this.ins;
        light.color.fromArray(color.value);
        light.intensity = intensity.value;
        light.position.fromArray(position.value);
        light.target.position.fromArray(target.value);
        light.updateMatrix();
        return true;
    }
}
CDirectionalLight.type = "CDirectionalLight";
exports.default = CDirectionalLight;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CGeometry.ts":
/*!*********************************************************!*\
  !*** /app/libs/ff-scene/source/components/CGeometry.ts ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Component_1 = __webpack_require__(/*! @ff/graph/Component */ "../../libs/ff-graph/source/Component.ts");
////////////////////////////////////////////////////////////////////////////////
class CGeometry extends Component_1.default {
    constructor(id) {
        super(id);
        this._geometry = null;
        this.addEvent("geometry");
    }
    get geometry() {
        return this._geometry;
    }
    set geometry(value) {
        this._geometry = value;
        this.emit("geometry", value);
    }
}
CGeometry.type = "CGeometry";
exports.default = CGeometry;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CGrid.ts":
/*!*****************************************************!*\
  !*** /app/libs/ff-scene/source/components/CGrid.ts ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const math_1 = __webpack_require__(/*! @ff/core/math */ "../../libs/ff-core/source/math.ts");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CObject3D_1 = __webpack_require__(/*! ./CObject3D */ "../../libs/ff-scene/source/components/CObject3D.ts");
const Grid_1 = __webpack_require__(/*! @ff/three/Grid */ "../../libs/ff-three/source/Grid.ts");
////////////////////////////////////////////////////////////////////////////////
const _vec3a = new THREE.Vector3();
const _vec3b = new THREE.Vector3();
const ins = {
    position: propertyTypes_1.types.Vector3("Transform.Position"),
    rotation: propertyTypes_1.types.Vector3("Transform.Rotation"),
    scale: propertyTypes_1.types.Scale3("Transform.Scale"),
    size: propertyTypes_1.types.Number("Grid.Size", 20),
    mainDivs: propertyTypes_1.types.Number("Grid.Main.Divisions", 2),
    mainColor: propertyTypes_1.types.ColorRGB("Grid.Main.Color", [1, 1, 1]),
    subDivs: propertyTypes_1.types.Number("Grid.Sub.Divisions", 10),
    subColor: propertyTypes_1.types.ColorRGB("Grid.Sub.Color", [0.5, 0.5, 0.5])
};
class CGrid extends CObject3D_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
    }
    update() {
        let grid = this.object3D;
        const { size, mainDivs, mainColor, subDivs, subColor } = this.ins;
        if (size.changed || mainDivs.changed || mainColor.changed || subDivs.changed || subColor.changed) {
            const props = {
                size: size.value,
                mainDivisions: mainDivs.value,
                mainColor: new THREE.Color().fromArray(mainColor.value),
                subDivisions: subDivs.value,
                subColor: new THREE.Color().fromArray(subColor.value)
            };
            const newGrid = this.object3D = new Grid_1.default(props);
            if (grid) {
                newGrid.matrix.copy(grid.matrix);
                newGrid.matrixWorldNeedsUpdate = true;
            }
            grid = newGrid;
        }
        const { position, rotation, scale } = this.ins;
        if (position.changed || rotation.changed || scale.changed) {
            grid.position.fromArray(position.value);
            _vec3a.fromArray(rotation.value).multiplyScalar(math_1.default.DEG2RAD);
            grid.rotation.setFromVector3(_vec3a, "XYZ");
            grid.scale.fromArray(scale.value);
            grid.updateMatrix();
        }
        return true;
    }
}
CGrid.type = "CGrid";
exports.default = CGrid;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CLight.ts":
/*!******************************************************!*\
  !*** /app/libs/ff-scene/source/components/CLight.ts ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CObject3D_1 = __webpack_require__(/*! ./CObject3D */ "../../libs/ff-scene/source/components/CObject3D.ts");
////////////////////////////////////////////////////////////////////////////////
const ins = {
    color: propertyTypes_1.types.ColorRGB("Color"),
    intensity: propertyTypes_1.types.Number("Intensity", 1)
};
class CLight extends CObject3D_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
    }
    get light() {
        return this.object3D;
    }
}
CLight.type = "CLight";
exports.default = CLight;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CMain.ts":
/*!*****************************************************!*\
  !*** /app/libs/ff-scene/source/components/CMain.ts ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Component_1 = __webpack_require__(/*! @ff/graph/Component */ "../../libs/ff-graph/source/Component.ts");
const CScene_1 = __webpack_require__(/*! ./CScene */ "../../libs/ff-scene/source/components/CScene.ts");
const CCamera_1 = __webpack_require__(/*! ./CCamera */ "../../libs/ff-scene/source/components/CCamera.ts");
////////////////////////////////////////////////////////////////////////////////
const ins = {
    scene: Component_1.types.Option("Scene", []),
    camera: Component_1.types.Option("Camera", [])
};
class CMain extends Component_1.default {
    constructor() {
        super(...arguments);
        this.scenes = [];
        this.cameras = [];
        this.selectedScene = null;
        this.selectedCamera = null;
        this.ins = this.addInputs(ins);
    }
    get sceneComponent() {
        return this.selectedScene;
    }
    get cameraComponent() {
        return this.selectedCamera;
    }
    get scene() {
        return this.selectedScene ? this.selectedScene.scene : null;
    }
    get camera() {
        return this.selectedCamera ? this.selectedCamera.camera : null;
    }
    create() {
        this.scenes = this.system.components.cloneArray(CScene_1.default);
        this.system.components.on(CScene_1.default, this.onSceneComponent, this);
        this.cameras = this.system.components.cloneArray(CCamera_1.default);
        this.system.components.on(CCamera_1.default, this.onCameraComponent, this);
        this.updateOptions();
    }
    update() {
        const ins = this.ins;
        if (ins.scene.changed) {
            const index = ins.scene.getValidatedValue();
            this.selectedScene = index >= 0 ? this.scenes[index] : null;
        }
        if (ins.camera.changed) {
            const index = ins.camera.getValidatedValue();
            this.selectedCamera = index >= 0 ? this.cameras[index] : null;
        }
        return true;
    }
    dispose() {
        this.system.components.off(CScene_1.default, this.onSceneComponent, this);
        this.system.components.off(CCamera_1.default, this.onCameraComponent, this);
    }
    onSceneComponent(event) {
        const inScene = this.ins.scene;
        if (event.add) {
            this.scenes.push(event.component);
            this.updateOptions();
        }
        else {
            const index = this.scenes.indexOf(event.component);
            this.scenes.splice(index, 1);
            this.updateOptions();
            if (!inScene.hasInLinks() && index <= inScene.value) {
                inScene.setValue(Math.max(0, inScene.value - 1));
            }
        }
        inScene.set();
    }
    onCameraComponent(event) {
        const inCamera = this.ins.camera;
        if (event.add) {
            this.cameras.push(event.component);
            this.updateOptions();
        }
        else {
            const index = this.cameras.indexOf(event.component);
            this.cameras.splice(index, 1);
            this.updateOptions();
            if (!inCamera.hasInLinks() && index <= inCamera.value) {
                inCamera.setValue(Math.max(0, inCamera.value - 1));
            }
        }
        inCamera.set();
    }
    updateOptions() {
        const { scene, camera } = this.ins;
        if (this.scenes.length > 0) {
            scene.setOptions(this.scenes.map(scene => scene.name || scene.type));
        }
        else {
            scene.setOptions(["N/A"]);
        }
        if (this.cameras.length > 0) {
            camera.setOptions(this.cameras.map(camera => camera.name || camera.type));
        }
        else {
            camera.setOptions(["N/A"]);
        }
    }
}
CMain.type = "CMain";
exports.default = CMain;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CMaterial.ts":
/*!*********************************************************!*\
  !*** /app/libs/ff-scene/source/components/CMaterial.ts ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Component_1 = __webpack_require__(/*! @ff/graph/Component */ "../../libs/ff-graph/source/Component.ts");
////////////////////////////////////////////////////////////////////////////////
class CMaterial extends Component_1.default {
    constructor(id) {
        super(id);
        this._material = null;
        this.addEvent("material");
    }
    get material() {
        return this._material;
    }
    set material(value) {
        this._material = value;
        this.emit("material", value);
    }
}
CMaterial.type = "CMaterial";
exports.default = CMaterial;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CMesh.ts":
/*!*****************************************************!*\
  !*** /app/libs/ff-scene/source/components/CMesh.ts ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const CGeometry_1 = __webpack_require__(/*! ./CGeometry */ "../../libs/ff-scene/source/components/CGeometry.ts");
const CMaterial_1 = __webpack_require__(/*! ./CMaterial */ "../../libs/ff-scene/source/components/CMaterial.ts");
const CObject3D_1 = __webpack_require__(/*! ./CObject3D */ "../../libs/ff-scene/source/components/CObject3D.ts");
////////////////////////////////////////////////////////////////////////////////
class CMesh extends CObject3D_1.default {
    get mesh() {
        return this.object3D;
    }
    create() {
        super.create();
        this.object3D = new THREE.Mesh();
        this.object3D.visible = false;
        this.geometryTracker = this.trackComponent(CGeometry_1.default, component => {
            this.mesh.geometry = component.geometry;
            component.on("geometry", this.updateGeometry, this);
        }, component => {
            this.mesh.geometry = null;
            component.off("geometry", this.updateGeometry, this);
        });
        this.materialTracker = this.trackComponent(CMaterial_1.default, component => {
            this.mesh.material = component.material;
            component.on("material", this.updateMaterial, this);
        }, component => {
            this.mesh.material = null;
            component.off("material", this.updateMaterial, this);
        });
    }
    toString() {
        const geo = this.mesh.geometry;
        const mat = this.mesh.material;
        return `${this.type} - Geometry: '${geo ? geo.type : "N/A"}', Material: '${mat ? mat.type : "N/A"}'`;
    }
    updateGeometry(geometry) {
        this.mesh.geometry = geometry;
        this.mesh.visible = !!(geometry && this.mesh.material);
    }
    updateMaterial(material) {
        this.mesh.material = material;
        this.mesh.visible = !!(this.mesh.geometry && material);
    }
}
CMesh.type = "CMesh";
exports.default = CMesh;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CObject3D.ts":
/*!*********************************************************!*\
  !*** /app/libs/ff-scene/source/components/CObject3D.ts ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Component_1 = __webpack_require__(/*! @ff/graph/Component */ "../../libs/ff-graph/source/Component.ts");
const CTransform_1 = __webpack_require__(/*! ./CTransform */ "../../libs/ff-scene/source/components/CTransform.ts");
////////////////////////////////////////////////////////////////////////////////
const _context = {
    view: null,
    viewport: null,
    renderer: null,
    scene: null,
    camera: null,
    geometry: null,
    material: null,
    group: null
};
const _hookObject3D = function (object) {
    if (object.material) {
        object.onBeforeRender = function (r, s, c, g, material) {
            if (material.isIndexShader) {
                //console.log("setIndex #%s for %s", object.id, object);
                material.setIndex(object.id);
            }
        };
    }
};
const _unhookObject3D = function (object) {
    if (object.material) {
        object.onBeforeRender = null;
    }
};
/**
 * Base component for Three.js renderable objects.
 * If component is added to a node together with a [[Transform]] component,
 * it is automatically added as a child to the transform.
 */
class CObject3D extends Component_1.default {
    constructor(id) {
        super(id);
        this._object3D = null;
        this.addEvent("object");
    }
    get transform() {
        return this.node.components.get(CTransform_1.default);
    }
    get object3D() {
        return this._object3D;
    }
    set object3D(object) {
        const transform = this.transform;
        const currentObject = this._object3D;
        if (currentObject) {
            object.userData["component"] = null;
            currentObject.onBeforeRender = null;
            currentObject.onAfterRender = null;
            this.unregisterPickableObject3D(currentObject, true);
            if (transform) {
                transform.removeObject3D(currentObject);
            }
        }
        this.emit({ type: "object", current: currentObject, next: object });
        this._object3D = object;
        if (object) {
            object.userData["component"] = this;
            object.matrixAutoUpdate = false;
            object.onBeforeRender = this._onBeforeRender.bind(this);
            if (this.afterRender) {
                object.onAfterRender = this._onAfterRender.bind(this);
            }
            this.registerPickableObject3D(object, true);
            if (transform) {
                transform.addObject3D(object);
            }
        }
    }
    create() {
        this.trackComponent(CTransform_1.default, transform => {
            if (this._object3D) {
                transform.addObject3D(this._object3D);
            }
        }, transform => {
            if (this._object3D) {
                transform.removeObject3D(this._object3D);
            }
        });
    }
    dispose() {
        if (this._object3D) {
            const transform = this.transform;
            if (transform) {
                transform.removeObject3D(this._object3D);
            }
        }
        super.dispose();
    }
    /**
     * For renderable components, this is called right before the component is rendered.
     * Override to make adjustments specific to the renderer, view or viewport.
     * @param context
     */
    beforeRender(context) {
    }
    /**
     * For renderable components, this is called right after the component is rendered.
     * Override to make adjustments specific to the renderer, view or viewport.
     * @param context
     */
    afterRender(context) {
    }
    addObject3D(object) {
        this._object3D.add(object);
        this.registerPickableObject3D(object, true);
    }
    removeObject3D(object) {
        this._object3D.remove(object);
        this.unregisterPickableObject3D(object, true);
    }
    /**
     * This should be called after an external change to this component's Object3D subtree.
     * It registers newly added mesh objects with the picking service.
     * @param object
     * @param recursive
     */
    registerPickableObject3D(object, recursive = false) {
        if (recursive && object === this._object3D) {
            object.children.forEach(child => child.traverse(object => _hookObject3D(object)));
        }
        else if (recursive) {
            object.traverse(object => _hookObject3D(object));
        }
        else if (object !== this._object3D) {
            _hookObject3D(object);
        }
    }
    /**
     * This should be called before an external change to this component's Object3D subtree.
     * It unregisters the mesh objects in the subtree from the picking service.
     * @param object
     * @param recursive
     */
    unregisterPickableObject3D(object, recursive = false) {
        if (recursive && object === this._object3D) {
            object.children.forEach(child => child.traverse(object => _unhookObject3D(object)));
        }
        else if (recursive) {
            object.traverse(object => _unhookObject3D(object));
        }
        else if (object !== this._object3D) {
            _unhookObject3D(object);
        }
    }
    /**
     * Returns a text representation.
     */
    toString() {
        return super.toString() + (this._object3D ? ` - type: ${this._object3D.type}` : " - (null)");
    }
    _onBeforeRender(renderer, scene, camera, geometry, material, group) {
        // index rendering for picking: set shader index uniform to object index
        if (material.isIndexShader) {
            material.setIndex(this.object3D.id);
        }
        if (this.beforeRender) {
            _context.view = renderer["__view"];
            _context.viewport = renderer["__viewport"];
            _context.renderer = renderer;
            _context.scene = scene;
            _context.camera = camera;
            _context.geometry = geometry;
            _context.material = material;
            _context.group = group;
            this.beforeRender(_context);
        }
    }
    _onAfterRender(renderer, scene, camera, geometry, material, group) {
        _context.view = renderer["__view"];
        _context.viewport = renderer["__viewport"];
        _context.renderer = renderer;
        _context.scene = scene;
        _context.camera = camera;
        _context.geometry = geometry;
        _context.material = material;
        _context.group = group;
        this.afterRender(_context);
    }
}
CObject3D.type = "CObject3D";
exports.default = CObject3D;
CObject3D.prototype.beforeRender = null;
CObject3D.prototype.afterRender = null;


/***/ }),

/***/ "../../libs/ff-scene/source/components/COrbitManipulator.ts":
/*!*****************************************************************!*\
  !*** /app/libs/ff-scene/source/components/COrbitManipulator.ts ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const Component_1 = __webpack_require__(/*! @ff/graph/Component */ "../../libs/ff-graph/source/Component.ts");
const OrbitManipulator_1 = __webpack_require__(/*! @ff/three/OrbitManipulator */ "../../libs/ff-three/source/OrbitManipulator.ts");
const CScene_1 = __webpack_require__(/*! ./CScene */ "../../libs/ff-scene/source/components/CScene.ts");
////////////////////////////////////////////////////////////////////////////////
const ins = {
    enabled: propertyTypes_1.types.Boolean("Enabled", true),
    orbit: propertyTypes_1.types.Vector3("Orbit", [0, 0, 0]),
    offset: propertyTypes_1.types.Vector3("Offset", [0, 0, 50]),
    minOrbit: propertyTypes_1.types.Vector3("Min.Orbit", [-90, NaN, NaN]),
    minOffset: propertyTypes_1.types.Vector3("Min.Offset", [NaN, NaN, 0.1]),
    maxOrbit: propertyTypes_1.types.Vector3("Max.Orbit", [90, NaN, NaN]),
    maxOffset: propertyTypes_1.types.Vector3("Max.Offset", [NaN, NaN, 100])
};
const outs = {
    orbit: propertyTypes_1.types.Vector3("Orbit"),
    offset: propertyTypes_1.types.Vector3("Offset"),
    size: propertyTypes_1.types.Number("Size")
};
class COrbitManipulator extends Component_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
        this.outs = this.addOutputs(outs);
        this._manip = new OrbitManipulator_1.default();
    }
    create() {
        super.create();
        this._manip.cameraMode = true;
        this.system.on(["pointer-down", "pointer-up", "pointer-move"], this.onPointer, this);
        this.system.on("wheel", this.onTrigger, this);
        this.trackComponent(CScene_1.default, component => {
            component.on("active-camera", this.onActiveCamera, this);
        }, component => {
            component.off("active-camera", this.onActiveCamera, this);
        });
    }
    dispose() {
        super.dispose();
        this.system.off(["pointer-down", "pointer-up", "pointer-move"], this.onPointer, this);
        this.system.off("wheel", this.onTrigger, this);
    }
    update() {
        const manip = this._manip;
        const ins = this.ins;
        const { minOrbit, minOffset, maxOrbit, maxOffset } = ins;
        if (minOrbit.changed || minOffset.changed || maxOrbit.changed || maxOffset.changed) {
            manip.minOrbit.fromArray(minOrbit.value);
            manip.minOffset.fromArray(minOffset.value);
            manip.maxOrbit.fromArray(maxOrbit.value);
            manip.maxOffset.fromArray(maxOffset.value);
        }
        if (ins.orbit.changed) {
            manip.orbit.fromArray(ins.orbit.value);
        }
        if (ins.offset.changed) {
            manip.offset.fromArray(ins.offset.value);
        }
        return true;
    }
    tick() {
        const manip = this._manip;
        const { enabled } = this.ins;
        const { orbit, offset, size } = this.outs;
        if (enabled.value) {
            manip.update();
            manip.orbit.toArray(orbit.value);
            orbit.set();
            manip.offset.toArray(offset.value);
            offset.set();
            size.setValue(manip.size);
            const cameraComponent = this._activeCameraComponent;
            if (cameraComponent) {
                const transformComponent = cameraComponent.transform;
                if (transformComponent) {
                    this._manip.toObject(transformComponent.object3D);
                }
                else {
                    this._manip.toObject(cameraComponent.object3D);
                }
                if (cameraComponent.camera.isOrthographicCamera) {
                    cameraComponent.camera.size = this._manip.size;
                }
                return true;
            }
        }
        return false;
    }
    onPointer(event) {
        if (this.ins.enabled.value && this._activeCameraComponent) {
            const viewport = event.viewport;
            this._manip.setViewportSize(viewport.width, viewport.height);
            this._manip.onPointer(event);
            event.stopPropagation = true;
        }
    }
    onTrigger(event) {
        if (this.ins.enabled.value && this._activeCameraComponent) {
            const viewport = event.viewport;
            this._manip.setViewportSize(viewport.width, viewport.height);
            this._manip.onTrigger(event);
            event.stopPropagation = true;
        }
    }
    onActiveCamera(event) {
        this._activeCameraComponent = event.next;
    }
}
COrbitManipulator.type = "COrbitManipulator";
exports.default = COrbitManipulator;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CPhongMaterial.ts":
/*!**************************************************************!*\
  !*** /app/libs/ff-scene/source/components/CPhongMaterial.ts ***!
  \**************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CMaterial_1 = __webpack_require__(/*! ./CMaterial */ "../../libs/ff-scene/source/components/CMaterial.ts");
////////////////////////////////////////////////////////////////////////////////
const ins = {
    color: propertyTypes_1.types.ColorRGB("Color")
};
class CPhongMaterial extends CMaterial_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
    }
    create() {
        this.material = new THREE.MeshPhongMaterial();
    }
    update() {
        const material = this.material;
        const { color } = this.ins;
        material.color.setRGB(color.value[0], color.value[1], color.value[2]);
        return true;
    }
}
CPhongMaterial.type = "CPhongMaterial";
exports.default = CPhongMaterial;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CPickSelection.ts":
/*!**************************************************************!*\
  !*** /app/libs/ff-scene/source/components/CPickSelection.ts ***!
  \**************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Component_1 = __webpack_require__(/*! @ff/graph/Component */ "../../libs/ff-graph/source/Component.ts");
const ComponentTracker_1 = __webpack_require__(/*! @ff/graph/ComponentTracker */ "../../libs/ff-graph/source/ComponentTracker.ts");
const CSelection_1 = __webpack_require__(/*! @ff/graph/components/CSelection */ "../../libs/ff-graph/source/components/CSelection.ts");
const Bracket_1 = __webpack_require__(/*! @ff/three/Bracket */ "../../libs/ff-three/source/Bracket.ts");
const CObject3D_1 = __webpack_require__(/*! ./CObject3D */ "../../libs/ff-scene/source/components/CObject3D.ts");
const CTransform_1 = __webpack_require__(/*! ./CTransform */ "../../libs/ff-scene/source/components/CTransform.ts");
const CScene_1 = __webpack_require__(/*! ./CScene */ "../../libs/ff-scene/source/components/CScene.ts");
////////////////////////////////////////////////////////////////////////////////
const inputs = {
    bracketsVisible: Component_1.types.Boolean("Brackets.Visible", true)
};
class CPickSelection extends CSelection_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(inputs);
        this.startX = 0;
        this.startY = 0;
        this._brackets = new Map();
        this._sceneTracker = null;
    }
    create() {
        super.create();
        this.system.on("pointer-down", this.onPointerDown, this);
        this.system.on("pointer-up", this.onPointerUp, this);
    }
    dispose() {
        this.system.off("pointer-down", this.onPointerDown, this);
        this.system.off("pointer-up", this.onPointerUp, this);
        this._sceneTracker.dispose();
        super.dispose();
    }
    update() {
        return true;
    }
    onSelectNode(node, selected) {
        super.onSelectNode(node, selected);
        const transform = node.components.get(CTransform_1.default);
        if (transform) {
            this.updateBracket(transform, selected);
        }
    }
    onSelectComponent(component, selected) {
        super.onSelectComponent(component, selected);
        if (component instanceof CObject3D_1.default || component instanceof CTransform_1.default) {
            this.updateBracket(component, selected);
        }
    }
    onActiveGraph(graph) {
        if (this._sceneTracker) {
            this._sceneTracker.dispose();
        }
        if (graph) {
            this._sceneTracker = new ComponentTracker_1.default(graph.components, CScene_1.default, component => {
                component.on("after-render", this.onSceneAfterRender, this);
            }, component => {
                component.off("after-render", this.onSceneAfterRender, this);
            });
        }
    }
    onPointerDown(event) {
        if (event.isPrimary) {
            this.startX = event.centerX;
            this.startY = event.centerY;
        }
    }
    onPointerUp(event) {
        if (event.isPrimary) {
            const distance = Math.abs(this.startX - event.centerX) + Math.abs(this.startY - event.centerY);
            if (distance < 2) {
                if (event.component) {
                    this.selectComponent(event.component, event.ctrlKey);
                }
                else if (!event.ctrlKey) {
                    this.clearSelection();
                }
            }
        }
    }
    onSceneAfterRender(event) {
        const renderer = event.context.renderer;
        const camera = event.context.camera;
        if (this.ins.bracketsVisible.value) {
            for (let entry of this._brackets) {
                renderer.render(entry[1], camera);
            }
        }
    }
    updateBracket(component, selected) {
        if (!component) {
            return;
        }
        if (selected) {
            const object3D = component.object3D;
            if (object3D) {
                const bracket = new Bracket_1.default(component.object3D);
                this._brackets.set(component, bracket);
            }
        }
        else {
            const bracket = this._brackets.get(component);
            if (bracket) {
                this._brackets.delete(component);
                bracket.dispose();
            }
        }
    }
}
CPickSelection.type = "CPickSelection";
exports.default = CPickSelection;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CPointLight.ts":
/*!***********************************************************!*\
  !*** /app/libs/ff-scene/source/components/CPointLight.ts ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CLight_1 = __webpack_require__(/*! ./CLight */ "../../libs/ff-scene/source/components/CLight.ts");
////////////////////////////////////////////////////////////////////////////////
const ins = {
    distance: propertyTypes_1.types.Number("Distance"),
    decay: propertyTypes_1.types.Number("Decay", 1)
};
class CPointLight extends CLight_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
    }
    get light() {
        return this.object3D;
    }
    create() {
        super.create();
        this.object3D = new THREE.PointLight();
    }
    update() {
        const light = this.light;
        const { color, intensity, distance, decay } = this.ins;
        light.color.fromArray(color.value);
        light.intensity = intensity.value;
        light.distance = distance.value;
        light.decay = decay.value;
        light.updateMatrix();
        return true;
    }
}
CPointLight.type = "CPointLight";
exports.default = CPointLight;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CRenderGraph.ts":
/*!************************************************************!*\
  !*** /app/libs/ff-scene/source/components/CRenderGraph.ts ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const CGraph_1 = __webpack_require__(/*! @ff/graph/components/CGraph */ "../../libs/ff-graph/source/components/CGraph.ts");
const CTransform_1 = __webpack_require__(/*! ./CTransform */ "../../libs/ff-scene/source/components/CTransform.ts");
////////////////////////////////////////////////////////////////////////////////
class CRenderGraph extends CGraph_1.default {
    set innerRoot(root) {
        if (root.is(CTransform_1.default)) {
            const parent = this.components.get(CTransform_1.default);
            const previous = this.innerRoot;
            const next = root;
            if (parent && previous) {
                parent.removeObject3D(previous.object3D);
            }
            super.innerRoot = next;
            if (parent && next) {
                parent.addObject3D(next.object3D);
            }
        }
    }
}
CRenderGraph.type = "CRenderGraph";
exports.default = CRenderGraph;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CRenderer.ts":
/*!*********************************************************!*\
  !*** /app/libs/ff-scene/source/components/CRenderer.ts ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Component_1 = __webpack_require__(/*! @ff/graph/Component */ "../../libs/ff-graph/source/Component.ts");
const CPulse_1 = __webpack_require__(/*! @ff/graph/components/CPulse */ "../../libs/ff-graph/source/components/CPulse.ts");
class CRenderer extends Component_1.default {
    constructor(id) {
        super(id);
        this.views = [];
        this._activeSceneComponent = null;
        this.addEvents("active-scene", "active-camera");
    }
    get activeSceneComponent() {
        return this._activeSceneComponent;
    }
    set activeSceneComponent(component) {
        if (component !== this._activeSceneComponent) {
            const previousScene = this._activeSceneComponent;
            const previousCamera = this.activeCameraComponent;
            if (previousScene) {
                previousScene.off("active-camera", this.onActiveCamera, this);
            }
            if (component) {
                component.on("active-camera", this.onActiveCamera, this);
            }
            this._activeSceneComponent = component;
            const nextCamera = this.activeCameraComponent;
            const sceneEvent = { type: "active-scene", previous: previousScene, next: component };
            this.emit(sceneEvent);
            const cameraEvent = { type: "active-camera", previous: previousCamera, next: nextCamera };
            this.emit(cameraEvent);
        }
    }
    get activeSceneGraph() {
        return this._activeSceneComponent ? this._activeSceneComponent.graph : null;
    }
    get activeScene() {
        return this._activeSceneComponent ? this._activeSceneComponent.scene : null;
    }
    get activeCameraComponent() {
        return this._activeSceneComponent ? this._activeSceneComponent.activeCameraComponent : null;
    }
    get activeCamera() {
        const component = this._activeSceneComponent ? this._activeSceneComponent.activeCameraComponent : null;
        return component ? component.camera : null;
    }
    create() {
        this.trackComponent(CPulse_1.default, component => {
            component.on("pulse", this.onPulse, this);
        }, component => {
            component.off("pulse", this.onPulse, this);
        });
    }
    attachView(view) {
        this.views.push(view);
        //console.log("RenderSystem.attachView - total views: %s", this.views.length);
    }
    detachView(view) {
        const index = this.views.indexOf(view);
        if (index < 0) {
            throw new Error("render view not registered");
        }
        this.views.splice(index, 1);
        //console.log("RenderSystem.detachView - total views: %s", this.views.length);
    }
    onPulse() {
        this.views.forEach(view => {
            view.render();
        });
    }
    onActiveCamera(event) {
        this.emit(event);
    }
}
CRenderer.type = "CRenderer";
CRenderer.isSystemSingleton = true;
exports.default = CRenderer;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CScene.ts":
/*!******************************************************!*\
  !*** /app/libs/ff-scene/source/components/CScene.ts ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CRenderer_1 = __webpack_require__(/*! ./CRenderer */ "../../libs/ff-scene/source/components/CRenderer.ts");
const CTransform_1 = __webpack_require__(/*! ./CTransform */ "../../libs/ff-scene/source/components/CTransform.ts");
const _context = {
    view: null,
    viewport: null,
    renderer: null,
    scene: null,
    camera: null
};
const _beforeRenderEvent = {
    type: "before-render",
    component: null,
    context: _context
};
const _afterRenderEvent = {
    type: "after-render",
    component: null,
    context: _context
};
const ins = {
    activate: propertyTypes_1.types.Event("Activate")
};
class CScene extends CTransform_1.default {
    constructor(id) {
        super(id);
        this._activeCameraComponent = null;
        this.ins = this.addInputs(ins, 0);
        this.addEvents("before-render", "after-render", "active-camera");
    }
    get scene() {
        return this.object3D;
    }
    get activeCameraComponent() {
        return this._activeCameraComponent;
    }
    set activeCameraComponent(component) {
        if (component !== this._activeCameraComponent) {
            const previous = this._activeCameraComponent;
            this._activeCameraComponent = component;
            const event = { type: "active-camera", previous, next: component };
            this.emit(event);
            const renderer = this.renderer;
            if (renderer) {
                this.renderer.emit(event);
            }
        }
    }
    get activeCamera() {
        return this._activeCameraComponent ? this._activeCameraComponent.camera : null;
    }
    get renderer() {
        return this.system.graph.components.get(CRenderer_1.default);
    }
    create() {
        super.create();
        const renderer = this.renderer;
        if (renderer && !renderer.activeSceneComponent) {
            renderer.activeSceneComponent = this;
        }
    }
    update(context) {
        const updated = super.update(context);
        if (this.ins.activate.changed) {
            const renderer = this.renderer;
            if (renderer) {
                renderer.activeSceneComponent = this;
            }
        }
        return updated;
    }
    dispose() {
        const renderer = this.renderer;
        if (renderer && renderer.activeSceneComponent === this) {
            renderer.activeSceneComponent = null;
        }
        super.dispose();
    }
    beforeRender(context) {
    }
    afterRender(context) {
    }
    createObject3D() {
        const scene = new THREE.Scene();
        scene.onBeforeRender = this._onBeforeRender.bind(this);
        scene.onAfterRender = this._onAfterRender.bind(this);
        return scene;
    }
    _onBeforeRender(renderer, scene, camera) {
        _context.view = renderer["__view"];
        _context.viewport = renderer["__viewport"];
        _context.renderer = renderer;
        _context.scene = scene;
        _context.camera = camera;
        this.beforeRender && this.beforeRender(_context);
        _beforeRenderEvent.component = this;
        this.emit(_beforeRenderEvent);
    }
    _onAfterRender(renderer, scene, camera) {
        _context.view = renderer["__view"];
        _context.viewport = renderer["__viewport"];
        _context.renderer = renderer;
        _context.scene = scene;
        _context.camera = camera;
        this.afterRender && this.afterRender(_context);
        _afterRenderEvent.component = this;
        this.emit(_afterRenderEvent);
    }
}
CScene.type = "CScene";
CScene.isGraphSingleton = true;
exports.default = CScene;
CScene.prototype.beforeRender = null;
CScene.prototype.afterRender = null;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CSpotLight.ts":
/*!**********************************************************!*\
  !*** /app/libs/ff-scene/source/components/CSpotLight.ts ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CLight_1 = __webpack_require__(/*! ./CLight */ "../../libs/ff-scene/source/components/CLight.ts");
////////////////////////////////////////////////////////////////////////////////
const ins = {
    distance: propertyTypes_1.types.Number("Distance"),
    decay: propertyTypes_1.types.Number("Decay", 1),
    angle: propertyTypes_1.types.Number("Angle", 45),
    penumbra: propertyTypes_1.types.Number("Penumbra", 0.5)
};
class CSpotLight extends CLight_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
    }
    get light() {
        return this.object3D;
    }
    create() {
        super.create();
        this.object3D = new THREE.SpotLight();
    }
    update() {
        const light = this.light;
        const { color, intensity, distance, decay, angle, penumbra } = this.ins;
        light.color.fromArray(color.value);
        light.intensity = intensity.value;
        light.distance = distance.value;
        light.decay = decay.value;
        light.angle = angle.value;
        light.penumbra = penumbra.value;
        light.updateMatrix();
        return true;
    }
}
CSpotLight.type = "CSpotLight";
exports.default = CSpotLight;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CTorus.ts":
/*!******************************************************!*\
  !*** /app/libs/ff-scene/source/components/CTorus.ts ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CGeometry_1 = __webpack_require__(/*! ./CGeometry */ "../../libs/ff-scene/source/components/CGeometry.ts");
////////////////////////////////////////////////////////////////////////////////
const ins = {
    radius: propertyTypes_1.types.Number("Radius", 10),
    tube: propertyTypes_1.types.Number("Tube", 3),
    angle: propertyTypes_1.types.Number("Angle", 360),
    segments: propertyTypes_1.types.Vector2("Segments", [24, 12])
};
class CTorus extends CGeometry_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
    }
    update() {
        const { radius, tube, angle, segments } = this.ins;
        this.geometry = new THREE.TorusBufferGeometry(radius.value, tube.value, segments.value[0], segments.value[1], angle.value);
        return true;
    }
}
CTorus.type = "CTorus";
exports.default = CTorus;


/***/ }),

/***/ "../../libs/ff-scene/source/components/CTransform.ts":
/*!**********************************************************!*\
  !*** /app/libs/ff-scene/source/components/CTransform.ts ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const math_1 = __webpack_require__(/*! @ff/core/math */ "../../libs/ff-core/source/math.ts");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CHierarchy_1 = __webpack_require__(/*! @ff/graph/components/CHierarchy */ "../../libs/ff-graph/source/components/CHierarchy.ts");
////////////////////////////////////////////////////////////////////////////////
const _vec3a = new THREE.Vector3();
const _vec3b = new THREE.Vector3();
const _quat = new THREE.Quaternion();
const _euler = new THREE.Euler();
var ERotationOrder;
(function (ERotationOrder) {
    ERotationOrder[ERotationOrder["XYZ"] = 0] = "XYZ";
    ERotationOrder[ERotationOrder["YZX"] = 1] = "YZX";
    ERotationOrder[ERotationOrder["ZXY"] = 2] = "ZXY";
    ERotationOrder[ERotationOrder["XZY"] = 3] = "XZY";
    ERotationOrder[ERotationOrder["YXZ"] = 4] = "YXZ";
    ERotationOrder[ERotationOrder["ZYX"] = 5] = "ZYX";
})(ERotationOrder = exports.ERotationOrder || (exports.ERotationOrder = {}));
const ins = {
    position: propertyTypes_1.types.Vector3("Position"),
    rotation: propertyTypes_1.types.Vector3("Rotation"),
    order: propertyTypes_1.types.Enum("Order", ERotationOrder),
    scale: propertyTypes_1.types.Vector3("Scale", [1, 1, 1])
};
const outs = {
    matrix: propertyTypes_1.types.Matrix4("Matrix")
};
/**
 * Allows arranging components in a hierarchical structure. Each [[TransformComponent]]
 * contains a transformation which affects its children as well as other components which
 * are part of the same entity.
 */
class CTransform extends CHierarchy_1.default {
    constructor(id) {
        super(id);
        this.ins = this.addInputs(ins);
        this.outs = this.addOutputs(outs);
        this._object3D = this.createObject3D();
        this._object3D.matrixAutoUpdate = false;
    }
    get transform() {
        return this;
    }
    /**
     * Returns the three.js renderable object wrapped in this component.
     */
    get object3D() {
        return this._object3D;
    }
    /**
     * Returns an array of child components of this.
     */
    get children() {
        return this._children || [];
    }
    /**
     * Returns a reference to the local transformation matrix.
     */
    get matrix() {
        return this._object3D.matrix;
    }
    update(context) {
        const object3D = this._object3D;
        const { position, rotation, order, scale } = this.ins;
        const { matrix } = this.outs;
        object3D.position.fromArray(position.value);
        _vec3a.fromArray(rotation.value).multiplyScalar(math_1.default.DEG2RAD);
        const orderName = order.getOptionText();
        object3D.rotation.setFromVector3(_vec3a, orderName);
        object3D.scale.fromArray(scale.value);
        object3D.updateMatrix();
        object3D.matrix.toArray(matrix.value);
        matrix.set();
        return true;
    }
    dispose() {
        if (!this._object3D) {
            return;
        }
        // detach the three.js object from its parent and children
        if (this._object3D.parent) {
            this._object3D.parent.remove(this._object3D);
        }
        this._object3D.children.slice().forEach(child => this._object3D.remove(child));
        super.dispose();
    }
    setFromMatrix(matrix) {
        const { position, rotation, order, scale } = this.ins;
        matrix.decompose(_vec3a, _quat, _vec3b);
        _vec3a.toArray(position.value);
        const orderName = order.getOptionText();
        _euler.setFromQuaternion(_quat, orderName);
        _euler.toVector3(_vec3a);
        _vec3a.multiplyScalar(math_1.default.RAD2DEG).toArray(rotation.value);
        _vec3b.toArray(scale.value);
        position.set();
        rotation.set();
        scale.set();
    }
    /**
     * Adds the given transform component as a children to this.
     * @param component
     */
    addChild(component) {
        super.addChild(component);
        this._object3D.add(component._object3D);
    }
    /**
     * Removes the given transform component from the list of children of this.
     * @param component
     */
    removeChild(component) {
        this._object3D.remove(component._object3D);
        super.removeChild(component);
    }
    /**
     * Called by [[CObject3D]] to attach its three.js renderable object to the transform component.
     * Do not call this directly.
     * @param object
     */
    addObject3D(object) {
        this._object3D.add(object);
    }
    /**
     * Called by [[CObject3D]] to detach its three.js renderable object from the transform component.
     * Do not call this directly.
     * @param object
     */
    removeObject3D(object) {
        this._object3D.remove(object);
    }
    createObject3D() {
        return new THREE.Object3D();
    }
}
CTransform.type = "CTransform";
exports.default = CTransform;


/***/ }),

/***/ "../../libs/ff-scene/source/components/index.ts":
/*!*****************************************************!*\
  !*** /app/libs/ff-scene/source/components/index.ts ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const CBasicMaterial_1 = __webpack_require__(/*! ./CBasicMaterial */ "../../libs/ff-scene/source/components/CBasicMaterial.ts");
exports.CBasicMaterial = CBasicMaterial_1.default;
const CBox_1 = __webpack_require__(/*! ./CBox */ "../../libs/ff-scene/source/components/CBox.ts");
exports.CBox = CBox_1.default;
const CCamera_1 = __webpack_require__(/*! ./CCamera */ "../../libs/ff-scene/source/components/CCamera.ts");
exports.CCamera = CCamera_1.default;
const CDirectionalLight_1 = __webpack_require__(/*! ./CDirectionalLight */ "../../libs/ff-scene/source/components/CDirectionalLight.ts");
exports.CDirectionalLight = CDirectionalLight_1.default;
const CGeometry_1 = __webpack_require__(/*! ./CGeometry */ "../../libs/ff-scene/source/components/CGeometry.ts");
exports.CGeometry = CGeometry_1.default;
const CGrid_1 = __webpack_require__(/*! ./CGrid */ "../../libs/ff-scene/source/components/CGrid.ts");
exports.CGrid = CGrid_1.default;
const CLight_1 = __webpack_require__(/*! ./CLight */ "../../libs/ff-scene/source/components/CLight.ts");
exports.CLight = CLight_1.default;
const CMain_1 = __webpack_require__(/*! ./CMain */ "../../libs/ff-scene/source/components/CMain.ts");
exports.CMain = CMain_1.default;
const CMaterial_1 = __webpack_require__(/*! ./CMaterial */ "../../libs/ff-scene/source/components/CMaterial.ts");
exports.CMaterial = CMaterial_1.default;
const CMesh_1 = __webpack_require__(/*! ./CMesh */ "../../libs/ff-scene/source/components/CMesh.ts");
exports.CMesh = CMesh_1.default;
const CObject3D_1 = __webpack_require__(/*! ./CObject3D */ "../../libs/ff-scene/source/components/CObject3D.ts");
exports.CObject3D = CObject3D_1.default;
const COrbitManipulator_1 = __webpack_require__(/*! ./COrbitManipulator */ "../../libs/ff-scene/source/components/COrbitManipulator.ts");
exports.COrbitManipulator = COrbitManipulator_1.default;
const CPhongMaterial_1 = __webpack_require__(/*! ./CPhongMaterial */ "../../libs/ff-scene/source/components/CPhongMaterial.ts");
exports.CPhongMaterial = CPhongMaterial_1.default;
const CPickSelection_1 = __webpack_require__(/*! ./CPickSelection */ "../../libs/ff-scene/source/components/CPickSelection.ts");
exports.CPickSelection = CPickSelection_1.default;
const CPointLight_1 = __webpack_require__(/*! ./CPointLight */ "../../libs/ff-scene/source/components/CPointLight.ts");
exports.CPointLight = CPointLight_1.default;
const CRenderer_1 = __webpack_require__(/*! ./CRenderer */ "../../libs/ff-scene/source/components/CRenderer.ts");
exports.CRenderer = CRenderer_1.default;
const CRenderGraph_1 = __webpack_require__(/*! ./CRenderGraph */ "../../libs/ff-scene/source/components/CRenderGraph.ts");
exports.CRenderGraph = CRenderGraph_1.default;
const CScene_1 = __webpack_require__(/*! ./CScene */ "../../libs/ff-scene/source/components/CScene.ts");
exports.CScene = CScene_1.default;
const CSpotLight_1 = __webpack_require__(/*! ./CSpotLight */ "../../libs/ff-scene/source/components/CSpotLight.ts");
exports.CSpotLight = CSpotLight_1.default;
const CTorus_1 = __webpack_require__(/*! ./CTorus */ "../../libs/ff-scene/source/components/CTorus.ts");
exports.CTorus = CTorus_1.default;
const CTransform_1 = __webpack_require__(/*! ./CTransform */ "../../libs/ff-scene/source/components/CTransform.ts");
exports.CTransform = CTransform_1.default;
exports.componentTypes = [
    CBasicMaterial_1.default,
    CBox_1.default,
    CCamera_1.default,
    CPickSelection_1.default,
    CDirectionalLight_1.default,
    CGrid_1.default,
    CMain_1.default,
    CMesh_1.default,
    COrbitManipulator_1.default,
    CPhongMaterial_1.default,
    CPointLight_1.default,
    CRenderer_1.default,
    CRenderGraph_1.default,
    CScene_1.default,
    CSpotLight_1.default,
    CTorus_1.default,
    CTransform_1.default
];


/***/ }),

/***/ "../../libs/ff-three/source/Bracket.ts":
/*!********************************************!*\
  !*** /app/libs/ff-three/source/Bracket.ts ***!
  \********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const helpers_1 = __webpack_require__(/*! ./helpers */ "../../libs/ff-three/source/helpers.ts");
////////////////////////////////////////////////////////////////////////////////
const _vec3 = new THREE.Vector3();
const _mat4 = new THREE.Matrix4();
/**
 * Wireframe selection bracket.
 */
class Bracket extends THREE.LineSegments {
    constructor(target, props) {
        props = Object.assign({}, Bracket.defaultProps, props);
        const box = new THREE.Box3();
        box.makeEmpty();
        helpers_1.computeLocalBoundingBox(target, box);
        const length = props.length;
        const min = [box.min.x, box.min.y, box.min.z];
        const max = [box.max.x, box.max.y, box.max.z];
        const size = [(max[0] - min[0]) * length, (max[1] - min[1]) * length, (max[2] - min[2]) * length];
        let vertices;
        if (isFinite(size[0]) && isFinite(size[1]) && isFinite(size[2])) {
            vertices = [
                min[0], min[1], min[2], min[0] + size[0], min[1], min[2],
                min[0], min[1], min[2], min[0], min[1] + size[1], min[2],
                min[0], min[1], min[2], min[0], min[1], min[2] + size[2],
                max[0], min[1], min[2], max[0] - size[0], min[1], min[2],
                max[0], min[1], min[2], max[0], min[1] + size[1], min[2],
                max[0], min[1], min[2], max[0], min[1], min[2] + size[2],
                min[0], max[1], min[2], min[0] + size[0], max[1], min[2],
                min[0], max[1], min[2], min[0], max[1] - size[1], min[2],
                min[0], max[1], min[2], min[0], max[1], min[2] + size[2],
                min[0], min[1], max[2], min[0] + size[0], min[1], max[2],
                min[0], min[1], max[2], min[0], min[1] + size[1], max[2],
                min[0], min[1], max[2], min[0], min[1], max[2] - size[2],
                min[0], max[1], max[2], min[0] + size[0], max[1], max[2],
                min[0], max[1], max[2], min[0], max[1] - size[1], max[2],
                min[0], max[1], max[2], min[0], max[1], max[2] - size[2],
                max[0], min[1], max[2], max[0] - size[0], min[1], max[2],
                max[0], min[1], max[2], max[0], min[1] + size[1], max[2],
                max[0], min[1], max[2], max[0], min[1], max[2] - size[2],
                max[0], max[1], min[2], max[0] - size[0], max[1], min[2],
                max[0], max[1], min[2], max[0], max[1] - size[1], min[2],
                max[0], max[1], min[2], max[0], max[1], min[2] + size[2],
                max[0], max[1], max[2], max[0] - size[0], max[1], max[2],
                max[0], max[1], max[2], max[0], max[1] - size[1], max[2],
                max[0], max[1], max[2], max[0], max[1], max[2] - size[2],
            ];
        }
        else {
            vertices = [
                -1, 0, 0, 1, 0, 0,
                0, -1, 0, 0, 1, 0,
                0, 0, -1, 0, 0, 1,
            ];
        }
        const geometry = new THREE.BufferGeometry();
        geometry.addAttribute("position", new THREE.Float32BufferAttribute(vertices, 3));
        const material = new THREE.LineBasicMaterial({
            color: props.color,
            depthTest: false
        });
        super(geometry, material);
        this.renderOrder = 1;
        this.onBeforeRender = () => {
            target.updateMatrixWorld(false);
            this.matrixWorld.copy(target.matrixWorld);
        };
    }
    dispose() {
        if (this.parent) {
            this.parent.remove(this);
        }
        this.geometry.dispose();
    }
    static expandBoundingBox(object, root, box) {
        const geometry = object.geometry;
        if (geometry !== undefined) {
            let parent = object;
            _mat4.identity();
            while (parent && parent !== root) {
                _mat4.premultiply(parent.matrix);
                parent = parent.parent;
            }
            if (geometry.isGeometry) {
                const vertices = geometry.vertices;
                for (let i = 0, n = vertices.length; i < n; ++i) {
                    _vec3.copy(vertices[i]).applyMatrix4(_mat4);
                    box.expandByPoint(_vec3);
                }
            }
            else if (geometry.isBufferGeometry) {
                const attribute = geometry.attributes.position;
                if (attribute !== undefined) {
                    for (let i = 0, n = attribute.count; i < n; ++i) {
                        _vec3.fromBufferAttribute(attribute, i).applyMatrix4(_mat4);
                        box.expandByPoint(_vec3);
                    }
                }
            }
        }
        const children = object.children;
        for (let i = 0, n = children.length; i < n; ++i) {
            Bracket.expandBoundingBox(children[i], root, box);
        }
    }
}
Bracket.defaultProps = {
    color: new THREE.Color("#ffd633"),
    length: 0.25
};
exports.default = Bracket;


/***/ }),

/***/ "../../libs/ff-three/source/GPUPicker.ts":
/*!**********************************************!*\
  !*** /app/libs/ff-three/source/GPUPicker.ts ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const IndexShader_1 = __webpack_require__(/*! ./shaders/IndexShader */ "../../libs/ff-three/source/shaders/IndexShader.ts");
const PositionShader_1 = __webpack_require__(/*! ./shaders/PositionShader */ "../../libs/ff-three/source/shaders/PositionShader.ts");
const NormalShader_1 = __webpack_require__(/*! ./shaders/NormalShader */ "../../libs/ff-three/source/shaders/NormalShader.ts");
////////////////////////////////////////////////////////////////////////////////
const _vec3 = new THREE.Vector3();
class GPUPicker {
    constructor(renderer) {
        this.renderer = renderer;
        this.pickTextures = [];
        for (let i = 0; i < 3; ++i) {
            this.pickTextures[i] = new THREE.WebGLRenderTarget(1, 1, { stencilBuffer: false });
        }
        this.pickBuffer = new Uint8Array(4);
        this.indexShader = new IndexShader_1.default();
        this.positionShader = new PositionShader_1.default();
        this.normalShader = new NormalShader_1.default();
    }
    pickObject(scene, camera, event) {
        const index = this.pickIndex(scene, camera, event);
        if (index > 0) {
            return scene.getObjectById(index);
        }
        return undefined;
    }
    pickIndex(scene, camera, event) {
        const viewport = event.viewport;
        camera = viewport.updateCamera(camera);
        const overrideMaterial = scene.overrideMaterial;
        scene.overrideMaterial = this.indexShader;
        const renderer = this.renderer;
        const pickTexture = this.pickTextures[0];
        const color = renderer.getClearColor().clone();
        renderer.setClearColor(0);
        viewport.applyPickViewport(pickTexture, event);
        renderer.render(scene, camera, pickTexture, true);
        renderer.setRenderTarget();
        renderer.setClearColor(color);
        scene.overrideMaterial = overrideMaterial;
        const buffer = this.pickBuffer;
        renderer.readRenderTargetPixels(pickTexture, 0, 0, 1, 1, buffer);
        return buffer[0] + buffer[1] * 256 + buffer[2] * 65536;
    }
    pickPosition(scene, camera, boundingBox, event) {
        const viewport = event.viewport;
        camera = viewport.updateCamera(camera);
        const overrideMaterial = scene.overrideMaterial;
        const shader = scene.overrideMaterial = this.positionShader;
        const renderer = this.renderer;
        const pickTextures = this.pickTextures;
        const color = renderer.getClearColor().clone();
        renderer.setClearColor(0);
        for (let i = 0; i < 3; ++i) {
            shader.uniforms.index.value = i;
            shader.uniforms.range.value[0] = boundingBox.min.getComponent(i);
            shader.uniforms.range.value[1] = boundingBox.max.getComponent(i);
            viewport.applyPickViewport(pickTextures[i], event);
            renderer.render(scene, camera, pickTextures[i], true);
        }
        renderer.setRenderTarget();
        renderer.setClearColor(color);
        scene.overrideMaterial = overrideMaterial;
        const buffer = this.pickBuffer;
        const position = new THREE.Vector3();
        for (let i = 0; i < 3; ++i) {
            renderer.readRenderTargetPixels(pickTextures[i], 0, 0, 1, 1, buffer);
            position[i] = buffer[0] / 255
                + buffer[1] / 255 / 256
                + buffer[2] / 255 / 65536
                + buffer[3] / 255 / 16777216;
        }
        boundingBox.getSize(_vec3);
        return position.multiply(_vec3).add(boundingBox.min);
    }
    pickNormal(scene, camera, event) {
        const viewport = event.viewport;
        camera = viewport.updateCamera(camera);
        const overrideMaterial = scene.overrideMaterial;
        scene.overrideMaterial = this.normalShader;
        const renderer = this.renderer;
        const pickTexture = this.pickTextures[0];
        const color = renderer.getClearColor().clone();
        renderer.setClearColor(0);
        viewport.applyPickViewport(pickTexture, event);
        renderer.render(scene, camera, pickTexture, true);
        renderer.setRenderTarget();
        renderer.setClearColor(color);
        scene.overrideMaterial = overrideMaterial;
        const buffer = this.pickBuffer;
        renderer.readRenderTargetPixels(pickTexture, 0, 0, 1, 1, buffer);
        return new THREE.Vector3(buffer[0] / 255 * 2 - 1, buffer[1] / 255 * 2 - 1, buffer[2] / 255 * 2 - 1).normalize();
    }
}
exports.default = GPUPicker;


/***/ }),

/***/ "../../libs/ff-three/source/Grid.ts":
/*!*****************************************!*\
  !*** /app/libs/ff-three/source/Grid.ts ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
class Grid extends THREE.LineSegments {
    constructor(props) {
        const geometry = Grid.generate(props);
        const material = new THREE.LineBasicMaterial({
            vertexColors: THREE.VertexColors
        });
        super(geometry, material);
    }
    update(props) {
        if (this.geometry) {
            this.geometry.dispose();
        }
        this.geometry = Grid.generate(props);
    }
    static generate(props) {
        const mainColor = new THREE.Color(props.mainColor);
        const subColor = new THREE.Color(props.subColor);
        const divisions = props.mainDivisions * props.subDivisions;
        const step = props.size / divisions;
        const halfSize = props.size * 0.5;
        const vertices = [];
        const colors = [];
        for (let i = 0, j = 0, k = -halfSize; i <= divisions; ++i, k += step) {
            vertices.push(-halfSize, 0, k, halfSize, 0, k);
            vertices.push(k, 0, -halfSize, k, 0, halfSize);
            const color = i % props.subDivisions === 0 ? mainColor : subColor;
            color.toArray(colors, j);
            j += 3;
            color.toArray(colors, j);
            j += 3;
            color.toArray(colors, j);
            j += 3;
            color.toArray(colors, j);
            j += 3;
        }
        const geometry = new THREE.BufferGeometry();
        geometry.addAttribute("position", new THREE.Float32BufferAttribute(vertices, 3));
        geometry.addAttribute("color", new THREE.Float32BufferAttribute(colors, 3));
        return geometry;
    }
}
exports.default = Grid;


/***/ }),

/***/ "../../libs/ff-three/source/OrbitManipulator.ts":
/*!*****************************************************!*\
  !*** /app/libs/ff-three/source/OrbitManipulator.ts ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const math_1 = __webpack_require__(/*! @ff/core/math */ "../../libs/ff-core/source/math.ts");
const math_2 = __webpack_require__(/*! ./math */ "../../libs/ff-three/source/math.ts");
const UniversalCamera_1 = __webpack_require__(/*! ./UniversalCamera */ "../../libs/ff-three/source/UniversalCamera.ts");
exports.EProjection = UniversalCamera_1.EProjection;
////////////////////////////////////////////////////////////////////////////////
const _vec3a = new THREE.Vector3();
const _vec3b = new THREE.Vector3();
var EViewPreset;
(function (EViewPreset) {
    EViewPreset[EViewPreset["Left"] = 0] = "Left";
    EViewPreset[EViewPreset["Right"] = 1] = "Right";
    EViewPreset[EViewPreset["Top"] = 2] = "Top";
    EViewPreset[EViewPreset["Bottom"] = 3] = "Bottom";
    EViewPreset[EViewPreset["Front"] = 4] = "Front";
    EViewPreset[EViewPreset["Back"] = 5] = "Back";
    EViewPreset[EViewPreset["None"] = 6] = "None";
})(EViewPreset = exports.EViewPreset || (exports.EViewPreset = {}));
var EManipMode;
(function (EManipMode) {
    EManipMode[EManipMode["Off"] = 0] = "Off";
    EManipMode[EManipMode["Pan"] = 1] = "Pan";
    EManipMode[EManipMode["Orbit"] = 2] = "Orbit";
    EManipMode[EManipMode["Dolly"] = 3] = "Dolly";
    EManipMode[EManipMode["Zoom"] = 4] = "Zoom";
    EManipMode[EManipMode["PanDolly"] = 5] = "PanDolly";
    EManipMode[EManipMode["Roll"] = 6] = "Roll";
})(EManipMode || (EManipMode = {}));
var EManipPhase;
(function (EManipPhase) {
    EManipPhase[EManipPhase["Off"] = 0] = "Off";
    EManipPhase[EManipPhase["Active"] = 1] = "Active";
    EManipPhase[EManipPhase["Release"] = 2] = "Release";
})(EManipPhase || (EManipPhase = {}));
class OrbitManipulator {
    constructor() {
        this.orbit = new THREE.Vector3(0, 0, 0);
        this.offset = new THREE.Vector3(0, 0, 50);
        this.size = 50;
        this.zoom = 1;
        this.minOrbit = new THREE.Vector3(-90, -Infinity, -Infinity);
        this.maxOrbit = new THREE.Vector3(90, Infinity, Infinity);
        this.minOffset = new THREE.Vector3(-Infinity, -Infinity, 0.1);
        this.maxOffset = new THREE.Vector3(Infinity, Infinity, 1000);
        this.orientationEnabled = true;
        this.offsetEnabled = true;
        this.cameraMode = true;
        this.orthographicMode = false;
        this.mode = EManipMode.Off;
        this.phase = EManipPhase.Off;
        this.prevPinchDist = 0;
        this.deltaX = 0;
        this.deltaY = 0;
        this.deltaPinch = 0;
        this.deltaWheel = 0;
        this.viewportWidth = 100;
        this.viewportHeight = 100;
    }
    onPointer(event) {
        if (event.isPrimary) {
            if (event.type === "pointer-down") {
                this.phase = EManipPhase.Active;
            }
            else if (event.type === "pointer-up") {
                this.phase = EManipPhase.Release;
                return true;
            }
        }
        if (event.type === "pointer-down") {
            this.mode = this.getModeFromEvent(event);
        }
        this.deltaX += event.movementX;
        this.deltaY += event.movementY;
        // calculate pinch
        if (event.pointerCount === 2) {
            const positions = event.activePositions;
            const dx = positions[1].clientX - positions[0].clientX;
            const dy = positions[1].clientY - positions[0].clientY;
            const pinchDist = Math.sqrt(dx * dx + dy * dy);
            const prevPinchDist = this.prevPinchDist || pinchDist;
            this.deltaPinch *= prevPinchDist > 0 ? (pinchDist / prevPinchDist) : 1;
            this.prevPinchDist = pinchDist;
        }
        else {
            this.deltaPinch = 1;
            this.prevPinchDist = 0;
        }
        return true;
    }
    onTrigger(event) {
        if (event.type === "wheel") {
            this.deltaWheel += math_1.default.limit(event.wheel, -1, 1);
            return true;
        }
        return false;
    }
    setViewportSize(width, height) {
        this.viewportWidth = width;
        this.viewportHeight = height;
    }
    setFromCamera(camera, adaptLimits = false) {
        const orbit = this.orbit;
        const offset = this.offset;
        math_2.default.decomposeOrbitMatrix(camera.matrix, orbit, offset);
        this.orbit.multiplyScalar(math_2.default.RAD2DEG);
        const cam = camera;
        if ((this.orthographicMode = cam.isOrthographicCamera)) {
            this.size = cam.isUniversalCamera ? cam.size : cam.top - cam.bottom;
        }
        if (adaptLimits) {
            this.minOffset.min(offset);
            this.maxOffset.max(offset);
        }
    }
    setFromObject(object) {
        math_2.default.decomposeOrbitMatrix(object.matrix, this.orbit, this.offset);
        this.orbit.multiplyScalar(math_2.default.RAD2DEG);
        this.orthographicMode = false;
    }
    setFromMatrix(matrix, invert = false) {
        math_2.default.decomposeOrbitMatrix(matrix, this.orbit, this.offset);
        this.orbit.multiplyScalar(math_2.default.RAD2DEG);
        this.orthographicMode = false;
    }
    /**
     * Updates the matrix of the given camera. If the camera's projection is orthographic,
     * updates the camera's size parameter as well.
     * @param camera
     */
    toCamera(camera) {
        _vec3a.copy(this.orbit).multiplyScalar(math_1.default.DEG2RAD);
        _vec3b.copy(this.offset);
        if (this.orthographicMode) {
            _vec3b.z = this.maxOffset.z;
        }
        math_2.default.composeOrbitMatrix(_vec3a, _vec3b, camera.matrix);
        camera.matrixWorldNeedsUpdate = true;
        const cam = camera;
        if (cam.isOrthographicCamera) {
            if (cam.isUniversalCamera) {
                cam.size = this.offset.z;
            }
            else {
                const aspect = camera.userData["aspect"] || 1;
                const halfSize = this.offset.z * 0.5;
                cam.left = -halfSize * aspect;
                cam.right = halfSize * aspect;
                cam.bottom = -halfSize;
                cam.top = halfSize;
            }
            cam.updateProjectionMatrix();
        }
    }
    /**
     * Sets the given object's matrix from the manipulator's current orbit and offset.
     * @param object
     */
    toObject(object) {
        _vec3a.copy(this.orbit).multiplyScalar(math_1.default.DEG2RAD);
        _vec3b.copy(this.offset);
        if (this.orthographicMode) {
            _vec3b.z = this.maxOffset.z;
        }
        math_2.default.composeOrbitMatrix(_vec3a, _vec3b, object.matrix);
        object.matrixWorldNeedsUpdate = true;
    }
    /**
     * Sets the given matrix from the manipulator's current orbit and offset.
     * @param matrix
     */
    toMatrix(matrix) {
        _vec3a.copy(this.orbit).multiplyScalar(math_1.default.DEG2RAD);
        _vec3b.copy(this.offset);
        if (this.orthographicMode) {
            _vec3b.z = this.maxOffset.z;
        }
        math_2.default.composeOrbitMatrix(_vec3a, _vec3b, matrix);
    }
    /**
     * Updates the manipulator.
     * @returns true if the state has changed during the update.
     */
    update() {
        if (this.phase === EManipPhase.Off && this.deltaWheel === 0) {
            return false;
        }
        if (this.deltaWheel !== 0) {
            this.updatePose(0, 0, this.deltaWheel * 0.07 + 1, 0, 0, 0);
            this.deltaWheel = 0;
            return true;
        }
        if (this.phase === EManipPhase.Active) {
            if (this.deltaX === 0 && this.deltaY === 0 && this.deltaPinch === 1) {
                return false;
            }
            this.updateByMode();
            this.deltaX = 0;
            this.deltaY = 0;
            this.deltaPinch = 1;
            return true;
        }
        else if (this.phase === EManipPhase.Release) {
            this.deltaX *= 0.85;
            this.deltaY *= 0.85;
            this.deltaPinch = 1;
            this.updateByMode();
            const delta = Math.abs(this.deltaX) + Math.abs(this.deltaY);
            if (delta < 0.1) {
                this.mode = EManipMode.Off;
                this.phase = EManipPhase.Off;
            }
            return true;
        }
        return false;
    }
    updateByMode() {
        switch (this.mode) {
            case EManipMode.Orbit:
                this.updatePose(0, 0, 1, this.deltaY, this.deltaX, 0);
                break;
            case EManipMode.Pan:
                this.updatePose(this.deltaX, this.deltaY, 1, 0, 0, 0);
                break;
            case EManipMode.Roll:
                this.updatePose(0, 0, 1, 0, 0, this.deltaX);
                break;
            case EManipMode.Dolly:
                this.updatePose(0, 0, this.deltaY * 0.0075 + 1, 0, 0, 0);
                break;
            case EManipMode.PanDolly:
                const pinchScale = (this.deltaPinch - 1) * 0.5 + 1;
                this.updatePose(this.deltaX, this.deltaY, 1 / pinchScale, 0, 0, 0);
                break;
        }
    }
    updatePose(dX, dY, dScale, dPitch, dHead, dRoll) {
        const { orbit, minOrbit, maxOrbit, offset, minOffset, maxOffset } = this;
        let inverse = this.cameraMode ? -1 : 1;
        if (this.orientationEnabled) {
            orbit.x += inverse * dPitch * 300 / this.viewportHeight;
            orbit.y += inverse * dHead * 300 / this.viewportHeight;
            orbit.z += inverse * dRoll * 300 / this.viewportHeight;
            // check limits
            orbit.x = math_1.default.limit(orbit.x, minOrbit.x, maxOrbit.x);
            orbit.y = math_1.default.limit(orbit.y, minOrbit.y, maxOrbit.y);
            orbit.z = math_1.default.limit(orbit.z, minOrbit.z, maxOrbit.z);
        }
        if (this.offsetEnabled) {
            const factor = offset.z = dScale * offset.z;
            offset.x += dX * factor * inverse * 2 / this.viewportHeight;
            offset.y -= dY * factor * inverse * 2 / this.viewportHeight;
            // check limits
            offset.x = math_1.default.limit(offset.x, minOffset.x, maxOffset.x);
            offset.y = math_1.default.limit(offset.y, minOffset.y, maxOffset.y);
            offset.z = math_1.default.limit(offset.z, minOffset.z, maxOffset.z);
        }
    }
    getModeFromEvent(event) {
        if (event.source === "mouse") {
            const button = event.originalEvent.button;
            // left button
            if (button === 0) {
                if (event.ctrlKey) {
                    return EManipMode.Pan;
                }
                if (event.altKey) {
                    return EManipMode.Dolly;
                }
                return EManipMode.Orbit;
            }
            // right button
            if (button === 2) {
                if (event.altKey) {
                    return EManipMode.Roll;
                }
                else {
                    return EManipMode.Pan;
                }
            }
            // middle button
            if (button === 1) {
                return EManipMode.Dolly;
            }
        }
        else if (event.source === "touch") {
            const count = event.pointerCount;
            if (count === 1) {
                return EManipMode.Orbit;
            }
            if (count === 2) {
                return EManipMode.PanDolly;
            }
            return EManipMode.Pan;
        }
    }
}
exports.default = OrbitManipulator;


/***/ }),

/***/ "../../libs/ff-three/source/UniversalCamera.ts":
/*!****************************************************!*\
  !*** /app/libs/ff-three/source/UniversalCamera.ts ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const math_1 = __webpack_require__(/*! @ff/core/math */ "../../libs/ff-core/source/math.ts");
////////////////////////////////////////////////////////////////////////////////
const _halfPi = Math.PI * 0.5;
const _box = new THREE.Box3();
const _size = new THREE.Vector3();
const _center = new THREE.Vector3();
const _translation = new THREE.Vector3();
const _mat4a = new THREE.Matrix4();
const _mat4b = new THREE.Matrix4();
const _cameraOrientation = [
    new THREE.Vector3(0, -_halfPi, 0),
    new THREE.Vector3(0, _halfPi, 0),
    new THREE.Vector3(-_halfPi, 0, 0),
    new THREE.Vector3(_halfPi, 0, 0),
    new THREE.Vector3(0, 0, 0),
    new THREE.Vector3(0, Math.PI, 0),
];
var EProjection;
(function (EProjection) {
    EProjection[EProjection["Perspective"] = 0] = "Perspective";
    EProjection[EProjection["Orthographic"] = 1] = "Orthographic";
})(EProjection = exports.EProjection || (exports.EProjection = {}));
var EViewPreset;
(function (EViewPreset) {
    EViewPreset[EViewPreset["None"] = -1] = "None";
    EViewPreset[EViewPreset["Left"] = 0] = "Left";
    EViewPreset[EViewPreset["Right"] = 1] = "Right";
    EViewPreset[EViewPreset["Top"] = 2] = "Top";
    EViewPreset[EViewPreset["Bottom"] = 3] = "Bottom";
    EViewPreset[EViewPreset["Front"] = 4] = "Front";
    EViewPreset[EViewPreset["Back"] = 5] = "Back";
})(EViewPreset = exports.EViewPreset || (exports.EViewPreset = {}));
class UniversalCamera extends THREE.Camera {
    constructor(projection) {
        super();
        this.isUniversalCamera = true;
        this.fov = 50;
        this.size = 20;
        this.aspect = 1;
        this.distance = 20;
        this.zoom = 1;
        this.near = 0.1;
        this.far = 2000;
        // additional perspective parameters
        this.focus = 10;
        this.filmGauge = 35;
        this.filmOffset = 0;
        // view offset
        this.view = null;
        this.setProjection(projection);
    }
    setProjection(type) {
        if (type === EProjection.Orthographic) {
            this.type = "OrthographicCamera";
            this.isPerspectiveCamera = false;
            this.isOrthographicCamera = true;
        }
        else {
            this.type = "PerspectiveCamera";
            this.isPerspectiveCamera = true;
            this.isOrthographicCamera = false;
        }
        this.updateProjectionMatrix();
    }
    getProjection() {
        return this.isOrthographicCamera ? EProjection.Orthographic : EProjection.Perspective;
    }
    setPreset(preset) {
        if (preset !== EViewPreset.None) {
            this.rotation.setFromVector3(_cameraOrientation[preset], "XYZ");
            this.position.set(0, 0, this.distance).applyQuaternion(this.quaternion);
        }
        else {
            this.rotation.set(0, 0, 0);
            this.position.set(0, 0, 0);
        }
        this.updateMatrix();
    }
    setFocalLength(focalLength) {
        const vExtentSlope = 0.5 * this.getFilmHeight() / focalLength;
        this.fov = THREE.Math.RAD2DEG * 2 * Math.atan(vExtentSlope);
        this.updateProjectionMatrix();
    }
    getFocalLength() {
        const vExtentSlope = Math.tan(THREE.Math.DEG2RAD * 0.5 * this.fov);
        return 0.5 * this.getFilmHeight() / vExtentSlope;
    }
    getEffectiveFOV() {
        return THREE.Math.RAD2DEG * 2 * Math.atan(Math.tan(THREE.Math.DEG2RAD * 0.5 * this.fov) / this.zoom);
    }
    getFilmWidth() {
        return this.filmGauge * Math.min(this.aspect, 1);
    }
    getFilmHeight() {
        return this.filmGauge / Math.max(this.aspect, 1);
    }
    setViewOffset(viewportWidth, viewportHeight, windowX, windowY, windowWidth, windowHeight) {
        if (this.isPerspectiveCamera) {
            THREE.PerspectiveCamera.prototype.setViewOffset.call(this, viewportWidth, viewportHeight, windowX, windowY, windowWidth, windowHeight);
        }
        else {
            THREE.OrthographicCamera.prototype.setViewOffset.call(this, viewportWidth, viewportHeight, windowX, windowY, windowWidth, windowHeight);
        }
    }
    clearViewOffset() {
        if (this.view !== null) {
            this.view.enabled = false;
        }
        this.updateProjectionMatrix();
    }
    zoomToView() {
        // TODO: Implement
    }
    moveToView(boundingBox) {
        this.updateMatrixWorld(false);
        _box.copy(boundingBox);
        _mat4a.extractRotation(this.matrixWorldInverse);
        _box.applyMatrix4(_mat4a);
        _box.getSize(_size);
        _box.getCenter(_center);
        const objectSize = Math.max(_size.x / this.aspect, _size.y);
        _translation.set(-_center.x, -_center.y, 0);
        if (this.isPerspectiveCamera) {
            _translation.z = _size.z / (2 * Math.tan(this.fov * math_1.default.DEG2RAD * 0.5));
        }
        else {
            this.size = objectSize * 0.5;
            _translation.z = _size.z * 2;
            this.far = Math.max(this.far, _translation.z * 2);
        }
        _mat4a.extractRotation(this.matrixWorld);
        _translation.applyMatrix4(_mat4a);
        this.matrix.decompose(this.position, this.quaternion, this.scale);
        this.position.copy(_translation);
        this.updateMatrix();
    }
    updateProjectionMatrix() {
        const near = this.near;
        const far = this.far;
        const aspect = this.aspect;
        const zoom = this.zoom;
        const view = this.view;
        if (this.isOrthographicCamera) {
            const size = this.size;
            const dy = size / (2 * zoom);
            const dx = dy * aspect;
            let left = -dx;
            let right = dx;
            let top = dy;
            let bottom = -dy;
            if (view && view.enabled) {
                const zoomW = zoom / (view.width / view.fullWidth);
                const zoomH = zoom / (view.height / view.fullHeight);
                const scaleW = size * aspect / view.width;
                const scaleH = size / view.height;
                left += scaleW * (view.offsetX / zoomW);
                right = left + scaleW * (view.width / zoomW);
                top -= scaleH * (view.offsetY / zoomH);
                bottom = top - scaleH * (view.height / zoomH);
            }
            this.projectionMatrix.makeOrthographic(left, right, top, bottom, near, far);
        }
        else {
            let top = near * Math.tan(THREE.Math.DEG2RAD * 0.5 * this.fov) / zoom;
            let height = 2 * top;
            let width = aspect * height;
            let left = -0.5 * width;
            if (view && view.enabled) {
                left += view.offsetX * width / view.fullWidth;
                top -= view.offsetY * height / view.fullHeight;
                width *= view.width / view.fullWidth;
                height *= view.height / view.fullHeight;
            }
            var skew = this.filmOffset;
            if (skew !== 0) {
                left += near * skew / this.getFilmWidth();
            }
            this.projectionMatrix.makePerspective(left, left + width, top, top - height, near, far);
        }
        this.projectionMatrixInverse.getInverse(this.projectionMatrix);
    }
    copy(source, recursive) {
        super.copy(source, recursive);
        this.type = source.type;
        this.isOrthographicCamera = source.isOrthographicCamera;
        this.isPerspectiveCamera = source.isPerspectiveCamera;
        this.fov = source.fov;
        this.size = source.size;
        this.aspect = source.aspect;
        this.zoom = source.zoom;
        this.near = source.near;
        this.far = source.far;
        this.focus = source.focus;
        this.filmGauge = source.filmGauge;
        this.filmOffset = source.filmOffset;
        this.view = source.view ? Object.assign({}, source.view) : null;
        return this;
    }
    clone() {
        return new this.constructor().copy(this);
    }
    toJSON(meta) {
        const data = super.toJSON(meta);
        data.object.fov = this.fov;
        data.object.size = this.size;
        data.object.aspect = this.aspect;
        data.object.zoom = this.zoom;
        data.object.near = this.near;
        data.object.far = this.far;
        data.object.focus = this.focus;
        data.object.filmGauge = this.filmGauge;
        data.object.filmOffset = this.filmOffset;
        if (this.view !== null) {
            data.object.view = Object.assign({}, this.view);
        }
        return data;
    }
}
exports.default = UniversalCamera;


/***/ }),

/***/ "../../libs/ff-three/source/Viewport.ts":
/*!*********************************************!*\
  !*** /app/libs/ff-three/source/Viewport.ts ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const UniversalCamera_1 = __webpack_require__(/*! ./UniversalCamera */ "../../libs/ff-three/source/UniversalCamera.ts");
const OrbitManipulator_1 = __webpack_require__(/*! ./OrbitManipulator */ "../../libs/ff-three/source/OrbitManipulator.ts");
class Viewport {
    constructor(left, top, width, height) {
        this.next = null;
        this.enabled = true;
        this.next = null;
        this._relRect = {
            left: left || 0,
            top: top || 0,
            width: width || 1,
            height: height || 1
        };
        this._absRect = {
            left: 0,
            top: 0,
            width: 1,
            height: 1
        };
        this._canvasWidth = 1;
        this._canvasHeight = 1;
        this._sceneCamera = null;
        this._vpCamera = null;
        this._manip = null;
    }
    get left() {
        return this._absRect.left;
    }
    get top() {
        return this._absRect.top;
    }
    get width() {
        return this._absRect.width;
    }
    get height() {
        return this._absRect.height;
    }
    get canvasWidth() {
        return this._canvasWidth;
    }
    get canvasHeight() {
        return this._canvasHeight;
    }
    get camera() {
        return this._vpCamera || this._sceneCamera;
    }
    get sceneCamera() {
        return this._sceneCamera;
    }
    get viewportCamera() {
        return this._vpCamera;
    }
    get manip() {
        return this._manip;
    }
    setSize(left, top, width, height) {
        const relRect = this._relRect;
        relRect.left = left;
        relRect.top = top;
        relRect.width = width;
        relRect.height = height;
        this.updateViewport();
    }
    setCanvasSize(width, height) {
        this._canvasWidth = width;
        this._canvasHeight = height;
        this.updateViewport();
        if (this._manip) {
            this._manip.setViewportSize(width, height);
        }
    }
    setBuiltInCamera(type, preset) {
        if (!this._vpCamera) {
            this._vpCamera = new UniversalCamera_1.default(type);
            this._vpCamera.matrixAutoUpdate = false;
        }
        else {
            this._vpCamera.setProjection(type);
        }
        if (preset !== undefined) {
            this._vpCamera.setPreset(preset);
        }
    }
    unsetBuiltInCamera() {
        this._vpCamera = null;
    }
    enableCameraManip(state) {
        if (!state && this._manip) {
            this._manip = null;
        }
        else if (state && this._vpCamera) {
            if (!this._manip) {
                this._manip = new OrbitManipulator_1.default();
                this._manip.setViewportSize(this.width, this.height);
                this._manip.setFromCamera(this._vpCamera);
            }
        }
        return this._manip;
    }
    moveCameraToView(box) {
        const camera = this.viewportCamera;
        const manip = this._manip;
        if (camera) {
            camera.moveToView(box);
            if (manip) {
                manip.setFromCamera(camera, true);
            }
        }
    }
    isPointInside(x, y) {
        const absRect = this._absRect;
        return x >= absRect.left && x < absRect.left + absRect.width
            && y >= absRect.top && y < absRect.top + absRect.height;
    }
    getDevicePoint(x, y, result) {
        const absRect = this._absRect;
        const ndx = ((x - absRect.left) / absRect.width) * 2 - 1;
        const ndy = 1 - ((y - absRect.top) / absRect.height) * 2;
        return result ? result.set(ndx, ndy) : new THREE.Vector2(ndx, ndy);
    }
    getDeviceX(x) {
        const absRect = this._absRect;
        return ((x - absRect.left) / absRect.width) * 2 - 1;
    }
    getDeviceY(y) {
        const absRect = this._absRect;
        return 1 - ((y - absRect.top) / absRect.height) * 2;
    }
    updateCamera(sceneCamera) {
        let currentCamera = sceneCamera;
        if (this._vpCamera) {
            currentCamera = this._vpCamera;
            if (this._manip) {
                this._manip.update();
                this._manip.toCamera(currentCamera);
            }
        }
        if (!currentCamera) {
            return;
        }
        const absRect = this._absRect;
        const aspect = absRect.width / absRect.height;
        if (aspect !== currentCamera.userData["aspect"]) {
            currentCamera.userData["aspect"] = aspect;
            if (currentCamera.isUniversalCamera || currentCamera.isPerspectiveCamera) {
                currentCamera.aspect = aspect;
                currentCamera.updateProjectionMatrix();
            }
            else if (currentCamera.isOrthographicCamera) {
                const dy = (currentCamera.top - currentCamera.bottom) * 0.5;
                currentCamera.left = -dy * aspect;
                currentCamera.right = dy * aspect;
                currentCamera.updateProjectionMatrix();
            }
        }
        return currentCamera;
    }
    applyViewport(renderer) {
        const absRect = this._absRect;
        renderer.setViewport(absRect.left, absRect.top, absRect.width, absRect.height);
        renderer["viewport"] = this;
    }
    applyPickViewport(target, event) {
        const absRect = this._absRect;
        const left = event.localX - absRect.left;
        const top = event.localY - absRect.top;
        target.viewport.set(-left, -absRect.height + top, absRect.width, absRect.height);
        //console.log("Viewport.applyPickViewport - offset: ", -left, -top);
    }
    toViewportEvent(event) {
        const vpEvent = event;
        vpEvent.viewport = this;
        vpEvent.deviceX = this.getDeviceX(event.localX);
        vpEvent.deviceY = this.getDeviceY(event.localY);
        return vpEvent;
    }
    isInside(event) {
        return this.enabled && this.isPointInside(event.localX, event.localY);
    }
    onPointer(event) {
        if (this.enabled && this._manip) {
            return this._manip.onPointer(event);
        }
        return false;
    }
    onTrigger(event) {
        if (this.enabled && this._manip) {
            return this._manip.onTrigger(event);
        }
        return false;
    }
    updateViewport() {
        const relRect = this._relRect;
        const absRect = this._absRect;
        const canvasWidth = this._canvasWidth;
        const canvasHeight = this._canvasHeight;
        absRect.left = Math.round(relRect.left * canvasWidth);
        absRect.top = Math.round(relRect.top * canvasHeight);
        absRect.width = Math.round(relRect.width * canvasWidth);
        absRect.height = Math.round(relRect.height * canvasHeight);
    }
}
exports.default = Viewport;


/***/ }),

/***/ "../../libs/ff-three/source/helpers.ts":
/*!********************************************!*\
  !*** /app/libs/ff-three/source/helpers.ts ***!
  \********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
////////////////////////////////////////////////////////////////////////////////
const _vec3 = new THREE.Vector3();
const _mat4 = new THREE.Matrix4();
const _euler = new THREE.Euler();
const _quat = new THREE.Quaternion();
function degreesToQuaternion(rotation, order, quaternion) {
    const result = quaternion || new THREE.Quaternion();
    _vec3.fromArray(rotation).multiplyScalar(THREE.Math.DEG2RAD);
    _euler.setFromVector3(_vec3, order);
    result.setFromEuler(_euler, false);
    return result;
}
exports.degreesToQuaternion = degreesToQuaternion;
function quaternionToDegrees(quaternion, order, rotation) {
    const result = rotation || [0, 0, 0];
    _euler.setFromQuaternion(quaternion, order, false);
    _euler.toVector3(_vec3);
    _vec3.multiplyScalar(THREE.Math.RAD2DEG).toArray(result);
    return result;
}
exports.quaternionToDegrees = quaternionToDegrees;
function disposeObject(object) {
    const geometries = new Map();
    const materials = new Map();
    const textures = new Map();
    object.traverse(object => {
        const mesh = object;
        if (mesh.isMesh) {
            const geometry = mesh.geometry;
            if (geometry) {
                geometries.set(geometry.uuid, geometry);
            }
            const material = mesh.material;
            if (material) {
                materials.set(material.uuid, material);
                for (let key in material) {
                    const texture = material[key]; // THREE.Texture;
                    if (texture && texture.isTexture) {
                        textures.set(texture.uuid, texture);
                    }
                }
            }
        }
    });
    for (let entry of textures) {
        entry[1].dispose();
    }
    for (let entry of materials) {
        entry[1].dispose();
    }
    for (let entry of geometries) {
        entry[1].dispose();
    }
}
exports.disposeObject = disposeObject;
/**
 * Computes the bounding box of the given object, relative to the given root (same as object if
 * not specified explicitly). Accounts for the transforms of all children relative to the root.
 * Caller is responsible for emptying the given bounding box, and for updating the matrices of
 * all child objects.
 * @param object
 * @param box The box to be updated.
 * @param root
 */
function computeLocalBoundingBox(object, box, root) {
    if (!root) {
        root = object;
    }
    const geometry = object.geometry;
    if (geometry && object.visible) {
        let current = object;
        _mat4.identity();
        while (current && current !== root) {
            _mat4.premultiply(current.matrix);
            current = current.parent;
        }
        if (geometry.isGeometry) {
            const vertices = geometry.vertices;
            for (let i = 0, n = vertices.length; i < n; ++i) {
                _vec3.copy(vertices[i]).applyMatrix4(_mat4);
                box.expandByPoint(_vec3);
            }
        }
        else if (geometry.isBufferGeometry) {
            const attribute = geometry.attributes.position;
            if (attribute !== undefined) {
                for (let i = 0, n = attribute.count; i < n; ++i) {
                    _vec3.fromBufferAttribute(attribute, i).applyMatrix4(_mat4);
                    box.expandByPoint(_vec3);
                }
            }
        }
    }
    const children = object.children;
    for (let i = 0, n = children.length; i < n; ++i) {
        computeLocalBoundingBox(children[i], box, root);
    }
}
exports.computeLocalBoundingBox = computeLocalBoundingBox;


/***/ }),

/***/ "../../libs/ff-three/source/math.ts":
/*!*****************************************!*\
  !*** /app/libs/ff-three/source/math.ts ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const math_1 = __webpack_require__(/*! @ff/core/math */ "../../libs/ff-core/source/math.ts");
////////////////////////////////////////////////////////////////////////////////
const _vec4a = new THREE.Vector4();
const _vec4b = new THREE.Vector4();
const _vec3a = new THREE.Vector3();
const _vec3b = new THREE.Vector3();
const _mat4 = new THREE.Matrix4();
const _euler = new THREE.Euler();
const _quat = new THREE.Quaternion();
const math = {
    PI: 3.1415926535897932384626433832795,
    DOUBLE_PI: 6.283185307179586476925286766559,
    HALF_PI: 1.5707963267948966192313216916398,
    QUARTER_PI: 0.78539816339744830961566084581988,
    DEG2RAD: 0.01745329251994329576923690768489,
    RAD2DEG: 57.295779513082320876798154814105,
    composeOrbitMatrix: function (orientation, offset, result) {
        const pitch = orientation.x;
        const head = orientation.y;
        const roll = orientation.z;
        const ox = offset.x;
        const oy = offset.y;
        const oz = offset.z;
        const sinX = Math.sin(pitch);
        const cosX = Math.cos(pitch);
        const sinY = Math.sin(head);
        const cosY = Math.cos(head);
        const sinZ = Math.sin(roll);
        const cosZ = Math.cos(roll);
        const m00 = cosY * cosZ;
        const m01 = cosZ * sinY * sinX - sinZ * cosX;
        const m02 = cosZ * sinY * cosX + sinZ * sinX;
        const m10 = cosY * sinZ;
        const m11 = sinX * sinY * sinZ + cosZ * cosX;
        const m12 = sinZ * sinY * cosX - cosZ * sinX;
        const m20 = -sinY;
        const m21 = cosY * sinX;
        const m22 = cosY * cosX;
        result = result || new THREE.Matrix4();
        const e = result.elements;
        e[0] = m00;
        e[1] = m10;
        e[2] = m20;
        e[3] = 0;
        e[4] = m01;
        e[5] = m11;
        e[6] = m21;
        e[7] = 0;
        e[8] = m02;
        e[9] = m12;
        e[10] = m22;
        e[11] = 0;
        e[12] = ox * m00 + oy * m01 + oz * m02;
        e[13] = ox * m10 + oy * m11 + oz * m12;
        e[14] = ox * m20 + oy * m21 + oz * m22;
        e[15] = 1;
        return result;
    },
    decomposeOrbitMatrix: function (matrix, orientationOut, offsetOut) {
        _euler.setFromRotationMatrix(matrix, "ZYX");
        _euler.toVector3(orientationOut);
        _mat4.getInverse(matrix);
        _vec4a.set(0, 0, 0, 1);
        _vec4a.applyMatrix4(_mat4);
        offsetOut.x = -_vec4a.x;
        offsetOut.y = -_vec4a.y;
        offsetOut.z = -_vec4a.z;
    },
    isMatrix4Identity: function (matrix) {
        const e = matrix.elements;
        return e[0] === 1 && e[1] === 0 && e[2] === 0 && e[3] === 0
            && e[4] === 0 && e[5] === 1 && e[6] === 0 && e[7] === 0
            && e[8] === 0 && e[9] === 0 && e[10] === 1 && e[11] === 0
            && e[12] === 0 && e[13] === 0 && e[14] === 0 && e[15] === 1;
    },
    decomposeTransformMatrix: function (matrix, posOut, rotOut, scaleOut) {
        _mat4.fromArray(matrix);
        _mat4.decompose(_vec3a, _quat, _vec3b);
        _euler.setFromQuaternion(_quat, "XYZ");
        _vec3a.toArray(posOut);
        _vec3b.toArray(scaleOut);
        _euler.toVector3(_vec3a);
        _vec4a.multiplyScalar(math_1.default.RAD2DEG);
        _vec3a.toArray(rotOut);
    }
};
exports.default = math;


/***/ }),

/***/ "../../libs/ff-three/source/shaders/IndexShader.ts":
/*!********************************************************!*\
  !*** /app/libs/ff-three/source/shaders/IndexShader.ts ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
////////////////////////////////////////////////////////////////////////////////
class IndexShader extends THREE.ShaderMaterial {
    constructor() {
        super(...arguments);
        this.isIndexShader = true;
        this.uniformsNeedUpdate = false;
        this.lights = false;
        this.uniforms = {
            index: { value: [0, 0, 0] }
        };
        this.vertexShader = [
            "void main() {",
            "  #include <begin_vertex>",
            "  #include <project_vertex>",
            "}",
        ].join("\n");
        this.fragmentShader = [
            "uniform vec3 index;",
            "void main() {",
            "  gl_FragColor = vec4(index, 1.0);",
            "}"
        ].join("\n");
    }
    static indexFromPixel(pixel) {
        return pixel[0] + pixel[1] << 8 + pixel[2] << 16;
    }
    static zoneFromPixel(pixel) {
        return pixel[3];
    }
    setIndex(index) {
        const hb = index >> 16;
        const mb = (index >> 8) - (hb << 8);
        const lb = index - (hb << 16) - (mb << 8);
        const value = this.uniforms.index.value;
        value[0] = lb / 255;
        value[1] = mb / 255;
        value[2] = hb / 255;
        this.uniformsNeedUpdate = true;
    }
}
exports.default = IndexShader;


/***/ }),

/***/ "../../libs/ff-three/source/shaders/NormalShader.ts":
/*!*********************************************************!*\
  !*** /app/libs/ff-three/source/shaders/NormalShader.ts ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
////////////////////////////////////////////////////////////////////////////////
class NormalShader extends THREE.ShaderMaterial {
    constructor() {
        super(...arguments);
        this.isNormalShader = true;
        this.uniforms = {
            index: { value: 0 }
        };
        this.vertexShader = [
            "varying vec3 vLocalNormal;",
            "void main() {",
            "  #include <beginnormal_vertex>",
            "  #include <begin_vertex>",
            "  #include <project_vertex>",
            "  vLocalNormal = vec3(normal);",
            "}",
        ].join("\n");
        this.fragmentShader = [
            "uniform vec3 index;",
            "varying vec3 vLocalNormal;",
            "void main() {",
            "  vec3 normal = normalize(vLocalNormal);",
            "  gl_FragColor = vec4(normal * 0.5 + 0.5, 1.0);",
            "}"
        ].join("\n");
    }
}
exports.default = NormalShader;


/***/ }),

/***/ "../../libs/ff-three/source/shaders/PositionShader.ts":
/*!***********************************************************!*\
  !*** /app/libs/ff-three/source/shaders/PositionShader.ts ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
////////////////////////////////////////////////////////////////////////////////
class PositionShader extends THREE.ShaderMaterial {
    constructor() {
        super(...arguments);
        this.isPositionShader = true;
        this.uniforms = {
            index: { value: 0 },
            range: { value: [-1, 1] }
        };
        this.vertexShader = [
            "varying vec3 vLocalPosition;",
            "void main() {",
            "  #include <begin_vertex>",
            "  #include <project_vertex>",
            "  vLocalPosition = vec3(position);",
            "}",
        ].join("\n");
        this.fragmentShader = [
            "uniform float index;",
            "uniform float range;",
            "varying vec3 vLocalPosition;",
            "vec4 toVec4(float v) {",
            "  float vn = (v - range.x) / (range.y - range.x);",
            "  float b0 = floor(vn * 255.0) / 255.0; vn = (vn - b0) * 256.0;",
            "  float b1 = floor(vn * 255.0) / 255.0; vn = (vn - b1) * 256.0;",
            "  float b2 = floor(vn * 255.0) / 255.0; vn = (vn - b2) * 256.0;",
            "  float b3 = floor(vn * 255.0) / 255.0;",
            "  return vec4(clamp(b0, 0.0, 1.0), clamp(b1, 0.0, 1.0), clamp(b2, 0.0, 1.0), clamp(b3, 0.0, 1.0));",
            "}",
            "void main() {",
            "  gl_FragColor = (index == 0.0 ? toVec4(vLocalPosition.x)",
            "    : (index == 1.0 ? toVec4(vLocalPosition.y) : toVec4(vLocalPosition.z)));",
            "  }"
        ].join("\n");
    }
}
exports.default = PositionShader;


/***/ }),

/***/ "../../libs/ff-ui/source/CustomElement.ts":
/*!***********************************************!*\
  !*** /app/libs/ff-ui/source/CustomElement.ts ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var CustomElement_1;
const lit_element_1 = __webpack_require__(/*! lit-element */ "../../node_modules/lit-element/lit-element.js");
////////////////////////////////////////////////////////////////////////////////
var lit_element_2 = __webpack_require__(/*! lit-element */ "../../node_modules/lit-element/lit-element.js");
exports.property = lit_element_2.property;
var lit_html_1 = __webpack_require__(/*! lit-html */ "../../node_modules/lit-html/lit-html.js");
exports.html = lit_html_1.html;
exports.svg = lit_html_1.svg;
exports.render = lit_html_1.render;
exports.TemplateResult = lit_html_1.TemplateResult;
var repeat_1 = __webpack_require__(/*! lit-html/directives/repeat */ "../../node_modules/lit-html/directives/repeat.js");
exports.repeat = repeat_1.repeat;
let CustomElement = CustomElement_1 = class CustomElement extends lit_element_1.LitElement {
    constructor() {
        super(...arguments);
        this._isFirstConnected = false;
    }
    static setStyle(element, style) {
        Object.assign(element.style, style);
    }
    static setAttribs(element, attribs) {
        for (let name in attribs) {
            element.setAttribute(name, attribs[name]);
        }
    }
    get shady() {
        return this.constructor.shady;
    }
    appendTo(parent) {
        parent.appendChild(this);
        return this;
    }
    removeChildren() {
        while (this.firstChild) {
            this.removeChild(this.firstChild);
        }
    }
    getChildrenArray() {
        return Array.from(this.children);
    }
    appendElement(tagOrType, style) {
        return this.createElement(tagOrType, style, this);
    }
    createElement(tagOrType, style, parent) {
        let element;
        if (typeof tagOrType === "string") {
            element = document.createElement(tagOrType);
        }
        else if (tagOrType instanceof HTMLElement) {
            element = tagOrType;
        }
        else {
            element = new tagOrType();
        }
        if (style) {
            Object.assign(element.style, style);
        }
        if (parent) {
            parent.appendChild(element);
        }
        return element;
    }
    setStyle(style) {
        CustomElement_1.setStyle(this, style);
        return this;
    }
    setAttribute(name, value) {
        super.setAttribute(name, value);
        return this;
    }
    setAttributes(attribs) {
        CustomElement_1.setAttribs(this, attribs);
        return this;
    }
    addClass(...classes) {
        classes.forEach(klass => this.classList.add(klass));
        return this;
    }
    removeClass(...classes) {
        classes.forEach(klass => this.classList.remove(klass));
        return this;
    }
    setClass(name, state) {
        if (state) {
            this.classList.add(name);
        }
        else {
            this.classList.remove(name);
        }
        return this;
    }
    hasFocus() {
        return document.activeElement === this;
    }
    on(type, listener, options) {
        this.addEventListener(type, listener, options);
        return this;
    }
    off(type, listener, options) {
        this.removeEventListener(type, listener, options);
        return this;
    }
    connectedCallback() {
        if (!this._isFirstConnected) {
            this._isFirstConnected = true;
            this.firstConnected();
        }
        this.connected();
        super.connectedCallback();
    }
    disconnectedCallback() {
        super.disconnectedCallback();
        this.disconnected();
    }
    createRenderRoot() {
        return this.shady ? super.createRenderRoot() : this;
    }
    firstConnected() {
    }
    connected() {
    }
    disconnected() {
    }
};
CustomElement.tagName = "ff-custom-element";
CustomElement.shady = false;
CustomElement = CustomElement_1 = __decorate([
    customElement("ff-custom-element")
], CustomElement);
exports.default = CustomElement;
function customElement(tagName) {
    return (constructor) => {
        constructor.tagName = tagName;
        customElements.define(constructor.tagName, constructor);
        return constructor;
    };
}
exports.customElement = customElement;


/***/ }),

/***/ "../../libs/ff-ui/source/QuadSplitter.ts":
/*!**********************************************!*\
  !*** /app/libs/ff-ui/source/QuadSplitter.ts ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
__webpack_require__(/*! ./Splitter */ "../../libs/ff-ui/source/Splitter.ts");
const CustomElement_1 = __webpack_require__(/*! ./CustomElement */ "../../libs/ff-ui/source/CustomElement.ts");
////////////////////////////////////////////////////////////////////////////////
var EQuadViewLayout;
(function (EQuadViewLayout) {
    EQuadViewLayout[EQuadViewLayout["Single"] = 0] = "Single";
    EQuadViewLayout[EQuadViewLayout["HorizontalSplit"] = 1] = "HorizontalSplit";
    EQuadViewLayout[EQuadViewLayout["VerticalSplit"] = 2] = "VerticalSplit";
    EQuadViewLayout[EQuadViewLayout["Quad"] = 3] = "Quad";
})(EQuadViewLayout = exports.EQuadViewLayout || (exports.EQuadViewLayout = {}));
let QuadSplitter = class QuadSplitter extends CustomElement_1.default {
    constructor() {
        super(...arguments);
        this.layout = EQuadViewLayout.Single;
        this.horizontalPosition = 0.5;
        this.verticalPosition = 0.5;
    }
    render() {
        const layout = this.layout;
        if (layout === EQuadViewLayout.Single) {
            return CustomElement_1.html ``;
        }
        const elements = [];
        if (layout === EQuadViewLayout.HorizontalSplit || layout === EQuadViewLayout.Quad) {
            elements.push(CustomElement_1.html `
                <div class="ff-horizontal" style="position:absolute; top:0; bottom:0; left:0; right:0; display:flex;">
                    <div class="ff-left" style="flex:1 1;"></div>
                    <ff-splitter direction="horizontal" position=${this.horizontalPosition} @ff-splitter-change=${this.onSplitterChange}></ff-splitter>
                    <div style="flex:1 1;"></div>
                </div>
            `);
        }
        if (layout === EQuadViewLayout.VerticalSplit || layout === EQuadViewLayout.Quad) {
            elements.push(CustomElement_1.html `
                <div class="ff-vertical" style="position:absolute; top:0; bottom:0; left:0; right:0; display:flex; flex-direction: column">
                    <div class="ff-top" style="flex:1 1;"></div>
                    <ff-splitter direction="vertical" position=${this.verticalPosition} @ff-splitter-change=${this.onSplitterChange}></ff-splitter>
                    <div style="flex:1 1;"></div>
                </div>
            `);
        }
        return CustomElement_1.html `${elements}`;
    }
    onSplitterChange(event) {
        if (event.detail.direction === "horizontal") {
            this.horizontalPosition = event.detail.position;
        }
        else {
            this.verticalPosition = event.detail.position;
        }
        if (this.onChange) {
            this.onChange({
                layout: this.layout,
                horizontalSplit: this.horizontalPosition,
                verticalSplit: this.verticalPosition,
                isDragging: event.detail.isDragging
            });
        }
    }
};
__decorate([
    CustomElement_1.property({ attribute: false })
], QuadSplitter.prototype, "layout", void 0);
__decorate([
    CustomElement_1.property({ attribute: false })
], QuadSplitter.prototype, "horizontalPosition", void 0);
__decorate([
    CustomElement_1.property({ attribute: false })
], QuadSplitter.prototype, "verticalPosition", void 0);
QuadSplitter = __decorate([
    CustomElement_1.customElement("ff-quad-splitter")
], QuadSplitter);
exports.default = QuadSplitter;


/***/ }),

/***/ "../../libs/ff-ui/source/Splitter.ts":
/*!******************************************!*\
  !*** /app/libs/ff-ui/source/Splitter.ts ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * FF Typescript Foundation Library
 * Copyright 2018 Ralph Wiedemeier, Frame Factory GmbH
 *
 * License: MIT
 */
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
var Splitter_1;
const CustomElement_1 = __webpack_require__(/*! ./CustomElement */ "../../libs/ff-ui/source/CustomElement.ts");
let Splitter = Splitter_1 = class Splitter extends CustomElement_1.default {
    constructor() {
        super();
        this.direction = "horizontal";
        this.width = 5;
        this.margin = 20;
        this.detached = false;
        this._isActive = false;
        this._offset = 0;
        this._position = 0;
        this.addEventListener("pointerdown", (e) => this.onPointerDown(e));
        this.addEventListener("pointermove", (e) => this.onPointerMove(e));
        this.addEventListener("pointerup", (e) => this.onPointerUpOrCancel(e));
        this.addEventListener("pointercancel", (e) => this.onPointerUpOrCancel(e));
    }
    get position() {
        return this._position;
    }
    isHorizontal() {
        return this.direction === "horizontal";
    }
    update(changedProperties) {
        super.update(changedProperties);
        const isHorizontal = this.isHorizontal();
        const width = this.width;
        this.setStyle({
            padding: isHorizontal ? `0 ${width}px` : `${width}px 0`,
            margin: isHorizontal ? `0 ${-width}px` : `${-width}px 0`,
            cursor: isHorizontal ? "col-resize" : "row-resize"
        });
    }
    firstUpdated() {
        this.setAttribute("touch-action", "none");
        this.setStyle({
            position: "relative",
            display: "block",
            zIndex: "1",
            touchAction: "none"
        });
    }
    onPointerDown(event) {
        if (event.isPrimary) {
            event.stopPropagation();
            event.preventDefault();
            this._isActive = true;
            this.setPointerCapture(event.pointerId);
            const rect = this.getBoundingClientRect();
            this._offset = this.isHorizontal()
                ? rect.left + rect.width * 0.5 - event.clientX
                : rect.top + rect.height * 0.5 - event.clientY;
        }
    }
    onPointerMove(event) {
        if (event.isPrimary && this._isActive) {
            event.stopPropagation();
            event.preventDefault();
            const parent = this.parentElement;
            if (!parent) {
                return;
            }
            const rect = parent.getBoundingClientRect();
            const isHorizontal = this.isHorizontal();
            const parentSize = isHorizontal ? rect.width : rect.height;
            let position = this._offset + (isHorizontal ? event.clientX - rect.left : event.clientY - rect.top);
            let relativePosition = position / parentSize;
            if (!this.detached) {
                const prevElement = this.previousElementSibling;
                const nextElement = this.nextElementSibling;
                if (prevElement instanceof HTMLElement && nextElement instanceof HTMLElement) {
                    const children = Array.from(parent.children);
                    let splitAreaStart = 0;
                    let splitAreaSize = parentSize;
                    let visited = false;
                    children.forEach(child => {
                        if (child instanceof Splitter_1) {
                            return;
                        }
                        if (child === prevElement || child === nextElement) {
                            visited = true;
                            return;
                        }
                        const childRect = child.getBoundingClientRect();
                        const childSize = isHorizontal ? childRect.width : childRect.height;
                        splitAreaSize -= childSize;
                        if (!visited) {
                            splitAreaStart += childSize;
                        }
                    });
                    const minSize = this.margin;
                    const maxSize = splitAreaSize - minSize;
                    position = (position - splitAreaStart);
                    position = position < minSize ? minSize : (position > maxSize ? maxSize : position);
                    const nextSize = (splitAreaSize - position) / parentSize;
                    relativePosition = position / parentSize;
                    prevElement.style.flexBasis = (relativePosition * 100).toFixed(3) + "%";
                    nextElement.style.flexBasis = (nextSize * 100).toFixed(3) + "%";
                    // send global resize event so components can adjust to new size
                    setTimeout(() => window.dispatchEvent(new CustomEvent("resize")), 0);
                }
            }
            this._position = relativePosition;
            this.dispatchEvent(new CustomEvent(Splitter_1.changeEvent, {
                detail: {
                    direction: this.direction,
                    position: this._position,
                    isDragging: true
                }
            }));
        }
    }
    onPointerUpOrCancel(event) {
        if (event.isPrimary) {
            event.stopPropagation();
            event.preventDefault();
            this._isActive = false;
            this.dispatchEvent(new CustomEvent(Splitter_1.changeEvent, {
                detail: {
                    direction: this.direction,
                    position: this._position,
                    isDragging: false
                }
            }));
        }
    }
};
Splitter.changeEvent = "ff-splitter-change";
__decorate([
    CustomElement_1.property({ type: String })
], Splitter.prototype, "direction", void 0);
__decorate([
    CustomElement_1.property({ type: Number })
], Splitter.prototype, "width", void 0);
__decorate([
    CustomElement_1.property({ type: Number })
], Splitter.prototype, "margin", void 0);
__decorate([
    CustomElement_1.property({ type: Boolean })
], Splitter.prototype, "detached", void 0);
Splitter = Splitter_1 = __decorate([
    CustomElement_1.customElement("ff-splitter")
], Splitter);
exports.default = Splitter;


/***/ }),

/***/ "../../node_modules/ajv/lib/ajv.js":
/*!****************************************!*\
  !*** /app/node_modules/ajv/lib/ajv.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var compileSchema = __webpack_require__(/*! ./compile */ "../../node_modules/ajv/lib/compile/index.js")
  , resolve = __webpack_require__(/*! ./compile/resolve */ "../../node_modules/ajv/lib/compile/resolve.js")
  , Cache = __webpack_require__(/*! ./cache */ "../../node_modules/ajv/lib/cache.js")
  , SchemaObject = __webpack_require__(/*! ./compile/schema_obj */ "../../node_modules/ajv/lib/compile/schema_obj.js")
  , stableStringify = __webpack_require__(/*! fast-json-stable-stringify */ "../../node_modules/fast-json-stable-stringify/index.js")
  , formats = __webpack_require__(/*! ./compile/formats */ "../../node_modules/ajv/lib/compile/formats.js")
  , rules = __webpack_require__(/*! ./compile/rules */ "../../node_modules/ajv/lib/compile/rules.js")
  , $dataMetaSchema = __webpack_require__(/*! ./data */ "../../node_modules/ajv/lib/data.js")
  , util = __webpack_require__(/*! ./compile/util */ "../../node_modules/ajv/lib/compile/util.js");

module.exports = Ajv;

Ajv.prototype.validate = validate;
Ajv.prototype.compile = compile;
Ajv.prototype.addSchema = addSchema;
Ajv.prototype.addMetaSchema = addMetaSchema;
Ajv.prototype.validateSchema = validateSchema;
Ajv.prototype.getSchema = getSchema;
Ajv.prototype.removeSchema = removeSchema;
Ajv.prototype.addFormat = addFormat;
Ajv.prototype.errorsText = errorsText;

Ajv.prototype._addSchema = _addSchema;
Ajv.prototype._compile = _compile;

Ajv.prototype.compileAsync = __webpack_require__(/*! ./compile/async */ "../../node_modules/ajv/lib/compile/async.js");
var customKeyword = __webpack_require__(/*! ./keyword */ "../../node_modules/ajv/lib/keyword.js");
Ajv.prototype.addKeyword = customKeyword.add;
Ajv.prototype.getKeyword = customKeyword.get;
Ajv.prototype.removeKeyword = customKeyword.remove;

var errorClasses = __webpack_require__(/*! ./compile/error_classes */ "../../node_modules/ajv/lib/compile/error_classes.js");
Ajv.ValidationError = errorClasses.Validation;
Ajv.MissingRefError = errorClasses.MissingRef;
Ajv.$dataMetaSchema = $dataMetaSchema;

var META_SCHEMA_ID = 'http://json-schema.org/draft-07/schema';

var META_IGNORE_OPTIONS = [ 'removeAdditional', 'useDefaults', 'coerceTypes' ];
var META_SUPPORT_DATA = ['/properties'];

/**
 * Creates validator instance.
 * Usage: `Ajv(opts)`
 * @param {Object} opts optional options
 * @return {Object} ajv instance
 */
function Ajv(opts) {
  if (!(this instanceof Ajv)) return new Ajv(opts);
  opts = this._opts = util.copy(opts) || {};
  setLogger(this);
  this._schemas = {};
  this._refs = {};
  this._fragments = {};
  this._formats = formats(opts.format);

  this._cache = opts.cache || new Cache;
  this._loadingSchemas = {};
  this._compilations = [];
  this.RULES = rules();
  this._getId = chooseGetId(opts);

  opts.loopRequired = opts.loopRequired || Infinity;
  if (opts.errorDataPath == 'property') opts._errorDataPathProperty = true;
  if (opts.serialize === undefined) opts.serialize = stableStringify;
  this._metaOpts = getMetaSchemaOptions(this);

  if (opts.formats) addInitialFormats(this);
  addDefaultMetaSchema(this);
  if (typeof opts.meta == 'object') this.addMetaSchema(opts.meta);
  if (opts.nullable) this.addKeyword('nullable', {metaSchema: {const: true}});
  addInitialSchemas(this);
}



/**
 * Validate data using schema
 * Schema will be compiled and cached (using serialized JSON as key. [fast-json-stable-stringify](https://github.com/epoberezkin/fast-json-stable-stringify) is used to serialize.
 * @this   Ajv
 * @param  {String|Object} schemaKeyRef key, ref or schema object
 * @param  {Any} data to be validated
 * @return {Boolean} validation result. Errors from the last validation will be available in `ajv.errors` (and also in compiled schema: `schema.errors`).
 */
function validate(schemaKeyRef, data) {
  var v;
  if (typeof schemaKeyRef == 'string') {
    v = this.getSchema(schemaKeyRef);
    if (!v) throw new Error('no schema with key or ref "' + schemaKeyRef + '"');
  } else {
    var schemaObj = this._addSchema(schemaKeyRef);
    v = schemaObj.validate || this._compile(schemaObj);
  }

  var valid = v(data);
  if (v.$async !== true) this.errors = v.errors;
  return valid;
}


/**
 * Create validating function for passed schema.
 * @this   Ajv
 * @param  {Object} schema schema object
 * @param  {Boolean} _meta true if schema is a meta-schema. Used internally to compile meta schemas of custom keywords.
 * @return {Function} validating function
 */
function compile(schema, _meta) {
  var schemaObj = this._addSchema(schema, undefined, _meta);
  return schemaObj.validate || this._compile(schemaObj);
}


/**
 * Adds schema to the instance.
 * @this   Ajv
 * @param {Object|Array} schema schema or array of schemas. If array is passed, `key` and other parameters will be ignored.
 * @param {String} key Optional schema key. Can be passed to `validate` method instead of schema object or id/ref. One schema per instance can have empty `id` and `key`.
 * @param {Boolean} _skipValidation true to skip schema validation. Used internally, option validateSchema should be used instead.
 * @param {Boolean} _meta true if schema is a meta-schema. Used internally, addMetaSchema should be used instead.
 * @return {Ajv} this for method chaining
 */
function addSchema(schema, key, _skipValidation, _meta) {
  if (Array.isArray(schema)){
    for (var i=0; i<schema.length; i++) this.addSchema(schema[i], undefined, _skipValidation, _meta);
    return this;
  }
  var id = this._getId(schema);
  if (id !== undefined && typeof id != 'string')
    throw new Error('schema id must be string');
  key = resolve.normalizeId(key || id);
  checkUnique(this, key);
  this._schemas[key] = this._addSchema(schema, _skipValidation, _meta, true);
  return this;
}


/**
 * Add schema that will be used to validate other schemas
 * options in META_IGNORE_OPTIONS are alway set to false
 * @this   Ajv
 * @param {Object} schema schema object
 * @param {String} key optional schema key
 * @param {Boolean} skipValidation true to skip schema validation, can be used to override validateSchema option for meta-schema
 * @return {Ajv} this for method chaining
 */
function addMetaSchema(schema, key, skipValidation) {
  this.addSchema(schema, key, skipValidation, true);
  return this;
}


/**
 * Validate schema
 * @this   Ajv
 * @param {Object} schema schema to validate
 * @param {Boolean} throwOrLogError pass true to throw (or log) an error if invalid
 * @return {Boolean} true if schema is valid
 */
function validateSchema(schema, throwOrLogError) {
  var $schema = schema.$schema;
  if ($schema !== undefined && typeof $schema != 'string')
    throw new Error('$schema must be a string');
  $schema = $schema || this._opts.defaultMeta || defaultMeta(this);
  if (!$schema) {
    this.logger.warn('meta-schema not available');
    this.errors = null;
    return true;
  }
  var valid = this.validate($schema, schema);
  if (!valid && throwOrLogError) {
    var message = 'schema is invalid: ' + this.errorsText();
    if (this._opts.validateSchema == 'log') this.logger.error(message);
    else throw new Error(message);
  }
  return valid;
}


function defaultMeta(self) {
  var meta = self._opts.meta;
  self._opts.defaultMeta = typeof meta == 'object'
                            ? self._getId(meta) || meta
                            : self.getSchema(META_SCHEMA_ID)
                              ? META_SCHEMA_ID
                              : undefined;
  return self._opts.defaultMeta;
}


/**
 * Get compiled schema from the instance by `key` or `ref`.
 * @this   Ajv
 * @param  {String} keyRef `key` that was passed to `addSchema` or full schema reference (`schema.id` or resolved id).
 * @return {Function} schema validating function (with property `schema`).
 */
function getSchema(keyRef) {
  var schemaObj = _getSchemaObj(this, keyRef);
  switch (typeof schemaObj) {
    case 'object': return schemaObj.validate || this._compile(schemaObj);
    case 'string': return this.getSchema(schemaObj);
    case 'undefined': return _getSchemaFragment(this, keyRef);
  }
}


function _getSchemaFragment(self, ref) {
  var res = resolve.schema.call(self, { schema: {} }, ref);
  if (res) {
    var schema = res.schema
      , root = res.root
      , baseId = res.baseId;
    var v = compileSchema.call(self, schema, root, undefined, baseId);
    self._fragments[ref] = new SchemaObject({
      ref: ref,
      fragment: true,
      schema: schema,
      root: root,
      baseId: baseId,
      validate: v
    });
    return v;
  }
}


function _getSchemaObj(self, keyRef) {
  keyRef = resolve.normalizeId(keyRef);
  return self._schemas[keyRef] || self._refs[keyRef] || self._fragments[keyRef];
}


/**
 * Remove cached schema(s).
 * If no parameter is passed all schemas but meta-schemas are removed.
 * If RegExp is passed all schemas with key/id matching pattern but meta-schemas are removed.
 * Even if schema is referenced by other schemas it still can be removed as other schemas have local references.
 * @this   Ajv
 * @param  {String|Object|RegExp} schemaKeyRef key, ref, pattern to match key/ref or schema object
 * @return {Ajv} this for method chaining
 */
function removeSchema(schemaKeyRef) {
  if (schemaKeyRef instanceof RegExp) {
    _removeAllSchemas(this, this._schemas, schemaKeyRef);
    _removeAllSchemas(this, this._refs, schemaKeyRef);
    return this;
  }
  switch (typeof schemaKeyRef) {
    case 'undefined':
      _removeAllSchemas(this, this._schemas);
      _removeAllSchemas(this, this._refs);
      this._cache.clear();
      return this;
    case 'string':
      var schemaObj = _getSchemaObj(this, schemaKeyRef);
      if (schemaObj) this._cache.del(schemaObj.cacheKey);
      delete this._schemas[schemaKeyRef];
      delete this._refs[schemaKeyRef];
      return this;
    case 'object':
      var serialize = this._opts.serialize;
      var cacheKey = serialize ? serialize(schemaKeyRef) : schemaKeyRef;
      this._cache.del(cacheKey);
      var id = this._getId(schemaKeyRef);
      if (id) {
        id = resolve.normalizeId(id);
        delete this._schemas[id];
        delete this._refs[id];
      }
  }
  return this;
}


function _removeAllSchemas(self, schemas, regex) {
  for (var keyRef in schemas) {
    var schemaObj = schemas[keyRef];
    if (!schemaObj.meta && (!regex || regex.test(keyRef))) {
      self._cache.del(schemaObj.cacheKey);
      delete schemas[keyRef];
    }
  }
}


/* @this   Ajv */
function _addSchema(schema, skipValidation, meta, shouldAddSchema) {
  if (typeof schema != 'object' && typeof schema != 'boolean')
    throw new Error('schema should be object or boolean');
  var serialize = this._opts.serialize;
  var cacheKey = serialize ? serialize(schema) : schema;
  var cached = this._cache.get(cacheKey);
  if (cached) return cached;

  shouldAddSchema = shouldAddSchema || this._opts.addUsedSchema !== false;

  var id = resolve.normalizeId(this._getId(schema));
  if (id && shouldAddSchema) checkUnique(this, id);

  var willValidate = this._opts.validateSchema !== false && !skipValidation;
  var recursiveMeta;
  if (willValidate && !(recursiveMeta = id && id == resolve.normalizeId(schema.$schema)))
    this.validateSchema(schema, true);

  var localRefs = resolve.ids.call(this, schema);

  var schemaObj = new SchemaObject({
    id: id,
    schema: schema,
    localRefs: localRefs,
    cacheKey: cacheKey,
    meta: meta
  });

  if (id[0] != '#' && shouldAddSchema) this._refs[id] = schemaObj;
  this._cache.put(cacheKey, schemaObj);

  if (willValidate && recursiveMeta) this.validateSchema(schema, true);

  return schemaObj;
}


/* @this   Ajv */
function _compile(schemaObj, root) {
  if (schemaObj.compiling) {
    schemaObj.validate = callValidate;
    callValidate.schema = schemaObj.schema;
    callValidate.errors = null;
    callValidate.root = root ? root : callValidate;
    if (schemaObj.schema.$async === true)
      callValidate.$async = true;
    return callValidate;
  }
  schemaObj.compiling = true;

  var currentOpts;
  if (schemaObj.meta) {
    currentOpts = this._opts;
    this._opts = this._metaOpts;
  }

  var v;
  try { v = compileSchema.call(this, schemaObj.schema, root, schemaObj.localRefs); }
  catch(e) {
    delete schemaObj.validate;
    throw e;
  }
  finally {
    schemaObj.compiling = false;
    if (schemaObj.meta) this._opts = currentOpts;
  }

  schemaObj.validate = v;
  schemaObj.refs = v.refs;
  schemaObj.refVal = v.refVal;
  schemaObj.root = v.root;
  return v;


  /* @this   {*} - custom context, see passContext option */
  function callValidate() {
    /* jshint validthis: true */
    var _validate = schemaObj.validate;
    var result = _validate.apply(this, arguments);
    callValidate.errors = _validate.errors;
    return result;
  }
}


function chooseGetId(opts) {
  switch (opts.schemaId) {
    case 'auto': return _get$IdOrId;
    case 'id': return _getId;
    default: return _get$Id;
  }
}

/* @this   Ajv */
function _getId(schema) {
  if (schema.$id) this.logger.warn('schema $id ignored', schema.$id);
  return schema.id;
}

/* @this   Ajv */
function _get$Id(schema) {
  if (schema.id) this.logger.warn('schema id ignored', schema.id);
  return schema.$id;
}


function _get$IdOrId(schema) {
  if (schema.$id && schema.id && schema.$id != schema.id)
    throw new Error('schema $id is different from id');
  return schema.$id || schema.id;
}


/**
 * Convert array of error message objects to string
 * @this   Ajv
 * @param  {Array<Object>} errors optional array of validation errors, if not passed errors from the instance are used.
 * @param  {Object} options optional options with properties `separator` and `dataVar`.
 * @return {String} human readable string with all errors descriptions
 */
function errorsText(errors, options) {
  errors = errors || this.errors;
  if (!errors) return 'No errors';
  options = options || {};
  var separator = options.separator === undefined ? ', ' : options.separator;
  var dataVar = options.dataVar === undefined ? 'data' : options.dataVar;

  var text = '';
  for (var i=0; i<errors.length; i++) {
    var e = errors[i];
    if (e) text += dataVar + e.dataPath + ' ' + e.message + separator;
  }
  return text.slice(0, -separator.length);
}


/**
 * Add custom format
 * @this   Ajv
 * @param {String} name format name
 * @param {String|RegExp|Function} format string is converted to RegExp; function should return boolean (true when valid)
 * @return {Ajv} this for method chaining
 */
function addFormat(name, format) {
  if (typeof format == 'string') format = new RegExp(format);
  this._formats[name] = format;
  return this;
}


function addDefaultMetaSchema(self) {
  var $dataSchema;
  if (self._opts.$data) {
    $dataSchema = __webpack_require__(/*! ./refs/data.json */ "../../node_modules/ajv/lib/refs/data.json");
    self.addMetaSchema($dataSchema, $dataSchema.$id, true);
  }
  if (self._opts.meta === false) return;
  var metaSchema = __webpack_require__(/*! ./refs/json-schema-draft-07.json */ "../../node_modules/ajv/lib/refs/json-schema-draft-07.json");
  if (self._opts.$data) metaSchema = $dataMetaSchema(metaSchema, META_SUPPORT_DATA);
  self.addMetaSchema(metaSchema, META_SCHEMA_ID, true);
  self._refs['http://json-schema.org/schema'] = META_SCHEMA_ID;
}


function addInitialSchemas(self) {
  var optsSchemas = self._opts.schemas;
  if (!optsSchemas) return;
  if (Array.isArray(optsSchemas)) self.addSchema(optsSchemas);
  else for (var key in optsSchemas) self.addSchema(optsSchemas[key], key);
}


function addInitialFormats(self) {
  for (var name in self._opts.formats) {
    var format = self._opts.formats[name];
    self.addFormat(name, format);
  }
}


function checkUnique(self, id) {
  if (self._schemas[id] || self._refs[id])
    throw new Error('schema with key or id "' + id + '" already exists');
}


function getMetaSchemaOptions(self) {
  var metaOpts = util.copy(self._opts);
  for (var i=0; i<META_IGNORE_OPTIONS.length; i++)
    delete metaOpts[META_IGNORE_OPTIONS[i]];
  return metaOpts;
}


function setLogger(self) {
  var logger = self._opts.logger;
  if (logger === false) {
    self.logger = {log: noop, warn: noop, error: noop};
  } else {
    if (logger === undefined) logger = console;
    if (!(typeof logger == 'object' && logger.log && logger.warn && logger.error))
      throw new Error('logger must implement log, warn and error methods');
    self.logger = logger;
  }
}


function noop() {}


/***/ }),

/***/ "../../node_modules/ajv/lib/cache.js":
/*!******************************************!*\
  !*** /app/node_modules/ajv/lib/cache.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";



var Cache = module.exports = function Cache() {
  this._cache = {};
};


Cache.prototype.put = function Cache_put(key, value) {
  this._cache[key] = value;
};


Cache.prototype.get = function Cache_get(key) {
  return this._cache[key];
};


Cache.prototype.del = function Cache_del(key) {
  delete this._cache[key];
};


Cache.prototype.clear = function Cache_clear() {
  this._cache = {};
};


/***/ }),

/***/ "../../node_modules/ajv/lib/compile/async.js":
/*!**************************************************!*\
  !*** /app/node_modules/ajv/lib/compile/async.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var MissingRefError = __webpack_require__(/*! ./error_classes */ "../../node_modules/ajv/lib/compile/error_classes.js").MissingRef;

module.exports = compileAsync;


/**
 * Creates validating function for passed schema with asynchronous loading of missing schemas.
 * `loadSchema` option should be a function that accepts schema uri and returns promise that resolves with the schema.
 * @this  Ajv
 * @param {Object}   schema schema object
 * @param {Boolean}  meta optional true to compile meta-schema; this parameter can be skipped
 * @param {Function} callback an optional node-style callback, it is called with 2 parameters: error (or null) and validating function.
 * @return {Promise} promise that resolves with a validating function.
 */
function compileAsync(schema, meta, callback) {
  /* eslint no-shadow: 0 */
  /* global Promise */
  /* jshint validthis: true */
  var self = this;
  if (typeof this._opts.loadSchema != 'function')
    throw new Error('options.loadSchema should be a function');

  if (typeof meta == 'function') {
    callback = meta;
    meta = undefined;
  }

  var p = loadMetaSchemaOf(schema).then(function () {
    var schemaObj = self._addSchema(schema, undefined, meta);
    return schemaObj.validate || _compileAsync(schemaObj);
  });

  if (callback) {
    p.then(
      function(v) { callback(null, v); },
      callback
    );
  }

  return p;


  function loadMetaSchemaOf(sch) {
    var $schema = sch.$schema;
    return $schema && !self.getSchema($schema)
            ? compileAsync.call(self, { $ref: $schema }, true)
            : Promise.resolve();
  }


  function _compileAsync(schemaObj) {
    try { return self._compile(schemaObj); }
    catch(e) {
      if (e instanceof MissingRefError) return loadMissingSchema(e);
      throw e;
    }


    function loadMissingSchema(e) {
      var ref = e.missingSchema;
      if (added(ref)) throw new Error('Schema ' + ref + ' is loaded but ' + e.missingRef + ' cannot be resolved');

      var schemaPromise = self._loadingSchemas[ref];
      if (!schemaPromise) {
        schemaPromise = self._loadingSchemas[ref] = self._opts.loadSchema(ref);
        schemaPromise.then(removePromise, removePromise);
      }

      return schemaPromise.then(function (sch) {
        if (!added(ref)) {
          return loadMetaSchemaOf(sch).then(function () {
            if (!added(ref)) self.addSchema(sch, ref, undefined, meta);
          });
        }
      }).then(function() {
        return _compileAsync(schemaObj);
      });

      function removePromise() {
        delete self._loadingSchemas[ref];
      }

      function added(ref) {
        return self._refs[ref] || self._schemas[ref];
      }
    }
  }
}


/***/ }),

/***/ "../../node_modules/ajv/lib/compile/error_classes.js":
/*!**********************************************************!*\
  !*** /app/node_modules/ajv/lib/compile/error_classes.js ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var resolve = __webpack_require__(/*! ./resolve */ "../../node_modules/ajv/lib/compile/resolve.js");

module.exports = {
  Validation: errorSubclass(ValidationError),
  MissingRef: errorSubclass(MissingRefError)
};


function ValidationError(errors) {
  this.message = 'validation failed';
  this.errors = errors;
  this.ajv = this.validation = true;
}


MissingRefError.message = function (baseId, ref) {
  return 'can\'t resolve reference ' + ref + ' from id ' + baseId;
};


function MissingRefError(baseId, ref, message) {
  this.message = message || MissingRefError.message(baseId, ref);
  this.missingRef = resolve.url(baseId, ref);
  this.missingSchema = resolve.normalizeId(resolve.fullPath(this.missingRef));
}


function errorSubclass(Subclass) {
  Subclass.prototype = Object.create(Error.prototype);
  Subclass.prototype.constructor = Subclass;
  return Subclass;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/compile/formats.js":
/*!****************************************************!*\
  !*** /app/node_modules/ajv/lib/compile/formats.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var util = __webpack_require__(/*! ./util */ "../../node_modules/ajv/lib/compile/util.js");

var DATE = /^(\d\d\d\d)-(\d\d)-(\d\d)$/;
var DAYS = [0,31,28,31,30,31,30,31,31,30,31,30,31];
var TIME = /^(\d\d):(\d\d):(\d\d)(\.\d+)?(z|[+-]\d\d:\d\d)?$/i;
var HOSTNAME = /^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[-0-9a-z]{0,61}[0-9a-z])?)*$/i;
var URI = /^(?:[a-z][a-z0-9+\-.]*:)(?:\/?\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:]|%[0-9a-f]{2})*@)?(?:\[(?:(?:(?:(?:[0-9a-f]{1,4}:){6}|::(?:[0-9a-f]{1,4}:){5}|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}|(?:(?:[0-9a-f]{1,4}:){0,1}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::)(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?))|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|[Vv][0-9a-f]+\.[a-z0-9\-._~!$&'()*+,;=:]+)\]|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)|(?:[a-z0-9\-._~!$&'()*+,;=]|%[0-9a-f]{2})*)(?::\d*)?(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*|\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*)?|(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*)(?:\?(?:[a-z0-9\-._~!$&'()*+,;=:@/?]|%[0-9a-f]{2})*)?(?:#(?:[a-z0-9\-._~!$&'()*+,;=:@/?]|%[0-9a-f]{2})*)?$/i;
var URIREF = /^(?:[a-z][a-z0-9+\-.]*:)?(?:\/?\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:]|%[0-9a-f]{2})*@)?(?:\[(?:(?:(?:(?:[0-9a-f]{1,4}:){6}|::(?:[0-9a-f]{1,4}:){5}|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}|(?:(?:[0-9a-f]{1,4}:){0,1}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::)(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?))|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|[Vv][0-9a-f]+\.[a-z0-9\-._~!$&'()*+,;=:]+)\]|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)|(?:[a-z0-9\-._~!$&'"()*+,;=]|%[0-9a-f]{2})*)(?::\d*)?(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*|\/(?:(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*)?|(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*)?(?:\?(?:[a-z0-9\-._~!$&'"()*+,;=:@/?]|%[0-9a-f]{2})*)?(?:#(?:[a-z0-9\-._~!$&'"()*+,;=:@/?]|%[0-9a-f]{2})*)?$/i;
// uri-template: https://tools.ietf.org/html/rfc6570
var URITEMPLATE = /^(?:(?:[^\x00-\x20"'<>%\\^`{|}]|%[0-9a-f]{2})|\{[+#./;?&=,!@|]?(?:[a-z0-9_]|%[0-9a-f]{2})+(?::[1-9][0-9]{0,3}|\*)?(?:,(?:[a-z0-9_]|%[0-9a-f]{2})+(?::[1-9][0-9]{0,3}|\*)?)*\})*$/i;
// For the source: https://gist.github.com/dperini/729294
// For test cases: https://mathiasbynens.be/demo/url-regex
// @todo Delete current URL in favour of the commented out URL rule when this issue is fixed https://github.com/eslint/eslint/issues/7983.
// var URL = /^(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u{00a1}-\u{ffff}0-9]+-?)*[a-z\u{00a1}-\u{ffff}0-9]+)(?:\.(?:[a-z\u{00a1}-\u{ffff}0-9]+-?)*[a-z\u{00a1}-\u{ffff}0-9]+)*(?:\.(?:[a-z\u{00a1}-\u{ffff}]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?$/iu;
var URL = /^(?:(?:http[s\u017F]?|ftp):\/\/)(?:(?:[\0-\x08\x0E-\x1F!-\x9F\xA1-\u167F\u1681-\u1FFF\u200B-\u2027\u202A-\u202E\u2030-\u205E\u2060-\u2FFF\u3001-\uD7FF\uE000-\uFEFE\uFF00-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+(?::(?:[\0-\x08\x0E-\x1F!-\x9F\xA1-\u167F\u1681-\u1FFF\u200B-\u2027\u202A-\u202E\u2030-\u205E\u2060-\u2FFF\u3001-\uD7FF\uE000-\uFEFE\uFF00-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])*)?@)?(?:(?!10(?:\.[0-9]{1,3}){3})(?!127(?:\.[0-9]{1,3}){3})(?!169\.254(?:\.[0-9]{1,3}){2})(?!192\.168(?:\.[0-9]{1,3}){2})(?!172\.(?:1[6-9]|2[0-9]|3[01])(?:\.[0-9]{1,3}){2})(?:[1-9][0-9]?|1[0-9][0-9]|2[01][0-9]|22[0-3])(?:\.(?:1?[0-9]{1,2}|2[0-4][0-9]|25[0-5])){2}(?:\.(?:[1-9][0-9]?|1[0-9][0-9]|2[0-4][0-9]|25[0-4]))|(?:(?:(?:[0-9KSa-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+-?)*(?:[0-9KSa-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+)(?:\.(?:(?:[0-9KSa-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+-?)*(?:[0-9KSa-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+)*(?:\.(?:(?:[KSa-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF]){2,})))(?::[0-9]{2,5})?(?:\/(?:[\0-\x08\x0E-\x1F!-\x9F\xA1-\u167F\u1681-\u1FFF\u200B-\u2027\u202A-\u202E\u2030-\u205E\u2060-\u2FFF\u3001-\uD7FF\uE000-\uFEFE\uFF00-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])*)?$/i;
var UUID = /^(?:urn:uuid:)?[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}$/i;
var JSON_POINTER = /^(?:\/(?:[^~/]|~0|~1)*)*$/;
var JSON_POINTER_URI_FRAGMENT = /^#(?:\/(?:[a-z0-9_\-.!$&'()*+,;:=@]|%[0-9a-f]{2}|~0|~1)*)*$/i;
var RELATIVE_JSON_POINTER = /^(?:0|[1-9][0-9]*)(?:#|(?:\/(?:[^~/]|~0|~1)*)*)$/;


module.exports = formats;

function formats(mode) {
  mode = mode == 'full' ? 'full' : 'fast';
  return util.copy(formats[mode]);
}


formats.fast = {
  // date: http://tools.ietf.org/html/rfc3339#section-5.6
  date: /^\d\d\d\d-[0-1]\d-[0-3]\d$/,
  // date-time: http://tools.ietf.org/html/rfc3339#section-5.6
  time: /^(?:[0-2]\d:[0-5]\d:[0-5]\d|23:59:60)(?:\.\d+)?(?:z|[+-]\d\d:\d\d)?$/i,
  'date-time': /^\d\d\d\d-[0-1]\d-[0-3]\d[t\s](?:[0-2]\d:[0-5]\d:[0-5]\d|23:59:60)(?:\.\d+)?(?:z|[+-]\d\d:\d\d)$/i,
  // uri: https://github.com/mafintosh/is-my-json-valid/blob/master/formats.js
  uri: /^(?:[a-z][a-z0-9+-.]*:)(?:\/?\/)?[^\s]*$/i,
  'uri-reference': /^(?:(?:[a-z][a-z0-9+-.]*:)?\/?\/)?(?:[^\\\s#][^\s#]*)?(?:#[^\\\s]*)?$/i,
  'uri-template': URITEMPLATE,
  url: URL,
  // email (sources from jsen validator):
  // http://stackoverflow.com/questions/201323/using-a-regular-expression-to-validate-an-email-address#answer-8829363
  // http://www.w3.org/TR/html5/forms.html#valid-e-mail-address (search for 'willful violation')
  email: /^[a-z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/i,
  hostname: HOSTNAME,
  // optimized https://www.safaribooksonline.com/library/view/regular-expressions-cookbook/9780596802837/ch07s16.html
  ipv4: /^(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)$/,
  // optimized http://stackoverflow.com/questions/53497/regular-expression-that-matches-valid-ipv6-addresses
  ipv6: /^\s*(?:(?:(?:[0-9a-f]{1,4}:){7}(?:[0-9a-f]{1,4}|:))|(?:(?:[0-9a-f]{1,4}:){6}(?::[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){5}(?:(?:(?::[0-9a-f]{1,4}){1,2})|:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){4}(?:(?:(?::[0-9a-f]{1,4}){1,3})|(?:(?::[0-9a-f]{1,4})?:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){3}(?:(?:(?::[0-9a-f]{1,4}){1,4})|(?:(?::[0-9a-f]{1,4}){0,2}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){2}(?:(?:(?::[0-9a-f]{1,4}){1,5})|(?:(?::[0-9a-f]{1,4}){0,3}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){1}(?:(?:(?::[0-9a-f]{1,4}){1,6})|(?:(?::[0-9a-f]{1,4}){0,4}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?::(?:(?:(?::[0-9a-f]{1,4}){1,7})|(?:(?::[0-9a-f]{1,4}){0,5}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(?:%.+)?\s*$/i,
  regex: regex,
  // uuid: http://tools.ietf.org/html/rfc4122
  uuid: UUID,
  // JSON-pointer: https://tools.ietf.org/html/rfc6901
  // uri fragment: https://tools.ietf.org/html/rfc3986#appendix-A
  'json-pointer': JSON_POINTER,
  'json-pointer-uri-fragment': JSON_POINTER_URI_FRAGMENT,
  // relative JSON-pointer: http://tools.ietf.org/html/draft-luff-relative-json-pointer-00
  'relative-json-pointer': RELATIVE_JSON_POINTER
};


formats.full = {
  date: date,
  time: time,
  'date-time': date_time,
  uri: uri,
  'uri-reference': URIREF,
  'uri-template': URITEMPLATE,
  url: URL,
  email: /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i,
  hostname: hostname,
  ipv4: /^(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)$/,
  ipv6: /^\s*(?:(?:(?:[0-9a-f]{1,4}:){7}(?:[0-9a-f]{1,4}|:))|(?:(?:[0-9a-f]{1,4}:){6}(?::[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){5}(?:(?:(?::[0-9a-f]{1,4}){1,2})|:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){4}(?:(?:(?::[0-9a-f]{1,4}){1,3})|(?:(?::[0-9a-f]{1,4})?:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){3}(?:(?:(?::[0-9a-f]{1,4}){1,4})|(?:(?::[0-9a-f]{1,4}){0,2}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){2}(?:(?:(?::[0-9a-f]{1,4}){1,5})|(?:(?::[0-9a-f]{1,4}){0,3}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){1}(?:(?:(?::[0-9a-f]{1,4}){1,6})|(?:(?::[0-9a-f]{1,4}){0,4}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?::(?:(?:(?::[0-9a-f]{1,4}){1,7})|(?:(?::[0-9a-f]{1,4}){0,5}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(?:%.+)?\s*$/i,
  regex: regex,
  uuid: UUID,
  'json-pointer': JSON_POINTER,
  'json-pointer-uri-fragment': JSON_POINTER_URI_FRAGMENT,
  'relative-json-pointer': RELATIVE_JSON_POINTER
};


function isLeapYear(year) {
  // https://tools.ietf.org/html/rfc3339#appendix-C
  return year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0);
}


function date(str) {
  // full-date from http://tools.ietf.org/html/rfc3339#section-5.6
  var matches = str.match(DATE);
  if (!matches) return false;

  var year = +matches[1];
  var month = +matches[2];
  var day = +matches[3];

  return month >= 1 && month <= 12 && day >= 1 &&
          day <= (month == 2 && isLeapYear(year) ? 29 : DAYS[month]);
}


function time(str, full) {
  var matches = str.match(TIME);
  if (!matches) return false;

  var hour = matches[1];
  var minute = matches[2];
  var second = matches[3];
  var timeZone = matches[5];
  return ((hour <= 23 && minute <= 59 && second <= 59) ||
          (hour == 23 && minute == 59 && second == 60)) &&
         (!full || timeZone);
}


var DATE_TIME_SEPARATOR = /t|\s/i;
function date_time(str) {
  // http://tools.ietf.org/html/rfc3339#section-5.6
  var dateTime = str.split(DATE_TIME_SEPARATOR);
  return dateTime.length == 2 && date(dateTime[0]) && time(dateTime[1], true);
}


function hostname(str) {
  // https://tools.ietf.org/html/rfc1034#section-3.5
  // https://tools.ietf.org/html/rfc1123#section-2
  return str.length <= 255 && HOSTNAME.test(str);
}


var NOT_URI_FRAGMENT = /\/|:/;
function uri(str) {
  // http://jmrware.com/articles/2009/uri_regexp/URI_regex.html + optional protocol + required "."
  return NOT_URI_FRAGMENT.test(str) && URI.test(str);
}


var Z_ANCHOR = /[^\\]\\Z/;
function regex(str) {
  if (Z_ANCHOR.test(str)) return false;
  try {
    new RegExp(str);
    return true;
  } catch(e) {
    return false;
  }
}


/***/ }),

/***/ "../../node_modules/ajv/lib/compile/index.js":
/*!**************************************************!*\
  !*** /app/node_modules/ajv/lib/compile/index.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var resolve = __webpack_require__(/*! ./resolve */ "../../node_modules/ajv/lib/compile/resolve.js")
  , util = __webpack_require__(/*! ./util */ "../../node_modules/ajv/lib/compile/util.js")
  , errorClasses = __webpack_require__(/*! ./error_classes */ "../../node_modules/ajv/lib/compile/error_classes.js")
  , stableStringify = __webpack_require__(/*! fast-json-stable-stringify */ "../../node_modules/fast-json-stable-stringify/index.js");

var validateGenerator = __webpack_require__(/*! ../dotjs/validate */ "../../node_modules/ajv/lib/dotjs/validate.js");

/**
 * Functions below are used inside compiled validations function
 */

var ucs2length = util.ucs2length;
var equal = __webpack_require__(/*! fast-deep-equal */ "../../node_modules/fast-deep-equal/index.js");

// this error is thrown by async schemas to return validation errors via exception
var ValidationError = errorClasses.Validation;

module.exports = compile;


/**
 * Compiles schema to validation function
 * @this   Ajv
 * @param  {Object} schema schema object
 * @param  {Object} root object with information about the root schema for this schema
 * @param  {Object} localRefs the hash of local references inside the schema (created by resolve.id), used for inline resolution
 * @param  {String} baseId base ID for IDs in the schema
 * @return {Function} validation function
 */
function compile(schema, root, localRefs, baseId) {
  /* jshint validthis: true, evil: true */
  /* eslint no-shadow: 0 */
  var self = this
    , opts = this._opts
    , refVal = [ undefined ]
    , refs = {}
    , patterns = []
    , patternsHash = {}
    , defaults = []
    , defaultsHash = {}
    , customRules = [];

  root = root || { schema: schema, refVal: refVal, refs: refs };

  var c = checkCompiling.call(this, schema, root, baseId);
  var compilation = this._compilations[c.index];
  if (c.compiling) return (compilation.callValidate = callValidate);

  var formats = this._formats;
  var RULES = this.RULES;

  try {
    var v = localCompile(schema, root, localRefs, baseId);
    compilation.validate = v;
    var cv = compilation.callValidate;
    if (cv) {
      cv.schema = v.schema;
      cv.errors = null;
      cv.refs = v.refs;
      cv.refVal = v.refVal;
      cv.root = v.root;
      cv.$async = v.$async;
      if (opts.sourceCode) cv.source = v.source;
    }
    return v;
  } finally {
    endCompiling.call(this, schema, root, baseId);
  }

  /* @this   {*} - custom context, see passContext option */
  function callValidate() {
    /* jshint validthis: true */
    var validate = compilation.validate;
    var result = validate.apply(this, arguments);
    callValidate.errors = validate.errors;
    return result;
  }

  function localCompile(_schema, _root, localRefs, baseId) {
    var isRoot = !_root || (_root && _root.schema == _schema);
    if (_root.schema != root.schema)
      return compile.call(self, _schema, _root, localRefs, baseId);

    var $async = _schema.$async === true;

    var sourceCode = validateGenerator({
      isTop: true,
      schema: _schema,
      isRoot: isRoot,
      baseId: baseId,
      root: _root,
      schemaPath: '',
      errSchemaPath: '#',
      errorPath: '""',
      MissingRefError: errorClasses.MissingRef,
      RULES: RULES,
      validate: validateGenerator,
      util: util,
      resolve: resolve,
      resolveRef: resolveRef,
      usePattern: usePattern,
      useDefault: useDefault,
      useCustomRule: useCustomRule,
      opts: opts,
      formats: formats,
      logger: self.logger,
      self: self
    });

    sourceCode = vars(refVal, refValCode) + vars(patterns, patternCode)
                   + vars(defaults, defaultCode) + vars(customRules, customRuleCode)
                   + sourceCode;

    if (opts.processCode) sourceCode = opts.processCode(sourceCode);
    // console.log('\n\n\n *** \n', JSON.stringify(sourceCode));
    var validate;
    try {
      var makeValidate = new Function(
        'self',
        'RULES',
        'formats',
        'root',
        'refVal',
        'defaults',
        'customRules',
        'equal',
        'ucs2length',
        'ValidationError',
        sourceCode
      );

      validate = makeValidate(
        self,
        RULES,
        formats,
        root,
        refVal,
        defaults,
        customRules,
        equal,
        ucs2length,
        ValidationError
      );

      refVal[0] = validate;
    } catch(e) {
      self.logger.error('Error compiling schema, function code:', sourceCode);
      throw e;
    }

    validate.schema = _schema;
    validate.errors = null;
    validate.refs = refs;
    validate.refVal = refVal;
    validate.root = isRoot ? validate : _root;
    if ($async) validate.$async = true;
    if (opts.sourceCode === true) {
      validate.source = {
        code: sourceCode,
        patterns: patterns,
        defaults: defaults
      };
    }

    return validate;
  }

  function resolveRef(baseId, ref, isRoot) {
    ref = resolve.url(baseId, ref);
    var refIndex = refs[ref];
    var _refVal, refCode;
    if (refIndex !== undefined) {
      _refVal = refVal[refIndex];
      refCode = 'refVal[' + refIndex + ']';
      return resolvedRef(_refVal, refCode);
    }
    if (!isRoot && root.refs) {
      var rootRefId = root.refs[ref];
      if (rootRefId !== undefined) {
        _refVal = root.refVal[rootRefId];
        refCode = addLocalRef(ref, _refVal);
        return resolvedRef(_refVal, refCode);
      }
    }

    refCode = addLocalRef(ref);
    var v = resolve.call(self, localCompile, root, ref);
    if (v === undefined) {
      var localSchema = localRefs && localRefs[ref];
      if (localSchema) {
        v = resolve.inlineRef(localSchema, opts.inlineRefs)
            ? localSchema
            : compile.call(self, localSchema, root, localRefs, baseId);
      }
    }

    if (v === undefined) {
      removeLocalRef(ref);
    } else {
      replaceLocalRef(ref, v);
      return resolvedRef(v, refCode);
    }
  }

  function addLocalRef(ref, v) {
    var refId = refVal.length;
    refVal[refId] = v;
    refs[ref] = refId;
    return 'refVal' + refId;
  }

  function removeLocalRef(ref) {
    delete refs[ref];
  }

  function replaceLocalRef(ref, v) {
    var refId = refs[ref];
    refVal[refId] = v;
  }

  function resolvedRef(refVal, code) {
    return typeof refVal == 'object' || typeof refVal == 'boolean'
            ? { code: code, schema: refVal, inline: true }
            : { code: code, $async: refVal && !!refVal.$async };
  }

  function usePattern(regexStr) {
    var index = patternsHash[regexStr];
    if (index === undefined) {
      index = patternsHash[regexStr] = patterns.length;
      patterns[index] = regexStr;
    }
    return 'pattern' + index;
  }

  function useDefault(value) {
    switch (typeof value) {
      case 'boolean':
      case 'number':
        return '' + value;
      case 'string':
        return util.toQuotedString(value);
      case 'object':
        if (value === null) return 'null';
        var valueStr = stableStringify(value);
        var index = defaultsHash[valueStr];
        if (index === undefined) {
          index = defaultsHash[valueStr] = defaults.length;
          defaults[index] = value;
        }
        return 'default' + index;
    }
  }

  function useCustomRule(rule, schema, parentSchema, it) {
    var validateSchema = rule.definition.validateSchema;
    if (validateSchema && self._opts.validateSchema !== false) {
      var valid = validateSchema(schema);
      if (!valid) {
        var message = 'keyword schema is invalid: ' + self.errorsText(validateSchema.errors);
        if (self._opts.validateSchema == 'log') self.logger.error(message);
        else throw new Error(message);
      }
    }

    var compile = rule.definition.compile
      , inline = rule.definition.inline
      , macro = rule.definition.macro;

    var validate;
    if (compile) {
      validate = compile.call(self, schema, parentSchema, it);
    } else if (macro) {
      validate = macro.call(self, schema, parentSchema, it);
      if (opts.validateSchema !== false) self.validateSchema(validate, true);
    } else if (inline) {
      validate = inline.call(self, it, rule.keyword, schema, parentSchema);
    } else {
      validate = rule.definition.validate;
      if (!validate) return;
    }

    if (validate === undefined)
      throw new Error('custom keyword "' + rule.keyword + '"failed to compile');

    var index = customRules.length;
    customRules[index] = validate;

    return {
      code: 'customRule' + index,
      validate: validate
    };
  }
}


/**
 * Checks if the schema is currently compiled
 * @this   Ajv
 * @param  {Object} schema schema to compile
 * @param  {Object} root root object
 * @param  {String} baseId base schema ID
 * @return {Object} object with properties "index" (compilation index) and "compiling" (boolean)
 */
function checkCompiling(schema, root, baseId) {
  /* jshint validthis: true */
  var index = compIndex.call(this, schema, root, baseId);
  if (index >= 0) return { index: index, compiling: true };
  index = this._compilations.length;
  this._compilations[index] = {
    schema: schema,
    root: root,
    baseId: baseId
  };
  return { index: index, compiling: false };
}


/**
 * Removes the schema from the currently compiled list
 * @this   Ajv
 * @param  {Object} schema schema to compile
 * @param  {Object} root root object
 * @param  {String} baseId base schema ID
 */
function endCompiling(schema, root, baseId) {
  /* jshint validthis: true */
  var i = compIndex.call(this, schema, root, baseId);
  if (i >= 0) this._compilations.splice(i, 1);
}


/**
 * Index of schema compilation in the currently compiled list
 * @this   Ajv
 * @param  {Object} schema schema to compile
 * @param  {Object} root root object
 * @param  {String} baseId base schema ID
 * @return {Integer} compilation index
 */
function compIndex(schema, root, baseId) {
  /* jshint validthis: true */
  for (var i=0; i<this._compilations.length; i++) {
    var c = this._compilations[i];
    if (c.schema == schema && c.root == root && c.baseId == baseId) return i;
  }
  return -1;
}


function patternCode(i, patterns) {
  return 'var pattern' + i + ' = new RegExp(' + util.toQuotedString(patterns[i]) + ');';
}


function defaultCode(i) {
  return 'var default' + i + ' = defaults[' + i + '];';
}


function refValCode(i, refVal) {
  return refVal[i] === undefined ? '' : 'var refVal' + i + ' = refVal[' + i + '];';
}


function customRuleCode(i) {
  return 'var customRule' + i + ' = customRules[' + i + '];';
}


function vars(arr, statement) {
  if (!arr.length) return '';
  var code = '';
  for (var i=0; i<arr.length; i++)
    code += statement(i, arr);
  return code;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/compile/resolve.js":
/*!****************************************************!*\
  !*** /app/node_modules/ajv/lib/compile/resolve.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var URI = __webpack_require__(/*! uri-js */ "../../node_modules/uri-js/dist/es5/uri.all.js")
  , equal = __webpack_require__(/*! fast-deep-equal */ "../../node_modules/fast-deep-equal/index.js")
  , util = __webpack_require__(/*! ./util */ "../../node_modules/ajv/lib/compile/util.js")
  , SchemaObject = __webpack_require__(/*! ./schema_obj */ "../../node_modules/ajv/lib/compile/schema_obj.js")
  , traverse = __webpack_require__(/*! json-schema-traverse */ "../../node_modules/json-schema-traverse/index.js");

module.exports = resolve;

resolve.normalizeId = normalizeId;
resolve.fullPath = getFullPath;
resolve.url = resolveUrl;
resolve.ids = resolveIds;
resolve.inlineRef = inlineRef;
resolve.schema = resolveSchema;

/**
 * [resolve and compile the references ($ref)]
 * @this   Ajv
 * @param  {Function} compile reference to schema compilation funciton (localCompile)
 * @param  {Object} root object with information about the root schema for the current schema
 * @param  {String} ref reference to resolve
 * @return {Object|Function} schema object (if the schema can be inlined) or validation function
 */
function resolve(compile, root, ref) {
  /* jshint validthis: true */
  var refVal = this._refs[ref];
  if (typeof refVal == 'string') {
    if (this._refs[refVal]) refVal = this._refs[refVal];
    else return resolve.call(this, compile, root, refVal);
  }

  refVal = refVal || this._schemas[ref];
  if (refVal instanceof SchemaObject) {
    return inlineRef(refVal.schema, this._opts.inlineRefs)
            ? refVal.schema
            : refVal.validate || this._compile(refVal);
  }

  var res = resolveSchema.call(this, root, ref);
  var schema, v, baseId;
  if (res) {
    schema = res.schema;
    root = res.root;
    baseId = res.baseId;
  }

  if (schema instanceof SchemaObject) {
    v = schema.validate || compile.call(this, schema.schema, root, undefined, baseId);
  } else if (schema !== undefined) {
    v = inlineRef(schema, this._opts.inlineRefs)
        ? schema
        : compile.call(this, schema, root, undefined, baseId);
  }

  return v;
}


/**
 * Resolve schema, its root and baseId
 * @this Ajv
 * @param  {Object} root root object with properties schema, refVal, refs
 * @param  {String} ref  reference to resolve
 * @return {Object} object with properties schema, root, baseId
 */
function resolveSchema(root, ref) {
  /* jshint validthis: true */
  var p = URI.parse(ref)
    , refPath = _getFullPath(p)
    , baseId = getFullPath(this._getId(root.schema));
  if (Object.keys(root.schema).length === 0 || refPath !== baseId) {
    var id = normalizeId(refPath);
    var refVal = this._refs[id];
    if (typeof refVal == 'string') {
      return resolveRecursive.call(this, root, refVal, p);
    } else if (refVal instanceof SchemaObject) {
      if (!refVal.validate) this._compile(refVal);
      root = refVal;
    } else {
      refVal = this._schemas[id];
      if (refVal instanceof SchemaObject) {
        if (!refVal.validate) this._compile(refVal);
        if (id == normalizeId(ref))
          return { schema: refVal, root: root, baseId: baseId };
        root = refVal;
      } else {
        return;
      }
    }
    if (!root.schema) return;
    baseId = getFullPath(this._getId(root.schema));
  }
  return getJsonPointer.call(this, p, baseId, root.schema, root);
}


/* @this Ajv */
function resolveRecursive(root, ref, parsedRef) {
  /* jshint validthis: true */
  var res = resolveSchema.call(this, root, ref);
  if (res) {
    var schema = res.schema;
    var baseId = res.baseId;
    root = res.root;
    var id = this._getId(schema);
    if (id) baseId = resolveUrl(baseId, id);
    return getJsonPointer.call(this, parsedRef, baseId, schema, root);
  }
}


var PREVENT_SCOPE_CHANGE = util.toHash(['properties', 'patternProperties', 'enum', 'dependencies', 'definitions']);
/* @this Ajv */
function getJsonPointer(parsedRef, baseId, schema, root) {
  /* jshint validthis: true */
  parsedRef.fragment = parsedRef.fragment || '';
  if (parsedRef.fragment.slice(0,1) != '/') return;
  var parts = parsedRef.fragment.split('/');

  for (var i = 1; i < parts.length; i++) {
    var part = parts[i];
    if (part) {
      part = util.unescapeFragment(part);
      schema = schema[part];
      if (schema === undefined) break;
      var id;
      if (!PREVENT_SCOPE_CHANGE[part]) {
        id = this._getId(schema);
        if (id) baseId = resolveUrl(baseId, id);
        if (schema.$ref) {
          var $ref = resolveUrl(baseId, schema.$ref);
          var res = resolveSchema.call(this, root, $ref);
          if (res) {
            schema = res.schema;
            root = res.root;
            baseId = res.baseId;
          }
        }
      }
    }
  }
  if (schema !== undefined && schema !== root.schema)
    return { schema: schema, root: root, baseId: baseId };
}


var SIMPLE_INLINED = util.toHash([
  'type', 'format', 'pattern',
  'maxLength', 'minLength',
  'maxProperties', 'minProperties',
  'maxItems', 'minItems',
  'maximum', 'minimum',
  'uniqueItems', 'multipleOf',
  'required', 'enum'
]);
function inlineRef(schema, limit) {
  if (limit === false) return false;
  if (limit === undefined || limit === true) return checkNoRef(schema);
  else if (limit) return countKeys(schema) <= limit;
}


function checkNoRef(schema) {
  var item;
  if (Array.isArray(schema)) {
    for (var i=0; i<schema.length; i++) {
      item = schema[i];
      if (typeof item == 'object' && !checkNoRef(item)) return false;
    }
  } else {
    for (var key in schema) {
      if (key == '$ref') return false;
      item = schema[key];
      if (typeof item == 'object' && !checkNoRef(item)) return false;
    }
  }
  return true;
}


function countKeys(schema) {
  var count = 0, item;
  if (Array.isArray(schema)) {
    for (var i=0; i<schema.length; i++) {
      item = schema[i];
      if (typeof item == 'object') count += countKeys(item);
      if (count == Infinity) return Infinity;
    }
  } else {
    for (var key in schema) {
      if (key == '$ref') return Infinity;
      if (SIMPLE_INLINED[key]) {
        count++;
      } else {
        item = schema[key];
        if (typeof item == 'object') count += countKeys(item) + 1;
        if (count == Infinity) return Infinity;
      }
    }
  }
  return count;
}


function getFullPath(id, normalize) {
  if (normalize !== false) id = normalizeId(id);
  var p = URI.parse(id);
  return _getFullPath(p);
}


function _getFullPath(p) {
  return URI.serialize(p).split('#')[0] + '#';
}


var TRAILING_SLASH_HASH = /#\/?$/;
function normalizeId(id) {
  return id ? id.replace(TRAILING_SLASH_HASH, '') : '';
}


function resolveUrl(baseId, id) {
  id = normalizeId(id);
  return URI.resolve(baseId, id);
}


/* @this Ajv */
function resolveIds(schema) {
  var schemaId = normalizeId(this._getId(schema));
  var baseIds = {'': schemaId};
  var fullPaths = {'': getFullPath(schemaId, false)};
  var localRefs = {};
  var self = this;

  traverse(schema, {allKeys: true}, function(sch, jsonPtr, rootSchema, parentJsonPtr, parentKeyword, parentSchema, keyIndex) {
    if (jsonPtr === '') return;
    var id = self._getId(sch);
    var baseId = baseIds[parentJsonPtr];
    var fullPath = fullPaths[parentJsonPtr] + '/' + parentKeyword;
    if (keyIndex !== undefined)
      fullPath += '/' + (typeof keyIndex == 'number' ? keyIndex : util.escapeFragment(keyIndex));

    if (typeof id == 'string') {
      id = baseId = normalizeId(baseId ? URI.resolve(baseId, id) : id);

      var refVal = self._refs[id];
      if (typeof refVal == 'string') refVal = self._refs[refVal];
      if (refVal && refVal.schema) {
        if (!equal(sch, refVal.schema))
          throw new Error('id "' + id + '" resolves to more than one schema');
      } else if (id != normalizeId(fullPath)) {
        if (id[0] == '#') {
          if (localRefs[id] && !equal(sch, localRefs[id]))
            throw new Error('id "' + id + '" resolves to more than one schema');
          localRefs[id] = sch;
        } else {
          self._refs[id] = fullPath;
        }
      }
    }
    baseIds[jsonPtr] = baseId;
    fullPaths[jsonPtr] = fullPath;
  });

  return localRefs;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/compile/rules.js":
/*!**************************************************!*\
  !*** /app/node_modules/ajv/lib/compile/rules.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var ruleModules = __webpack_require__(/*! ../dotjs */ "../../node_modules/ajv/lib/dotjs/index.js")
  , toHash = __webpack_require__(/*! ./util */ "../../node_modules/ajv/lib/compile/util.js").toHash;

module.exports = function rules() {
  var RULES = [
    { type: 'number',
      rules: [ { 'maximum': ['exclusiveMaximum'] },
               { 'minimum': ['exclusiveMinimum'] }, 'multipleOf', 'format'] },
    { type: 'string',
      rules: [ 'maxLength', 'minLength', 'pattern', 'format' ] },
    { type: 'array',
      rules: [ 'maxItems', 'minItems', 'items', 'contains', 'uniqueItems' ] },
    { type: 'object',
      rules: [ 'maxProperties', 'minProperties', 'required', 'dependencies', 'propertyNames',
               { 'properties': ['additionalProperties', 'patternProperties'] } ] },
    { rules: [ '$ref', 'const', 'enum', 'not', 'anyOf', 'oneOf', 'allOf', 'if' ] }
  ];

  var ALL = [ 'type', '$comment' ];
  var KEYWORDS = [
    '$schema', '$id', 'id', '$data', 'title',
    'description', 'default', 'definitions',
    'examples', 'readOnly', 'writeOnly',
    'contentMediaType', 'contentEncoding',
    'additionalItems', 'then', 'else'
  ];
  var TYPES = [ 'number', 'integer', 'string', 'array', 'object', 'boolean', 'null' ];
  RULES.all = toHash(ALL);
  RULES.types = toHash(TYPES);

  RULES.forEach(function (group) {
    group.rules = group.rules.map(function (keyword) {
      var implKeywords;
      if (typeof keyword == 'object') {
        var key = Object.keys(keyword)[0];
        implKeywords = keyword[key];
        keyword = key;
        implKeywords.forEach(function (k) {
          ALL.push(k);
          RULES.all[k] = true;
        });
      }
      ALL.push(keyword);
      var rule = RULES.all[keyword] = {
        keyword: keyword,
        code: ruleModules[keyword],
        implements: implKeywords
      };
      return rule;
    });

    RULES.all.$comment = {
      keyword: '$comment',
      code: ruleModules.$comment
    };

    if (group.type) RULES.types[group.type] = group;
  });

  RULES.keywords = toHash(ALL.concat(KEYWORDS));
  RULES.custom = {};

  return RULES;
};


/***/ }),

/***/ "../../node_modules/ajv/lib/compile/schema_obj.js":
/*!*******************************************************!*\
  !*** /app/node_modules/ajv/lib/compile/schema_obj.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var util = __webpack_require__(/*! ./util */ "../../node_modules/ajv/lib/compile/util.js");

module.exports = SchemaObject;

function SchemaObject(obj) {
  util.copy(obj, this);
}


/***/ }),

/***/ "../../node_modules/ajv/lib/compile/ucs2length.js":
/*!*******************************************************!*\
  !*** /app/node_modules/ajv/lib/compile/ucs2length.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// https://mathiasbynens.be/notes/javascript-encoding
// https://github.com/bestiejs/punycode.js - punycode.ucs2.decode
module.exports = function ucs2length(str) {
  var length = 0
    , len = str.length
    , pos = 0
    , value;
  while (pos < len) {
    length++;
    value = str.charCodeAt(pos++);
    if (value >= 0xD800 && value <= 0xDBFF && pos < len) {
      // high surrogate, and there is a next character
      value = str.charCodeAt(pos);
      if ((value & 0xFC00) == 0xDC00) pos++; // low surrogate
    }
  }
  return length;
};


/***/ }),

/***/ "../../node_modules/ajv/lib/compile/util.js":
/*!*************************************************!*\
  !*** /app/node_modules/ajv/lib/compile/util.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";



module.exports = {
  copy: copy,
  checkDataType: checkDataType,
  checkDataTypes: checkDataTypes,
  coerceToTypes: coerceToTypes,
  toHash: toHash,
  getProperty: getProperty,
  escapeQuotes: escapeQuotes,
  equal: __webpack_require__(/*! fast-deep-equal */ "../../node_modules/fast-deep-equal/index.js"),
  ucs2length: __webpack_require__(/*! ./ucs2length */ "../../node_modules/ajv/lib/compile/ucs2length.js"),
  varOccurences: varOccurences,
  varReplace: varReplace,
  cleanUpCode: cleanUpCode,
  finalCleanUpCode: finalCleanUpCode,
  schemaHasRules: schemaHasRules,
  schemaHasRulesExcept: schemaHasRulesExcept,
  toQuotedString: toQuotedString,
  getPathExpr: getPathExpr,
  getPath: getPath,
  getData: getData,
  unescapeFragment: unescapeFragment,
  unescapeJsonPointer: unescapeJsonPointer,
  escapeFragment: escapeFragment,
  escapeJsonPointer: escapeJsonPointer
};


function copy(o, to) {
  to = to || {};
  for (var key in o) to[key] = o[key];
  return to;
}


function checkDataType(dataType, data, negate) {
  var EQUAL = negate ? ' !== ' : ' === '
    , AND = negate ? ' || ' : ' && '
    , OK = negate ? '!' : ''
    , NOT = negate ? '' : '!';
  switch (dataType) {
    case 'null': return data + EQUAL + 'null';
    case 'array': return OK + 'Array.isArray(' + data + ')';
    case 'object': return '(' + OK + data + AND +
                          'typeof ' + data + EQUAL + '"object"' + AND +
                          NOT + 'Array.isArray(' + data + '))';
    case 'integer': return '(typeof ' + data + EQUAL + '"number"' + AND +
                           NOT + '(' + data + ' % 1)' +
                           AND + data + EQUAL + data + ')';
    default: return 'typeof ' + data + EQUAL + '"' + dataType + '"';
  }
}


function checkDataTypes(dataTypes, data) {
  switch (dataTypes.length) {
    case 1: return checkDataType(dataTypes[0], data, true);
    default:
      var code = '';
      var types = toHash(dataTypes);
      if (types.array && types.object) {
        code = types.null ? '(': '(!' + data + ' || ';
        code += 'typeof ' + data + ' !== "object")';
        delete types.null;
        delete types.array;
        delete types.object;
      }
      if (types.number) delete types.integer;
      for (var t in types)
        code += (code ? ' && ' : '' ) + checkDataType(t, data, true);

      return code;
  }
}


var COERCE_TO_TYPES = toHash([ 'string', 'number', 'integer', 'boolean', 'null' ]);
function coerceToTypes(optionCoerceTypes, dataTypes) {
  if (Array.isArray(dataTypes)) {
    var types = [];
    for (var i=0; i<dataTypes.length; i++) {
      var t = dataTypes[i];
      if (COERCE_TO_TYPES[t]) types[types.length] = t;
      else if (optionCoerceTypes === 'array' && t === 'array') types[types.length] = t;
    }
    if (types.length) return types;
  } else if (COERCE_TO_TYPES[dataTypes]) {
    return [dataTypes];
  } else if (optionCoerceTypes === 'array' && dataTypes === 'array') {
    return ['array'];
  }
}


function toHash(arr) {
  var hash = {};
  for (var i=0; i<arr.length; i++) hash[arr[i]] = true;
  return hash;
}


var IDENTIFIER = /^[a-z$_][a-z$_0-9]*$/i;
var SINGLE_QUOTE = /'|\\/g;
function getProperty(key) {
  return typeof key == 'number'
          ? '[' + key + ']'
          : IDENTIFIER.test(key)
            ? '.' + key
            : "['" + escapeQuotes(key) + "']";
}


function escapeQuotes(str) {
  return str.replace(SINGLE_QUOTE, '\\$&')
            .replace(/\n/g, '\\n')
            .replace(/\r/g, '\\r')
            .replace(/\f/g, '\\f')
            .replace(/\t/g, '\\t');
}


function varOccurences(str, dataVar) {
  dataVar += '[^0-9]';
  var matches = str.match(new RegExp(dataVar, 'g'));
  return matches ? matches.length : 0;
}


function varReplace(str, dataVar, expr) {
  dataVar += '([^0-9])';
  expr = expr.replace(/\$/g, '$$$$');
  return str.replace(new RegExp(dataVar, 'g'), expr + '$1');
}


var EMPTY_ELSE = /else\s*{\s*}/g
  , EMPTY_IF_NO_ELSE = /if\s*\([^)]+\)\s*\{\s*\}(?!\s*else)/g
  , EMPTY_IF_WITH_ELSE = /if\s*\(([^)]+)\)\s*\{\s*\}\s*else(?!\s*if)/g;
function cleanUpCode(out) {
  return out.replace(EMPTY_ELSE, '')
            .replace(EMPTY_IF_NO_ELSE, '')
            .replace(EMPTY_IF_WITH_ELSE, 'if (!($1))');
}


var ERRORS_REGEXP = /[^v.]errors/g
  , REMOVE_ERRORS = /var errors = 0;|var vErrors = null;|validate.errors = vErrors;/g
  , REMOVE_ERRORS_ASYNC = /var errors = 0;|var vErrors = null;/g
  , RETURN_VALID = 'return errors === 0;'
  , RETURN_TRUE = 'validate.errors = null; return true;'
  , RETURN_ASYNC = /if \(errors === 0\) return data;\s*else throw new ValidationError\(vErrors\);/
  , RETURN_DATA_ASYNC = 'return data;'
  , ROOTDATA_REGEXP = /[^A-Za-z_$]rootData[^A-Za-z0-9_$]/g
  , REMOVE_ROOTDATA = /if \(rootData === undefined\) rootData = data;/;

function finalCleanUpCode(out, async) {
  var matches = out.match(ERRORS_REGEXP);
  if (matches && matches.length == 2) {
    out = async
          ? out.replace(REMOVE_ERRORS_ASYNC, '')
               .replace(RETURN_ASYNC, RETURN_DATA_ASYNC)
          : out.replace(REMOVE_ERRORS, '')
               .replace(RETURN_VALID, RETURN_TRUE);
  }

  matches = out.match(ROOTDATA_REGEXP);
  if (!matches || matches.length !== 3) return out;
  return out.replace(REMOVE_ROOTDATA, '');
}


function schemaHasRules(schema, rules) {
  if (typeof schema == 'boolean') return !schema;
  for (var key in schema) if (rules[key]) return true;
}


function schemaHasRulesExcept(schema, rules, exceptKeyword) {
  if (typeof schema == 'boolean') return !schema && exceptKeyword != 'not';
  for (var key in schema) if (key != exceptKeyword && rules[key]) return true;
}


function toQuotedString(str) {
  return '\'' + escapeQuotes(str) + '\'';
}


function getPathExpr(currentPath, expr, jsonPointers, isNumber) {
  var path = jsonPointers // false by default
              ? '\'/\' + ' + expr + (isNumber ? '' : '.replace(/~/g, \'~0\').replace(/\\//g, \'~1\')')
              : (isNumber ? '\'[\' + ' + expr + ' + \']\'' : '\'[\\\'\' + ' + expr + ' + \'\\\']\'');
  return joinPaths(currentPath, path);
}


function getPath(currentPath, prop, jsonPointers) {
  var path = jsonPointers // false by default
              ? toQuotedString('/' + escapeJsonPointer(prop))
              : toQuotedString(getProperty(prop));
  return joinPaths(currentPath, path);
}


var JSON_POINTER = /^\/(?:[^~]|~0|~1)*$/;
var RELATIVE_JSON_POINTER = /^([0-9]+)(#|\/(?:[^~]|~0|~1)*)?$/;
function getData($data, lvl, paths) {
  var up, jsonPointer, data, matches;
  if ($data === '') return 'rootData';
  if ($data[0] == '/') {
    if (!JSON_POINTER.test($data)) throw new Error('Invalid JSON-pointer: ' + $data);
    jsonPointer = $data;
    data = 'rootData';
  } else {
    matches = $data.match(RELATIVE_JSON_POINTER);
    if (!matches) throw new Error('Invalid JSON-pointer: ' + $data);
    up = +matches[1];
    jsonPointer = matches[2];
    if (jsonPointer == '#') {
      if (up >= lvl) throw new Error('Cannot access property/index ' + up + ' levels up, current level is ' + lvl);
      return paths[lvl - up];
    }

    if (up > lvl) throw new Error('Cannot access data ' + up + ' levels up, current level is ' + lvl);
    data = 'data' + ((lvl - up) || '');
    if (!jsonPointer) return data;
  }

  var expr = data;
  var segments = jsonPointer.split('/');
  for (var i=0; i<segments.length; i++) {
    var segment = segments[i];
    if (segment) {
      data += getProperty(unescapeJsonPointer(segment));
      expr += ' && ' + data;
    }
  }
  return expr;
}


function joinPaths (a, b) {
  if (a == '""') return b;
  return (a + ' + ' + b).replace(/' \+ '/g, '');
}


function unescapeFragment(str) {
  return unescapeJsonPointer(decodeURIComponent(str));
}


function escapeFragment(str) {
  return encodeURIComponent(escapeJsonPointer(str));
}


function escapeJsonPointer(str) {
  return str.replace(/~/g, '~0').replace(/\//g, '~1');
}


function unescapeJsonPointer(str) {
  return str.replace(/~1/g, '/').replace(/~0/g, '~');
}


/***/ }),

/***/ "../../node_modules/ajv/lib/data.js":
/*!*****************************************!*\
  !*** /app/node_modules/ajv/lib/data.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var KEYWORDS = [
  'multipleOf',
  'maximum',
  'exclusiveMaximum',
  'minimum',
  'exclusiveMinimum',
  'maxLength',
  'minLength',
  'pattern',
  'additionalItems',
  'maxItems',
  'minItems',
  'uniqueItems',
  'maxProperties',
  'minProperties',
  'required',
  'additionalProperties',
  'enum',
  'format',
  'const'
];

module.exports = function (metaSchema, keywordsJsonPointers) {
  for (var i=0; i<keywordsJsonPointers.length; i++) {
    metaSchema = JSON.parse(JSON.stringify(metaSchema));
    var segments = keywordsJsonPointers[i].split('/');
    var keywords = metaSchema;
    var j;
    for (j=1; j<segments.length; j++)
      keywords = keywords[segments[j]];

    for (j=0; j<KEYWORDS.length; j++) {
      var key = KEYWORDS[j];
      var schema = keywords[key];
      if (schema) {
        keywords[key] = {
          anyOf: [
            schema,
            { $ref: 'https://raw.githubusercontent.com/epoberezkin/ajv/master/lib/refs/data.json#' }
          ]
        };
      }
    }
  }

  return metaSchema;
};


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/_limit.js":
/*!*************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/_limit.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate__limit(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $errorKeyword;
  var $data = 'data' + ($dataLvl || '');
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  var $isMax = $keyword == 'maximum',
    $exclusiveKeyword = $isMax ? 'exclusiveMaximum' : 'exclusiveMinimum',
    $schemaExcl = it.schema[$exclusiveKeyword],
    $isDataExcl = it.opts.$data && $schemaExcl && $schemaExcl.$data,
    $op = $isMax ? '<' : '>',
    $notOp = $isMax ? '>' : '<',
    $errorKeyword = undefined;
  if ($isDataExcl) {
    var $schemaValueExcl = it.util.getData($schemaExcl.$data, $dataLvl, it.dataPathArr),
      $exclusive = 'exclusive' + $lvl,
      $exclType = 'exclType' + $lvl,
      $exclIsNumber = 'exclIsNumber' + $lvl,
      $opExpr = 'op' + $lvl,
      $opStr = '\' + ' + $opExpr + ' + \'';
    out += ' var schemaExcl' + ($lvl) + ' = ' + ($schemaValueExcl) + '; ';
    $schemaValueExcl = 'schemaExcl' + $lvl;
    out += ' var ' + ($exclusive) + '; var ' + ($exclType) + ' = typeof ' + ($schemaValueExcl) + '; if (' + ($exclType) + ' != \'boolean\' && ' + ($exclType) + ' != \'undefined\' && ' + ($exclType) + ' != \'number\') { ';
    var $errorKeyword = $exclusiveKeyword;
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = ''; /* istanbul ignore else */
    if (it.createErrors !== false) {
      out += ' { keyword: \'' + ($errorKeyword || '_exclusiveLimit') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: {} ';
      if (it.opts.messages !== false) {
        out += ' , message: \'' + ($exclusiveKeyword) + ' should be boolean\' ';
      }
      if (it.opts.verbose) {
        out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
      }
      out += ' } ';
    } else {
      out += ' {} ';
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it.compositeRule && $breakOnError) {
      /* istanbul ignore if */
      if (it.async) {
        out += ' throw new ValidationError([' + (__err) + ']); ';
      } else {
        out += ' validate.errors = [' + (__err) + ']; return false; ';
      }
    } else {
      out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
    }
    out += ' } else if ( ';
    if ($isData) {
      out += ' (' + ($schemaValue) + ' !== undefined && typeof ' + ($schemaValue) + ' != \'number\') || ';
    }
    out += ' ' + ($exclType) + ' == \'number\' ? ( (' + ($exclusive) + ' = ' + ($schemaValue) + ' === undefined || ' + ($schemaValueExcl) + ' ' + ($op) + '= ' + ($schemaValue) + ') ? ' + ($data) + ' ' + ($notOp) + '= ' + ($schemaValueExcl) + ' : ' + ($data) + ' ' + ($notOp) + ' ' + ($schemaValue) + ' ) : ( (' + ($exclusive) + ' = ' + ($schemaValueExcl) + ' === true) ? ' + ($data) + ' ' + ($notOp) + '= ' + ($schemaValue) + ' : ' + ($data) + ' ' + ($notOp) + ' ' + ($schemaValue) + ' ) || ' + ($data) + ' !== ' + ($data) + ') { var op' + ($lvl) + ' = ' + ($exclusive) + ' ? \'' + ($op) + '\' : \'' + ($op) + '=\'; ';
    if ($schema === undefined) {
      $errorKeyword = $exclusiveKeyword;
      $errSchemaPath = it.errSchemaPath + '/' + $exclusiveKeyword;
      $schemaValue = $schemaValueExcl;
      $isData = $isDataExcl;
    }
  } else {
    var $exclIsNumber = typeof $schemaExcl == 'number',
      $opStr = $op;
    if ($exclIsNumber && $isData) {
      var $opExpr = '\'' + $opStr + '\'';
      out += ' if ( ';
      if ($isData) {
        out += ' (' + ($schemaValue) + ' !== undefined && typeof ' + ($schemaValue) + ' != \'number\') || ';
      }
      out += ' ( ' + ($schemaValue) + ' === undefined || ' + ($schemaExcl) + ' ' + ($op) + '= ' + ($schemaValue) + ' ? ' + ($data) + ' ' + ($notOp) + '= ' + ($schemaExcl) + ' : ' + ($data) + ' ' + ($notOp) + ' ' + ($schemaValue) + ' ) || ' + ($data) + ' !== ' + ($data) + ') { ';
    } else {
      if ($exclIsNumber && $schema === undefined) {
        $exclusive = true;
        $errorKeyword = $exclusiveKeyword;
        $errSchemaPath = it.errSchemaPath + '/' + $exclusiveKeyword;
        $schemaValue = $schemaExcl;
        $notOp += '=';
      } else {
        if ($exclIsNumber) $schemaValue = Math[$isMax ? 'min' : 'max']($schemaExcl, $schema);
        if ($schemaExcl === ($exclIsNumber ? $schemaValue : true)) {
          $exclusive = true;
          $errorKeyword = $exclusiveKeyword;
          $errSchemaPath = it.errSchemaPath + '/' + $exclusiveKeyword;
          $notOp += '=';
        } else {
          $exclusive = false;
          $opStr += '=';
        }
      }
      var $opExpr = '\'' + $opStr + '\'';
      out += ' if ( ';
      if ($isData) {
        out += ' (' + ($schemaValue) + ' !== undefined && typeof ' + ($schemaValue) + ' != \'number\') || ';
      }
      out += ' ' + ($data) + ' ' + ($notOp) + ' ' + ($schemaValue) + ' || ' + ($data) + ' !== ' + ($data) + ') { ';
    }
  }
  $errorKeyword = $errorKeyword || $keyword;
  var $$outStack = $$outStack || [];
  $$outStack.push(out);
  out = ''; /* istanbul ignore else */
  if (it.createErrors !== false) {
    out += ' { keyword: \'' + ($errorKeyword || '_limit') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { comparison: ' + ($opExpr) + ', limit: ' + ($schemaValue) + ', exclusive: ' + ($exclusive) + ' } ';
    if (it.opts.messages !== false) {
      out += ' , message: \'should be ' + ($opStr) + ' ';
      if ($isData) {
        out += '\' + ' + ($schemaValue);
      } else {
        out += '' + ($schemaValue) + '\'';
      }
    }
    if (it.opts.verbose) {
      out += ' , schema:  ';
      if ($isData) {
        out += 'validate.schema' + ($schemaPath);
      } else {
        out += '' + ($schema);
      }
      out += '         , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
    }
    out += ' } ';
  } else {
    out += ' {} ';
  }
  var __err = out;
  out = $$outStack.pop();
  if (!it.compositeRule && $breakOnError) {
    /* istanbul ignore if */
    if (it.async) {
      out += ' throw new ValidationError([' + (__err) + ']); ';
    } else {
      out += ' validate.errors = [' + (__err) + ']; return false; ';
    }
  } else {
    out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
  }
  out += ' } ';
  if ($breakOnError) {
    out += ' else { ';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/_limitItems.js":
/*!******************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/_limitItems.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate__limitItems(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $errorKeyword;
  var $data = 'data' + ($dataLvl || '');
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  var $op = $keyword == 'maxItems' ? '>' : '<';
  out += 'if ( ';
  if ($isData) {
    out += ' (' + ($schemaValue) + ' !== undefined && typeof ' + ($schemaValue) + ' != \'number\') || ';
  }
  out += ' ' + ($data) + '.length ' + ($op) + ' ' + ($schemaValue) + ') { ';
  var $errorKeyword = $keyword;
  var $$outStack = $$outStack || [];
  $$outStack.push(out);
  out = ''; /* istanbul ignore else */
  if (it.createErrors !== false) {
    out += ' { keyword: \'' + ($errorKeyword || '_limitItems') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { limit: ' + ($schemaValue) + ' } ';
    if (it.opts.messages !== false) {
      out += ' , message: \'should NOT have ';
      if ($keyword == 'maxItems') {
        out += 'more';
      } else {
        out += 'fewer';
      }
      out += ' than ';
      if ($isData) {
        out += '\' + ' + ($schemaValue) + ' + \'';
      } else {
        out += '' + ($schema);
      }
      out += ' items\' ';
    }
    if (it.opts.verbose) {
      out += ' , schema:  ';
      if ($isData) {
        out += 'validate.schema' + ($schemaPath);
      } else {
        out += '' + ($schema);
      }
      out += '         , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
    }
    out += ' } ';
  } else {
    out += ' {} ';
  }
  var __err = out;
  out = $$outStack.pop();
  if (!it.compositeRule && $breakOnError) {
    /* istanbul ignore if */
    if (it.async) {
      out += ' throw new ValidationError([' + (__err) + ']); ';
    } else {
      out += ' validate.errors = [' + (__err) + ']; return false; ';
    }
  } else {
    out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
  }
  out += '} ';
  if ($breakOnError) {
    out += ' else { ';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/_limitLength.js":
/*!*******************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/_limitLength.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate__limitLength(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $errorKeyword;
  var $data = 'data' + ($dataLvl || '');
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  var $op = $keyword == 'maxLength' ? '>' : '<';
  out += 'if ( ';
  if ($isData) {
    out += ' (' + ($schemaValue) + ' !== undefined && typeof ' + ($schemaValue) + ' != \'number\') || ';
  }
  if (it.opts.unicode === false) {
    out += ' ' + ($data) + '.length ';
  } else {
    out += ' ucs2length(' + ($data) + ') ';
  }
  out += ' ' + ($op) + ' ' + ($schemaValue) + ') { ';
  var $errorKeyword = $keyword;
  var $$outStack = $$outStack || [];
  $$outStack.push(out);
  out = ''; /* istanbul ignore else */
  if (it.createErrors !== false) {
    out += ' { keyword: \'' + ($errorKeyword || '_limitLength') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { limit: ' + ($schemaValue) + ' } ';
    if (it.opts.messages !== false) {
      out += ' , message: \'should NOT be ';
      if ($keyword == 'maxLength') {
        out += 'longer';
      } else {
        out += 'shorter';
      }
      out += ' than ';
      if ($isData) {
        out += '\' + ' + ($schemaValue) + ' + \'';
      } else {
        out += '' + ($schema);
      }
      out += ' characters\' ';
    }
    if (it.opts.verbose) {
      out += ' , schema:  ';
      if ($isData) {
        out += 'validate.schema' + ($schemaPath);
      } else {
        out += '' + ($schema);
      }
      out += '         , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
    }
    out += ' } ';
  } else {
    out += ' {} ';
  }
  var __err = out;
  out = $$outStack.pop();
  if (!it.compositeRule && $breakOnError) {
    /* istanbul ignore if */
    if (it.async) {
      out += ' throw new ValidationError([' + (__err) + ']); ';
    } else {
      out += ' validate.errors = [' + (__err) + ']; return false; ';
    }
  } else {
    out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
  }
  out += '} ';
  if ($breakOnError) {
    out += ' else { ';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/_limitProperties.js":
/*!***********************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/_limitProperties.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate__limitProperties(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $errorKeyword;
  var $data = 'data' + ($dataLvl || '');
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  var $op = $keyword == 'maxProperties' ? '>' : '<';
  out += 'if ( ';
  if ($isData) {
    out += ' (' + ($schemaValue) + ' !== undefined && typeof ' + ($schemaValue) + ' != \'number\') || ';
  }
  out += ' Object.keys(' + ($data) + ').length ' + ($op) + ' ' + ($schemaValue) + ') { ';
  var $errorKeyword = $keyword;
  var $$outStack = $$outStack || [];
  $$outStack.push(out);
  out = ''; /* istanbul ignore else */
  if (it.createErrors !== false) {
    out += ' { keyword: \'' + ($errorKeyword || '_limitProperties') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { limit: ' + ($schemaValue) + ' } ';
    if (it.opts.messages !== false) {
      out += ' , message: \'should NOT have ';
      if ($keyword == 'maxProperties') {
        out += 'more';
      } else {
        out += 'fewer';
      }
      out += ' than ';
      if ($isData) {
        out += '\' + ' + ($schemaValue) + ' + \'';
      } else {
        out += '' + ($schema);
      }
      out += ' properties\' ';
    }
    if (it.opts.verbose) {
      out += ' , schema:  ';
      if ($isData) {
        out += 'validate.schema' + ($schemaPath);
      } else {
        out += '' + ($schema);
      }
      out += '         , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
    }
    out += ' } ';
  } else {
    out += ' {} ';
  }
  var __err = out;
  out = $$outStack.pop();
  if (!it.compositeRule && $breakOnError) {
    /* istanbul ignore if */
    if (it.async) {
      out += ' throw new ValidationError([' + (__err) + ']); ';
    } else {
      out += ' validate.errors = [' + (__err) + ']; return false; ';
    }
  } else {
    out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
  }
  out += '} ';
  if ($breakOnError) {
    out += ' else { ';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/allOf.js":
/*!************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/allOf.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_allOf(it, $keyword, $ruleType) {
  var out = ' ';
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $it = it.util.copy(it);
  var $closingBraces = '';
  $it.level++;
  var $nextValid = 'valid' + $it.level;
  var $currentBaseId = $it.baseId,
    $allSchemasEmpty = true;
  var arr1 = $schema;
  if (arr1) {
    var $sch, $i = -1,
      l1 = arr1.length - 1;
    while ($i < l1) {
      $sch = arr1[$i += 1];
      if (it.util.schemaHasRules($sch, it.RULES.all)) {
        $allSchemasEmpty = false;
        $it.schema = $sch;
        $it.schemaPath = $schemaPath + '[' + $i + ']';
        $it.errSchemaPath = $errSchemaPath + '/' + $i;
        out += '  ' + (it.validate($it)) + ' ';
        $it.baseId = $currentBaseId;
        if ($breakOnError) {
          out += ' if (' + ($nextValid) + ') { ';
          $closingBraces += '}';
        }
      }
    }
  }
  if ($breakOnError) {
    if ($allSchemasEmpty) {
      out += ' if (true) { ';
    } else {
      out += ' ' + ($closingBraces.slice(0, -1)) + ' ';
    }
  }
  out = it.util.cleanUpCode(out);
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/anyOf.js":
/*!************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/anyOf.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_anyOf(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $valid = 'valid' + $lvl;
  var $errs = 'errs__' + $lvl;
  var $it = it.util.copy(it);
  var $closingBraces = '';
  $it.level++;
  var $nextValid = 'valid' + $it.level;
  var $noEmptySchema = $schema.every(function($sch) {
    return it.util.schemaHasRules($sch, it.RULES.all);
  });
  if ($noEmptySchema) {
    var $currentBaseId = $it.baseId;
    out += ' var ' + ($errs) + ' = errors; var ' + ($valid) + ' = false;  ';
    var $wasComposite = it.compositeRule;
    it.compositeRule = $it.compositeRule = true;
    var arr1 = $schema;
    if (arr1) {
      var $sch, $i = -1,
        l1 = arr1.length - 1;
      while ($i < l1) {
        $sch = arr1[$i += 1];
        $it.schema = $sch;
        $it.schemaPath = $schemaPath + '[' + $i + ']';
        $it.errSchemaPath = $errSchemaPath + '/' + $i;
        out += '  ' + (it.validate($it)) + ' ';
        $it.baseId = $currentBaseId;
        out += ' ' + ($valid) + ' = ' + ($valid) + ' || ' + ($nextValid) + '; if (!' + ($valid) + ') { ';
        $closingBraces += '}';
      }
    }
    it.compositeRule = $it.compositeRule = $wasComposite;
    out += ' ' + ($closingBraces) + ' if (!' + ($valid) + ') {   var err =   '; /* istanbul ignore else */
    if (it.createErrors !== false) {
      out += ' { keyword: \'' + ('anyOf') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: {} ';
      if (it.opts.messages !== false) {
        out += ' , message: \'should match some schema in anyOf\' ';
      }
      if (it.opts.verbose) {
        out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
      }
      out += ' } ';
    } else {
      out += ' {} ';
    }
    out += ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
    if (!it.compositeRule && $breakOnError) {
      /* istanbul ignore if */
      if (it.async) {
        out += ' throw new ValidationError(vErrors); ';
      } else {
        out += ' validate.errors = vErrors; return false; ';
      }
    }
    out += ' } else {  errors = ' + ($errs) + '; if (vErrors !== null) { if (' + ($errs) + ') vErrors.length = ' + ($errs) + '; else vErrors = null; } ';
    if (it.opts.allErrors) {
      out += ' } ';
    }
    out = it.util.cleanUpCode(out);
  } else {
    if ($breakOnError) {
      out += ' if (true) { ';
    }
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/comment.js":
/*!**************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/comment.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_comment(it, $keyword, $ruleType) {
  var out = ' ';
  var $schema = it.schema[$keyword];
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $comment = it.util.toQuotedString($schema);
  if (it.opts.$comment === true) {
    out += ' console.log(' + ($comment) + ');';
  } else if (typeof it.opts.$comment == 'function') {
    out += ' self._opts.$comment(' + ($comment) + ', ' + (it.util.toQuotedString($errSchemaPath)) + ', validate.root.schema);';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/const.js":
/*!************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/const.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_const(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $valid = 'valid' + $lvl;
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  if (!$isData) {
    out += ' var schema' + ($lvl) + ' = validate.schema' + ($schemaPath) + ';';
  }
  out += 'var ' + ($valid) + ' = equal(' + ($data) + ', schema' + ($lvl) + '); if (!' + ($valid) + ') {   ';
  var $$outStack = $$outStack || [];
  $$outStack.push(out);
  out = ''; /* istanbul ignore else */
  if (it.createErrors !== false) {
    out += ' { keyword: \'' + ('const') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { allowedValue: schema' + ($lvl) + ' } ';
    if (it.opts.messages !== false) {
      out += ' , message: \'should be equal to constant\' ';
    }
    if (it.opts.verbose) {
      out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
    }
    out += ' } ';
  } else {
    out += ' {} ';
  }
  var __err = out;
  out = $$outStack.pop();
  if (!it.compositeRule && $breakOnError) {
    /* istanbul ignore if */
    if (it.async) {
      out += ' throw new ValidationError([' + (__err) + ']); ';
    } else {
      out += ' validate.errors = [' + (__err) + ']; return false; ';
    }
  } else {
    out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
  }
  out += ' }';
  if ($breakOnError) {
    out += ' else { ';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/contains.js":
/*!***************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/contains.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_contains(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $valid = 'valid' + $lvl;
  var $errs = 'errs__' + $lvl;
  var $it = it.util.copy(it);
  var $closingBraces = '';
  $it.level++;
  var $nextValid = 'valid' + $it.level;
  var $idx = 'i' + $lvl,
    $dataNxt = $it.dataLevel = it.dataLevel + 1,
    $nextData = 'data' + $dataNxt,
    $currentBaseId = it.baseId,
    $nonEmptySchema = it.util.schemaHasRules($schema, it.RULES.all);
  out += 'var ' + ($errs) + ' = errors;var ' + ($valid) + ';';
  if ($nonEmptySchema) {
    var $wasComposite = it.compositeRule;
    it.compositeRule = $it.compositeRule = true;
    $it.schema = $schema;
    $it.schemaPath = $schemaPath;
    $it.errSchemaPath = $errSchemaPath;
    out += ' var ' + ($nextValid) + ' = false; for (var ' + ($idx) + ' = 0; ' + ($idx) + ' < ' + ($data) + '.length; ' + ($idx) + '++) { ';
    $it.errorPath = it.util.getPathExpr(it.errorPath, $idx, it.opts.jsonPointers, true);
    var $passData = $data + '[' + $idx + ']';
    $it.dataPathArr[$dataNxt] = $idx;
    var $code = it.validate($it);
    $it.baseId = $currentBaseId;
    if (it.util.varOccurences($code, $nextData) < 2) {
      out += ' ' + (it.util.varReplace($code, $nextData, $passData)) + ' ';
    } else {
      out += ' var ' + ($nextData) + ' = ' + ($passData) + '; ' + ($code) + ' ';
    }
    out += ' if (' + ($nextValid) + ') break; }  ';
    it.compositeRule = $it.compositeRule = $wasComposite;
    out += ' ' + ($closingBraces) + ' if (!' + ($nextValid) + ') {';
  } else {
    out += ' if (' + ($data) + '.length == 0) {';
  }
  var $$outStack = $$outStack || [];
  $$outStack.push(out);
  out = ''; /* istanbul ignore else */
  if (it.createErrors !== false) {
    out += ' { keyword: \'' + ('contains') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: {} ';
    if (it.opts.messages !== false) {
      out += ' , message: \'should contain a valid item\' ';
    }
    if (it.opts.verbose) {
      out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
    }
    out += ' } ';
  } else {
    out += ' {} ';
  }
  var __err = out;
  out = $$outStack.pop();
  if (!it.compositeRule && $breakOnError) {
    /* istanbul ignore if */
    if (it.async) {
      out += ' throw new ValidationError([' + (__err) + ']); ';
    } else {
      out += ' validate.errors = [' + (__err) + ']; return false; ';
    }
  } else {
    out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
  }
  out += ' } else { ';
  if ($nonEmptySchema) {
    out += '  errors = ' + ($errs) + '; if (vErrors !== null) { if (' + ($errs) + ') vErrors.length = ' + ($errs) + '; else vErrors = null; } ';
  }
  if (it.opts.allErrors) {
    out += ' } ';
  }
  out = it.util.cleanUpCode(out);
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/custom.js":
/*!*************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/custom.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_custom(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $errorKeyword;
  var $data = 'data' + ($dataLvl || '');
  var $valid = 'valid' + $lvl;
  var $errs = 'errs__' + $lvl;
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  var $rule = this,
    $definition = 'definition' + $lvl,
    $rDef = $rule.definition,
    $closingBraces = '';
  var $compile, $inline, $macro, $ruleValidate, $validateCode;
  if ($isData && $rDef.$data) {
    $validateCode = 'keywordValidate' + $lvl;
    var $validateSchema = $rDef.validateSchema;
    out += ' var ' + ($definition) + ' = RULES.custom[\'' + ($keyword) + '\'].definition; var ' + ($validateCode) + ' = ' + ($definition) + '.validate;';
  } else {
    $ruleValidate = it.useCustomRule($rule, $schema, it.schema, it);
    if (!$ruleValidate) return;
    $schemaValue = 'validate.schema' + $schemaPath;
    $validateCode = $ruleValidate.code;
    $compile = $rDef.compile;
    $inline = $rDef.inline;
    $macro = $rDef.macro;
  }
  var $ruleErrs = $validateCode + '.errors',
    $i = 'i' + $lvl,
    $ruleErr = 'ruleErr' + $lvl,
    $asyncKeyword = $rDef.async;
  if ($asyncKeyword && !it.async) throw new Error('async keyword in sync schema');
  if (!($inline || $macro)) {
    out += '' + ($ruleErrs) + ' = null;';
  }
  out += 'var ' + ($errs) + ' = errors;var ' + ($valid) + ';';
  if ($isData && $rDef.$data) {
    $closingBraces += '}';
    out += ' if (' + ($schemaValue) + ' === undefined) { ' + ($valid) + ' = true; } else { ';
    if ($validateSchema) {
      $closingBraces += '}';
      out += ' ' + ($valid) + ' = ' + ($definition) + '.validateSchema(' + ($schemaValue) + '); if (' + ($valid) + ') { ';
    }
  }
  if ($inline) {
    if ($rDef.statements) {
      out += ' ' + ($ruleValidate.validate) + ' ';
    } else {
      out += ' ' + ($valid) + ' = ' + ($ruleValidate.validate) + '; ';
    }
  } else if ($macro) {
    var $it = it.util.copy(it);
    var $closingBraces = '';
    $it.level++;
    var $nextValid = 'valid' + $it.level;
    $it.schema = $ruleValidate.validate;
    $it.schemaPath = '';
    var $wasComposite = it.compositeRule;
    it.compositeRule = $it.compositeRule = true;
    var $code = it.validate($it).replace(/validate\.schema/g, $validateCode);
    it.compositeRule = $it.compositeRule = $wasComposite;
    out += ' ' + ($code);
  } else {
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = '';
    out += '  ' + ($validateCode) + '.call( ';
    if (it.opts.passContext) {
      out += 'this';
    } else {
      out += 'self';
    }
    if ($compile || $rDef.schema === false) {
      out += ' , ' + ($data) + ' ';
    } else {
      out += ' , ' + ($schemaValue) + ' , ' + ($data) + ' , validate.schema' + (it.schemaPath) + ' ';
    }
    out += ' , (dataPath || \'\')';
    if (it.errorPath != '""') {
      out += ' + ' + (it.errorPath);
    }
    var $parentData = $dataLvl ? 'data' + (($dataLvl - 1) || '') : 'parentData',
      $parentDataProperty = $dataLvl ? it.dataPathArr[$dataLvl] : 'parentDataProperty';
    out += ' , ' + ($parentData) + ' , ' + ($parentDataProperty) + ' , rootData )  ';
    var def_callRuleValidate = out;
    out = $$outStack.pop();
    if ($rDef.errors === false) {
      out += ' ' + ($valid) + ' = ';
      if ($asyncKeyword) {
        out += 'await ';
      }
      out += '' + (def_callRuleValidate) + '; ';
    } else {
      if ($asyncKeyword) {
        $ruleErrs = 'customErrors' + $lvl;
        out += ' var ' + ($ruleErrs) + ' = null; try { ' + ($valid) + ' = await ' + (def_callRuleValidate) + '; } catch (e) { ' + ($valid) + ' = false; if (e instanceof ValidationError) ' + ($ruleErrs) + ' = e.errors; else throw e; } ';
      } else {
        out += ' ' + ($ruleErrs) + ' = null; ' + ($valid) + ' = ' + (def_callRuleValidate) + '; ';
      }
    }
  }
  if ($rDef.modifying) {
    out += ' if (' + ($parentData) + ') ' + ($data) + ' = ' + ($parentData) + '[' + ($parentDataProperty) + '];';
  }
  out += '' + ($closingBraces);
  if ($rDef.valid) {
    if ($breakOnError) {
      out += ' if (true) { ';
    }
  } else {
    out += ' if ( ';
    if ($rDef.valid === undefined) {
      out += ' !';
      if ($macro) {
        out += '' + ($nextValid);
      } else {
        out += '' + ($valid);
      }
    } else {
      out += ' ' + (!$rDef.valid) + ' ';
    }
    out += ') { ';
    $errorKeyword = $rule.keyword;
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = '';
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = ''; /* istanbul ignore else */
    if (it.createErrors !== false) {
      out += ' { keyword: \'' + ($errorKeyword || 'custom') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { keyword: \'' + ($rule.keyword) + '\' } ';
      if (it.opts.messages !== false) {
        out += ' , message: \'should pass "' + ($rule.keyword) + '" keyword validation\' ';
      }
      if (it.opts.verbose) {
        out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
      }
      out += ' } ';
    } else {
      out += ' {} ';
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it.compositeRule && $breakOnError) {
      /* istanbul ignore if */
      if (it.async) {
        out += ' throw new ValidationError([' + (__err) + ']); ';
      } else {
        out += ' validate.errors = [' + (__err) + ']; return false; ';
      }
    } else {
      out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
    }
    var def_customError = out;
    out = $$outStack.pop();
    if ($inline) {
      if ($rDef.errors) {
        if ($rDef.errors != 'full') {
          out += '  for (var ' + ($i) + '=' + ($errs) + '; ' + ($i) + '<errors; ' + ($i) + '++) { var ' + ($ruleErr) + ' = vErrors[' + ($i) + ']; if (' + ($ruleErr) + '.dataPath === undefined) ' + ($ruleErr) + '.dataPath = (dataPath || \'\') + ' + (it.errorPath) + '; if (' + ($ruleErr) + '.schemaPath === undefined) { ' + ($ruleErr) + '.schemaPath = "' + ($errSchemaPath) + '"; } ';
          if (it.opts.verbose) {
            out += ' ' + ($ruleErr) + '.schema = ' + ($schemaValue) + '; ' + ($ruleErr) + '.data = ' + ($data) + '; ';
          }
          out += ' } ';
        }
      } else {
        if ($rDef.errors === false) {
          out += ' ' + (def_customError) + ' ';
        } else {
          out += ' if (' + ($errs) + ' == errors) { ' + (def_customError) + ' } else {  for (var ' + ($i) + '=' + ($errs) + '; ' + ($i) + '<errors; ' + ($i) + '++) { var ' + ($ruleErr) + ' = vErrors[' + ($i) + ']; if (' + ($ruleErr) + '.dataPath === undefined) ' + ($ruleErr) + '.dataPath = (dataPath || \'\') + ' + (it.errorPath) + '; if (' + ($ruleErr) + '.schemaPath === undefined) { ' + ($ruleErr) + '.schemaPath = "' + ($errSchemaPath) + '"; } ';
          if (it.opts.verbose) {
            out += ' ' + ($ruleErr) + '.schema = ' + ($schemaValue) + '; ' + ($ruleErr) + '.data = ' + ($data) + '; ';
          }
          out += ' } } ';
        }
      }
    } else if ($macro) {
      out += '   var err =   '; /* istanbul ignore else */
      if (it.createErrors !== false) {
        out += ' { keyword: \'' + ($errorKeyword || 'custom') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { keyword: \'' + ($rule.keyword) + '\' } ';
        if (it.opts.messages !== false) {
          out += ' , message: \'should pass "' + ($rule.keyword) + '" keyword validation\' ';
        }
        if (it.opts.verbose) {
          out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
        }
        out += ' } ';
      } else {
        out += ' {} ';
      }
      out += ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
      if (!it.compositeRule && $breakOnError) {
        /* istanbul ignore if */
        if (it.async) {
          out += ' throw new ValidationError(vErrors); ';
        } else {
          out += ' validate.errors = vErrors; return false; ';
        }
      }
    } else {
      if ($rDef.errors === false) {
        out += ' ' + (def_customError) + ' ';
      } else {
        out += ' if (Array.isArray(' + ($ruleErrs) + ')) { if (vErrors === null) vErrors = ' + ($ruleErrs) + '; else vErrors = vErrors.concat(' + ($ruleErrs) + '); errors = vErrors.length;  for (var ' + ($i) + '=' + ($errs) + '; ' + ($i) + '<errors; ' + ($i) + '++) { var ' + ($ruleErr) + ' = vErrors[' + ($i) + ']; if (' + ($ruleErr) + '.dataPath === undefined) ' + ($ruleErr) + '.dataPath = (dataPath || \'\') + ' + (it.errorPath) + ';  ' + ($ruleErr) + '.schemaPath = "' + ($errSchemaPath) + '";  ';
        if (it.opts.verbose) {
          out += ' ' + ($ruleErr) + '.schema = ' + ($schemaValue) + '; ' + ($ruleErr) + '.data = ' + ($data) + '; ';
        }
        out += ' } } else { ' + (def_customError) + ' } ';
      }
    }
    out += ' } ';
    if ($breakOnError) {
      out += ' else { ';
    }
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/dependencies.js":
/*!*******************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/dependencies.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_dependencies(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $errs = 'errs__' + $lvl;
  var $it = it.util.copy(it);
  var $closingBraces = '';
  $it.level++;
  var $nextValid = 'valid' + $it.level;
  var $schemaDeps = {},
    $propertyDeps = {},
    $ownProperties = it.opts.ownProperties;
  for ($property in $schema) {
    var $sch = $schema[$property];
    var $deps = Array.isArray($sch) ? $propertyDeps : $schemaDeps;
    $deps[$property] = $sch;
  }
  out += 'var ' + ($errs) + ' = errors;';
  var $currentErrorPath = it.errorPath;
  out += 'var missing' + ($lvl) + ';';
  for (var $property in $propertyDeps) {
    $deps = $propertyDeps[$property];
    if ($deps.length) {
      out += ' if ( ' + ($data) + (it.util.getProperty($property)) + ' !== undefined ';
      if ($ownProperties) {
        out += ' && Object.prototype.hasOwnProperty.call(' + ($data) + ', \'' + (it.util.escapeQuotes($property)) + '\') ';
      }
      if ($breakOnError) {
        out += ' && ( ';
        var arr1 = $deps;
        if (arr1) {
          var $propertyKey, $i = -1,
            l1 = arr1.length - 1;
          while ($i < l1) {
            $propertyKey = arr1[$i += 1];
            if ($i) {
              out += ' || ';
            }
            var $prop = it.util.getProperty($propertyKey),
              $useData = $data + $prop;
            out += ' ( ( ' + ($useData) + ' === undefined ';
            if ($ownProperties) {
              out += ' || ! Object.prototype.hasOwnProperty.call(' + ($data) + ', \'' + (it.util.escapeQuotes($propertyKey)) + '\') ';
            }
            out += ') && (missing' + ($lvl) + ' = ' + (it.util.toQuotedString(it.opts.jsonPointers ? $propertyKey : $prop)) + ') ) ';
          }
        }
        out += ')) {  ';
        var $propertyPath = 'missing' + $lvl,
          $missingProperty = '\' + ' + $propertyPath + ' + \'';
        if (it.opts._errorDataPathProperty) {
          it.errorPath = it.opts.jsonPointers ? it.util.getPathExpr($currentErrorPath, $propertyPath, true) : $currentErrorPath + ' + ' + $propertyPath;
        }
        var $$outStack = $$outStack || [];
        $$outStack.push(out);
        out = ''; /* istanbul ignore else */
        if (it.createErrors !== false) {
          out += ' { keyword: \'' + ('dependencies') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { property: \'' + (it.util.escapeQuotes($property)) + '\', missingProperty: \'' + ($missingProperty) + '\', depsCount: ' + ($deps.length) + ', deps: \'' + (it.util.escapeQuotes($deps.length == 1 ? $deps[0] : $deps.join(", "))) + '\' } ';
          if (it.opts.messages !== false) {
            out += ' , message: \'should have ';
            if ($deps.length == 1) {
              out += 'property ' + (it.util.escapeQuotes($deps[0]));
            } else {
              out += 'properties ' + (it.util.escapeQuotes($deps.join(", ")));
            }
            out += ' when property ' + (it.util.escapeQuotes($property)) + ' is present\' ';
          }
          if (it.opts.verbose) {
            out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
          }
          out += ' } ';
        } else {
          out += ' {} ';
        }
        var __err = out;
        out = $$outStack.pop();
        if (!it.compositeRule && $breakOnError) {
          /* istanbul ignore if */
          if (it.async) {
            out += ' throw new ValidationError([' + (__err) + ']); ';
          } else {
            out += ' validate.errors = [' + (__err) + ']; return false; ';
          }
        } else {
          out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
        }
      } else {
        out += ' ) { ';
        var arr2 = $deps;
        if (arr2) {
          var $propertyKey, i2 = -1,
            l2 = arr2.length - 1;
          while (i2 < l2) {
            $propertyKey = arr2[i2 += 1];
            var $prop = it.util.getProperty($propertyKey),
              $missingProperty = it.util.escapeQuotes($propertyKey),
              $useData = $data + $prop;
            if (it.opts._errorDataPathProperty) {
              it.errorPath = it.util.getPath($currentErrorPath, $propertyKey, it.opts.jsonPointers);
            }
            out += ' if ( ' + ($useData) + ' === undefined ';
            if ($ownProperties) {
              out += ' || ! Object.prototype.hasOwnProperty.call(' + ($data) + ', \'' + (it.util.escapeQuotes($propertyKey)) + '\') ';
            }
            out += ') {  var err =   '; /* istanbul ignore else */
            if (it.createErrors !== false) {
              out += ' { keyword: \'' + ('dependencies') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { property: \'' + (it.util.escapeQuotes($property)) + '\', missingProperty: \'' + ($missingProperty) + '\', depsCount: ' + ($deps.length) + ', deps: \'' + (it.util.escapeQuotes($deps.length == 1 ? $deps[0] : $deps.join(", "))) + '\' } ';
              if (it.opts.messages !== false) {
                out += ' , message: \'should have ';
                if ($deps.length == 1) {
                  out += 'property ' + (it.util.escapeQuotes($deps[0]));
                } else {
                  out += 'properties ' + (it.util.escapeQuotes($deps.join(", ")));
                }
                out += ' when property ' + (it.util.escapeQuotes($property)) + ' is present\' ';
              }
              if (it.opts.verbose) {
                out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
              }
              out += ' } ';
            } else {
              out += ' {} ';
            }
            out += ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } ';
          }
        }
      }
      out += ' }   ';
      if ($breakOnError) {
        $closingBraces += '}';
        out += ' else { ';
      }
    }
  }
  it.errorPath = $currentErrorPath;
  var $currentBaseId = $it.baseId;
  for (var $property in $schemaDeps) {
    var $sch = $schemaDeps[$property];
    if (it.util.schemaHasRules($sch, it.RULES.all)) {
      out += ' ' + ($nextValid) + ' = true; if ( ' + ($data) + (it.util.getProperty($property)) + ' !== undefined ';
      if ($ownProperties) {
        out += ' && Object.prototype.hasOwnProperty.call(' + ($data) + ', \'' + (it.util.escapeQuotes($property)) + '\') ';
      }
      out += ') { ';
      $it.schema = $sch;
      $it.schemaPath = $schemaPath + it.util.getProperty($property);
      $it.errSchemaPath = $errSchemaPath + '/' + it.util.escapeFragment($property);
      out += '  ' + (it.validate($it)) + ' ';
      $it.baseId = $currentBaseId;
      out += ' }  ';
      if ($breakOnError) {
        out += ' if (' + ($nextValid) + ') { ';
        $closingBraces += '}';
      }
    }
  }
  if ($breakOnError) {
    out += '   ' + ($closingBraces) + ' if (' + ($errs) + ' == errors) {';
  }
  out = it.util.cleanUpCode(out);
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/enum.js":
/*!***********************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/enum.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_enum(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $valid = 'valid' + $lvl;
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  var $i = 'i' + $lvl,
    $vSchema = 'schema' + $lvl;
  if (!$isData) {
    out += ' var ' + ($vSchema) + ' = validate.schema' + ($schemaPath) + ';';
  }
  out += 'var ' + ($valid) + ';';
  if ($isData) {
    out += ' if (schema' + ($lvl) + ' === undefined) ' + ($valid) + ' = true; else if (!Array.isArray(schema' + ($lvl) + ')) ' + ($valid) + ' = false; else {';
  }
  out += '' + ($valid) + ' = false;for (var ' + ($i) + '=0; ' + ($i) + '<' + ($vSchema) + '.length; ' + ($i) + '++) if (equal(' + ($data) + ', ' + ($vSchema) + '[' + ($i) + '])) { ' + ($valid) + ' = true; break; }';
  if ($isData) {
    out += '  }  ';
  }
  out += ' if (!' + ($valid) + ') {   ';
  var $$outStack = $$outStack || [];
  $$outStack.push(out);
  out = ''; /* istanbul ignore else */
  if (it.createErrors !== false) {
    out += ' { keyword: \'' + ('enum') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { allowedValues: schema' + ($lvl) + ' } ';
    if (it.opts.messages !== false) {
      out += ' , message: \'should be equal to one of the allowed values\' ';
    }
    if (it.opts.verbose) {
      out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
    }
    out += ' } ';
  } else {
    out += ' {} ';
  }
  var __err = out;
  out = $$outStack.pop();
  if (!it.compositeRule && $breakOnError) {
    /* istanbul ignore if */
    if (it.async) {
      out += ' throw new ValidationError([' + (__err) + ']); ';
    } else {
      out += ' validate.errors = [' + (__err) + ']; return false; ';
    }
  } else {
    out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
  }
  out += ' }';
  if ($breakOnError) {
    out += ' else { ';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/format.js":
/*!*************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/format.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_format(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  if (it.opts.format === false) {
    if ($breakOnError) {
      out += ' if (true) { ';
    }
    return out;
  }
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  var $unknownFormats = it.opts.unknownFormats,
    $allowUnknown = Array.isArray($unknownFormats);
  if ($isData) {
    var $format = 'format' + $lvl,
      $isObject = 'isObject' + $lvl,
      $formatType = 'formatType' + $lvl;
    out += ' var ' + ($format) + ' = formats[' + ($schemaValue) + ']; var ' + ($isObject) + ' = typeof ' + ($format) + ' == \'object\' && !(' + ($format) + ' instanceof RegExp) && ' + ($format) + '.validate; var ' + ($formatType) + ' = ' + ($isObject) + ' && ' + ($format) + '.type || \'string\'; if (' + ($isObject) + ') { ';
    if (it.async) {
      out += ' var async' + ($lvl) + ' = ' + ($format) + '.async; ';
    }
    out += ' ' + ($format) + ' = ' + ($format) + '.validate; } if (  ';
    if ($isData) {
      out += ' (' + ($schemaValue) + ' !== undefined && typeof ' + ($schemaValue) + ' != \'string\') || ';
    }
    out += ' (';
    if ($unknownFormats != 'ignore') {
      out += ' (' + ($schemaValue) + ' && !' + ($format) + ' ';
      if ($allowUnknown) {
        out += ' && self._opts.unknownFormats.indexOf(' + ($schemaValue) + ') == -1 ';
      }
      out += ') || ';
    }
    out += ' (' + ($format) + ' && ' + ($formatType) + ' == \'' + ($ruleType) + '\' && !(typeof ' + ($format) + ' == \'function\' ? ';
    if (it.async) {
      out += ' (async' + ($lvl) + ' ? await ' + ($format) + '(' + ($data) + ') : ' + ($format) + '(' + ($data) + ')) ';
    } else {
      out += ' ' + ($format) + '(' + ($data) + ') ';
    }
    out += ' : ' + ($format) + '.test(' + ($data) + '))))) {';
  } else {
    var $format = it.formats[$schema];
    if (!$format) {
      if ($unknownFormats == 'ignore') {
        it.logger.warn('unknown format "' + $schema + '" ignored in schema at path "' + it.errSchemaPath + '"');
        if ($breakOnError) {
          out += ' if (true) { ';
        }
        return out;
      } else if ($allowUnknown && $unknownFormats.indexOf($schema) >= 0) {
        if ($breakOnError) {
          out += ' if (true) { ';
        }
        return out;
      } else {
        throw new Error('unknown format "' + $schema + '" is used in schema at path "' + it.errSchemaPath + '"');
      }
    }
    var $isObject = typeof $format == 'object' && !($format instanceof RegExp) && $format.validate;
    var $formatType = $isObject && $format.type || 'string';
    if ($isObject) {
      var $async = $format.async === true;
      $format = $format.validate;
    }
    if ($formatType != $ruleType) {
      if ($breakOnError) {
        out += ' if (true) { ';
      }
      return out;
    }
    if ($async) {
      if (!it.async) throw new Error('async format in sync schema');
      var $formatRef = 'formats' + it.util.getProperty($schema) + '.validate';
      out += ' if (!(await ' + ($formatRef) + '(' + ($data) + '))) { ';
    } else {
      out += ' if (! ';
      var $formatRef = 'formats' + it.util.getProperty($schema);
      if ($isObject) $formatRef += '.validate';
      if (typeof $format == 'function') {
        out += ' ' + ($formatRef) + '(' + ($data) + ') ';
      } else {
        out += ' ' + ($formatRef) + '.test(' + ($data) + ') ';
      }
      out += ') { ';
    }
  }
  var $$outStack = $$outStack || [];
  $$outStack.push(out);
  out = ''; /* istanbul ignore else */
  if (it.createErrors !== false) {
    out += ' { keyword: \'' + ('format') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { format:  ';
    if ($isData) {
      out += '' + ($schemaValue);
    } else {
      out += '' + (it.util.toQuotedString($schema));
    }
    out += '  } ';
    if (it.opts.messages !== false) {
      out += ' , message: \'should match format "';
      if ($isData) {
        out += '\' + ' + ($schemaValue) + ' + \'';
      } else {
        out += '' + (it.util.escapeQuotes($schema));
      }
      out += '"\' ';
    }
    if (it.opts.verbose) {
      out += ' , schema:  ';
      if ($isData) {
        out += 'validate.schema' + ($schemaPath);
      } else {
        out += '' + (it.util.toQuotedString($schema));
      }
      out += '         , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
    }
    out += ' } ';
  } else {
    out += ' {} ';
  }
  var __err = out;
  out = $$outStack.pop();
  if (!it.compositeRule && $breakOnError) {
    /* istanbul ignore if */
    if (it.async) {
      out += ' throw new ValidationError([' + (__err) + ']); ';
    } else {
      out += ' validate.errors = [' + (__err) + ']; return false; ';
    }
  } else {
    out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
  }
  out += ' } ';
  if ($breakOnError) {
    out += ' else { ';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/if.js":
/*!*********************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/if.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_if(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $valid = 'valid' + $lvl;
  var $errs = 'errs__' + $lvl;
  var $it = it.util.copy(it);
  $it.level++;
  var $nextValid = 'valid' + $it.level;
  var $thenSch = it.schema['then'],
    $elseSch = it.schema['else'],
    $thenPresent = $thenSch !== undefined && it.util.schemaHasRules($thenSch, it.RULES.all),
    $elsePresent = $elseSch !== undefined && it.util.schemaHasRules($elseSch, it.RULES.all),
    $currentBaseId = $it.baseId;
  if ($thenPresent || $elsePresent) {
    var $ifClause;
    $it.createErrors = false;
    $it.schema = $schema;
    $it.schemaPath = $schemaPath;
    $it.errSchemaPath = $errSchemaPath;
    out += ' var ' + ($errs) + ' = errors; var ' + ($valid) + ' = true;  ';
    var $wasComposite = it.compositeRule;
    it.compositeRule = $it.compositeRule = true;
    out += '  ' + (it.validate($it)) + ' ';
    $it.baseId = $currentBaseId;
    $it.createErrors = true;
    out += '  errors = ' + ($errs) + '; if (vErrors !== null) { if (' + ($errs) + ') vErrors.length = ' + ($errs) + '; else vErrors = null; }  ';
    it.compositeRule = $it.compositeRule = $wasComposite;
    if ($thenPresent) {
      out += ' if (' + ($nextValid) + ') {  ';
      $it.schema = it.schema['then'];
      $it.schemaPath = it.schemaPath + '.then';
      $it.errSchemaPath = it.errSchemaPath + '/then';
      out += '  ' + (it.validate($it)) + ' ';
      $it.baseId = $currentBaseId;
      out += ' ' + ($valid) + ' = ' + ($nextValid) + '; ';
      if ($thenPresent && $elsePresent) {
        $ifClause = 'ifClause' + $lvl;
        out += ' var ' + ($ifClause) + ' = \'then\'; ';
      } else {
        $ifClause = '\'then\'';
      }
      out += ' } ';
      if ($elsePresent) {
        out += ' else { ';
      }
    } else {
      out += ' if (!' + ($nextValid) + ') { ';
    }
    if ($elsePresent) {
      $it.schema = it.schema['else'];
      $it.schemaPath = it.schemaPath + '.else';
      $it.errSchemaPath = it.errSchemaPath + '/else';
      out += '  ' + (it.validate($it)) + ' ';
      $it.baseId = $currentBaseId;
      out += ' ' + ($valid) + ' = ' + ($nextValid) + '; ';
      if ($thenPresent && $elsePresent) {
        $ifClause = 'ifClause' + $lvl;
        out += ' var ' + ($ifClause) + ' = \'else\'; ';
      } else {
        $ifClause = '\'else\'';
      }
      out += ' } ';
    }
    out += ' if (!' + ($valid) + ') {   var err =   '; /* istanbul ignore else */
    if (it.createErrors !== false) {
      out += ' { keyword: \'' + ('if') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { failingKeyword: ' + ($ifClause) + ' } ';
      if (it.opts.messages !== false) {
        out += ' , message: \'should match "\' + ' + ($ifClause) + ' + \'" schema\' ';
      }
      if (it.opts.verbose) {
        out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
      }
      out += ' } ';
    } else {
      out += ' {} ';
    }
    out += ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
    if (!it.compositeRule && $breakOnError) {
      /* istanbul ignore if */
      if (it.async) {
        out += ' throw new ValidationError(vErrors); ';
      } else {
        out += ' validate.errors = vErrors; return false; ';
      }
    }
    out += ' }   ';
    if ($breakOnError) {
      out += ' else { ';
    }
    out = it.util.cleanUpCode(out);
  } else {
    if ($breakOnError) {
      out += ' if (true) { ';
    }
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/index.js":
/*!************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/index.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


//all requires must be explicit because browserify won't work with dynamic requires
module.exports = {
  '$ref': __webpack_require__(/*! ./ref */ "../../node_modules/ajv/lib/dotjs/ref.js"),
  allOf: __webpack_require__(/*! ./allOf */ "../../node_modules/ajv/lib/dotjs/allOf.js"),
  anyOf: __webpack_require__(/*! ./anyOf */ "../../node_modules/ajv/lib/dotjs/anyOf.js"),
  '$comment': __webpack_require__(/*! ./comment */ "../../node_modules/ajv/lib/dotjs/comment.js"),
  const: __webpack_require__(/*! ./const */ "../../node_modules/ajv/lib/dotjs/const.js"),
  contains: __webpack_require__(/*! ./contains */ "../../node_modules/ajv/lib/dotjs/contains.js"),
  dependencies: __webpack_require__(/*! ./dependencies */ "../../node_modules/ajv/lib/dotjs/dependencies.js"),
  'enum': __webpack_require__(/*! ./enum */ "../../node_modules/ajv/lib/dotjs/enum.js"),
  format: __webpack_require__(/*! ./format */ "../../node_modules/ajv/lib/dotjs/format.js"),
  'if': __webpack_require__(/*! ./if */ "../../node_modules/ajv/lib/dotjs/if.js"),
  items: __webpack_require__(/*! ./items */ "../../node_modules/ajv/lib/dotjs/items.js"),
  maximum: __webpack_require__(/*! ./_limit */ "../../node_modules/ajv/lib/dotjs/_limit.js"),
  minimum: __webpack_require__(/*! ./_limit */ "../../node_modules/ajv/lib/dotjs/_limit.js"),
  maxItems: __webpack_require__(/*! ./_limitItems */ "../../node_modules/ajv/lib/dotjs/_limitItems.js"),
  minItems: __webpack_require__(/*! ./_limitItems */ "../../node_modules/ajv/lib/dotjs/_limitItems.js"),
  maxLength: __webpack_require__(/*! ./_limitLength */ "../../node_modules/ajv/lib/dotjs/_limitLength.js"),
  minLength: __webpack_require__(/*! ./_limitLength */ "../../node_modules/ajv/lib/dotjs/_limitLength.js"),
  maxProperties: __webpack_require__(/*! ./_limitProperties */ "../../node_modules/ajv/lib/dotjs/_limitProperties.js"),
  minProperties: __webpack_require__(/*! ./_limitProperties */ "../../node_modules/ajv/lib/dotjs/_limitProperties.js"),
  multipleOf: __webpack_require__(/*! ./multipleOf */ "../../node_modules/ajv/lib/dotjs/multipleOf.js"),
  not: __webpack_require__(/*! ./not */ "../../node_modules/ajv/lib/dotjs/not.js"),
  oneOf: __webpack_require__(/*! ./oneOf */ "../../node_modules/ajv/lib/dotjs/oneOf.js"),
  pattern: __webpack_require__(/*! ./pattern */ "../../node_modules/ajv/lib/dotjs/pattern.js"),
  properties: __webpack_require__(/*! ./properties */ "../../node_modules/ajv/lib/dotjs/properties.js"),
  propertyNames: __webpack_require__(/*! ./propertyNames */ "../../node_modules/ajv/lib/dotjs/propertyNames.js"),
  required: __webpack_require__(/*! ./required */ "../../node_modules/ajv/lib/dotjs/required.js"),
  uniqueItems: __webpack_require__(/*! ./uniqueItems */ "../../node_modules/ajv/lib/dotjs/uniqueItems.js"),
  validate: __webpack_require__(/*! ./validate */ "../../node_modules/ajv/lib/dotjs/validate.js")
};


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/items.js":
/*!************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/items.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_items(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $valid = 'valid' + $lvl;
  var $errs = 'errs__' + $lvl;
  var $it = it.util.copy(it);
  var $closingBraces = '';
  $it.level++;
  var $nextValid = 'valid' + $it.level;
  var $idx = 'i' + $lvl,
    $dataNxt = $it.dataLevel = it.dataLevel + 1,
    $nextData = 'data' + $dataNxt,
    $currentBaseId = it.baseId;
  out += 'var ' + ($errs) + ' = errors;var ' + ($valid) + ';';
  if (Array.isArray($schema)) {
    var $additionalItems = it.schema.additionalItems;
    if ($additionalItems === false) {
      out += ' ' + ($valid) + ' = ' + ($data) + '.length <= ' + ($schema.length) + '; ';
      var $currErrSchemaPath = $errSchemaPath;
      $errSchemaPath = it.errSchemaPath + '/additionalItems';
      out += '  if (!' + ($valid) + ') {   ';
      var $$outStack = $$outStack || [];
      $$outStack.push(out);
      out = ''; /* istanbul ignore else */
      if (it.createErrors !== false) {
        out += ' { keyword: \'' + ('additionalItems') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { limit: ' + ($schema.length) + ' } ';
        if (it.opts.messages !== false) {
          out += ' , message: \'should NOT have more than ' + ($schema.length) + ' items\' ';
        }
        if (it.opts.verbose) {
          out += ' , schema: false , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
        }
        out += ' } ';
      } else {
        out += ' {} ';
      }
      var __err = out;
      out = $$outStack.pop();
      if (!it.compositeRule && $breakOnError) {
        /* istanbul ignore if */
        if (it.async) {
          out += ' throw new ValidationError([' + (__err) + ']); ';
        } else {
          out += ' validate.errors = [' + (__err) + ']; return false; ';
        }
      } else {
        out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
      }
      out += ' } ';
      $errSchemaPath = $currErrSchemaPath;
      if ($breakOnError) {
        $closingBraces += '}';
        out += ' else { ';
      }
    }
    var arr1 = $schema;
    if (arr1) {
      var $sch, $i = -1,
        l1 = arr1.length - 1;
      while ($i < l1) {
        $sch = arr1[$i += 1];
        if (it.util.schemaHasRules($sch, it.RULES.all)) {
          out += ' ' + ($nextValid) + ' = true; if (' + ($data) + '.length > ' + ($i) + ') { ';
          var $passData = $data + '[' + $i + ']';
          $it.schema = $sch;
          $it.schemaPath = $schemaPath + '[' + $i + ']';
          $it.errSchemaPath = $errSchemaPath + '/' + $i;
          $it.errorPath = it.util.getPathExpr(it.errorPath, $i, it.opts.jsonPointers, true);
          $it.dataPathArr[$dataNxt] = $i;
          var $code = it.validate($it);
          $it.baseId = $currentBaseId;
          if (it.util.varOccurences($code, $nextData) < 2) {
            out += ' ' + (it.util.varReplace($code, $nextData, $passData)) + ' ';
          } else {
            out += ' var ' + ($nextData) + ' = ' + ($passData) + '; ' + ($code) + ' ';
          }
          out += ' }  ';
          if ($breakOnError) {
            out += ' if (' + ($nextValid) + ') { ';
            $closingBraces += '}';
          }
        }
      }
    }
    if (typeof $additionalItems == 'object' && it.util.schemaHasRules($additionalItems, it.RULES.all)) {
      $it.schema = $additionalItems;
      $it.schemaPath = it.schemaPath + '.additionalItems';
      $it.errSchemaPath = it.errSchemaPath + '/additionalItems';
      out += ' ' + ($nextValid) + ' = true; if (' + ($data) + '.length > ' + ($schema.length) + ') {  for (var ' + ($idx) + ' = ' + ($schema.length) + '; ' + ($idx) + ' < ' + ($data) + '.length; ' + ($idx) + '++) { ';
      $it.errorPath = it.util.getPathExpr(it.errorPath, $idx, it.opts.jsonPointers, true);
      var $passData = $data + '[' + $idx + ']';
      $it.dataPathArr[$dataNxt] = $idx;
      var $code = it.validate($it);
      $it.baseId = $currentBaseId;
      if (it.util.varOccurences($code, $nextData) < 2) {
        out += ' ' + (it.util.varReplace($code, $nextData, $passData)) + ' ';
      } else {
        out += ' var ' + ($nextData) + ' = ' + ($passData) + '; ' + ($code) + ' ';
      }
      if ($breakOnError) {
        out += ' if (!' + ($nextValid) + ') break; ';
      }
      out += ' } }  ';
      if ($breakOnError) {
        out += ' if (' + ($nextValid) + ') { ';
        $closingBraces += '}';
      }
    }
  } else if (it.util.schemaHasRules($schema, it.RULES.all)) {
    $it.schema = $schema;
    $it.schemaPath = $schemaPath;
    $it.errSchemaPath = $errSchemaPath;
    out += '  for (var ' + ($idx) + ' = ' + (0) + '; ' + ($idx) + ' < ' + ($data) + '.length; ' + ($idx) + '++) { ';
    $it.errorPath = it.util.getPathExpr(it.errorPath, $idx, it.opts.jsonPointers, true);
    var $passData = $data + '[' + $idx + ']';
    $it.dataPathArr[$dataNxt] = $idx;
    var $code = it.validate($it);
    $it.baseId = $currentBaseId;
    if (it.util.varOccurences($code, $nextData) < 2) {
      out += ' ' + (it.util.varReplace($code, $nextData, $passData)) + ' ';
    } else {
      out += ' var ' + ($nextData) + ' = ' + ($passData) + '; ' + ($code) + ' ';
    }
    if ($breakOnError) {
      out += ' if (!' + ($nextValid) + ') break; ';
    }
    out += ' }';
  }
  if ($breakOnError) {
    out += ' ' + ($closingBraces) + ' if (' + ($errs) + ' == errors) {';
  }
  out = it.util.cleanUpCode(out);
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/multipleOf.js":
/*!*****************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/multipleOf.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_multipleOf(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  out += 'var division' + ($lvl) + ';if (';
  if ($isData) {
    out += ' ' + ($schemaValue) + ' !== undefined && ( typeof ' + ($schemaValue) + ' != \'number\' || ';
  }
  out += ' (division' + ($lvl) + ' = ' + ($data) + ' / ' + ($schemaValue) + ', ';
  if (it.opts.multipleOfPrecision) {
    out += ' Math.abs(Math.round(division' + ($lvl) + ') - division' + ($lvl) + ') > 1e-' + (it.opts.multipleOfPrecision) + ' ';
  } else {
    out += ' division' + ($lvl) + ' !== parseInt(division' + ($lvl) + ') ';
  }
  out += ' ) ';
  if ($isData) {
    out += '  )  ';
  }
  out += ' ) {   ';
  var $$outStack = $$outStack || [];
  $$outStack.push(out);
  out = ''; /* istanbul ignore else */
  if (it.createErrors !== false) {
    out += ' { keyword: \'' + ('multipleOf') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { multipleOf: ' + ($schemaValue) + ' } ';
    if (it.opts.messages !== false) {
      out += ' , message: \'should be multiple of ';
      if ($isData) {
        out += '\' + ' + ($schemaValue);
      } else {
        out += '' + ($schemaValue) + '\'';
      }
    }
    if (it.opts.verbose) {
      out += ' , schema:  ';
      if ($isData) {
        out += 'validate.schema' + ($schemaPath);
      } else {
        out += '' + ($schema);
      }
      out += '         , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
    }
    out += ' } ';
  } else {
    out += ' {} ';
  }
  var __err = out;
  out = $$outStack.pop();
  if (!it.compositeRule && $breakOnError) {
    /* istanbul ignore if */
    if (it.async) {
      out += ' throw new ValidationError([' + (__err) + ']); ';
    } else {
      out += ' validate.errors = [' + (__err) + ']; return false; ';
    }
  } else {
    out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
  }
  out += '} ';
  if ($breakOnError) {
    out += ' else { ';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/not.js":
/*!**********************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/not.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_not(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $errs = 'errs__' + $lvl;
  var $it = it.util.copy(it);
  $it.level++;
  var $nextValid = 'valid' + $it.level;
  if (it.util.schemaHasRules($schema, it.RULES.all)) {
    $it.schema = $schema;
    $it.schemaPath = $schemaPath;
    $it.errSchemaPath = $errSchemaPath;
    out += ' var ' + ($errs) + ' = errors;  ';
    var $wasComposite = it.compositeRule;
    it.compositeRule = $it.compositeRule = true;
    $it.createErrors = false;
    var $allErrorsOption;
    if ($it.opts.allErrors) {
      $allErrorsOption = $it.opts.allErrors;
      $it.opts.allErrors = false;
    }
    out += ' ' + (it.validate($it)) + ' ';
    $it.createErrors = true;
    if ($allErrorsOption) $it.opts.allErrors = $allErrorsOption;
    it.compositeRule = $it.compositeRule = $wasComposite;
    out += ' if (' + ($nextValid) + ') {   ';
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = ''; /* istanbul ignore else */
    if (it.createErrors !== false) {
      out += ' { keyword: \'' + ('not') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: {} ';
      if (it.opts.messages !== false) {
        out += ' , message: \'should NOT be valid\' ';
      }
      if (it.opts.verbose) {
        out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
      }
      out += ' } ';
    } else {
      out += ' {} ';
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it.compositeRule && $breakOnError) {
      /* istanbul ignore if */
      if (it.async) {
        out += ' throw new ValidationError([' + (__err) + ']); ';
      } else {
        out += ' validate.errors = [' + (__err) + ']; return false; ';
      }
    } else {
      out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
    }
    out += ' } else {  errors = ' + ($errs) + '; if (vErrors !== null) { if (' + ($errs) + ') vErrors.length = ' + ($errs) + '; else vErrors = null; } ';
    if (it.opts.allErrors) {
      out += ' } ';
    }
  } else {
    out += '  var err =   '; /* istanbul ignore else */
    if (it.createErrors !== false) {
      out += ' { keyword: \'' + ('not') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: {} ';
      if (it.opts.messages !== false) {
        out += ' , message: \'should NOT be valid\' ';
      }
      if (it.opts.verbose) {
        out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
      }
      out += ' } ';
    } else {
      out += ' {} ';
    }
    out += ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
    if ($breakOnError) {
      out += ' if (false) { ';
    }
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/oneOf.js":
/*!************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/oneOf.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_oneOf(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $valid = 'valid' + $lvl;
  var $errs = 'errs__' + $lvl;
  var $it = it.util.copy(it);
  var $closingBraces = '';
  $it.level++;
  var $nextValid = 'valid' + $it.level;
  var $currentBaseId = $it.baseId,
    $prevValid = 'prevValid' + $lvl,
    $passingSchemas = 'passingSchemas' + $lvl;
  out += 'var ' + ($errs) + ' = errors , ' + ($prevValid) + ' = false , ' + ($valid) + ' = false , ' + ($passingSchemas) + ' = null; ';
  var $wasComposite = it.compositeRule;
  it.compositeRule = $it.compositeRule = true;
  var arr1 = $schema;
  if (arr1) {
    var $sch, $i = -1,
      l1 = arr1.length - 1;
    while ($i < l1) {
      $sch = arr1[$i += 1];
      if (it.util.schemaHasRules($sch, it.RULES.all)) {
        $it.schema = $sch;
        $it.schemaPath = $schemaPath + '[' + $i + ']';
        $it.errSchemaPath = $errSchemaPath + '/' + $i;
        out += '  ' + (it.validate($it)) + ' ';
        $it.baseId = $currentBaseId;
      } else {
        out += ' var ' + ($nextValid) + ' = true; ';
      }
      if ($i) {
        out += ' if (' + ($nextValid) + ' && ' + ($prevValid) + ') { ' + ($valid) + ' = false; ' + ($passingSchemas) + ' = [' + ($passingSchemas) + ', ' + ($i) + ']; } else { ';
        $closingBraces += '}';
      }
      out += ' if (' + ($nextValid) + ') { ' + ($valid) + ' = ' + ($prevValid) + ' = true; ' + ($passingSchemas) + ' = ' + ($i) + '; }';
    }
  }
  it.compositeRule = $it.compositeRule = $wasComposite;
  out += '' + ($closingBraces) + 'if (!' + ($valid) + ') {   var err =   '; /* istanbul ignore else */
  if (it.createErrors !== false) {
    out += ' { keyword: \'' + ('oneOf') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { passingSchemas: ' + ($passingSchemas) + ' } ';
    if (it.opts.messages !== false) {
      out += ' , message: \'should match exactly one schema in oneOf\' ';
    }
    if (it.opts.verbose) {
      out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
    }
    out += ' } ';
  } else {
    out += ' {} ';
  }
  out += ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
  if (!it.compositeRule && $breakOnError) {
    /* istanbul ignore if */
    if (it.async) {
      out += ' throw new ValidationError(vErrors); ';
    } else {
      out += ' validate.errors = vErrors; return false; ';
    }
  }
  out += '} else {  errors = ' + ($errs) + '; if (vErrors !== null) { if (' + ($errs) + ') vErrors.length = ' + ($errs) + '; else vErrors = null; }';
  if (it.opts.allErrors) {
    out += ' } ';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/pattern.js":
/*!**************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/pattern.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_pattern(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  var $regexp = $isData ? '(new RegExp(' + $schemaValue + '))' : it.usePattern($schema);
  out += 'if ( ';
  if ($isData) {
    out += ' (' + ($schemaValue) + ' !== undefined && typeof ' + ($schemaValue) + ' != \'string\') || ';
  }
  out += ' !' + ($regexp) + '.test(' + ($data) + ') ) {   ';
  var $$outStack = $$outStack || [];
  $$outStack.push(out);
  out = ''; /* istanbul ignore else */
  if (it.createErrors !== false) {
    out += ' { keyword: \'' + ('pattern') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { pattern:  ';
    if ($isData) {
      out += '' + ($schemaValue);
    } else {
      out += '' + (it.util.toQuotedString($schema));
    }
    out += '  } ';
    if (it.opts.messages !== false) {
      out += ' , message: \'should match pattern "';
      if ($isData) {
        out += '\' + ' + ($schemaValue) + ' + \'';
      } else {
        out += '' + (it.util.escapeQuotes($schema));
      }
      out += '"\' ';
    }
    if (it.opts.verbose) {
      out += ' , schema:  ';
      if ($isData) {
        out += 'validate.schema' + ($schemaPath);
      } else {
        out += '' + (it.util.toQuotedString($schema));
      }
      out += '         , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
    }
    out += ' } ';
  } else {
    out += ' {} ';
  }
  var __err = out;
  out = $$outStack.pop();
  if (!it.compositeRule && $breakOnError) {
    /* istanbul ignore if */
    if (it.async) {
      out += ' throw new ValidationError([' + (__err) + ']); ';
    } else {
      out += ' validate.errors = [' + (__err) + ']; return false; ';
    }
  } else {
    out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
  }
  out += '} ';
  if ($breakOnError) {
    out += ' else { ';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/properties.js":
/*!*****************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/properties.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_properties(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $errs = 'errs__' + $lvl;
  var $it = it.util.copy(it);
  var $closingBraces = '';
  $it.level++;
  var $nextValid = 'valid' + $it.level;
  var $key = 'key' + $lvl,
    $idx = 'idx' + $lvl,
    $dataNxt = $it.dataLevel = it.dataLevel + 1,
    $nextData = 'data' + $dataNxt,
    $dataProperties = 'dataProperties' + $lvl;
  var $schemaKeys = Object.keys($schema || {}),
    $pProperties = it.schema.patternProperties || {},
    $pPropertyKeys = Object.keys($pProperties),
    $aProperties = it.schema.additionalProperties,
    $someProperties = $schemaKeys.length || $pPropertyKeys.length,
    $noAdditional = $aProperties === false,
    $additionalIsSchema = typeof $aProperties == 'object' && Object.keys($aProperties).length,
    $removeAdditional = it.opts.removeAdditional,
    $checkAdditional = $noAdditional || $additionalIsSchema || $removeAdditional,
    $ownProperties = it.opts.ownProperties,
    $currentBaseId = it.baseId;
  var $required = it.schema.required;
  if ($required && !(it.opts.$data && $required.$data) && $required.length < it.opts.loopRequired) var $requiredHash = it.util.toHash($required);
  out += 'var ' + ($errs) + ' = errors;var ' + ($nextValid) + ' = true;';
  if ($ownProperties) {
    out += ' var ' + ($dataProperties) + ' = undefined;';
  }
  if ($checkAdditional) {
    if ($ownProperties) {
      out += ' ' + ($dataProperties) + ' = ' + ($dataProperties) + ' || Object.keys(' + ($data) + '); for (var ' + ($idx) + '=0; ' + ($idx) + '<' + ($dataProperties) + '.length; ' + ($idx) + '++) { var ' + ($key) + ' = ' + ($dataProperties) + '[' + ($idx) + ']; ';
    } else {
      out += ' for (var ' + ($key) + ' in ' + ($data) + ') { ';
    }
    if ($someProperties) {
      out += ' var isAdditional' + ($lvl) + ' = !(false ';
      if ($schemaKeys.length) {
        if ($schemaKeys.length > 8) {
          out += ' || validate.schema' + ($schemaPath) + '.hasOwnProperty(' + ($key) + ') ';
        } else {
          var arr1 = $schemaKeys;
          if (arr1) {
            var $propertyKey, i1 = -1,
              l1 = arr1.length - 1;
            while (i1 < l1) {
              $propertyKey = arr1[i1 += 1];
              out += ' || ' + ($key) + ' == ' + (it.util.toQuotedString($propertyKey)) + ' ';
            }
          }
        }
      }
      if ($pPropertyKeys.length) {
        var arr2 = $pPropertyKeys;
        if (arr2) {
          var $pProperty, $i = -1,
            l2 = arr2.length - 1;
          while ($i < l2) {
            $pProperty = arr2[$i += 1];
            out += ' || ' + (it.usePattern($pProperty)) + '.test(' + ($key) + ') ';
          }
        }
      }
      out += ' ); if (isAdditional' + ($lvl) + ') { ';
    }
    if ($removeAdditional == 'all') {
      out += ' delete ' + ($data) + '[' + ($key) + ']; ';
    } else {
      var $currentErrorPath = it.errorPath;
      var $additionalProperty = '\' + ' + $key + ' + \'';
      if (it.opts._errorDataPathProperty) {
        it.errorPath = it.util.getPathExpr(it.errorPath, $key, it.opts.jsonPointers);
      }
      if ($noAdditional) {
        if ($removeAdditional) {
          out += ' delete ' + ($data) + '[' + ($key) + ']; ';
        } else {
          out += ' ' + ($nextValid) + ' = false; ';
          var $currErrSchemaPath = $errSchemaPath;
          $errSchemaPath = it.errSchemaPath + '/additionalProperties';
          var $$outStack = $$outStack || [];
          $$outStack.push(out);
          out = ''; /* istanbul ignore else */
          if (it.createErrors !== false) {
            out += ' { keyword: \'' + ('additionalProperties') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { additionalProperty: \'' + ($additionalProperty) + '\' } ';
            if (it.opts.messages !== false) {
              out += ' , message: \'';
              if (it.opts._errorDataPathProperty) {
                out += 'is an invalid additional property';
              } else {
                out += 'should NOT have additional properties';
              }
              out += '\' ';
            }
            if (it.opts.verbose) {
              out += ' , schema: false , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
            }
            out += ' } ';
          } else {
            out += ' {} ';
          }
          var __err = out;
          out = $$outStack.pop();
          if (!it.compositeRule && $breakOnError) {
            /* istanbul ignore if */
            if (it.async) {
              out += ' throw new ValidationError([' + (__err) + ']); ';
            } else {
              out += ' validate.errors = [' + (__err) + ']; return false; ';
            }
          } else {
            out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
          }
          $errSchemaPath = $currErrSchemaPath;
          if ($breakOnError) {
            out += ' break; ';
          }
        }
      } else if ($additionalIsSchema) {
        if ($removeAdditional == 'failing') {
          out += ' var ' + ($errs) + ' = errors;  ';
          var $wasComposite = it.compositeRule;
          it.compositeRule = $it.compositeRule = true;
          $it.schema = $aProperties;
          $it.schemaPath = it.schemaPath + '.additionalProperties';
          $it.errSchemaPath = it.errSchemaPath + '/additionalProperties';
          $it.errorPath = it.opts._errorDataPathProperty ? it.errorPath : it.util.getPathExpr(it.errorPath, $key, it.opts.jsonPointers);
          var $passData = $data + '[' + $key + ']';
          $it.dataPathArr[$dataNxt] = $key;
          var $code = it.validate($it);
          $it.baseId = $currentBaseId;
          if (it.util.varOccurences($code, $nextData) < 2) {
            out += ' ' + (it.util.varReplace($code, $nextData, $passData)) + ' ';
          } else {
            out += ' var ' + ($nextData) + ' = ' + ($passData) + '; ' + ($code) + ' ';
          }
          out += ' if (!' + ($nextValid) + ') { errors = ' + ($errs) + '; if (validate.errors !== null) { if (errors) validate.errors.length = errors; else validate.errors = null; } delete ' + ($data) + '[' + ($key) + ']; }  ';
          it.compositeRule = $it.compositeRule = $wasComposite;
        } else {
          $it.schema = $aProperties;
          $it.schemaPath = it.schemaPath + '.additionalProperties';
          $it.errSchemaPath = it.errSchemaPath + '/additionalProperties';
          $it.errorPath = it.opts._errorDataPathProperty ? it.errorPath : it.util.getPathExpr(it.errorPath, $key, it.opts.jsonPointers);
          var $passData = $data + '[' + $key + ']';
          $it.dataPathArr[$dataNxt] = $key;
          var $code = it.validate($it);
          $it.baseId = $currentBaseId;
          if (it.util.varOccurences($code, $nextData) < 2) {
            out += ' ' + (it.util.varReplace($code, $nextData, $passData)) + ' ';
          } else {
            out += ' var ' + ($nextData) + ' = ' + ($passData) + '; ' + ($code) + ' ';
          }
          if ($breakOnError) {
            out += ' if (!' + ($nextValid) + ') break; ';
          }
        }
      }
      it.errorPath = $currentErrorPath;
    }
    if ($someProperties) {
      out += ' } ';
    }
    out += ' }  ';
    if ($breakOnError) {
      out += ' if (' + ($nextValid) + ') { ';
      $closingBraces += '}';
    }
  }
  var $useDefaults = it.opts.useDefaults && !it.compositeRule;
  if ($schemaKeys.length) {
    var arr3 = $schemaKeys;
    if (arr3) {
      var $propertyKey, i3 = -1,
        l3 = arr3.length - 1;
      while (i3 < l3) {
        $propertyKey = arr3[i3 += 1];
        var $sch = $schema[$propertyKey];
        if (it.util.schemaHasRules($sch, it.RULES.all)) {
          var $prop = it.util.getProperty($propertyKey),
            $passData = $data + $prop,
            $hasDefault = $useDefaults && $sch.default !== undefined;
          $it.schema = $sch;
          $it.schemaPath = $schemaPath + $prop;
          $it.errSchemaPath = $errSchemaPath + '/' + it.util.escapeFragment($propertyKey);
          $it.errorPath = it.util.getPath(it.errorPath, $propertyKey, it.opts.jsonPointers);
          $it.dataPathArr[$dataNxt] = it.util.toQuotedString($propertyKey);
          var $code = it.validate($it);
          $it.baseId = $currentBaseId;
          if (it.util.varOccurences($code, $nextData) < 2) {
            $code = it.util.varReplace($code, $nextData, $passData);
            var $useData = $passData;
          } else {
            var $useData = $nextData;
            out += ' var ' + ($nextData) + ' = ' + ($passData) + '; ';
          }
          if ($hasDefault) {
            out += ' ' + ($code) + ' ';
          } else {
            if ($requiredHash && $requiredHash[$propertyKey]) {
              out += ' if ( ' + ($useData) + ' === undefined ';
              if ($ownProperties) {
                out += ' || ! Object.prototype.hasOwnProperty.call(' + ($data) + ', \'' + (it.util.escapeQuotes($propertyKey)) + '\') ';
              }
              out += ') { ' + ($nextValid) + ' = false; ';
              var $currentErrorPath = it.errorPath,
                $currErrSchemaPath = $errSchemaPath,
                $missingProperty = it.util.escapeQuotes($propertyKey);
              if (it.opts._errorDataPathProperty) {
                it.errorPath = it.util.getPath($currentErrorPath, $propertyKey, it.opts.jsonPointers);
              }
              $errSchemaPath = it.errSchemaPath + '/required';
              var $$outStack = $$outStack || [];
              $$outStack.push(out);
              out = ''; /* istanbul ignore else */
              if (it.createErrors !== false) {
                out += ' { keyword: \'' + ('required') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { missingProperty: \'' + ($missingProperty) + '\' } ';
                if (it.opts.messages !== false) {
                  out += ' , message: \'';
                  if (it.opts._errorDataPathProperty) {
                    out += 'is a required property';
                  } else {
                    out += 'should have required property \\\'' + ($missingProperty) + '\\\'';
                  }
                  out += '\' ';
                }
                if (it.opts.verbose) {
                  out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
                }
                out += ' } ';
              } else {
                out += ' {} ';
              }
              var __err = out;
              out = $$outStack.pop();
              if (!it.compositeRule && $breakOnError) {
                /* istanbul ignore if */
                if (it.async) {
                  out += ' throw new ValidationError([' + (__err) + ']); ';
                } else {
                  out += ' validate.errors = [' + (__err) + ']; return false; ';
                }
              } else {
                out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
              }
              $errSchemaPath = $currErrSchemaPath;
              it.errorPath = $currentErrorPath;
              out += ' } else { ';
            } else {
              if ($breakOnError) {
                out += ' if ( ' + ($useData) + ' === undefined ';
                if ($ownProperties) {
                  out += ' || ! Object.prototype.hasOwnProperty.call(' + ($data) + ', \'' + (it.util.escapeQuotes($propertyKey)) + '\') ';
                }
                out += ') { ' + ($nextValid) + ' = true; } else { ';
              } else {
                out += ' if (' + ($useData) + ' !== undefined ';
                if ($ownProperties) {
                  out += ' &&   Object.prototype.hasOwnProperty.call(' + ($data) + ', \'' + (it.util.escapeQuotes($propertyKey)) + '\') ';
                }
                out += ' ) { ';
              }
            }
            out += ' ' + ($code) + ' } ';
          }
        }
        if ($breakOnError) {
          out += ' if (' + ($nextValid) + ') { ';
          $closingBraces += '}';
        }
      }
    }
  }
  if ($pPropertyKeys.length) {
    var arr4 = $pPropertyKeys;
    if (arr4) {
      var $pProperty, i4 = -1,
        l4 = arr4.length - 1;
      while (i4 < l4) {
        $pProperty = arr4[i4 += 1];
        var $sch = $pProperties[$pProperty];
        if (it.util.schemaHasRules($sch, it.RULES.all)) {
          $it.schema = $sch;
          $it.schemaPath = it.schemaPath + '.patternProperties' + it.util.getProperty($pProperty);
          $it.errSchemaPath = it.errSchemaPath + '/patternProperties/' + it.util.escapeFragment($pProperty);
          if ($ownProperties) {
            out += ' ' + ($dataProperties) + ' = ' + ($dataProperties) + ' || Object.keys(' + ($data) + '); for (var ' + ($idx) + '=0; ' + ($idx) + '<' + ($dataProperties) + '.length; ' + ($idx) + '++) { var ' + ($key) + ' = ' + ($dataProperties) + '[' + ($idx) + ']; ';
          } else {
            out += ' for (var ' + ($key) + ' in ' + ($data) + ') { ';
          }
          out += ' if (' + (it.usePattern($pProperty)) + '.test(' + ($key) + ')) { ';
          $it.errorPath = it.util.getPathExpr(it.errorPath, $key, it.opts.jsonPointers);
          var $passData = $data + '[' + $key + ']';
          $it.dataPathArr[$dataNxt] = $key;
          var $code = it.validate($it);
          $it.baseId = $currentBaseId;
          if (it.util.varOccurences($code, $nextData) < 2) {
            out += ' ' + (it.util.varReplace($code, $nextData, $passData)) + ' ';
          } else {
            out += ' var ' + ($nextData) + ' = ' + ($passData) + '; ' + ($code) + ' ';
          }
          if ($breakOnError) {
            out += ' if (!' + ($nextValid) + ') break; ';
          }
          out += ' } ';
          if ($breakOnError) {
            out += ' else ' + ($nextValid) + ' = true; ';
          }
          out += ' }  ';
          if ($breakOnError) {
            out += ' if (' + ($nextValid) + ') { ';
            $closingBraces += '}';
          }
        }
      }
    }
  }
  if ($breakOnError) {
    out += ' ' + ($closingBraces) + ' if (' + ($errs) + ' == errors) {';
  }
  out = it.util.cleanUpCode(out);
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/propertyNames.js":
/*!********************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/propertyNames.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_propertyNames(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $errs = 'errs__' + $lvl;
  var $it = it.util.copy(it);
  var $closingBraces = '';
  $it.level++;
  var $nextValid = 'valid' + $it.level;
  out += 'var ' + ($errs) + ' = errors;';
  if (it.util.schemaHasRules($schema, it.RULES.all)) {
    $it.schema = $schema;
    $it.schemaPath = $schemaPath;
    $it.errSchemaPath = $errSchemaPath;
    var $key = 'key' + $lvl,
      $idx = 'idx' + $lvl,
      $i = 'i' + $lvl,
      $invalidName = '\' + ' + $key + ' + \'',
      $dataNxt = $it.dataLevel = it.dataLevel + 1,
      $nextData = 'data' + $dataNxt,
      $dataProperties = 'dataProperties' + $lvl,
      $ownProperties = it.opts.ownProperties,
      $currentBaseId = it.baseId;
    if ($ownProperties) {
      out += ' var ' + ($dataProperties) + ' = undefined; ';
    }
    if ($ownProperties) {
      out += ' ' + ($dataProperties) + ' = ' + ($dataProperties) + ' || Object.keys(' + ($data) + '); for (var ' + ($idx) + '=0; ' + ($idx) + '<' + ($dataProperties) + '.length; ' + ($idx) + '++) { var ' + ($key) + ' = ' + ($dataProperties) + '[' + ($idx) + ']; ';
    } else {
      out += ' for (var ' + ($key) + ' in ' + ($data) + ') { ';
    }
    out += ' var startErrs' + ($lvl) + ' = errors; ';
    var $passData = $key;
    var $wasComposite = it.compositeRule;
    it.compositeRule = $it.compositeRule = true;
    var $code = it.validate($it);
    $it.baseId = $currentBaseId;
    if (it.util.varOccurences($code, $nextData) < 2) {
      out += ' ' + (it.util.varReplace($code, $nextData, $passData)) + ' ';
    } else {
      out += ' var ' + ($nextData) + ' = ' + ($passData) + '; ' + ($code) + ' ';
    }
    it.compositeRule = $it.compositeRule = $wasComposite;
    out += ' if (!' + ($nextValid) + ') { for (var ' + ($i) + '=startErrs' + ($lvl) + '; ' + ($i) + '<errors; ' + ($i) + '++) { vErrors[' + ($i) + '].propertyName = ' + ($key) + '; }   var err =   '; /* istanbul ignore else */
    if (it.createErrors !== false) {
      out += ' { keyword: \'' + ('propertyNames') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { propertyName: \'' + ($invalidName) + '\' } ';
      if (it.opts.messages !== false) {
        out += ' , message: \'property name \\\'' + ($invalidName) + '\\\' is invalid\' ';
      }
      if (it.opts.verbose) {
        out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
      }
      out += ' } ';
    } else {
      out += ' {} ';
    }
    out += ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
    if (!it.compositeRule && $breakOnError) {
      /* istanbul ignore if */
      if (it.async) {
        out += ' throw new ValidationError(vErrors); ';
      } else {
        out += ' validate.errors = vErrors; return false; ';
      }
    }
    if ($breakOnError) {
      out += ' break; ';
    }
    out += ' } }';
  }
  if ($breakOnError) {
    out += ' ' + ($closingBraces) + ' if (' + ($errs) + ' == errors) {';
  }
  out = it.util.cleanUpCode(out);
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/ref.js":
/*!**********************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/ref.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_ref(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $valid = 'valid' + $lvl;
  var $async, $refCode;
  if ($schema == '#' || $schema == '#/') {
    if (it.isRoot) {
      $async = it.async;
      $refCode = 'validate';
    } else {
      $async = it.root.schema.$async === true;
      $refCode = 'root.refVal[0]';
    }
  } else {
    var $refVal = it.resolveRef(it.baseId, $schema, it.isRoot);
    if ($refVal === undefined) {
      var $message = it.MissingRefError.message(it.baseId, $schema);
      if (it.opts.missingRefs == 'fail') {
        it.logger.error($message);
        var $$outStack = $$outStack || [];
        $$outStack.push(out);
        out = ''; /* istanbul ignore else */
        if (it.createErrors !== false) {
          out += ' { keyword: \'' + ('$ref') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { ref: \'' + (it.util.escapeQuotes($schema)) + '\' } ';
          if (it.opts.messages !== false) {
            out += ' , message: \'can\\\'t resolve reference ' + (it.util.escapeQuotes($schema)) + '\' ';
          }
          if (it.opts.verbose) {
            out += ' , schema: ' + (it.util.toQuotedString($schema)) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
          }
          out += ' } ';
        } else {
          out += ' {} ';
        }
        var __err = out;
        out = $$outStack.pop();
        if (!it.compositeRule && $breakOnError) {
          /* istanbul ignore if */
          if (it.async) {
            out += ' throw new ValidationError([' + (__err) + ']); ';
          } else {
            out += ' validate.errors = [' + (__err) + ']; return false; ';
          }
        } else {
          out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
        }
        if ($breakOnError) {
          out += ' if (false) { ';
        }
      } else if (it.opts.missingRefs == 'ignore') {
        it.logger.warn($message);
        if ($breakOnError) {
          out += ' if (true) { ';
        }
      } else {
        throw new it.MissingRefError(it.baseId, $schema, $message);
      }
    } else if ($refVal.inline) {
      var $it = it.util.copy(it);
      $it.level++;
      var $nextValid = 'valid' + $it.level;
      $it.schema = $refVal.schema;
      $it.schemaPath = '';
      $it.errSchemaPath = $schema;
      var $code = it.validate($it).replace(/validate\.schema/g, $refVal.code);
      out += ' ' + ($code) + ' ';
      if ($breakOnError) {
        out += ' if (' + ($nextValid) + ') { ';
      }
    } else {
      $async = $refVal.$async === true || (it.async && $refVal.$async !== false);
      $refCode = $refVal.code;
    }
  }
  if ($refCode) {
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = '';
    if (it.opts.passContext) {
      out += ' ' + ($refCode) + '.call(this, ';
    } else {
      out += ' ' + ($refCode) + '( ';
    }
    out += ' ' + ($data) + ', (dataPath || \'\')';
    if (it.errorPath != '""') {
      out += ' + ' + (it.errorPath);
    }
    var $parentData = $dataLvl ? 'data' + (($dataLvl - 1) || '') : 'parentData',
      $parentDataProperty = $dataLvl ? it.dataPathArr[$dataLvl] : 'parentDataProperty';
    out += ' , ' + ($parentData) + ' , ' + ($parentDataProperty) + ', rootData)  ';
    var __callValidate = out;
    out = $$outStack.pop();
    if ($async) {
      if (!it.async) throw new Error('async schema referenced by sync schema');
      if ($breakOnError) {
        out += ' var ' + ($valid) + '; ';
      }
      out += ' try { await ' + (__callValidate) + '; ';
      if ($breakOnError) {
        out += ' ' + ($valid) + ' = true; ';
      }
      out += ' } catch (e) { if (!(e instanceof ValidationError)) throw e; if (vErrors === null) vErrors = e.errors; else vErrors = vErrors.concat(e.errors); errors = vErrors.length; ';
      if ($breakOnError) {
        out += ' ' + ($valid) + ' = false; ';
      }
      out += ' } ';
      if ($breakOnError) {
        out += ' if (' + ($valid) + ') { ';
      }
    } else {
      out += ' if (!' + (__callValidate) + ') { if (vErrors === null) vErrors = ' + ($refCode) + '.errors; else vErrors = vErrors.concat(' + ($refCode) + '.errors); errors = vErrors.length; } ';
      if ($breakOnError) {
        out += ' else { ';
      }
    }
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/required.js":
/*!***************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/required.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_required(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $valid = 'valid' + $lvl;
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  var $vSchema = 'schema' + $lvl;
  if (!$isData) {
    if ($schema.length < it.opts.loopRequired && it.schema.properties && Object.keys(it.schema.properties).length) {
      var $required = [];
      var arr1 = $schema;
      if (arr1) {
        var $property, i1 = -1,
          l1 = arr1.length - 1;
        while (i1 < l1) {
          $property = arr1[i1 += 1];
          var $propertySch = it.schema.properties[$property];
          if (!($propertySch && it.util.schemaHasRules($propertySch, it.RULES.all))) {
            $required[$required.length] = $property;
          }
        }
      }
    } else {
      var $required = $schema;
    }
  }
  if ($isData || $required.length) {
    var $currentErrorPath = it.errorPath,
      $loopRequired = $isData || $required.length >= it.opts.loopRequired,
      $ownProperties = it.opts.ownProperties;
    if ($breakOnError) {
      out += ' var missing' + ($lvl) + '; ';
      if ($loopRequired) {
        if (!$isData) {
          out += ' var ' + ($vSchema) + ' = validate.schema' + ($schemaPath) + '; ';
        }
        var $i = 'i' + $lvl,
          $propertyPath = 'schema' + $lvl + '[' + $i + ']',
          $missingProperty = '\' + ' + $propertyPath + ' + \'';
        if (it.opts._errorDataPathProperty) {
          it.errorPath = it.util.getPathExpr($currentErrorPath, $propertyPath, it.opts.jsonPointers);
        }
        out += ' var ' + ($valid) + ' = true; ';
        if ($isData) {
          out += ' if (schema' + ($lvl) + ' === undefined) ' + ($valid) + ' = true; else if (!Array.isArray(schema' + ($lvl) + ')) ' + ($valid) + ' = false; else {';
        }
        out += ' for (var ' + ($i) + ' = 0; ' + ($i) + ' < ' + ($vSchema) + '.length; ' + ($i) + '++) { ' + ($valid) + ' = ' + ($data) + '[' + ($vSchema) + '[' + ($i) + ']] !== undefined ';
        if ($ownProperties) {
          out += ' &&   Object.prototype.hasOwnProperty.call(' + ($data) + ', ' + ($vSchema) + '[' + ($i) + ']) ';
        }
        out += '; if (!' + ($valid) + ') break; } ';
        if ($isData) {
          out += '  }  ';
        }
        out += '  if (!' + ($valid) + ') {   ';
        var $$outStack = $$outStack || [];
        $$outStack.push(out);
        out = ''; /* istanbul ignore else */
        if (it.createErrors !== false) {
          out += ' { keyword: \'' + ('required') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { missingProperty: \'' + ($missingProperty) + '\' } ';
          if (it.opts.messages !== false) {
            out += ' , message: \'';
            if (it.opts._errorDataPathProperty) {
              out += 'is a required property';
            } else {
              out += 'should have required property \\\'' + ($missingProperty) + '\\\'';
            }
            out += '\' ';
          }
          if (it.opts.verbose) {
            out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
          }
          out += ' } ';
        } else {
          out += ' {} ';
        }
        var __err = out;
        out = $$outStack.pop();
        if (!it.compositeRule && $breakOnError) {
          /* istanbul ignore if */
          if (it.async) {
            out += ' throw new ValidationError([' + (__err) + ']); ';
          } else {
            out += ' validate.errors = [' + (__err) + ']; return false; ';
          }
        } else {
          out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
        }
        out += ' } else { ';
      } else {
        out += ' if ( ';
        var arr2 = $required;
        if (arr2) {
          var $propertyKey, $i = -1,
            l2 = arr2.length - 1;
          while ($i < l2) {
            $propertyKey = arr2[$i += 1];
            if ($i) {
              out += ' || ';
            }
            var $prop = it.util.getProperty($propertyKey),
              $useData = $data + $prop;
            out += ' ( ( ' + ($useData) + ' === undefined ';
            if ($ownProperties) {
              out += ' || ! Object.prototype.hasOwnProperty.call(' + ($data) + ', \'' + (it.util.escapeQuotes($propertyKey)) + '\') ';
            }
            out += ') && (missing' + ($lvl) + ' = ' + (it.util.toQuotedString(it.opts.jsonPointers ? $propertyKey : $prop)) + ') ) ';
          }
        }
        out += ') {  ';
        var $propertyPath = 'missing' + $lvl,
          $missingProperty = '\' + ' + $propertyPath + ' + \'';
        if (it.opts._errorDataPathProperty) {
          it.errorPath = it.opts.jsonPointers ? it.util.getPathExpr($currentErrorPath, $propertyPath, true) : $currentErrorPath + ' + ' + $propertyPath;
        }
        var $$outStack = $$outStack || [];
        $$outStack.push(out);
        out = ''; /* istanbul ignore else */
        if (it.createErrors !== false) {
          out += ' { keyword: \'' + ('required') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { missingProperty: \'' + ($missingProperty) + '\' } ';
          if (it.opts.messages !== false) {
            out += ' , message: \'';
            if (it.opts._errorDataPathProperty) {
              out += 'is a required property';
            } else {
              out += 'should have required property \\\'' + ($missingProperty) + '\\\'';
            }
            out += '\' ';
          }
          if (it.opts.verbose) {
            out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
          }
          out += ' } ';
        } else {
          out += ' {} ';
        }
        var __err = out;
        out = $$outStack.pop();
        if (!it.compositeRule && $breakOnError) {
          /* istanbul ignore if */
          if (it.async) {
            out += ' throw new ValidationError([' + (__err) + ']); ';
          } else {
            out += ' validate.errors = [' + (__err) + ']; return false; ';
          }
        } else {
          out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
        }
        out += ' } else { ';
      }
    } else {
      if ($loopRequired) {
        if (!$isData) {
          out += ' var ' + ($vSchema) + ' = validate.schema' + ($schemaPath) + '; ';
        }
        var $i = 'i' + $lvl,
          $propertyPath = 'schema' + $lvl + '[' + $i + ']',
          $missingProperty = '\' + ' + $propertyPath + ' + \'';
        if (it.opts._errorDataPathProperty) {
          it.errorPath = it.util.getPathExpr($currentErrorPath, $propertyPath, it.opts.jsonPointers);
        }
        if ($isData) {
          out += ' if (' + ($vSchema) + ' && !Array.isArray(' + ($vSchema) + ')) {  var err =   '; /* istanbul ignore else */
          if (it.createErrors !== false) {
            out += ' { keyword: \'' + ('required') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { missingProperty: \'' + ($missingProperty) + '\' } ';
            if (it.opts.messages !== false) {
              out += ' , message: \'';
              if (it.opts._errorDataPathProperty) {
                out += 'is a required property';
              } else {
                out += 'should have required property \\\'' + ($missingProperty) + '\\\'';
              }
              out += '\' ';
            }
            if (it.opts.verbose) {
              out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
            }
            out += ' } ';
          } else {
            out += ' {} ';
          }
          out += ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } else if (' + ($vSchema) + ' !== undefined) { ';
        }
        out += ' for (var ' + ($i) + ' = 0; ' + ($i) + ' < ' + ($vSchema) + '.length; ' + ($i) + '++) { if (' + ($data) + '[' + ($vSchema) + '[' + ($i) + ']] === undefined ';
        if ($ownProperties) {
          out += ' || ! Object.prototype.hasOwnProperty.call(' + ($data) + ', ' + ($vSchema) + '[' + ($i) + ']) ';
        }
        out += ') {  var err =   '; /* istanbul ignore else */
        if (it.createErrors !== false) {
          out += ' { keyword: \'' + ('required') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { missingProperty: \'' + ($missingProperty) + '\' } ';
          if (it.opts.messages !== false) {
            out += ' , message: \'';
            if (it.opts._errorDataPathProperty) {
              out += 'is a required property';
            } else {
              out += 'should have required property \\\'' + ($missingProperty) + '\\\'';
            }
            out += '\' ';
          }
          if (it.opts.verbose) {
            out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
          }
          out += ' } ';
        } else {
          out += ' {} ';
        }
        out += ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } } ';
        if ($isData) {
          out += '  }  ';
        }
      } else {
        var arr3 = $required;
        if (arr3) {
          var $propertyKey, i3 = -1,
            l3 = arr3.length - 1;
          while (i3 < l3) {
            $propertyKey = arr3[i3 += 1];
            var $prop = it.util.getProperty($propertyKey),
              $missingProperty = it.util.escapeQuotes($propertyKey),
              $useData = $data + $prop;
            if (it.opts._errorDataPathProperty) {
              it.errorPath = it.util.getPath($currentErrorPath, $propertyKey, it.opts.jsonPointers);
            }
            out += ' if ( ' + ($useData) + ' === undefined ';
            if ($ownProperties) {
              out += ' || ! Object.prototype.hasOwnProperty.call(' + ($data) + ', \'' + (it.util.escapeQuotes($propertyKey)) + '\') ';
            }
            out += ') {  var err =   '; /* istanbul ignore else */
            if (it.createErrors !== false) {
              out += ' { keyword: \'' + ('required') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { missingProperty: \'' + ($missingProperty) + '\' } ';
              if (it.opts.messages !== false) {
                out += ' , message: \'';
                if (it.opts._errorDataPathProperty) {
                  out += 'is a required property';
                } else {
                  out += 'should have required property \\\'' + ($missingProperty) + '\\\'';
                }
                out += '\' ';
              }
              if (it.opts.verbose) {
                out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
              }
              out += ' } ';
            } else {
              out += ' {} ';
            }
            out += ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } ';
          }
        }
      }
    }
    it.errorPath = $currentErrorPath;
  } else if ($breakOnError) {
    out += ' if (true) {';
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/uniqueItems.js":
/*!******************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/uniqueItems.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_uniqueItems(it, $keyword, $ruleType) {
  var out = ' ';
  var $lvl = it.level;
  var $dataLvl = it.dataLevel;
  var $schema = it.schema[$keyword];
  var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
  var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
  var $breakOnError = !it.opts.allErrors;
  var $data = 'data' + ($dataLvl || '');
  var $valid = 'valid' + $lvl;
  var $isData = it.opts.$data && $schema && $schema.$data,
    $schemaValue;
  if ($isData) {
    out += ' var schema' + ($lvl) + ' = ' + (it.util.getData($schema.$data, $dataLvl, it.dataPathArr)) + '; ';
    $schemaValue = 'schema' + $lvl;
  } else {
    $schemaValue = $schema;
  }
  if (($schema || $isData) && it.opts.uniqueItems !== false) {
    if ($isData) {
      out += ' var ' + ($valid) + '; if (' + ($schemaValue) + ' === false || ' + ($schemaValue) + ' === undefined) ' + ($valid) + ' = true; else if (typeof ' + ($schemaValue) + ' != \'boolean\') ' + ($valid) + ' = false; else { ';
    }
    out += ' var i = ' + ($data) + '.length , ' + ($valid) + ' = true , j; if (i > 1) { ';
    var $itemType = it.schema.items && it.schema.items.type,
      $typeIsArray = Array.isArray($itemType);
    if (!$itemType || $itemType == 'object' || $itemType == 'array' || ($typeIsArray && ($itemType.indexOf('object') >= 0 || $itemType.indexOf('array') >= 0))) {
      out += ' outer: for (;i--;) { for (j = i; j--;) { if (equal(' + ($data) + '[i], ' + ($data) + '[j])) { ' + ($valid) + ' = false; break outer; } } } ';
    } else {
      out += ' var itemIndices = {}, item; for (;i--;) { var item = ' + ($data) + '[i]; ';
      var $method = 'checkDataType' + ($typeIsArray ? 's' : '');
      out += ' if (' + (it.util[$method]($itemType, 'item', true)) + ') continue; ';
      if ($typeIsArray) {
        out += ' if (typeof item == \'string\') item = \'"\' + item; ';
      }
      out += ' if (typeof itemIndices[item] == \'number\') { ' + ($valid) + ' = false; j = itemIndices[item]; break; } itemIndices[item] = i; } ';
    }
    out += ' } ';
    if ($isData) {
      out += '  }  ';
    }
    out += ' if (!' + ($valid) + ') {   ';
    var $$outStack = $$outStack || [];
    $$outStack.push(out);
    out = ''; /* istanbul ignore else */
    if (it.createErrors !== false) {
      out += ' { keyword: \'' + ('uniqueItems') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { i: i, j: j } ';
      if (it.opts.messages !== false) {
        out += ' , message: \'should NOT have duplicate items (items ## \' + j + \' and \' + i + \' are identical)\' ';
      }
      if (it.opts.verbose) {
        out += ' , schema:  ';
        if ($isData) {
          out += 'validate.schema' + ($schemaPath);
        } else {
          out += '' + ($schema);
        }
        out += '         , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
      }
      out += ' } ';
    } else {
      out += ' {} ';
    }
    var __err = out;
    out = $$outStack.pop();
    if (!it.compositeRule && $breakOnError) {
      /* istanbul ignore if */
      if (it.async) {
        out += ' throw new ValidationError([' + (__err) + ']); ';
      } else {
        out += ' validate.errors = [' + (__err) + ']; return false; ';
      }
    } else {
      out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
    }
    out += ' } ';
    if ($breakOnError) {
      out += ' else { ';
    }
  } else {
    if ($breakOnError) {
      out += ' if (true) { ';
    }
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/dotjs/validate.js":
/*!***************************************************!*\
  !*** /app/node_modules/ajv/lib/dotjs/validate.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

module.exports = function generate_validate(it, $keyword, $ruleType) {
  var out = '';
  var $async = it.schema.$async === true,
    $refKeywords = it.util.schemaHasRulesExcept(it.schema, it.RULES.all, '$ref'),
    $id = it.self._getId(it.schema);
  if (it.isTop) {
    out += ' var validate = ';
    if ($async) {
      it.async = true;
      out += 'async ';
    }
    out += 'function(data, dataPath, parentData, parentDataProperty, rootData) { \'use strict\'; ';
    if ($id && (it.opts.sourceCode || it.opts.processCode)) {
      out += ' ' + ('/\*# sourceURL=' + $id + ' */') + ' ';
    }
  }
  if (typeof it.schema == 'boolean' || !($refKeywords || it.schema.$ref)) {
    var $keyword = 'false schema';
    var $lvl = it.level;
    var $dataLvl = it.dataLevel;
    var $schema = it.schema[$keyword];
    var $schemaPath = it.schemaPath + it.util.getProperty($keyword);
    var $errSchemaPath = it.errSchemaPath + '/' + $keyword;
    var $breakOnError = !it.opts.allErrors;
    var $errorKeyword;
    var $data = 'data' + ($dataLvl || '');
    var $valid = 'valid' + $lvl;
    if (it.schema === false) {
      if (it.isTop) {
        $breakOnError = true;
      } else {
        out += ' var ' + ($valid) + ' = false; ';
      }
      var $$outStack = $$outStack || [];
      $$outStack.push(out);
      out = ''; /* istanbul ignore else */
      if (it.createErrors !== false) {
        out += ' { keyword: \'' + ($errorKeyword || 'false schema') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: {} ';
        if (it.opts.messages !== false) {
          out += ' , message: \'boolean schema is false\' ';
        }
        if (it.opts.verbose) {
          out += ' , schema: false , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
        }
        out += ' } ';
      } else {
        out += ' {} ';
      }
      var __err = out;
      out = $$outStack.pop();
      if (!it.compositeRule && $breakOnError) {
        /* istanbul ignore if */
        if (it.async) {
          out += ' throw new ValidationError([' + (__err) + ']); ';
        } else {
          out += ' validate.errors = [' + (__err) + ']; return false; ';
        }
      } else {
        out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
      }
    } else {
      if (it.isTop) {
        if ($async) {
          out += ' return data; ';
        } else {
          out += ' validate.errors = null; return true; ';
        }
      } else {
        out += ' var ' + ($valid) + ' = true; ';
      }
    }
    if (it.isTop) {
      out += ' }; return validate; ';
    }
    return out;
  }
  if (it.isTop) {
    var $top = it.isTop,
      $lvl = it.level = 0,
      $dataLvl = it.dataLevel = 0,
      $data = 'data';
    it.rootId = it.resolve.fullPath(it.self._getId(it.root.schema));
    it.baseId = it.baseId || it.rootId;
    delete it.isTop;
    it.dataPathArr = [undefined];
    out += ' var vErrors = null; ';
    out += ' var errors = 0;     ';
    out += ' if (rootData === undefined) rootData = data; ';
  } else {
    var $lvl = it.level,
      $dataLvl = it.dataLevel,
      $data = 'data' + ($dataLvl || '');
    if ($id) it.baseId = it.resolve.url(it.baseId, $id);
    if ($async && !it.async) throw new Error('async schema in sync schema');
    out += ' var errs_' + ($lvl) + ' = errors;';
  }
  var $valid = 'valid' + $lvl,
    $breakOnError = !it.opts.allErrors,
    $closingBraces1 = '',
    $closingBraces2 = '';
  var $errorKeyword;
  var $typeSchema = it.schema.type,
    $typeIsArray = Array.isArray($typeSchema);
  if ($typeSchema && it.opts.nullable && it.schema.nullable === true) {
    if ($typeIsArray) {
      if ($typeSchema.indexOf('null') == -1) $typeSchema = $typeSchema.concat('null');
    } else if ($typeSchema != 'null') {
      $typeSchema = [$typeSchema, 'null'];
      $typeIsArray = true;
    }
  }
  if ($typeIsArray && $typeSchema.length == 1) {
    $typeSchema = $typeSchema[0];
    $typeIsArray = false;
  }
  if (it.schema.$ref && $refKeywords) {
    if (it.opts.extendRefs == 'fail') {
      throw new Error('$ref: validation keywords used in schema at path "' + it.errSchemaPath + '" (see option extendRefs)');
    } else if (it.opts.extendRefs !== true) {
      $refKeywords = false;
      it.logger.warn('$ref: keywords ignored in schema at path "' + it.errSchemaPath + '"');
    }
  }
  if (it.schema.$comment && it.opts.$comment) {
    out += ' ' + (it.RULES.all.$comment.code(it, '$comment'));
  }
  if ($typeSchema) {
    if (it.opts.coerceTypes) {
      var $coerceToTypes = it.util.coerceToTypes(it.opts.coerceTypes, $typeSchema);
    }
    var $rulesGroup = it.RULES.types[$typeSchema];
    if ($coerceToTypes || $typeIsArray || $rulesGroup === true || ($rulesGroup && !$shouldUseGroup($rulesGroup))) {
      var $schemaPath = it.schemaPath + '.type',
        $errSchemaPath = it.errSchemaPath + '/type';
      var $schemaPath = it.schemaPath + '.type',
        $errSchemaPath = it.errSchemaPath + '/type',
        $method = $typeIsArray ? 'checkDataTypes' : 'checkDataType';
      out += ' if (' + (it.util[$method]($typeSchema, $data, true)) + ') { ';
      if ($coerceToTypes) {
        var $dataType = 'dataType' + $lvl,
          $coerced = 'coerced' + $lvl;
        out += ' var ' + ($dataType) + ' = typeof ' + ($data) + '; ';
        if (it.opts.coerceTypes == 'array') {
          out += ' if (' + ($dataType) + ' == \'object\' && Array.isArray(' + ($data) + ')) ' + ($dataType) + ' = \'array\'; ';
        }
        out += ' var ' + ($coerced) + ' = undefined; ';
        var $bracesCoercion = '';
        var arr1 = $coerceToTypes;
        if (arr1) {
          var $type, $i = -1,
            l1 = arr1.length - 1;
          while ($i < l1) {
            $type = arr1[$i += 1];
            if ($i) {
              out += ' if (' + ($coerced) + ' === undefined) { ';
              $bracesCoercion += '}';
            }
            if (it.opts.coerceTypes == 'array' && $type != 'array') {
              out += ' if (' + ($dataType) + ' == \'array\' && ' + ($data) + '.length == 1) { ' + ($coerced) + ' = ' + ($data) + ' = ' + ($data) + '[0]; ' + ($dataType) + ' = typeof ' + ($data) + ';  } ';
            }
            if ($type == 'string') {
              out += ' if (' + ($dataType) + ' == \'number\' || ' + ($dataType) + ' == \'boolean\') ' + ($coerced) + ' = \'\' + ' + ($data) + '; else if (' + ($data) + ' === null) ' + ($coerced) + ' = \'\'; ';
            } else if ($type == 'number' || $type == 'integer') {
              out += ' if (' + ($dataType) + ' == \'boolean\' || ' + ($data) + ' === null || (' + ($dataType) + ' == \'string\' && ' + ($data) + ' && ' + ($data) + ' == +' + ($data) + ' ';
              if ($type == 'integer') {
                out += ' && !(' + ($data) + ' % 1)';
              }
              out += ')) ' + ($coerced) + ' = +' + ($data) + '; ';
            } else if ($type == 'boolean') {
              out += ' if (' + ($data) + ' === \'false\' || ' + ($data) + ' === 0 || ' + ($data) + ' === null) ' + ($coerced) + ' = false; else if (' + ($data) + ' === \'true\' || ' + ($data) + ' === 1) ' + ($coerced) + ' = true; ';
            } else if ($type == 'null') {
              out += ' if (' + ($data) + ' === \'\' || ' + ($data) + ' === 0 || ' + ($data) + ' === false) ' + ($coerced) + ' = null; ';
            } else if (it.opts.coerceTypes == 'array' && $type == 'array') {
              out += ' if (' + ($dataType) + ' == \'string\' || ' + ($dataType) + ' == \'number\' || ' + ($dataType) + ' == \'boolean\' || ' + ($data) + ' == null) ' + ($coerced) + ' = [' + ($data) + ']; ';
            }
          }
        }
        out += ' ' + ($bracesCoercion) + ' if (' + ($coerced) + ' === undefined) {   ';
        var $$outStack = $$outStack || [];
        $$outStack.push(out);
        out = ''; /* istanbul ignore else */
        if (it.createErrors !== false) {
          out += ' { keyword: \'' + ($errorKeyword || 'type') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { type: \'';
          if ($typeIsArray) {
            out += '' + ($typeSchema.join(","));
          } else {
            out += '' + ($typeSchema);
          }
          out += '\' } ';
          if (it.opts.messages !== false) {
            out += ' , message: \'should be ';
            if ($typeIsArray) {
              out += '' + ($typeSchema.join(","));
            } else {
              out += '' + ($typeSchema);
            }
            out += '\' ';
          }
          if (it.opts.verbose) {
            out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
          }
          out += ' } ';
        } else {
          out += ' {} ';
        }
        var __err = out;
        out = $$outStack.pop();
        if (!it.compositeRule && $breakOnError) {
          /* istanbul ignore if */
          if (it.async) {
            out += ' throw new ValidationError([' + (__err) + ']); ';
          } else {
            out += ' validate.errors = [' + (__err) + ']; return false; ';
          }
        } else {
          out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
        }
        out += ' } else {  ';
        var $parentData = $dataLvl ? 'data' + (($dataLvl - 1) || '') : 'parentData',
          $parentDataProperty = $dataLvl ? it.dataPathArr[$dataLvl] : 'parentDataProperty';
        out += ' ' + ($data) + ' = ' + ($coerced) + '; ';
        if (!$dataLvl) {
          out += 'if (' + ($parentData) + ' !== undefined)';
        }
        out += ' ' + ($parentData) + '[' + ($parentDataProperty) + '] = ' + ($coerced) + '; } ';
      } else {
        var $$outStack = $$outStack || [];
        $$outStack.push(out);
        out = ''; /* istanbul ignore else */
        if (it.createErrors !== false) {
          out += ' { keyword: \'' + ($errorKeyword || 'type') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { type: \'';
          if ($typeIsArray) {
            out += '' + ($typeSchema.join(","));
          } else {
            out += '' + ($typeSchema);
          }
          out += '\' } ';
          if (it.opts.messages !== false) {
            out += ' , message: \'should be ';
            if ($typeIsArray) {
              out += '' + ($typeSchema.join(","));
            } else {
              out += '' + ($typeSchema);
            }
            out += '\' ';
          }
          if (it.opts.verbose) {
            out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
          }
          out += ' } ';
        } else {
          out += ' {} ';
        }
        var __err = out;
        out = $$outStack.pop();
        if (!it.compositeRule && $breakOnError) {
          /* istanbul ignore if */
          if (it.async) {
            out += ' throw new ValidationError([' + (__err) + ']); ';
          } else {
            out += ' validate.errors = [' + (__err) + ']; return false; ';
          }
        } else {
          out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
        }
      }
      out += ' } ';
    }
  }
  if (it.schema.$ref && !$refKeywords) {
    out += ' ' + (it.RULES.all.$ref.code(it, '$ref')) + ' ';
    if ($breakOnError) {
      out += ' } if (errors === ';
      if ($top) {
        out += '0';
      } else {
        out += 'errs_' + ($lvl);
      }
      out += ') { ';
      $closingBraces2 += '}';
    }
  } else {
    var arr2 = it.RULES;
    if (arr2) {
      var $rulesGroup, i2 = -1,
        l2 = arr2.length - 1;
      while (i2 < l2) {
        $rulesGroup = arr2[i2 += 1];
        if ($shouldUseGroup($rulesGroup)) {
          if ($rulesGroup.type) {
            out += ' if (' + (it.util.checkDataType($rulesGroup.type, $data)) + ') { ';
          }
          if (it.opts.useDefaults && !it.compositeRule) {
            if ($rulesGroup.type == 'object' && it.schema.properties) {
              var $schema = it.schema.properties,
                $schemaKeys = Object.keys($schema);
              var arr3 = $schemaKeys;
              if (arr3) {
                var $propertyKey, i3 = -1,
                  l3 = arr3.length - 1;
                while (i3 < l3) {
                  $propertyKey = arr3[i3 += 1];
                  var $sch = $schema[$propertyKey];
                  if ($sch.default !== undefined) {
                    var $passData = $data + it.util.getProperty($propertyKey);
                    out += '  if (' + ($passData) + ' === undefined ';
                    if (it.opts.useDefaults == 'empty') {
                      out += ' || ' + ($passData) + ' === null || ' + ($passData) + ' === \'\' ';
                    }
                    out += ' ) ' + ($passData) + ' = ';
                    if (it.opts.useDefaults == 'shared') {
                      out += ' ' + (it.useDefault($sch.default)) + ' ';
                    } else {
                      out += ' ' + (JSON.stringify($sch.default)) + ' ';
                    }
                    out += '; ';
                  }
                }
              }
            } else if ($rulesGroup.type == 'array' && Array.isArray(it.schema.items)) {
              var arr4 = it.schema.items;
              if (arr4) {
                var $sch, $i = -1,
                  l4 = arr4.length - 1;
                while ($i < l4) {
                  $sch = arr4[$i += 1];
                  if ($sch.default !== undefined) {
                    var $passData = $data + '[' + $i + ']';
                    out += '  if (' + ($passData) + ' === undefined ';
                    if (it.opts.useDefaults == 'empty') {
                      out += ' || ' + ($passData) + ' === null || ' + ($passData) + ' === \'\' ';
                    }
                    out += ' ) ' + ($passData) + ' = ';
                    if (it.opts.useDefaults == 'shared') {
                      out += ' ' + (it.useDefault($sch.default)) + ' ';
                    } else {
                      out += ' ' + (JSON.stringify($sch.default)) + ' ';
                    }
                    out += '; ';
                  }
                }
              }
            }
          }
          var arr5 = $rulesGroup.rules;
          if (arr5) {
            var $rule, i5 = -1,
              l5 = arr5.length - 1;
            while (i5 < l5) {
              $rule = arr5[i5 += 1];
              if ($shouldUseRule($rule)) {
                var $code = $rule.code(it, $rule.keyword, $rulesGroup.type);
                if ($code) {
                  out += ' ' + ($code) + ' ';
                  if ($breakOnError) {
                    $closingBraces1 += '}';
                  }
                }
              }
            }
          }
          if ($breakOnError) {
            out += ' ' + ($closingBraces1) + ' ';
            $closingBraces1 = '';
          }
          if ($rulesGroup.type) {
            out += ' } ';
            if ($typeSchema && $typeSchema === $rulesGroup.type && !$coerceToTypes) {
              out += ' else { ';
              var $schemaPath = it.schemaPath + '.type',
                $errSchemaPath = it.errSchemaPath + '/type';
              var $$outStack = $$outStack || [];
              $$outStack.push(out);
              out = ''; /* istanbul ignore else */
              if (it.createErrors !== false) {
                out += ' { keyword: \'' + ($errorKeyword || 'type') + '\' , dataPath: (dataPath || \'\') + ' + (it.errorPath) + ' , schemaPath: ' + (it.util.toQuotedString($errSchemaPath)) + ' , params: { type: \'';
                if ($typeIsArray) {
                  out += '' + ($typeSchema.join(","));
                } else {
                  out += '' + ($typeSchema);
                }
                out += '\' } ';
                if (it.opts.messages !== false) {
                  out += ' , message: \'should be ';
                  if ($typeIsArray) {
                    out += '' + ($typeSchema.join(","));
                  } else {
                    out += '' + ($typeSchema);
                  }
                  out += '\' ';
                }
                if (it.opts.verbose) {
                  out += ' , schema: validate.schema' + ($schemaPath) + ' , parentSchema: validate.schema' + (it.schemaPath) + ' , data: ' + ($data) + ' ';
                }
                out += ' } ';
              } else {
                out += ' {} ';
              }
              var __err = out;
              out = $$outStack.pop();
              if (!it.compositeRule && $breakOnError) {
                /* istanbul ignore if */
                if (it.async) {
                  out += ' throw new ValidationError([' + (__err) + ']); ';
                } else {
                  out += ' validate.errors = [' + (__err) + ']; return false; ';
                }
              } else {
                out += ' var err = ' + (__err) + ';  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ';
              }
              out += ' } ';
            }
          }
          if ($breakOnError) {
            out += ' if (errors === ';
            if ($top) {
              out += '0';
            } else {
              out += 'errs_' + ($lvl);
            }
            out += ') { ';
            $closingBraces2 += '}';
          }
        }
      }
    }
  }
  if ($breakOnError) {
    out += ' ' + ($closingBraces2) + ' ';
  }
  if ($top) {
    if ($async) {
      out += ' if (errors === 0) return data;           ';
      out += ' else throw new ValidationError(vErrors); ';
    } else {
      out += ' validate.errors = vErrors; ';
      out += ' return errors === 0;       ';
    }
    out += ' }; return validate;';
  } else {
    out += ' var ' + ($valid) + ' = errors === errs_' + ($lvl) + ';';
  }
  out = it.util.cleanUpCode(out);
  if ($top) {
    out = it.util.finalCleanUpCode(out, $async);
  }

  function $shouldUseGroup($rulesGroup) {
    var rules = $rulesGroup.rules;
    for (var i = 0; i < rules.length; i++)
      if ($shouldUseRule(rules[i])) return true;
  }

  function $shouldUseRule($rule) {
    return it.schema[$rule.keyword] !== undefined || ($rule.implements && $ruleImplementsSomeKeyword($rule));
  }

  function $ruleImplementsSomeKeyword($rule) {
    var impl = $rule.implements;
    for (var i = 0; i < impl.length; i++)
      if (it.schema[impl[i]] !== undefined) return true;
  }
  return out;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/keyword.js":
/*!********************************************!*\
  !*** /app/node_modules/ajv/lib/keyword.js ***!
  \********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var IDENTIFIER = /^[a-z_$][a-z0-9_$-]*$/i;
var customRuleCode = __webpack_require__(/*! ./dotjs/custom */ "../../node_modules/ajv/lib/dotjs/custom.js");

module.exports = {
  add: addKeyword,
  get: getKeyword,
  remove: removeKeyword
};

/**
 * Define custom keyword
 * @this  Ajv
 * @param {String} keyword custom keyword, should be unique (including different from all standard, custom and macro keywords).
 * @param {Object} definition keyword definition object with properties `type` (type(s) which the keyword applies to), `validate` or `compile`.
 * @return {Ajv} this for method chaining
 */
function addKeyword(keyword, definition) {
  /* jshint validthis: true */
  /* eslint no-shadow: 0 */
  var RULES = this.RULES;

  if (RULES.keywords[keyword])
    throw new Error('Keyword ' + keyword + ' is already defined');

  if (!IDENTIFIER.test(keyword))
    throw new Error('Keyword ' + keyword + ' is not a valid identifier');

  if (definition) {
    if (definition.macro && definition.valid !== undefined)
      throw new Error('"valid" option cannot be used with macro keywords');

    var dataType = definition.type;
    if (Array.isArray(dataType)) {
      var i, len = dataType.length;
      for (i=0; i<len; i++) checkDataType(dataType[i]);
      for (i=0; i<len; i++) _addRule(keyword, dataType[i], definition);
    } else {
      if (dataType) checkDataType(dataType);
      _addRule(keyword, dataType, definition);
    }

    var $data = definition.$data === true && this._opts.$data;
    if ($data && !definition.validate)
      throw new Error('$data support: "validate" function is not defined');

    var metaSchema = definition.metaSchema;
    if (metaSchema) {
      if ($data) {
        metaSchema = {
          anyOf: [
            metaSchema,
            { '$ref': 'https://raw.githubusercontent.com/epoberezkin/ajv/master/lib/refs/data.json#' }
          ]
        };
      }
      definition.validateSchema = this.compile(metaSchema, true);
    }
  }

  RULES.keywords[keyword] = RULES.all[keyword] = true;


  function _addRule(keyword, dataType, definition) {
    var ruleGroup;
    for (var i=0; i<RULES.length; i++) {
      var rg = RULES[i];
      if (rg.type == dataType) {
        ruleGroup = rg;
        break;
      }
    }

    if (!ruleGroup) {
      ruleGroup = { type: dataType, rules: [] };
      RULES.push(ruleGroup);
    }

    var rule = {
      keyword: keyword,
      definition: definition,
      custom: true,
      code: customRuleCode,
      implements: definition.implements
    };
    ruleGroup.rules.push(rule);
    RULES.custom[keyword] = rule;
  }


  function checkDataType(dataType) {
    if (!RULES.types[dataType]) throw new Error('Unknown type ' + dataType);
  }

  return this;
}


/**
 * Get keyword
 * @this  Ajv
 * @param {String} keyword pre-defined or custom keyword.
 * @return {Object|Boolean} custom keyword definition, `true` if it is a predefined keyword, `false` otherwise.
 */
function getKeyword(keyword) {
  /* jshint validthis: true */
  var rule = this.RULES.custom[keyword];
  return rule ? rule.definition : this.RULES.keywords[keyword] || false;
}


/**
 * Remove keyword
 * @this  Ajv
 * @param {String} keyword pre-defined or custom keyword.
 * @return {Ajv} this for method chaining
 */
function removeKeyword(keyword) {
  /* jshint validthis: true */
  var RULES = this.RULES;
  delete RULES.keywords[keyword];
  delete RULES.all[keyword];
  delete RULES.custom[keyword];
  for (var i=0; i<RULES.length; i++) {
    var rules = RULES[i].rules;
    for (var j=0; j<rules.length; j++) {
      if (rules[j].keyword == keyword) {
        rules.splice(j, 1);
        break;
      }
    }
  }
  return this;
}


/***/ }),

/***/ "../../node_modules/ajv/lib/refs/data.json":
/*!************************************************!*\
  !*** /app/node_modules/ajv/lib/refs/data.json ***!
  \************************************************/
/*! exports provided: $schema, $id, description, type, required, properties, additionalProperties, default */
/***/ (function(module) {

module.exports = {"$schema":"http://json-schema.org/draft-07/schema#","$id":"https://raw.githubusercontent.com/epoberezkin/ajv/master/lib/refs/data.json#","description":"Meta-schema for $data reference (JSON Schema extension proposal)","type":"object","required":["$data"],"properties":{"$data":{"type":"string","anyOf":[{"format":"relative-json-pointer"},{"format":"json-pointer"}]}},"additionalProperties":false};

/***/ }),

/***/ "../../node_modules/ajv/lib/refs/json-schema-draft-07.json":
/*!****************************************************************!*\
  !*** /app/node_modules/ajv/lib/refs/json-schema-draft-07.json ***!
  \****************************************************************/
/*! exports provided: $schema, $id, title, definitions, type, properties, default */
/***/ (function(module) {

module.exports = {"$schema":"http://json-schema.org/draft-07/schema#","$id":"http://json-schema.org/draft-07/schema#","title":"Core schema meta-schema","definitions":{"schemaArray":{"type":"array","minItems":1,"items":{"$ref":"#"}},"nonNegativeInteger":{"type":"integer","minimum":0},"nonNegativeIntegerDefault0":{"allOf":[{"$ref":"#/definitions/nonNegativeInteger"},{"default":0}]},"simpleTypes":{"enum":["array","boolean","integer","null","number","object","string"]},"stringArray":{"type":"array","items":{"type":"string"},"uniqueItems":true,"default":[]}},"type":["object","boolean"],"properties":{"$id":{"type":"string","format":"uri-reference"},"$schema":{"type":"string","format":"uri"},"$ref":{"type":"string","format":"uri-reference"},"$comment":{"type":"string"},"title":{"type":"string"},"description":{"type":"string"},"default":true,"readOnly":{"type":"boolean","default":false},"examples":{"type":"array","items":true},"multipleOf":{"type":"number","exclusiveMinimum":0},"maximum":{"type":"number"},"exclusiveMaximum":{"type":"number"},"minimum":{"type":"number"},"exclusiveMinimum":{"type":"number"},"maxLength":{"$ref":"#/definitions/nonNegativeInteger"},"minLength":{"$ref":"#/definitions/nonNegativeIntegerDefault0"},"pattern":{"type":"string","format":"regex"},"additionalItems":{"$ref":"#"},"items":{"anyOf":[{"$ref":"#"},{"$ref":"#/definitions/schemaArray"}],"default":true},"maxItems":{"$ref":"#/definitions/nonNegativeInteger"},"minItems":{"$ref":"#/definitions/nonNegativeIntegerDefault0"},"uniqueItems":{"type":"boolean","default":false},"contains":{"$ref":"#"},"maxProperties":{"$ref":"#/definitions/nonNegativeInteger"},"minProperties":{"$ref":"#/definitions/nonNegativeIntegerDefault0"},"required":{"$ref":"#/definitions/stringArray"},"additionalProperties":{"$ref":"#"},"definitions":{"type":"object","additionalProperties":{"$ref":"#"},"default":{}},"properties":{"type":"object","additionalProperties":{"$ref":"#"},"default":{}},"patternProperties":{"type":"object","additionalProperties":{"$ref":"#"},"propertyNames":{"format":"regex"},"default":{}},"dependencies":{"type":"object","additionalProperties":{"anyOf":[{"$ref":"#"},{"$ref":"#/definitions/stringArray"}]}},"propertyNames":{"$ref":"#"},"const":true,"enum":{"type":"array","items":true,"minItems":1,"uniqueItems":true},"type":{"anyOf":[{"$ref":"#/definitions/simpleTypes"},{"type":"array","items":{"$ref":"#/definitions/simpleTypes"},"minItems":1,"uniqueItems":true}]},"format":{"type":"string"},"contentMediaType":{"type":"string"},"contentEncoding":{"type":"string"},"if":{"$ref":"#"},"then":{"$ref":"#"},"else":{"$ref":"#"},"allOf":{"$ref":"#/definitions/schemaArray"},"anyOf":{"$ref":"#/definitions/schemaArray"},"oneOf":{"$ref":"#/definitions/schemaArray"},"not":{"$ref":"#"}},"default":true};

/***/ }),

/***/ "../../node_modules/fast-deep-equal/index.js":
/*!**************************************************!*\
  !*** /app/node_modules/fast-deep-equal/index.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var isArray = Array.isArray;
var keyList = Object.keys;
var hasProp = Object.prototype.hasOwnProperty;

module.exports = function equal(a, b) {
  if (a === b) return true;

  if (a && b && typeof a == 'object' && typeof b == 'object') {
    var arrA = isArray(a)
      , arrB = isArray(b)
      , i
      , length
      , key;

    if (arrA && arrB) {
      length = a.length;
      if (length != b.length) return false;
      for (i = length; i-- !== 0;)
        if (!equal(a[i], b[i])) return false;
      return true;
    }

    if (arrA != arrB) return false;

    var dateA = a instanceof Date
      , dateB = b instanceof Date;
    if (dateA != dateB) return false;
    if (dateA && dateB) return a.getTime() == b.getTime();

    var regexpA = a instanceof RegExp
      , regexpB = b instanceof RegExp;
    if (regexpA != regexpB) return false;
    if (regexpA && regexpB) return a.toString() == b.toString();

    var keys = keyList(a);
    length = keys.length;

    if (length !== keyList(b).length)
      return false;

    for (i = length; i-- !== 0;)
      if (!hasProp.call(b, keys[i])) return false;

    for (i = length; i-- !== 0;) {
      key = keys[i];
      if (!equal(a[key], b[key])) return false;
    }

    return true;
  }

  return a!==a && b!==b;
};


/***/ }),

/***/ "../../node_modules/fast-json-stable-stringify/index.js":
/*!*************************************************************!*\
  !*** /app/node_modules/fast-json-stable-stringify/index.js ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = function (data, opts) {
    if (!opts) opts = {};
    if (typeof opts === 'function') opts = { cmp: opts };
    var cycles = (typeof opts.cycles === 'boolean') ? opts.cycles : false;

    var cmp = opts.cmp && (function (f) {
        return function (node) {
            return function (a, b) {
                var aobj = { key: a, value: node[a] };
                var bobj = { key: b, value: node[b] };
                return f(aobj, bobj);
            };
        };
    })(opts.cmp);

    var seen = [];
    return (function stringify (node) {
        if (node && node.toJSON && typeof node.toJSON === 'function') {
            node = node.toJSON();
        }

        if (node === undefined) return;
        if (typeof node == 'number') return isFinite(node) ? '' + node : 'null';
        if (typeof node !== 'object') return JSON.stringify(node);

        var i, out;
        if (Array.isArray(node)) {
            out = '[';
            for (i = 0; i < node.length; i++) {
                if (i) out += ',';
                out += stringify(node[i]) || 'null';
            }
            return out + ']';
        }

        if (node === null) return 'null';

        if (seen.indexOf(node) !== -1) {
            if (cycles) return JSON.stringify('__cycle__');
            throw new TypeError('Converting circular structure to JSON');
        }

        var seenIndex = seen.push(node) - 1;
        var keys = Object.keys(node).sort(cmp && cmp(node));
        out = '';
        for (i = 0; i < keys.length; i++) {
            var key = keys[i];
            var value = stringify(node[key]);

            if (!value) continue;
            if (out) out += ',';
            out += JSON.stringify(key) + ':' + value;
        }
        seen.splice(seenIndex, 1);
        return '{' + out + '}';
    })(data);
};


/***/ }),

/***/ "../../node_modules/json-schema-traverse/index.js":
/*!*******************************************************!*\
  !*** /app/node_modules/json-schema-traverse/index.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var traverse = module.exports = function (schema, opts, cb) {
  // Legacy support for v0.3.1 and earlier.
  if (typeof opts == 'function') {
    cb = opts;
    opts = {};
  }

  cb = opts.cb || cb;
  var pre = (typeof cb == 'function') ? cb : cb.pre || function() {};
  var post = cb.post || function() {};

  _traverse(opts, pre, post, schema, '', schema);
};


traverse.keywords = {
  additionalItems: true,
  items: true,
  contains: true,
  additionalProperties: true,
  propertyNames: true,
  not: true
};

traverse.arrayKeywords = {
  items: true,
  allOf: true,
  anyOf: true,
  oneOf: true
};

traverse.propsKeywords = {
  definitions: true,
  properties: true,
  patternProperties: true,
  dependencies: true
};

traverse.skipKeywords = {
  default: true,
  enum: true,
  const: true,
  required: true,
  maximum: true,
  minimum: true,
  exclusiveMaximum: true,
  exclusiveMinimum: true,
  multipleOf: true,
  maxLength: true,
  minLength: true,
  pattern: true,
  format: true,
  maxItems: true,
  minItems: true,
  uniqueItems: true,
  maxProperties: true,
  minProperties: true
};


function _traverse(opts, pre, post, schema, jsonPtr, rootSchema, parentJsonPtr, parentKeyword, parentSchema, keyIndex) {
  if (schema && typeof schema == 'object' && !Array.isArray(schema)) {
    pre(schema, jsonPtr, rootSchema, parentJsonPtr, parentKeyword, parentSchema, keyIndex);
    for (var key in schema) {
      var sch = schema[key];
      if (Array.isArray(sch)) {
        if (key in traverse.arrayKeywords) {
          for (var i=0; i<sch.length; i++)
            _traverse(opts, pre, post, sch[i], jsonPtr + '/' + key + '/' + i, rootSchema, jsonPtr, key, schema, i);
        }
      } else if (key in traverse.propsKeywords) {
        if (sch && typeof sch == 'object') {
          for (var prop in sch)
            _traverse(opts, pre, post, sch[prop], jsonPtr + '/' + key + '/' + escapeJsonPtr(prop), rootSchema, jsonPtr, key, schema, prop);
        }
      } else if (key in traverse.keywords || (opts.allKeys && !(key in traverse.skipKeywords))) {
        _traverse(opts, pre, post, sch, jsonPtr + '/' + key, rootSchema, jsonPtr, key, schema);
      }
    }
    post(schema, jsonPtr, rootSchema, parentJsonPtr, parentKeyword, parentSchema, keyIndex);
  }
}


function escapeJsonPtr(str) {
  return str.replace(/~/g, '~0').replace(/\//g, '~1');
}


/***/ }),

/***/ "../../node_modules/lit-element/lib/css-tag.js":
/*!****************************************************!*\
  !*** /app/node_modules/lit-element/lib/css-tag.js ***!
  \****************************************************/
/*! exports provided: supportsAdoptingStyleSheets, CSSResult, css */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "supportsAdoptingStyleSheets", function() { return supportsAdoptingStyleSheets; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "CSSResult", function() { return CSSResult; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "css", function() { return css; });
/**
@license
Copyright (c) 2019 The Polymer Project Authors. All rights reserved.
This code may only be used under the BSD style license found at
http://polymer.github.io/LICENSE.txt The complete set of authors may be found at
http://polymer.github.io/AUTHORS.txt The complete set of contributors may be
found at http://polymer.github.io/CONTRIBUTORS.txt Code distributed by Google as
part of the polymer project is also subject to an additional IP rights grant
found at http://polymer.github.io/PATENTS.txt
*/
const supportsAdoptingStyleSheets = ('adoptedStyleSheets' in Document.prototype);
class CSSResult {
    constructor(cssText) { this.cssText = cssText; }
    // Note, this is a getter so that it's lazy. In practice, this means
    // stylesheets are not created until the first element instance is made.
    get styleSheet() {
        if (this._styleSheet === undefined) {
            // Note, if `adoptedStyleSheets` is supported then we assume CSSStyleSheet
            // is constructable.
            if (supportsAdoptingStyleSheets) {
                this._styleSheet = new CSSStyleSheet();
                this._styleSheet.replaceSync(this.cssText);
            }
            else {
                this._styleSheet = null;
            }
        }
        return this._styleSheet;
    }
}
const textFromCSSResult = (value) => {
    if (value instanceof CSSResult) {
        return value.cssText;
    }
    else {
        throw new Error(`Value passed to 'css' function must be a 'css' function result: ${value}.`);
    }
};
const css = (strings, ...values) => {
    const cssText = values.reduce((acc, v, idx) => acc + textFromCSSResult(v) + strings[idx + 1], strings[0]);
    return new CSSResult(cssText);
};


/***/ }),

/***/ "../../node_modules/lit-element/lib/decorators.js":
/*!*******************************************************!*\
  !*** /app/node_modules/lit-element/lib/decorators.js ***!
  \*******************************************************/
/*! exports provided: customElement, property, query, queryAll, eventOptions */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "customElement", function() { return customElement; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "property", function() { return property; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "query", function() { return query; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "queryAll", function() { return queryAll; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "eventOptions", function() { return eventOptions; });
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
const legacyCustomElement = (tagName, clazz) => {
    window.customElements.define(tagName, clazz);
    // Cast as any because TS doesn't recognize the return type as being a
    // subtype of the decorated class when clazz is typed as
    // `Constructor<HTMLElement>` for some reason.
    // `Constructor<HTMLElement>` is helpful to make sure the decorator is
    // applied to elements however.
    return clazz;
};
const standardCustomElement = (tagName, descriptor) => {
    const { kind, elements } = descriptor;
    return {
        kind,
        elements,
        // This callback is called once the class is otherwise fully defined
        finisher(clazz) {
            window.customElements.define(tagName, clazz);
        }
    };
};
/**
 * Class decorator factory that defines the decorated class as a custom element.
 *
 * @param tagName the name of the custom element to define
 *
 * In TypeScript, the `tagName` passed to `customElement` should be a key of the
 * `HTMLElementTagNameMap` interface. To add your element to the interface,
 * declare the interface in this module:
 *
 *     @customElement('my-element')
 *     export class MyElement extends LitElement {}
 *
 *     declare global {
 *       interface HTMLElementTagNameMap {
 *         'my-element': MyElement;
 *       }
 *     }
 *
 */
const customElement = (tagName) => (classOrDescriptor) => (typeof classOrDescriptor === 'function')
    ? legacyCustomElement(tagName, classOrDescriptor)
    : standardCustomElement(tagName, classOrDescriptor);
const standardProperty = (options, element) => {
    // When decorating an accessor, pass it through and add property metadata.
    // Note, the `hasOwnProperty` check in `createProperty` ensures we don't
    // stomp over the user's accessor.
    if (element.kind === 'method' && element.descriptor &&
        !('value' in element.descriptor)) {
        return Object.assign({}, element, { finisher(clazz) {
                clazz.createProperty(element.key, options);
            } });
    }
    else {
        // createProperty() takes care of defining the property, but we still
        // must return some kind of descriptor, so return a descriptor for an
        // unused prototype field. The finisher calls createProperty().
        return {
            kind: 'field',
            key: Symbol(),
            placement: 'own',
            descriptor: {},
            // When @babel/plugin-proposal-decorators implements initializers,
            // do this instead of the initializer below. See:
            // https://github.com/babel/babel/issues/9260 extras: [
            //   {
            //     kind: 'initializer',
            //     placement: 'own',
            //     initializer: descriptor.initializer,
            //   }
            // ],
            initializer() {
                if (typeof element.initializer === 'function') {
                    this[element.key] = element.initializer.call(this);
                }
            },
            finisher(clazz) {
                clazz.createProperty(element.key, options);
            }
        };
    }
};
const legacyProperty = (options, proto, name) => {
    proto.constructor.createProperty(name, options);
};
/**
 * A property decorator which creates a LitElement property which reflects a
 * corresponding attribute value. A `PropertyDeclaration` may optionally be
 * supplied to configure property features.
 *
 * @ExportDecoratedItems
 */
function property(options) {
    return (protoOrDescriptor, name) => (name !== undefined)
        ? legacyProperty(options, protoOrDescriptor, name)
        : standardProperty(options, protoOrDescriptor);
}
/**
 * A property decorator that converts a class property into a getter that
 * executes a querySelector on the element's renderRoot.
 */
const query = _query((target, selector) => target.querySelector(selector));
/**
 * A property decorator that converts a class property into a getter
 * that executes a querySelectorAll on the element's renderRoot.
 */
const queryAll = _query((target, selector) => target.querySelectorAll(selector));
const legacyQuery = (descriptor, proto, name) => { Object.defineProperty(proto, name, descriptor); };
const standardQuery = (descriptor, element) => ({
    kind: 'method',
    placement: 'prototype',
    key: element.key,
    descriptor,
});
/**
 * Base-implementation of `@query` and `@queryAll` decorators.
 *
 * @param queryFn exectute a `selector` (ie, querySelector or querySelectorAll)
 * against `target`.
 * @suppress {visibility} The descriptor accesses an internal field on the
 * element.
 */
function _query(queryFn) {
    return (selector) => (protoOrDescriptor, name) => {
        const descriptor = {
            get() { return queryFn(this.renderRoot, selector); },
            enumerable: true,
            configurable: true,
        };
        return (name !== undefined)
            ? legacyQuery(descriptor, protoOrDescriptor, name)
            : standardQuery(descriptor, protoOrDescriptor);
    };
}
const standardEventOptions = (options, element) => {
    return Object.assign({}, element, { finisher(clazz) {
            Object.assign(clazz.prototype[element.key], options);
        } });
};
const legacyEventOptions = (options, proto, name) => { Object.assign(proto[name], options); };
/**
 * Adds event listener options to a method used as an event listener in a
 * lit-html template.
 *
 * @param options An object that specifis event listener options as accepted by
 * `EventTarget#addEventListener` and `EventTarget#removeEventListener`.
 *
 * Current browsers support the `capture`, `passive`, and `once` options. See:
 * https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener#Parameters
 *
 * @example
 *
 *     class MyElement {
 *
 *       clicked = false;
 *
 *       render() {
 *         return html`<div @click=${this._onClick}`><button></button></div>`;
 *       }
 *
 *       @eventOptions({capture: true})
 *       _onClick(e) {
 *         this.clicked = true;
 *       }
 *     }
 */
const eventOptions = (options) => 
// Return value typed as any to prevent TypeScript from complaining that
// standard decorator function signature does not match TypeScript decorator
// signature
// TODO(kschaaf): unclear why it was only failing on this decorator and not
// the others
((protoOrDescriptor, name) => (name !== undefined)
    ? legacyEventOptions(options, protoOrDescriptor, name)
    : standardEventOptions(options, protoOrDescriptor));


/***/ }),

/***/ "../../node_modules/lit-element/lib/updating-element.js":
/*!*************************************************************!*\
  !*** /app/node_modules/lit-element/lib/updating-element.js ***!
  \*************************************************************/
/*! exports provided: JSCompiler_renameProperty, defaultConverter, notEqual, UpdatingElement */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "JSCompiler_renameProperty", function() { return JSCompiler_renameProperty; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "defaultConverter", function() { return defaultConverter; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "notEqual", function() { return notEqual; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "UpdatingElement", function() { return UpdatingElement; });
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
/**
 * When using Closure Compiler, JSCompiler_renameProperty(property, object) is
 * replaced at compile time by the munged name for object[property]. We cannot
 * alias this function, so we have to use a small shim that has the same
 * behavior when not compiling.
 */
const JSCompiler_renameProperty = (prop, _obj) => prop;
const defaultConverter = {
    toAttribute(value, type) {
        switch (type) {
            case Boolean:
                return value ? '' : null;
            case Object:
            case Array:
                // if the value is `null` or `undefined` pass this through
                // to allow removing/no change behavior.
                return value == null ? value : JSON.stringify(value);
        }
        return value;
    },
    fromAttribute(value, type) {
        switch (type) {
            case Boolean:
                return value !== null;
            case Number:
                return value === null ? null : Number(value);
            case Object:
            case Array:
                return JSON.parse(value);
        }
        return value;
    }
};
/**
 * Change function that returns true if `value` is different from `oldValue`.
 * This method is used as the default for a property's `hasChanged` function.
 */
const notEqual = (value, old) => {
    // This ensures (old==NaN, value==NaN) always returns false
    return old !== value && (old === old || value === value);
};
const defaultPropertyDeclaration = {
    attribute: true,
    type: String,
    converter: defaultConverter,
    reflect: false,
    hasChanged: notEqual
};
const microtaskPromise = Promise.resolve(true);
const STATE_HAS_UPDATED = 1;
const STATE_UPDATE_REQUESTED = 1 << 2;
const STATE_IS_REFLECTING_TO_ATTRIBUTE = 1 << 3;
const STATE_IS_REFLECTING_TO_PROPERTY = 1 << 4;
const STATE_HAS_CONNECTED = 1 << 5;
/**
 * Base element class which manages element properties and attributes. When
 * properties change, the `update` method is asynchronously called. This method
 * should be supplied by subclassers to render updates as desired.
 */
class UpdatingElement extends HTMLElement {
    constructor() {
        super();
        this._updateState = 0;
        this._instanceProperties = undefined;
        this._updatePromise = microtaskPromise;
        this._hasConnectedResolver = undefined;
        /**
         * Map with keys for any properties that have changed since the last
         * update cycle with previous values.
         */
        this._changedProperties = new Map();
        /**
         * Map with keys of properties that should be reflected when updated.
         */
        this._reflectingProperties = undefined;
        this.initialize();
    }
    /**
     * Returns a list of attributes corresponding to the registered properties.
     * @nocollapse
     */
    static get observedAttributes() {
        // note: piggy backing on this to ensure we're _finalized.
        this._finalize();
        const attributes = [];
        // Use forEach so this works even if for/of loops are compiled to for loops
        // expecting arrays
        this._classProperties.forEach((v, p) => {
            const attr = this._attributeNameForProperty(p, v);
            if (attr !== undefined) {
                this._attributeToPropertyMap.set(attr, p);
                attributes.push(attr);
            }
        });
        return attributes;
    }
    /**
     * Ensures the private `_classProperties` property metadata is created.
     * In addition to `_finalize` this is also called in `createProperty` to
     * ensure the `@property` decorator can add property metadata.
     */
    /** @nocollapse */
    static _ensureClassProperties() {
        // ensure private storage for property declarations.
        if (!this.hasOwnProperty(JSCompiler_renameProperty('_classProperties', this))) {
            this._classProperties = new Map();
            // NOTE: Workaround IE11 not supporting Map constructor argument.
            const superProperties = Object.getPrototypeOf(this)._classProperties;
            if (superProperties !== undefined) {
                superProperties.forEach((v, k) => this._classProperties.set(k, v));
            }
        }
    }
    /**
     * Creates a property accessor on the element prototype if one does not exist.
     * The property setter calls the property's `hasChanged` property option
     * or uses a strict identity check to determine whether or not to request
     * an update.
     * @nocollapse
     */
    static createProperty(name, options = defaultPropertyDeclaration) {
        // Note, since this can be called by the `@property` decorator which
        // is called before `_finalize`, we ensure storage exists for property
        // metadata.
        this._ensureClassProperties();
        this._classProperties.set(name, options);
        // Do not generate an accessor if the prototype already has one, since
        // it would be lost otherwise and that would never be the user's intention;
        // Instead, we expect users to call `requestUpdate` themselves from
        // user-defined accessors. Note that if the super has an accessor we will
        // still overwrite it
        if (options.noAccessor || this.prototype.hasOwnProperty(name)) {
            return;
        }
        const key = typeof name === 'symbol' ? Symbol() : `__${name}`;
        Object.defineProperty(this.prototype, name, {
            get() { return this[key]; },
            set(value) {
                const oldValue = this[name];
                this[key] = value;
                this.requestUpdate(name, oldValue);
            },
            configurable: true,
            enumerable: true
        });
    }
    /**
     * Creates property accessors for registered properties and ensures
     * any superclasses are also finalized.
     * @nocollapse
     */
    static _finalize() {
        if (this.hasOwnProperty(JSCompiler_renameProperty('finalized', this)) &&
            this.finalized) {
            return;
        }
        // finalize any superclasses
        const superCtor = Object.getPrototypeOf(this);
        if (typeof superCtor._finalize === 'function') {
            superCtor._finalize();
        }
        this.finalized = true;
        this._ensureClassProperties();
        // initialize Map populated in observedAttributes
        this._attributeToPropertyMap = new Map();
        // make any properties
        // Note, only process "own" properties since this element will inherit
        // any properties defined on the superClass, and finalization ensures
        // the entire prototype chain is finalized.
        if (this.hasOwnProperty(JSCompiler_renameProperty('properties', this))) {
            const props = this.properties;
            // support symbols in properties (IE11 does not support this)
            const propKeys = [
                ...Object.getOwnPropertyNames(props),
                ...(typeof Object.getOwnPropertySymbols === 'function')
                    ? Object.getOwnPropertySymbols(props)
                    : []
            ];
            // This for/of is ok because propKeys is an array
            for (const p of propKeys) {
                // note, use of `any` is due to TypeSript lack of support for symbol in
                // index types
                this.createProperty(p, props[p]);
            }
        }
    }
    /**
     * Returns the property name for the given attribute `name`.
     * @nocollapse
     */
    static _attributeNameForProperty(name, options) {
        const attribute = options.attribute;
        return attribute === false
            ? undefined
            : (typeof attribute === 'string'
                ? attribute
                : (typeof name === 'string' ? name.toLowerCase()
                    : undefined));
    }
    /**
     * Returns true if a property should request an update.
     * Called when a property value is set and uses the `hasChanged`
     * option for the property if present or a strict identity check.
     * @nocollapse
     */
    static _valueHasChanged(value, old, hasChanged = notEqual) {
        return hasChanged(value, old);
    }
    /**
     * Returns the property value for the given attribute value.
     * Called via the `attributeChangedCallback` and uses the property's
     * `converter` or `converter.fromAttribute` property option.
     * @nocollapse
     */
    static _propertyValueFromAttribute(value, options) {
        const type = options.type;
        const converter = options.converter || defaultConverter;
        const fromAttribute = (typeof converter === 'function' ? converter : converter.fromAttribute);
        return fromAttribute ? fromAttribute(value, type) : value;
    }
    /**
     * Returns the attribute value for the given property value. If this
     * returns undefined, the property will *not* be reflected to an attribute.
     * If this returns null, the attribute will be removed, otherwise the
     * attribute will be set to the value.
     * This uses the property's `reflect` and `type.toAttribute` property options.
     * @nocollapse
     */
    static _propertyValueToAttribute(value, options) {
        if (options.reflect === undefined) {
            return;
        }
        const type = options.type;
        const converter = options.converter;
        const toAttribute = converter && converter.toAttribute ||
            defaultConverter.toAttribute;
        return toAttribute(value, type);
    }
    /**
     * Performs element initialization. By default captures any pre-set values for
     * registered properties.
     */
    initialize() { this._saveInstanceProperties(); }
    /**
     * Fixes any properties set on the instance before upgrade time.
     * Otherwise these would shadow the accessor and break these properties.
     * The properties are stored in a Map which is played back after the
     * constructor runs. Note, on very old versions of Safari (<=9) or Chrome
     * (<=41), properties created for native platform properties like (`id` or
     * `name`) may not have default values set in the element constructor. On
     * these browsers native properties appear on instances and therefore their
     * default value will overwrite any element default (e.g. if the element sets
     * this.id = 'id' in the constructor, the 'id' will become '' since this is
     * the native platform default).
     */
    _saveInstanceProperties() {
        // Use forEach so this works even if for/of loops are compiled to for loops
        // expecting arrays
        this.constructor
            ._classProperties.forEach((_v, p) => {
            if (this.hasOwnProperty(p)) {
                const value = this[p];
                delete this[p];
                if (!this._instanceProperties) {
                    this._instanceProperties = new Map();
                }
                this._instanceProperties.set(p, value);
            }
        });
    }
    /**
     * Applies previously saved instance properties.
     */
    _applyInstanceProperties() {
        // Use forEach so this works even if for/of loops are compiled to for loops
        // expecting arrays
        this._instanceProperties.forEach((v, p) => this[p] = v);
        this._instanceProperties = undefined;
    }
    connectedCallback() {
        this._updateState = this._updateState | STATE_HAS_CONNECTED;
        // Ensure connection triggers an update. Updates cannot complete before
        // connection and if one is pending connection the `_hasConnectionResolver`
        // will exist. If so, resolve it to complete the update, otherwise
        // requestUpdate.
        if (this._hasConnectedResolver) {
            this._hasConnectedResolver();
            this._hasConnectedResolver = undefined;
        }
        else {
            this.requestUpdate();
        }
    }
    /**
     * Allows for `super.disconnectedCallback()` in extensions while
     * reserving the possibility of making non-breaking feature additions
     * when disconnecting at some point in the future.
     */
    disconnectedCallback() { }
    /**
     * Synchronizes property values when attributes change.
     */
    attributeChangedCallback(name, old, value) {
        if (old !== value) {
            this._attributeToProperty(name, value);
        }
    }
    _propertyToAttribute(name, value, options = defaultPropertyDeclaration) {
        const ctor = this.constructor;
        const attr = ctor._attributeNameForProperty(name, options);
        if (attr !== undefined) {
            const attrValue = ctor._propertyValueToAttribute(value, options);
            // an undefined value does not change the attribute.
            if (attrValue === undefined) {
                return;
            }
            // Track if the property is being reflected to avoid
            // setting the property again via `attributeChangedCallback`. Note:
            // 1. this takes advantage of the fact that the callback is synchronous.
            // 2. will behave incorrectly if multiple attributes are in the reaction
            // stack at time of calling. However, since we process attributes
            // in `update` this should not be possible (or an extreme corner case
            // that we'd like to discover).
            // mark state reflecting
            this._updateState = this._updateState | STATE_IS_REFLECTING_TO_ATTRIBUTE;
            if (attrValue == null) {
                this.removeAttribute(attr);
            }
            else {
                this.setAttribute(attr, attrValue);
            }
            // mark state not reflecting
            this._updateState = this._updateState & ~STATE_IS_REFLECTING_TO_ATTRIBUTE;
        }
    }
    _attributeToProperty(name, value) {
        // Use tracking info to avoid deserializing attribute value if it was
        // just set from a property setter.
        if (this._updateState & STATE_IS_REFLECTING_TO_ATTRIBUTE) {
            return;
        }
        const ctor = this.constructor;
        const propName = ctor._attributeToPropertyMap.get(name);
        if (propName !== undefined) {
            const options = ctor._classProperties.get(propName) || defaultPropertyDeclaration;
            // mark state reflecting
            this._updateState = this._updateState | STATE_IS_REFLECTING_TO_PROPERTY;
            this[propName] =
                ctor._propertyValueFromAttribute(value, options);
            // mark state not reflecting
            this._updateState = this._updateState & ~STATE_IS_REFLECTING_TO_PROPERTY;
        }
    }
    /**
     * Requests an update which is processed asynchronously. This should
     * be called when an element should update based on some state not triggered
     * by setting a property. In this case, pass no arguments. It should also be
     * called when manually implementing a property setter. In this case, pass the
     * property `name` and `oldValue` to ensure that any configured property
     * options are honored. Returns the `updateComplete` Promise which is resolved
     * when the update completes.
     *
     * @param name {PropertyKey} (optional) name of requesting property
     * @param oldValue {any} (optional) old value of requesting property
     * @returns {Promise} A Promise that is resolved when the update completes.
     */
    requestUpdate(name, oldValue) {
        let shouldRequestUpdate = true;
        // if we have a property key, perform property update steps.
        if (name !== undefined && !this._changedProperties.has(name)) {
            const ctor = this.constructor;
            const options = ctor._classProperties.get(name) || defaultPropertyDeclaration;
            if (ctor._valueHasChanged(this[name], oldValue, options.hasChanged)) {
                // track old value when changing.
                this._changedProperties.set(name, oldValue);
                // add to reflecting properties set
                if (options.reflect === true &&
                    !(this._updateState & STATE_IS_REFLECTING_TO_PROPERTY)) {
                    if (this._reflectingProperties === undefined) {
                        this._reflectingProperties = new Map();
                    }
                    this._reflectingProperties.set(name, options);
                }
                // abort the request if the property should not be considered changed.
            }
            else {
                shouldRequestUpdate = false;
            }
        }
        if (!this._hasRequestedUpdate && shouldRequestUpdate) {
            this._enqueueUpdate();
        }
        return this.updateComplete;
    }
    /**
     * Sets up the element to asynchronously update.
     */
    async _enqueueUpdate() {
        // Mark state updating...
        this._updateState = this._updateState | STATE_UPDATE_REQUESTED;
        let resolve;
        const previousUpdatePromise = this._updatePromise;
        this._updatePromise = new Promise((res) => resolve = res);
        // Ensure any previous update has resolved before updating.
        // This `await` also ensures that property changes are batched.
        await previousUpdatePromise;
        // Make sure the element has connected before updating.
        if (!this._hasConnected) {
            await new Promise((res) => this._hasConnectedResolver = res);
        }
        // Allow `performUpdate` to be asynchronous to enable scheduling of updates.
        const result = this.performUpdate();
        // Note, this is to avoid delaying an additional microtask unless we need
        // to.
        if (result != null &&
            typeof result.then === 'function') {
            await result;
        }
        resolve(!this._hasRequestedUpdate);
    }
    get _hasConnected() {
        return (this._updateState & STATE_HAS_CONNECTED);
    }
    get _hasRequestedUpdate() {
        return (this._updateState & STATE_UPDATE_REQUESTED);
    }
    get hasUpdated() { return (this._updateState & STATE_HAS_UPDATED); }
    /**
     * Performs an element update.
     *
     * You can override this method to change the timing of updates. For instance,
     * to schedule updates to occur just before the next frame:
     *
     * ```
     * protected async performUpdate(): Promise<unknown> {
     *   await new Promise((resolve) => requestAnimationFrame(() => resolve()));
     *   super.performUpdate();
     * }
     * ```
     */
    performUpdate() {
        // Mixin instance properties once, if they exist.
        if (this._instanceProperties) {
            this._applyInstanceProperties();
        }
        if (this.shouldUpdate(this._changedProperties)) {
            const changedProperties = this._changedProperties;
            this.update(changedProperties);
            this._markUpdated();
            if (!(this._updateState & STATE_HAS_UPDATED)) {
                this._updateState = this._updateState | STATE_HAS_UPDATED;
                this.firstUpdated(changedProperties);
            }
            this.updated(changedProperties);
        }
        else {
            this._markUpdated();
        }
    }
    _markUpdated() {
        this._changedProperties = new Map();
        this._updateState = this._updateState & ~STATE_UPDATE_REQUESTED;
    }
    /**
     * Returns a Promise that resolves when the element has completed updating.
     * The Promise value is a boolean that is `true` if the element completed the
     * update without triggering another update. The Promise result is `false` if
     * a property was set inside `updated()`. This getter can be implemented to
     * await additional state. For example, it is sometimes useful to await a
     * rendered element before fulfilling this Promise. To do this, first await
     * `super.updateComplete` then any subsequent state.
     *
     * @returns {Promise} The Promise returns a boolean that indicates if the
     * update resolved without triggering another update.
     */
    get updateComplete() { return this._updatePromise; }
    /**
     * Controls whether or not `update` should be called when the element requests
     * an update. By default, this method always returns `true`, but this can be
     * customized to control when to update.
     *
     * * @param _changedProperties Map of changed properties with old values
     */
    shouldUpdate(_changedProperties) {
        return true;
    }
    /**
     * Updates the element. This method reflects property values to attributes.
     * It can be overridden to render and keep updated element DOM.
     * Setting properties inside this method will *not* trigger
     * another update.
     *
     * * @param _changedProperties Map of changed properties with old values
     */
    update(_changedProperties) {
        if (this._reflectingProperties !== undefined &&
            this._reflectingProperties.size > 0) {
            // Use forEach so this works even if for/of loops are compiled to for
            // loops expecting arrays
            this._reflectingProperties.forEach((v, k) => this._propertyToAttribute(k, this[k], v));
            this._reflectingProperties = undefined;
        }
    }
    /**
     * Invoked whenever the element is updated. Implement to perform
     * post-updating tasks via DOM APIs, for example, focusing an element.
     *
     * Setting properties inside this method will trigger the element to update
     * again after this update cycle completes.
     *
     * * @param _changedProperties Map of changed properties with old values
     */
    updated(_changedProperties) { }
    /**
     * Invoked when the element is first updated. Implement to perform one time
     * work on the element after update.
     *
     * Setting properties inside this method will trigger the element to update
     * again after this update cycle completes.
     *
     * * @param _changedProperties Map of changed properties with old values
     */
    firstUpdated(_changedProperties) { }
}
/**
 * Marks class as having finished creating properties.
 */
UpdatingElement.finalized = true;


/***/ }),

/***/ "../../node_modules/lit-element/lit-element.js":
/*!****************************************************!*\
  !*** /app/node_modules/lit-element/lit-element.js ***!
  \****************************************************/
/*! exports provided: html, svg, TemplateResult, SVGTemplateResult, LitElement, JSCompiler_renameProperty, defaultConverter, notEqual, UpdatingElement, customElement, property, query, queryAll, eventOptions, supportsAdoptingStyleSheets, CSSResult, css */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "LitElement", function() { return LitElement; });
/* harmony import */ var lit_html__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! lit-html */ "../../node_modules/lit-html/lit-html.js");
/* harmony import */ var lit_html_lib_shady_render__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! lit-html/lib/shady-render */ "../../node_modules/lit-html/lib/shady-render.js");
/* harmony import */ var _lib_updating_element_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./lib/updating-element.js */ "../../node_modules/lit-element/lib/updating-element.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "JSCompiler_renameProperty", function() { return _lib_updating_element_js__WEBPACK_IMPORTED_MODULE_2__["JSCompiler_renameProperty"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "defaultConverter", function() { return _lib_updating_element_js__WEBPACK_IMPORTED_MODULE_2__["defaultConverter"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "notEqual", function() { return _lib_updating_element_js__WEBPACK_IMPORTED_MODULE_2__["notEqual"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "UpdatingElement", function() { return _lib_updating_element_js__WEBPACK_IMPORTED_MODULE_2__["UpdatingElement"]; });

/* harmony import */ var _lib_decorators_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./lib/decorators.js */ "../../node_modules/lit-element/lib/decorators.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "customElement", function() { return _lib_decorators_js__WEBPACK_IMPORTED_MODULE_3__["customElement"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "property", function() { return _lib_decorators_js__WEBPACK_IMPORTED_MODULE_3__["property"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "query", function() { return _lib_decorators_js__WEBPACK_IMPORTED_MODULE_3__["query"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "queryAll", function() { return _lib_decorators_js__WEBPACK_IMPORTED_MODULE_3__["queryAll"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "eventOptions", function() { return _lib_decorators_js__WEBPACK_IMPORTED_MODULE_3__["eventOptions"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "html", function() { return lit_html__WEBPACK_IMPORTED_MODULE_0__["html"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "svg", function() { return lit_html__WEBPACK_IMPORTED_MODULE_0__["svg"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "TemplateResult", function() { return lit_html__WEBPACK_IMPORTED_MODULE_0__["TemplateResult"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "SVGTemplateResult", function() { return lit_html__WEBPACK_IMPORTED_MODULE_0__["SVGTemplateResult"]; });

/* harmony import */ var _lib_css_tag_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./lib/css-tag.js */ "../../node_modules/lit-element/lib/css-tag.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "supportsAdoptingStyleSheets", function() { return _lib_css_tag_js__WEBPACK_IMPORTED_MODULE_4__["supportsAdoptingStyleSheets"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "CSSResult", function() { return _lib_css_tag_js__WEBPACK_IMPORTED_MODULE_4__["CSSResult"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "css", function() { return _lib_css_tag_js__WEBPACK_IMPORTED_MODULE_4__["css"]; });

/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */








/**
 * Minimal implementation of Array.prototype.flat
 * @param arr the array to flatten
 * @param result the accumlated result
 */
function arrayFlat(styles, result = []) {
    for (let i = 0, length = styles.length; i < length; i++) {
        const value = styles[i];
        if (Array.isArray(value)) {
            arrayFlat(value, result);
        }
        else {
            result.push(value);
        }
    }
    return result;
}
/** Deeply flattens styles array. Uses native flat if available. */
const flattenStyles = (styles) => styles.flat ? styles.flat(Infinity) : arrayFlat(styles);
class LitElement extends _lib_updating_element_js__WEBPACK_IMPORTED_MODULE_2__["UpdatingElement"] {
    static get _uniqueStyles() {
        if (!this.hasOwnProperty(Object(_lib_updating_element_js__WEBPACK_IMPORTED_MODULE_2__["JSCompiler_renameProperty"])('_styles', this))) {
            // Inherit styles from superclass if none have been set.
            if (!this.hasOwnProperty(Object(_lib_updating_element_js__WEBPACK_IMPORTED_MODULE_2__["JSCompiler_renameProperty"])('styles', this))) {
                this._styles = this._styles !== undefined ? this._styles : [];
            }
            else {
                // Take care not to call `this.styles` multiple times since this generates
                // new CSSResults each time.
                // TODO(sorvell): Since we do not cache CSSResults by input, any
                // shared styles will generate new stylesheet objects, which is wasteful.
                // This should be addressed when a browser ships constructable
                // stylesheets.
                const userStyles = this.styles;
                if (Array.isArray(userStyles)) {
                    const styles = flattenStyles(userStyles);
                    // As a performance optimization to avoid duplicated styling that can
                    // occur especially when composing via subclassing, de-duplicate styles
                    // preserving the last item in the list. The last item is kept to
                    // try to preserve cascade order with the assumption that it's most
                    // important that last added styles override previous styles.
                    const styleSet = styles.reduceRight((set, s) => {
                        set.add(s);
                        // on IE set.add does not return the set.
                        return set;
                    }, new Set());
                    // Array.from does not work on Set in IE
                    this._styles = [];
                    styleSet.forEach((v) => this._styles.unshift(v));
                }
                else {
                    this._styles = userStyles ? [userStyles] : [];
                }
            }
        }
        return this._styles;
    }
    /**
     * Performs element initialization. By default this calls `createRenderRoot`
     * to create the element `renderRoot` node and captures any pre-set values for
     * registered properties.
     */
    initialize() {
        super.initialize();
        this.renderRoot = this.createRenderRoot();
        // Note, if renderRoot is not a shadowRoot, styles would/could apply to the
        // element's getRootNode(). While this could be done, we're choosing not to
        // support this now since it would require different logic around de-duping.
        if (window.ShadowRoot && this.renderRoot instanceof window.ShadowRoot) {
            this.adoptStyles();
        }
    }
    /**
     * Returns the node into which the element should render and by default
     * creates and returns an open shadowRoot. Implement to customize where the
     * element's DOM is rendered. For example, to render into the element's
     * childNodes, return `this`.
     * @returns {Element|DocumentFragment} Returns a node into which to render.
     */
    createRenderRoot() {
        return this.attachShadow({ mode: 'open' });
    }
    /**
     * Applies styling to the element shadowRoot using the `static get styles`
     * property. Styling will apply using `shadowRoot.adoptedStyleSheets` where
     * available and will fallback otherwise. When Shadow DOM is polyfilled,
     * ShadyCSS scopes styles and adds them to the document. When Shadow DOM
     * is available but `adoptedStyleSheets` is not, styles are appended to the
     * end of the `shadowRoot` to [mimic spec
     * behavior](https://wicg.github.io/construct-stylesheets/#using-constructed-stylesheets).
     */
    adoptStyles() {
        const styles = this.constructor._uniqueStyles;
        if (styles.length === 0) {
            return;
        }
        // There are three separate cases here based on Shadow DOM support.
        // (1) shadowRoot polyfilled: use ShadyCSS
        // (2) shadowRoot.adoptedStyleSheets available: use it.
        // (3) shadowRoot.adoptedStyleSheets polyfilled: append styles after
        // rendering
        if (window.ShadyCSS !== undefined && !window.ShadyCSS.nativeShadow) {
            window.ShadyCSS.ScopingShim.prepareAdoptedCssText(styles.map((s) => s.cssText), this.localName);
        }
        else if (_lib_css_tag_js__WEBPACK_IMPORTED_MODULE_4__["supportsAdoptingStyleSheets"]) {
            this.renderRoot.adoptedStyleSheets =
                styles.map((s) => s.styleSheet);
        }
        else {
            // This must be done after rendering so the actual style insertion is done
            // in `update`.
            this._needsShimAdoptedStyleSheets = true;
        }
    }
    connectedCallback() {
        super.connectedCallback();
        // Note, first update/render handles styleElement so we only call this if
        // connected after first update.
        if (this.hasUpdated && window.ShadyCSS !== undefined) {
            window.ShadyCSS.styleElement(this);
        }
    }
    /**
     * Updates the element. This method reflects property values to attributes
     * and calls `render` to render DOM via lit-html. Setting properties inside
     * this method will *not* trigger another update.
     * * @param _changedProperties Map of changed properties with old values
     */
    update(changedProperties) {
        super.update(changedProperties);
        const templateResult = this.render();
        if (templateResult instanceof lit_html__WEBPACK_IMPORTED_MODULE_0__["TemplateResult"]) {
            this.constructor
                .render(templateResult, this.renderRoot, { scopeName: this.localName, eventContext: this });
        }
        // When native Shadow DOM is used but adoptedStyles are not supported,
        // insert styling after rendering to ensure adoptedStyles have highest
        // priority.
        if (this._needsShimAdoptedStyleSheets) {
            this._needsShimAdoptedStyleSheets = false;
            this.constructor._uniqueStyles.forEach((s) => {
                const style = document.createElement('style');
                style.textContent = s.cssText;
                this.renderRoot.appendChild(style);
            });
        }
    }
    /**
     * Invoked on each update to perform rendering tasks. This method must return
     * a lit-html TemplateResult. Setting properties inside this method will *not*
     * trigger the element to update.
     */
    render() { }
}
/**
 * Ensure this class is marked as `finalized` as an optimization ensuring
 * it will not needlessly try to `finalize`.
 */
LitElement.finalized = true;
/**
 * Render method used to render the lit-html TemplateResult to the element's
 * DOM.
 * @param {TemplateResult} Template to render.
 * @param {Element|DocumentFragment} Node into which to render.
 * @param {String} Element name.
 * @nocollapse
 */
LitElement.render = lit_html_lib_shady_render__WEBPACK_IMPORTED_MODULE_1__["render"];


/***/ }),

/***/ "../../node_modules/lit-html/directives/repeat.js":
/*!*******************************************************!*\
  !*** /app/node_modules/lit-html/directives/repeat.js ***!
  \*******************************************************/
/*! exports provided: repeat */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "repeat", function() { return repeat; });
/* harmony import */ var _lit_html_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../lit-html.js */ "../../node_modules/lit-html/lit-html.js");
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */

// Helper functions for manipulating parts
// TODO(kschaaf): Refactor into Part API?
const createAndInsertPart = (containerPart, beforePart) => {
    const container = containerPart.startNode.parentNode;
    const beforeNode = beforePart === undefined ? containerPart.endNode :
        beforePart.startNode;
    const startNode = container.insertBefore(Object(_lit_html_js__WEBPACK_IMPORTED_MODULE_0__["createMarker"])(), beforeNode);
    container.insertBefore(Object(_lit_html_js__WEBPACK_IMPORTED_MODULE_0__["createMarker"])(), beforeNode);
    const newPart = new _lit_html_js__WEBPACK_IMPORTED_MODULE_0__["NodePart"](containerPart.options);
    newPart.insertAfterNode(startNode);
    return newPart;
};
const updatePart = (part, value) => {
    part.setValue(value);
    part.commit();
    return part;
};
const insertPartBefore = (containerPart, part, ref) => {
    const container = containerPart.startNode.parentNode;
    const beforeNode = ref ? ref.startNode : containerPart.endNode;
    const endNode = part.endNode.nextSibling;
    if (endNode !== beforeNode) {
        Object(_lit_html_js__WEBPACK_IMPORTED_MODULE_0__["reparentNodes"])(container, part.startNode, endNode, beforeNode);
    }
};
const removePart = (part) => {
    Object(_lit_html_js__WEBPACK_IMPORTED_MODULE_0__["removeNodes"])(part.startNode.parentNode, part.startNode, part.endNode.nextSibling);
};
// Helper for generating a map of array item to its index over a subset
// of an array (used to lazily generate `newKeyToIndexMap` and
// `oldKeyToIndexMap`)
const generateMap = (list, start, end) => {
    const map = new Map();
    for (let i = start; i <= end; i++) {
        map.set(list[i], i);
    }
    return map;
};
// Stores previous ordered list of parts and map of key to index
const partListCache = new WeakMap();
const keyListCache = new WeakMap();
/**
 * A directive that repeats a series of values (usually `TemplateResults`)
 * generated from an iterable, and updates those items efficiently when the
 * iterable changes based on user-provided `keys` associated with each item.
 *
 * Note that if a `keyFn` is provided, strict key-to-DOM mapping is maintained,
 * meaning previous DOM for a given key is moved into the new position if
 * needed, and DOM will never be reused with values for different keys (new DOM
 * will always be created for new keys). This is generally the most efficient
 * way to use `repeat` since it performs minimum unnecessary work for insertions
 * amd removals.
 *
 * IMPORTANT: If providing a `keyFn`, keys *must* be unique for all items in a
 * given call to `repeat`. The behavior when two or more items have the same key
 * is undefined.
 *
 * If no `keyFn` is provided, this directive will perform similar to mapping
 * items to values, and DOM will be reused against potentially different items.
 */
const repeat = Object(_lit_html_js__WEBPACK_IMPORTED_MODULE_0__["directive"])((items, keyFnOrTemplate, template) => {
    let keyFn;
    if (template === undefined) {
        template = keyFnOrTemplate;
    }
    else if (keyFnOrTemplate !== undefined) {
        keyFn = keyFnOrTemplate;
    }
    return (containerPart) => {
        if (!(containerPart instanceof _lit_html_js__WEBPACK_IMPORTED_MODULE_0__["NodePart"])) {
            throw new Error('repeat can only be used in text bindings');
        }
        // Old part & key lists are retrieved from the last update (associated
        // with the part for this instance of the directive)
        const oldParts = partListCache.get(containerPart) || [];
        const oldKeys = keyListCache.get(containerPart) || [];
        // New part list will be built up as we go (either reused from old parts
        // or created for new keys in this update). This is saved in the above
        // cache at the end of the update.
        const newParts = [];
        // New value list is eagerly generated from items along with a parallel
        // array indicating its key.
        const newValues = [];
        const newKeys = [];
        let index = 0;
        for (const item of items) {
            newKeys[index] = keyFn ? keyFn(item, index) : index;
            newValues[index] = template(item, index);
            index++;
        }
        // Maps from key to index for current and previous update; these are
        // generated lazily only when needed as a performance optimization,
        // since they are only required for multiple non-contiguous changes in
        // the list, which are less common.
        let newKeyToIndexMap;
        let oldKeyToIndexMap;
        // Head and tail pointers to old parts and new values
        let oldHead = 0;
        let oldTail = oldParts.length - 1;
        let newHead = 0;
        let newTail = newValues.length - 1;
        // Overview of O(n) reconciliation algorithm (general approach based on
        // ideas found in ivi, vue, snabbdom, etc.):
        //
        // * We start with the list of old parts and new values (and arrays of
        //   their respective keys), head/tail pointers into each, and we build
        //   up the new list of parts by updating (and when needed, moving) old
        //   parts or creating new ones. The initial scenario might look like
        //   this (for brevity of the diagrams, the numbers in the array reflect
        //   keys associated with the old parts or new values, although keys and
        //   parts/values are actually stored in parallel arrays indexed using
        //   the same head/tail pointers):
        //
        //      oldHead v                 v oldTail
        //   oldKeys:  [0, 1, 2, 3, 4, 5, 6]
        //   newParts: [ ,  ,  ,  ,  ,  ,  ]
        //   newKeys:  [0, 2, 1, 4, 3, 7, 6] <- reflects the user's new item
        //   order
        //      newHead ^                 ^ newTail
        //
        // * Iterate old & new lists from both sides, updating, swapping, or
        //   removing parts at the head/tail locations until neither head nor
        //   tail can move.
        //
        // * Example below: keys at head pointers match, so update old part 0
        // in-
        //   place (no need to move it) and record part 0 in the `newParts`
        //   list. The last thing we do is advance the `oldHead` and `newHead`
        //   pointers (will be reflected in the next diagram).
        //
        //      oldHead v                 v oldTail
        //   oldKeys:  [0, 1, 2, 3, 4, 5, 6]
        //   newParts: [0,  ,  ,  ,  ,  ,  ] <- heads matched: update 0 and
        //   newKeys:  [0, 2, 1, 4, 3, 7, 6]    advance both oldHead & newHead
        //      newHead ^                 ^ newTail
        //
        // * Example below: head pointers don't match, but tail pointers do, so
        //   update part 6 in place (no need to move it), and record part 6 in
        //   the `newParts` list. Last, advance the `oldTail` and `oldHead`
        //   pointers.
        //
        //         oldHead v              v oldTail
        //   oldKeys:  [0, 1, 2, 3, 4, 5, 6]
        //   newParts: [0,  ,  ,  ,  ,  , 6] <- tails matched: update 6 and
        //   newKeys:  [0, 2, 1, 4, 3, 7, 6]    advance both oldTail & newTail
        //         newHead ^              ^ newTail
        //
        // * If neither head nor tail match; next check if one of the old
        // head/tail
        //   items was removed. We first need to generate the reverse map of new
        //   keys to index (`newKeyToIndexMap`), which is done once lazily as a
        //   performance optimization, since we only hit this case if multiple
        //   non-contiguous changes were made. Note that for contiguous removal
        //   anywhere in the list, the head and tails would advance from either
        //   end and pass each other before we get to this case and removals
        //   would be handled in the final while loop without needing to
        //   generate the map.
        //
        // * Example below: The key at `oldTail` was removed (no longer in the
        //   `newKeyToIndexMap`), so remove that part from the DOM and advance
        //   just the `oldTail` pointer.
        //
        //         oldHead v           v oldTail
        //   oldKeys:  [0, 1, 2, 3, 4, 5, 6]
        //   newParts: [0,  ,  ,  ,  ,  , 6] <- 5 not in new map; remove 5 and
        //   newKeys:  [0, 2, 1, 4, 3, 7, 6]    advance oldTail
        //         newHead ^           ^ newTail
        //
        // * Once head and tail cannot move, any mismatches are due to either
        // new or
        //   moved items; if a new key is in the previous "old key to old index"
        //   map, move the old part to the new location, otherwise create and
        //   insert a new part. Note that when moving an old part we null its
        //   position in the oldParts array if it lies between the head and tail
        //   so we know to skip it when the pointers get there.
        //
        // * Example below: neither head nor tail match, and neither were
        // removed;
        //   so find the `newHead` key in the `oldKeyToIndexMap`, and move that
        //   old part's DOM into the next head position (before
        //   `oldParts[oldHead]`). Last, null the part in the `oldPart` array
        //   since it was somewhere in the remaining oldParts still to be
        //   scanned (between the head and tail pointers) so that we know to
        //   skip that old part on future iterations.
        //
        //         oldHead v        v oldTail
        //   oldKeys:  [0, 1, -, 3, 4, 5, 6]
        //   newParts: [0, 2,  ,  ,  ,  , 6] <- stuck; update & move 2 into
        //   place newKeys:  [0, 2, 1, 4, 3, 7, 6]    and advance newHead
        //         newHead ^           ^ newTail
        //
        // * Note that for moves/insertions like the one above, a part inserted
        // at
        //   the head pointer is inserted before the current
        //   `oldParts[oldHead]`, and a part inserted at the tail pointer is
        //   inserted before `newParts[newTail+1]`. The seeming asymmetry lies
        //   in the fact that new parts are moved into place outside in, so to
        //   the right of the head pointer are old parts, and to the right of
        //   the tail pointer are new parts.
        //
        // * We always restart back from the top of the algorithm, allowing
        // matching
        //   and simple updates in place to continue...
        //
        // * Example below: the head pointers once again match, so simply update
        //   part 1 and record it in the `newParts` array.  Last, advance both
        //   head pointers.
        //
        //         oldHead v        v oldTail
        //   oldKeys:  [0, 1, -, 3, 4, 5, 6]
        //   newParts: [0, 2, 1,  ,  ,  , 6] <- heads matched; update 1 and
        //   newKeys:  [0, 2, 1, 4, 3, 7, 6]    advance both oldHead & newHead
        //            newHead ^        ^ newTail
        //
        // * As mentioned above, items that were moved as a result of being
        // stuck
        //   (the final else clause in the code below) are marked with null, so
        //   we always advance old pointers over these so we're comparing the
        //   next actual old value on either end.
        //
        // * Example below: `oldHead` is null (already placed in newParts), so
        //   advance `oldHead`.
        //
        //            oldHead v     v oldTail
        //   oldKeys:  [0, 1, -, 3, 4, 5, 6] // old head already used; advance
        //   newParts: [0, 2, 1,  ,  ,  , 6] // oldHead
        //   newKeys:  [0, 2, 1, 4, 3, 7, 6]
        //               newHead ^     ^ newTail
        //
        // * Note it's not critical to mark old parts as null when they are
        // moved
        //   from head to tail or tail to head, since they will be outside the
        //   pointer range and never visited again.
        //
        // * Example below: Here the old tail key matches the new head key, so
        //   the part at the `oldTail` position and move its DOM to the new
        //   head position (before `oldParts[oldHead]`). Last, advance `oldTail`
        //   and `newHead` pointers.
        //
        //               oldHead v  v oldTail
        //   oldKeys:  [0, 1, -, 3, 4, 5, 6]
        //   newParts: [0, 2, 1, 4,  ,  , 6] <- old tail matches new head:
        //   update newKeys:  [0, 2, 1, 4, 3, 7, 6]   & move 4, advance oldTail
        //   & newHead
        //               newHead ^     ^ newTail
        //
        // * Example below: Old and new head keys match, so update the old head
        //   part in place, and advance the `oldHead` and `newHead` pointers.
        //
        //               oldHead v oldTail
        //   oldKeys:  [0, 1, -, 3, 4, 5, 6]
        //   newParts: [0, 2, 1, 4, 3,   ,6] <- heads match: update 3 and
        //   advance newKeys:  [0, 2, 1, 4, 3, 7, 6]    oldHead & newHead
        //                  newHead ^  ^ newTail
        //
        // * Once the new or old pointers move past each other then all we have
        //   left is additions (if old list exhausted) or removals (if new list
        //   exhausted). Those are handled in the final while loops at the end.
        //
        // * Example below: `oldHead` exceeded `oldTail`, so we're done with the
        //   main loop.  Create the remaining part and insert it at the new head
        //   position, and the update is complete.
        //
        //                   (oldHead > oldTail)
        //   oldKeys:  [0, 1, -, 3, 4, 5, 6]
        //   newParts: [0, 2, 1, 4, 3, 7 ,6] <- create and insert 7
        //   newKeys:  [0, 2, 1, 4, 3, 7, 6]
        //                     newHead ^ newTail
        //
        // * Note that the order of the if/else clauses is not important to the
        //   algorithm, as long as the null checks come first (to ensure we're
        //   always working on valid old parts) and that the final else clause
        //   comes last (since that's where the expensive moves occur). The
        //   order of remaining clauses is is just a simple guess at which cases
        //   will be most common.
        //
        // * TODO(kschaaf) Note, we could calculate the longest increasing
        //   subsequence (LIS) of old items in new position, and only move those
        //   not in the LIS set. However that costs O(nlogn) time and adds a bit
        //   more code, and only helps make rare types of mutations require
        //   fewer moves. The above handles removes, adds, reversal, swaps, and
        //   single moves of contiguous items in linear time, in the minimum
        //   number of moves. As the number of multiple moves where LIS might
        //   help approaches a random shuffle, the LIS optimization becomes less
        //   helpful, so it seems not worth the code at this point. Could
        //   reconsider if a compelling case arises.
        while (oldHead <= oldTail && newHead <= newTail) {
            if (oldParts[oldHead] === null) {
                // `null` means old part at head has already been used below; skip
                oldHead++;
            }
            else if (oldParts[oldTail] === null) {
                // `null` means old part at tail has already been used below; skip
                oldTail--;
            }
            else if (oldKeys[oldHead] === newKeys[newHead]) {
                // Old head matches new head; update in place
                newParts[newHead] =
                    updatePart(oldParts[oldHead], newValues[newHead]);
                oldHead++;
                newHead++;
            }
            else if (oldKeys[oldTail] === newKeys[newTail]) {
                // Old tail matches new tail; update in place
                newParts[newTail] =
                    updatePart(oldParts[oldTail], newValues[newTail]);
                oldTail--;
                newTail--;
            }
            else if (oldKeys[oldHead] === newKeys[newTail]) {
                // Old head matches new tail; update and move to new tail
                newParts[newTail] =
                    updatePart(oldParts[oldHead], newValues[newTail]);
                insertPartBefore(containerPart, oldParts[oldHead], newParts[newTail + 1]);
                oldHead++;
                newTail--;
            }
            else if (oldKeys[oldTail] === newKeys[newHead]) {
                // Old tail matches new head; update and move to new head
                newParts[newHead] =
                    updatePart(oldParts[oldTail], newValues[newHead]);
                insertPartBefore(containerPart, oldParts[oldTail], oldParts[oldHead]);
                oldTail--;
                newHead++;
            }
            else {
                if (newKeyToIndexMap === undefined) {
                    // Lazily generate key-to-index maps, used for removals & moves
                    // below
                    newKeyToIndexMap = generateMap(newKeys, newHead, newTail);
                    oldKeyToIndexMap = generateMap(oldKeys, oldHead, oldTail);
                }
                if (!newKeyToIndexMap.has(oldKeys[oldHead])) {
                    // Old head is no longer in new list; remove
                    removePart(oldParts[oldHead]);
                    oldHead++;
                }
                else if (!newKeyToIndexMap.has(oldKeys[oldTail])) {
                    // Old tail is no longer in new list; remove
                    removePart(oldParts[oldTail]);
                    oldTail--;
                }
                else {
                    // Any mismatches at this point are due to additions or moves; see
                    // if we have an old part we can reuse and move into place
                    const oldIndex = oldKeyToIndexMap.get(newKeys[newHead]);
                    const oldPart = oldIndex !== undefined ? oldParts[oldIndex] : null;
                    if (oldPart === null) {
                        // No old part for this value; create a new one and insert it
                        const newPart = createAndInsertPart(containerPart, oldParts[oldHead]);
                        updatePart(newPart, newValues[newHead]);
                        newParts[newHead] = newPart;
                    }
                    else {
                        // Reuse old part
                        newParts[newHead] = updatePart(oldPart, newValues[newHead]);
                        insertPartBefore(containerPart, oldPart, oldParts[oldHead]);
                        // This marks the old part as having been used, so that it will
                        // be skipped in the first two checks above
                        oldParts[oldIndex] = null;
                    }
                    newHead++;
                }
            }
        }
        // Add parts for any remaining new values
        while (newHead <= newTail) {
            // For all remaining additions, we insert before last new tail,
            // since old pointers are no longer valid
            const newPart = createAndInsertPart(containerPart, newParts[newTail + 1]);
            updatePart(newPart, newValues[newHead]);
            newParts[newHead++] = newPart;
        }
        // Remove any remaining unused old parts
        while (oldHead <= oldTail) {
            const oldPart = oldParts[oldHead++];
            if (oldPart !== null) {
                removePart(oldPart);
            }
        }
        // Save order of new parts for next round
        partListCache.set(containerPart, newParts);
        keyListCache.set(containerPart, newKeys);
    };
});


/***/ }),

/***/ "../../node_modules/lit-html/lib/default-template-processor.js":
/*!********************************************************************!*\
  !*** /app/node_modules/lit-html/lib/default-template-processor.js ***!
  \********************************************************************/
/*! exports provided: DefaultTemplateProcessor, defaultTemplateProcessor */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "DefaultTemplateProcessor", function() { return DefaultTemplateProcessor; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "defaultTemplateProcessor", function() { return defaultTemplateProcessor; });
/* harmony import */ var _parts_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./parts.js */ "../../node_modules/lit-html/lib/parts.js");
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */

/**
 * Creates Parts when a template is instantiated.
 */
class DefaultTemplateProcessor {
    /**
     * Create parts for an attribute-position binding, given the event, attribute
     * name, and string literals.
     *
     * @param element The element containing the binding
     * @param name  The attribute name
     * @param strings The string literals. There are always at least two strings,
     *   event for fully-controlled bindings with a single expression.
     */
    handleAttributeExpressions(element, name, strings, options) {
        const prefix = name[0];
        if (prefix === '.') {
            const comitter = new _parts_js__WEBPACK_IMPORTED_MODULE_0__["PropertyCommitter"](element, name.slice(1), strings);
            return comitter.parts;
        }
        if (prefix === '@') {
            return [new _parts_js__WEBPACK_IMPORTED_MODULE_0__["EventPart"](element, name.slice(1), options.eventContext)];
        }
        if (prefix === '?') {
            return [new _parts_js__WEBPACK_IMPORTED_MODULE_0__["BooleanAttributePart"](element, name.slice(1), strings)];
        }
        const comitter = new _parts_js__WEBPACK_IMPORTED_MODULE_0__["AttributeCommitter"](element, name, strings);
        return comitter.parts;
    }
    /**
     * Create parts for a text-position binding.
     * @param templateFactory
     */
    handleTextExpression(options) {
        return new _parts_js__WEBPACK_IMPORTED_MODULE_0__["NodePart"](options);
    }
}
const defaultTemplateProcessor = new DefaultTemplateProcessor();


/***/ }),

/***/ "../../node_modules/lit-html/lib/directive.js":
/*!***************************************************!*\
  !*** /app/node_modules/lit-html/lib/directive.js ***!
  \***************************************************/
/*! exports provided: directive, isDirective */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "directive", function() { return directive; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isDirective", function() { return isDirective; });
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
const directives = new WeakMap();
/**
 * Brands a function as a directive so that lit-html will call the function
 * during template rendering, rather than passing as a value.
 *
 * @param f The directive factory function. Must be a function that returns a
 * function of the signature `(part: Part) => void`. The returned function will
 * be called with the part object
 *
 * @example
 *
 * ```
 * import {directive, html} from 'lit-html';
 *
 * const immutable = directive((v) => (part) => {
 *   if (part.value !== v) {
 *     part.setValue(v)
 *   }
 * });
 * ```
 */
const directive = (f) => ((...args) => {
    const d = f(...args);
    directives.set(d, true);
    return d;
});
const isDirective = (o) => typeof o === 'function' && directives.has(o);


/***/ }),

/***/ "../../node_modules/lit-html/lib/dom.js":
/*!*********************************************!*\
  !*** /app/node_modules/lit-html/lib/dom.js ***!
  \*********************************************/
/*! exports provided: isCEPolyfill, reparentNodes, removeNodes */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isCEPolyfill", function() { return isCEPolyfill; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "reparentNodes", function() { return reparentNodes; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "removeNodes", function() { return removeNodes; });
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
/**
 * @module lit-html
 */
/**
 * True if the custom elements polyfill is in use.
 */
const isCEPolyfill = window.customElements !== undefined &&
    window.customElements.polyfillWrapFlushCallback !== undefined;
/**
 * Reparents nodes, starting from `startNode` (inclusive) to `endNode`
 * (exclusive), into another container (could be the same container), before
 * `beforeNode`. If `beforeNode` is null, it appends the nodes to the
 * container.
 */
const reparentNodes = (container, start, end = null, before = null) => {
    let node = start;
    while (node !== end) {
        const n = node.nextSibling;
        container.insertBefore(node, before);
        node = n;
    }
};
/**
 * Removes nodes, starting from `startNode` (inclusive) to `endNode`
 * (exclusive), from `container`.
 */
const removeNodes = (container, startNode, endNode = null) => {
    let node = startNode;
    while (node !== endNode) {
        const n = node.nextSibling;
        container.removeChild(node);
        node = n;
    }
};


/***/ }),

/***/ "../../node_modules/lit-html/lib/modify-template.js":
/*!*********************************************************!*\
  !*** /app/node_modules/lit-html/lib/modify-template.js ***!
  \*********************************************************/
/*! exports provided: removeNodesFromTemplate, insertNodeIntoTemplate */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "removeNodesFromTemplate", function() { return removeNodesFromTemplate; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "insertNodeIntoTemplate", function() { return insertNodeIntoTemplate; });
/* harmony import */ var _template_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./template.js */ "../../node_modules/lit-html/lib/template.js");
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
/**
 * @module shady-render
 */

const walkerNodeFilter = 133 /* NodeFilter.SHOW_{ELEMENT|COMMENT|TEXT} */;
/**
 * Removes the list of nodes from a Template safely. In addition to removing
 * nodes from the Template, the Template part indices are updated to match
 * the mutated Template DOM.
 *
 * As the template is walked the removal state is tracked and
 * part indices are adjusted as needed.
 *
 * div
 *   div#1 (remove) <-- start removing (removing node is div#1)
 *     div
 *       div#2 (remove)  <-- continue removing (removing node is still div#1)
 *         div
 * div <-- stop removing since previous sibling is the removing node (div#1,
 * removed 4 nodes)
 */
function removeNodesFromTemplate(template, nodesToRemove) {
    const { element: { content }, parts } = template;
    const walker = document.createTreeWalker(content, walkerNodeFilter, null, false);
    let partIndex = nextActiveIndexInTemplateParts(parts);
    let part = parts[partIndex];
    let nodeIndex = -1;
    let removeCount = 0;
    const nodesToRemoveInTemplate = [];
    let currentRemovingNode = null;
    while (walker.nextNode()) {
        nodeIndex++;
        const node = walker.currentNode;
        // End removal if stepped past the removing node
        if (node.previousSibling === currentRemovingNode) {
            currentRemovingNode = null;
        }
        // A node to remove was found in the template
        if (nodesToRemove.has(node)) {
            nodesToRemoveInTemplate.push(node);
            // Track node we're removing
            if (currentRemovingNode === null) {
                currentRemovingNode = node;
            }
        }
        // When removing, increment count by which to adjust subsequent part indices
        if (currentRemovingNode !== null) {
            removeCount++;
        }
        while (part !== undefined && part.index === nodeIndex) {
            // If part is in a removed node deactivate it by setting index to -1 or
            // adjust the index as needed.
            part.index = currentRemovingNode !== null ? -1 : part.index - removeCount;
            // go to the next active part.
            partIndex = nextActiveIndexInTemplateParts(parts, partIndex);
            part = parts[partIndex];
        }
    }
    nodesToRemoveInTemplate.forEach((n) => n.parentNode.removeChild(n));
}
const countNodes = (node) => {
    let count = (node.nodeType === 11 /* Node.DOCUMENT_FRAGMENT_NODE */) ? 0 : 1;
    const walker = document.createTreeWalker(node, walkerNodeFilter, null, false);
    while (walker.nextNode()) {
        count++;
    }
    return count;
};
const nextActiveIndexInTemplateParts = (parts, startIndex = -1) => {
    for (let i = startIndex + 1; i < parts.length; i++) {
        const part = parts[i];
        if (Object(_template_js__WEBPACK_IMPORTED_MODULE_0__["isTemplatePartActive"])(part)) {
            return i;
        }
    }
    return -1;
};
/**
 * Inserts the given node into the Template, optionally before the given
 * refNode. In addition to inserting the node into the Template, the Template
 * part indices are updated to match the mutated Template DOM.
 */
function insertNodeIntoTemplate(template, node, refNode = null) {
    const { element: { content }, parts } = template;
    // If there's no refNode, then put node at end of template.
    // No part indices need to be shifted in this case.
    if (refNode === null || refNode === undefined) {
        content.appendChild(node);
        return;
    }
    const walker = document.createTreeWalker(content, walkerNodeFilter, null, false);
    let partIndex = nextActiveIndexInTemplateParts(parts);
    let insertCount = 0;
    let walkerIndex = -1;
    while (walker.nextNode()) {
        walkerIndex++;
        const walkerNode = walker.currentNode;
        if (walkerNode === refNode) {
            insertCount = countNodes(node);
            refNode.parentNode.insertBefore(node, refNode);
        }
        while (partIndex !== -1 && parts[partIndex].index === walkerIndex) {
            // If we've inserted the node, simply adjust all subsequent parts
            if (insertCount > 0) {
                while (partIndex !== -1) {
                    parts[partIndex].index += insertCount;
                    partIndex = nextActiveIndexInTemplateParts(parts, partIndex);
                }
                return;
            }
            partIndex = nextActiveIndexInTemplateParts(parts, partIndex);
        }
    }
}


/***/ }),

/***/ "../../node_modules/lit-html/lib/part.js":
/*!**********************************************!*\
  !*** /app/node_modules/lit-html/lib/part.js ***!
  \**********************************************/
/*! exports provided: noChange, nothing */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "noChange", function() { return noChange; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "nothing", function() { return nothing; });
/**
 * @license
 * Copyright (c) 2018 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
/**
 * A sentinel value that signals that a value was handled by a directive and
 * should not be written to the DOM.
 */
const noChange = {};
/**
 * A sentinel value that signals a NodePart to fully clear its content.
 */
const nothing = {};


/***/ }),

/***/ "../../node_modules/lit-html/lib/parts.js":
/*!***********************************************!*\
  !*** /app/node_modules/lit-html/lib/parts.js ***!
  \***********************************************/
/*! exports provided: isPrimitive, AttributeCommitter, AttributePart, NodePart, BooleanAttributePart, PropertyCommitter, PropertyPart, EventPart */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isPrimitive", function() { return isPrimitive; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "AttributeCommitter", function() { return AttributeCommitter; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "AttributePart", function() { return AttributePart; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "NodePart", function() { return NodePart; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "BooleanAttributePart", function() { return BooleanAttributePart; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "PropertyCommitter", function() { return PropertyCommitter; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "PropertyPart", function() { return PropertyPart; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "EventPart", function() { return EventPart; });
/* harmony import */ var _directive_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./directive.js */ "../../node_modules/lit-html/lib/directive.js");
/* harmony import */ var _dom_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./dom.js */ "../../node_modules/lit-html/lib/dom.js");
/* harmony import */ var _part_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./part.js */ "../../node_modules/lit-html/lib/part.js");
/* harmony import */ var _template_instance_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./template-instance.js */ "../../node_modules/lit-html/lib/template-instance.js");
/* harmony import */ var _template_result_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./template-result.js */ "../../node_modules/lit-html/lib/template-result.js");
/* harmony import */ var _template_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./template.js */ "../../node_modules/lit-html/lib/template.js");
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
/**
 * @module lit-html
 */






const isPrimitive = (value) => (value === null ||
    !(typeof value === 'object' || typeof value === 'function'));
/**
 * Sets attribute values for AttributeParts, so that the value is only set once
 * even if there are multiple parts for an attribute.
 */
class AttributeCommitter {
    constructor(element, name, strings) {
        this.dirty = true;
        this.element = element;
        this.name = name;
        this.strings = strings;
        this.parts = [];
        for (let i = 0; i < strings.length - 1; i++) {
            this.parts[i] = this._createPart();
        }
    }
    /**
     * Creates a single part. Override this to create a differnt type of part.
     */
    _createPart() {
        return new AttributePart(this);
    }
    _getValue() {
        const strings = this.strings;
        const l = strings.length - 1;
        let text = '';
        for (let i = 0; i < l; i++) {
            text += strings[i];
            const part = this.parts[i];
            if (part !== undefined) {
                const v = part.value;
                if (v != null &&
                    (Array.isArray(v) || typeof v !== 'string' && v[Symbol.iterator])) {
                    for (const t of v) {
                        text += typeof t === 'string' ? t : String(t);
                    }
                }
                else {
                    text += typeof v === 'string' ? v : String(v);
                }
            }
        }
        text += strings[l];
        return text;
    }
    commit() {
        if (this.dirty) {
            this.dirty = false;
            this.element.setAttribute(this.name, this._getValue());
        }
    }
}
class AttributePart {
    constructor(comitter) {
        this.value = undefined;
        this.committer = comitter;
    }
    setValue(value) {
        if (value !== _part_js__WEBPACK_IMPORTED_MODULE_2__["noChange"] && (!isPrimitive(value) || value !== this.value)) {
            this.value = value;
            // If the value is a not a directive, dirty the committer so that it'll
            // call setAttribute. If the value is a directive, it'll dirty the
            // committer if it calls setValue().
            if (!Object(_directive_js__WEBPACK_IMPORTED_MODULE_0__["isDirective"])(value)) {
                this.committer.dirty = true;
            }
        }
    }
    commit() {
        while (Object(_directive_js__WEBPACK_IMPORTED_MODULE_0__["isDirective"])(this.value)) {
            const directive = this.value;
            this.value = _part_js__WEBPACK_IMPORTED_MODULE_2__["noChange"];
            directive(this);
        }
        if (this.value === _part_js__WEBPACK_IMPORTED_MODULE_2__["noChange"]) {
            return;
        }
        this.committer.commit();
    }
}
class NodePart {
    constructor(options) {
        this.value = undefined;
        this._pendingValue = undefined;
        this.options = options;
    }
    /**
     * Inserts this part into a container.
     *
     * This part must be empty, as its contents are not automatically moved.
     */
    appendInto(container) {
        this.startNode = container.appendChild(Object(_template_js__WEBPACK_IMPORTED_MODULE_5__["createMarker"])());
        this.endNode = container.appendChild(Object(_template_js__WEBPACK_IMPORTED_MODULE_5__["createMarker"])());
    }
    /**
     * Inserts this part between `ref` and `ref`'s next sibling. Both `ref` and
     * its next sibling must be static, unchanging nodes such as those that appear
     * in a literal section of a template.
     *
     * This part must be empty, as its contents are not automatically moved.
     */
    insertAfterNode(ref) {
        this.startNode = ref;
        this.endNode = ref.nextSibling;
    }
    /**
     * Appends this part into a parent part.
     *
     * This part must be empty, as its contents are not automatically moved.
     */
    appendIntoPart(part) {
        part._insert(this.startNode = Object(_template_js__WEBPACK_IMPORTED_MODULE_5__["createMarker"])());
        part._insert(this.endNode = Object(_template_js__WEBPACK_IMPORTED_MODULE_5__["createMarker"])());
    }
    /**
     * Appends this part after `ref`
     *
     * This part must be empty, as its contents are not automatically moved.
     */
    insertAfterPart(ref) {
        ref._insert(this.startNode = Object(_template_js__WEBPACK_IMPORTED_MODULE_5__["createMarker"])());
        this.endNode = ref.endNode;
        ref.endNode = this.startNode;
    }
    setValue(value) {
        this._pendingValue = value;
    }
    commit() {
        while (Object(_directive_js__WEBPACK_IMPORTED_MODULE_0__["isDirective"])(this._pendingValue)) {
            const directive = this._pendingValue;
            this._pendingValue = _part_js__WEBPACK_IMPORTED_MODULE_2__["noChange"];
            directive(this);
        }
        const value = this._pendingValue;
        if (value === _part_js__WEBPACK_IMPORTED_MODULE_2__["noChange"]) {
            return;
        }
        if (isPrimitive(value)) {
            if (value !== this.value) {
                this._commitText(value);
            }
        }
        else if (value instanceof _template_result_js__WEBPACK_IMPORTED_MODULE_4__["TemplateResult"]) {
            this._commitTemplateResult(value);
        }
        else if (value instanceof Node) {
            this._commitNode(value);
        }
        else if (Array.isArray(value) || value[Symbol.iterator]) {
            this._commitIterable(value);
        }
        else if (value === _part_js__WEBPACK_IMPORTED_MODULE_2__["nothing"]) {
            this.value = _part_js__WEBPACK_IMPORTED_MODULE_2__["nothing"];
            this.clear();
        }
        else {
            // Fallback, will render the string representation
            this._commitText(value);
        }
    }
    _insert(node) {
        this.endNode.parentNode.insertBefore(node, this.endNode);
    }
    _commitNode(value) {
        if (this.value === value) {
            return;
        }
        this.clear();
        this._insert(value);
        this.value = value;
    }
    _commitText(value) {
        const node = this.startNode.nextSibling;
        value = value == null ? '' : value;
        if (node === this.endNode.previousSibling &&
            node.nodeType === 3 /* Node.TEXT_NODE */) {
            // If we only have a single text node between the markers, we can just
            // set its value, rather than replacing it.
            // TODO(justinfagnani): Can we just check if this.value is primitive?
            node.data = value;
        }
        else {
            this._commitNode(document.createTextNode(typeof value === 'string' ? value : String(value)));
        }
        this.value = value;
    }
    _commitTemplateResult(value) {
        const template = this.options.templateFactory(value);
        if (this.value && this.value.template === template) {
            this.value.update(value.values);
        }
        else {
            // Make sure we propagate the template processor from the TemplateResult
            // so that we use its syntax extension, etc. The template factory comes
            // from the render function options so that it can control template
            // caching and preprocessing.
            const instance = new _template_instance_js__WEBPACK_IMPORTED_MODULE_3__["TemplateInstance"](template, value.processor, this.options);
            const fragment = instance._clone();
            instance.update(value.values);
            this._commitNode(fragment);
            this.value = instance;
        }
    }
    _commitIterable(value) {
        // For an Iterable, we create a new InstancePart per item, then set its
        // value to the item. This is a little bit of overhead for every item in
        // an Iterable, but it lets us recurse easily and efficiently update Arrays
        // of TemplateResults that will be commonly returned from expressions like:
        // array.map((i) => html`${i}`), by reusing existing TemplateInstances.
        // If _value is an array, then the previous render was of an
        // iterable and _value will contain the NodeParts from the previous
        // render. If _value is not an array, clear this part and make a new
        // array for NodeParts.
        if (!Array.isArray(this.value)) {
            this.value = [];
            this.clear();
        }
        // Lets us keep track of how many items we stamped so we can clear leftover
        // items from a previous render
        const itemParts = this.value;
        let partIndex = 0;
        let itemPart;
        for (const item of value) {
            // Try to reuse an existing part
            itemPart = itemParts[partIndex];
            // If no existing part, create a new one
            if (itemPart === undefined) {
                itemPart = new NodePart(this.options);
                itemParts.push(itemPart);
                if (partIndex === 0) {
                    itemPart.appendIntoPart(this);
                }
                else {
                    itemPart.insertAfterPart(itemParts[partIndex - 1]);
                }
            }
            itemPart.setValue(item);
            itemPart.commit();
            partIndex++;
        }
        if (partIndex < itemParts.length) {
            // Truncate the parts array so _value reflects the current state
            itemParts.length = partIndex;
            this.clear(itemPart && itemPart.endNode);
        }
    }
    clear(startNode = this.startNode) {
        Object(_dom_js__WEBPACK_IMPORTED_MODULE_1__["removeNodes"])(this.startNode.parentNode, startNode.nextSibling, this.endNode);
    }
}
/**
 * Implements a boolean attribute, roughly as defined in the HTML
 * specification.
 *
 * If the value is truthy, then the attribute is present with a value of
 * ''. If the value is falsey, the attribute is removed.
 */
class BooleanAttributePart {
    constructor(element, name, strings) {
        this.value = undefined;
        this._pendingValue = undefined;
        if (strings.length !== 2 || strings[0] !== '' || strings[1] !== '') {
            throw new Error('Boolean attributes can only contain a single expression');
        }
        this.element = element;
        this.name = name;
        this.strings = strings;
    }
    setValue(value) {
        this._pendingValue = value;
    }
    commit() {
        while (Object(_directive_js__WEBPACK_IMPORTED_MODULE_0__["isDirective"])(this._pendingValue)) {
            const directive = this._pendingValue;
            this._pendingValue = _part_js__WEBPACK_IMPORTED_MODULE_2__["noChange"];
            directive(this);
        }
        if (this._pendingValue === _part_js__WEBPACK_IMPORTED_MODULE_2__["noChange"]) {
            return;
        }
        const value = !!this._pendingValue;
        if (this.value !== value) {
            if (value) {
                this.element.setAttribute(this.name, '');
            }
            else {
                this.element.removeAttribute(this.name);
            }
        }
        this.value = value;
        this._pendingValue = _part_js__WEBPACK_IMPORTED_MODULE_2__["noChange"];
    }
}
/**
 * Sets attribute values for PropertyParts, so that the value is only set once
 * even if there are multiple parts for a property.
 *
 * If an expression controls the whole property value, then the value is simply
 * assigned to the property under control. If there are string literals or
 * multiple expressions, then the strings are expressions are interpolated into
 * a string first.
 */
class PropertyCommitter extends AttributeCommitter {
    constructor(element, name, strings) {
        super(element, name, strings);
        this.single =
            (strings.length === 2 && strings[0] === '' && strings[1] === '');
    }
    _createPart() {
        return new PropertyPart(this);
    }
    _getValue() {
        if (this.single) {
            return this.parts[0].value;
        }
        return super._getValue();
    }
    commit() {
        if (this.dirty) {
            this.dirty = false;
            this.element[this.name] = this._getValue();
        }
    }
}
class PropertyPart extends AttributePart {
}
// Detect event listener options support. If the `capture` property is read
// from the options object, then options are supported. If not, then the thrid
// argument to add/removeEventListener is interpreted as the boolean capture
// value so we should only pass the `capture` property.
let eventOptionsSupported = false;
try {
    const options = {
        get capture() {
            eventOptionsSupported = true;
            return false;
        }
    };
    window.addEventListener('test', options, options);
    window.removeEventListener('test', options, options);
}
catch (_e) {
}
class EventPart {
    constructor(element, eventName, eventContext) {
        this.value = undefined;
        this._pendingValue = undefined;
        this.element = element;
        this.eventName = eventName;
        this.eventContext = eventContext;
        this._boundHandleEvent = (e) => this.handleEvent(e);
    }
    setValue(value) {
        this._pendingValue = value;
    }
    commit() {
        while (Object(_directive_js__WEBPACK_IMPORTED_MODULE_0__["isDirective"])(this._pendingValue)) {
            const directive = this._pendingValue;
            this._pendingValue = _part_js__WEBPACK_IMPORTED_MODULE_2__["noChange"];
            directive(this);
        }
        if (this._pendingValue === _part_js__WEBPACK_IMPORTED_MODULE_2__["noChange"]) {
            return;
        }
        const newListener = this._pendingValue;
        const oldListener = this.value;
        const shouldRemoveListener = newListener == null ||
            oldListener != null &&
                (newListener.capture !== oldListener.capture ||
                    newListener.once !== oldListener.once ||
                    newListener.passive !== oldListener.passive);
        const shouldAddListener = newListener != null && (oldListener == null || shouldRemoveListener);
        if (shouldRemoveListener) {
            this.element.removeEventListener(this.eventName, this._boundHandleEvent, this._options);
        }
        if (shouldAddListener) {
            this._options = getOptions(newListener);
            this.element.addEventListener(this.eventName, this._boundHandleEvent, this._options);
        }
        this.value = newListener;
        this._pendingValue = _part_js__WEBPACK_IMPORTED_MODULE_2__["noChange"];
    }
    handleEvent(event) {
        if (typeof this.value === 'function') {
            this.value.call(this.eventContext || this.element, event);
        }
        else {
            this.value.handleEvent(event);
        }
    }
}
// We copy options because of the inconsistent behavior of browsers when reading
// the third argument of add/removeEventListener. IE11 doesn't support options
// at all. Chrome 41 only reads `capture` if the argument is an object.
const getOptions = (o) => o &&
    (eventOptionsSupported ?
        { capture: o.capture, passive: o.passive, once: o.once } :
        o.capture);


/***/ }),

/***/ "../../node_modules/lit-html/lib/render.js":
/*!************************************************!*\
  !*** /app/node_modules/lit-html/lib/render.js ***!
  \************************************************/
/*! exports provided: parts, render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "parts", function() { return parts; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony import */ var _dom_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dom.js */ "../../node_modules/lit-html/lib/dom.js");
/* harmony import */ var _parts_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./parts.js */ "../../node_modules/lit-html/lib/parts.js");
/* harmony import */ var _template_factory_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./template-factory.js */ "../../node_modules/lit-html/lib/template-factory.js");
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
/**
 * @module lit-html
 */



const parts = new WeakMap();
/**
 * Renders a template to a container.
 *
 * To update a container with new values, reevaluate the template literal and
 * call `render` with the new result.
 *
 * @param result a TemplateResult created by evaluating a template tag like
 *     `html` or `svg`.
 * @param container A DOM parent to render to. The entire contents are either
 *     replaced, or efficiently updated if the same result type was previous
 *     rendered there.
 * @param options RenderOptions for the entire render tree rendered to this
 *     container. Render options must *not* change between renders to the same
 *     container, as those changes will not effect previously rendered DOM.
 */
const render = (result, container, options) => {
    let part = parts.get(container);
    if (part === undefined) {
        Object(_dom_js__WEBPACK_IMPORTED_MODULE_0__["removeNodes"])(container, container.firstChild);
        parts.set(container, part = new _parts_js__WEBPACK_IMPORTED_MODULE_1__["NodePart"](Object.assign({ templateFactory: _template_factory_js__WEBPACK_IMPORTED_MODULE_2__["templateFactory"] }, options)));
        part.appendInto(container);
    }
    part.setValue(result);
    part.commit();
};


/***/ }),

/***/ "../../node_modules/lit-html/lib/shady-render.js":
/*!******************************************************!*\
  !*** /app/node_modules/lit-html/lib/shady-render.js ***!
  \******************************************************/
/*! exports provided: html, svg, TemplateResult, render */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony import */ var _dom_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dom.js */ "../../node_modules/lit-html/lib/dom.js");
/* harmony import */ var _modify_template_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modify-template.js */ "../../node_modules/lit-html/lib/modify-template.js");
/* harmony import */ var _render_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./render.js */ "../../node_modules/lit-html/lib/render.js");
/* harmony import */ var _template_factory_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./template-factory.js */ "../../node_modules/lit-html/lib/template-factory.js");
/* harmony import */ var _template_instance_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./template-instance.js */ "../../node_modules/lit-html/lib/template-instance.js");
/* harmony import */ var _template_result_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./template-result.js */ "../../node_modules/lit-html/lib/template-result.js");
/* harmony import */ var _template_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./template.js */ "../../node_modules/lit-html/lib/template.js");
/* harmony import */ var _lit_html_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../lit-html.js */ "../../node_modules/lit-html/lit-html.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "html", function() { return _lit_html_js__WEBPACK_IMPORTED_MODULE_7__["html"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "svg", function() { return _lit_html_js__WEBPACK_IMPORTED_MODULE_7__["svg"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "TemplateResult", function() { return _lit_html_js__WEBPACK_IMPORTED_MODULE_7__["TemplateResult"]; });

/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
/**
 * Module to add shady DOM/shady CSS polyfill support to lit-html template
 * rendering. See the [[render]] method for details.
 *
 * @module shady-render
 * @preferred
 */
/**
 * Do not remove this comment; it keeps typedoc from misplacing the module
 * docs.
 */








// Get a key to lookup in `templateCaches`.
const getTemplateCacheKey = (type, scopeName) => `${type}--${scopeName}`;
let compatibleShadyCSSVersion = true;
if (typeof window.ShadyCSS === 'undefined') {
    compatibleShadyCSSVersion = false;
}
else if (typeof window.ShadyCSS.prepareTemplateDom === 'undefined') {
    console.warn(`Incompatible ShadyCSS version detected.` +
        `Please update to at least @webcomponents/webcomponentsjs@2.0.2 and` +
        `@webcomponents/shadycss@1.3.1.`);
    compatibleShadyCSSVersion = false;
}
/**
 * Template factory which scopes template DOM using ShadyCSS.
 * @param scopeName {string}
 */
const shadyTemplateFactory = (scopeName) => (result) => {
    const cacheKey = getTemplateCacheKey(result.type, scopeName);
    let templateCache = _template_factory_js__WEBPACK_IMPORTED_MODULE_3__["templateCaches"].get(cacheKey);
    if (templateCache === undefined) {
        templateCache = {
            stringsArray: new WeakMap(),
            keyString: new Map()
        };
        _template_factory_js__WEBPACK_IMPORTED_MODULE_3__["templateCaches"].set(cacheKey, templateCache);
    }
    let template = templateCache.stringsArray.get(result.strings);
    if (template !== undefined) {
        return template;
    }
    const key = result.strings.join(_template_js__WEBPACK_IMPORTED_MODULE_6__["marker"]);
    template = templateCache.keyString.get(key);
    if (template === undefined) {
        const element = result.getTemplateElement();
        if (compatibleShadyCSSVersion) {
            window.ShadyCSS.prepareTemplateDom(element, scopeName);
        }
        template = new _template_js__WEBPACK_IMPORTED_MODULE_6__["Template"](result, element);
        templateCache.keyString.set(key, template);
    }
    templateCache.stringsArray.set(result.strings, template);
    return template;
};
const TEMPLATE_TYPES = ['html', 'svg'];
/**
 * Removes all style elements from Templates for the given scopeName.
 */
const removeStylesFromLitTemplates = (scopeName) => {
    TEMPLATE_TYPES.forEach((type) => {
        const templates = _template_factory_js__WEBPACK_IMPORTED_MODULE_3__["templateCaches"].get(getTemplateCacheKey(type, scopeName));
        if (templates !== undefined) {
            templates.keyString.forEach((template) => {
                const { element: { content } } = template;
                // IE 11 doesn't support the iterable param Set constructor
                const styles = new Set();
                Array.from(content.querySelectorAll('style')).forEach((s) => {
                    styles.add(s);
                });
                Object(_modify_template_js__WEBPACK_IMPORTED_MODULE_1__["removeNodesFromTemplate"])(template, styles);
            });
        }
    });
};
const shadyRenderSet = new Set();
/**
 * For the given scope name, ensures that ShadyCSS style scoping is performed.
 * This is done just once per scope name so the fragment and template cannot
 * be modified.
 * (1) extracts styles from the rendered fragment and hands them to ShadyCSS
 * to be scoped and appended to the document
 * (2) removes style elements from all lit-html Templates for this scope name.
 *
 * Note, <style> elements can only be placed into templates for the
 * initial rendering of the scope. If <style> elements are included in templates
 * dynamically rendered to the scope (after the first scope render), they will
 * not be scoped and the <style> will be left in the template and rendered
 * output.
 */
const prepareTemplateStyles = (renderedDOM, template, scopeName) => {
    shadyRenderSet.add(scopeName);
    // Move styles out of rendered DOM and store.
    const styles = renderedDOM.querySelectorAll('style');
    // If there are no styles, skip unnecessary work
    if (styles.length === 0) {
        // Ensure prepareTemplateStyles is called to support adding
        // styles via `prepareAdoptedCssText` since that requires that
        // `prepareTemplateStyles` is called.
        window.ShadyCSS.prepareTemplateStyles(template.element, scopeName);
        return;
    }
    const condensedStyle = document.createElement('style');
    // Collect styles into a single style. This helps us make sure ShadyCSS
    // manipulations will not prevent us from being able to fix up template
    // part indices.
    // NOTE: collecting styles is inefficient for browsers but ShadyCSS
    // currently does this anyway. When it does not, this should be changed.
    for (let i = 0; i < styles.length; i++) {
        const style = styles[i];
        style.parentNode.removeChild(style);
        condensedStyle.textContent += style.textContent;
    }
    // Remove styles from nested templates in this scope.
    removeStylesFromLitTemplates(scopeName);
    // And then put the condensed style into the "root" template passed in as
    // `template`.
    Object(_modify_template_js__WEBPACK_IMPORTED_MODULE_1__["insertNodeIntoTemplate"])(template, condensedStyle, template.element.content.firstChild);
    // Note, it's important that ShadyCSS gets the template that `lit-html`
    // will actually render so that it can update the style inside when
    // needed (e.g. @apply native Shadow DOM case).
    window.ShadyCSS.prepareTemplateStyles(template.element, scopeName);
    if (window.ShadyCSS.nativeShadow) {
        // When in native Shadow DOM, re-add styling to rendered content using
        // the style ShadyCSS produced.
        const style = template.element.content.querySelector('style');
        renderedDOM.insertBefore(style.cloneNode(true), renderedDOM.firstChild);
    }
    else {
        // When not in native Shadow DOM, at this point ShadyCSS will have
        // removed the style from the lit template and parts will be broken as a
        // result. To fix this, we put back the style node ShadyCSS removed
        // and then tell lit to remove that node from the template.
        // NOTE, ShadyCSS creates its own style so we can safely add/remove
        // `condensedStyle` here.
        template.element.content.insertBefore(condensedStyle, template.element.content.firstChild);
        const removes = new Set();
        removes.add(condensedStyle);
        Object(_modify_template_js__WEBPACK_IMPORTED_MODULE_1__["removeNodesFromTemplate"])(template, removes);
    }
};
/**
 * Extension to the standard `render` method which supports rendering
 * to ShadowRoots when the ShadyDOM (https://github.com/webcomponents/shadydom)
 * and ShadyCSS (https://github.com/webcomponents/shadycss) polyfills are used
 * or when the webcomponentsjs
 * (https://github.com/webcomponents/webcomponentsjs) polyfill is used.
 *
 * Adds a `scopeName` option which is used to scope element DOM and stylesheets
 * when native ShadowDOM is unavailable. The `scopeName` will be added to
 * the class attribute of all rendered DOM. In addition, any style elements will
 * be automatically re-written with this `scopeName` selector and moved out
 * of the rendered DOM and into the document `<head>`.
 *
 * It is common to use this render method in conjunction with a custom element
 * which renders a shadowRoot. When this is done, typically the element's
 * `localName` should be used as the `scopeName`.
 *
 * In addition to DOM scoping, ShadyCSS also supports a basic shim for css
 * custom properties (needed only on older browsers like IE11) and a shim for
 * a deprecated feature called `@apply` that supports applying a set of css
 * custom properties to a given location.
 *
 * Usage considerations:
 *
 * * Part values in `<style>` elements are only applied the first time a given
 * `scopeName` renders. Subsequent changes to parts in style elements will have
 * no effect. Because of this, parts in style elements should only be used for
 * values that will never change, for example parts that set scope-wide theme
 * values or parts which render shared style elements.
 *
 * * Note, due to a limitation of the ShadyDOM polyfill, rendering in a
 * custom element's `constructor` is not supported. Instead rendering should
 * either done asynchronously, for example at microtask timing (for example
 * `Promise.resolve()`), or be deferred until the first time the element's
 * `connectedCallback` runs.
 *
 * Usage considerations when using shimmed custom properties or `@apply`:
 *
 * * Whenever any dynamic changes are made which affect
 * css custom properties, `ShadyCSS.styleElement(element)` must be called
 * to update the element. There are two cases when this is needed:
 * (1) the element is connected to a new parent, (2) a class is added to the
 * element that causes it to match different custom properties.
 * To address the first case when rendering a custom element, `styleElement`
 * should be called in the element's `connectedCallback`.
 *
 * * Shimmed custom properties may only be defined either for an entire
 * shadowRoot (for example, in a `:host` rule) or via a rule that directly
 * matches an element with a shadowRoot. In other words, instead of flowing from
 * parent to child as do native css custom properties, shimmed custom properties
 * flow only from shadowRoots to nested shadowRoots.
 *
 * * When using `@apply` mixing css shorthand property names with
 * non-shorthand names (for example `border` and `border-width`) is not
 * supported.
 */
const render = (result, container, options) => {
    const scopeName = options.scopeName;
    const hasRendered = _render_js__WEBPACK_IMPORTED_MODULE_2__["parts"].has(container);
    const needsScoping = container instanceof ShadowRoot &&
        compatibleShadyCSSVersion && result instanceof _template_result_js__WEBPACK_IMPORTED_MODULE_5__["TemplateResult"];
    // Handle first render to a scope specially...
    const firstScopeRender = needsScoping && !shadyRenderSet.has(scopeName);
    // On first scope render, render into a fragment; this cannot be a single
    // fragment that is reused since nested renders can occur synchronously.
    const renderContainer = firstScopeRender ? document.createDocumentFragment() : container;
    Object(_render_js__WEBPACK_IMPORTED_MODULE_2__["render"])(result, renderContainer, Object.assign({ templateFactory: shadyTemplateFactory(scopeName) }, options));
    // When performing first scope render,
    // (1) We've rendered into a fragment so that there's a chance to
    // `prepareTemplateStyles` before sub-elements hit the DOM
    // (which might cause them to render based on a common pattern of
    // rendering in a custom element's `connectedCallback`);
    // (2) Scope the template with ShadyCSS one time only for this scope.
    // (3) Render the fragment into the container and make sure the
    // container knows its `part` is the one we just rendered. This ensures
    // DOM will be re-used on subsequent renders.
    if (firstScopeRender) {
        const part = _render_js__WEBPACK_IMPORTED_MODULE_2__["parts"].get(renderContainer);
        _render_js__WEBPACK_IMPORTED_MODULE_2__["parts"].delete(renderContainer);
        if (part.value instanceof _template_instance_js__WEBPACK_IMPORTED_MODULE_4__["TemplateInstance"]) {
            prepareTemplateStyles(renderContainer, part.value.template, scopeName);
        }
        Object(_dom_js__WEBPACK_IMPORTED_MODULE_0__["removeNodes"])(container, container.firstChild);
        container.appendChild(renderContainer);
        _render_js__WEBPACK_IMPORTED_MODULE_2__["parts"].set(container, part);
    }
    // After elements have hit the DOM, update styling if this is the
    // initial render to this container.
    // This is needed whenever dynamic changes are made so it would be
    // safest to do every render; however, this would regress performance
    // so we leave it up to the user to call `ShadyCSSS.styleElement`
    // for dynamic changes.
    if (!hasRendered && needsScoping) {
        window.ShadyCSS.styleElement(container.host);
    }
};


/***/ }),

/***/ "../../node_modules/lit-html/lib/template-factory.js":
/*!**********************************************************!*\
  !*** /app/node_modules/lit-html/lib/template-factory.js ***!
  \**********************************************************/
/*! exports provided: templateFactory, templateCaches */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "templateFactory", function() { return templateFactory; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "templateCaches", function() { return templateCaches; });
/* harmony import */ var _template_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./template.js */ "../../node_modules/lit-html/lib/template.js");
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */

/**
 * The default TemplateFactory which caches Templates keyed on
 * result.type and result.strings.
 */
function templateFactory(result) {
    let templateCache = templateCaches.get(result.type);
    if (templateCache === undefined) {
        templateCache = {
            stringsArray: new WeakMap(),
            keyString: new Map()
        };
        templateCaches.set(result.type, templateCache);
    }
    let template = templateCache.stringsArray.get(result.strings);
    if (template !== undefined) {
        return template;
    }
    // If the TemplateStringsArray is new, generate a key from the strings
    // This key is shared between all templates with identical content
    const key = result.strings.join(_template_js__WEBPACK_IMPORTED_MODULE_0__["marker"]);
    // Check if we already have a Template for this key
    template = templateCache.keyString.get(key);
    if (template === undefined) {
        // If we have not seen this key before, create a new Template
        template = new _template_js__WEBPACK_IMPORTED_MODULE_0__["Template"](result, result.getTemplateElement());
        // Cache the Template for this key
        templateCache.keyString.set(key, template);
    }
    // Cache all future queries for this TemplateStringsArray
    templateCache.stringsArray.set(result.strings, template);
    return template;
}
const templateCaches = new Map();


/***/ }),

/***/ "../../node_modules/lit-html/lib/template-instance.js":
/*!***********************************************************!*\
  !*** /app/node_modules/lit-html/lib/template-instance.js ***!
  \***********************************************************/
/*! exports provided: TemplateInstance */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "TemplateInstance", function() { return TemplateInstance; });
/* harmony import */ var _dom_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dom.js */ "../../node_modules/lit-html/lib/dom.js");
/* harmony import */ var _template_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./template.js */ "../../node_modules/lit-html/lib/template.js");
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
/**
 * @module lit-html
 */


/**
 * An instance of a `Template` that can be attached to the DOM and updated
 * with new values.
 */
class TemplateInstance {
    constructor(template, processor, options) {
        this._parts = [];
        this.template = template;
        this.processor = processor;
        this.options = options;
    }
    update(values) {
        let i = 0;
        for (const part of this._parts) {
            if (part !== undefined) {
                part.setValue(values[i]);
            }
            i++;
        }
        for (const part of this._parts) {
            if (part !== undefined) {
                part.commit();
            }
        }
    }
    _clone() {
        // When using the Custom Elements polyfill, clone the node, rather than
        // importing it, to keep the fragment in the template's document. This
        // leaves the fragment inert so custom elements won't upgrade and
        // potentially modify their contents by creating a polyfilled ShadowRoot
        // while we traverse the tree.
        const fragment = _dom_js__WEBPACK_IMPORTED_MODULE_0__["isCEPolyfill"] ?
            this.template.element.content.cloneNode(true) :
            document.importNode(this.template.element.content, true);
        const parts = this.template.parts;
        let partIndex = 0;
        let nodeIndex = 0;
        const _prepareInstance = (fragment) => {
            // Edge needs all 4 parameters present; IE11 needs 3rd parameter to be
            // null
            const walker = document.createTreeWalker(fragment, 133 /* NodeFilter.SHOW_{ELEMENT|COMMENT|TEXT} */, null, false);
            let node = walker.nextNode();
            // Loop through all the nodes and parts of a template
            while (partIndex < parts.length && node !== null) {
                const part = parts[partIndex];
                // Consecutive Parts may have the same node index, in the case of
                // multiple bound attributes on an element. So each iteration we either
                // increment the nodeIndex, if we aren't on a node with a part, or the
                // partIndex if we are. By not incrementing the nodeIndex when we find a
                // part, we allow for the next part to be associated with the current
                // node if neccessasry.
                if (!Object(_template_js__WEBPACK_IMPORTED_MODULE_1__["isTemplatePartActive"])(part)) {
                    this._parts.push(undefined);
                    partIndex++;
                }
                else if (nodeIndex === part.index) {
                    if (part.type === 'node') {
                        const part = this.processor.handleTextExpression(this.options);
                        part.insertAfterNode(node.previousSibling);
                        this._parts.push(part);
                    }
                    else {
                        this._parts.push(...this.processor.handleAttributeExpressions(node, part.name, part.strings, this.options));
                    }
                    partIndex++;
                }
                else {
                    nodeIndex++;
                    if (node.nodeName === 'TEMPLATE') {
                        _prepareInstance(node.content);
                    }
                    node = walker.nextNode();
                }
            }
        };
        _prepareInstance(fragment);
        if (_dom_js__WEBPACK_IMPORTED_MODULE_0__["isCEPolyfill"]) {
            document.adoptNode(fragment);
            customElements.upgrade(fragment);
        }
        return fragment;
    }
}


/***/ }),

/***/ "../../node_modules/lit-html/lib/template-result.js":
/*!*********************************************************!*\
  !*** /app/node_modules/lit-html/lib/template-result.js ***!
  \*********************************************************/
/*! exports provided: TemplateResult, SVGTemplateResult */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "TemplateResult", function() { return TemplateResult; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "SVGTemplateResult", function() { return SVGTemplateResult; });
/* harmony import */ var _dom_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dom.js */ "../../node_modules/lit-html/lib/dom.js");
/* harmony import */ var _template_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./template.js */ "../../node_modules/lit-html/lib/template.js");
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
/**
 * @module lit-html
 */


/**
 * The return type of `html`, which holds a Template and the values from
 * interpolated expressions.
 */
class TemplateResult {
    constructor(strings, values, type, processor) {
        this.strings = strings;
        this.values = values;
        this.type = type;
        this.processor = processor;
    }
    /**
     * Returns a string of HTML used to create a `<template>` element.
     */
    getHTML() {
        const endIndex = this.strings.length - 1;
        let html = '';
        for (let i = 0; i < endIndex; i++) {
            const s = this.strings[i];
            // This exec() call does two things:
            // 1) Appends a suffix to the bound attribute name to opt out of special
            // attribute value parsing that IE11 and Edge do, like for style and
            // many SVG attributes. The Template class also appends the same suffix
            // when looking up attributes to create Parts.
            // 2) Adds an unquoted-attribute-safe marker for the first expression in
            // an attribute. Subsequent attribute expressions will use node markers,
            // and this is safe since attributes with multiple expressions are
            // guaranteed to be quoted.
            const match = _template_js__WEBPACK_IMPORTED_MODULE_1__["lastAttributeNameRegex"].exec(s);
            if (match) {
                // We're starting a new bound attribute.
                // Add the safe attribute suffix, and use unquoted-attribute-safe
                // marker.
                html += s.substr(0, match.index) + match[1] + match[2] +
                    _template_js__WEBPACK_IMPORTED_MODULE_1__["boundAttributeSuffix"] + match[3] + _template_js__WEBPACK_IMPORTED_MODULE_1__["marker"];
            }
            else {
                // We're either in a bound node, or trailing bound attribute.
                // Either way, nodeMarker is safe to use.
                html += s + _template_js__WEBPACK_IMPORTED_MODULE_1__["nodeMarker"];
            }
        }
        return html + this.strings[endIndex];
    }
    getTemplateElement() {
        const template = document.createElement('template');
        template.innerHTML = this.getHTML();
        return template;
    }
}
/**
 * A TemplateResult for SVG fragments.
 *
 * This class wraps HTMl in an `<svg>` tag in order to parse its contents in the
 * SVG namespace, then modifies the template to remove the `<svg>` tag so that
 * clones only container the original fragment.
 */
class SVGTemplateResult extends TemplateResult {
    getHTML() {
        return `<svg>${super.getHTML()}</svg>`;
    }
    getTemplateElement() {
        const template = super.getTemplateElement();
        const content = template.content;
        const svgElement = content.firstChild;
        content.removeChild(svgElement);
        Object(_dom_js__WEBPACK_IMPORTED_MODULE_0__["reparentNodes"])(content, svgElement.firstChild);
        return template;
    }
}


/***/ }),

/***/ "../../node_modules/lit-html/lib/template.js":
/*!**************************************************!*\
  !*** /app/node_modules/lit-html/lib/template.js ***!
  \**************************************************/
/*! exports provided: marker, nodeMarker, markerRegex, boundAttributeSuffix, Template, isTemplatePartActive, createMarker, lastAttributeNameRegex */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "marker", function() { return marker; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "nodeMarker", function() { return nodeMarker; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "markerRegex", function() { return markerRegex; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "boundAttributeSuffix", function() { return boundAttributeSuffix; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Template", function() { return Template; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isTemplatePartActive", function() { return isTemplatePartActive; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "createMarker", function() { return createMarker; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "lastAttributeNameRegex", function() { return lastAttributeNameRegex; });
/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
/**
 * An expression marker with embedded unique key to avoid collision with
 * possible text in templates.
 */
const marker = `{{lit-${String(Math.random()).slice(2)}}}`;
/**
 * An expression marker used text-positions, multi-binding attributes, and
 * attributes with markup-like text values.
 */
const nodeMarker = `<!--${marker}-->`;
const markerRegex = new RegExp(`${marker}|${nodeMarker}`);
/**
 * Suffix appended to all bound attribute names.
 */
const boundAttributeSuffix = '$lit$';
/**
 * An updateable Template that tracks the location of dynamic parts.
 */
class Template {
    constructor(result, element) {
        this.parts = [];
        this.element = element;
        let index = -1;
        let partIndex = 0;
        const nodesToRemove = [];
        const _prepareTemplate = (template) => {
            const content = template.content;
            // Edge needs all 4 parameters present; IE11 needs 3rd parameter to be
            // null
            const walker = document.createTreeWalker(content, 133 /* NodeFilter.SHOW_{ELEMENT|COMMENT|TEXT} */, null, false);
            // Keeps track of the last index associated with a part. We try to delete
            // unnecessary nodes, but we never want to associate two different parts
            // to the same index. They must have a constant node between.
            let lastPartIndex = 0;
            while (walker.nextNode()) {
                index++;
                const node = walker.currentNode;
                if (node.nodeType === 1 /* Node.ELEMENT_NODE */) {
                    if (node.hasAttributes()) {
                        const attributes = node.attributes;
                        // Per
                        // https://developer.mozilla.org/en-US/docs/Web/API/NamedNodeMap,
                        // attributes are not guaranteed to be returned in document order.
                        // In particular, Edge/IE can return them out of order, so we cannot
                        // assume a correspondance between part index and attribute index.
                        let count = 0;
                        for (let i = 0; i < attributes.length; i++) {
                            if (attributes[i].value.indexOf(marker) >= 0) {
                                count++;
                            }
                        }
                        while (count-- > 0) {
                            // Get the template literal section leading up to the first
                            // expression in this attribute
                            const stringForPart = result.strings[partIndex];
                            // Find the attribute name
                            const name = lastAttributeNameRegex.exec(stringForPart)[2];
                            // Find the corresponding attribute
                            // All bound attributes have had a suffix added in
                            // TemplateResult#getHTML to opt out of special attribute
                            // handling. To look up the attribute value we also need to add
                            // the suffix.
                            const attributeLookupName = name.toLowerCase() + boundAttributeSuffix;
                            const attributeValue = node.getAttribute(attributeLookupName);
                            const strings = attributeValue.split(markerRegex);
                            this.parts.push({ type: 'attribute', index, name, strings });
                            node.removeAttribute(attributeLookupName);
                            partIndex += strings.length - 1;
                        }
                    }
                    if (node.tagName === 'TEMPLATE') {
                        _prepareTemplate(node);
                    }
                }
                else if (node.nodeType === 3 /* Node.TEXT_NODE */) {
                    const data = node.data;
                    if (data.indexOf(marker) >= 0) {
                        const parent = node.parentNode;
                        const strings = data.split(markerRegex);
                        const lastIndex = strings.length - 1;
                        // Generate a new text node for each literal section
                        // These nodes are also used as the markers for node parts
                        for (let i = 0; i < lastIndex; i++) {
                            parent.insertBefore((strings[i] === '') ? createMarker() :
                                document.createTextNode(strings[i]), node);
                            this.parts.push({ type: 'node', index: ++index });
                        }
                        // If there's no text, we must insert a comment to mark our place.
                        // Else, we can trust it will stick around after cloning.
                        if (strings[lastIndex] === '') {
                            parent.insertBefore(createMarker(), node);
                            nodesToRemove.push(node);
                        }
                        else {
                            node.data = strings[lastIndex];
                        }
                        // We have a part for each match found
                        partIndex += lastIndex;
                    }
                }
                else if (node.nodeType === 8 /* Node.COMMENT_NODE */) {
                    if (node.data === marker) {
                        const parent = node.parentNode;
                        // Add a new marker node to be the startNode of the Part if any of
                        // the following are true:
                        //  * We don't have a previousSibling
                        //  * The previousSibling is already the start of a previous part
                        if (node.previousSibling === null || index === lastPartIndex) {
                            index++;
                            parent.insertBefore(createMarker(), node);
                        }
                        lastPartIndex = index;
                        this.parts.push({ type: 'node', index });
                        // If we don't have a nextSibling, keep this node so we have an end.
                        // Else, we can remove it to save future costs.
                        if (node.nextSibling === null) {
                            node.data = '';
                        }
                        else {
                            nodesToRemove.push(node);
                            index--;
                        }
                        partIndex++;
                    }
                    else {
                        let i = -1;
                        while ((i = node.data.indexOf(marker, i + 1)) !==
                            -1) {
                            // Comment node has a binding marker inside, make an inactive part
                            // The binding won't work, but subsequent bindings will
                            // TODO (justinfagnani): consider whether it's even worth it to
                            // make bindings in comments work
                            this.parts.push({ type: 'node', index: -1 });
                        }
                    }
                }
            }
        };
        _prepareTemplate(element);
        // Remove text binding nodes after the walk to not disturb the TreeWalker
        for (const n of nodesToRemove) {
            n.parentNode.removeChild(n);
        }
    }
}
const isTemplatePartActive = (part) => part.index !== -1;
// Allows `document.createComment('')` to be renamed for a
// small manual size-savings.
const createMarker = () => document.createComment('');
/**
 * This regex extracts the attribute name preceding an attribute-position
 * expression. It does this by matching the syntax allowed for attributes
 * against the string literal directly preceding the expression, assuming that
 * the expression is in an attribute-value position.
 *
 * See attributes in the HTML spec:
 * https://www.w3.org/TR/html5/syntax.html#attributes-0
 *
 * "\0-\x1F\x7F-\x9F" are Unicode control characters
 *
 * " \x09\x0a\x0c\x0d" are HTML space characters:
 * https://www.w3.org/TR/html5/infrastructure.html#space-character
 *
 * So an attribute is:
 *  * The name: any character except a control character, space character, ('),
 *    ("), ">", "=", or "/"
 *  * Followed by zero or more space characters
 *  * Followed by "="
 *  * Followed by zero or more space characters
 *  * Followed by:
 *    * Any character except space, ('), ("), "<", ">", "=", (`), or
 *    * (") then any non-("), or
 *    * (') then any non-(')
 */
const lastAttributeNameRegex = /([ \x09\x0a\x0c\x0d])([^\0-\x1F\x7F-\x9F \x09\x0a\x0c\x0d"'>=/]+)([ \x09\x0a\x0c\x0d]*=[ \x09\x0a\x0c\x0d]*(?:[^ \x09\x0a\x0c\x0d"'`<>=]*|"[^"]*|'[^']*))$/;


/***/ }),

/***/ "../../node_modules/lit-html/lit-html.js":
/*!**********************************************!*\
  !*** /app/node_modules/lit-html/lit-html.js ***!
  \**********************************************/
/*! exports provided: DefaultTemplateProcessor, defaultTemplateProcessor, directive, isDirective, removeNodes, reparentNodes, noChange, nothing, AttributeCommitter, AttributePart, BooleanAttributePart, EventPart, isPrimitive, NodePart, PropertyCommitter, PropertyPart, parts, render, templateCaches, templateFactory, TemplateInstance, SVGTemplateResult, TemplateResult, createMarker, isTemplatePartActive, Template, html, svg */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "html", function() { return html; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "svg", function() { return svg; });
/* harmony import */ var _lib_default_template_processor_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./lib/default-template-processor.js */ "../../node_modules/lit-html/lib/default-template-processor.js");
/* harmony import */ var _lib_template_result_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./lib/template-result.js */ "../../node_modules/lit-html/lib/template-result.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "DefaultTemplateProcessor", function() { return _lib_default_template_processor_js__WEBPACK_IMPORTED_MODULE_0__["DefaultTemplateProcessor"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "defaultTemplateProcessor", function() { return _lib_default_template_processor_js__WEBPACK_IMPORTED_MODULE_0__["defaultTemplateProcessor"]; });

/* harmony import */ var _lib_directive_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./lib/directive.js */ "../../node_modules/lit-html/lib/directive.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "directive", function() { return _lib_directive_js__WEBPACK_IMPORTED_MODULE_2__["directive"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "isDirective", function() { return _lib_directive_js__WEBPACK_IMPORTED_MODULE_2__["isDirective"]; });

/* harmony import */ var _lib_dom_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./lib/dom.js */ "../../node_modules/lit-html/lib/dom.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "removeNodes", function() { return _lib_dom_js__WEBPACK_IMPORTED_MODULE_3__["removeNodes"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "reparentNodes", function() { return _lib_dom_js__WEBPACK_IMPORTED_MODULE_3__["reparentNodes"]; });

/* harmony import */ var _lib_part_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./lib/part.js */ "../../node_modules/lit-html/lib/part.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "noChange", function() { return _lib_part_js__WEBPACK_IMPORTED_MODULE_4__["noChange"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "nothing", function() { return _lib_part_js__WEBPACK_IMPORTED_MODULE_4__["nothing"]; });

/* harmony import */ var _lib_parts_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./lib/parts.js */ "../../node_modules/lit-html/lib/parts.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "AttributeCommitter", function() { return _lib_parts_js__WEBPACK_IMPORTED_MODULE_5__["AttributeCommitter"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "AttributePart", function() { return _lib_parts_js__WEBPACK_IMPORTED_MODULE_5__["AttributePart"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "BooleanAttributePart", function() { return _lib_parts_js__WEBPACK_IMPORTED_MODULE_5__["BooleanAttributePart"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "EventPart", function() { return _lib_parts_js__WEBPACK_IMPORTED_MODULE_5__["EventPart"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "isPrimitive", function() { return _lib_parts_js__WEBPACK_IMPORTED_MODULE_5__["isPrimitive"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "NodePart", function() { return _lib_parts_js__WEBPACK_IMPORTED_MODULE_5__["NodePart"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "PropertyCommitter", function() { return _lib_parts_js__WEBPACK_IMPORTED_MODULE_5__["PropertyCommitter"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "PropertyPart", function() { return _lib_parts_js__WEBPACK_IMPORTED_MODULE_5__["PropertyPart"]; });

/* harmony import */ var _lib_render_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./lib/render.js */ "../../node_modules/lit-html/lib/render.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "parts", function() { return _lib_render_js__WEBPACK_IMPORTED_MODULE_6__["parts"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _lib_render_js__WEBPACK_IMPORTED_MODULE_6__["render"]; });

/* harmony import */ var _lib_template_factory_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./lib/template-factory.js */ "../../node_modules/lit-html/lib/template-factory.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "templateCaches", function() { return _lib_template_factory_js__WEBPACK_IMPORTED_MODULE_7__["templateCaches"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "templateFactory", function() { return _lib_template_factory_js__WEBPACK_IMPORTED_MODULE_7__["templateFactory"]; });

/* harmony import */ var _lib_template_instance_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./lib/template-instance.js */ "../../node_modules/lit-html/lib/template-instance.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "TemplateInstance", function() { return _lib_template_instance_js__WEBPACK_IMPORTED_MODULE_8__["TemplateInstance"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "SVGTemplateResult", function() { return _lib_template_result_js__WEBPACK_IMPORTED_MODULE_1__["SVGTemplateResult"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "TemplateResult", function() { return _lib_template_result_js__WEBPACK_IMPORTED_MODULE_1__["TemplateResult"]; });

/* harmony import */ var _lib_template_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./lib/template.js */ "../../node_modules/lit-html/lib/template.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "createMarker", function() { return _lib_template_js__WEBPACK_IMPORTED_MODULE_9__["createMarker"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "isTemplatePartActive", function() { return _lib_template_js__WEBPACK_IMPORTED_MODULE_9__["isTemplatePartActive"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "Template", function() { return _lib_template_js__WEBPACK_IMPORTED_MODULE_9__["Template"]; });

/**
 * @license
 * Copyright (c) 2017 The Polymer Project Authors. All rights reserved.
 * This code may only be used under the BSD style license found at
 * http://polymer.github.io/LICENSE.txt
 * The complete set of authors may be found at
 * http://polymer.github.io/AUTHORS.txt
 * The complete set of contributors may be found at
 * http://polymer.github.io/CONTRIBUTORS.txt
 * Code distributed by Google as part of the polymer project is also
 * subject to an additional IP rights grant found at
 * http://polymer.github.io/PATENTS.txt
 */
/**
 *
 * Main lit-html module.
 *
 * Main exports:
 *
 * -  [[html]]
 * -  [[svg]]
 * -  [[render]]
 *
 * @module lit-html
 * @preferred
 */
/**
 * Do not remove this comment; it keeps typedoc from misplacing the module
 * docs.
 */




// TODO(justinfagnani): remove line when we get NodePart moving methods








/**
 * Interprets a template literal as an HTML template that can efficiently
 * render to and update a container.
 */
const html = (strings, ...values) => new _lib_template_result_js__WEBPACK_IMPORTED_MODULE_1__["TemplateResult"](strings, values, 'html', _lib_default_template_processor_js__WEBPACK_IMPORTED_MODULE_0__["defaultTemplateProcessor"]);
/**
 * Interprets a template literal as an SVG template that can efficiently
 * render to and update a container.
 */
const svg = (strings, ...values) => new _lib_template_result_js__WEBPACK_IMPORTED_MODULE_1__["SVGTemplateResult"](strings, values, 'svg', _lib_default_template_processor_js__WEBPACK_IMPORTED_MODULE_0__["defaultTemplateProcessor"]);


/***/ }),

/***/ "../../node_modules/raw-loader/index.js!./core/shaders/uberPBRShader.frag":
/*!**********************************************************************!*\
  !*** /app/node_modules/raw-loader!./core/shaders/uberPBRShader.frag ***!
  \**********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = "//#define PHYSICAL\n\nuniform vec3 diffuse;\nuniform vec3 emissive;\nuniform float roughness;\nuniform float metalness;\nuniform float opacity;\n\n#ifndef STANDARD\n\tuniform float clearCoat;\n\tuniform float clearCoatRoughness;\n#endif\n\nvarying vec3 vViewPosition;\n\n#ifndef FLAT_SHADED\n\tvarying vec3 vNormal;\n#endif\n\n#include <common>\n#include <packing>\n#include <dithering_pars_fragment>\n#include <color_pars_fragment>\n\n//#include <uv_pars_fragment>\n//#include <uv2_pars_fragment>\n// REPLACED WITH\n#if defined(USE_MAP) || defined(USE_BUMPMAP) || defined(USE_NORMALMAP) || defined(USE_SPECULARMAP) || defined(USE_ALPHAMAP) || defined(USE_EMISSIVEMAP) || defined(USE_ROUGHNESSMAP) || defined(USE_METALNESSMAP) || defined(USE_LIGHTMAP) || defined(USE_AOMAP)\n\tvarying vec2 vUv;\n#endif\n\n#include <map_pars_fragment>\n#include <alphamap_pars_fragment>\n#include <aomap_pars_fragment>\n#include <lightmap_pars_fragment>\n#include <emissivemap_pars_fragment>\n#include <bsdfs>\n#include <cube_uv_reflection_fragment>\n#include <envmap_pars_fragment>\n#include <envmap_physical_pars_fragment>\n#include <fog_pars_fragment>\n#include <lights_pars_begin>\n#include <lights_physical_pars_fragment>\n#include <shadowmap_pars_fragment>\n#include <bumpmap_pars_fragment>\n#include <normalmap_pars_fragment>\n#include <roughnessmap_pars_fragment>\n#include <metalnessmap_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\n#ifdef USE_AOMAP\n    uniform vec3 aoMapMix;\n#endif\n\n#ifdef MODE_XRAY\n    varying float vIntensity;\n#endif\n\n#ifdef CUT_PLANE\n    varying vec3 vWorldPosition;\n    uniform vec4 cutPlaneDirection;\n    uniform vec3 cutPlaneColor;\n#endif\n\nvoid main() {\n    #ifdef CUT_PLANE\n        if (dot(vWorldPosition, cutPlaneDirection.xyz) < cutPlaneDirection.w) {\n            discard;\n        }\n    #endif\n\n\t#include <clipping_planes_fragment>\n\n\tvec4 diffuseColor = vec4( diffuse, opacity );\n\tReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );\n\tvec3 totalEmissiveRadiance = emissive;\n\n\t#include <logdepthbuf_fragment>\n\t#include <map_fragment>\n\t#include <color_fragment>\n\t#include <alphamap_fragment>\n\t#include <alphatest_fragment>\n\t#include <roughnessmap_fragment>\n\t#include <metalnessmap_fragment>\n\t#include <normal_fragment_begin>\n    #include <normal_fragment_maps>\n\n\t#ifdef CUT_PLANE\n\t    // on the cut surface (back facing fragments revealed), replace normal with cut plane direction\n        if (!gl_FrontFacing) {\n            normal = -cutPlaneDirection.xyz;\n            diffuseColor.rgb = cutPlaneColor.rgb;\n        }\n\t#endif\n\n\t#include <emissivemap_fragment>\n\n\t// accumulation\n    #if defined(USE_LIGHTMAP) || defined(USE_AOMAP)\n        vec2 vUv2 = vUv;\n    #endif\n\n\t#include <lights_physical_fragment>\n\t#include <lights_fragment_begin>\n\t#include <lights_fragment_maps>\n\t#include <lights_fragment_end>\n\n\t// modulation\n\t//#include <aomap_fragment>\n\t// REPLACED WITH\n\t#ifdef USE_AOMAP\n\t    // if cut plane is enabled, disable ambient occlusion on back facing fragments\n\t    #ifdef CUT_PLANE\n            if (gl_FrontFacing) {\n\t    #endif\n\n    \t// reads channel R, compatible with a combined OcclusionRoughnessMetallic (RGB) texture\n    \tvec3 aoSample = texture2D(aoMap, vUv).rgb;\n    \tvec3 aoFactors = mix(vec3(1.0), aoSample, clamp(aoMapMix * aoMapIntensity, 0.0, 1.0));\n    \tfloat ambientOcclusion = aoFactors.x * aoFactors.y * aoFactors.z;\n    \tfloat ambientOcclusion2 = ambientOcclusion * ambientOcclusion;\n    \treflectedLight.directDiffuse *= ambientOcclusion2;\n    \treflectedLight.directSpecular *= ambientOcclusion;\n    \t//reflectedLight.indirectDiffuse *= ambientOcclusion;\n\n    \t#if defined(USE_ENVMAP) && defined(PHYSICAL)\n    \t\tfloat dotNV = saturate(dot(geometry.normal, geometry.viewDir));\n    \t\treflectedLight.indirectSpecular *= computeSpecularOcclusion(dotNV, ambientOcclusion, material.specularRoughness);\n    \t#endif\n\n    \t#ifdef CUT_PLANE\n    \t    }\n    \t#endif\n    #endif\n\n\tvec3 outgoingLight = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse + reflectedLight.directSpecular + reflectedLight.indirectSpecular + totalEmissiveRadiance;\n\n\tgl_FragColor = vec4(outgoingLight, diffuseColor.a);\n\n\t#include <tonemapping_fragment>\n\t#include <encodings_fragment>\n\t#include <fog_fragment>\n\t#include <premultiplied_alpha_fragment>\n\t#include <dithering_fragment>\n\n    #ifdef MODE_NORMALS\n        gl_FragColor = vec4(vec3(normal * 0.5 + 0.5), 1.0);\n    #endif\n\n    #ifdef MODE_XRAY\n        gl_FragColor = vec4(vec3(0.4, 0.7, 1.0) * vIntensity, 1.0);\n    #endif\n}\n"

/***/ }),

/***/ "../../node_modules/raw-loader/index.js!./core/shaders/uberPBRShader.vert":
/*!**********************************************************************!*\
  !*** /app/node_modules/raw-loader!./core/shaders/uberPBRShader.vert ***!
  \**********************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = "//#define PHYSICAL\n\nvarying vec3 vViewPosition;\n\n#ifndef FLAT_SHADED\n\tvarying vec3 vNormal;\n#endif\n\n#include <common>\n\n//#include <uv_pars_vertex>\n//#include <uv2_pars_vertex>\n// REPLACED WITH\n#if defined(USE_MAP) || defined(USE_BUMPMAP) || defined(USE_NORMALMAP) || defined(USE_SPECULARMAP) || defined(USE_ALPHAMAP) || defined(USE_EMISSIVEMAP) || defined(USE_ROUGHNESSMAP) || defined(USE_METALNESSMAP) || defined(USE_LIGHTMAP) || defined(USE_AOMAP)\n\tvarying vec2 vUv;\n\tuniform mat3 uvTransform;\n#endif\n\n#include <displacementmap_pars_vertex>\n#include <color_pars_vertex>\n#include <fog_pars_vertex>\n#include <morphtarget_pars_vertex>\n#include <skinning_pars_vertex>\n#include <shadowmap_pars_vertex>\n#include <logdepthbuf_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\n#ifdef MODE_XRAY\n    varying float vIntensity;\n#endif\n\n#ifdef CUT_PLANE\n    varying vec3 vWorldPosition;\n#endif\n\nvoid main() {\n\n//\t#include <uv_vertex>\n//\t#include <uv2_vertex>\n//  REPLACED WITH\n#if defined(USE_MAP) || defined(USE_BUMPMAP) || defined(USE_NORMALMAP) || defined(USE_SPECULARMAP) || defined(USE_ALPHAMAP) || defined(USE_EMISSIVEMAP) || defined(USE_ROUGHNESSMAP) || defined(USE_METALNESSMAP) || defined(USE_LIGHTMAP) || defined(USE_AOMAP)\n\tvUv = (uvTransform * vec3(uv, 1)).xy;\n#endif\n\n\t#include <color_vertex>\n\n\t#include <beginnormal_vertex>\n\t#include <morphnormal_vertex>\n\t#include <skinbase_vertex>\n\t#include <skinnormal_vertex>\n\t#include <defaultnormal_vertex>\n\n#ifndef FLAT_SHADED // Normal computed with derivatives when FLAT_SHADED\n\tvNormal = normalize(transformedNormal);\n#endif\n\n#ifdef MODE_XRAY\n    vIntensity = pow(abs(1.0 - abs(dot(vNormal, vec3(0.0, 0.0, 1.0)))), 3.0);\n#endif\n\n\t#include <begin_vertex>\n\t#include <morphtarget_vertex>\n\t#include <skinning_vertex>\n\t#include <displacementmap_vertex>\n\t#include <project_vertex>\n\t#include <logdepthbuf_vertex>\n\t#include <clipping_planes_vertex>\n\n\tvViewPosition = -mvPosition.xyz;\n\n\t// #include <worldpos_vertex>\n\t// REPLACED WITH\n\t#if defined(USE_ENVMAP) || defined(DISTANCE) || defined(USE_SHADOWMAP) || defined(CUT_PLANE)\n    \tvec4 worldPosition = modelMatrix * vec4( transformed, 1.0 );\n    #endif\n\n\t#include <shadowmap_vertex>\n\t#include <fog_vertex>\n\n#ifdef CUT_PLANE\n    vWorldPosition = worldPosition.xyz / worldPosition.w;\n#endif\n\n#ifdef MODE_NORMALS\n    vNormal = normal;\n#endif\n}\n"

/***/ }),

/***/ "../../node_modules/resolve-pathname/esm/resolve-pathname.js":
/*!******************************************************************!*\
  !*** /app/node_modules/resolve-pathname/esm/resolve-pathname.js ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function isAbsolute(pathname) {
  return pathname.charAt(0) === '/';
}

// About 1.5x faster than the two-arg version of Array#splice()
function spliceOne(list, index) {
  for (var i = index, k = i + 1, n = list.length; k < n; i += 1, k += 1) {
    list[i] = list[k];
  }

  list.pop();
}

// This implementation is based heavily on node's url.parse
function resolvePathname(to, from) {
  if (from === undefined) from = '';

  var toParts = (to && to.split('/')) || [];
  var fromParts = (from && from.split('/')) || [];

  var isToAbs = to && isAbsolute(to);
  var isFromAbs = from && isAbsolute(from);
  var mustEndAbs = isToAbs || isFromAbs;

  if (to && isAbsolute(to)) {
    // to is absolute
    fromParts = toParts;
  } else if (toParts.length) {
    // to is relative, drop the filename
    fromParts.pop();
    fromParts = fromParts.concat(toParts);
  }

  if (!fromParts.length) return '/';

  var hasTrailingSlash;
  if (fromParts.length) {
    var last = fromParts[fromParts.length - 1];
    hasTrailingSlash = last === '.' || last === '..' || last === '';
  } else {
    hasTrailingSlash = false;
  }

  var up = 0;
  for (var i = fromParts.length; i >= 0; i--) {
    var part = fromParts[i];

    if (part === '.') {
      spliceOne(fromParts, i);
    } else if (part === '..') {
      spliceOne(fromParts, i);
      up++;
    } else if (up) {
      spliceOne(fromParts, i);
      up--;
    }
  }

  if (!mustEndAbs) for (; up--; up) fromParts.unshift('..');

  if (
    mustEndAbs &&
    fromParts[0] !== '' &&
    (!fromParts[0] || !isAbsolute(fromParts[0]))
  )
    fromParts.unshift('');

  var result = fromParts.join('/');

  if (hasTrailingSlash && result.substr(-1) !== '/') result += '/';

  return result;
}

/* harmony default export */ __webpack_exports__["default"] = (resolvePathname);


/***/ }),

/***/ "../../node_modules/three/examples/js/loaders/DRACOLoader.js":
/*!******************************************************************!*\
  !*** /app/node_modules/three/examples/js/loaders/DRACOLoader.js ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
// Copyright 2016 The Draco Authors.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//


/**
 * @param {THREE.LoadingManager} manager
 */
THREE.DRACOLoader = function(manager) {
    this.timeLoaded = 0;
    this.manager = manager || THREE.DefaultLoadingManager;
    this.materials = null;
    this.verbosity = 0;
    this.attributeOptions = {};
    this.drawMode = THREE.TrianglesDrawMode;
    // Native Draco attribute type to Three.JS attribute type.
    this.nativeAttributeMap = {
      'position' : 'POSITION',
      'normal' : 'NORMAL',
      'color' : 'COLOR',
      'uv' : 'TEX_COORD'
    };
};

THREE.DRACOLoader.prototype = {

    constructor: THREE.DRACOLoader,

    load: function(url, onLoad, onProgress, onError) {
        var scope = this;
        var loader = new THREE.FileLoader(scope.manager);
        loader.setPath(this.path);
        loader.setResponseType('arraybuffer');
        loader.load(url, function(blob) {
            scope.decodeDracoFile(blob, onLoad);
        }, onProgress, onError);
    },

    setPath: function(value) {
        this.path = value;
        return this;
    },

    setVerbosity: function(level) {
        this.verbosity = level;
        return this;
    },

    /**
     *  Sets desired mode for generated geometry indices.
     *  Can be either:
     *      THREE.TrianglesDrawMode
     *      THREE.TriangleStripDrawMode
     */
    setDrawMode: function(drawMode) {
        this.drawMode = drawMode;
        return this;
    },

    /**
     * Skips dequantization for a specific attribute.
     * |attributeName| is the THREE.js name of the given attribute type.
     * The only currently supported |attributeName| is 'position', more may be
     * added in future.
     */
    setSkipDequantization: function(attributeName, skip) {
        var skipDequantization = true;
        if (typeof skip !== 'undefined')
          skipDequantization = skip;
        this.getAttributeOptions(attributeName).skipDequantization =
            skipDequantization;
        return this;
    },

    /**
     * Decompresses a Draco buffer. Names of attributes (for ID and type maps)
     * must be one of the supported three.js types, including: position, color,
     * normal, uv, uv2, skinIndex, skinWeight.
     *
     * @param {ArrayBuffer} rawBuffer
     * @param {Function} callback
     * @param {Object|undefined} attributeUniqueIdMap Provides a pre-defined ID
     *     for each attribute in the geometry to be decoded. If given,
     *     `attributeTypeMap` is required and `nativeAttributeMap` will be
     *     ignored.
     * @param {Object|undefined} attributeTypeMap Provides a predefined data
     *     type (as a typed array constructor) for each attribute in the
     *     geometry to be decoded.
     */
    decodeDracoFile: function(rawBuffer, callback, attributeUniqueIdMap,
                              attributeTypeMap) {
      var scope = this;
      THREE.DRACOLoader.getDecoderModule()
          .then( function ( module ) {
            scope.decodeDracoFileInternal( rawBuffer, module.decoder, callback,
              attributeUniqueIdMap, attributeTypeMap);
          });
    },

    decodeDracoFileInternal: function(rawBuffer, dracoDecoder, callback,
                                      attributeUniqueIdMap, attributeTypeMap) {
      /*
       * Here is how to use Draco Javascript decoder and get the geometry.
       */
      var buffer = new dracoDecoder.DecoderBuffer();
      buffer.Init(new Int8Array(rawBuffer), rawBuffer.byteLength);
      var decoder = new dracoDecoder.Decoder();

      /*
       * Determine what type is this file: mesh or point cloud.
       */
      var geometryType = decoder.GetEncodedGeometryType(buffer);
      if (geometryType == dracoDecoder.TRIANGULAR_MESH) {
        if (this.verbosity > 0) {
          console.log('Loaded a mesh.');
        }
      } else if (geometryType == dracoDecoder.POINT_CLOUD) {
        if (this.verbosity > 0) {
          console.log('Loaded a point cloud.');
        }
      } else {
        var errorMsg = 'THREE.DRACOLoader: Unknown geometry type.';
        console.error(errorMsg);
        throw new Error(errorMsg);
      }
      callback(this.convertDracoGeometryTo3JS(dracoDecoder, decoder,
          geometryType, buffer, attributeUniqueIdMap, attributeTypeMap));
    },

    addAttributeToGeometry: function(dracoDecoder, decoder, dracoGeometry,
                                     attributeName, attributeType, attribute,
                                     geometry, geometryBuffer) {
      if (attribute.ptr === 0) {
        var errorMsg = 'THREE.DRACOLoader: No attribute ' + attributeName;
        console.error(errorMsg);
        throw new Error(errorMsg);
      }

      var numComponents = attribute.num_components();
      var numPoints = dracoGeometry.num_points();
      var numValues = numPoints * numComponents;
      var attributeData;
      var TypedBufferAttribute;

      switch ( attributeType ) {

        case Float32Array:
          attributeData = new dracoDecoder.DracoFloat32Array();
          decoder.GetAttributeFloatForAllPoints(
            dracoGeometry, attribute, attributeData);
          geometryBuffer[ attributeName ] = new Float32Array( numValues );
          TypedBufferAttribute = THREE.Float32BufferAttribute;
          break;

        case Int8Array:
          attributeData = new dracoDecoder.DracoInt8Array();
          decoder.GetAttributeInt8ForAllPoints(
            dracoGeometry, attribute, attributeData );
          geometryBuffer[ attributeName ] = new Int8Array( numValues );
          TypedBufferAttribute = THREE.Int8BufferAttribute;
          break;

        case Int16Array:
          attributeData = new dracoDecoder.DracoInt16Array();
          decoder.GetAttributeInt16ForAllPoints(
            dracoGeometry, attribute, attributeData);
          geometryBuffer[ attributeName ] = new Int16Array( numValues );
          TypedBufferAttribute = THREE.Int16BufferAttribute;
          break;

        case Int32Array:
          attributeData = new dracoDecoder.DracoInt32Array();
          decoder.GetAttributeInt32ForAllPoints(
            dracoGeometry, attribute, attributeData);
          geometryBuffer[ attributeName ] = new Int32Array( numValues );
          TypedBufferAttribute = THREE.Int32BufferAttribute;
          break;

        case Uint8Array:
          attributeData = new dracoDecoder.DracoUInt8Array();
          decoder.GetAttributeUInt8ForAllPoints(
            dracoGeometry, attribute, attributeData);
          geometryBuffer[ attributeName ] = new Uint8Array( numValues );
          TypedBufferAttribute = THREE.Uint8BufferAttribute;
          break;

        case Uint16Array:
          attributeData = new dracoDecoder.DracoUInt16Array();
          decoder.GetAttributeUInt16ForAllPoints(
            dracoGeometry, attribute, attributeData);
          geometryBuffer[ attributeName ] = new Uint16Array( numValues );
          TypedBufferAttribute = THREE.Uint16BufferAttribute;
          break;

        case Uint32Array:
          attributeData = new dracoDecoder.DracoUInt32Array();
          decoder.GetAttributeUInt32ForAllPoints(
            dracoGeometry, attribute, attributeData);
          geometryBuffer[ attributeName ] = new Uint32Array( numValues );
          TypedBufferAttribute = THREE.Uint32BufferAttribute;
          break;

        default:
          var errorMsg = 'THREE.DRACOLoader: Unexpected attribute type.';
          console.error( errorMsg );
          throw new Error( errorMsg );

      }

      // Copy data from decoder.
      for (var i = 0; i < numValues; i++) {
        geometryBuffer[attributeName][i] = attributeData.GetValue(i);
      }
      // Add attribute to THREEJS geometry for rendering.
      geometry.addAttribute(attributeName,
          new TypedBufferAttribute(geometryBuffer[attributeName],
            numComponents));
      dracoDecoder.destroy(attributeData);
    },

    convertDracoGeometryTo3JS: function(dracoDecoder, decoder, geometryType,
                                        buffer, attributeUniqueIdMap,
                                        attributeTypeMap) {
        // TODO: Should not assume native Draco attribute IDs apply.
        if (this.getAttributeOptions('position').skipDequantization === true) {
          decoder.SkipAttributeTransform(dracoDecoder.POSITION);
        }
        var dracoGeometry;
        var decodingStatus;
        var start_time = performance.now();
        if (geometryType === dracoDecoder.TRIANGULAR_MESH) {
          dracoGeometry = new dracoDecoder.Mesh();
          decodingStatus = decoder.DecodeBufferToMesh(buffer, dracoGeometry);
        } else {
          dracoGeometry = new dracoDecoder.PointCloud();
          decodingStatus =
              decoder.DecodeBufferToPointCloud(buffer, dracoGeometry);
        }
        if (!decodingStatus.ok() || dracoGeometry.ptr == 0) {
          var errorMsg = 'THREE.DRACOLoader: Decoding failed: ';
          errorMsg += decodingStatus.error_msg();
          console.error(errorMsg);
          dracoDecoder.destroy(decoder);
          dracoDecoder.destroy(dracoGeometry);
          throw new Error(errorMsg);
        }

        var decode_end = performance.now();
        dracoDecoder.destroy(buffer);
        /*
         * Example on how to retrieve mesh and attributes.
         */
        var numFaces;
        if (geometryType == dracoDecoder.TRIANGULAR_MESH) {
          numFaces = dracoGeometry.num_faces();
          if (this.verbosity > 0) {
            console.log('Number of faces loaded: ' + numFaces.toString());
          }
        } else {
          numFaces = 0;
        }

        var numPoints = dracoGeometry.num_points();
        var numAttributes = dracoGeometry.num_attributes();
        if (this.verbosity > 0) {
          console.log('Number of points loaded: ' + numPoints.toString());
          console.log('Number of attributes loaded: ' +
              numAttributes.toString());
        }

        // Verify if there is position attribute.
        // TODO: Should not assume native Draco attribute IDs apply.
        var posAttId = decoder.GetAttributeId(dracoGeometry,
                                              dracoDecoder.POSITION);
        if (posAttId == -1) {
          var errorMsg = 'THREE.DRACOLoader: No position attribute found.';
          console.error(errorMsg);
          dracoDecoder.destroy(decoder);
          dracoDecoder.destroy(dracoGeometry);
          throw new Error(errorMsg);
        }
        var posAttribute = decoder.GetAttribute(dracoGeometry, posAttId);

        // Structure for converting to THREEJS geometry later.
        var geometryBuffer = {};
        // Import data to Three JS geometry.
        var geometry = new THREE.BufferGeometry();

        // Do not use both the native attribute map and a provided (e.g. glTF) map.
        if ( attributeUniqueIdMap ) {

          // Add attributes of user specified unique id. E.g. GLTF models.
          for (var attributeName in attributeUniqueIdMap) {
            var attributeType = attributeTypeMap[attributeName];
            var attributeId = attributeUniqueIdMap[attributeName];
            var attribute = decoder.GetAttributeByUniqueId(dracoGeometry,
                                                           attributeId);
            this.addAttributeToGeometry(dracoDecoder, decoder, dracoGeometry,
                attributeName, attributeType, attribute, geometry, geometryBuffer);
          }

        } else {

          // Add native Draco attribute type to geometry.
          for (var attributeName in this.nativeAttributeMap) {
            var attId = decoder.GetAttributeId(dracoGeometry,
                dracoDecoder[this.nativeAttributeMap[attributeName]]);
            if (attId !== -1) {
              if (this.verbosity > 0) {
                console.log('Loaded ' + attributeName + ' attribute.');
              }
              var attribute = decoder.GetAttribute(dracoGeometry, attId);
              this.addAttributeToGeometry(dracoDecoder, decoder, dracoGeometry,
                  attributeName, Float32Array, attribute, geometry, geometryBuffer);
            }
          }

        }

        // For mesh, we need to generate the faces.
        if (geometryType == dracoDecoder.TRIANGULAR_MESH) {
          if (this.drawMode === THREE.TriangleStripDrawMode) {
            var stripsArray = new dracoDecoder.DracoInt32Array();
            var numStrips = decoder.GetTriangleStripsFromMesh(
                dracoGeometry, stripsArray);
            geometryBuffer.indices = new Uint32Array(stripsArray.size());
            for (var i = 0; i < stripsArray.size(); ++i) {
              geometryBuffer.indices[i] = stripsArray.GetValue(i);
            }
            dracoDecoder.destroy(stripsArray);
          } else {
            var numIndices = numFaces * 3;
            geometryBuffer.indices = new Uint32Array(numIndices);
            var ia = new dracoDecoder.DracoInt32Array();
            for (var i = 0; i < numFaces; ++i) {
              decoder.GetFaceFromMesh(dracoGeometry, i, ia);
              var index = i * 3;
              geometryBuffer.indices[index] = ia.GetValue(0);
              geometryBuffer.indices[index + 1] = ia.GetValue(1);
              geometryBuffer.indices[index + 2] = ia.GetValue(2);
            }
            dracoDecoder.destroy(ia);
         }
        }

        geometry.drawMode = this.drawMode;
        if (geometryType == dracoDecoder.TRIANGULAR_MESH) {
          geometry.setIndex(new(geometryBuffer.indices.length > 65535 ?
                THREE.Uint32BufferAttribute : THREE.Uint16BufferAttribute)
              (geometryBuffer.indices, 1));
        }

        // TODO: Should not assume native Draco attribute IDs apply.
        // TODO: Can other attribute types be quantized?
        var posTransform = new dracoDecoder.AttributeQuantizationTransform();
        if (posTransform.InitFromAttribute(posAttribute)) {
          // Quantized attribute. Store the quantization parameters into the
          // THREE.js attribute.
          geometry.attributes['position'].isQuantized = true;
          geometry.attributes['position'].maxRange = posTransform.range();
          geometry.attributes['position'].numQuantizationBits =
              posTransform.quantization_bits();
          geometry.attributes['position'].minValues = new Float32Array(3);
          for (var i = 0; i < 3; ++i) {
            geometry.attributes['position'].minValues[i] =
                posTransform.min_value(i);
          }
        }
        dracoDecoder.destroy(posTransform);
        dracoDecoder.destroy(decoder);
        dracoDecoder.destroy(dracoGeometry);

        this.decode_time = decode_end - start_time;
        this.import_time = performance.now() - decode_end;

        if (this.verbosity > 0) {
          console.log('Decode time: ' + this.decode_time);
          console.log('Import time: ' + this.import_time);
        }
        return geometry;
    },

    isVersionSupported: function(version, callback) {
        THREE.DRACOLoader.getDecoderModule()
            .then( function ( module ) {
              callback( module.decoder.isVersionSupported( version ) );
            });
    },

    getAttributeOptions: function(attributeName) {
        if (typeof this.attributeOptions[attributeName] === 'undefined')
          this.attributeOptions[attributeName] = {};
        return this.attributeOptions[attributeName];
    }
};

THREE.DRACOLoader.decoderPath = './';
THREE.DRACOLoader.decoderConfig = {};
THREE.DRACOLoader.decoderModulePromise = null;

/**
 * Sets the base path for decoder source files.
 * @param {string} path
 */
THREE.DRACOLoader.setDecoderPath = function ( path ) {
  THREE.DRACOLoader.decoderPath = path;
};

/**
 * Sets decoder configuration and releases singleton decoder module. Module
 * will be recreated with the next decoding call.
 * @param {Object} config
 */
THREE.DRACOLoader.setDecoderConfig = function ( config ) {
  var wasmBinary = THREE.DRACOLoader.decoderConfig.wasmBinary;
  THREE.DRACOLoader.decoderConfig = config || {};
  THREE.DRACOLoader.releaseDecoderModule();

  // Reuse WASM binary.
  if ( wasmBinary ) THREE.DRACOLoader.decoderConfig.wasmBinary = wasmBinary;
};

/**
 * Releases the singleton DracoDecoderModule instance. Module will be recreated
 * with the next decoding call.
 */
THREE.DRACOLoader.releaseDecoderModule = function () {
  THREE.DRACOLoader.decoderModulePromise = null;
};

/**
 * Gets WebAssembly or asm.js singleton instance of DracoDecoderModule
 * after testing for browser support. Returns Promise that resolves when
 * module is available.
 * @return {Promise<{decoder: DracoDecoderModule}>}
 */
THREE.DRACOLoader.getDecoderModule = function () {
  var scope = this;
  var path = THREE.DRACOLoader.decoderPath;
  var config = THREE.DRACOLoader.decoderConfig;
  var promise = THREE.DRACOLoader.decoderModulePromise;

  if ( promise ) return promise;

  // Load source files.
  if ( typeof DracoDecoderModule !== 'undefined' ) {
    // Loaded externally.
    promise = Promise.resolve();
  } else if ( typeof WebAssembly !== 'object' || config.type === 'js' ) {
    // Load with asm.js.
    promise = THREE.DRACOLoader._loadScript( path + 'draco_decoder.js' );
  } else {
    // Load with WebAssembly.
    config.wasmBinaryFile = path + 'draco_decoder.wasm';
    promise = THREE.DRACOLoader._loadScript( path + 'draco_wasm_wrapper.js' )
        .then( function () {
          return THREE.DRACOLoader._loadArrayBuffer( config.wasmBinaryFile );
        } )
        .then( function ( wasmBinary ) {
          config.wasmBinary = wasmBinary;
        } );
  }

  // Wait for source files, then create and return a decoder.
  promise = promise.then( function () {
    return new Promise( function ( resolve ) {
      config.onModuleLoaded = function ( decoder ) {
        scope.timeLoaded = performance.now();
        // Module is Promise-like. Wrap before resolving to avoid loop.
        resolve( { decoder: decoder } );
      };
      DracoDecoderModule( config );
    } );
  } );

  THREE.DRACOLoader.decoderModulePromise = promise;
  return promise;
};

/**
 * @param {string} src
 * @return {Promise}
 */
THREE.DRACOLoader._loadScript = function ( src ) {
  var prevScript = document.getElementById( 'decoder_script' );
  if ( prevScript !== null ) {
    prevScript.parentNode.removeChild( prevScript );
  }
  var head = document.getElementsByTagName( 'head' )[ 0 ];
  var script = document.createElement( 'script' );
  script.id = 'decoder_script';
  script.type = 'text/javascript';
  script.src = src;
  return new Promise( function ( resolve ) {
    script.onload = resolve;
    head.appendChild( script );
  });
};

/**
 * @param {string} src
 * @return {Promise}
 */
THREE.DRACOLoader._loadArrayBuffer = function ( src ) {
  var loader = new THREE.FileLoader();
  loader.setResponseType( 'arraybuffer' );
  return new Promise( function( resolve, reject ) {
    loader.load( src, resolve, undefined, reject );
  });
};


/***/ }),

/***/ "../../node_modules/three/examples/js/loaders/GLTFLoader.js":
/*!*****************************************************************!*\
  !*** /app/node_modules/three/examples/js/loaders/GLTFLoader.js ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * @author Rich Tibbett / https://github.com/richtr
 * @author mrdoob / http://mrdoob.com/
 * @author Tony Parisi / http://www.tonyparisi.com/
 * @author Takahiro / https://github.com/takahirox
 * @author Don McCurdy / https://www.donmccurdy.com
 */

THREE.GLTFLoader = ( function () {

	function GLTFLoader( manager ) {

		this.manager = ( manager !== undefined ) ? manager : THREE.DefaultLoadingManager;
		this.dracoLoader = null;

	}

	GLTFLoader.prototype = {

		constructor: GLTFLoader,

		crossOrigin: 'anonymous',

		load: function ( url, onLoad, onProgress, onError ) {

			var scope = this;

			var resourcePath;

			if ( this.resourcePath !== undefined ) {

				resourcePath = this.resourcePath;

			} else if ( this.path !== undefined ) {

				resourcePath = this.path;

			} else {

				resourcePath = THREE.LoaderUtils.extractUrlBase( url );

			}

			// Tells the LoadingManager to track an extra item, which resolves after
			// the model is fully loaded. This means the count of items loaded will
			// be incorrect, but ensures manager.onLoad() does not fire early.
			scope.manager.itemStart( url );

			var _onError = function ( e ) {

				if ( onError ) {

					onError( e );

				} else {

					console.error( e );

				}

				scope.manager.itemError( url );
				scope.manager.itemEnd( url );

			};

			var loader = new THREE.FileLoader( scope.manager );

			loader.setPath( this.path );
			loader.setResponseType( 'arraybuffer' );

			loader.load( url, function ( data ) {

				try {

					scope.parse( data, resourcePath, function ( gltf ) {

						onLoad( gltf );

						scope.manager.itemEnd( url );

					}, _onError );

				} catch ( e ) {

					_onError( e );

				}

			}, onProgress, _onError );

		},

		setCrossOrigin: function ( value ) {

			this.crossOrigin = value;
			return this;

		},

		setPath: function ( value ) {

			this.path = value;
			return this;

		},

		setResourcePath: function ( value ) {

			this.resourcePath = value;
			return this;

		},

		setDRACOLoader: function ( dracoLoader ) {

			this.dracoLoader = dracoLoader;
			return this;

		},

		parse: function ( data, path, onLoad, onError ) {

			var content;
			var extensions = {};

			if ( typeof data === 'string' ) {

				content = data;

			} else {

				var magic = THREE.LoaderUtils.decodeText( new Uint8Array( data, 0, 4 ) );

				if ( magic === BINARY_EXTENSION_HEADER_MAGIC ) {

					try {

						extensions[ EXTENSIONS.KHR_BINARY_GLTF ] = new GLTFBinaryExtension( data );

					} catch ( error ) {

						if ( onError ) onError( error );
						return;

					}

					content = extensions[ EXTENSIONS.KHR_BINARY_GLTF ].content;

				} else {

					content = THREE.LoaderUtils.decodeText( new Uint8Array( data ) );

				}

			}

			var json = JSON.parse( content );

			if ( json.asset === undefined || json.asset.version[ 0 ] < 2 ) {

				if ( onError ) onError( new Error( 'THREE.GLTFLoader: Unsupported asset. glTF versions >=2.0 are supported. Use LegacyGLTFLoader instead.' ) );
				return;

			}

			if ( json.extensionsUsed ) {

				for ( var i = 0; i < json.extensionsUsed.length; ++ i ) {

					var extensionName = json.extensionsUsed[ i ];
					var extensionsRequired = json.extensionsRequired || [];

					switch ( extensionName ) {

						case EXTENSIONS.KHR_LIGHTS_PUNCTUAL:
							extensions[ extensionName ] = new GLTFLightsExtension( json );
							break;

						case EXTENSIONS.KHR_MATERIALS_UNLIT:
							extensions[ extensionName ] = new GLTFMaterialsUnlitExtension( json );
							break;

						case EXTENSIONS.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS:
							extensions[ extensionName ] = new GLTFMaterialsPbrSpecularGlossinessExtension( json );
							break;

						case EXTENSIONS.KHR_DRACO_MESH_COMPRESSION:
							extensions[ extensionName ] = new GLTFDracoMeshCompressionExtension( json, this.dracoLoader );
							break;

						case EXTENSIONS.MSFT_TEXTURE_DDS:
							extensions[ EXTENSIONS.MSFT_TEXTURE_DDS ] = new GLTFTextureDDSExtension( json );
							break;

						case EXTENSIONS.KHR_TEXTURE_TRANSFORM:
							extensions[ EXTENSIONS.KHR_TEXTURE_TRANSFORM ] = new GLTFTextureTransformExtension( json );
							break;

						default:

							if ( extensionsRequired.indexOf( extensionName ) >= 0 ) {

								console.warn( 'THREE.GLTFLoader: Unknown extension "' + extensionName + '".' );

							}

					}

				}

			}

			var parser = new GLTFParser( json, extensions, {

				path: path || this.resourcePath || '',
				crossOrigin: this.crossOrigin,
				manager: this.manager

			} );

			parser.parse( function ( scene, scenes, cameras, animations, json ) {

				var glTF = {
					scene: scene,
					scenes: scenes,
					cameras: cameras,
					animations: animations,
					asset: json.asset,
					parser: parser,
					userData: {}
				};

				addUnknownExtensionsToUserData( extensions, glTF, json );

				onLoad( glTF );

			}, onError );

		}

	};

	/* GLTFREGISTRY */

	function GLTFRegistry() {

		var objects = {};

		return	{

			get: function ( key ) {

				return objects[ key ];

			},

			add: function ( key, object ) {

				objects[ key ] = object;

			},

			remove: function ( key ) {

				delete objects[ key ];

			},

			removeAll: function () {

				objects = {};

			}

		};

	}

	/*********************************/
	/********** EXTENSIONS ***********/
	/*********************************/

	var EXTENSIONS = {
		KHR_BINARY_GLTF: 'KHR_binary_glTF',
		KHR_DRACO_MESH_COMPRESSION: 'KHR_draco_mesh_compression',
		KHR_LIGHTS_PUNCTUAL: 'KHR_lights_punctual',
		KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS: 'KHR_materials_pbrSpecularGlossiness',
		KHR_MATERIALS_UNLIT: 'KHR_materials_unlit',
		KHR_TEXTURE_TRANSFORM: 'KHR_texture_transform',
		MSFT_TEXTURE_DDS: 'MSFT_texture_dds'
	};

	/**
	 * DDS Texture Extension
	 *
	 * Specification:
	 * https://github.com/KhronosGroup/glTF/tree/master/extensions/2.0/Vendor/MSFT_texture_dds
	 *
	 */
	function GLTFTextureDDSExtension() {

		if ( ! THREE.DDSLoader ) {

			throw new Error( 'THREE.GLTFLoader: Attempting to load .dds texture without importing THREE.DDSLoader' );

		}

		this.name = EXTENSIONS.MSFT_TEXTURE_DDS;
		this.ddsLoader = new THREE.DDSLoader();

	}

	/**
	 * Lights Extension
	 *
	 * Specification: PENDING
	 */
	function GLTFLightsExtension( json ) {

		this.name = EXTENSIONS.KHR_LIGHTS_PUNCTUAL;

		var extension = ( json.extensions && json.extensions[ EXTENSIONS.KHR_LIGHTS_PUNCTUAL ] ) || {};
		this.lightDefs = extension.lights || [];

	}

	GLTFLightsExtension.prototype.loadLight = function ( lightIndex ) {

		var lightDef = this.lightDefs[ lightIndex ];
		var lightNode;

		var color = new THREE.Color( 0xffffff );
		if ( lightDef.color !== undefined ) color.fromArray( lightDef.color );

		var range = lightDef.range !== undefined ? lightDef.range : 0;

		switch ( lightDef.type ) {

			case 'directional':
				lightNode = new THREE.DirectionalLight( color );
				lightNode.target.position.set( 0, 0, -1 );
				lightNode.add( lightNode.target );
				break;

			case 'point':
				lightNode = new THREE.PointLight( color );
				lightNode.distance = range;
				break;

			case 'spot':
				lightNode = new THREE.SpotLight( color );
				lightNode.distance = range;
				// Handle spotlight properties.
				lightDef.spot = lightDef.spot || {};
				lightDef.spot.innerConeAngle = lightDef.spot.innerConeAngle !== undefined ? lightDef.spot.innerConeAngle : 0;
				lightDef.spot.outerConeAngle = lightDef.spot.outerConeAngle !== undefined ? lightDef.spot.outerConeAngle : Math.PI / 4.0;
				lightNode.angle = lightDef.spot.outerConeAngle;
				lightNode.penumbra = 1.0 - lightDef.spot.innerConeAngle / lightDef.spot.outerConeAngle;
				lightNode.target.position.set( 0, 0, -1 );
				lightNode.add( lightNode.target );
				break;

			default:
				throw new Error( 'THREE.GLTFLoader: Unexpected light type, "' + lightDef.type + '".' );

		}

		lightNode.decay = 2;

		if ( lightDef.intensity !== undefined ) lightNode.intensity = lightDef.intensity;

		lightNode.name = lightDef.name || ( 'light_' + lightIndex );

		return Promise.resolve( lightNode );

	};

	/**
	 * Unlit Materials Extension (pending)
	 *
	 * PR: https://github.com/KhronosGroup/glTF/pull/1163
	 */
	function GLTFMaterialsUnlitExtension( json ) {

		this.name = EXTENSIONS.KHR_MATERIALS_UNLIT;

	}

	GLTFMaterialsUnlitExtension.prototype.getMaterialType = function ( material ) {

		return THREE.MeshBasicMaterial;

	};

	GLTFMaterialsUnlitExtension.prototype.extendParams = function ( materialParams, material, parser ) {

		var pending = [];

		materialParams.color = new THREE.Color( 1.0, 1.0, 1.0 );
		materialParams.opacity = 1.0;

		var metallicRoughness = material.pbrMetallicRoughness;

		if ( metallicRoughness ) {

			if ( Array.isArray( metallicRoughness.baseColorFactor ) ) {

				var array = metallicRoughness.baseColorFactor;

				materialParams.color.fromArray( array );
				materialParams.opacity = array[ 3 ];

			}

			if ( metallicRoughness.baseColorTexture !== undefined ) {

				pending.push( parser.assignTexture( materialParams, 'map', metallicRoughness.baseColorTexture ) );

			}

		}

		return Promise.all( pending );

	};

	/* BINARY EXTENSION */

	var BINARY_EXTENSION_BUFFER_NAME = 'binary_glTF';
	var BINARY_EXTENSION_HEADER_MAGIC = 'glTF';
	var BINARY_EXTENSION_HEADER_LENGTH = 12;
	var BINARY_EXTENSION_CHUNK_TYPES = { JSON: 0x4E4F534A, BIN: 0x004E4942 };

	function GLTFBinaryExtension( data ) {

		this.name = EXTENSIONS.KHR_BINARY_GLTF;
		this.content = null;
		this.body = null;

		var headerView = new DataView( data, 0, BINARY_EXTENSION_HEADER_LENGTH );

		this.header = {
			magic: THREE.LoaderUtils.decodeText( new Uint8Array( data.slice( 0, 4 ) ) ),
			version: headerView.getUint32( 4, true ),
			length: headerView.getUint32( 8, true )
		};

		if ( this.header.magic !== BINARY_EXTENSION_HEADER_MAGIC ) {

			throw new Error( 'THREE.GLTFLoader: Unsupported glTF-Binary header.' );

		} else if ( this.header.version < 2.0 ) {

			throw new Error( 'THREE.GLTFLoader: Legacy binary file detected. Use LegacyGLTFLoader instead.' );

		}

		var chunkView = new DataView( data, BINARY_EXTENSION_HEADER_LENGTH );
		var chunkIndex = 0;

		while ( chunkIndex < chunkView.byteLength ) {

			var chunkLength = chunkView.getUint32( chunkIndex, true );
			chunkIndex += 4;

			var chunkType = chunkView.getUint32( chunkIndex, true );
			chunkIndex += 4;

			if ( chunkType === BINARY_EXTENSION_CHUNK_TYPES.JSON ) {

				var contentArray = new Uint8Array( data, BINARY_EXTENSION_HEADER_LENGTH + chunkIndex, chunkLength );
				this.content = THREE.LoaderUtils.decodeText( contentArray );

			} else if ( chunkType === BINARY_EXTENSION_CHUNK_TYPES.BIN ) {

				var byteOffset = BINARY_EXTENSION_HEADER_LENGTH + chunkIndex;
				this.body = data.slice( byteOffset, byteOffset + chunkLength );

			}

			// Clients must ignore chunks with unknown types.

			chunkIndex += chunkLength;

		}

		if ( this.content === null ) {

			throw new Error( 'THREE.GLTFLoader: JSON content not found.' );

		}

	}

	/**
	 * DRACO Mesh Compression Extension
	 *
	 * Specification: https://github.com/KhronosGroup/glTF/pull/874
	 */
	function GLTFDracoMeshCompressionExtension( json, dracoLoader ) {

		if ( ! dracoLoader ) {

			throw new Error( 'THREE.GLTFLoader: No DRACOLoader instance provided.' );

		}

		this.name = EXTENSIONS.KHR_DRACO_MESH_COMPRESSION;
		this.json = json;
		this.dracoLoader = dracoLoader;
		THREE.DRACOLoader.getDecoderModule();

	}

	GLTFDracoMeshCompressionExtension.prototype.decodePrimitive = function ( primitive, parser ) {

		var json = this.json;
		var dracoLoader = this.dracoLoader;
		var bufferViewIndex = primitive.extensions[ this.name ].bufferView;
		var gltfAttributeMap = primitive.extensions[ this.name ].attributes;
		var threeAttributeMap = {};
		var attributeNormalizedMap = {};
		var attributeTypeMap = {};

		for ( var attributeName in gltfAttributeMap ) {

			if ( ! ( attributeName in ATTRIBUTES ) ) continue;

			threeAttributeMap[ ATTRIBUTES[ attributeName ] ] = gltfAttributeMap[ attributeName ];

		}

		for ( attributeName in primitive.attributes ) {

			if ( ATTRIBUTES[ attributeName ] !== undefined && gltfAttributeMap[ attributeName ] !== undefined ) {

				var accessorDef = json.accessors[ primitive.attributes[ attributeName ] ];
				var componentType = WEBGL_COMPONENT_TYPES[ accessorDef.componentType ];

				attributeTypeMap[ ATTRIBUTES[ attributeName ] ] = componentType;
				attributeNormalizedMap[ ATTRIBUTES[ attributeName ] ] = accessorDef.normalized === true;

			}

		}

		return parser.getDependency( 'bufferView', bufferViewIndex ).then( function ( bufferView ) {

			return new Promise( function ( resolve ) {

				dracoLoader.decodeDracoFile( bufferView, function ( geometry ) {

					for ( var attributeName in geometry.attributes ) {

						var attribute = geometry.attributes[ attributeName ];
						var normalized = attributeNormalizedMap[ attributeName ];

						if ( normalized !== undefined ) attribute.normalized = normalized;

					}

					resolve( geometry );

				}, threeAttributeMap, attributeTypeMap );

			} );

		} );

	};

	/**
	 * Texture Transform Extension
	 *
	 * Specification:
	 */
	function GLTFTextureTransformExtension( json ) {

		this.name = EXTENSIONS.KHR_TEXTURE_TRANSFORM;

	}

	GLTFTextureTransformExtension.prototype.extendTexture = function ( texture, transform ) {

		texture = texture.clone();

		if ( transform.offset !== undefined ) {

			texture.offset.fromArray( transform.offset );

		}

		if ( transform.rotation !== undefined ) {

			texture.rotation = transform.rotation;

		}

		if ( transform.scale !== undefined ) {

			texture.repeat.fromArray( transform.scale );

		}

		if ( transform.texCoord !== undefined ) {

			console.warn( 'THREE.GLTFLoader: Custom UV sets in "' + this.name + '" extension not yet supported.' );

		}

		texture.needsUpdate = true;

		return texture;

	};

	/**
	 * Specular-Glossiness Extension
	 *
	 * Specification: https://github.com/KhronosGroup/glTF/tree/master/extensions/2.0/Khronos/KHR_materials_pbrSpecularGlossiness
	 */
	function GLTFMaterialsPbrSpecularGlossinessExtension() {

		return {

			name: EXTENSIONS.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS,

			specularGlossinessParams: [
				'color',
				'map',
				'lightMap',
				'lightMapIntensity',
				'aoMap',
				'aoMapIntensity',
				'emissive',
				'emissiveIntensity',
				'emissiveMap',
				'bumpMap',
				'bumpScale',
				'normalMap',
				'displacementMap',
				'displacementScale',
				'displacementBias',
				'specularMap',
				'specular',
				'glossinessMap',
				'glossiness',
				'alphaMap',
				'envMap',
				'envMapIntensity',
				'refractionRatio',
			],

			getMaterialType: function () {

				return THREE.ShaderMaterial;

			},

			extendParams: function ( params, material, parser ) {

				var pbrSpecularGlossiness = material.extensions[ this.name ];

				var shader = THREE.ShaderLib[ 'standard' ];

				var uniforms = THREE.UniformsUtils.clone( shader.uniforms );

				var specularMapParsFragmentChunk = [
					'#ifdef USE_SPECULARMAP',
					'	uniform sampler2D specularMap;',
					'#endif'
				].join( '\n' );

				var glossinessMapParsFragmentChunk = [
					'#ifdef USE_GLOSSINESSMAP',
					'	uniform sampler2D glossinessMap;',
					'#endif'
				].join( '\n' );

				var specularMapFragmentChunk = [
					'vec3 specularFactor = specular;',
					'#ifdef USE_SPECULARMAP',
					'	vec4 texelSpecular = texture2D( specularMap, vUv );',
					'	texelSpecular = sRGBToLinear( texelSpecular );',
					'	// reads channel RGB, compatible with a glTF Specular-Glossiness (RGBA) texture',
					'	specularFactor *= texelSpecular.rgb;',
					'#endif'
				].join( '\n' );

				var glossinessMapFragmentChunk = [
					'float glossinessFactor = glossiness;',
					'#ifdef USE_GLOSSINESSMAP',
					'	vec4 texelGlossiness = texture2D( glossinessMap, vUv );',
					'	// reads channel A, compatible with a glTF Specular-Glossiness (RGBA) texture',
					'	glossinessFactor *= texelGlossiness.a;',
					'#endif'
				].join( '\n' );

				var lightPhysicalFragmentChunk = [
					'PhysicalMaterial material;',
					'material.diffuseColor = diffuseColor.rgb;',
					'material.specularRoughness = clamp( 1.0 - glossinessFactor, 0.04, 1.0 );',
					'material.specularColor = specularFactor.rgb;',
				].join( '\n' );

				var fragmentShader = shader.fragmentShader
					.replace( 'uniform float roughness;', 'uniform vec3 specular;' )
					.replace( 'uniform float metalness;', 'uniform float glossiness;' )
					.replace( '#include <roughnessmap_pars_fragment>', specularMapParsFragmentChunk )
					.replace( '#include <metalnessmap_pars_fragment>', glossinessMapParsFragmentChunk )
					.replace( '#include <roughnessmap_fragment>', specularMapFragmentChunk )
					.replace( '#include <metalnessmap_fragment>', glossinessMapFragmentChunk )
					.replace( '#include <lights_physical_fragment>', lightPhysicalFragmentChunk );

				delete uniforms.roughness;
				delete uniforms.metalness;
				delete uniforms.roughnessMap;
				delete uniforms.metalnessMap;

				uniforms.specular = { value: new THREE.Color().setHex( 0x111111 ) };
				uniforms.glossiness = { value: 0.5 };
				uniforms.specularMap = { value: null };
				uniforms.glossinessMap = { value: null };

				params.vertexShader = shader.vertexShader;
				params.fragmentShader = fragmentShader;
				params.uniforms = uniforms;
				params.defines = { 'STANDARD': '' };

				params.color = new THREE.Color( 1.0, 1.0, 1.0 );
				params.opacity = 1.0;

				var pending = [];

				if ( Array.isArray( pbrSpecularGlossiness.diffuseFactor ) ) {

					var array = pbrSpecularGlossiness.diffuseFactor;

					params.color.fromArray( array );
					params.opacity = array[ 3 ];

				}

				if ( pbrSpecularGlossiness.diffuseTexture !== undefined ) {

					pending.push( parser.assignTexture( params, 'map', pbrSpecularGlossiness.diffuseTexture ) );

				}

				params.emissive = new THREE.Color( 0.0, 0.0, 0.0 );
				params.glossiness = pbrSpecularGlossiness.glossinessFactor !== undefined ? pbrSpecularGlossiness.glossinessFactor : 1.0;
				params.specular = new THREE.Color( 1.0, 1.0, 1.0 );

				if ( Array.isArray( pbrSpecularGlossiness.specularFactor ) ) {

					params.specular.fromArray( pbrSpecularGlossiness.specularFactor );

				}

				if ( pbrSpecularGlossiness.specularGlossinessTexture !== undefined ) {

					var specGlossMapDef = pbrSpecularGlossiness.specularGlossinessTexture;
					pending.push( parser.assignTexture( params, 'glossinessMap', specGlossMapDef ) );
					pending.push( parser.assignTexture( params, 'specularMap', specGlossMapDef ) );

				}

				return Promise.all( pending );

			},

			createMaterial: function ( params ) {

				// setup material properties based on MeshStandardMaterial for Specular-Glossiness

				var material = new THREE.ShaderMaterial( {
					defines: params.defines,
					vertexShader: params.vertexShader,
					fragmentShader: params.fragmentShader,
					uniforms: params.uniforms,
					fog: true,
					lights: true,
					opacity: params.opacity,
					transparent: params.transparent
				} );

				material.isGLTFSpecularGlossinessMaterial = true;

				material.color = params.color;

				material.map = params.map === undefined ? null : params.map;

				material.lightMap = null;
				material.lightMapIntensity = 1.0;

				material.aoMap = params.aoMap === undefined ? null : params.aoMap;
				material.aoMapIntensity = 1.0;

				material.emissive = params.emissive;
				material.emissiveIntensity = 1.0;
				material.emissiveMap = params.emissiveMap === undefined ? null : params.emissiveMap;

				material.bumpMap = params.bumpMap === undefined ? null : params.bumpMap;
				material.bumpScale = 1;

				material.normalMap = params.normalMap === undefined ? null : params.normalMap;
				if ( params.normalScale ) material.normalScale = params.normalScale;

				material.displacementMap = null;
				material.displacementScale = 1;
				material.displacementBias = 0;

				material.specularMap = params.specularMap === undefined ? null : params.specularMap;
				material.specular = params.specular;

				material.glossinessMap = params.glossinessMap === undefined ? null : params.glossinessMap;
				material.glossiness = params.glossiness;

				material.alphaMap = null;

				material.envMap = params.envMap === undefined ? null : params.envMap;
				material.envMapIntensity = 1.0;

				material.refractionRatio = 0.98;

				material.extensions.derivatives = true;

				return material;

			},

			/**
			 * Clones a GLTFSpecularGlossinessMaterial instance. The ShaderMaterial.copy() method can
			 * copy only properties it knows about or inherits, and misses many properties that would
			 * normally be defined by MeshStandardMaterial.
			 *
			 * This method allows GLTFSpecularGlossinessMaterials to be cloned in the process of
			 * loading a glTF model, but cloning later (e.g. by the user) would require these changes
			 * AND also updating `.onBeforeRender` on the parent mesh.
			 *
			 * @param  {THREE.ShaderMaterial} source
			 * @return {THREE.ShaderMaterial}
			 */
			cloneMaterial: function ( source ) {

				var target = source.clone();

				target.isGLTFSpecularGlossinessMaterial = true;

				var params = this.specularGlossinessParams;

				for ( var i = 0, il = params.length; i < il; i ++ ) {

					target[ params[ i ] ] = source[ params[ i ] ];

				}

				return target;

			},

			// Here's based on refreshUniformsCommon() and refreshUniformsStandard() in WebGLRenderer.
			refreshUniforms: function ( renderer, scene, camera, geometry, material, group ) {

				if ( material.isGLTFSpecularGlossinessMaterial !== true ) {

					return;

				}

				var uniforms = material.uniforms;
				var defines = material.defines;

				uniforms.opacity.value = material.opacity;

				uniforms.diffuse.value.copy( material.color );
				uniforms.emissive.value.copy( material.emissive ).multiplyScalar( material.emissiveIntensity );

				uniforms.map.value = material.map;
				uniforms.specularMap.value = material.specularMap;
				uniforms.alphaMap.value = material.alphaMap;

				uniforms.lightMap.value = material.lightMap;
				uniforms.lightMapIntensity.value = material.lightMapIntensity;

				uniforms.aoMap.value = material.aoMap;
				uniforms.aoMapIntensity.value = material.aoMapIntensity;

				// uv repeat and offset setting priorities
				// 1. color map
				// 2. specular map
				// 3. normal map
				// 4. bump map
				// 5. alpha map
				// 6. emissive map

				var uvScaleMap;

				if ( material.map ) {

					uvScaleMap = material.map;

				} else if ( material.specularMap ) {

					uvScaleMap = material.specularMap;

				} else if ( material.displacementMap ) {

					uvScaleMap = material.displacementMap;

				} else if ( material.normalMap ) {

					uvScaleMap = material.normalMap;

				} else if ( material.bumpMap ) {

					uvScaleMap = material.bumpMap;

				} else if ( material.glossinessMap ) {

					uvScaleMap = material.glossinessMap;

				} else if ( material.alphaMap ) {

					uvScaleMap = material.alphaMap;

				} else if ( material.emissiveMap ) {

					uvScaleMap = material.emissiveMap;

				}

				if ( uvScaleMap !== undefined ) {

					// backwards compatibility
					if ( uvScaleMap.isWebGLRenderTarget ) {

						uvScaleMap = uvScaleMap.texture;

					}

					if ( uvScaleMap.matrixAutoUpdate === true ) {

						uvScaleMap.updateMatrix();

					}

					uniforms.uvTransform.value.copy( uvScaleMap.matrix );

				}

				if ( material.envMap ) {

					uniforms.envMap.value = material.envMap;
					uniforms.envMapIntensity.value = material.envMapIntensity;

					// don't flip CubeTexture envMaps, flip everything else:
					//  WebGLRenderTargetCube will be flipped for backwards compatibility
					//  WebGLRenderTargetCube.texture will be flipped because it's a Texture and NOT a CubeTexture
					// this check must be handled differently, or removed entirely, if WebGLRenderTargetCube uses a CubeTexture in the future
					uniforms.flipEnvMap.value = material.envMap.isCubeTexture ? - 1 : 1;

					uniforms.reflectivity.value = material.reflectivity;
					uniforms.refractionRatio.value = material.refractionRatio;

					uniforms.maxMipLevel.value = renderer.properties.get( material.envMap ).__maxMipLevel;
				}

				uniforms.specular.value.copy( material.specular );
				uniforms.glossiness.value = material.glossiness;

				uniforms.glossinessMap.value = material.glossinessMap;

				uniforms.emissiveMap.value = material.emissiveMap;
				uniforms.bumpMap.value = material.bumpMap;
				uniforms.normalMap.value = material.normalMap;

				uniforms.displacementMap.value = material.displacementMap;
				uniforms.displacementScale.value = material.displacementScale;
				uniforms.displacementBias.value = material.displacementBias;

				if ( uniforms.glossinessMap.value !== null && defines.USE_GLOSSINESSMAP === undefined ) {

					defines.USE_GLOSSINESSMAP = '';
					// set USE_ROUGHNESSMAP to enable vUv
					defines.USE_ROUGHNESSMAP = '';

				}

				if ( uniforms.glossinessMap.value === null && defines.USE_GLOSSINESSMAP !== undefined ) {

					delete defines.USE_GLOSSINESSMAP;
					delete defines.USE_ROUGHNESSMAP;

				}

			}

		};

	}

	/*********************************/
	/********** INTERPOLATION ********/
	/*********************************/

	// Spline Interpolation
	// Specification: https://github.com/KhronosGroup/glTF/blob/master/specification/2.0/README.md#appendix-c-spline-interpolation
	function GLTFCubicSplineInterpolant( parameterPositions, sampleValues, sampleSize, resultBuffer ) {

		THREE.Interpolant.call( this, parameterPositions, sampleValues, sampleSize, resultBuffer );

	}

	GLTFCubicSplineInterpolant.prototype = Object.create( THREE.Interpolant.prototype );
	GLTFCubicSplineInterpolant.prototype.constructor = GLTFCubicSplineInterpolant;

	GLTFCubicSplineInterpolant.prototype.copySampleValue_ = function ( index ) {

		// Copies a sample value to the result buffer. See description of glTF
		// CUBICSPLINE values layout in interpolate_() function below.

		var result = this.resultBuffer,
			values = this.sampleValues,
			valueSize = this.valueSize,
			offset = index * valueSize * 3 + valueSize;

		for ( var i = 0; i !== valueSize; i ++ ) {

			result[ i ] = values[ offset + i ];

		}

		return result;

	};

	GLTFCubicSplineInterpolant.prototype.beforeStart_ = GLTFCubicSplineInterpolant.prototype.copySampleValue_;

	GLTFCubicSplineInterpolant.prototype.afterEnd_ = GLTFCubicSplineInterpolant.prototype.copySampleValue_;

	GLTFCubicSplineInterpolant.prototype.interpolate_ = function ( i1, t0, t, t1 ) {

		var result = this.resultBuffer;
		var values = this.sampleValues;
		var stride = this.valueSize;

		var stride2 = stride * 2;
		var stride3 = stride * 3;

		var td = t1 - t0;

		var p = ( t - t0 ) / td;
		var pp = p * p;
		var ppp = pp * p;

		var offset1 = i1 * stride3;
		var offset0 = offset1 - stride3;

		var s2 = - 2 * ppp + 3 * pp;
		var s3 = ppp - pp;
		var s0 = 1 - s2;
		var s1 = s3 - pp + p;

		// Layout of keyframe output values for CUBICSPLINE animations:
		//   [ inTangent_1, splineVertex_1, outTangent_1, inTangent_2, splineVertex_2, ... ]
		for ( var i = 0; i !== stride; i ++ ) {

			var p0 = values[ offset0 + i + stride ]; // splineVertex_k
			var m0 = values[ offset0 + i + stride2 ] * td; // outTangent_k * (t_k+1 - t_k)
			var p1 = values[ offset1 + i + stride ]; // splineVertex_k+1
			var m1 = values[ offset1 + i ] * td; // inTangent_k+1 * (t_k+1 - t_k)

			result[ i ] = s0 * p0 + s1 * m0 + s2 * p1 + s3 * m1;

		}

		return result;

	};

	/*********************************/
	/********** INTERNALS ************/
	/*********************************/

	/* CONSTANTS */

	var WEBGL_CONSTANTS = {
		FLOAT: 5126,
		//FLOAT_MAT2: 35674,
		FLOAT_MAT3: 35675,
		FLOAT_MAT4: 35676,
		FLOAT_VEC2: 35664,
		FLOAT_VEC3: 35665,
		FLOAT_VEC4: 35666,
		LINEAR: 9729,
		REPEAT: 10497,
		SAMPLER_2D: 35678,
		POINTS: 0,
		LINES: 1,
		LINE_LOOP: 2,
		LINE_STRIP: 3,
		TRIANGLES: 4,
		TRIANGLE_STRIP: 5,
		TRIANGLE_FAN: 6,
		UNSIGNED_BYTE: 5121,
		UNSIGNED_SHORT: 5123
	};

	var WEBGL_TYPE = {
		5126: Number,
		//35674: THREE.Matrix2,
		35675: THREE.Matrix3,
		35676: THREE.Matrix4,
		35664: THREE.Vector2,
		35665: THREE.Vector3,
		35666: THREE.Vector4,
		35678: THREE.Texture
	};

	var WEBGL_COMPONENT_TYPES = {
		5120: Int8Array,
		5121: Uint8Array,
		5122: Int16Array,
		5123: Uint16Array,
		5125: Uint32Array,
		5126: Float32Array
	};

	var WEBGL_FILTERS = {
		9728: THREE.NearestFilter,
		9729: THREE.LinearFilter,
		9984: THREE.NearestMipMapNearestFilter,
		9985: THREE.LinearMipMapNearestFilter,
		9986: THREE.NearestMipMapLinearFilter,
		9987: THREE.LinearMipMapLinearFilter
	};

	var WEBGL_WRAPPINGS = {
		33071: THREE.ClampToEdgeWrapping,
		33648: THREE.MirroredRepeatWrapping,
		10497: THREE.RepeatWrapping
	};

	var WEBGL_SIDES = {
		1028: THREE.BackSide, // Culling front
		1029: THREE.FrontSide // Culling back
		//1032: THREE.NoSide   // Culling front and back, what to do?
	};

	var WEBGL_DEPTH_FUNCS = {
		512: THREE.NeverDepth,
		513: THREE.LessDepth,
		514: THREE.EqualDepth,
		515: THREE.LessEqualDepth,
		516: THREE.GreaterEqualDepth,
		517: THREE.NotEqualDepth,
		518: THREE.GreaterEqualDepth,
		519: THREE.AlwaysDepth
	};

	var WEBGL_BLEND_EQUATIONS = {
		32774: THREE.AddEquation,
		32778: THREE.SubtractEquation,
		32779: THREE.ReverseSubtractEquation
	};

	var WEBGL_BLEND_FUNCS = {
		0: THREE.ZeroFactor,
		1: THREE.OneFactor,
		768: THREE.SrcColorFactor,
		769: THREE.OneMinusSrcColorFactor,
		770: THREE.SrcAlphaFactor,
		771: THREE.OneMinusSrcAlphaFactor,
		772: THREE.DstAlphaFactor,
		773: THREE.OneMinusDstAlphaFactor,
		774: THREE.DstColorFactor,
		775: THREE.OneMinusDstColorFactor,
		776: THREE.SrcAlphaSaturateFactor
		// The followings are not supported by Three.js yet
		//32769: CONSTANT_COLOR,
		//32770: ONE_MINUS_CONSTANT_COLOR,
		//32771: CONSTANT_ALPHA,
		//32772: ONE_MINUS_CONSTANT_COLOR
	};

	var WEBGL_TYPE_SIZES = {
		'SCALAR': 1,
		'VEC2': 2,
		'VEC3': 3,
		'VEC4': 4,
		'MAT2': 4,
		'MAT3': 9,
		'MAT4': 16
	};

	var ATTRIBUTES = {
		POSITION: 'position',
		NORMAL: 'normal',
		TEXCOORD_0: 'uv',
		TEXCOORD_1: 'uv2',
		COLOR_0: 'color',
		WEIGHTS_0: 'skinWeight',
		JOINTS_0: 'skinIndex',
	};

	var PATH_PROPERTIES = {
		scale: 'scale',
		translation: 'position',
		rotation: 'quaternion',
		weights: 'morphTargetInfluences'
	};

	var INTERPOLATION = {
		CUBICSPLINE: THREE.InterpolateSmooth, // We use custom interpolation GLTFCubicSplineInterpolation for CUBICSPLINE.
		                                      // KeyframeTrack.optimize() can't handle glTF Cubic Spline output values layout,
		                                      // using THREE.InterpolateSmooth for KeyframeTrack instantiation to prevent optimization.
		                                      // See KeyframeTrack.optimize() for the detail.
		LINEAR: THREE.InterpolateLinear,
		STEP: THREE.InterpolateDiscrete
	};

	var STATES_ENABLES = {
		2884: 'CULL_FACE',
		2929: 'DEPTH_TEST',
		3042: 'BLEND',
		3089: 'SCISSOR_TEST',
		32823: 'POLYGON_OFFSET_FILL',
		32926: 'SAMPLE_ALPHA_TO_COVERAGE'
	};

	var ALPHA_MODES = {
		OPAQUE: 'OPAQUE',
		MASK: 'MASK',
		BLEND: 'BLEND'
	};

	var MIME_TYPE_FORMATS = {
		'image/png': THREE.RGBAFormat,
		'image/jpeg': THREE.RGBFormat
	};

	/* UTILITY FUNCTIONS */

	function resolveURL( url, path ) {

		// Invalid URL
		if ( typeof url !== 'string' || url === '' ) return '';

		// Absolute URL http://,https://,//
		if ( /^(https?:)?\/\//i.test( url ) ) return url;

		// Data URI
		if ( /^data:.*,.*$/i.test( url ) ) return url;

		// Blob URL
		if ( /^blob:.*$/i.test( url ) ) return url;

		// Relative URL
		return path + url;

	}

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/blob/master/specification/2.0/README.md#default-material
	 */
	function createDefaultMaterial() {

		return new THREE.MeshStandardMaterial( {
			color: 0xFFFFFF,
			emissive: 0x000000,
			metalness: 1,
			roughness: 1,
			transparent: false,
			depthTest: true,
			side: THREE.FrontSide
		} );

	}

	function addUnknownExtensionsToUserData( knownExtensions, object, objectDef ) {

		// Add unknown glTF extensions to an object's userData.

		for ( var name in objectDef.extensions ) {

			if ( knownExtensions[ name ] === undefined ) {

				object.userData.gltfExtensions = object.userData.gltfExtensions || {};
				object.userData.gltfExtensions[ name ] = objectDef.extensions[ name ];

			}

		}

	}

	/**
	 * @param {THREE.Object3D|THREE.Material|THREE.BufferGeometry} object
	 * @param {GLTF.definition} gltfDef
	 */
	function assignExtrasToUserData( object, gltfDef ) {

		if ( gltfDef.extras !== undefined ) {

			if ( typeof gltfDef.extras === 'object' ) {

				object.userData = gltfDef.extras;

			} else {

				console.warn( 'THREE.GLTFLoader: Ignoring primitive type .extras, ' + gltfDef.extras );

			}

		}

	}

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/blob/master/specification/2.0/README.md#morph-targets
	 *
	 * @param {THREE.BufferGeometry} geometry
	 * @param {Array<GLTF.Target>} targets
	 * @param {GLTFParser} parser
	 * @return {Promise<THREE.BufferGeometry>}
	 */
	function addMorphTargets( geometry, targets, parser ) {

		var hasMorphPosition = false;
		var hasMorphNormal = false;

		for ( var i = 0, il = targets.length; i < il; i ++ ) {

			var target = targets[ i ];

			if ( target.POSITION !== undefined ) hasMorphPosition = true;
			if ( target.NORMAL !== undefined ) hasMorphNormal = true;

			if ( hasMorphPosition && hasMorphNormal ) break;

		}

		if ( ! hasMorphPosition && ! hasMorphNormal ) return Promise.resolve( geometry );

		var pendingPositionAccessors = [];
		var pendingNormalAccessors = [];

		for ( var i = 0, il = targets.length; i < il; i ++ ) {

			var target = targets[ i ];

			if ( hasMorphPosition ) {

				// TODO: Error-prone use of a callback inside a loop.
				var accessor = target.POSITION !== undefined
					? parser.getDependency( 'accessor', target.POSITION )
						.then( function ( accessor ) {
							// Cloning not to pollute original accessor below
							return cloneBufferAttribute( accessor );
						} )
					: geometry.attributes.position;

				pendingPositionAccessors.push( accessor );

			}

			if ( hasMorphNormal ) {

				// TODO: Error-prone use of a callback inside a loop.
				var accessor = target.NORMAL !== undefined
					? parser.getDependency( 'accessor', target.NORMAL )
						.then( function ( accessor ) {
							return cloneBufferAttribute( accessor );
						} )
					: geometry.attributes.normal;

				pendingNormalAccessors.push( accessor );

			}

		}

		return Promise.all( [
			Promise.all( pendingPositionAccessors ),
			Promise.all( pendingNormalAccessors )
		] ).then( function ( accessors ) {

			var morphPositions = accessors[ 0 ];
			var morphNormals = accessors[ 1 ];

			for ( var i = 0, il = targets.length; i < il; i ++ ) {

				var target = targets[ i ];
				var attributeName = 'morphTarget' + i;

				if ( hasMorphPosition ) {

					// Three.js morph position is absolute value. The formula is
					//   basePosition
					//     + weight0 * ( morphPosition0 - basePosition )
					//     + weight1 * ( morphPosition1 - basePosition )
					//     ...
					// while the glTF one is relative
					//   basePosition
					//     + weight0 * glTFmorphPosition0
					//     + weight1 * glTFmorphPosition1
					//     ...
					// then we need to convert from relative to absolute here.

					if ( target.POSITION !== undefined ) {

						var positionAttribute = morphPositions[ i ];
						positionAttribute.name = attributeName;

						var position = geometry.attributes.position;

						for ( var j = 0, jl = positionAttribute.count; j < jl; j ++ ) {

							positionAttribute.setXYZ(
								j,
								positionAttribute.getX( j ) + position.getX( j ),
								positionAttribute.getY( j ) + position.getY( j ),
								positionAttribute.getZ( j ) + position.getZ( j )
							);

						}

					}

				}

				if ( hasMorphNormal ) {

					// see target.POSITION's comment

					if ( target.NORMAL !== undefined ) {

						var normalAttribute = morphNormals[ i ];
						normalAttribute.name = attributeName;

						var normal = geometry.attributes.normal;

						for ( var j = 0, jl = normalAttribute.count; j < jl; j ++ ) {

							normalAttribute.setXYZ(
								j,
								normalAttribute.getX( j ) + normal.getX( j ),
								normalAttribute.getY( j ) + normal.getY( j ),
								normalAttribute.getZ( j ) + normal.getZ( j )
							);

						}

					}

				}

			}

			if ( hasMorphPosition ) geometry.morphAttributes.position = morphPositions;
			if ( hasMorphNormal ) geometry.morphAttributes.normal = morphNormals;

			return geometry;

		} );

	}

	/**
	 * @param {THREE.Mesh} mesh
	 * @param {GLTF.Mesh} meshDef
	 */
	function updateMorphTargets( mesh, meshDef ) {

		mesh.updateMorphTargets();

		if ( meshDef.weights !== undefined ) {

			for ( var i = 0, il = meshDef.weights.length; i < il; i ++ ) {

				mesh.morphTargetInfluences[ i ] = meshDef.weights[ i ];

			}

		}

		// .extras has user-defined data, so check that .extras.targetNames is an array.
		if ( meshDef.extras && Array.isArray( meshDef.extras.targetNames ) ) {

			var targetNames = meshDef.extras.targetNames;

			if ( mesh.morphTargetInfluences.length === targetNames.length ) {

				mesh.morphTargetDictionary = {};

				for ( var i = 0, il = targetNames.length; i < il; i ++ ) {

					mesh.morphTargetDictionary[ targetNames[ i ] ] = i;

				}

			} else {

				console.warn( 'THREE.GLTFLoader: Invalid extras.targetNames length. Ignoring names.' );

			}

		}

	}

	function isPrimitiveEqual( a, b ) {

		var dracoExtA = a.extensions ? a.extensions[ EXTENSIONS.KHR_DRACO_MESH_COMPRESSION ] : undefined;
		var dracoExtB = b.extensions ? b.extensions[ EXTENSIONS.KHR_DRACO_MESH_COMPRESSION ] : undefined;

		if ( dracoExtA && dracoExtB ) {

			if ( dracoExtA.bufferView !== dracoExtB.bufferView ) return false;

			return isObjectEqual( dracoExtA.attributes, dracoExtB.attributes );

		}

		if ( a.indices !== b.indices ) {

			return false;

		}

		return isObjectEqual( a.attributes, b.attributes );

	}

	function isObjectEqual( a, b ) {

		if ( Object.keys( a ).length !== Object.keys( b ).length ) return false;

		for ( var key in a ) {

			if ( a[ key ] !== b[ key ] ) return false;

		}

		return true;

	}

	function isArrayEqual( a, b ) {

		if ( a.length !== b.length ) return false;

		for ( var i = 0, il = a.length; i < il; i ++ ) {

			if ( a[ i ] !== b[ i ] ) return false;

		}

		return true;

	}

	function getCachedGeometry( cache, newPrimitive ) {

		for ( var i = 0, il = cache.length; i < il; i ++ ) {

			var cached = cache[ i ];

			if ( isPrimitiveEqual( cached.primitive, newPrimitive ) ) return cached.promise;

		}

		return null;

	}

	function getCachedCombinedGeometry( cache, geometries ) {

		for ( var i = 0, il = cache.length; i < il; i ++ ) {

			var cached = cache[ i ];

			if ( isArrayEqual( geometries, cached.baseGeometries ) ) return cached.geometry;

		}

		return null;

	}

	function getCachedMultiPassGeometry( cache, geometry, primitives ) {

		for ( var i = 0, il = cache.length; i < il; i ++ ) {

			var cached = cache[ i ];

			if ( geometry === cached.baseGeometry && isArrayEqual( primitives, cached.primitives ) ) return cached.geometry;

		}

		return null;

	}

	function cloneBufferAttribute( attribute ) {

		if ( attribute.isInterleavedBufferAttribute ) {

			var count = attribute.count;
			var itemSize = attribute.itemSize;
			var array = attribute.array.slice( 0, count * itemSize );

			for ( var i = 0, j = 0; i < count; ++ i ) {

				array[ j ++ ] = attribute.getX( i );
				if ( itemSize >= 2 ) array[ j ++ ] = attribute.getY( i );
				if ( itemSize >= 3 ) array[ j ++ ] = attribute.getZ( i );
				if ( itemSize >= 4 ) array[ j ++ ] = attribute.getW( i );

			}

			return new THREE.BufferAttribute( array, itemSize, attribute.normalized );

		}

		return attribute.clone();

	}

	/**
	 * Checks if we can build a single Mesh with MultiMaterial from multiple primitives.
	 * Returns true if all primitives use the same attributes/morphAttributes/mode
	 * and also have index. Otherwise returns false.
	 *
	 * @param {Array<GLTF.Primitive>} primitives
	 * @return {Boolean}
	 */
	function isMultiPassGeometry( primitives ) {

		if ( primitives.length < 2 ) return false;

		var primitive0 = primitives[ 0 ];
		var targets0 = primitive0.targets || [];

		if ( primitive0.indices === undefined ) return false;

		for ( var i = 1, il = primitives.length; i < il; i ++ ) {

			var primitive = primitives[ i ];

			if ( primitive0.mode !== primitive.mode ) return false;
			if ( primitive.indices === undefined ) return false;
			if ( primitive.extensions && primitive.extensions[ EXTENSIONS.KHR_DRACO_MESH_COMPRESSION ] ) return false;
			if ( ! isObjectEqual( primitive0.attributes, primitive.attributes ) ) return false;

			var targets = primitive.targets || [];

			if ( targets0.length !== targets.length ) return false;

			for ( var j = 0, jl = targets0.length; j < jl; j ++ ) {

				if ( ! isObjectEqual( targets0[ j ], targets[ j ] ) ) return false;

			}

		}

		return true;

	}

	/* GLTF PARSER */

	function GLTFParser( json, extensions, options ) {

		this.json = json || {};
		this.extensions = extensions || {};
		this.options = options || {};

		// loader object cache
		this.cache = new GLTFRegistry();

		// BufferGeometry caching
		this.primitiveCache = [];
		this.multiplePrimitivesCache = [];
		this.multiPassGeometryCache = [];

		this.textureLoader = new THREE.TextureLoader( this.options.manager );
		this.textureLoader.setCrossOrigin( this.options.crossOrigin );

		this.fileLoader = new THREE.FileLoader( this.options.manager );
		this.fileLoader.setResponseType( 'arraybuffer' );

	}

	GLTFParser.prototype.parse = function ( onLoad, onError ) {

		var json = this.json;

		// Clear the loader cache
		this.cache.removeAll();

		// Mark the special nodes/meshes in json for efficient parse
		this.markDefs();

		// Fire the callback on complete
		this.getMultiDependencies( [

			'scene',
			'animation',
			'camera'

		] ).then( function ( dependencies ) {

			var scenes = dependencies.scenes || [];
			var scene = scenes[ json.scene || 0 ];
			var animations = dependencies.animations || [];
			var cameras = dependencies.cameras || [];

			onLoad( scene, scenes, cameras, animations, json );

		} ).catch( onError );

	};

	/**
	 * Marks the special nodes/meshes in json for efficient parse.
	 */
	GLTFParser.prototype.markDefs = function () {

		var nodeDefs = this.json.nodes || [];
		var skinDefs = this.json.skins || [];
		var meshDefs = this.json.meshes || [];

		var meshReferences = {};
		var meshUses = {};

		// Nothing in the node definition indicates whether it is a Bone or an
		// Object3D. Use the skins' joint references to mark bones.
		for ( var skinIndex = 0, skinLength = skinDefs.length; skinIndex < skinLength; skinIndex ++ ) {

			var joints = skinDefs[ skinIndex ].joints;

			for ( var i = 0, il = joints.length; i < il; i ++ ) {

				nodeDefs[ joints[ i ] ].isBone = true;

			}

		}

		// Meshes can (and should) be reused by multiple nodes in a glTF asset. To
		// avoid having more than one THREE.Mesh with the same name, count
		// references and rename instances below.
		//
		// Example: CesiumMilkTruck sample model reuses "Wheel" meshes.
		for ( var nodeIndex = 0, nodeLength = nodeDefs.length; nodeIndex < nodeLength; nodeIndex ++ ) {

			var nodeDef = nodeDefs[ nodeIndex ];

			if ( nodeDef.mesh !== undefined ) {

				if ( meshReferences[ nodeDef.mesh ] === undefined ) {

					meshReferences[ nodeDef.mesh ] = meshUses[ nodeDef.mesh ] = 0;

				}

				meshReferences[ nodeDef.mesh ] ++;

				// Nothing in the mesh definition indicates whether it is
				// a SkinnedMesh or Mesh. Use the node's mesh reference
				// to mark SkinnedMesh if node has skin.
				if ( nodeDef.skin !== undefined ) {

					meshDefs[ nodeDef.mesh ].isSkinnedMesh = true;

				}

			}

		}

		this.json.meshReferences = meshReferences;
		this.json.meshUses = meshUses;

	};

	/**
	 * Requests the specified dependency asynchronously, with caching.
	 * @param {string} type
	 * @param {number} index
	 * @return {Promise<THREE.Object3D|THREE.Material|THREE.Texture|THREE.AnimationClip|ArrayBuffer|Object>}
	 */
	GLTFParser.prototype.getDependency = function ( type, index ) {

		var cacheKey = type + ':' + index;
		var dependency = this.cache.get( cacheKey );

		if ( ! dependency ) {

			switch ( type ) {

				case 'scene':
					dependency = this.loadScene( index );
					break;

				case 'node':
					dependency = this.loadNode( index );
					break;

				case 'mesh':
					dependency = this.loadMesh( index );
					break;

				case 'accessor':
					dependency = this.loadAccessor( index );
					break;

				case 'bufferView':
					dependency = this.loadBufferView( index );
					break;

				case 'buffer':
					dependency = this.loadBuffer( index );
					break;

				case 'material':
					dependency = this.loadMaterial( index );
					break;

				case 'texture':
					dependency = this.loadTexture( index );
					break;

				case 'skin':
					dependency = this.loadSkin( index );
					break;

				case 'animation':
					dependency = this.loadAnimation( index );
					break;

				case 'camera':
					dependency = this.loadCamera( index );
					break;

				case 'light':
					dependency = this.extensions[ EXTENSIONS.KHR_LIGHTS_PUNCTUAL ].loadLight( index );
					break

				default:
					throw new Error( 'Unknown type: ' + type );

			}

			this.cache.add( cacheKey, dependency );

		}

		return dependency;

	};

	/**
	 * Requests all dependencies of the specified type asynchronously, with caching.
	 * @param {string} type
	 * @return {Promise<Array<Object>>}
	 */
	GLTFParser.prototype.getDependencies = function ( type ) {

		var dependencies = this.cache.get( type );

		if ( ! dependencies ) {

			var parser = this;
			var defs = this.json[ type + ( type === 'mesh' ? 'es' : 's' ) ] || [];

			dependencies = Promise.all( defs.map( function ( def, index ) {

				return parser.getDependency( type, index );

			} ) );

			this.cache.add( type, dependencies );

		}

		return dependencies;

	};

	/**
	 * Requests all multiple dependencies of the specified types asynchronously, with caching.
	 * @param {Array<string>} types
	 * @return {Promise<Object<Array<Object>>>}
	 */
	GLTFParser.prototype.getMultiDependencies = function ( types ) {

		var results = {};
		var pending = [];

		for ( var i = 0, il = types.length; i < il; i ++ ) {

			var type = types[ i ];
			var value = this.getDependencies( type );

			// TODO: Error-prone use of a callback inside a loop.
			value = value.then( function ( key, value ) {

				results[ key ] = value;

			}.bind( this, type + ( type === 'mesh' ? 'es' : 's' ) ) );

			pending.push( value );

		}

		return Promise.all( pending ).then( function () {

			return results;

		} );

	};

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/blob/master/specification/2.0/README.md#buffers-and-buffer-views
	 * @param {number} bufferIndex
	 * @return {Promise<ArrayBuffer>}
	 */
	GLTFParser.prototype.loadBuffer = function ( bufferIndex ) {

		var bufferDef = this.json.buffers[ bufferIndex ];
		var loader = this.fileLoader;

		if ( bufferDef.type && bufferDef.type !== 'arraybuffer' ) {

			throw new Error( 'THREE.GLTFLoader: ' + bufferDef.type + ' buffer type is not supported.' );

		}

		// If present, GLB container is required to be the first buffer.
		if ( bufferDef.uri === undefined && bufferIndex === 0 ) {

			return Promise.resolve( this.extensions[ EXTENSIONS.KHR_BINARY_GLTF ].body );

		}

		var options = this.options;

		return new Promise( function ( resolve, reject ) {

			loader.load( resolveURL( bufferDef.uri, options.path ), resolve, undefined, function () {

				reject( new Error( 'THREE.GLTFLoader: Failed to load buffer "' + bufferDef.uri + '".' ) );

			} );

		} );

	};

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/blob/master/specification/2.0/README.md#buffers-and-buffer-views
	 * @param {number} bufferViewIndex
	 * @return {Promise<ArrayBuffer>}
	 */
	GLTFParser.prototype.loadBufferView = function ( bufferViewIndex ) {

		var bufferViewDef = this.json.bufferViews[ bufferViewIndex ];

		return this.getDependency( 'buffer', bufferViewDef.buffer ).then( function ( buffer ) {

			var byteLength = bufferViewDef.byteLength || 0;
			var byteOffset = bufferViewDef.byteOffset || 0;
			return buffer.slice( byteOffset, byteOffset + byteLength );

		} );

	};

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/blob/master/specification/2.0/README.md#accessors
	 * @param {number} accessorIndex
	 * @return {Promise<THREE.BufferAttribute|THREE.InterleavedBufferAttribute>}
	 */
	GLTFParser.prototype.loadAccessor = function ( accessorIndex ) {

		var parser = this;
		var json = this.json;

		var accessorDef = this.json.accessors[ accessorIndex ];

		if ( accessorDef.bufferView === undefined && accessorDef.sparse === undefined ) {

			// Ignore empty accessors, which may be used to declare runtime
			// information about attributes coming from another source (e.g. Draco
			// compression extension).
			return Promise.resolve( null );

		}

		var pendingBufferViews = [];

		if ( accessorDef.bufferView !== undefined ) {

			pendingBufferViews.push( this.getDependency( 'bufferView', accessorDef.bufferView ) );

		} else {

			pendingBufferViews.push( null );

		}

		if ( accessorDef.sparse !== undefined ) {

			pendingBufferViews.push( this.getDependency( 'bufferView', accessorDef.sparse.indices.bufferView ) );
			pendingBufferViews.push( this.getDependency( 'bufferView', accessorDef.sparse.values.bufferView ) );

		}

		return Promise.all( pendingBufferViews ).then( function ( bufferViews ) {

			var bufferView = bufferViews[ 0 ];

			var itemSize = WEBGL_TYPE_SIZES[ accessorDef.type ];
			var TypedArray = WEBGL_COMPONENT_TYPES[ accessorDef.componentType ];

			// For VEC3: itemSize is 3, elementBytes is 4, itemBytes is 12.
			var elementBytes = TypedArray.BYTES_PER_ELEMENT;
			var itemBytes = elementBytes * itemSize;
			var byteOffset = accessorDef.byteOffset || 0;
			var byteStride = accessorDef.bufferView !== undefined ? json.bufferViews[ accessorDef.bufferView ].byteStride : undefined;
			var normalized = accessorDef.normalized === true;
			var array, bufferAttribute;

			// The buffer is not interleaved if the stride is the item size in bytes.
			if ( byteStride && byteStride !== itemBytes ) {

				var ibCacheKey = 'InterleavedBuffer:' + accessorDef.bufferView + ':' + accessorDef.componentType;
				var ib = parser.cache.get( ibCacheKey );

				if ( ! ib ) {

					// Use the full buffer if it's interleaved.
					array = new TypedArray( bufferView );

					// Integer parameters to IB/IBA are in array elements, not bytes.
					ib = new THREE.InterleavedBuffer( array, byteStride / elementBytes );

					parser.cache.add( ibCacheKey, ib );

				}

				bufferAttribute = new THREE.InterleavedBufferAttribute( ib, itemSize, byteOffset / elementBytes, normalized );

			} else {

				if ( bufferView === null ) {

					array = new TypedArray( accessorDef.count * itemSize );

				} else {

					array = new TypedArray( bufferView, byteOffset, accessorDef.count * itemSize );

				}

				bufferAttribute = new THREE.BufferAttribute( array, itemSize, normalized );

			}

			// https://github.com/KhronosGroup/glTF/blob/master/specification/2.0/README.md#sparse-accessors
			if ( accessorDef.sparse !== undefined ) {

				var itemSizeIndices = WEBGL_TYPE_SIZES.SCALAR;
				var TypedArrayIndices = WEBGL_COMPONENT_TYPES[ accessorDef.sparse.indices.componentType ];

				var byteOffsetIndices = accessorDef.sparse.indices.byteOffset || 0;
				var byteOffsetValues = accessorDef.sparse.values.byteOffset || 0;

				var sparseIndices = new TypedArrayIndices( bufferViews[ 1 ], byteOffsetIndices, accessorDef.sparse.count * itemSizeIndices );
				var sparseValues = new TypedArray( bufferViews[ 2 ], byteOffsetValues, accessorDef.sparse.count * itemSize );

				if ( bufferView !== null ) {

					// Avoid modifying the original ArrayBuffer, if the bufferView wasn't initialized with zeroes.
					bufferAttribute.setArray( bufferAttribute.array.slice() );

				}

				for ( var i = 0, il = sparseIndices.length; i < il; i ++ ) {

					var index = sparseIndices[ i ];

					bufferAttribute.setX( index, sparseValues[ i * itemSize ] );
					if ( itemSize >= 2 ) bufferAttribute.setY( index, sparseValues[ i * itemSize + 1 ] );
					if ( itemSize >= 3 ) bufferAttribute.setZ( index, sparseValues[ i * itemSize + 2 ] );
					if ( itemSize >= 4 ) bufferAttribute.setW( index, sparseValues[ i * itemSize + 3 ] );
					if ( itemSize >= 5 ) throw new Error( 'THREE.GLTFLoader: Unsupported itemSize in sparse BufferAttribute.' );

				}

			}

			return bufferAttribute;

		} );

	};

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/tree/master/specification/2.0#textures
	 * @param {number} textureIndex
	 * @return {Promise<THREE.Texture>}
	 */
	GLTFParser.prototype.loadTexture = function ( textureIndex ) {

		var parser = this;
		var json = this.json;
		var options = this.options;
		var textureLoader = this.textureLoader;

		var URL = window.URL || window.webkitURL;

		var textureDef = json.textures[ textureIndex ];

		var textureExtensions = textureDef.extensions || {};

		var source;

		if ( textureExtensions[ EXTENSIONS.MSFT_TEXTURE_DDS ] ) {

			source = json.images[ textureExtensions[ EXTENSIONS.MSFT_TEXTURE_DDS ].source ];

		} else {

			source = json.images[ textureDef.source ];

		}

		var sourceURI = source.uri;
		var isObjectURL = false;

		if ( source.bufferView !== undefined ) {

			// Load binary image data from bufferView, if provided.

			sourceURI = parser.getDependency( 'bufferView', source.bufferView ).then( function ( bufferView ) {

				isObjectURL = true;
				var blob = new Blob( [ bufferView ], { type: source.mimeType } );
				sourceURI = URL.createObjectURL( blob );
				return sourceURI;

			} );

		}

		return Promise.resolve( sourceURI ).then( function ( sourceURI ) {

			// Load Texture resource.

			var loader = THREE.Loader.Handlers.get( sourceURI );

			if ( ! loader ) {

				loader = textureExtensions[ EXTENSIONS.MSFT_TEXTURE_DDS ]
					? parser.extensions[ EXTENSIONS.MSFT_TEXTURE_DDS ].ddsLoader
					: textureLoader;

			}

			return new Promise( function ( resolve, reject ) {

				loader.load( resolveURL( sourceURI, options.path ), resolve, undefined, reject );

			} );

		} ).then( function ( texture ) {

			// Clean up resources and configure Texture.

			if ( isObjectURL === true ) {

				URL.revokeObjectURL( sourceURI );

			}

			texture.flipY = false;

			if ( textureDef.name !== undefined ) texture.name = textureDef.name;

			// Ignore unknown mime types, like DDS files.
			if ( source.mimeType in MIME_TYPE_FORMATS ) {

				texture.format = MIME_TYPE_FORMATS[ source.mimeType ];

			}

			var samplers = json.samplers || {};
			var sampler = samplers[ textureDef.sampler ] || {};

			texture.magFilter = WEBGL_FILTERS[ sampler.magFilter ] || THREE.LinearFilter;
			texture.minFilter = WEBGL_FILTERS[ sampler.minFilter ] || THREE.LinearMipMapLinearFilter;
			texture.wrapS = WEBGL_WRAPPINGS[ sampler.wrapS ] || THREE.RepeatWrapping;
			texture.wrapT = WEBGL_WRAPPINGS[ sampler.wrapT ] || THREE.RepeatWrapping;

			return texture;

		} );

	};

	/**
	 * Asynchronously assigns a texture to the given material parameters.
	 * @param {Object} materialParams
	 * @param {string} mapName
	 * @param {Object} mapDef
	 * @return {Promise}
	 */
	GLTFParser.prototype.assignTexture = function ( materialParams, mapName, mapDef ) {

		var parser = this;

		return this.getDependency( 'texture', mapDef.index ).then( function ( texture ) {

			if ( parser.extensions[ EXTENSIONS.KHR_TEXTURE_TRANSFORM ] ) {

				var transform = mapDef.extensions !== undefined ? mapDef.extensions[ EXTENSIONS.KHR_TEXTURE_TRANSFORM ] : undefined;

				if ( transform ) {

					texture = parser.extensions[ EXTENSIONS.KHR_TEXTURE_TRANSFORM ].extendTexture( texture, transform );

				}

			}

			materialParams[ mapName ] = texture;

		} );

	};

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/blob/master/specification/2.0/README.md#materials
	 * @param {number} materialIndex
	 * @return {Promise<THREE.Material>}
	 */
	GLTFParser.prototype.loadMaterial = function ( materialIndex ) {

		var parser = this;
		var json = this.json;
		var extensions = this.extensions;
		var materialDef = json.materials[ materialIndex ];

		var materialType;
		var materialParams = {};
		var materialExtensions = materialDef.extensions || {};

		var pending = [];

		if ( materialExtensions[ EXTENSIONS.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS ] ) {

			var sgExtension = extensions[ EXTENSIONS.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS ];
			materialType = sgExtension.getMaterialType( materialDef );
			pending.push( sgExtension.extendParams( materialParams, materialDef, parser ) );

		} else if ( materialExtensions[ EXTENSIONS.KHR_MATERIALS_UNLIT ] ) {

			var kmuExtension = extensions[ EXTENSIONS.KHR_MATERIALS_UNLIT ];
			materialType = kmuExtension.getMaterialType( materialDef );
			pending.push( kmuExtension.extendParams( materialParams, materialDef, parser ) );

		} else {

			// Specification:
			// https://github.com/KhronosGroup/glTF/tree/master/specification/2.0#metallic-roughness-material

			materialType = THREE.MeshStandardMaterial;

			var metallicRoughness = materialDef.pbrMetallicRoughness || {};

			materialParams.color = new THREE.Color( 1.0, 1.0, 1.0 );
			materialParams.opacity = 1.0;

			if ( Array.isArray( metallicRoughness.baseColorFactor ) ) {

				var array = metallicRoughness.baseColorFactor;

				materialParams.color.fromArray( array );
				materialParams.opacity = array[ 3 ];

			}

			if ( metallicRoughness.baseColorTexture !== undefined ) {

				pending.push( parser.assignTexture( materialParams, 'map', metallicRoughness.baseColorTexture ) );

			}

			materialParams.metalness = metallicRoughness.metallicFactor !== undefined ? metallicRoughness.metallicFactor : 1.0;
			materialParams.roughness = metallicRoughness.roughnessFactor !== undefined ? metallicRoughness.roughnessFactor : 1.0;

			if ( metallicRoughness.metallicRoughnessTexture !== undefined ) {

				pending.push( parser.assignTexture( materialParams, 'metalnessMap', metallicRoughness.metallicRoughnessTexture ) );
				pending.push( parser.assignTexture( materialParams, 'roughnessMap', metallicRoughness.metallicRoughnessTexture ) );

			}

		}

		if ( materialDef.doubleSided === true ) {

			materialParams.side = THREE.DoubleSide;

		}

		var alphaMode = materialDef.alphaMode || ALPHA_MODES.OPAQUE;

		if ( alphaMode === ALPHA_MODES.BLEND ) {

			materialParams.transparent = true;

		} else {

			materialParams.transparent = false;

			if ( alphaMode === ALPHA_MODES.MASK ) {

				materialParams.alphaTest = materialDef.alphaCutoff !== undefined ? materialDef.alphaCutoff : 0.5;

			}

		}

		if ( materialDef.normalTexture !== undefined && materialType !== THREE.MeshBasicMaterial ) {

			pending.push( parser.assignTexture( materialParams, 'normalMap', materialDef.normalTexture ) );

			materialParams.normalScale = new THREE.Vector2( 1, 1 );

			if ( materialDef.normalTexture.scale !== undefined ) {

				materialParams.normalScale.set( materialDef.normalTexture.scale, materialDef.normalTexture.scale );

			}

		}

		if ( materialDef.occlusionTexture !== undefined && materialType !== THREE.MeshBasicMaterial ) {

			pending.push( parser.assignTexture( materialParams, 'aoMap', materialDef.occlusionTexture ) );

			if ( materialDef.occlusionTexture.strength !== undefined ) {

				materialParams.aoMapIntensity = materialDef.occlusionTexture.strength;

			}

		}

		if ( materialDef.emissiveFactor !== undefined && materialType !== THREE.MeshBasicMaterial ) {

			materialParams.emissive = new THREE.Color().fromArray( materialDef.emissiveFactor );

		}

		if ( materialDef.emissiveTexture !== undefined && materialType !== THREE.MeshBasicMaterial ) {

			pending.push( parser.assignTexture( materialParams, 'emissiveMap', materialDef.emissiveTexture ) );

		}

		return Promise.all( pending ).then( function () {

			var material;

			if ( materialType === THREE.ShaderMaterial ) {

				material = extensions[ EXTENSIONS.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS ].createMaterial( materialParams );

			} else {

				material = new materialType( materialParams );

			}

			if ( materialDef.name !== undefined ) material.name = materialDef.name;

			// Normal map textures use OpenGL conventions:
			// https://github.com/KhronosGroup/glTF/tree/master/specification/2.0#materialnormaltexture
			if ( material.normalScale ) {

				material.normalScale.y = - material.normalScale.y;

			}

			// baseColorTexture, emissiveTexture, and specularGlossinessTexture use sRGB encoding.
			if ( material.map ) material.map.encoding = THREE.sRGBEncoding;
			if ( material.emissiveMap ) material.emissiveMap.encoding = THREE.sRGBEncoding;
			if ( material.specularMap ) material.specularMap.encoding = THREE.sRGBEncoding;

			assignExtrasToUserData( material, materialDef );

			if ( materialDef.extensions ) addUnknownExtensionsToUserData( extensions, material, materialDef );

			return material;

		} );

	};

	/**
	 * @param {THREE.BufferGeometry} geometry
	 * @param {GLTF.Primitive} primitiveDef
	 * @param {GLTFParser} parser
	 * @return {Promise<THREE.BufferGeometry>}
	 */
	function addPrimitiveAttributes( geometry, primitiveDef, parser ) {

		var attributes = primitiveDef.attributes;

		var pending = [];

		function assignAttributeAccessor( accessorIndex, attributeName ) {

			return parser.getDependency( 'accessor', accessorIndex )
				.then( function ( accessor ) {

					geometry.addAttribute( attributeName, accessor );

				} );

		}

		for ( var gltfAttributeName in attributes ) {

			var threeAttributeName = ATTRIBUTES[ gltfAttributeName ];

			if ( ! threeAttributeName ) continue;

			// Skip attributes already provided by e.g. Draco extension.
			if ( threeAttributeName in geometry.attributes ) continue;

			pending.push( assignAttributeAccessor( attributes[ gltfAttributeName ], threeAttributeName ) );

		}

		if ( primitiveDef.indices !== undefined && ! geometry.index ) {

			var accessor = parser.getDependency( 'accessor', primitiveDef.indices ).then( function ( accessor ) {

				geometry.setIndex( accessor );

			} );

			pending.push( accessor );

		}

		assignExtrasToUserData( geometry, primitiveDef );

		return Promise.all( pending ).then( function () {

			return primitiveDef.targets !== undefined
				? addMorphTargets( geometry, primitiveDef.targets, parser )
				: geometry;

		} );

	}

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/blob/master/specification/2.0/README.md#geometry
	 *
	 * Creates BufferGeometries from primitives.
	 * If we can build a single BufferGeometry with .groups from multiple primitives, returns one BufferGeometry.
	 * Otherwise, returns BufferGeometries without .groups as many as primitives.
	 *
	 * @param {Array<GLTF.Primitive>} primitives
	 * @return {Promise<Array<THREE.BufferGeometry>>}
	 */
	GLTFParser.prototype.loadGeometries = function ( primitives ) {

		var parser = this;
		var extensions = this.extensions;
		var cache = this.primitiveCache;

		var isMultiPass = isMultiPassGeometry( primitives );
		var originalPrimitives;

		if ( isMultiPass ) {

			originalPrimitives = primitives; // save original primitives and use later

			// We build a single BufferGeometry with .groups from multiple primitives
			// because all primitives share the same attributes/morph/mode and have indices.

			primitives = [ primitives[ 0 ] ];

			// Sets .groups and combined indices to a geometry later in this method.

		}

		function createDracoPrimitive( primitive ) {

			return extensions[ EXTENSIONS.KHR_DRACO_MESH_COMPRESSION ]
				.decodePrimitive( primitive, parser )
				.then( function ( geometry ) {

					return addPrimitiveAttributes( geometry, primitive, parser );

				} );

		}

		var pending = [];

		for ( var i = 0, il = primitives.length; i < il; i ++ ) {

			var primitive = primitives[ i ];

			// See if we've already created this geometry
			var cached = getCachedGeometry( cache, primitive );

			if ( cached ) {

				// Use the cached geometry if it exists
				pending.push( cached );

			} else {

				var geometryPromise;

				if ( primitive.extensions && primitive.extensions[ EXTENSIONS.KHR_DRACO_MESH_COMPRESSION ] ) {

					// Use DRACO geometry if available
					geometryPromise = createDracoPrimitive( primitive );

				} else {

					// Otherwise create a new geometry
					geometryPromise = addPrimitiveAttributes( new THREE.BufferGeometry(), primitive, parser );

				}

				// Cache this geometry
				cache.push( { primitive: primitive, promise: geometryPromise } );

				pending.push( geometryPromise );

			}

		}

		return Promise.all( pending ).then( function ( geometries ) {

			if ( isMultiPass ) {

				var baseGeometry = geometries[ 0 ];

				// See if we've already created this combined geometry
				var cache = parser.multiPassGeometryCache;
				var cached = getCachedMultiPassGeometry( cache, baseGeometry, originalPrimitives );

				if ( cached !== null ) return [ cached.geometry ];

				// Cloning geometry because of index override.
				// Attributes can be reused so cloning by myself here.
				var geometry = new THREE.BufferGeometry();

				geometry.name = baseGeometry.name;
				geometry.userData = baseGeometry.userData;

				for ( var key in baseGeometry.attributes ) geometry.addAttribute( key, baseGeometry.attributes[ key ] );
				for ( var key in baseGeometry.morphAttributes ) geometry.morphAttributes[ key ] = baseGeometry.morphAttributes[ key ];

				var pendingIndices = [];

				for ( var i = 0, il = originalPrimitives.length; i < il; i ++ ) {

					pendingIndices.push( parser.getDependency( 'accessor', originalPrimitives[ i ].indices ) );

				}

				return Promise.all( pendingIndices ).then( function ( accessors ) {

					var indices = [];
					var offset = 0;

					for ( var i = 0, il = originalPrimitives.length; i < il; i ++ ) {

						var accessor = accessors[ i ];

						for ( var j = 0, jl = accessor.count; j < jl; j ++ ) indices.push( accessor.array[ j ] );

						geometry.addGroup( offset, accessor.count, i );

						offset += accessor.count;

					}

					geometry.setIndex( indices );

					cache.push( { geometry: geometry, baseGeometry: baseGeometry, primitives: originalPrimitives } );

					return [ geometry ];

				} );

			} else if ( geometries.length > 1 && THREE.BufferGeometryUtils !== undefined ) {

				// Tries to merge geometries with BufferGeometryUtils if possible

				for ( var i = 1, il = primitives.length; i < il; i ++ ) {

					// can't merge if draw mode is different
					if ( primitives[ 0 ].mode !== primitives[ i ].mode ) return geometries;

				}

				// See if we've already created this combined geometry
				var cache = parser.multiplePrimitivesCache;
				var cached = getCachedCombinedGeometry( cache, geometries );

				if ( cached ) {

					if ( cached.geometry !== null ) return [ cached.geometry ];

				} else {

					var geometry = THREE.BufferGeometryUtils.mergeBufferGeometries( geometries, true );

					cache.push( { geometry: geometry, baseGeometries: geometries } );

					if ( geometry !== null ) return [ geometry ];

				}

			}

			return geometries;

		} );

	};

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/blob/master/specification/2.0/README.md#meshes
	 * @param {number} meshIndex
	 * @return {Promise<THREE.Group|THREE.Mesh|THREE.SkinnedMesh>}
	 */
	GLTFParser.prototype.loadMesh = function ( meshIndex ) {

		var parser = this;
		var json = this.json;
		var extensions = this.extensions;

		var meshDef = json.meshes[ meshIndex ];
		var primitives = meshDef.primitives;

		var pending = [];

		for ( var i = 0, il = primitives.length; i < il; i ++ ) {

			var material = primitives[ i ].material === undefined
				? createDefaultMaterial()
				: this.getDependency( 'material', primitives[ i ].material );

			pending.push( material );

		}

		return Promise.all( pending ).then( function ( originalMaterials ) {

			return parser.loadGeometries( primitives ).then( function ( geometries ) {

				var isMultiMaterial = geometries.length === 1 && geometries[ 0 ].groups.length > 0;

				var meshes = [];

				for ( var i = 0, il = geometries.length; i < il; i ++ ) {

					var geometry = geometries[ i ];
					var primitive = primitives[ i ];

					// 1. create Mesh

					var mesh;

					var material = isMultiMaterial ? originalMaterials : originalMaterials[ i ];

					if ( primitive.mode === WEBGL_CONSTANTS.TRIANGLES ||
						primitive.mode === WEBGL_CONSTANTS.TRIANGLE_STRIP ||
						primitive.mode === WEBGL_CONSTANTS.TRIANGLE_FAN ||
						primitive.mode === undefined ) {

						// .isSkinnedMesh isn't in glTF spec. See .markDefs()
						mesh = meshDef.isSkinnedMesh === true
							? new THREE.SkinnedMesh( geometry, material )
							: new THREE.Mesh( geometry, material );

						if ( mesh.isSkinnedMesh === true ) mesh.normalizeSkinWeights(); // #15319

						if ( primitive.mode === WEBGL_CONSTANTS.TRIANGLE_STRIP ) {

							mesh.drawMode = THREE.TriangleStripDrawMode;

						} else if ( primitive.mode === WEBGL_CONSTANTS.TRIANGLE_FAN ) {

							mesh.drawMode = THREE.TriangleFanDrawMode;

						}

					} else if ( primitive.mode === WEBGL_CONSTANTS.LINES ) {

						mesh = new THREE.LineSegments( geometry, material );

					} else if ( primitive.mode === WEBGL_CONSTANTS.LINE_STRIP ) {

						mesh = new THREE.Line( geometry, material );

					} else if ( primitive.mode === WEBGL_CONSTANTS.LINE_LOOP ) {

						mesh = new THREE.LineLoop( geometry, material );

					} else if ( primitive.mode === WEBGL_CONSTANTS.POINTS ) {

						mesh = new THREE.Points( geometry, material );

					} else {

						throw new Error( 'THREE.GLTFLoader: Primitive mode unsupported: ' + primitive.mode );

					}

					if ( Object.keys( mesh.geometry.morphAttributes ).length > 0 ) {

						updateMorphTargets( mesh, meshDef );

					}

					mesh.name = meshDef.name || ( 'mesh_' + meshIndex );

					if ( geometries.length > 1 ) mesh.name += '_' + i;

					assignExtrasToUserData( mesh, meshDef );

					meshes.push( mesh );

					// 2. update Material depending on Mesh and BufferGeometry

					var materials = isMultiMaterial ? mesh.material : [ mesh.material ];

					var useVertexColors = geometry.attributes.color !== undefined;
					var useFlatShading = geometry.attributes.normal === undefined;
					var useSkinning = mesh.isSkinnedMesh === true;
					var useMorphTargets = Object.keys( geometry.morphAttributes ).length > 0;
					var useMorphNormals = useMorphTargets && geometry.morphAttributes.normal !== undefined;

					for ( var j = 0, jl = materials.length; j < jl; j ++ ) {

						var material = materials[ j ];

						if ( mesh.isPoints ) {

							var cacheKey = 'PointsMaterial:' + material.uuid;

							var pointsMaterial = parser.cache.get( cacheKey );

							if ( ! pointsMaterial ) {

								pointsMaterial = new THREE.PointsMaterial();
								THREE.Material.prototype.copy.call( pointsMaterial, material );
								pointsMaterial.color.copy( material.color );
								pointsMaterial.map = material.map;
								pointsMaterial.lights = false; // PointsMaterial doesn't support lights yet

								parser.cache.add( cacheKey, pointsMaterial );

							}

							material = pointsMaterial;

						} else if ( mesh.isLine ) {

							var cacheKey = 'LineBasicMaterial:' + material.uuid;

							var lineMaterial = parser.cache.get( cacheKey );

							if ( ! lineMaterial ) {

								lineMaterial = new THREE.LineBasicMaterial();
								THREE.Material.prototype.copy.call( lineMaterial, material );
								lineMaterial.color.copy( material.color );
								lineMaterial.lights = false; // LineBasicMaterial doesn't support lights yet

								parser.cache.add( cacheKey, lineMaterial );

							}

							material = lineMaterial;

						}

						// Clone the material if it will be modified
						if ( useVertexColors || useFlatShading || useSkinning || useMorphTargets ) {

							var cacheKey = 'ClonedMaterial:' + material.uuid + ':';

							if ( material.isGLTFSpecularGlossinessMaterial ) cacheKey += 'specular-glossiness:';
							if ( useSkinning ) cacheKey += 'skinning:';
							if ( useVertexColors ) cacheKey += 'vertex-colors:';
							if ( useFlatShading ) cacheKey += 'flat-shading:';
							if ( useMorphTargets ) cacheKey += 'morph-targets:';
							if ( useMorphNormals ) cacheKey += 'morph-normals:';

							var cachedMaterial = parser.cache.get( cacheKey );

							if ( ! cachedMaterial ) {

								cachedMaterial = material.isGLTFSpecularGlossinessMaterial
									? extensions[ EXTENSIONS.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS ].cloneMaterial( material )
									: material.clone();

								if ( useSkinning ) cachedMaterial.skinning = true;
								if ( useVertexColors ) cachedMaterial.vertexColors = THREE.VertexColors;
								if ( useFlatShading ) cachedMaterial.flatShading = true;
								if ( useMorphTargets ) cachedMaterial.morphTargets = true;
								if ( useMorphNormals ) cachedMaterial.morphNormals = true;

								parser.cache.add( cacheKey, cachedMaterial );

							}

							material = cachedMaterial;

						}

						materials[ j ] = material;

						// workarounds for mesh and geometry

						if ( material.aoMap && geometry.attributes.uv2 === undefined && geometry.attributes.uv !== undefined ) {

							console.log( 'THREE.GLTFLoader: Duplicating UVs to support aoMap.' );
							geometry.addAttribute( 'uv2', new THREE.BufferAttribute( geometry.attributes.uv.array, 2 ) );

						}

						if ( material.isGLTFSpecularGlossinessMaterial ) {

							// for GLTFSpecularGlossinessMaterial(ShaderMaterial) uniforms runtime update
							mesh.onBeforeRender = extensions[ EXTENSIONS.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS ].refreshUniforms;

						}

					}

					mesh.material = isMultiMaterial ? materials : materials[ 0 ];

				}

				if ( meshes.length === 1 ) {

					return meshes[ 0 ];

				}

				var group = new THREE.Group();

				for ( var i = 0, il = meshes.length; i < il; i ++ ) {

					group.add( meshes[ i ] );

				}

				return group;

			} );

		} );

	};

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/tree/master/specification/2.0#cameras
	 * @param {number} cameraIndex
	 * @return {Promise<THREE.Camera>}
	 */
	GLTFParser.prototype.loadCamera = function ( cameraIndex ) {

		var camera;
		var cameraDef = this.json.cameras[ cameraIndex ];
		var params = cameraDef[ cameraDef.type ];

		if ( ! params ) {

			console.warn( 'THREE.GLTFLoader: Missing camera parameters.' );
			return;

		}

		if ( cameraDef.type === 'perspective' ) {

			camera = new THREE.PerspectiveCamera( THREE.Math.radToDeg( params.yfov ), params.aspectRatio || 1, params.znear || 1, params.zfar || 2e6 );

		} else if ( cameraDef.type === 'orthographic' ) {

			camera = new THREE.OrthographicCamera( params.xmag / - 2, params.xmag / 2, params.ymag / 2, params.ymag / - 2, params.znear, params.zfar );

		}

		if ( cameraDef.name !== undefined ) camera.name = cameraDef.name;

		assignExtrasToUserData( camera, cameraDef );

		return Promise.resolve( camera );

	};

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/tree/master/specification/2.0#skins
	 * @param {number} skinIndex
	 * @return {Promise<Object>}
	 */
	GLTFParser.prototype.loadSkin = function ( skinIndex ) {

		var skinDef = this.json.skins[ skinIndex ];

		var skinEntry = { joints: skinDef.joints };

		if ( skinDef.inverseBindMatrices === undefined ) {

			return Promise.resolve( skinEntry );

		}

		return this.getDependency( 'accessor', skinDef.inverseBindMatrices ).then( function ( accessor ) {

			skinEntry.inverseBindMatrices = accessor;

			return skinEntry;

		} );

	};

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/tree/master/specification/2.0#animations
	 * @param {number} animationIndex
	 * @return {Promise<THREE.AnimationClip>}
	 */
	GLTFParser.prototype.loadAnimation = function ( animationIndex ) {

		var json = this.json;

		var animationDef = json.animations[ animationIndex ];

		var pendingNodes = [];
		var pendingInputAccessors = [];
		var pendingOutputAccessors = [];
		var pendingSamplers = [];
		var pendingTargets = [];

		for ( var i = 0, il = animationDef.channels.length; i < il; i ++ ) {

			var channel = animationDef.channels[ i ];
			var sampler = animationDef.samplers[ channel.sampler ];
			var target = channel.target;
			var name = target.node !== undefined ? target.node : target.id; // NOTE: target.id is deprecated.
			var input = animationDef.parameters !== undefined ? animationDef.parameters[ sampler.input ] : sampler.input;
			var output = animationDef.parameters !== undefined ? animationDef.parameters[ sampler.output ] : sampler.output;

			pendingNodes.push( this.getDependency( 'node', name ) );
			pendingInputAccessors.push( this.getDependency( 'accessor', input ) );
			pendingOutputAccessors.push( this.getDependency( 'accessor', output ) );
			pendingSamplers.push( sampler );
			pendingTargets.push( target );

		}

		return Promise.all( [

			Promise.all( pendingNodes ),
			Promise.all( pendingInputAccessors ),
			Promise.all( pendingOutputAccessors ),
			Promise.all( pendingSamplers ),
			Promise.all( pendingTargets )

		] ).then( function ( dependencies ) {

			var nodes = dependencies[ 0 ];
			var inputAccessors = dependencies[ 1 ];
			var outputAccessors = dependencies[ 2 ];
			var samplers = dependencies[ 3 ];
			var targets = dependencies[ 4 ];

			var tracks = [];

			for ( var i = 0, il = nodes.length; i < il; i ++ ) {

				var node = nodes[ i ];
				var inputAccessor = inputAccessors[ i ];
				var outputAccessor = outputAccessors[ i ];
				var sampler = samplers[ i ];
				var target = targets[ i ];

				if ( node === undefined ) continue;

				node.updateMatrix();
				node.matrixAutoUpdate = true;

				var TypedKeyframeTrack;

				switch ( PATH_PROPERTIES[ target.path ] ) {

					case PATH_PROPERTIES.weights:

						TypedKeyframeTrack = THREE.NumberKeyframeTrack;
						break;

					case PATH_PROPERTIES.rotation:

						TypedKeyframeTrack = THREE.QuaternionKeyframeTrack;
						break;

					case PATH_PROPERTIES.position:
					case PATH_PROPERTIES.scale:
					default:

						TypedKeyframeTrack = THREE.VectorKeyframeTrack;
						break;

				}

				var targetName = node.name ? node.name : node.uuid;

				var interpolation = sampler.interpolation !== undefined ? INTERPOLATION[ sampler.interpolation ] : THREE.InterpolateLinear;

				var targetNames = [];

				if ( PATH_PROPERTIES[ target.path ] === PATH_PROPERTIES.weights ) {

					// node can be THREE.Group here but
					// PATH_PROPERTIES.weights(morphTargetInfluences) should be
					// the property of a mesh object under group.

					node.traverse( function ( object ) {

						if ( object.isMesh === true && object.morphTargetInfluences ) {

							targetNames.push( object.name ? object.name : object.uuid );

						}

					} );

				} else {

					targetNames.push( targetName );

				}

				// KeyframeTrack.optimize() will modify given 'times' and 'values'
				// buffers before creating a truncated copy to keep. Because buffers may
				// be reused by other tracks, make copies here.
				for ( var j = 0, jl = targetNames.length; j < jl; j ++ ) {

					var track = new TypedKeyframeTrack(
						targetNames[ j ] + '.' + PATH_PROPERTIES[ target.path ],
						THREE.AnimationUtils.arraySlice( inputAccessor.array, 0 ),
						THREE.AnimationUtils.arraySlice( outputAccessor.array, 0 ),
						interpolation
					);

					// Here is the trick to enable custom interpolation.
					// Overrides .createInterpolant in a factory method which creates custom interpolation.
					if ( sampler.interpolation === 'CUBICSPLINE' ) {

						track.createInterpolant = function InterpolantFactoryMethodGLTFCubicSpline( result ) {

							// A CUBICSPLINE keyframe in glTF has three output values for each input value,
							// representing inTangent, splineVertex, and outTangent. As a result, track.getValueSize()
							// must be divided by three to get the interpolant's sampleSize argument.

							return new GLTFCubicSplineInterpolant( this.times, this.values, this.getValueSize() / 3, result );

						};

						// Workaround, provide an alternate way to know if the interpolant type is cubis spline to track.
						// track.getInterpolation() doesn't return valid value for custom interpolant.
						track.createInterpolant.isInterpolantFactoryMethodGLTFCubicSpline = true;

					}

					tracks.push( track );

				}

			}

			var name = animationDef.name !== undefined ? animationDef.name : 'animation_' + animationIndex;

			return new THREE.AnimationClip( name, undefined, tracks );

		} );

	};

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/tree/master/specification/2.0#nodes-and-hierarchy
	 * @param {number} nodeIndex
	 * @return {Promise<THREE.Object3D>}
	 */
	GLTFParser.prototype.loadNode = function ( nodeIndex ) {

		var json = this.json;
		var extensions = this.extensions;
		var parser = this;

		var meshReferences = json.meshReferences;
		var meshUses = json.meshUses;

		var nodeDef = json.nodes[ nodeIndex ];

		return ( function() {

			// .isBone isn't in glTF spec. See .markDefs
			if ( nodeDef.isBone === true ) {

				return Promise.resolve( new THREE.Bone() );

			} else if ( nodeDef.mesh !== undefined ) {

				return parser.getDependency( 'mesh', nodeDef.mesh ).then( function ( mesh ) {

					var node;

					if ( meshReferences[ nodeDef.mesh ] > 1 ) {

						var instanceNum = meshUses[ nodeDef.mesh ] ++;

						node = mesh.clone();
						node.name += '_instance_' + instanceNum;

						// onBeforeRender copy for Specular-Glossiness
						node.onBeforeRender = mesh.onBeforeRender;

						for ( var i = 0, il = node.children.length; i < il; i ++ ) {

							node.children[ i ].name += '_instance_' + instanceNum;
							node.children[ i ].onBeforeRender = mesh.children[ i ].onBeforeRender;

						}

					} else {

						node = mesh;

					}

					// if weights are provided on the node, override weights on the mesh.
					if ( nodeDef.weights !== undefined ) {

						node.traverse( function ( o ) {

							if ( ! o.isMesh ) return;

							for ( var i = 0, il = nodeDef.weights.length; i < il; i ++ ) {

								o.morphTargetInfluences[ i ] = nodeDef.weights[ i ];

							}

						} );

					}

					return node;

				} );

			} else if ( nodeDef.camera !== undefined ) {

				return parser.getDependency( 'camera', nodeDef.camera );

			} else if ( nodeDef.extensions
				&& nodeDef.extensions[ EXTENSIONS.KHR_LIGHTS_PUNCTUAL ]
				&& nodeDef.extensions[ EXTENSIONS.KHR_LIGHTS_PUNCTUAL ].light !== undefined ) {

				return parser.getDependency( 'light', nodeDef.extensions[ EXTENSIONS.KHR_LIGHTS_PUNCTUAL ].light );

			} else {

				return Promise.resolve( new THREE.Object3D() );

			}

		}() ).then( function ( node ) {

			if ( nodeDef.name !== undefined ) {

				node.name = THREE.PropertyBinding.sanitizeNodeName( nodeDef.name );

			}

			assignExtrasToUserData( node, nodeDef );

			if ( nodeDef.extensions ) addUnknownExtensionsToUserData( extensions, node, nodeDef );

			if ( nodeDef.matrix !== undefined ) {

				var matrix = new THREE.Matrix4();
				matrix.fromArray( nodeDef.matrix );
				node.applyMatrix( matrix );

			} else {

				if ( nodeDef.translation !== undefined ) {

					node.position.fromArray( nodeDef.translation );

				}

				if ( nodeDef.rotation !== undefined ) {

					node.quaternion.fromArray( nodeDef.rotation );

				}

				if ( nodeDef.scale !== undefined ) {

					node.scale.fromArray( nodeDef.scale );

				}

			}

			return node;

		} );

	};

	/**
	 * Specification: https://github.com/KhronosGroup/glTF/tree/master/specification/2.0#scenes
	 * @param {number} sceneIndex
	 * @return {Promise<THREE.Scene>}
	 */
	GLTFParser.prototype.loadScene = function () {

		// scene node hierachy builder

		function buildNodeHierachy( nodeId, parentObject, json, parser ) {

			var nodeDef = json.nodes[ nodeId ];

			return parser.getDependency( 'node', nodeId ).then( function ( node ) {

				if ( nodeDef.skin === undefined ) return node;

				// build skeleton here as well

				var skinEntry;

				return parser.getDependency( 'skin', nodeDef.skin ).then( function ( skin ) {

					skinEntry = skin;

					var pendingJoints = [];

					for ( var i = 0, il = skinEntry.joints.length; i < il; i ++ ) {

						pendingJoints.push( parser.getDependency( 'node', skinEntry.joints[ i ] ) );

					}

					return Promise.all( pendingJoints );

				} ).then( function ( jointNodes ) {

					var meshes = node.isGroup === true ? node.children : [ node ];

					for ( var i = 0, il = meshes.length; i < il; i ++ ) {

						var mesh = meshes[ i ];

						var bones = [];
						var boneInverses = [];

						for ( var j = 0, jl = jointNodes.length; j < jl; j ++ ) {

							var jointNode = jointNodes[ j ];

							if ( jointNode ) {

								bones.push( jointNode );

								var mat = new THREE.Matrix4();

								if ( skinEntry.inverseBindMatrices !== undefined ) {

									mat.fromArray( skinEntry.inverseBindMatrices.array, j * 16 );

								}

								boneInverses.push( mat );

							} else {

								console.warn( 'THREE.GLTFLoader: Joint "%s" could not be found.', skinEntry.joints[ j ] );

							}

						}

						mesh.bind( new THREE.Skeleton( bones, boneInverses ), mesh.matrixWorld );

					};

					return node;

				} );

			} ).then( function ( node ) {

				// build node hierachy

				parentObject.add( node );

				var pending = [];

				if ( nodeDef.children ) {

					var children = nodeDef.children;

					for ( var i = 0, il = children.length; i < il; i ++ ) {

						var child = children[ i ];
						pending.push( buildNodeHierachy( child, node, json, parser ) );

					}

				}

				return Promise.all( pending );

			} );

		}

		return function loadScene( sceneIndex ) {

			var json = this.json;
			var extensions = this.extensions;
			var sceneDef = this.json.scenes[ sceneIndex ];
			var parser = this;

			var scene = new THREE.Scene();
			if ( sceneDef.name !== undefined ) scene.name = sceneDef.name;

			assignExtrasToUserData( scene, sceneDef );

			if ( sceneDef.extensions ) addUnknownExtensionsToUserData( extensions, scene, sceneDef );

			var nodeIds = sceneDef.nodes || [];

			var pending = [];

			for ( var i = 0, il = nodeIds.length; i < il; i ++ ) {

				pending.push( buildNodeHierachy( nodeIds[ i ], scene, json, parser ) );

			}

			return Promise.all( pending ).then( function () {

				return scene;

			} );

		};

	}();

	return GLTFLoader;

} )();


/***/ }),

/***/ "../../node_modules/three/examples/js/loaders/OBJLoader.js":
/*!****************************************************************!*\
  !*** /app/node_modules/three/examples/js/loaders/OBJLoader.js ***!
  \****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * @author mrdoob / http://mrdoob.com/
 */

THREE.OBJLoader = ( function () {

	// o object_name | g group_name
	var object_pattern = /^[og]\s*(.+)?/;
	// mtllib file_reference
	var material_library_pattern = /^mtllib /;
	// usemtl material_name
	var material_use_pattern = /^usemtl /;

	function ParserState() {

		var state = {
			objects: [],
			object: {},

			vertices: [],
			normals: [],
			colors: [],
			uvs: [],

			materialLibraries: [],

			startObject: function ( name, fromDeclaration ) {

				// If the current object (initial from reset) is not from a g/o declaration in the parsed
				// file. We need to use it for the first parsed g/o to keep things in sync.
				if ( this.object && this.object.fromDeclaration === false ) {

					this.object.name = name;
					this.object.fromDeclaration = ( fromDeclaration !== false );
					return;

				}

				var previousMaterial = ( this.object && typeof this.object.currentMaterial === 'function' ? this.object.currentMaterial() : undefined );

				if ( this.object && typeof this.object._finalize === 'function' ) {

					this.object._finalize( true );

				}

				this.object = {
					name: name || '',
					fromDeclaration: ( fromDeclaration !== false ),

					geometry: {
						vertices: [],
						normals: [],
						colors: [],
						uvs: []
					},
					materials: [],
					smooth: true,

					startMaterial: function ( name, libraries ) {

						var previous = this._finalize( false );

						// New usemtl declaration overwrites an inherited material, except if faces were declared
						// after the material, then it must be preserved for proper MultiMaterial continuation.
						if ( previous && ( previous.inherited || previous.groupCount <= 0 ) ) {

							this.materials.splice( previous.index, 1 );

						}

						var material = {
							index: this.materials.length,
							name: name || '',
							mtllib: ( Array.isArray( libraries ) && libraries.length > 0 ? libraries[ libraries.length - 1 ] : '' ),
							smooth: ( previous !== undefined ? previous.smooth : this.smooth ),
							groupStart: ( previous !== undefined ? previous.groupEnd : 0 ),
							groupEnd: - 1,
							groupCount: - 1,
							inherited: false,

							clone: function ( index ) {

								var cloned = {
									index: ( typeof index === 'number' ? index : this.index ),
									name: this.name,
									mtllib: this.mtllib,
									smooth: this.smooth,
									groupStart: 0,
									groupEnd: - 1,
									groupCount: - 1,
									inherited: false
								};
								cloned.clone = this.clone.bind( cloned );
								return cloned;

							}
						};

						this.materials.push( material );

						return material;

					},

					currentMaterial: function () {

						if ( this.materials.length > 0 ) {

							return this.materials[ this.materials.length - 1 ];

						}

						return undefined;

					},

					_finalize: function ( end ) {

						var lastMultiMaterial = this.currentMaterial();
						if ( lastMultiMaterial && lastMultiMaterial.groupEnd === - 1 ) {

							lastMultiMaterial.groupEnd = this.geometry.vertices.length / 3;
							lastMultiMaterial.groupCount = lastMultiMaterial.groupEnd - lastMultiMaterial.groupStart;
							lastMultiMaterial.inherited = false;

						}

						// Ignore objects tail materials if no face declarations followed them before a new o/g started.
						if ( end && this.materials.length > 1 ) {

							for ( var mi = this.materials.length - 1; mi >= 0; mi -- ) {

								if ( this.materials[ mi ].groupCount <= 0 ) {

									this.materials.splice( mi, 1 );

								}

							}

						}

						// Guarantee at least one empty material, this makes the creation later more straight forward.
						if ( end && this.materials.length === 0 ) {

							this.materials.push( {
								name: '',
								smooth: this.smooth
							} );

						}

						return lastMultiMaterial;

					}
				};

				// Inherit previous objects material.
				// Spec tells us that a declared material must be set to all objects until a new material is declared.
				// If a usemtl declaration is encountered while this new object is being parsed, it will
				// overwrite the inherited material. Exception being that there was already face declarations
				// to the inherited material, then it will be preserved for proper MultiMaterial continuation.

				if ( previousMaterial && previousMaterial.name && typeof previousMaterial.clone === 'function' ) {

					var declared = previousMaterial.clone( 0 );
					declared.inherited = true;
					this.object.materials.push( declared );

				}

				this.objects.push( this.object );

			},

			finalize: function () {

				if ( this.object && typeof this.object._finalize === 'function' ) {

					this.object._finalize( true );

				}

			},

			parseVertexIndex: function ( value, len ) {

				var index = parseInt( value, 10 );
				return ( index >= 0 ? index - 1 : index + len / 3 ) * 3;

			},

			parseNormalIndex: function ( value, len ) {

				var index = parseInt( value, 10 );
				return ( index >= 0 ? index - 1 : index + len / 3 ) * 3;

			},

			parseUVIndex: function ( value, len ) {

				var index = parseInt( value, 10 );
				return ( index >= 0 ? index - 1 : index + len / 2 ) * 2;

			},

			addVertex: function ( a, b, c ) {

				var src = this.vertices;
				var dst = this.object.geometry.vertices;

				dst.push( src[ a + 0 ], src[ a + 1 ], src[ a + 2 ] );
				dst.push( src[ b + 0 ], src[ b + 1 ], src[ b + 2 ] );
				dst.push( src[ c + 0 ], src[ c + 1 ], src[ c + 2 ] );

			},

			addVertexPoint: function ( a ) {

				var src = this.vertices;
				var dst = this.object.geometry.vertices;

				dst.push( src[ a + 0 ], src[ a + 1 ], src[ a + 2 ] );

			},

			addVertexLine: function ( a ) {

				var src = this.vertices;
				var dst = this.object.geometry.vertices;

				dst.push( src[ a + 0 ], src[ a + 1 ], src[ a + 2 ] );

			},

			addNormal: function ( a, b, c ) {

				var src = this.normals;
				var dst = this.object.geometry.normals;

				dst.push( src[ a + 0 ], src[ a + 1 ], src[ a + 2 ] );
				dst.push( src[ b + 0 ], src[ b + 1 ], src[ b + 2 ] );
				dst.push( src[ c + 0 ], src[ c + 1 ], src[ c + 2 ] );

			},

			addColor: function ( a, b, c ) {

				var src = this.colors;
				var dst = this.object.geometry.colors;

				dst.push( src[ a + 0 ], src[ a + 1 ], src[ a + 2 ] );
				dst.push( src[ b + 0 ], src[ b + 1 ], src[ b + 2 ] );
				dst.push( src[ c + 0 ], src[ c + 1 ], src[ c + 2 ] );

			},

			addUV: function ( a, b, c ) {

				var src = this.uvs;
				var dst = this.object.geometry.uvs;

				dst.push( src[ a + 0 ], src[ a + 1 ] );
				dst.push( src[ b + 0 ], src[ b + 1 ] );
				dst.push( src[ c + 0 ], src[ c + 1 ] );

			},

			addUVLine: function ( a ) {

				var src = this.uvs;
				var dst = this.object.geometry.uvs;

				dst.push( src[ a + 0 ], src[ a + 1 ] );

			},

			addFace: function ( a, b, c, ua, ub, uc, na, nb, nc ) {

				var vLen = this.vertices.length;

				var ia = this.parseVertexIndex( a, vLen );
				var ib = this.parseVertexIndex( b, vLen );
				var ic = this.parseVertexIndex( c, vLen );

				this.addVertex( ia, ib, ic );

				if ( ua !== undefined && ua !== '' ) {

					var uvLen = this.uvs.length;
					ia = this.parseUVIndex( ua, uvLen );
					ib = this.parseUVIndex( ub, uvLen );
					ic = this.parseUVIndex( uc, uvLen );
					this.addUV( ia, ib, ic );

				}

				if ( na !== undefined && na !== '' ) {

					// Normals are many times the same. If so, skip function call and parseInt.
					var nLen = this.normals.length;
					ia = this.parseNormalIndex( na, nLen );

					ib = na === nb ? ia : this.parseNormalIndex( nb, nLen );
					ic = na === nc ? ia : this.parseNormalIndex( nc, nLen );

					this.addNormal( ia, ib, ic );

				}

				if ( this.colors.length > 0 ) {

					this.addColor( ia, ib, ic );

				}

			},

			addPointGeometry: function ( vertices ) {

				this.object.geometry.type = 'Points';

				var vLen = this.vertices.length;

				for ( var vi = 0, l = vertices.length; vi < l; vi ++ ) {

					this.addVertexPoint( this.parseVertexIndex( vertices[ vi ], vLen ) );

				}

			},

			addLineGeometry: function ( vertices, uvs ) {

				this.object.geometry.type = 'Line';

				var vLen = this.vertices.length;
				var uvLen = this.uvs.length;

				for ( var vi = 0, l = vertices.length; vi < l; vi ++ ) {

					this.addVertexLine( this.parseVertexIndex( vertices[ vi ], vLen ) );

				}

				for ( var uvi = 0, l = uvs.length; uvi < l; uvi ++ ) {

					this.addUVLine( this.parseUVIndex( uvs[ uvi ], uvLen ) );

				}

			}

		};

		state.startObject( '', false );

		return state;

	}

	//

	function OBJLoader( manager ) {

		this.manager = ( manager !== undefined ) ? manager : THREE.DefaultLoadingManager;

		this.materials = null;

	}

	OBJLoader.prototype = {

		constructor: OBJLoader,

		load: function ( url, onLoad, onProgress, onError ) {

			var scope = this;

			var loader = new THREE.FileLoader( scope.manager );
			loader.setPath( this.path );
			loader.load( url, function ( text ) {

				onLoad( scope.parse( text ) );

			}, onProgress, onError );

		},

		setPath: function ( value ) {

			this.path = value;

			return this;

		},

		setMaterials: function ( materials ) {

			this.materials = materials;

			return this;

		},

		parse: function ( text ) {

			console.time( 'OBJLoader' );

			var state = new ParserState();

			if ( text.indexOf( '\r\n' ) !== - 1 ) {

				// This is faster than String.split with regex that splits on both
				text = text.replace( /\r\n/g, '\n' );

			}

			if ( text.indexOf( '\\\n' ) !== - 1 ) {

				// join lines separated by a line continuation character (\)
				text = text.replace( /\\\n/g, '' );

			}

			var lines = text.split( '\n' );
			var line = '', lineFirstChar = '';
			var lineLength = 0;
			var result = [];

			// Faster to just trim left side of the line. Use if available.
			var trimLeft = ( typeof ''.trimLeft === 'function' );

			for ( var i = 0, l = lines.length; i < l; i ++ ) {

				line = lines[ i ];

				line = trimLeft ? line.trimLeft() : line.trim();

				lineLength = line.length;

				if ( lineLength === 0 ) continue;

				lineFirstChar = line.charAt( 0 );

				// @todo invoke passed in handler if any
				if ( lineFirstChar === '#' ) continue;

				if ( lineFirstChar === 'v' ) {

					var data = line.split( /\s+/ );

					switch ( data[ 0 ] ) {

						case 'v':
							state.vertices.push(
								parseFloat( data[ 1 ] ),
								parseFloat( data[ 2 ] ),
								parseFloat( data[ 3 ] )
							);
							if ( data.length === 8 ) {

								state.colors.push(
									parseFloat( data[ 4 ] ),
									parseFloat( data[ 5 ] ),
									parseFloat( data[ 6 ] )

								);

							}
							break;
						case 'vn':
							state.normals.push(
								parseFloat( data[ 1 ] ),
								parseFloat( data[ 2 ] ),
								parseFloat( data[ 3 ] )
							);
							break;
						case 'vt':
							state.uvs.push(
								parseFloat( data[ 1 ] ),
								parseFloat( data[ 2 ] )
							);
							break;

					}

				} else if ( lineFirstChar === 'f' ) {

					var lineData = line.substr( 1 ).trim();
					var vertexData = lineData.split( /\s+/ );
					var faceVertices = [];

					// Parse the face vertex data into an easy to work with format

					for ( var j = 0, jl = vertexData.length; j < jl; j ++ ) {

						var vertex = vertexData[ j ];

						if ( vertex.length > 0 ) {

							var vertexParts = vertex.split( '/' );
							faceVertices.push( vertexParts );

						}

					}

					// Draw an edge between the first vertex and all subsequent vertices to form an n-gon

					var v1 = faceVertices[ 0 ];

					for ( var j = 1, jl = faceVertices.length - 1; j < jl; j ++ ) {

						var v2 = faceVertices[ j ];
						var v3 = faceVertices[ j + 1 ];

						state.addFace(
							v1[ 0 ], v2[ 0 ], v3[ 0 ],
							v1[ 1 ], v2[ 1 ], v3[ 1 ],
							v1[ 2 ], v2[ 2 ], v3[ 2 ]
						);

					}

				} else if ( lineFirstChar === 'l' ) {

					var lineParts = line.substring( 1 ).trim().split( " " );
					var lineVertices = [], lineUVs = [];

					if ( line.indexOf( "/" ) === - 1 ) {

						lineVertices = lineParts;

					} else {

						for ( var li = 0, llen = lineParts.length; li < llen; li ++ ) {

							var parts = lineParts[ li ].split( "/" );

							if ( parts[ 0 ] !== "" ) lineVertices.push( parts[ 0 ] );
							if ( parts[ 1 ] !== "" ) lineUVs.push( parts[ 1 ] );

						}

					}
					state.addLineGeometry( lineVertices, lineUVs );

				} else if ( lineFirstChar === 'p' ) {

					var lineData = line.substr( 1 ).trim();
					var pointData = lineData.split( " " );

					state.addPointGeometry( pointData );

				} else if ( ( result = object_pattern.exec( line ) ) !== null ) {

					// o object_name
					// or
					// g group_name

					// WORKAROUND: https://bugs.chromium.org/p/v8/issues/detail?id=2869
					// var name = result[ 0 ].substr( 1 ).trim();
					var name = ( " " + result[ 0 ].substr( 1 ).trim() ).substr( 1 );

					state.startObject( name );

				} else if ( material_use_pattern.test( line ) ) {

					// material

					state.object.startMaterial( line.substring( 7 ).trim(), state.materialLibraries );

				} else if ( material_library_pattern.test( line ) ) {

					// mtl file

					state.materialLibraries.push( line.substring( 7 ).trim() );

				} else if ( lineFirstChar === 's' ) {

					result = line.split( ' ' );

					// smooth shading

					// @todo Handle files that have varying smooth values for a set of faces inside one geometry,
					// but does not define a usemtl for each face set.
					// This should be detected and a dummy material created (later MultiMaterial and geometry groups).
					// This requires some care to not create extra material on each smooth value for "normal" obj files.
					// where explicit usemtl defines geometry groups.
					// Example asset: examples/models/obj/cerberus/Cerberus.obj

					/*
					 * http://paulbourke.net/dataformats/obj/
					 * or
					 * http://www.cs.utah.edu/~boulos/cs3505/obj_spec.pdf
					 *
					 * From chapter "Grouping" Syntax explanation "s group_number":
					 * "group_number is the smoothing group number. To turn off smoothing groups, use a value of 0 or off.
					 * Polygonal elements use group numbers to put elements in different smoothing groups. For free-form
					 * surfaces, smoothing groups are either turned on or off; there is no difference between values greater
					 * than 0."
					 */
					if ( result.length > 1 ) {

						var value = result[ 1 ].trim().toLowerCase();
						state.object.smooth = ( value !== '0' && value !== 'off' );

					} else {

						// ZBrush can produce "s" lines #11707
						state.object.smooth = true;

					}
					var material = state.object.currentMaterial();
					if ( material ) material.smooth = state.object.smooth;

				} else {

					// Handle null terminated files without exception
					if ( line === '\0' ) continue;

					throw new Error( 'THREE.OBJLoader: Unexpected line: "' + line + '"' );

				}

			}

			state.finalize();

			var container = new THREE.Group();
			container.materialLibraries = [].concat( state.materialLibraries );

			for ( var i = 0, l = state.objects.length; i < l; i ++ ) {

				var object = state.objects[ i ];
				var geometry = object.geometry;
				var materials = object.materials;
				var isLine = ( geometry.type === 'Line' );
				var isPoints = ( geometry.type === 'Points' );
				var hasVertexColors = false;

				// Skip o/g line declarations that did not follow with any faces
				if ( geometry.vertices.length === 0 ) continue;

				var buffergeometry = new THREE.BufferGeometry();

				buffergeometry.addAttribute( 'position', new THREE.Float32BufferAttribute( geometry.vertices, 3 ) );

				if ( geometry.normals.length > 0 ) {

					buffergeometry.addAttribute( 'normal', new THREE.Float32BufferAttribute( geometry.normals, 3 ) );

				} else {

					buffergeometry.computeVertexNormals();

				}

				if ( geometry.colors.length > 0 ) {

					hasVertexColors = true;
					buffergeometry.addAttribute( 'color', new THREE.Float32BufferAttribute( geometry.colors, 3 ) );

				}

				if ( geometry.uvs.length > 0 ) {

					buffergeometry.addAttribute( 'uv', new THREE.Float32BufferAttribute( geometry.uvs, 2 ) );

				}

				// Create materials

				var createdMaterials = [];

				for ( var mi = 0, miLen = materials.length; mi < miLen; mi ++ ) {

					var sourceMaterial = materials[ mi ];
					var material = undefined;

					if ( this.materials !== null ) {

						material = this.materials.create( sourceMaterial.name );

						// mtl etc. loaders probably can't create line materials correctly, copy properties to a line material.
						if ( isLine && material && ! ( material instanceof THREE.LineBasicMaterial ) ) {

							var materialLine = new THREE.LineBasicMaterial();
							THREE.Material.prototype.copy.call( materialLine, material );
							materialLine.color.copy( material.color );
							materialLine.lights = false;
							material = materialLine;

						} else if ( isPoints && material && ! ( material instanceof THREE.PointsMaterial ) ) {

							var materialPoints = new THREE.PointsMaterial( { size: 10, sizeAttenuation: false } );
							THREE.Material.prototype.copy.call( materialPoints, material );
							materialPoints.color.copy( material.color );
							materialPoints.map = material.map;
							materialPoints.lights = false;
							material = materialPoints;

						}

					}

					if ( ! material ) {

						if ( isLine ) {

							material = new THREE.LineBasicMaterial();

						} else if ( isPoints ) {

							material = new THREE.PointsMaterial( { size: 1, sizeAttenuation: false } );

						} else {

							material = new THREE.MeshPhongMaterial();

						}

						material.name = sourceMaterial.name;

					}

					material.flatShading = sourceMaterial.smooth ? false : true;
					material.vertexColors = hasVertexColors ? THREE.VertexColors : THREE.NoColors;

					createdMaterials.push( material );

				}

				// Create mesh

				var mesh;

				if ( createdMaterials.length > 1 ) {

					for ( var mi = 0, miLen = materials.length; mi < miLen; mi ++ ) {

						var sourceMaterial = materials[ mi ];
						buffergeometry.addGroup( sourceMaterial.groupStart, sourceMaterial.groupCount, mi );

					}

					if ( isLine ) {

						mesh = new THREE.LineSegments( buffergeometry, createdMaterials );

					} else if ( isPoints ) {

						mesh = new THREE.Points( buffergeometry, createdMaterials );

					} else {

						mesh = new THREE.Mesh( buffergeometry, createdMaterials );

					}

				} else {

					if ( isLine ) {

						mesh = new THREE.LineSegments( buffergeometry, createdMaterials[ 0 ] );

					} else if ( isPoints ) {

						mesh = new THREE.Points( buffergeometry, createdMaterials[ 0 ] );

					} else {

						mesh = new THREE.Mesh( buffergeometry, createdMaterials[ 0 ] );

					}

				}

				mesh.name = object.name;

				container.add( mesh );

			}

			console.timeEnd( 'OBJLoader' );

			return container;

		}

	};

	return OBJLoader;

} )();


/***/ }),

/***/ "../../node_modules/three/examples/js/loaders/PLYLoader.js":
/*!****************************************************************!*\
  !*** /app/node_modules/three/examples/js/loaders/PLYLoader.js ***!
  \****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/**
 * @author Wei Meng / http://about.me/menway
 *
 * Description: A THREE loader for PLY ASCII files (known as the Polygon
 * File Format or the Stanford Triangle Format).
 *
 * Limitations: ASCII decoding assumes file is UTF-8.
 *
 * Usage:
 *	var loader = new THREE.PLYLoader();
 *	loader.load('./models/ply/ascii/dolphins.ply', function (geometry) {
 *
 *		scene.add( new THREE.Mesh( geometry ) );
 *
 *	} );
 *
 * If the PLY file uses non standard property names, they can be mapped while
 * loading. For example, the following maps the properties
 * “diffuse_(red|green|blue)” in the file to standard color names.
 *
 * loader.setPropertyNameMapping( {
 *	diffuse_red: 'red',
 *	diffuse_green: 'green',
 *	diffuse_blue: 'blue'
 * } );
 *
 */


THREE.PLYLoader = function ( manager ) {

	this.manager = ( manager !== undefined ) ? manager : THREE.DefaultLoadingManager;

	this.propertyNameMapping = {};

};

THREE.PLYLoader.prototype = {

	constructor: THREE.PLYLoader,

	load: function ( url, onLoad, onProgress, onError ) {

		var scope = this;

		var loader = new THREE.FileLoader( this.manager );
		loader.setPath( this.path );
		loader.setResponseType( 'arraybuffer' );
		loader.load( url, function ( text ) {

			onLoad( scope.parse( text ) );

		}, onProgress, onError );

	},

	setPath: function ( value ) {

		this.path = value;
		return this;

	},

	setPropertyNameMapping: function ( mapping ) {

		this.propertyNameMapping = mapping;

	},

	parse: function ( data ) {

		function parseHeader( data ) {

			var patternHeader = /ply([\s\S]*)end_header\r?\n/;
			var headerText = '';
			var headerLength = 0;
			var result = patternHeader.exec( data );

			if ( result !== null ) {

				headerText = result[ 1 ];
				headerLength = result[ 0 ].length;

			}

			var header = {
				comments: [],
				elements: [],
				headerLength: headerLength
			};

			var lines = headerText.split( '\n' );
			var currentElement;
			var lineType, lineValues;

			function make_ply_element_property( propertValues, propertyNameMapping ) {

				var property = { type: propertValues[ 0 ] };

				if ( property.type === 'list' ) {

					property.name = propertValues[ 3 ];
					property.countType = propertValues[ 1 ];
					property.itemType = propertValues[ 2 ];

				} else {

					property.name = propertValues[ 1 ];

				}

				if ( property.name in propertyNameMapping ) {

					property.name = propertyNameMapping[ property.name ];

				}

				return property;

			}

			for ( var i = 0; i < lines.length; i ++ ) {

				var line = lines[ i ];
				line = line.trim();

				if ( line === '' ) continue;

				lineValues = line.split( /\s+/ );
				lineType = lineValues.shift();
				line = lineValues.join( ' ' );

				switch ( lineType ) {

					case 'format':

						header.format = lineValues[ 0 ];
						header.version = lineValues[ 1 ];

						break;

					case 'comment':

						header.comments.push( line );

						break;

					case 'element':

						if ( currentElement !== undefined ) {

							header.elements.push( currentElement );

						}

						currentElement = {};
						currentElement.name = lineValues[ 0 ];
						currentElement.count = parseInt( lineValues[ 1 ] );
						currentElement.properties = [];

						break;

					case 'property':

						currentElement.properties.push( make_ply_element_property( lineValues, scope.propertyNameMapping ) );

						break;


					default:

						console.log( 'unhandled', lineType, lineValues );

				}

			}

			if ( currentElement !== undefined ) {

				header.elements.push( currentElement );

			}

			return header;

		}

		function parseASCIINumber( n, type ) {

			switch ( type ) {

				case 'char': case 'uchar': case 'short': case 'ushort': case 'int': case 'uint':
				case 'int8': case 'uint8': case 'int16': case 'uint16': case 'int32': case 'uint32':

					return parseInt( n );

				case 'float': case 'double': case 'float32': case 'float64':

					return parseFloat( n );

			}

		}

		function parseASCIIElement( properties, line ) {

			var values = line.split( /\s+/ );

			var element = {};

			for ( var i = 0; i < properties.length; i ++ ) {

				if ( properties[ i ].type === 'list' ) {

					var list = [];
					var n = parseASCIINumber( values.shift(), properties[ i ].countType );

					for ( var j = 0; j < n; j ++ ) {

						list.push( parseASCIINumber( values.shift(), properties[ i ].itemType ) );

					}

					element[ properties[ i ].name ] = list;

				} else {

					element[ properties[ i ].name ] = parseASCIINumber( values.shift(), properties[ i ].type );

				}

			}

			return element;

		}

		function parseASCII( data, header ) {

			// PLY ascii format specification, as per http://en.wikipedia.org/wiki/PLY_(file_format)

			var buffer = {
				indices: [],
				vertices: [],
				normals: [],
				uvs: [],
				faceVertexUvs: [],
				colors: []
			};

			var result;

			var patternBody = /end_header\s([\s\S]*)$/;
			var body = '';
			if ( ( result = patternBody.exec( data ) ) !== null ) {

				body = result[ 1 ];

			}

			var lines = body.split( '\n' );
			var currentElement = 0;
			var currentElementCount = 0;

			for ( var i = 0; i < lines.length; i ++ ) {

				var line = lines[ i ];
				line = line.trim();
				if ( line === '' ) {

					continue;

				}

				if ( currentElementCount >= header.elements[ currentElement ].count ) {

					currentElement ++;
					currentElementCount = 0;

				}

				var element = parseASCIIElement( header.elements[ currentElement ].properties, line );

				handleElement( buffer, header.elements[ currentElement ].name, element );

				currentElementCount ++;

			}

			return postProcess( buffer );

		}

		function postProcess( buffer ) {

			var geometry = new THREE.BufferGeometry();

			// mandatory buffer data

			if ( buffer.indices.length > 0 ) {

				geometry.setIndex( buffer.indices );

			}

			geometry.addAttribute( 'position', new THREE.Float32BufferAttribute( buffer.vertices, 3 ) );

			// optional buffer data

			if ( buffer.normals.length > 0 ) {

				geometry.addAttribute( 'normal', new THREE.Float32BufferAttribute( buffer.normals, 3 ) );

			}

			if ( buffer.uvs.length > 0 ) {

				geometry.addAttribute( 'uv', new THREE.Float32BufferAttribute( buffer.uvs, 2 ) );

			}

			if ( buffer.colors.length > 0 ) {

				geometry.addAttribute( 'color', new THREE.Float32BufferAttribute( buffer.colors, 3 ) );

			}

			if ( buffer.faceVertexUvs.length > 0 ) {

				geometry = geometry.toNonIndexed();
				geometry.addAttribute( 'uv', new THREE.Float32BufferAttribute( buffer.faceVertexUvs, 2 ) );

			}

			geometry.computeBoundingSphere();

			return geometry;

		}

		function handleElement( buffer, elementName, element ) {

			if ( elementName === 'vertex' ) {

				buffer.vertices.push( element.x, element.y, element.z );

				if ( 'nx' in element && 'ny' in element && 'nz' in element ) {

					buffer.normals.push( element.nx, element.ny, element.nz );

				}

				if ( 's' in element && 't' in element ) {

					buffer.uvs.push( element.s, element.t );

				}

				if ( 'red' in element && 'green' in element && 'blue' in element ) {

					buffer.colors.push( element.red / 255.0, element.green / 255.0, element.blue / 255.0 );

				}

			} else if ( elementName === 'face' ) {

				var vertex_indices = element.vertex_indices || element.vertex_index; // issue #9338
				var texcoord = element.texcoord;

				if ( vertex_indices.length === 3 ) {

					buffer.indices.push( vertex_indices[ 0 ], vertex_indices[ 1 ], vertex_indices[ 2 ] );

					if ( texcoord && texcoord.length === 6 ) {

						buffer.faceVertexUvs.push( texcoord[ 0 ], texcoord[ 1 ] );
						buffer.faceVertexUvs.push( texcoord[ 2 ], texcoord[ 3 ] );
						buffer.faceVertexUvs.push( texcoord[ 4 ], texcoord[ 5 ] );

					}

				} else if ( vertex_indices.length === 4 ) {

					buffer.indices.push( vertex_indices[ 0 ], vertex_indices[ 1 ], vertex_indices[ 3 ] );
					buffer.indices.push( vertex_indices[ 1 ], vertex_indices[ 2 ], vertex_indices[ 3 ] );

				}

			}

		}

		function binaryRead( dataview, at, type, little_endian ) {

			switch ( type ) {

				// corespondences for non-specific length types here match rply:
				case 'int8':		case 'char':	 return [ dataview.getInt8( at ), 1 ];
				case 'uint8':		case 'uchar':	 return [ dataview.getUint8( at ), 1 ];
				case 'int16':		case 'short':	 return [ dataview.getInt16( at, little_endian ), 2 ];
				case 'uint16':	case 'ushort': return [ dataview.getUint16( at, little_endian ), 2 ];
				case 'int32':		case 'int':		 return [ dataview.getInt32( at, little_endian ), 4 ];
				case 'uint32':	case 'uint':	 return [ dataview.getUint32( at, little_endian ), 4 ];
				case 'float32': case 'float':	 return [ dataview.getFloat32( at, little_endian ), 4 ];
				case 'float64': case 'double': return [ dataview.getFloat64( at, little_endian ), 8 ];

			}

		}

		function binaryReadElement( dataview, at, properties, little_endian ) {

			var element = {};
			var result, read = 0;

			for ( var i = 0; i < properties.length; i ++ ) {

				if ( properties[ i ].type === 'list' ) {

					var list = [];

					result = binaryRead( dataview, at + read, properties[ i ].countType, little_endian );
					var n = result[ 0 ];
					read += result[ 1 ];

					for ( var j = 0; j < n; j ++ ) {

						result = binaryRead( dataview, at + read, properties[ i ].itemType, little_endian );
						list.push( result[ 0 ] );
						read += result[ 1 ];

					}

					element[ properties[ i ].name ] = list;

				} else {

					result = binaryRead( dataview, at + read, properties[ i ].type, little_endian );
					element[ properties[ i ].name ] = result[ 0 ];
					read += result[ 1 ];

				}

			}

			return [ element, read ];

		}

		function parseBinary( data, header ) {

			var buffer = {
				indices: [],
				vertices: [],
				normals: [],
				uvs: [],
				faceVertexUvs: [],
				colors: []
			};

			var little_endian = ( header.format === 'binary_little_endian' );
			var body = new DataView( data, header.headerLength );
			var result, loc = 0;

			for ( var currentElement = 0; currentElement < header.elements.length; currentElement ++ ) {

				for ( var currentElementCount = 0; currentElementCount < header.elements[ currentElement ].count; currentElementCount ++ ) {

					result = binaryReadElement( body, loc, header.elements[ currentElement ].properties, little_endian );
					loc += result[ 1 ];
					var element = result[ 0 ];

					handleElement( buffer, header.elements[ currentElement ].name, element );

				}

			}

			return postProcess( buffer );

		}

		//

		var geometry;
		var scope = this;

		if ( data instanceof ArrayBuffer ) {

			var text = THREE.LoaderUtils.decodeText( new Uint8Array( data ) );
			var header = parseHeader( text );

			geometry = header.format === 'ascii' ? parseASCII( text, header ) : parseBinary( data, header );

		} else {

			geometry = parseASCII( data, parseHeader( data ) );

		}

		return geometry;

	}

};


/***/ }),

/***/ "../../node_modules/uri-js/dist/es5/uri.all.js":
/*!****************************************************!*\
  !*** /app/node_modules/uri-js/dist/es5/uri.all.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/** @license URI.js v4.2.1 (c) 2011 Gary Court. License: http://github.com/garycourt/uri-js */
(function (global, factory) {
	 true ? factory(exports) :
	undefined;
}(this, (function (exports) { 'use strict';

function merge() {
    for (var _len = arguments.length, sets = Array(_len), _key = 0; _key < _len; _key++) {
        sets[_key] = arguments[_key];
    }

    if (sets.length > 1) {
        sets[0] = sets[0].slice(0, -1);
        var xl = sets.length - 1;
        for (var x = 1; x < xl; ++x) {
            sets[x] = sets[x].slice(1, -1);
        }
        sets[xl] = sets[xl].slice(1);
        return sets.join('');
    } else {
        return sets[0];
    }
}
function subexp(str) {
    return "(?:" + str + ")";
}
function typeOf(o) {
    return o === undefined ? "undefined" : o === null ? "null" : Object.prototype.toString.call(o).split(" ").pop().split("]").shift().toLowerCase();
}
function toUpperCase(str) {
    return str.toUpperCase();
}
function toArray(obj) {
    return obj !== undefined && obj !== null ? obj instanceof Array ? obj : typeof obj.length !== "number" || obj.split || obj.setInterval || obj.call ? [obj] : Array.prototype.slice.call(obj) : [];
}
function assign(target, source) {
    var obj = target;
    if (source) {
        for (var key in source) {
            obj[key] = source[key];
        }
    }
    return obj;
}

function buildExps(isIRI) {
    var ALPHA$$ = "[A-Za-z]",
        CR$ = "[\\x0D]",
        DIGIT$$ = "[0-9]",
        DQUOTE$$ = "[\\x22]",
        HEXDIG$$ = merge(DIGIT$$, "[A-Fa-f]"),
        //case-insensitive
    LF$$ = "[\\x0A]",
        SP$$ = "[\\x20]",
        PCT_ENCODED$ = subexp(subexp("%[EFef]" + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$) + "|" + subexp("%[89A-Fa-f]" + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$) + "|" + subexp("%" + HEXDIG$$ + HEXDIG$$)),
        //expanded
    GEN_DELIMS$$ = "[\\:\\/\\?\\#\\[\\]\\@]",
        SUB_DELIMS$$ = "[\\!\\$\\&\\'\\(\\)\\*\\+\\,\\;\\=]",
        RESERVED$$ = merge(GEN_DELIMS$$, SUB_DELIMS$$),
        UCSCHAR$$ = isIRI ? "[\\xA0-\\u200D\\u2010-\\u2029\\u202F-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFEF]" : "[]",
        //subset, excludes bidi control characters
    IPRIVATE$$ = isIRI ? "[\\uE000-\\uF8FF]" : "[]",
        //subset
    UNRESERVED$$ = merge(ALPHA$$, DIGIT$$, "[\\-\\.\\_\\~]", UCSCHAR$$),
        SCHEME$ = subexp(ALPHA$$ + merge(ALPHA$$, DIGIT$$, "[\\+\\-\\.]") + "*"),
        USERINFO$ = subexp(subexp(PCT_ENCODED$ + "|" + merge(UNRESERVED$$, SUB_DELIMS$$, "[\\:]")) + "*"),
        DEC_OCTET$ = subexp(subexp("25[0-5]") + "|" + subexp("2[0-4]" + DIGIT$$) + "|" + subexp("1" + DIGIT$$ + DIGIT$$) + "|" + subexp("[1-9]" + DIGIT$$) + "|" + DIGIT$$),
        DEC_OCTET_RELAXED$ = subexp(subexp("25[0-5]") + "|" + subexp("2[0-4]" + DIGIT$$) + "|" + subexp("1" + DIGIT$$ + DIGIT$$) + "|" + subexp("0?[1-9]" + DIGIT$$) + "|0?0?" + DIGIT$$),
        //relaxed parsing rules
    IPV4ADDRESS$ = subexp(DEC_OCTET_RELAXED$ + "\\." + DEC_OCTET_RELAXED$ + "\\." + DEC_OCTET_RELAXED$ + "\\." + DEC_OCTET_RELAXED$),
        H16$ = subexp(HEXDIG$$ + "{1,4}"),
        LS32$ = subexp(subexp(H16$ + "\\:" + H16$) + "|" + IPV4ADDRESS$),
        IPV6ADDRESS1$ = subexp(subexp(H16$ + "\\:") + "{6}" + LS32$),
        //                           6( h16 ":" ) ls32
    IPV6ADDRESS2$ = subexp("\\:\\:" + subexp(H16$ + "\\:") + "{5}" + LS32$),
        //                      "::" 5( h16 ":" ) ls32
    IPV6ADDRESS3$ = subexp(subexp(H16$) + "?\\:\\:" + subexp(H16$ + "\\:") + "{4}" + LS32$),
        //[               h16 ] "::" 4( h16 ":" ) ls32
    IPV6ADDRESS4$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,1}" + H16$) + "?\\:\\:" + subexp(H16$ + "\\:") + "{3}" + LS32$),
        //[ *1( h16 ":" ) h16 ] "::" 3( h16 ":" ) ls32
    IPV6ADDRESS5$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,2}" + H16$) + "?\\:\\:" + subexp(H16$ + "\\:") + "{2}" + LS32$),
        //[ *2( h16 ":" ) h16 ] "::" 2( h16 ":" ) ls32
    IPV6ADDRESS6$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,3}" + H16$) + "?\\:\\:" + H16$ + "\\:" + LS32$),
        //[ *3( h16 ":" ) h16 ] "::"    h16 ":"   ls32
    IPV6ADDRESS7$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,4}" + H16$) + "?\\:\\:" + LS32$),
        //[ *4( h16 ":" ) h16 ] "::"              ls32
    IPV6ADDRESS8$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,5}" + H16$) + "?\\:\\:" + H16$),
        //[ *5( h16 ":" ) h16 ] "::"              h16
    IPV6ADDRESS9$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,6}" + H16$) + "?\\:\\:"),
        //[ *6( h16 ":" ) h16 ] "::"
    IPV6ADDRESS$ = subexp([IPV6ADDRESS1$, IPV6ADDRESS2$, IPV6ADDRESS3$, IPV6ADDRESS4$, IPV6ADDRESS5$, IPV6ADDRESS6$, IPV6ADDRESS7$, IPV6ADDRESS8$, IPV6ADDRESS9$].join("|")),
        ZONEID$ = subexp(subexp(UNRESERVED$$ + "|" + PCT_ENCODED$) + "+"),
        //RFC 6874
    IPV6ADDRZ$ = subexp(IPV6ADDRESS$ + "\\%25" + ZONEID$),
        //RFC 6874
    IPV6ADDRZ_RELAXED$ = subexp(IPV6ADDRESS$ + subexp("\\%25|\\%(?!" + HEXDIG$$ + "{2})") + ZONEID$),
        //RFC 6874, with relaxed parsing rules
    IPVFUTURE$ = subexp("[vV]" + HEXDIG$$ + "+\\." + merge(UNRESERVED$$, SUB_DELIMS$$, "[\\:]") + "+"),
        IP_LITERAL$ = subexp("\\[" + subexp(IPV6ADDRZ_RELAXED$ + "|" + IPV6ADDRESS$ + "|" + IPVFUTURE$) + "\\]"),
        //RFC 6874
    REG_NAME$ = subexp(subexp(PCT_ENCODED$ + "|" + merge(UNRESERVED$$, SUB_DELIMS$$)) + "*"),
        HOST$ = subexp(IP_LITERAL$ + "|" + IPV4ADDRESS$ + "(?!" + REG_NAME$ + ")" + "|" + REG_NAME$),
        PORT$ = subexp(DIGIT$$ + "*"),
        AUTHORITY$ = subexp(subexp(USERINFO$ + "@") + "?" + HOST$ + subexp("\\:" + PORT$) + "?"),
        PCHAR$ = subexp(PCT_ENCODED$ + "|" + merge(UNRESERVED$$, SUB_DELIMS$$, "[\\:\\@]")),
        SEGMENT$ = subexp(PCHAR$ + "*"),
        SEGMENT_NZ$ = subexp(PCHAR$ + "+"),
        SEGMENT_NZ_NC$ = subexp(subexp(PCT_ENCODED$ + "|" + merge(UNRESERVED$$, SUB_DELIMS$$, "[\\@]")) + "+"),
        PATH_ABEMPTY$ = subexp(subexp("\\/" + SEGMENT$) + "*"),
        PATH_ABSOLUTE$ = subexp("\\/" + subexp(SEGMENT_NZ$ + PATH_ABEMPTY$) + "?"),
        //simplified
    PATH_NOSCHEME$ = subexp(SEGMENT_NZ_NC$ + PATH_ABEMPTY$),
        //simplified
    PATH_ROOTLESS$ = subexp(SEGMENT_NZ$ + PATH_ABEMPTY$),
        //simplified
    PATH_EMPTY$ = "(?!" + PCHAR$ + ")",
        PATH$ = subexp(PATH_ABEMPTY$ + "|" + PATH_ABSOLUTE$ + "|" + PATH_NOSCHEME$ + "|" + PATH_ROOTLESS$ + "|" + PATH_EMPTY$),
        QUERY$ = subexp(subexp(PCHAR$ + "|" + merge("[\\/\\?]", IPRIVATE$$)) + "*"),
        FRAGMENT$ = subexp(subexp(PCHAR$ + "|[\\/\\?]") + "*"),
        HIER_PART$ = subexp(subexp("\\/\\/" + AUTHORITY$ + PATH_ABEMPTY$) + "|" + PATH_ABSOLUTE$ + "|" + PATH_ROOTLESS$ + "|" + PATH_EMPTY$),
        URI$ = subexp(SCHEME$ + "\\:" + HIER_PART$ + subexp("\\?" + QUERY$) + "?" + subexp("\\#" + FRAGMENT$) + "?"),
        RELATIVE_PART$ = subexp(subexp("\\/\\/" + AUTHORITY$ + PATH_ABEMPTY$) + "|" + PATH_ABSOLUTE$ + "|" + PATH_NOSCHEME$ + "|" + PATH_EMPTY$),
        RELATIVE$ = subexp(RELATIVE_PART$ + subexp("\\?" + QUERY$) + "?" + subexp("\\#" + FRAGMENT$) + "?"),
        URI_REFERENCE$ = subexp(URI$ + "|" + RELATIVE$),
        ABSOLUTE_URI$ = subexp(SCHEME$ + "\\:" + HIER_PART$ + subexp("\\?" + QUERY$) + "?"),
        GENERIC_REF$ = "^(" + SCHEME$ + ")\\:" + subexp(subexp("\\/\\/(" + subexp("(" + USERINFO$ + ")@") + "?(" + HOST$ + ")" + subexp("\\:(" + PORT$ + ")") + "?)") + "?(" + PATH_ABEMPTY$ + "|" + PATH_ABSOLUTE$ + "|" + PATH_ROOTLESS$ + "|" + PATH_EMPTY$ + ")") + subexp("\\?(" + QUERY$ + ")") + "?" + subexp("\\#(" + FRAGMENT$ + ")") + "?$",
        RELATIVE_REF$ = "^(){0}" + subexp(subexp("\\/\\/(" + subexp("(" + USERINFO$ + ")@") + "?(" + HOST$ + ")" + subexp("\\:(" + PORT$ + ")") + "?)") + "?(" + PATH_ABEMPTY$ + "|" + PATH_ABSOLUTE$ + "|" + PATH_NOSCHEME$ + "|" + PATH_EMPTY$ + ")") + subexp("\\?(" + QUERY$ + ")") + "?" + subexp("\\#(" + FRAGMENT$ + ")") + "?$",
        ABSOLUTE_REF$ = "^(" + SCHEME$ + ")\\:" + subexp(subexp("\\/\\/(" + subexp("(" + USERINFO$ + ")@") + "?(" + HOST$ + ")" + subexp("\\:(" + PORT$ + ")") + "?)") + "?(" + PATH_ABEMPTY$ + "|" + PATH_ABSOLUTE$ + "|" + PATH_ROOTLESS$ + "|" + PATH_EMPTY$ + ")") + subexp("\\?(" + QUERY$ + ")") + "?$",
        SAMEDOC_REF$ = "^" + subexp("\\#(" + FRAGMENT$ + ")") + "?$",
        AUTHORITY_REF$ = "^" + subexp("(" + USERINFO$ + ")@") + "?(" + HOST$ + ")" + subexp("\\:(" + PORT$ + ")") + "?$";
    return {
        NOT_SCHEME: new RegExp(merge("[^]", ALPHA$$, DIGIT$$, "[\\+\\-\\.]"), "g"),
        NOT_USERINFO: new RegExp(merge("[^\\%\\:]", UNRESERVED$$, SUB_DELIMS$$), "g"),
        NOT_HOST: new RegExp(merge("[^\\%\\[\\]\\:]", UNRESERVED$$, SUB_DELIMS$$), "g"),
        NOT_PATH: new RegExp(merge("[^\\%\\/\\:\\@]", UNRESERVED$$, SUB_DELIMS$$), "g"),
        NOT_PATH_NOSCHEME: new RegExp(merge("[^\\%\\/\\@]", UNRESERVED$$, SUB_DELIMS$$), "g"),
        NOT_QUERY: new RegExp(merge("[^\\%]", UNRESERVED$$, SUB_DELIMS$$, "[\\:\\@\\/\\?]", IPRIVATE$$), "g"),
        NOT_FRAGMENT: new RegExp(merge("[^\\%]", UNRESERVED$$, SUB_DELIMS$$, "[\\:\\@\\/\\?]"), "g"),
        ESCAPE: new RegExp(merge("[^]", UNRESERVED$$, SUB_DELIMS$$), "g"),
        UNRESERVED: new RegExp(UNRESERVED$$, "g"),
        OTHER_CHARS: new RegExp(merge("[^\\%]", UNRESERVED$$, RESERVED$$), "g"),
        PCT_ENCODED: new RegExp(PCT_ENCODED$, "g"),
        IPV4ADDRESS: new RegExp("^(" + IPV4ADDRESS$ + ")$"),
        IPV6ADDRESS: new RegExp("^\\[?(" + IPV6ADDRESS$ + ")" + subexp(subexp("\\%25|\\%(?!" + HEXDIG$$ + "{2})") + "(" + ZONEID$ + ")") + "?\\]?$") //RFC 6874, with relaxed parsing rules
    };
}
var URI_PROTOCOL = buildExps(false);

var IRI_PROTOCOL = buildExps(true);

var slicedToArray = function () {
  function sliceIterator(arr, i) {
    var _arr = [];
    var _n = true;
    var _d = false;
    var _e = undefined;

    try {
      for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
        _arr.push(_s.value);

        if (i && _arr.length === i) break;
      }
    } catch (err) {
      _d = true;
      _e = err;
    } finally {
      try {
        if (!_n && _i["return"]) _i["return"]();
      } finally {
        if (_d) throw _e;
      }
    }

    return _arr;
  }

  return function (arr, i) {
    if (Array.isArray(arr)) {
      return arr;
    } else if (Symbol.iterator in Object(arr)) {
      return sliceIterator(arr, i);
    } else {
      throw new TypeError("Invalid attempt to destructure non-iterable instance");
    }
  };
}();













var toConsumableArray = function (arr) {
  if (Array.isArray(arr)) {
    for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) arr2[i] = arr[i];

    return arr2;
  } else {
    return Array.from(arr);
  }
};

/** Highest positive signed 32-bit float value */

var maxInt = 2147483647; // aka. 0x7FFFFFFF or 2^31-1

/** Bootstring parameters */
var base = 36;
var tMin = 1;
var tMax = 26;
var skew = 38;
var damp = 700;
var initialBias = 72;
var initialN = 128; // 0x80
var delimiter = '-'; // '\x2D'

/** Regular expressions */
var regexPunycode = /^xn--/;
var regexNonASCII = /[^\0-\x7E]/; // non-ASCII chars
var regexSeparators = /[\x2E\u3002\uFF0E\uFF61]/g; // RFC 3490 separators

/** Error messages */
var errors = {
	'overflow': 'Overflow: input needs wider integers to process',
	'not-basic': 'Illegal input >= 0x80 (not a basic code point)',
	'invalid-input': 'Invalid input'
};

/** Convenience shortcuts */
var baseMinusTMin = base - tMin;
var floor = Math.floor;
var stringFromCharCode = String.fromCharCode;

/*--------------------------------------------------------------------------*/

/**
 * A generic error utility function.
 * @private
 * @param {String} type The error type.
 * @returns {Error} Throws a `RangeError` with the applicable error message.
 */
function error$1(type) {
	throw new RangeError(errors[type]);
}

/**
 * A generic `Array#map` utility function.
 * @private
 * @param {Array} array The array to iterate over.
 * @param {Function} callback The function that gets called for every array
 * item.
 * @returns {Array} A new array of values returned by the callback function.
 */
function map(array, fn) {
	var result = [];
	var length = array.length;
	while (length--) {
		result[length] = fn(array[length]);
	}
	return result;
}

/**
 * A simple `Array#map`-like wrapper to work with domain name strings or email
 * addresses.
 * @private
 * @param {String} domain The domain name or email address.
 * @param {Function} callback The function that gets called for every
 * character.
 * @returns {Array} A new string of characters returned by the callback
 * function.
 */
function mapDomain(string, fn) {
	var parts = string.split('@');
	var result = '';
	if (parts.length > 1) {
		// In email addresses, only the domain name should be punycoded. Leave
		// the local part (i.e. everything up to `@`) intact.
		result = parts[0] + '@';
		string = parts[1];
	}
	// Avoid `split(regex)` for IE8 compatibility. See #17.
	string = string.replace(regexSeparators, '\x2E');
	var labels = string.split('.');
	var encoded = map(labels, fn).join('.');
	return result + encoded;
}

/**
 * Creates an array containing the numeric code points of each Unicode
 * character in the string. While JavaScript uses UCS-2 internally,
 * this function will convert a pair of surrogate halves (each of which
 * UCS-2 exposes as separate characters) into a single code point,
 * matching UTF-16.
 * @see `punycode.ucs2.encode`
 * @see <https://mathiasbynens.be/notes/javascript-encoding>
 * @memberOf punycode.ucs2
 * @name decode
 * @param {String} string The Unicode input string (UCS-2).
 * @returns {Array} The new array of code points.
 */
function ucs2decode(string) {
	var output = [];
	var counter = 0;
	var length = string.length;
	while (counter < length) {
		var value = string.charCodeAt(counter++);
		if (value >= 0xD800 && value <= 0xDBFF && counter < length) {
			// It's a high surrogate, and there is a next character.
			var extra = string.charCodeAt(counter++);
			if ((extra & 0xFC00) == 0xDC00) {
				// Low surrogate.
				output.push(((value & 0x3FF) << 10) + (extra & 0x3FF) + 0x10000);
			} else {
				// It's an unmatched surrogate; only append this code unit, in case the
				// next code unit is the high surrogate of a surrogate pair.
				output.push(value);
				counter--;
			}
		} else {
			output.push(value);
		}
	}
	return output;
}

/**
 * Creates a string based on an array of numeric code points.
 * @see `punycode.ucs2.decode`
 * @memberOf punycode.ucs2
 * @name encode
 * @param {Array} codePoints The array of numeric code points.
 * @returns {String} The new Unicode string (UCS-2).
 */
var ucs2encode = function ucs2encode(array) {
	return String.fromCodePoint.apply(String, toConsumableArray(array));
};

/**
 * Converts a basic code point into a digit/integer.
 * @see `digitToBasic()`
 * @private
 * @param {Number} codePoint The basic numeric code point value.
 * @returns {Number} The numeric value of a basic code point (for use in
 * representing integers) in the range `0` to `base - 1`, or `base` if
 * the code point does not represent a value.
 */
var basicToDigit = function basicToDigit(codePoint) {
	if (codePoint - 0x30 < 0x0A) {
		return codePoint - 0x16;
	}
	if (codePoint - 0x41 < 0x1A) {
		return codePoint - 0x41;
	}
	if (codePoint - 0x61 < 0x1A) {
		return codePoint - 0x61;
	}
	return base;
};

/**
 * Converts a digit/integer into a basic code point.
 * @see `basicToDigit()`
 * @private
 * @param {Number} digit The numeric value of a basic code point.
 * @returns {Number} The basic code point whose value (when used for
 * representing integers) is `digit`, which needs to be in the range
 * `0` to `base - 1`. If `flag` is non-zero, the uppercase form is
 * used; else, the lowercase form is used. The behavior is undefined
 * if `flag` is non-zero and `digit` has no uppercase form.
 */
var digitToBasic = function digitToBasic(digit, flag) {
	//  0..25 map to ASCII a..z or A..Z
	// 26..35 map to ASCII 0..9
	return digit + 22 + 75 * (digit < 26) - ((flag != 0) << 5);
};

/**
 * Bias adaptation function as per section 3.4 of RFC 3492.
 * https://tools.ietf.org/html/rfc3492#section-3.4
 * @private
 */
var adapt = function adapt(delta, numPoints, firstTime) {
	var k = 0;
	delta = firstTime ? floor(delta / damp) : delta >> 1;
	delta += floor(delta / numPoints);
	for (; /* no initialization */delta > baseMinusTMin * tMax >> 1; k += base) {
		delta = floor(delta / baseMinusTMin);
	}
	return floor(k + (baseMinusTMin + 1) * delta / (delta + skew));
};

/**
 * Converts a Punycode string of ASCII-only symbols to a string of Unicode
 * symbols.
 * @memberOf punycode
 * @param {String} input The Punycode string of ASCII-only symbols.
 * @returns {String} The resulting string of Unicode symbols.
 */
var decode = function decode(input) {
	// Don't use UCS-2.
	var output = [];
	var inputLength = input.length;
	var i = 0;
	var n = initialN;
	var bias = initialBias;

	// Handle the basic code points: let `basic` be the number of input code
	// points before the last delimiter, or `0` if there is none, then copy
	// the first basic code points to the output.

	var basic = input.lastIndexOf(delimiter);
	if (basic < 0) {
		basic = 0;
	}

	for (var j = 0; j < basic; ++j) {
		// if it's not a basic code point
		if (input.charCodeAt(j) >= 0x80) {
			error$1('not-basic');
		}
		output.push(input.charCodeAt(j));
	}

	// Main decoding loop: start just after the last delimiter if any basic code
	// points were copied; start at the beginning otherwise.

	for (var index = basic > 0 ? basic + 1 : 0; index < inputLength;) /* no final expression */{

		// `index` is the index of the next character to be consumed.
		// Decode a generalized variable-length integer into `delta`,
		// which gets added to `i`. The overflow checking is easier
		// if we increase `i` as we go, then subtract off its starting
		// value at the end to obtain `delta`.
		var oldi = i;
		for (var w = 1, k = base;; /* no condition */k += base) {

			if (index >= inputLength) {
				error$1('invalid-input');
			}

			var digit = basicToDigit(input.charCodeAt(index++));

			if (digit >= base || digit > floor((maxInt - i) / w)) {
				error$1('overflow');
			}

			i += digit * w;
			var t = k <= bias ? tMin : k >= bias + tMax ? tMax : k - bias;

			if (digit < t) {
				break;
			}

			var baseMinusT = base - t;
			if (w > floor(maxInt / baseMinusT)) {
				error$1('overflow');
			}

			w *= baseMinusT;
		}

		var out = output.length + 1;
		bias = adapt(i - oldi, out, oldi == 0);

		// `i` was supposed to wrap around from `out` to `0`,
		// incrementing `n` each time, so we'll fix that now:
		if (floor(i / out) > maxInt - n) {
			error$1('overflow');
		}

		n += floor(i / out);
		i %= out;

		// Insert `n` at position `i` of the output.
		output.splice(i++, 0, n);
	}

	return String.fromCodePoint.apply(String, output);
};

/**
 * Converts a string of Unicode symbols (e.g. a domain name label) to a
 * Punycode string of ASCII-only symbols.
 * @memberOf punycode
 * @param {String} input The string of Unicode symbols.
 * @returns {String} The resulting Punycode string of ASCII-only symbols.
 */
var encode = function encode(input) {
	var output = [];

	// Convert the input in UCS-2 to an array of Unicode code points.
	input = ucs2decode(input);

	// Cache the length.
	var inputLength = input.length;

	// Initialize the state.
	var n = initialN;
	var delta = 0;
	var bias = initialBias;

	// Handle the basic code points.
	var _iteratorNormalCompletion = true;
	var _didIteratorError = false;
	var _iteratorError = undefined;

	try {
		for (var _iterator = input[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
			var _currentValue2 = _step.value;

			if (_currentValue2 < 0x80) {
				output.push(stringFromCharCode(_currentValue2));
			}
		}
	} catch (err) {
		_didIteratorError = true;
		_iteratorError = err;
	} finally {
		try {
			if (!_iteratorNormalCompletion && _iterator.return) {
				_iterator.return();
			}
		} finally {
			if (_didIteratorError) {
				throw _iteratorError;
			}
		}
	}

	var basicLength = output.length;
	var handledCPCount = basicLength;

	// `handledCPCount` is the number of code points that have been handled;
	// `basicLength` is the number of basic code points.

	// Finish the basic string with a delimiter unless it's empty.
	if (basicLength) {
		output.push(delimiter);
	}

	// Main encoding loop:
	while (handledCPCount < inputLength) {

		// All non-basic code points < n have been handled already. Find the next
		// larger one:
		var m = maxInt;
		var _iteratorNormalCompletion2 = true;
		var _didIteratorError2 = false;
		var _iteratorError2 = undefined;

		try {
			for (var _iterator2 = input[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
				var currentValue = _step2.value;

				if (currentValue >= n && currentValue < m) {
					m = currentValue;
				}
			}

			// Increase `delta` enough to advance the decoder's <n,i> state to <m,0>,
			// but guard against overflow.
		} catch (err) {
			_didIteratorError2 = true;
			_iteratorError2 = err;
		} finally {
			try {
				if (!_iteratorNormalCompletion2 && _iterator2.return) {
					_iterator2.return();
				}
			} finally {
				if (_didIteratorError2) {
					throw _iteratorError2;
				}
			}
		}

		var handledCPCountPlusOne = handledCPCount + 1;
		if (m - n > floor((maxInt - delta) / handledCPCountPlusOne)) {
			error$1('overflow');
		}

		delta += (m - n) * handledCPCountPlusOne;
		n = m;

		var _iteratorNormalCompletion3 = true;
		var _didIteratorError3 = false;
		var _iteratorError3 = undefined;

		try {
			for (var _iterator3 = input[Symbol.iterator](), _step3; !(_iteratorNormalCompletion3 = (_step3 = _iterator3.next()).done); _iteratorNormalCompletion3 = true) {
				var _currentValue = _step3.value;

				if (_currentValue < n && ++delta > maxInt) {
					error$1('overflow');
				}
				if (_currentValue == n) {
					// Represent delta as a generalized variable-length integer.
					var q = delta;
					for (var k = base;; /* no condition */k += base) {
						var t = k <= bias ? tMin : k >= bias + tMax ? tMax : k - bias;
						if (q < t) {
							break;
						}
						var qMinusT = q - t;
						var baseMinusT = base - t;
						output.push(stringFromCharCode(digitToBasic(t + qMinusT % baseMinusT, 0)));
						q = floor(qMinusT / baseMinusT);
					}

					output.push(stringFromCharCode(digitToBasic(q, 0)));
					bias = adapt(delta, handledCPCountPlusOne, handledCPCount == basicLength);
					delta = 0;
					++handledCPCount;
				}
			}
		} catch (err) {
			_didIteratorError3 = true;
			_iteratorError3 = err;
		} finally {
			try {
				if (!_iteratorNormalCompletion3 && _iterator3.return) {
					_iterator3.return();
				}
			} finally {
				if (_didIteratorError3) {
					throw _iteratorError3;
				}
			}
		}

		++delta;
		++n;
	}
	return output.join('');
};

/**
 * Converts a Punycode string representing a domain name or an email address
 * to Unicode. Only the Punycoded parts of the input will be converted, i.e.
 * it doesn't matter if you call it on a string that has already been
 * converted to Unicode.
 * @memberOf punycode
 * @param {String} input The Punycoded domain name or email address to
 * convert to Unicode.
 * @returns {String} The Unicode representation of the given Punycode
 * string.
 */
var toUnicode = function toUnicode(input) {
	return mapDomain(input, function (string) {
		return regexPunycode.test(string) ? decode(string.slice(4).toLowerCase()) : string;
	});
};

/**
 * Converts a Unicode string representing a domain name or an email address to
 * Punycode. Only the non-ASCII parts of the domain name will be converted,
 * i.e. it doesn't matter if you call it with a domain that's already in
 * ASCII.
 * @memberOf punycode
 * @param {String} input The domain name or email address to convert, as a
 * Unicode string.
 * @returns {String} The Punycode representation of the given domain name or
 * email address.
 */
var toASCII = function toASCII(input) {
	return mapDomain(input, function (string) {
		return regexNonASCII.test(string) ? 'xn--' + encode(string) : string;
	});
};

/*--------------------------------------------------------------------------*/

/** Define the public API */
var punycode = {
	/**
  * A string representing the current Punycode.js version number.
  * @memberOf punycode
  * @type String
  */
	'version': '2.1.0',
	/**
  * An object of methods to convert from JavaScript's internal character
  * representation (UCS-2) to Unicode code points, and back.
  * @see <https://mathiasbynens.be/notes/javascript-encoding>
  * @memberOf punycode
  * @type Object
  */
	'ucs2': {
		'decode': ucs2decode,
		'encode': ucs2encode
	},
	'decode': decode,
	'encode': encode,
	'toASCII': toASCII,
	'toUnicode': toUnicode
};

/**
 * URI.js
 *
 * @fileoverview An RFC 3986 compliant, scheme extendable URI parsing/validating/resolving library for JavaScript.
 * @author <a href="mailto:gary.court@gmail.com">Gary Court</a>
 * @see http://github.com/garycourt/uri-js
 */
/**
 * Copyright 2011 Gary Court. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice, this list of
 *       conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright notice, this list
 *       of conditions and the following disclaimer in the documentation and/or other materials
 *       provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY GARY COURT ``AS IS'' AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL GARY COURT OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those of the
 * authors and should not be interpreted as representing official policies, either expressed
 * or implied, of Gary Court.
 */
var SCHEMES = {};
function pctEncChar(chr) {
    var c = chr.charCodeAt(0);
    var e = void 0;
    if (c < 16) e = "%0" + c.toString(16).toUpperCase();else if (c < 128) e = "%" + c.toString(16).toUpperCase();else if (c < 2048) e = "%" + (c >> 6 | 192).toString(16).toUpperCase() + "%" + (c & 63 | 128).toString(16).toUpperCase();else e = "%" + (c >> 12 | 224).toString(16).toUpperCase() + "%" + (c >> 6 & 63 | 128).toString(16).toUpperCase() + "%" + (c & 63 | 128).toString(16).toUpperCase();
    return e;
}
function pctDecChars(str) {
    var newStr = "";
    var i = 0;
    var il = str.length;
    while (i < il) {
        var c = parseInt(str.substr(i + 1, 2), 16);
        if (c < 128) {
            newStr += String.fromCharCode(c);
            i += 3;
        } else if (c >= 194 && c < 224) {
            if (il - i >= 6) {
                var c2 = parseInt(str.substr(i + 4, 2), 16);
                newStr += String.fromCharCode((c & 31) << 6 | c2 & 63);
            } else {
                newStr += str.substr(i, 6);
            }
            i += 6;
        } else if (c >= 224) {
            if (il - i >= 9) {
                var _c = parseInt(str.substr(i + 4, 2), 16);
                var c3 = parseInt(str.substr(i + 7, 2), 16);
                newStr += String.fromCharCode((c & 15) << 12 | (_c & 63) << 6 | c3 & 63);
            } else {
                newStr += str.substr(i, 9);
            }
            i += 9;
        } else {
            newStr += str.substr(i, 3);
            i += 3;
        }
    }
    return newStr;
}
function _normalizeComponentEncoding(components, protocol) {
    function decodeUnreserved(str) {
        var decStr = pctDecChars(str);
        return !decStr.match(protocol.UNRESERVED) ? str : decStr;
    }
    if (components.scheme) components.scheme = String(components.scheme).replace(protocol.PCT_ENCODED, decodeUnreserved).toLowerCase().replace(protocol.NOT_SCHEME, "");
    if (components.userinfo !== undefined) components.userinfo = String(components.userinfo).replace(protocol.PCT_ENCODED, decodeUnreserved).replace(protocol.NOT_USERINFO, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
    if (components.host !== undefined) components.host = String(components.host).replace(protocol.PCT_ENCODED, decodeUnreserved).toLowerCase().replace(protocol.NOT_HOST, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
    if (components.path !== undefined) components.path = String(components.path).replace(protocol.PCT_ENCODED, decodeUnreserved).replace(components.scheme ? protocol.NOT_PATH : protocol.NOT_PATH_NOSCHEME, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
    if (components.query !== undefined) components.query = String(components.query).replace(protocol.PCT_ENCODED, decodeUnreserved).replace(protocol.NOT_QUERY, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
    if (components.fragment !== undefined) components.fragment = String(components.fragment).replace(protocol.PCT_ENCODED, decodeUnreserved).replace(protocol.NOT_FRAGMENT, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
    return components;
}

function _stripLeadingZeros(str) {
    return str.replace(/^0*(.*)/, "$1") || "0";
}
function _normalizeIPv4(host, protocol) {
    var matches = host.match(protocol.IPV4ADDRESS) || [];

    var _matches = slicedToArray(matches, 2),
        address = _matches[1];

    if (address) {
        return address.split(".").map(_stripLeadingZeros).join(".");
    } else {
        return host;
    }
}
function _normalizeIPv6(host, protocol) {
    var matches = host.match(protocol.IPV6ADDRESS) || [];

    var _matches2 = slicedToArray(matches, 3),
        address = _matches2[1],
        zone = _matches2[2];

    if (address) {
        var _address$toLowerCase$ = address.toLowerCase().split('::').reverse(),
            _address$toLowerCase$2 = slicedToArray(_address$toLowerCase$, 2),
            last = _address$toLowerCase$2[0],
            first = _address$toLowerCase$2[1];

        var firstFields = first ? first.split(":").map(_stripLeadingZeros) : [];
        var lastFields = last.split(":").map(_stripLeadingZeros);
        var isLastFieldIPv4Address = protocol.IPV4ADDRESS.test(lastFields[lastFields.length - 1]);
        var fieldCount = isLastFieldIPv4Address ? 7 : 8;
        var lastFieldsStart = lastFields.length - fieldCount;
        var fields = Array(fieldCount);
        for (var x = 0; x < fieldCount; ++x) {
            fields[x] = firstFields[x] || lastFields[lastFieldsStart + x] || '';
        }
        if (isLastFieldIPv4Address) {
            fields[fieldCount - 1] = _normalizeIPv4(fields[fieldCount - 1], protocol);
        }
        var allZeroFields = fields.reduce(function (acc, field, index) {
            if (!field || field === "0") {
                var lastLongest = acc[acc.length - 1];
                if (lastLongest && lastLongest.index + lastLongest.length === index) {
                    lastLongest.length++;
                } else {
                    acc.push({ index: index, length: 1 });
                }
            }
            return acc;
        }, []);
        var longestZeroFields = allZeroFields.sort(function (a, b) {
            return b.length - a.length;
        })[0];
        var newHost = void 0;
        if (longestZeroFields && longestZeroFields.length > 1) {
            var newFirst = fields.slice(0, longestZeroFields.index);
            var newLast = fields.slice(longestZeroFields.index + longestZeroFields.length);
            newHost = newFirst.join(":") + "::" + newLast.join(":");
        } else {
            newHost = fields.join(":");
        }
        if (zone) {
            newHost += "%" + zone;
        }
        return newHost;
    } else {
        return host;
    }
}
var URI_PARSE = /^(?:([^:\/?#]+):)?(?:\/\/((?:([^\/?#@]*)@)?(\[[^\/?#\]]+\]|[^\/?#:]*)(?:\:(\d*))?))?([^?#]*)(?:\?([^#]*))?(?:#((?:.|\n|\r)*))?/i;
var NO_MATCH_IS_UNDEFINED = "".match(/(){0}/)[1] === undefined;
function parse(uriString) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    var components = {};
    var protocol = options.iri !== false ? IRI_PROTOCOL : URI_PROTOCOL;
    if (options.reference === "suffix") uriString = (options.scheme ? options.scheme + ":" : "") + "//" + uriString;
    var matches = uriString.match(URI_PARSE);
    if (matches) {
        if (NO_MATCH_IS_UNDEFINED) {
            //store each component
            components.scheme = matches[1];
            components.userinfo = matches[3];
            components.host = matches[4];
            components.port = parseInt(matches[5], 10);
            components.path = matches[6] || "";
            components.query = matches[7];
            components.fragment = matches[8];
            //fix port number
            if (isNaN(components.port)) {
                components.port = matches[5];
            }
        } else {
            //IE FIX for improper RegExp matching
            //store each component
            components.scheme = matches[1] || undefined;
            components.userinfo = uriString.indexOf("@") !== -1 ? matches[3] : undefined;
            components.host = uriString.indexOf("//") !== -1 ? matches[4] : undefined;
            components.port = parseInt(matches[5], 10);
            components.path = matches[6] || "";
            components.query = uriString.indexOf("?") !== -1 ? matches[7] : undefined;
            components.fragment = uriString.indexOf("#") !== -1 ? matches[8] : undefined;
            //fix port number
            if (isNaN(components.port)) {
                components.port = uriString.match(/\/\/(?:.|\n)*\:(?:\/|\?|\#|$)/) ? matches[4] : undefined;
            }
        }
        if (components.host) {
            //normalize IP hosts
            components.host = _normalizeIPv6(_normalizeIPv4(components.host, protocol), protocol);
        }
        //determine reference type
        if (components.scheme === undefined && components.userinfo === undefined && components.host === undefined && components.port === undefined && !components.path && components.query === undefined) {
            components.reference = "same-document";
        } else if (components.scheme === undefined) {
            components.reference = "relative";
        } else if (components.fragment === undefined) {
            components.reference = "absolute";
        } else {
            components.reference = "uri";
        }
        //check for reference errors
        if (options.reference && options.reference !== "suffix" && options.reference !== components.reference) {
            components.error = components.error || "URI is not a " + options.reference + " reference.";
        }
        //find scheme handler
        var schemeHandler = SCHEMES[(options.scheme || components.scheme || "").toLowerCase()];
        //check if scheme can't handle IRIs
        if (!options.unicodeSupport && (!schemeHandler || !schemeHandler.unicodeSupport)) {
            //if host component is a domain name
            if (components.host && (options.domainHost || schemeHandler && schemeHandler.domainHost)) {
                //convert Unicode IDN -> ASCII IDN
                try {
                    components.host = punycode.toASCII(components.host.replace(protocol.PCT_ENCODED, pctDecChars).toLowerCase());
                } catch (e) {
                    components.error = components.error || "Host's domain name can not be converted to ASCII via punycode: " + e;
                }
            }
            //convert IRI -> URI
            _normalizeComponentEncoding(components, URI_PROTOCOL);
        } else {
            //normalize encodings
            _normalizeComponentEncoding(components, protocol);
        }
        //perform scheme specific parsing
        if (schemeHandler && schemeHandler.parse) {
            schemeHandler.parse(components, options);
        }
    } else {
        components.error = components.error || "URI can not be parsed.";
    }
    return components;
}

function _recomposeAuthority(components, options) {
    var protocol = options.iri !== false ? IRI_PROTOCOL : URI_PROTOCOL;
    var uriTokens = [];
    if (components.userinfo !== undefined) {
        uriTokens.push(components.userinfo);
        uriTokens.push("@");
    }
    if (components.host !== undefined) {
        //normalize IP hosts, add brackets and escape zone separator for IPv6
        uriTokens.push(_normalizeIPv6(_normalizeIPv4(String(components.host), protocol), protocol).replace(protocol.IPV6ADDRESS, function (_, $1, $2) {
            return "[" + $1 + ($2 ? "%25" + $2 : "") + "]";
        }));
    }
    if (typeof components.port === "number") {
        uriTokens.push(":");
        uriTokens.push(components.port.toString(10));
    }
    return uriTokens.length ? uriTokens.join("") : undefined;
}

var RDS1 = /^\.\.?\//;
var RDS2 = /^\/\.(\/|$)/;
var RDS3 = /^\/\.\.(\/|$)/;
var RDS5 = /^\/?(?:.|\n)*?(?=\/|$)/;
function removeDotSegments(input) {
    var output = [];
    while (input.length) {
        if (input.match(RDS1)) {
            input = input.replace(RDS1, "");
        } else if (input.match(RDS2)) {
            input = input.replace(RDS2, "/");
        } else if (input.match(RDS3)) {
            input = input.replace(RDS3, "/");
            output.pop();
        } else if (input === "." || input === "..") {
            input = "";
        } else {
            var im = input.match(RDS5);
            if (im) {
                var s = im[0];
                input = input.slice(s.length);
                output.push(s);
            } else {
                throw new Error("Unexpected dot segment condition");
            }
        }
    }
    return output.join("");
}

function serialize(components) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    var protocol = options.iri ? IRI_PROTOCOL : URI_PROTOCOL;
    var uriTokens = [];
    //find scheme handler
    var schemeHandler = SCHEMES[(options.scheme || components.scheme || "").toLowerCase()];
    //perform scheme specific serialization
    if (schemeHandler && schemeHandler.serialize) schemeHandler.serialize(components, options);
    if (components.host) {
        //if host component is an IPv6 address
        if (protocol.IPV6ADDRESS.test(components.host)) {}
        //TODO: normalize IPv6 address as per RFC 5952

        //if host component is a domain name
        else if (options.domainHost || schemeHandler && schemeHandler.domainHost) {
                //convert IDN via punycode
                try {
                    components.host = !options.iri ? punycode.toASCII(components.host.replace(protocol.PCT_ENCODED, pctDecChars).toLowerCase()) : punycode.toUnicode(components.host);
                } catch (e) {
                    components.error = components.error || "Host's domain name can not be converted to " + (!options.iri ? "ASCII" : "Unicode") + " via punycode: " + e;
                }
            }
    }
    //normalize encoding
    _normalizeComponentEncoding(components, protocol);
    if (options.reference !== "suffix" && components.scheme) {
        uriTokens.push(components.scheme);
        uriTokens.push(":");
    }
    var authority = _recomposeAuthority(components, options);
    if (authority !== undefined) {
        if (options.reference !== "suffix") {
            uriTokens.push("//");
        }
        uriTokens.push(authority);
        if (components.path && components.path.charAt(0) !== "/") {
            uriTokens.push("/");
        }
    }
    if (components.path !== undefined) {
        var s = components.path;
        if (!options.absolutePath && (!schemeHandler || !schemeHandler.absolutePath)) {
            s = removeDotSegments(s);
        }
        if (authority === undefined) {
            s = s.replace(/^\/\//, "/%2F"); //don't allow the path to start with "//"
        }
        uriTokens.push(s);
    }
    if (components.query !== undefined) {
        uriTokens.push("?");
        uriTokens.push(components.query);
    }
    if (components.fragment !== undefined) {
        uriTokens.push("#");
        uriTokens.push(components.fragment);
    }
    return uriTokens.join(""); //merge tokens into a string
}

function resolveComponents(base, relative) {
    var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
    var skipNormalization = arguments[3];

    var target = {};
    if (!skipNormalization) {
        base = parse(serialize(base, options), options); //normalize base components
        relative = parse(serialize(relative, options), options); //normalize relative components
    }
    options = options || {};
    if (!options.tolerant && relative.scheme) {
        target.scheme = relative.scheme;
        //target.authority = relative.authority;
        target.userinfo = relative.userinfo;
        target.host = relative.host;
        target.port = relative.port;
        target.path = removeDotSegments(relative.path || "");
        target.query = relative.query;
    } else {
        if (relative.userinfo !== undefined || relative.host !== undefined || relative.port !== undefined) {
            //target.authority = relative.authority;
            target.userinfo = relative.userinfo;
            target.host = relative.host;
            target.port = relative.port;
            target.path = removeDotSegments(relative.path || "");
            target.query = relative.query;
        } else {
            if (!relative.path) {
                target.path = base.path;
                if (relative.query !== undefined) {
                    target.query = relative.query;
                } else {
                    target.query = base.query;
                }
            } else {
                if (relative.path.charAt(0) === "/") {
                    target.path = removeDotSegments(relative.path);
                } else {
                    if ((base.userinfo !== undefined || base.host !== undefined || base.port !== undefined) && !base.path) {
                        target.path = "/" + relative.path;
                    } else if (!base.path) {
                        target.path = relative.path;
                    } else {
                        target.path = base.path.slice(0, base.path.lastIndexOf("/") + 1) + relative.path;
                    }
                    target.path = removeDotSegments(target.path);
                }
                target.query = relative.query;
            }
            //target.authority = base.authority;
            target.userinfo = base.userinfo;
            target.host = base.host;
            target.port = base.port;
        }
        target.scheme = base.scheme;
    }
    target.fragment = relative.fragment;
    return target;
}

function resolve(baseURI, relativeURI, options) {
    var schemelessOptions = assign({ scheme: 'null' }, options);
    return serialize(resolveComponents(parse(baseURI, schemelessOptions), parse(relativeURI, schemelessOptions), schemelessOptions, true), schemelessOptions);
}

function normalize(uri, options) {
    if (typeof uri === "string") {
        uri = serialize(parse(uri, options), options);
    } else if (typeOf(uri) === "object") {
        uri = parse(serialize(uri, options), options);
    }
    return uri;
}

function equal(uriA, uriB, options) {
    if (typeof uriA === "string") {
        uriA = serialize(parse(uriA, options), options);
    } else if (typeOf(uriA) === "object") {
        uriA = serialize(uriA, options);
    }
    if (typeof uriB === "string") {
        uriB = serialize(parse(uriB, options), options);
    } else if (typeOf(uriB) === "object") {
        uriB = serialize(uriB, options);
    }
    return uriA === uriB;
}

function escapeComponent(str, options) {
    return str && str.toString().replace(!options || !options.iri ? URI_PROTOCOL.ESCAPE : IRI_PROTOCOL.ESCAPE, pctEncChar);
}

function unescapeComponent(str, options) {
    return str && str.toString().replace(!options || !options.iri ? URI_PROTOCOL.PCT_ENCODED : IRI_PROTOCOL.PCT_ENCODED, pctDecChars);
}

var handler = {
    scheme: "http",
    domainHost: true,
    parse: function parse(components, options) {
        //report missing host
        if (!components.host) {
            components.error = components.error || "HTTP URIs must have a host.";
        }
        return components;
    },
    serialize: function serialize(components, options) {
        //normalize the default port
        if (components.port === (String(components.scheme).toLowerCase() !== "https" ? 80 : 443) || components.port === "") {
            components.port = undefined;
        }
        //normalize the empty path
        if (!components.path) {
            components.path = "/";
        }
        //NOTE: We do not parse query strings for HTTP URIs
        //as WWW Form Url Encoded query strings are part of the HTML4+ spec,
        //and not the HTTP spec.
        return components;
    }
};

var handler$1 = {
    scheme: "https",
    domainHost: handler.domainHost,
    parse: handler.parse,
    serialize: handler.serialize
};

var O = {};
var isIRI = true;
//RFC 3986
var UNRESERVED$$ = "[A-Za-z0-9\\-\\.\\_\\~" + (isIRI ? "\\xA0-\\u200D\\u2010-\\u2029\\u202F-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFEF" : "") + "]";
var HEXDIG$$ = "[0-9A-Fa-f]"; //case-insensitive
var PCT_ENCODED$ = subexp(subexp("%[EFef]" + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$) + "|" + subexp("%[89A-Fa-f]" + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$) + "|" + subexp("%" + HEXDIG$$ + HEXDIG$$)); //expanded
//RFC 5322, except these symbols as per RFC 6068: @ : / ? # [ ] & ; =
//const ATEXT$$ = "[A-Za-z0-9\\!\\#\\$\\%\\&\\'\\*\\+\\-\\/\\=\\?\\^\\_\\`\\{\\|\\}\\~]";
//const WSP$$ = "[\\x20\\x09]";
//const OBS_QTEXT$$ = "[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]";  //(%d1-8 / %d11-12 / %d14-31 / %d127)
//const QTEXT$$ = merge("[\\x21\\x23-\\x5B\\x5D-\\x7E]", OBS_QTEXT$$);  //%d33 / %d35-91 / %d93-126 / obs-qtext
//const VCHAR$$ = "[\\x21-\\x7E]";
//const WSP$$ = "[\\x20\\x09]";
//const OBS_QP$ = subexp("\\\\" + merge("[\\x00\\x0D\\x0A]", OBS_QTEXT$$));  //%d0 / CR / LF / obs-qtext
//const FWS$ = subexp(subexp(WSP$$ + "*" + "\\x0D\\x0A") + "?" + WSP$$ + "+");
//const QUOTED_PAIR$ = subexp(subexp("\\\\" + subexp(VCHAR$$ + "|" + WSP$$)) + "|" + OBS_QP$);
//const QUOTED_STRING$ = subexp('\\"' + subexp(FWS$ + "?" + QCONTENT$) + "*" + FWS$ + "?" + '\\"');
var ATEXT$$ = "[A-Za-z0-9\\!\\$\\%\\'\\*\\+\\-\\^\\_\\`\\{\\|\\}\\~]";
var QTEXT$$ = "[\\!\\$\\%\\'\\(\\)\\*\\+\\,\\-\\.0-9\\<\\>A-Z\\x5E-\\x7E]";
var VCHAR$$ = merge(QTEXT$$, "[\\\"\\\\]");
var SOME_DELIMS$$ = "[\\!\\$\\'\\(\\)\\*\\+\\,\\;\\:\\@]";
var UNRESERVED = new RegExp(UNRESERVED$$, "g");
var PCT_ENCODED = new RegExp(PCT_ENCODED$, "g");
var NOT_LOCAL_PART = new RegExp(merge("[^]", ATEXT$$, "[\\.]", '[\\"]', VCHAR$$), "g");
var NOT_HFNAME = new RegExp(merge("[^]", UNRESERVED$$, SOME_DELIMS$$), "g");
var NOT_HFVALUE = NOT_HFNAME;
function decodeUnreserved(str) {
    var decStr = pctDecChars(str);
    return !decStr.match(UNRESERVED) ? str : decStr;
}
var handler$2 = {
    scheme: "mailto",
    parse: function parse$$1(components, options) {
        var mailtoComponents = components;
        var to = mailtoComponents.to = mailtoComponents.path ? mailtoComponents.path.split(",") : [];
        mailtoComponents.path = undefined;
        if (mailtoComponents.query) {
            var unknownHeaders = false;
            var headers = {};
            var hfields = mailtoComponents.query.split("&");
            for (var x = 0, xl = hfields.length; x < xl; ++x) {
                var hfield = hfields[x].split("=");
                switch (hfield[0]) {
                    case "to":
                        var toAddrs = hfield[1].split(",");
                        for (var _x = 0, _xl = toAddrs.length; _x < _xl; ++_x) {
                            to.push(toAddrs[_x]);
                        }
                        break;
                    case "subject":
                        mailtoComponents.subject = unescapeComponent(hfield[1], options);
                        break;
                    case "body":
                        mailtoComponents.body = unescapeComponent(hfield[1], options);
                        break;
                    default:
                        unknownHeaders = true;
                        headers[unescapeComponent(hfield[0], options)] = unescapeComponent(hfield[1], options);
                        break;
                }
            }
            if (unknownHeaders) mailtoComponents.headers = headers;
        }
        mailtoComponents.query = undefined;
        for (var _x2 = 0, _xl2 = to.length; _x2 < _xl2; ++_x2) {
            var addr = to[_x2].split("@");
            addr[0] = unescapeComponent(addr[0]);
            if (!options.unicodeSupport) {
                //convert Unicode IDN -> ASCII IDN
                try {
                    addr[1] = punycode.toASCII(unescapeComponent(addr[1], options).toLowerCase());
                } catch (e) {
                    mailtoComponents.error = mailtoComponents.error || "Email address's domain name can not be converted to ASCII via punycode: " + e;
                }
            } else {
                addr[1] = unescapeComponent(addr[1], options).toLowerCase();
            }
            to[_x2] = addr.join("@");
        }
        return mailtoComponents;
    },
    serialize: function serialize$$1(mailtoComponents, options) {
        var components = mailtoComponents;
        var to = toArray(mailtoComponents.to);
        if (to) {
            for (var x = 0, xl = to.length; x < xl; ++x) {
                var toAddr = String(to[x]);
                var atIdx = toAddr.lastIndexOf("@");
                var localPart = toAddr.slice(0, atIdx).replace(PCT_ENCODED, decodeUnreserved).replace(PCT_ENCODED, toUpperCase).replace(NOT_LOCAL_PART, pctEncChar);
                var domain = toAddr.slice(atIdx + 1);
                //convert IDN via punycode
                try {
                    domain = !options.iri ? punycode.toASCII(unescapeComponent(domain, options).toLowerCase()) : punycode.toUnicode(domain);
                } catch (e) {
                    components.error = components.error || "Email address's domain name can not be converted to " + (!options.iri ? "ASCII" : "Unicode") + " via punycode: " + e;
                }
                to[x] = localPart + "@" + domain;
            }
            components.path = to.join(",");
        }
        var headers = mailtoComponents.headers = mailtoComponents.headers || {};
        if (mailtoComponents.subject) headers["subject"] = mailtoComponents.subject;
        if (mailtoComponents.body) headers["body"] = mailtoComponents.body;
        var fields = [];
        for (var name in headers) {
            if (headers[name] !== O[name]) {
                fields.push(name.replace(PCT_ENCODED, decodeUnreserved).replace(PCT_ENCODED, toUpperCase).replace(NOT_HFNAME, pctEncChar) + "=" + headers[name].replace(PCT_ENCODED, decodeUnreserved).replace(PCT_ENCODED, toUpperCase).replace(NOT_HFVALUE, pctEncChar));
            }
        }
        if (fields.length) {
            components.query = fields.join("&");
        }
        return components;
    }
};

var URN_PARSE = /^([^\:]+)\:(.*)/;
//RFC 2141
var handler$3 = {
    scheme: "urn",
    parse: function parse$$1(components, options) {
        var matches = components.path && components.path.match(URN_PARSE);
        var urnComponents = components;
        if (matches) {
            var scheme = options.scheme || urnComponents.scheme || "urn";
            var nid = matches[1].toLowerCase();
            var nss = matches[2];
            var urnScheme = scheme + ":" + (options.nid || nid);
            var schemeHandler = SCHEMES[urnScheme];
            urnComponents.nid = nid;
            urnComponents.nss = nss;
            urnComponents.path = undefined;
            if (schemeHandler) {
                urnComponents = schemeHandler.parse(urnComponents, options);
            }
        } else {
            urnComponents.error = urnComponents.error || "URN can not be parsed.";
        }
        return urnComponents;
    },
    serialize: function serialize$$1(urnComponents, options) {
        var scheme = options.scheme || urnComponents.scheme || "urn";
        var nid = urnComponents.nid;
        var urnScheme = scheme + ":" + (options.nid || nid);
        var schemeHandler = SCHEMES[urnScheme];
        if (schemeHandler) {
            urnComponents = schemeHandler.serialize(urnComponents, options);
        }
        var uriComponents = urnComponents;
        var nss = urnComponents.nss;
        uriComponents.path = (nid || options.nid) + ":" + nss;
        return uriComponents;
    }
};

var UUID = /^[0-9A-Fa-f]{8}(?:\-[0-9A-Fa-f]{4}){3}\-[0-9A-Fa-f]{12}$/;
//RFC 4122
var handler$4 = {
    scheme: "urn:uuid",
    parse: function parse(urnComponents, options) {
        var uuidComponents = urnComponents;
        uuidComponents.uuid = uuidComponents.nss;
        uuidComponents.nss = undefined;
        if (!options.tolerant && (!uuidComponents.uuid || !uuidComponents.uuid.match(UUID))) {
            uuidComponents.error = uuidComponents.error || "UUID is not valid.";
        }
        return uuidComponents;
    },
    serialize: function serialize(uuidComponents, options) {
        var urnComponents = uuidComponents;
        //normalize UUID
        urnComponents.nss = (uuidComponents.uuid || "").toLowerCase();
        return urnComponents;
    }
};

SCHEMES[handler.scheme] = handler;
SCHEMES[handler$1.scheme] = handler$1;
SCHEMES[handler$2.scheme] = handler$2;
SCHEMES[handler$3.scheme] = handler$3;
SCHEMES[handler$4.scheme] = handler$4;

exports.SCHEMES = SCHEMES;
exports.pctEncChar = pctEncChar;
exports.pctDecChars = pctDecChars;
exports.parse = parse;
exports.removeDotSegments = removeDotSegments;
exports.serialize = serialize;
exports.resolveComponents = resolveComponents;
exports.resolve = resolve;
exports.normalize = normalize;
exports.equal = equal;
exports.escapeComponent = escapeComponent;
exports.unescapeComponent = unescapeComponent;

Object.defineProperty(exports, '__esModule', { value: true });

})));


/***/ }),

/***/ "../common/schema/annotations.schema.json":
/*!************************************************!*\
  !*** ../common/schema/annotations.schema.json ***!
  \************************************************/
/*! exports provided: $id, $schema, title, description, definitions, type, properties, additionalProperties, default */
/***/ (function(module) {

module.exports = {"$id":"https://schemas.3d.si.edu/public_api/annotations.schema.json","$schema":"http://json-schema.org/draft-07/schema#","title":"Annotations","description":"Spatial annotations (hot spots, hot zones) on a 3D item. Annotations can reference documents.","definitions":{"annotation":{"$id":"#annotation","type":"object","properties":{"id":{"type":"string","minLength":1},"title":{"type":"string"},"description":{"type":"string"},"style":{"type":"string"},"expanded":{"description":"Flag indicating whether this annotation is displayed in expanded state.","type":"boolean","default":false},"documents":{"description":"Array of document indices, listing documents related to this annotation.","type":"array","items":{"type":"integer","minimum":0}},"groups":{"description":"Array of group indices, listing all groups this annotation belongs to.","type":"array","items":{"type":"integer","minimum":0}},"position":{"description":"Position where the annotation is anchored, in local item coordinates.","$ref":"math.schema.json#/definitions/vector3"},"direction":{"description":"Direction of the stem of this annotation, usually corresponds to the surface normal.","$ref":"math.schema.json#/definitions/vector3"},"zoneIndex":{"description":"Index of the zone on the zone texture.","type":"integer","minimum":0}},"required":["id"],"additionalProperties":false},"group":{"$id":"#group","type":"object","properties":{"id":{"type":"string"},"title":{"type":"string"},"description":{"type":"string"},"visible":{"type":"boolean"}},"required":["id"],"additionalProperties":false}},"type":"object","properties":{"annotations":{"type":"array","items":{"$ref":"#annotation"}},"groups":{"type":"array","items":{"$ref":"#group"}}},"additionalProperties":false};

/***/ }),

/***/ "../common/schema/config.schema.json":
/*!*******************************************!*\
  !*** ../common/schema/config.schema.json ***!
  \*******************************************/
/*! exports provided: $id, $schema, title, description, type, properties, default */
/***/ (function(module) {

module.exports = {"$id":"https://schemas.3d.si.edu/public_api/config.schema.json","$schema":"http://json-schema.org/draft-07/schema#","title":"Smithsonian 3D Presentation - Voyager Explorer Configuration","description":"contains information about Voyager explorer configuration (scene environment, UI, tools, etc.)","type":"object","properties":{"scene":{"type":"object","properties":{"units":{"type":"string","enum":["mm","cm","m","in","ft","yd"]},"shader":{"type":"string"},"exposure":{"type":"number"},"gamma":{"type":"number"}},"required":["units","shader","exposure","gamma"]},"reader":{"type":"object","properties":{"visible":{"type":"boolean"},"position":{"type":"string"},"documentUri":{"description":"URI of the document currently displayed in the reader","type":"string","minLength":1}}},"interface":{"type":"object","properties":{"visible":{"type":"boolean"},"logo":{"type":"boolean"}}},"navigation":{"type":"object","properties":{"type":{"type":"string","enum":["orbit","walk"]},"enabled":{"type":"boolean"},"orbit":{"$comment":"TODO: Implement","type":"object","properties":{}},"walk":{"$comment":"TODO: Implement","type":"object","properties":{}}}},"background":{"type":"object","properties":{"type":{"type":"string","enum":["Solid","LinearGradient","RadialGradient"]},"color0":{"$ref":"math.schema.json#/definitions/vector3"},"color1":{"$ref":"math.schema.json#/definitions/vector3"}}},"groundPlane":{"type":"object","properties":{"visible":{"type":"boolean"},"offset":{"type":"number"},"color":{"$ref":"math.schema.json#/definitions/vector3"},"shadowVisible":{"type":"boolean"},"shadowColor":{"$ref":"math.schema.json#/definitions/vector3"}}},"grid":{"type":"object","properties":{"visible":{"type":"boolean"},"color":{"$ref":"math.schema.json#/definitions/vector3"}}},"tapeTool":{"type":"object","properties":{"active":{"type":"boolean"},"startPosition":{"$ref":"math.schema.json#/definitions/vector3"},"startDirection":{"$ref":"math.schema.json#/definitions/vector3"},"endPosition":{"$ref":"math.schema.json#/definitions/vector3"},"endDirection":{"$ref":"math.schema.json#/definitions/vector3"}}},"sectionTool":{"type":"object","properties":{"active":{"type":"boolean"},"plane":{"$ref":"math.schema.json#/definitions/vector4"}}}}};

/***/ }),

/***/ "../common/schema/documents.schema.json":
/*!**********************************************!*\
  !*** ../common/schema/documents.schema.json ***!
  \**********************************************/
/*! exports provided: $id, $schema, title, description, definitions, type, properties, additionalProperties, default */
/***/ (function(module) {

module.exports = {"$id":"https://schemas.3d.si.edu/public_api/documents.schema.json","$schema":"http://json-schema.org/draft-07/schema#","title":"Documents","description":"References to external documents, articles or media files.","definitions":{"document":{"$id":"#document","type":"object","properties":{"id":{"type":"string","minLength":1},"title":{"type":"string"},"description":{"type":"string"},"uri":{"type":"string"},"mimeType":{"type":"string"},"thumbnailUri":{"type":"string"}},"required":["id"]}},"type":"object","properties":{"mainDocumentId":{"description":"Id of the main document. This is the default document displayed with the item.","type":"string"},"documents":{"type":"array","items":{"$ref":"#document"}}},"additionalProperties":false};

/***/ }),

/***/ "../common/schema/item.schema.json":
/*!*****************************************!*\
  !*** ../common/schema/item.schema.json ***!
  \*****************************************/
/*! exports provided: $id, $schema, title, description, definitions, type, properties, required, additionalProperties, default */
/***/ (function(module) {

module.exports = {"$id":"https://schemas.3d.si.edu/public_api/item.schema.json","$schema":"http://json-schema.org/draft-07/schema#","title":"3D Item","description":"Describes a Smithsonian DPO 3D repository item.","definitions":{"info":{"$id":"#info","type":"object","properties":{"type":{"type":"string"},"copyright":{"type":"string"},"generator":{"type":"string"},"version":{"type":"string"}},"required":["type","version"],"additionalProperties":false}},"type":"object","properties":{"info":{"description":"Information about the type, generator, and version of this data structure.","$ref":"#info"},"meta":{"description":"Meta data about this item, including title, record info, collection, etc.","$ref":"meta.schema.json"},"process":{"description":"Information about how this item was digitized and processed.","$ref":"process.schema.json"},"model":{"description":"Describes the visual representations (models, derivatives).","$ref":"model.schema.json"},"documents":{"description":"References to external documents (articles, media files) containing additional information.","$ref":"documents.schema.json"},"annotations":{"description":"Spatial annotations (hot spots, hot zones). Annotations can reference documents.","$ref":"annotations.schema.json"}},"required":["info"],"additionalProperties":false};

/***/ }),

/***/ "../common/schema/math.schema.json":
/*!*****************************************!*\
  !*** ../common/schema/math.schema.json ***!
  \*****************************************/
/*! exports provided: $id, $schema, title, description, definitions, default */
/***/ (function(module) {

module.exports = {"$id":"https://schemas.3d.si.edu/public_api/math.schema.json","$schema":"http://json-schema.org/draft-07/schema#","title":"Math","description":"Definitions for mathematical compound objects such as vectors and matrices.","definitions":{"vector2":{"description":"2-component vector.","$id":"#vector2","type":"array","items":{"type":"number"},"minItems":2,"maxItems":2,"default":[0,0]},"vector3":{"description":"3-component vector.","$id":"#vector3","type":"array","items":{"type":"number"},"minItems":3,"maxItems":3,"default":[0,0,0]},"vector4":{"description":"4-component vector.","$id":"#vector4","type":"array","items":{"type":"number"},"minItems":4,"maxItems":4,"default":[0,0,0,0]},"matrix3":{"description":"3 by 3, matrix, storage: column-major.","$id":"#matrix3","type":"array","items":{"type":"number"},"minItems":9,"maxItems":9,"default":[1,0,0,0,1,0,0,0,1]},"matrix4":{"description":"4 by 4 matrix, storage: column-major.","$id":"#matrix4","type":"array","items":{"type":"number"},"minItems":16,"maxItems":16,"default":[1,0,0,0,0,1,0,0,0,0,1,0,0,0,0,1]},"boundingBox":{"description":"Axis-aligned 3D bounding box.","$id":"#boundingBox","type":"object","properties":{"min":{"$ref":"#vector3"},"max":{"$ref":"#vector3"}},"required":["min","max"]}}};

/***/ }),

/***/ "../common/schema/meta.schema.json":
/*!*****************************************!*\
  !*** ../common/schema/meta.schema.json ***!
  \*****************************************/
/*! exports provided: $id, $schema, title, description, type, properties, required, default */
/***/ (function(module) {

module.exports = {"$id":"https://schemas.3d.si.edu/public_api/meta.schema.json","$schema":"http://json-schema.org/draft-07/schema#","title":"Meta","description":"Meta data about a 3D item, including title, record info, collection, etc.","type":"object","properties":{},"required":[]};

/***/ }),

/***/ "../common/schema/model.schema.json":
/*!******************************************!*\
  !*** ../common/schema/model.schema.json ***!
  \******************************************/
/*! exports provided: $id, $schema, title, description, definitions, type, properties, required, additionalProperties, default */
/***/ (function(module) {

module.exports = {"$id":"https://schemas.3d.si.edu/public_api/model.schema.json","$schema":"http://json-schema.org/draft-07/schema#","title":"Model","description":"Describes the visual representations (models, derivatives) of a 3D item.","definitions":{"material":{"$id":"#material","type":"object","properties":{}},"asset":{"description":"an individual resource for a 3D model.","$id":"#asset","type":"object","properties":{"uri":{"type":"string","minLength":1},"type":{"type":"string","enum":["Model","Geometry","Image","Points","Volume"]},"mimeType":{"type":"string","minLength":1},"byteSize":{"type":"integer","minimum":1},"numFaces":{"type":"integer","minimum":1},"imageSize":{"type":"integer","minimum":1},"mapType":{"type":"string","enum":["Color","Normal","Occlusion","Emissive","MetallicRoughness","Zone"]}},"required":["uri","type"]}},"type":"object","properties":{"units":{"type":"string","enum":["mm","cm","m","in","ft","yd"]},"translation":{"description":"Translation vector. Apply to bring model into a 'neutral' pose.","$ref":"math.schema.json#/definitions/vector3"},"rotation":{"description":"Rotation quaternion. Apply to bring model into a 'neutral' pose.","$ref":"math.schema.json#/definitions/vector4"},"boundingBox":{"description":"Bounding box for this model, shared by all derivatives.","$ref":"math.schema.json#/definitions/boundingBox"},"material":{"description":"Surface properties for this model, shared by all derivatives.","$ref":"#material"},"derivatives":{"description":"List of visual representations derived from the master model.","type":"array","items":{"type":"object","properties":{"usage":{"description":"usage categories for a derivative.","type":"string","enum":["Web2D","Web3D","Print","Editorial"]},"quality":{"type":"string","enum":["Thumb","Low","Medium","High","Highest","LOD","Stream"]},"assets":{"description":"List of individual resources this derivative is composed of.","type":"array","items":{"$ref":"#asset"}}}}}},"required":["units","derivatives"],"additionalProperties":false};

/***/ }),

/***/ "../common/schema/presentation.schema.json":
/*!*************************************************!*\
  !*** ../common/schema/presentation.schema.json ***!
  \*************************************************/
/*! exports provided: $id, $schema, title, description, definitions, type, properties, required, default */
/***/ (function(module) {

module.exports = {"$id":"https://schemas.3d.si.edu/public_api/presentation.schema.json","$schema":"http://json-schema.org/draft-07/schema#","title":"Smithsonian 3D Presentation","description":"Describes a 3D scene containing one or multiple 3D items.","definitions":{"node":{"$id":"#node","type":"object","properties":{"children":{"type":"array","description":"The indices of this node's children.","items":{"type":"integer","minimum":0},"uniqueItems":true,"minItems":1},"matrix":{"description":"A floating-point 4x4 transformation matrix stored in column-major order.","type":"array","items":{"type":"number"},"minItems":16,"maxItems":16,"default":[1,0,0,0,0,1,0,0,0,0,1,0,0,0,0,1]},"translation":{"description":"The node's translation along the x, y, and z axes.","type":"array","items":{"type":"number"},"minItems":3,"maxItems":3,"default":[0,0,0]},"rotation":{"description":"The node's unit quaternion rotation in the order (x, y, z, w), where w is the scalar.","type":"array","items":{"type":"number","minimum":-1,"maximum":1},"minItems":4,"maxItems":4,"default":[0,0,0,1]},"scale":{"description":"The node's non-uniform scale, given as the scaling factors along the x, y, and z axes.","type":"array","items":{"type":"number"},"minItems":3,"maxItems":3,"default":[1,1,1]},"item":{"description":"The index of the item in this node.","type":"integer","minimum":0},"reference":{"description":"The index of the reference in this node.","type":"integer","minimum":0},"camera":{"description":"The index of the camera in this node.","type":"integer","minimum":0},"light":{"description":"The index of the light in this node.","type":"integer","minimum":0}},"not":{"anyOf":[{"required":["matrix","translation"]},{"required":["matrix","rotation"]},{"required":["matrix","scale"]}]}},"reference":{"$id":"#reference","type":"object","properties":{"mimeType":{"type":"string"},"uri":{"type":"string"}},"required":["uri"]},"camera":{"$id":"#camera","type":"object","properties":{"type":{"description":"Specifies if the camera uses a perspective or orthographic projection.","type":"string","enum":["perspective","orthographic"]},"perspective":{"description":"A perspective camera containing properties to create a perspective projection matrix.","type":"object","properties":{"yfov":{"type":"number","description":"The floating-point vertical field of view in radians.","exclusiveMinimum":0},"aspectRatio":{"type":"number","description":"The floating-point aspect ratio of the field of view.","exclusiveMinimum":0},"znear":{"type":"number","description":"The floating-point distance to the near clipping plane.","exclusiveMinimum":0},"zfar":{"type":"number","description":"The floating-point distance to the far clipping plane.","exclusiveMinimum":0}},"required":["yfov","znear"]},"orthographic":{"description":"An orthographic camera containing properties to create an orthographic projection matrix.","type":"object","properties":{"xmag":{"type":"number","description":"The floating-point horizontal magnification of the view. Must not be zero."},"ymag":{"type":"number","description":"The floating-point vertical magnification of the view. Must not be zero."},"znear":{"type":"number","description":"The floating-point distance to the near clipping plane.","exclusiveMinimum":0},"zfar":{"type":"number","description":"The floating-point distance to the far clipping plane. `zfar` must be greater than `znear`.","exclusiveMinimum":0}},"required":["xmag","ymag","znear","zfar"]}},"required":["type"],"not":{"required":["perspective","orthographic"]}},"light":{"$id":"#light","type":"object","properties":{"type":{"description":"Specifies the type of the light source.","type":"string","enum":["ambient","directional","point","spot","hemisphere"]},"color":{"$ref":"#colorRGB"},"intensity":{"type":"number","minimum":0,"default":1},"castShadow":{"type":"boolean","default":false},"point":{"type":"object","properties":{"distance":{"type":"number","minimum":0},"decay":{"type":"number","minimum":0}}},"spot":{"type":"object","properties":{"distance":{"type":"number","minimum":0},"decay":{"type":"number","minimum":0},"angle":{"type":"number","minimum":0},"penumbra":{"type":"number","minimum":0}}},"hemisphere":{"type":"object","properties":{"groundColor":{"$ref":"#colorRGB"}}}},"required":["type"],"not":{"required":["point","spot","hemisphere"]}},"colorRGB":{"$id":"#colorRGB","type":"array","items":{"type":"number","minimum":0,"maximum":1},"minItems":3,"maxItems":3,"default":[1,1,1]}},"type":"object","properties":{"asset":{"type":"object","properties":{"copyright":{"type":"string","description":"A copyright message to credit the content creator."},"generator":{"type":"string","description":"Tool that generated this presentation description."},"version":{"type":"string","description":"Version of this presentation description."}}},"scene":{"description":"The root nodes of the scene.","type":"object","properties":{"nodes":{"description":"The indices of each root node.","type":"array","items":{"type":"integer","minimum":0},"uniqueItems":true,"minItems":1}},"minItems":1},"nodes":{"description":"An array of nodes.","type":"array","items":{"$ref":"#node"},"minItems":1},"items":{"description":"An array if items.","type":"array","items":{"$ref":"item.schema.json"},"minItems":1},"references":{"description":"An array of references.","type":"array","items":{"$ref":"#reference"},"minItems":1},"cameras":{"description":"An array of cameras.","type":"array","items":{"$ref":"#camera"},"minItems":1},"lights":{"description":"An array of lights.","type":"array","items":{"$ref":"#light"},"minItems":1},"story":{"description":"Presentation-level tours and snapshots","$ref":"story.schema.json"},"config":{"description":"Voyager explorer global settings.","$ref":"config.schema.json"}},"required":["scene","nodes"]};

/***/ }),

/***/ "../common/schema/process.schema.json":
/*!********************************************!*\
  !*** ../common/schema/process.schema.json ***!
  \********************************************/
/*! exports provided: $id, $schema, title, description, type, properties, required, default */
/***/ (function(module) {

module.exports = {"$id":"https://schemas.3d.si.edu/public_api/process.schema.json","$schema":"http://json-schema.org/draft-07/schema#","title":"Process","description":"Information about how a 3D item was digitized and processed.","type":"object","properties":{},"required":[]};

/***/ }),

/***/ "../common/schema/story.schema.json":
/*!******************************************!*\
  !*** ../common/schema/story.schema.json ***!
  \******************************************/
/*! exports provided: $id, $schema, title, description, definitions, type, properties, required, default */
/***/ (function(module) {

module.exports = {"$id":"https://schemas.3d.si.edu/public_api/story.schema.json","$schema":"http://json-schema.org/draft-07/schema#","title":"Story","description":"Animated tours and snapshots for a 3D item.","definitions":{"snapshot":{"$id":"#snapshot","type":"object","properties":{"title":{"type":"string"},"description":{"type":"string"},"properties":{"type":"array","items":{"properties":{"path":{"type":"string","minLength":1}},"required":["path","value"]}}}},"tourstep":{"$id":"#tourstep","type":"object","properties":{"snapshot":{"type":"integer","minimum":0},"transitionTime":{"type":"number","minimum":0},"transitionCurve":{"type":"string"},"transitionCutPoint":{"type":"number","minimum":0,"maximum":1}},"required":["snapshot"]}},"type":"object","properties":{"snapshots":{"type":"array","items":{"$ref":"#snapshot"}},"tours":{"type":"array","items":{"type":"object","properties":{"title":{"type":"string"},"description":{"type":"string"},"steps":{"type":"array","items":{"$ref":"#tourstep"}}}}}},"required":[]};

/***/ }),

/***/ "../common/types/config.ts":
/*!*********************************!*\
  !*** ../common/types/config.ts ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const item_1 = __webpack_require__(/*! ./item */ "../common/types/item.ts");
exports.EUnitType = item_1.EUnitType;
var EShaderMode;
(function (EShaderMode) {
    EShaderMode[EShaderMode["Default"] = 0] = "Default";
    EShaderMode[EShaderMode["Clay"] = 1] = "Clay";
    EShaderMode[EShaderMode["XRay"] = 2] = "XRay";
    EShaderMode[EShaderMode["Normals"] = 3] = "Normals";
    EShaderMode[EShaderMode["Wireframe"] = 4] = "Wireframe";
})(EShaderMode = exports.EShaderMode || (exports.EShaderMode = {}));
var EBackgroundType;
(function (EBackgroundType) {
    EBackgroundType[EBackgroundType["Solid"] = 0] = "Solid";
    EBackgroundType[EBackgroundType["LinearGradient"] = 1] = "LinearGradient";
    EBackgroundType[EBackgroundType["RadialGradient"] = 2] = "RadialGradient";
})(EBackgroundType = exports.EBackgroundType || (exports.EBackgroundType = {}));
var ENavigationType;
(function (ENavigationType) {
    ENavigationType[ENavigationType["Orbit"] = 0] = "Orbit";
    ENavigationType[ENavigationType["Walk"] = 1] = "Walk";
})(ENavigationType = exports.ENavigationType || (exports.ENavigationType = {}));
var EReaderPosition;
(function (EReaderPosition) {
    EReaderPosition[EReaderPosition["Overlay"] = 0] = "Overlay";
    EReaderPosition[EReaderPosition["Left"] = 1] = "Left";
    EReaderPosition[EReaderPosition["Right"] = 2] = "Right";
})(EReaderPosition = exports.EReaderPosition || (exports.EReaderPosition = {}));


/***/ }),

/***/ "../common/types/item.ts":
/*!*******************************!*\
  !*** ../common/types/item.ts ***!
  \*******************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
var EUnitType;
(function (EUnitType) {
    EUnitType[EUnitType["mm"] = 0] = "mm";
    EUnitType[EUnitType["cm"] = 1] = "cm";
    EUnitType[EUnitType["m"] = 2] = "m";
    EUnitType[EUnitType["in"] = 3] = "in";
    EUnitType[EUnitType["ft"] = 4] = "ft";
    EUnitType[EUnitType["yd"] = 5] = "yd";
})(EUnitType = exports.EUnitType || (exports.EUnitType = {}));


/***/ }),

/***/ "./core/components/CVLoaders.ts":
/*!**************************************!*\
  !*** ./core/components/CVLoaders.ts ***!
  \**************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const resolve_pathname_1 = __webpack_require__(/*! resolve-pathname */ "../../node_modules/resolve-pathname/esm/resolve-pathname.js");
const THREE = __webpack_require__(/*! three */ "three");
const Component_1 = __webpack_require__(/*! @ff/graph/Component */ "../../libs/ff-graph/source/Component.ts");
const JSONLoader_1 = __webpack_require__(/*! ../loaders/JSONLoader */ "./core/loaders/JSONLoader.ts");
const JSONValidator_1 = __webpack_require__(/*! ../loaders/JSONValidator */ "./core/loaders/JSONValidator.ts");
const ModelLoader_1 = __webpack_require__(/*! ../loaders/ModelLoader */ "./core/loaders/ModelLoader.ts");
const GeometryLoader_1 = __webpack_require__(/*! ../loaders/GeometryLoader */ "./core/loaders/GeometryLoader.ts");
const TextureLoader_1 = __webpack_require__(/*! ../loaders/TextureLoader */ "./core/loaders/TextureLoader.ts");
////////////////////////////////////////////////////////////////////////////////
const _VERBOSE = false;
class CVLoaders extends Component_1.default {
    constructor(id) {
        super(id);
        const loadingManager = this._loadingManager = new PrivateLoadingManager();
        this.jsonLoader = new JSONLoader_1.default(loadingManager);
        this.validator = new JSONValidator_1.default();
        this.modelLoader = new ModelLoader_1.default(loadingManager);
        this.geometryLoader = new GeometryLoader_1.default(loadingManager);
        this.textureLoader = new TextureLoader_1.default(loadingManager);
    }
    loadJSON(url, path) {
        url = resolve_pathname_1.default(url, path);
        return this.jsonLoader.load(url);
    }
    loadModel(asset, path) {
        const url = resolve_pathname_1.default(asset.uri, path);
        return this.modelLoader.load(url);
    }
    loadGeometry(asset, path) {
        const url = resolve_pathname_1.default(asset.uri, path);
        return this.geometryLoader.load(url);
    }
    loadTexture(asset, path) {
        const url = resolve_pathname_1.default(asset.uri, path);
        return this.textureLoader.load(url);
    }
    validatePresentation(json) {
        return new Promise((resolve, reject) => {
            if (!this.validator.validatePresentation(json)) {
                return reject(new Error("invalid presentation data, validation failed"));
            }
            return resolve(json);
        });
    }
    validateItem(json) {
        return new Promise((resolve, reject) => {
            if (!this.validator.validateItem(json)) {
                return reject(new Error("invalid item data, validation failed"));
            }
            return resolve(json);
        });
    }
}
CVLoaders.type = "CVLoaders";
exports.default = CVLoaders;
////////////////////////////////////////////////////////////////////////////////
class PrivateLoadingManager extends THREE.LoadingManager {
    onLoadingStart() {
        if (_VERBOSE) {
            console.log("Loading files...");
        }
    }
    onLoadingProgress(url, itemsLoaded, itemsTotal) {
        if (_VERBOSE) {
            console.log(`Loaded ${itemsLoaded} of ${itemsTotal} files: ${url}`);
        }
    }
    onLoadingCompleted() {
        if (_VERBOSE) {
            console.log("Loading completed");
        }
    }
    onLoadingError() {
        if (_VERBOSE) {
            console.error(`Loading error`);
        }
    }
}


/***/ }),

/***/ "./core/components/CVModel.ts":
/*!************************************!*\
  !*** ./core/components/CVModel.ts ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const helpers = __webpack_require__(/*! @ff/three/helpers */ "../../libs/ff-three/source/helpers.ts");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CObject3D_1 = __webpack_require__(/*! @ff/scene/components/CObject3D */ "../../libs/ff-scene/source/components/CObject3D.ts");
const item_1 = __webpack_require__(/*! common/types/item */ "../common/types/item.ts");
const UberPBRMaterial_1 = __webpack_require__(/*! ../shaders/UberPBRMaterial */ "./core/shaders/UberPBRMaterial.ts");
exports.EShaderMode = UberPBRMaterial_1.EShaderMode;
const Derivative_1 = __webpack_require__(/*! ../models/Derivative */ "./core/models/Derivative.ts");
const DerivativeList_1 = __webpack_require__(/*! ../models/DerivativeList */ "./core/models/DerivativeList.ts");
const CVLoaders_1 = __webpack_require__(/*! ./CVLoaders */ "./core/components/CVLoaders.ts");
////////////////////////////////////////////////////////////////////////////////
const _vec3a = new THREE.Vector3();
const _vec3b = new THREE.Vector3();
const _quat = new THREE.Quaternion();
const _box = new THREE.Box3();
const _unitConversionFactor = {
    "mm": { "mm": 1, "cm": 0.1, "m": 0.001, "in": 0.0393701, "ft": 0.00328084, "yd": 0.00109361 },
    "cm": { "mm": 10, "cm": 1, "m": 0.01, "in": 0.393701, "ft": 0.0328084, "yd": 0.0109361 },
    "m": { "mm": 1000, "cm": 100, "m": 1, "in": 39.3701, "ft": 3.28084, "yd": 1.09361 },
    "in": { "mm": 25.4, "cm": 2.54, "m": 0.0254, "in": 1, "ft": 0.0833333, "yd": 0.0277778 },
    "ft": { "mm": 304.8, "cm": 30.48, "m": 0.3048, "in": 12, "ft": 1, "yd": 0.333334 },
    "yd": { "mm": 914.4, "cm": 91.44, "m": 0.9144, "in": 36, "ft": 3, "yd": 1 },
};
const ins = {
    visible: propertyTypes_1.types.Boolean("Visible", true),
    units: propertyTypes_1.types.Enum("Units", item_1.EUnitType, item_1.EUnitType.cm),
    quality: propertyTypes_1.types.Enum("Quality", Derivative_1.EDerivativeQuality, Derivative_1.EDerivativeQuality.High),
    autoLoad: propertyTypes_1.types.Boolean("Auto.Load", true),
    position: propertyTypes_1.types.Vector3("Pose.Position"),
    rotation: propertyTypes_1.types.Vector3("Pose.Rotation"),
    center: propertyTypes_1.types.Event("Pose.Center"),
    dumpDerivatives: propertyTypes_1.types.Event("Derivatives.Dump"),
};
const outs = {
    globalUnits: propertyTypes_1.types.Enum("GlobalUnits", item_1.EUnitType, item_1.EUnitType.cm),
    unitScale: propertyTypes_1.types.Number("UnitScale", { preset: 1, precision: 5 }),
};
/**
 * Renderable component representing a Voyager explorer model.
 */
class CVModel extends CObject3D_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
        this.outs = this.addOutputs(outs);
        this.assetPath = "";
        this.assetBaseName = "";
        this._derivatives = new DerivativeList_1.default();
        this._activeDerivative = null;
        this._boundingBox = new THREE.Box3();
        this._boxFrame = null;
    }
    get derivatives() {
        return this._derivatives;
    }
    get boundingBox() {
        return this._boundingBox;
    }
    get activeDerivative() {
        return this._activeDerivative;
    }
    create() {
        super.create();
        this.object3D = new THREE.Group();
    }
    update() {
        const ins = this.ins;
        if (!this.activeDerivative && ins.autoLoad.changed && ins.autoLoad.value) {
            this.autoLoad(ins.quality.value);
        }
        else if (ins.quality.changed) {
            const derivative = this.derivatives.select(Derivative_1.EDerivativeUsage.Web3D, ins.quality.value);
            if (derivative && derivative !== this.activeDerivative) {
                this.loadDerivative(derivative)
                    .catch(error => {
                    console.warn("Model.update - failed to load derivative");
                    console.warn(error);
                });
            }
        }
        if (ins.visible.changed) {
            this.object3D.visible = ins.visible.value;
        }
        if (ins.units.changed) {
            this.updateUnitScale();
            this.emit({ type: "change", what: "boundingBox", component: this });
        }
        if (ins.center.changed) {
            this.center();
        }
        if (ins.position.changed || ins.rotation.changed) {
            this.updateMatrixFromProps();
        }
        if (ins.dumpDerivatives.changed) {
            console.log(this.derivatives.toString(true));
        }
        return true;
    }
    dispose() {
        this.derivatives.clear();
        this._activeDerivative = null;
        super.dispose();
    }
    center() {
        const object3D = this.object3D;
        const position = this.ins.position;
        object3D.matrix.decompose(_vec3a, _quat, _vec3b);
        object3D.matrix.makeRotationFromQuaternion(_quat);
        _box.makeEmpty();
        helpers.computeLocalBoundingBox(object3D, _box, object3D.parent);
        _box.getCenter(_vec3a);
        _vec3a.multiplyScalar(-1).toArray(position.value);
        position.set();
    }
    setGlobalUnits(units) {
        this.outs.globalUnits.setValue(units);
        this.updateUnitScale();
    }
    setFromMatrix(matrix) {
        const { position, rotation } = this.ins;
        matrix.decompose(_vec3a, _quat, _vec3b);
        _vec3a.multiplyScalar(1 / this.outs.unitScale.value).toArray(position.value);
        helpers.quaternionToDegrees(_quat, CVModel.rotationOrder, rotation.value);
        position.set();
        rotation.set();
    }
    setShaderMode(shaderMode) {
        this.object3D.traverse(object => {
            const material = object["material"];
            if (material && material.isUberPBRMaterial) {
                material.setShaderMode(shaderMode);
            }
        });
    }
    deflate() {
        const data = this.toData();
        return data ? { data } : null;
    }
    inflate(json) {
        if (json.data) {
            this.fromData(json);
        }
    }
    toData() {
        const ins = this.ins;
        const data = {
            units: item_1.EUnitType[ins.units.value],
            derivatives: this.derivatives.toData()
        };
        data.boundingBox = {
            min: this._boundingBox.min.toArray(),
            max: this._boundingBox.max.toArray()
        };
        const position = ins.position.value;
        if (position[0] !== 0 || position[1] !== 0 || position[2] !== 0) {
            data.translation = _vec3a.toArray();
        }
        const rotation = ins.rotation.value;
        if (rotation[0] !== 0 || rotation[1] !== 0 || rotation[2] !== 0) {
            helpers.degreesToQuaternion(rotation, CVModel.rotationOrder, _quat);
            data.rotation = _quat.toArray();
        }
        //if (this.material) {
        // TODO: Implement
        //}
        return data;
    }
    fromData(data) {
        const { units, position, rotation } = this.ins;
        units.setValue(item_1.EUnitType[data.units] || 0);
        if (data.derivatives) {
            this.derivatives.fromData(data.derivatives);
        }
        if (data.translation || data.rotation) {
            position.setValue(data.translation ? data.translation.slice() : [0, 0, 0]);
            if (data.rotation) {
                _quat.fromArray(data.rotation);
                rotation.setValue(helpers.quaternionToDegrees(_quat, CVModel.rotationOrder));
            }
            else {
                rotation.setValue([0, 0, 0]);
            }
            this.updateMatrixFromProps();
        }
        if (data.boundingBox) {
            this._boundingBox.min.fromArray(data.boundingBox.min);
            this._boundingBox.max.fromArray(data.boundingBox.max);
            this._boxFrame = new THREE["Box3Helper"](this._boundingBox, "#ffffff");
            this.addObject3D(this._boxFrame);
            this.emit({ type: "change", what: "derivative", component: this });
        }
        //if (data.material) {
        // TODO: Implement
        //}
        // automatically display new derivatives if available
        this.ins.autoLoad.set();
    }
    inflateReferences() {
    }
    updateUnitScale() {
        const fromUnits = item_1.EUnitType[this.ins.units.getValidatedValue()];
        const toUnits = item_1.EUnitType[this.outs.globalUnits.value];
        this.outs.unitScale.setValue(_unitConversionFactor[fromUnits][toUnits]);
        //console.log("Model.updateUnitScale, from: %s, to: %s", fromUnits, toUnits);
        this.updateMatrixFromProps();
    }
    updateMatrixFromProps() {
        const ins = this.ins;
        const unitScale = this.outs.unitScale.value;
        _vec3a.fromArray(ins.position.value).multiplyScalar(unitScale);
        helpers.degreesToQuaternion(ins.rotation.value, CVModel.rotationOrder, _quat);
        _vec3b.setScalar(unitScale);
        const object3D = this.object3D;
        object3D.matrix.compose(_vec3a, _quat, _vec3b);
        object3D.matrixWorldNeedsUpdate = true;
    }
    /**
     * Automatically loads derivatives up to the given quality.
     * First loads the lowest available quality (usually thumb), then
     * loads the desired quality level.
     * @param quality
     */
    autoLoad(quality) {
        const sequence = [];
        const lowestQualityDerivative = this.derivatives.select(Derivative_1.EDerivativeUsage.Web3D, Derivative_1.EDerivativeQuality.Thumb);
        if (lowestQualityDerivative) {
            sequence.push(lowestQualityDerivative);
        }
        const targetQualityDerivative = this.derivatives.select(Derivative_1.EDerivativeUsage.Web3D, quality);
        if (targetQualityDerivative && targetQualityDerivative !== lowestQualityDerivative) {
            sequence.push(targetQualityDerivative);
        }
        if (sequence.length === 0) {
            return Promise.resolve();
        }
        // load sequence of derivatives one by one
        return sequence.reduce((promise, derivative) => {
            return promise.then(() => this.loadDerivative(derivative));
        }, Promise.resolve());
    }
    /**
     * Loads and displays the given derivative.
     * @param derivative
     */
    loadDerivative(derivative) {
        const loaders = this.system.components.safeGet(CVLoaders_1.default);
        return derivative.load(loaders, this.assetPath)
            .then(() => {
            if (!derivative.model) {
                return;
            }
            if (this._boxFrame) {
                this.removeObject3D(this._boxFrame);
                this._boxFrame.geometry.dispose();
            }
            if (this._activeDerivative) {
                this.removeObject3D(this._activeDerivative.model);
                this._activeDerivative.dispose();
            }
            helpers.computeLocalBoundingBox(derivative.model, this._boundingBox);
            this._activeDerivative = derivative;
            this.addObject3D(derivative.model);
            this.emit({ type: "change", what: "derivative", component: this });
            // TODO: Test
            //const bb = derivative.boundingBox;
            //const box = { min: bb.min.toArray(), max: bb.max.toArray() };
            //console.log("derivative bounding box: ", box);
        });
    }
}
CVModel.type = "CVModel";
CVModel.rotationOrder = "ZYX";
exports.default = CVModel;


/***/ }),

/***/ "./core/components/CVOrbitNavigation.ts":
/*!**********************************************!*\
  !*** ./core/components/CVOrbitNavigation.ts ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const math_1 = __webpack_require__(/*! @ff/core/math */ "../../libs/ff-core/source/math.ts");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const Component_1 = __webpack_require__(/*! @ff/graph/Component */ "../../libs/ff-graph/source/Component.ts");
const OrbitManipulator_1 = __webpack_require__(/*! @ff/three/OrbitManipulator */ "../../libs/ff-three/source/OrbitManipulator.ts");
const CRenderer_1 = __webpack_require__(/*! @ff/scene/components/CRenderer */ "../../libs/ff-scene/source/components/CRenderer.ts");
const CCamera_1 = __webpack_require__(/*! @ff/scene/components/CCamera */ "../../libs/ff-scene/source/components/CCamera.ts");
exports.EProjection = CCamera_1.EProjection;
const CVScene_1 = __webpack_require__(/*! ./CVScene */ "./core/components/CVScene.ts");
////////////////////////////////////////////////////////////////////////////////
const _box = new THREE.Box3();
const _size = new THREE.Vector3();
const _center = new THREE.Vector3();
const _translation = new THREE.Vector3();
const _orientationPreset = [
    [0, -90, 0],
    [0, 90, 0],
    [-90, 0, 0],
    [90, 0, 0],
    [0, 0, 0],
    [0, 180, 0],
];
const _replaceNull = function (vector, replacement) {
    for (let i = 0, n = vector.length; i < n; ++i) {
        vector[i] = vector[i] === null ? replacement : vector[i];
    }
    return vector;
};
var EViewPreset;
(function (EViewPreset) {
    EViewPreset[EViewPreset["Left"] = 0] = "Left";
    EViewPreset[EViewPreset["Right"] = 1] = "Right";
    EViewPreset[EViewPreset["Top"] = 2] = "Top";
    EViewPreset[EViewPreset["Bottom"] = 3] = "Bottom";
    EViewPreset[EViewPreset["Front"] = 4] = "Front";
    EViewPreset[EViewPreset["Back"] = 5] = "Back";
    EViewPreset[EViewPreset["None"] = 6] = "None";
})(EViewPreset = exports.EViewPreset || (exports.EViewPreset = {}));
const ins = {
    preset: propertyTypes_1.types.Enum("View.Preset", EViewPreset, EViewPreset.None),
    projection: propertyTypes_1.types.Enum("View.Projection", CCamera_1.EProjection, CCamera_1.EProjection.Perspective),
    enabled: propertyTypes_1.types.Boolean("Manip.Enabled", true),
    zoomExtents: propertyTypes_1.types.Event("Manip.ZoomExtents"),
    orbit: propertyTypes_1.types.Vector3("Manip.Orbit", [-25, -25, 0]),
    offset: propertyTypes_1.types.Vector3("Manip.Offset", [0, 0, 100]),
    minOrbit: propertyTypes_1.types.Vector3("Manip.Min.Orbit", [-90, -Infinity, -Infinity]),
    minOffset: propertyTypes_1.types.Vector3("Manip.Min.Offset", [-Infinity, -Infinity, 0.1]),
    maxOrbit: propertyTypes_1.types.Vector3("Manip.Max.Orbit", [90, Infinity, Infinity]),
    maxOffset: propertyTypes_1.types.Vector3("Manip.Max.Offset", [Infinity, Infinity, Infinity])
};
/**
 * Voyager explorer orbit navigation.
 * Controls manipulation and parameters of the camera.
 */
class CVOrbitNavigation extends Component_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
        this._manip = new OrbitManipulator_1.default();
        this._activeScene = null;
    }
    get renderer() {
        return this.system.graph.components.safeGet(CRenderer_1.default);
    }
    create() {
        super.create();
        this._manip.cameraMode = true;
        this.system.on(["pointer-down", "pointer-up", "pointer-move"], this.onPointer, this);
        this.system.on("wheel", this.onTrigger, this);
        this._activeScene = this.renderer.activeSceneComponent;
        this.renderer.on("active-scene", this.onActiveScene, this);
    }
    dispose() {
        super.dispose();
        this.system.off(["pointer-down", "pointer-up", "pointer-move"], this.onPointer, this);
        this.system.off("wheel", this.onTrigger, this);
        this.renderer.off("active-scene", this.onActiveScene, this);
        this._activeScene = null;
    }
    update() {
        const manip = this._manip;
        const component = this._activeScene ? this._activeScene.activeCameraComponent : null;
        const camera = component ? component.camera : null;
        const { projection, preset, zoomExtents, orbit, offset, minOrbit, minOffset, maxOrbit, maxOffset } = this.ins;
        if (camera && projection.changed) {
            camera.setProjection(projection.value);
            manip.orthographicMode = projection.value === CCamera_1.EProjection.Orthographic;
        }
        if (preset.changed && preset.value !== EViewPreset.None) {
            orbit.setValue(_orientationPreset[preset.getValidatedValue()].slice());
        }
        if (camera && zoomExtents.changed) {
            camera.updateMatrixWorld(false);
            _box.copy(this._activeScene.boundingBox);
            _box.applyMatrix4(camera.matrixWorldInverse);
            _box.getSize(_size);
            _box.getCenter(_center);
            const sizeXY = 0.75 * Math.max(_size.x, _size.y, _size.z);
            if (camera.isPerspectiveCamera) {
                offset.value[2] = sizeXY + sizeXY / (2 * Math.tan(camera.fov * math_1.default.DEG2RAD * 0.5));
            }
            else {
                offset.value[2] = _size.z * 2;
            }
            offset.set();
        }
        if (orbit.changed || offset.changed) {
            manip.orbit.fromArray(orbit.value);
            manip.offset.fromArray(offset.value);
        }
        if (minOrbit.changed || minOffset.changed || maxOrbit.changed || maxOffset.changed) {
            manip.minOrbit.fromArray(minOrbit.value);
            manip.minOffset.fromArray(minOffset.value);
            manip.maxOrbit.fromArray(maxOrbit.value);
            manip.maxOffset.fromArray(maxOffset.value);
        }
        return true;
    }
    tick() {
        const manip = this._manip;
        const component = this._activeScene && this._activeScene.activeCameraComponent;
        const ins = this.ins;
        if (ins.enabled.value) {
            const manipUpdated = manip.update();
            if (manipUpdated) {
                manip.orbit.toArray(ins.orbit.value);
                ins.orbit.set(true);
                manip.offset.toArray(ins.offset.value);
                ins.offset.set(true);
                ins.preset.setValue(EViewPreset.None, true);
            }
            if (component && (manipUpdated || this.updated)) {
                const camera = component.camera;
                const transformComponent = component.transform;
                if (transformComponent) {
                    this._manip.toObject(transformComponent.object3D);
                }
                else {
                    this._manip.toObject(camera);
                }
                if (camera.isOrthographicCamera) {
                    camera.size = this._manip.offset.z;
                    camera.updateProjectionMatrix();
                }
                return true;
            }
        }
        return false;
    }
    fromData(data) {
        const orbit = data.orbit;
        this.ins.copyValues({
            enabled: data.enabled,
            orbit: orbit.orbit.slice(),
            offset: orbit.offset.slice(),
            minOrbit: _replaceNull(orbit.minOrbit.slice(), -Infinity),
            maxOrbit: _replaceNull(orbit.maxOrbit.slice(), Infinity),
            minOffset: _replaceNull(orbit.minOffset.slice(), -Infinity),
            maxOffset: _replaceNull(orbit.maxOffset.slice(), Infinity),
        });
    }
    toData() {
        const ins = this.ins;
        return {
            type: "Orbit",
            enabled: ins.enabled.value,
            orbit: {
                orbit: ins.orbit.cloneValue(),
                offset: ins.offset.cloneValue(),
                minOrbit: ins.minOrbit.cloneValue(),
                maxOrbit: ins.maxOrbit.cloneValue(),
                minOffset: ins.minOffset.cloneValue(),
                maxOffset: ins.maxOffset.cloneValue(),
            }
        };
    }
    onPointer(event) {
        const viewport = event.viewport;
        if (viewport.viewportCamera) {
            return;
        }
        if (this.ins.enabled.value && this._activeScene && this._activeScene.activeCameraComponent) {
            this._manip.setViewportSize(viewport.width, viewport.height);
            this._manip.onPointer(event);
            event.stopPropagation = true;
        }
    }
    onTrigger(event) {
        const viewport = event.viewport;
        if (viewport.viewportCamera) {
            return;
        }
        if (this.ins.enabled.value && this._activeScene && this._activeScene.activeCameraComponent) {
            this._manip.setViewportSize(viewport.width, viewport.height);
            this._manip.onTrigger(event);
            event.stopPropagation = true;
        }
    }
    onActiveScene(event) {
        if (event.next instanceof CVScene_1.default) {
            this._activeScene = event.next;
            this.ins.zoomExtents.set();
        }
    }
}
CVOrbitNavigation.type = "CVOrbitNavigation";
exports.default = CVOrbitNavigation;


/***/ }),

/***/ "./core/components/CVScene.ts":
/*!************************************!*\
  !*** ./core/components/CVScene.ts ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const propertyTypes_1 = __webpack_require__(/*! @ff/graph/propertyTypes */ "../../libs/ff-graph/source/propertyTypes.ts");
const CScene_1 = __webpack_require__(/*! @ff/scene/components/CScene */ "../../libs/ff-scene/source/components/CScene.ts");
const config_1 = __webpack_require__(/*! common/types/config */ "../common/types/config.ts");
exports.EShaderMode = config_1.EShaderMode;
exports.EUnitType = config_1.EUnitType;
const CVModel_1 = __webpack_require__(/*! ./CVModel */ "./core/components/CVModel.ts");
const CVOrbitNavigation_1 = __webpack_require__(/*! ./CVOrbitNavigation */ "./core/components/CVOrbitNavigation.ts");
const ins = {
    units: propertyTypes_1.types.Enum("Scene.Units", config_1.EUnitType, config_1.EUnitType.cm),
    shader: propertyTypes_1.types.Enum("Renderer.Shader", config_1.EShaderMode),
    exposure: propertyTypes_1.types.Number("Renderer.Exposure", 1),
    gamma: propertyTypes_1.types.Number("Renderer.Gamma", 1),
    zoomExtents: propertyTypes_1.types.Event("ZoomExtents")
};
class CVScene extends CScene_1.default {
    constructor() {
        super(...arguments);
        this.ins = this.addInputs(ins);
        this.boundingBox = new THREE.Box3();
        this._zoomViews = false;
    }
    create() {
        this.scene.background = new THREE.TextureLoader().load("images/bg-gradient-blue.jpg");
        this.graph.components.on(CVModel_1.default, this.onModelComponent, this);
    }
    update(context) {
        super.update(context);
        const ins = this.ins;
        if (ins.units.changed) {
            this.updateModels();
        }
        if (ins.shader.changed) {
            const index = ins.shader.getValidatedValue();
            this.graph.components.getArray(CVModel_1.default).forEach(model => model.setShaderMode(index));
        }
        if (ins.zoomExtents.changed) {
            this._zoomViews = true;
            const manip = this.system.components.get(CVOrbitNavigation_1.default);
            if (manip) {
                manip.ins.zoomExtents.set();
            }
        }
        return true;
    }
    beforeRender(context) {
        if (this.updated) {
            context.renderer.toneMappingExposure = this.ins.exposure.value;
        }
        if (this._zoomViews) {
            context.viewport.moveCameraToView(this.boundingBox);
        }
    }
    finalize() {
        this._zoomViews = false;
    }
    fromData(data) {
        this.ins.copyValues({
            units: config_1.EUnitType[data.units] || config_1.EUnitType.mm,
            shader: config_1.EShaderMode[data.shader] || config_1.EShaderMode.Default,
            exposure: data.exposure !== undefined ? data.exposure : 1,
            gamma: data.gamma !== undefined ? data.gamma : 1
        });
    }
    toData() {
        const ins = this.ins;
        return {
            units: config_1.EUnitType[ins.units.value],
            shader: config_1.EShaderMode[ins.shader.value],
            exposure: ins.exposure.value,
            gamma: ins.gamma.value
        };
    }
    onModelComponent(event) {
        if (event.add) {
            event.component.setGlobalUnits(this.ins.units.value);
            event.component.on("change", this.updateModels, this);
        }
        else if (event.remove) {
            event.component.off("change", this.updateModels, this);
        }
    }
    updateModels() {
        // get bounding box of all models
        const box = this.boundingBox.makeEmpty();
        const models = this.transform.getChildren(CVModel_1.default, true);
        const units = this.ins.units.getValidatedValue();
        models.forEach(model => {
            model.setGlobalUnits(units);
            box.expandByObject(model.object3D);
        });
        this.emit({
            type: "change", what: "boundingBox", component: this
        });
    }
}
CVScene.type = "CVScene";
exports.default = CVScene;


/***/ }),

/***/ "./core/components/index.ts":
/*!**********************************!*\
  !*** ./core/components/index.ts ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const CVLoaders_1 = __webpack_require__(/*! ./CVLoaders */ "./core/components/CVLoaders.ts");
exports.CVLoaders = CVLoaders_1.default;
const CVModel_1 = __webpack_require__(/*! ./CVModel */ "./core/components/CVModel.ts");
exports.CVModel = CVModel_1.default;
const CVScene_1 = __webpack_require__(/*! ./CVScene */ "./core/components/CVScene.ts");
exports.CVScene = CVScene_1.default;
const CVOrbitNavigation_1 = __webpack_require__(/*! ./CVOrbitNavigation */ "./core/components/CVOrbitNavigation.ts");
exports.CVOrbitNavigation = CVOrbitNavigation_1.default;
exports.componentTypes = [
    CVLoaders_1.default,
    CVModel_1.default,
    CVScene_1.default,
    CVOrbitNavigation_1.default
];


/***/ }),

/***/ "./core/loaders/GeometryLoader.ts":
/*!****************************************!*\
  !*** ./core/loaders/GeometryLoader.ts ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
__webpack_require__(/*! three/examples/js/loaders/OBJLoader */ "../../node_modules/three/examples/js/loaders/OBJLoader.js");
const OBJLoader = THREE.OBJLoader;
__webpack_require__(/*! three/examples/js/loaders/PLYLoader */ "../../node_modules/three/examples/js/loaders/PLYLoader.js");
const PLYLoader = THREE.PLYLoader;
////////////////////////////////////////////////////////////////////////////////
class GeometryLoader {
    constructor(loadingManager) {
        this.objLoader = new OBJLoader(loadingManager);
        this.plyLoader = new PLYLoader(loadingManager);
    }
    canLoad(url) {
        const extension = url.split(".").pop().toLowerCase();
        return GeometryLoader.extensions.indexOf(extension) >= 0;
    }
    load(url) {
        const extension = url.split(".").pop().toLowerCase();
        return new Promise((resolve, reject) => {
            if (extension === "obj") {
                this.objLoader.load(url, result => {
                    const geometry = result.children[0].geometry;
                    console.log(geometry);
                    if (geometry && geometry.type === "Geometry" || geometry.type === "BufferGeometry") {
                        return resolve(geometry);
                    }
                    return reject(new Error(`Can't parse geometry from '${url}'`));
                });
            }
            else if (extension === "ply") {
                this.plyLoader.load(url, geometry => {
                    if (geometry && geometry.type === "Geometry" || geometry.type === "BufferGeometry") {
                        return resolve(geometry);
                    }
                    return reject(new Error(`Can't parse geometry from '${url}'`));
                });
            }
            else {
                throw new Error(`Can't load geometry, unknown extension: '${extension}' in '${url}'`);
            }
        });
    }
}
GeometryLoader.extensions = ["obj", "ply"];
exports.default = GeometryLoader;


/***/ }),

/***/ "./core/loaders/JSONLoader.ts":
/*!************************************!*\
  !*** ./core/loaders/JSONLoader.ts ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
////////////////////////////////////////////////////////////////////////////////
class JSONLoader {
    constructor(loadingManager) {
        this.loadingManager = loadingManager;
    }
    load(url) {
        this.loadingManager.itemStart(url);
        return fetch(url, {
            headers: {
                "Accept": "application/json"
            }
        }).then(result => {
            if (!result.ok) {
                this.loadingManager.itemError(url);
                throw new Error(`failed to fetch from '${url}', status: ${result.status} ${result.statusText}`);
            }
            this.loadingManager.itemEnd(url);
            return result.json();
        });
    }
}
exports.default = JSONLoader;


/***/ }),

/***/ "./core/loaders/JSONValidator.ts":
/*!***************************************!*\
  !*** ./core/loaders/JSONValidator.ts ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Ajv = __webpack_require__(/*! ajv */ "../../node_modules/ajv/lib/ajv.js");
const mathSchema = __webpack_require__(/*! common/schema/math.schema.json */ "../common/schema/math.schema.json");
const presentationSchema = __webpack_require__(/*! common/schema/presentation.schema.json */ "../common/schema/presentation.schema.json");
const configSchema = __webpack_require__(/*! common/schema/config.schema.json */ "../common/schema/config.schema.json");
const itemSchema = __webpack_require__(/*! common/schema/item.schema.json */ "../common/schema/item.schema.json");
const metaSchema = __webpack_require__(/*! common/schema/meta.schema.json */ "../common/schema/meta.schema.json");
const processSchema = __webpack_require__(/*! common/schema/process.schema.json */ "../common/schema/process.schema.json");
const modelSchema = __webpack_require__(/*! common/schema/model.schema.json */ "../common/schema/model.schema.json");
const annotationsSchema = __webpack_require__(/*! common/schema/annotations.schema.json */ "../common/schema/annotations.schema.json");
const storySchema = __webpack_require__(/*! common/schema/story.schema.json */ "../common/schema/story.schema.json");
const documentsSchema = __webpack_require__(/*! common/schema/documents.schema.json */ "../common/schema/documents.schema.json");
////////////////////////////////////////////////////////////////////////////////
class JSONValidator {
    constructor() {
        this._schemaValidator = new Ajv({
            schemas: [
                mathSchema,
                presentationSchema,
                configSchema,
                itemSchema,
                metaSchema,
                processSchema,
                modelSchema,
                documentsSchema,
                annotationsSchema,
                storySchema
            ],
            allErrors: true
        });
        this._validatePresentation = this._schemaValidator.getSchema("https://schemas.3d.si.edu/public_api/presentation.schema.json");
        this._validateItem = this._schemaValidator.getSchema("https://schemas.3d.si.edu/public_api/item.schema.json");
    }
    validatePresentation(presentation) {
        if (!this._validatePresentation(presentation)) {
            console.warn(this._schemaValidator.errorsText(this._validatePresentation.errors, { separator: ", ", dataVar: "presentation" }));
            return false;
        }
        console.log("JSONValidator.validatePresentation - OK");
        return true;
    }
    validateItem(item) {
        if (!this._validateItem(item)) {
            console.warn(this._schemaValidator.errorsText(this._validateItem.errors, { separator: ", ", dataVar: "item" }));
            return false;
        }
        console.log("JSONValidator.validateItem - OK");
        return true;
    }
}
exports.default = JSONValidator;


/***/ }),

/***/ "./core/loaders/ModelLoader.ts":
/*!*************************************!*\
  !*** ./core/loaders/ModelLoader.ts ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const resolve_pathname_1 = __webpack_require__(/*! resolve-pathname */ "../../node_modules/resolve-pathname/esm/resolve-pathname.js");
const THREE = __webpack_require__(/*! three */ "three");
__webpack_require__(/*! three/examples/js/loaders/GLTFLoader */ "../../node_modules/three/examples/js/loaders/GLTFLoader.js");
__webpack_require__(/*! three/examples/js/loaders/DRACOLoader */ "../../node_modules/three/examples/js/loaders/DRACOLoader.js");
const GLTFLoader = THREE.GLTFLoader;
const DRACOLoader = THREE.DRACOLoader;
const dracoPath = resolve_pathname_1.default("js/draco/", window.location.origin + window.location.pathname);
DRACOLoader.setDecoderPath(dracoPath);
console.log("DRACOLoader.setDracoPath - %s", dracoPath);
const UberPBRMaterial_1 = __webpack_require__(/*! ../shaders/UberPBRMaterial */ "./core/shaders/UberPBRMaterial.ts");
////////////////////////////////////////////////////////////////////////////////
class ModelLoader {
    constructor(loadingManager) {
        this.loadingManager = loadingManager;
        this.gltfLoader = new GLTFLoader(loadingManager);
        this.gltfLoader.setDRACOLoader(new DRACOLoader());
    }
    canLoad(url) {
        const extension = url.split(".").pop().toLowerCase();
        return ModelLoader.extensions.indexOf(extension) >= 0;
    }
    canLoadMimeType(mimeType) {
        return ModelLoader.mimeTypes.indexOf(mimeType) >= 0;
    }
    load(url) {
        return new Promise((resolve, reject) => {
            this.gltfLoader.load(url, gltf => {
                resolve(this.createModelGroup(gltf));
            }, null, error => {
                console.error(`failed to load '${url}': ${error}`);
                reject(new Error(error));
            });
        });
    }
    createModelGroup(gltf) {
        const scene = gltf.scene;
        if (scene.type !== "Scene") {
            throw new Error("not a valid gltf scene");
        }
        const model = new THREE.Group();
        scene.children.forEach(child => model.add(child));
        model.traverse((object) => {
            if (object.type === "Mesh") {
                const mesh = object;
                const material = mesh.material;
                if (material.map) {
                    material.map.encoding = THREE.LinearEncoding;
                }
                mesh.geometry.computeBoundingBox();
                const uberMat = new UberPBRMaterial_1.default();
                if (material.type === "MeshStandardMaterial") {
                    uberMat.copyStandardMaterial(material);
                }
                // TODO: Temp to correct test assets
                uberMat.roughness = 0.6;
                uberMat.metalness = 0;
                uberMat.enableObjectSpaceNormalMap(false);
                if (!uberMat.map) {
                    uberMat.color.set("#c0c0c0");
                }
                mesh.material = uberMat;
            }
        });
        return model;
    }
}
ModelLoader.extensions = ["gltf", "glb"];
ModelLoader.mimeTypes = ["model/gltf+json", "model/gltf-binary"];
exports.default = ModelLoader;


/***/ }),

/***/ "./core/loaders/TextureLoader.ts":
/*!***************************************!*\
  !*** ./core/loaders/TextureLoader.ts ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
////////////////////////////////////////////////////////////////////////////////
class TextureLoader {
    constructor(loadingManager) {
        this.textureLoader = new THREE.TextureLoader(loadingManager);
    }
    canLoad(url) {
        const extension = url.split(".").pop().toLowerCase();
        return TextureLoader.extensions.indexOf(extension) >= 0;
    }
    canLoadMimeType(mimeType) {
        return TextureLoader.mimeTypes.indexOf(mimeType) >= 0;
    }
    load(url) {
        return new Promise((resolve, reject) => {
            this.textureLoader.load(url, texture => {
                resolve(texture);
            }, null, errorEvent => {
                console.error(errorEvent);
                reject(new Error(errorEvent.message));
            });
        });
    }
    loadImmediate(url) {
        return this.textureLoader.load(url, null, null, errorEvent => {
            console.error(errorEvent);
        });
    }
}
TextureLoader.extensions = ["jpg", "png"];
TextureLoader.mimeTypes = ["image/jpeg", "image/png"];
exports.default = TextureLoader;


/***/ }),

/***/ "./core/models/Asset.ts":
/*!******************************!*\
  !*** ./core/models/Asset.ts ***!
  \******************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
var EAssetType;
(function (EAssetType) {
    EAssetType[EAssetType["Model"] = 0] = "Model";
    EAssetType[EAssetType["Geometry"] = 1] = "Geometry";
    EAssetType[EAssetType["Image"] = 2] = "Image";
    EAssetType[EAssetType["Texture"] = 3] = "Texture";
    EAssetType[EAssetType["Points"] = 4] = "Points";
    EAssetType[EAssetType["Volume"] = 5] = "Volume";
})(EAssetType = exports.EAssetType || (exports.EAssetType = {}));
var EMapType;
(function (EMapType) {
    EMapType[EMapType["Color"] = 0] = "Color";
    EMapType[EMapType["Normal"] = 1] = "Normal";
    EMapType[EMapType["Occlusion"] = 2] = "Occlusion";
    EMapType[EMapType["Emissive"] = 3] = "Emissive";
    EMapType[EMapType["MetallicRoughness"] = 4] = "MetallicRoughness";
    EMapType[EMapType["Zone"] = 5] = "Zone";
})(EMapType = exports.EMapType || (exports.EMapType = {}));
class Asset {
    constructor(typeOrData, uri) {
        this.uri = "";
        this.mimeType = "";
        this.type = undefined;
        this.mapType = undefined;
        this.byteSize = 0;
        this.numFaces = 0;
        this.numVertices = 0;
        this.imageSize = 0;
        if (uri === undefined) {
            this.fromData(typeOrData);
        }
        else {
            this.type = typeOrData;
            this.uri = uri;
        }
    }
    isValid() {
        return !!this.uri && this.type !== undefined;
    }
    fromData(assetData) {
        this.uri = assetData.uri;
        this.mimeType = assetData.mimeType || "";
        this.type = EAssetType[assetData.type];
        this.mapType = EMapType[assetData.mapType];
        this.byteSize = assetData.byteSize || 0;
        this.numFaces = assetData.numFaces || 0;
        this.numVertices = assetData.numVertices || 0;
        this.imageSize = assetData.imageSize || 0;
        if (this.type === undefined) {
            this.type = this.guessAssetType();
            if (this.type === undefined) {
                console.warn(`failed to determine asset type from asset: ${this.uri}`);
            }
        }
    }
    toData() {
        const data = {
            uri: this.uri,
            type: EAssetType[this.type]
        };
        if (this.mimeType) {
            data.mimeType = this.mimeType;
        }
        if (this.mapType !== undefined) {
            data.mapType = EMapType[this.mapType];
        }
        if (this.byteSize > 0) {
            data.byteSize = this.byteSize;
        }
        if (this.type === EAssetType.Model || this.type === EAssetType.Geometry) {
            if (this.numFaces > 0) {
                data.numFaces = this.numFaces;
            }
            if (this.numVertices > 0) {
                data.numVertices = this.numVertices;
            }
        }
        if (this.type === EAssetType.Image || this.type === EAssetType.Texture) {
            if (this.imageSize > 0) {
                data.imageSize = this.imageSize;
            }
        }
        return data;
    }
    toString() {
        return `Asset - type: '${EAssetType[this.type]}', uri: '${this.uri}', mime type: '${this.mimeType || "(not set)"}'`;
    }
    guessAssetType() {
        if (this.type !== undefined && EAssetType[this.type]) {
            return this.type;
        }
        if (this.mimeType) {
            if (this.mimeType === Asset.mimeType.gltfJson || this.mimeType === Asset.mimeType.gltfBinary) {
                return EAssetType.Model;
            }
            if (this.mimeType === Asset.mimeType.imageJpeg || this.mimeType === Asset.mimeType.imagePng) {
                return EAssetType.Image;
            }
        }
        const extension = this.uri.split(".").pop().toLowerCase();
        if (extension === "gltf" || extension === "glb") {
            return EAssetType.Model;
        }
        if (extension === "obj" || extension === "ply") {
            return EAssetType.Geometry;
        }
        if (extension === "jpg" || extension === "png") {
            return EAssetType.Image;
        }
        return undefined;
    }
}
Asset.mimeType = {
    gltfJson: "model/gltf+json",
    gltfBinary: "model/gltf-binary",
    imageJpeg: "image/jpeg",
    imagePng: "image/png"
};
exports.default = Asset;


/***/ }),

/***/ "./core/models/Derivative.ts":
/*!***********************************!*\
  !*** ./core/models/Derivative.ts ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const helpers_1 = __webpack_require__(/*! @ff/three/helpers */ "../../libs/ff-three/source/helpers.ts");
const UberPBRMaterial_1 = __webpack_require__(/*! ../shaders/UberPBRMaterial */ "./core/shaders/UberPBRMaterial.ts");
const Asset_1 = __webpack_require__(/*! ./Asset */ "./core/models/Asset.ts");
exports.Asset = Asset_1.default;
exports.EAssetType = Asset_1.EAssetType;
var EDerivativeUsage;
(function (EDerivativeUsage) {
    EDerivativeUsage[EDerivativeUsage["Web2D"] = 0] = "Web2D";
    EDerivativeUsage[EDerivativeUsage["Web3D"] = 1] = "Web3D";
    EDerivativeUsage[EDerivativeUsage["Print"] = 2] = "Print";
    EDerivativeUsage[EDerivativeUsage["Editorial"] = 3] = "Editorial";
})(EDerivativeUsage = exports.EDerivativeUsage || (exports.EDerivativeUsage = {}));
var EDerivativeQuality;
(function (EDerivativeQuality) {
    EDerivativeQuality[EDerivativeQuality["Thumb"] = 0] = "Thumb";
    EDerivativeQuality[EDerivativeQuality["Low"] = 1] = "Low";
    EDerivativeQuality[EDerivativeQuality["Medium"] = 2] = "Medium";
    EDerivativeQuality[EDerivativeQuality["High"] = 3] = "High";
    EDerivativeQuality[EDerivativeQuality["Highest"] = 4] = "Highest";
    EDerivativeQuality[EDerivativeQuality["LOD"] = 5] = "LOD";
    EDerivativeQuality[EDerivativeQuality["Stream"] = 6] = "Stream";
})(EDerivativeQuality = exports.EDerivativeQuality || (exports.EDerivativeQuality = {}));
class Derivative {
    constructor(usageOrData, quality) {
        this.id = "";
        if (quality === undefined) {
            this.fromData(usageOrData);
        }
        else {
            this.usage = usageOrData;
            this.quality = quality;
            this.assets = [];
        }
        this.model = null;
    }
    dispose() {
        if (this.model) {
            helpers_1.disposeObject(this.model);
        }
    }
    load(loaders, assetPath) {
        if (this.usage !== EDerivativeUsage.Web3D) {
            throw new Error("can't load, not a Web3D derivative");
        }
        console.log("Derivative.load - asset path: %s", assetPath);
        const modelAsset = this.findAsset(Asset_1.EAssetType.Model);
        if (modelAsset) {
            return loaders.loadModel(modelAsset, assetPath)
                .then(object => {
                if (this.model) {
                    helpers_1.disposeObject(this.model);
                }
                this.model = object;
                return object;
            });
        }
        const geoAsset = this.findAsset(Asset_1.EAssetType.Geometry);
        const imageAssets = this.findAssets(Asset_1.EAssetType.Image);
        if (geoAsset) {
            return loaders.loadGeometry(geoAsset, assetPath)
                .then(geometry => {
                this.model = new THREE.Mesh(geometry, new UberPBRMaterial_1.default());
                return Promise.all(imageAssets.map(asset => loaders.loadTexture(asset, assetPath)))
                    .catch(error => {
                    console.warn("failed to load texture files");
                    return [];
                });
            })
                .then(textures => {
                const material = this.model.material;
                this.assignTextures(imageAssets, textures, material);
                if (!material.map) {
                    material.color.setScalar(0.5);
                    material.roughness = 0.8;
                    material.metalness = 0;
                }
                return this.model;
            });
        }
    }
    createAsset(type, uri) {
        if (!uri) {
            throw new Error("uri must be specified");
        }
        const asset = new Asset_1.default(type, uri);
        this.assets.push(asset);
        return asset;
    }
    fromData(data) {
        this.usage = EDerivativeUsage[data.usage];
        if (this.usage === undefined) {
            throw new Error(`unknown derivative usage: ${data.usage}`);
        }
        this.quality = EDerivativeQuality[data.quality];
        if (this.quality === undefined) {
            throw new Error(`unknown derivative quality: ${data.quality}`);
        }
        this.assets = data.assets.map(assetData => new Asset_1.default(assetData));
    }
    toData() {
        return {
            usage: EDerivativeUsage[this.usage],
            quality: EDerivativeQuality[this.quality],
            assets: this.assets.map(asset => asset.toData())
        };
    }
    toString(verbose = false) {
        if (verbose) {
            return `Derivative - usage: '${EDerivativeUsage[this.usage]}', quality: '${EDerivativeQuality[this.quality]}'\n   `
                + this.assets.map(asset => asset.toString()).join("\n   ");
        }
        else {
            return `Derivative - usage: '${EDerivativeUsage[this.usage]}', quality: '${EDerivativeQuality[this.quality]}', #assets: ${this.assets.length})`;
        }
    }
    findAsset(type) {
        return this.assets.find(asset => asset.type === type);
    }
    findAssets(type) {
        return this.assets.filter(asset => asset.type === type);
    }
    assignTextures(assets, textures, material) {
        for (let i = 0; i < assets.length; ++i) {
            const asset = assets[i];
            const texture = textures[i];
            switch (asset.mapType) {
                case Asset_1.EMapType.Color:
                    material.map = texture;
                    break;
                case Asset_1.EMapType.Occlusion:
                    material.aoMap = texture;
                    break;
                case Asset_1.EMapType.Emissive:
                    material.emissiveMap = texture;
                    break;
                case Asset_1.EMapType.MetallicRoughness:
                    material.metalnessMap = texture;
                    material.roughnessMap = texture;
                    break;
                case Asset_1.EMapType.Normal:
                    material.normalMap = texture;
                    break;
            }
        }
    }
    disposeMesh(mesh) {
        mesh.geometry.dispose();
        const material = mesh.material;
        if (material.map) {
            material.map.dispose();
        }
        if (material.aoMap) {
            material.aoMap.dispose();
        }
        if (material.emissiveMap) {
            material.emissiveMap.dispose();
        }
        if (material.metalnessMap) {
            material.metalnessMap.dispose();
        }
        if (material.roughnessMap) {
            material.roughnessMap.dispose();
        }
        if (material.normalMap) {
            material.normalMap.dispose();
        }
    }
}
exports.default = Derivative;


/***/ }),

/***/ "./core/models/DerivativeList.ts":
/*!***************************************!*\
  !*** ./core/models/DerivativeList.ts ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const Derivative_1 = __webpack_require__(/*! ./Derivative */ "./core/models/Derivative.ts");
const Asset_1 = __webpack_require__(/*! ./Asset */ "./core/models/Asset.ts");
////////////////////////////////////////////////////////////////////////////////
const _EMPTY_ARRAY = [];
const _qualityLevels = [
    Derivative_1.EDerivativeQuality.Thumb,
    Derivative_1.EDerivativeQuality.Low,
    Derivative_1.EDerivativeQuality.Medium,
    Derivative_1.EDerivativeQuality.High,
    Derivative_1.EDerivativeQuality.Highest
];
class DerivativeList {
    constructor() {
        this.derivatives = {};
    }
    /**
     * From all derivatives with the given usage (e.g. web), select a derivative as close as possible to
     * the given quality. The selection strategy works as follows:
     * 1. Look for a derivative matching the quality exactly. If found, return it.
     * 2. Look for a derivative with higher quality. If found, return it.
     * 3. Look for a derivative with lower quality. If found return it, otherwise report an error.
     * @param quality
     * @param usage
     */
    select(usage, quality) {
        const usageKey = Derivative_1.EDerivativeUsage[usage];
        const qualityKey = Derivative_1.EDerivativeQuality[quality];
        const qualityIndex = _qualityLevels.indexOf(quality);
        if (qualityIndex < 0) {
            console.warn(`derivative quality not supported: '${qualityKey}'`);
            return null;
        }
        const derivative = this.get(usage, quality);
        if (derivative) {
            return derivative;
        }
        for (let i = qualityIndex + 1; i < _qualityLevels.length; ++i) {
            const derivative = this.get(usage, _qualityLevels[i]);
            if (derivative) {
                console.warn(`derivative quality '${qualityKey}' not available, using higher quality`);
                return derivative;
            }
        }
        for (let i = qualityIndex - 1; i >= 0; --i) {
            const derivative = this.get(usage, _qualityLevels[i]);
            if (derivative) {
                console.warn(`derivative quality '${qualityKey}' not available, using lower quality`);
                return derivative;
            }
        }
        console.warn(`no suitable derivative found for quality '${qualityKey}'`
            + ` and usage '${usageKey}'`);
        return null;
    }
    getByUsage(usage) {
        const key = Derivative_1.EDerivativeUsage[usage];
        return this.derivatives[key] || _EMPTY_ARRAY;
    }
    get(usage, quality) {
        const key = Derivative_1.EDerivativeUsage[usage];
        const bin = this.derivatives[key];
        if (bin) {
            for (let i = 0, n = bin.length; i < n; ++i) {
                if (bin[i].quality === quality) {
                    return bin[i];
                }
            }
        }
        return null;
    }
    getOrCreate(usage, quality) {
        const bin = this.getOrCreateBin(usage);
        for (let i = 0, n = bin.length; i < n; ++i) {
            if (bin[i].quality === quality) {
                return bin[i];
            }
        }
        const derivative = new Derivative_1.default(usage, quality);
        bin.push(derivative);
        return derivative;
    }
    createModelAsset(uri, quality) {
        const derivative = this.getOrCreate(Derivative_1.EDerivativeUsage.Web3D, quality);
        derivative.createAsset(Asset_1.EAssetType.Model, uri);
        return derivative;
    }
    createMeshAsset(geoUri, textureUri, quality) {
        const derivative = this.getOrCreate(Derivative_1.EDerivativeUsage.Web3D, quality);
        derivative.createAsset(Asset_1.EAssetType.Geometry, geoUri);
        if (textureUri) {
            const asset = derivative.createAsset(Asset_1.EAssetType.Image, textureUri);
            asset.mapType = Asset_1.EMapType.Color;
        }
        return derivative;
    }
    clear() {
        for (let key in this.derivatives) {
            this.derivatives[key].forEach(derivative => derivative.dispose());
        }
        this.derivatives = {};
    }
    toData() {
        const data = [];
        for (let key in this.derivatives) {
            this.derivatives[key].forEach(derivative => data.push(derivative.toData()));
        }
        return data;
    }
    fromData(data) {
        this.clear();
        data.forEach(derivativeData => {
            const bin = this.getOrCreateBin(Derivative_1.EDerivativeUsage[derivativeData.usage]);
            bin.push(new Derivative_1.default(derivativeData));
        });
    }
    toString(verbose = false) {
        const derivatives = this.derivatives;
        const keys = Object.keys(derivatives);
        if (verbose) {
            return `Derivatives (${keys.length}) \n ` + keys.map(key => derivatives[key].map(derivative => derivative.toString(true)).join("\n ")).join("\n ");
        }
        else {
            return `Derivatives (${keys.length}) ` + keys.map(key => `${key} (${derivatives[key].length})`).join(", ");
        }
    }
    getOrCreateBin(usage) {
        const key = Derivative_1.EDerivativeUsage[usage];
        return this.derivatives[key] || (this.derivatives[key] = []);
    }
}
exports.default = DerivativeList;


/***/ }),

/***/ "./core/shaders/UberPBRMaterial.ts":
/*!*****************************************!*\
  !*** ./core/shaders/UberPBRMaterial.ts ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const THREE = __webpack_require__(/*! three */ "three");
const fragmentShader = __webpack_require__(/*! raw-loader!./uberPBRShader.frag */ "../../node_modules/raw-loader/index.js!./core/shaders/uberPBRShader.frag");
const vertexShader = __webpack_require__(/*! raw-loader!./uberPBRShader.vert */ "../../node_modules/raw-loader/index.js!./core/shaders/uberPBRShader.vert");
const config_1 = __webpack_require__(/*! common/types/config */ "../common/types/config.ts");
exports.EShaderMode = config_1.EShaderMode;
class UberPBRMaterial extends THREE.MeshStandardMaterial {
    constructor(params) {
        super();
        this._clayColor = new THREE.Color("#a67a6c");
        this._paramCopy = {};
        this._sideCopy = THREE.FrontSide;
        this.type = "UberPBRMaterial";
        this.isUberPBRMaterial = true;
        this.isMeshStandardMaterial = true;
        this.isMeshPhysicalMaterial = false;
        this.defines = {
            "STANDARD": true,
            "PHYSICAL": false,
            "OBJECTSPACE_NORMALMAP": false,
            "MODE_NORMALS": false,
            "MODE_XRAY": false,
            "CUT_PLANE": false
        };
        this.uniforms = THREE.UniformsUtils.merge([
            THREE.ShaderLib.standard.uniforms,
            {
                aoMapMix: { value: new THREE.Vector3(0.25, 0.25, 0.25) },
                cutPlaneDirection: { value: new THREE.Vector4(0, 0, -1, 0) },
                cutPlaneColor: { value: new THREE.Vector3(1, 0, 0) }
            }
        ]);
        this._aoMapMix = this.uniforms.aoMapMix.value;
        this._cutPlaneDirection = this.uniforms.cutPlaneDirection.value;
        this._cutPlaneColor = this.uniforms.cutPlaneColor.value;
        //this.vertexShader = ShaderLib.standard.vertexShader;
        this.vertexShader = vertexShader;
        //this.fragmentShader = ShaderLib.standard.fragmentShader;
        this.fragmentShader = fragmentShader;
        this.color = new THREE.Color(0xffffff); // diffuse
        this.roughness = 0.7;
        this.metalness = 0.0;
        if (params) {
            this.setValues(params);
        }
    }
    set cutPlaneDirection(direction) {
        this._cutPlaneDirection.copy(direction);
    }
    get cutPlaneDirection() {
        return this._cutPlaneDirection;
    }
    set cutPlaneColor(color) {
        this._cutPlaneColor.copy(color);
    }
    get cutPlaneColor() {
        return this._cutPlaneColor;
    }
    set aoMapMix(mix) {
        this._aoMapMix.copy(mix);
    }
    get aoMapMix() {
        return this._aoMapMix;
    }
    setShaderMode(mode) {
        Object.assign(this, this._paramCopy);
        this.defines["MODE_NORMALS"] = false;
        this.defines["MODE_XRAY"] = false;
        this.needsUpdate = true;
        switch (mode) {
            case config_1.EShaderMode.Clay:
                this._paramCopy = {
                    color: this.color,
                    map: this.map,
                    roughness: this.roughness,
                    metalness: this.metalness,
                    aoMapIntensity: this.aoMapIntensity,
                    blending: this.blending,
                    transparent: this.transparent,
                    depthWrite: this.depthWrite
                };
                this.color = this._clayColor;
                this.map = null;
                this.roughness = 1;
                this.metalness = 0;
                this.aoMapIntensity *= 1;
                this.blending = THREE.NoBlending;
                this.transparent = false;
                this.depthWrite = true;
                break;
            case config_1.EShaderMode.Normals:
                this._paramCopy = {
                    blending: this.blending,
                    transparent: this.transparent,
                    depthWrite: this.depthWrite
                };
                this.defines["MODE_NORMALS"] = true;
                this.blending = THREE.NoBlending;
                this.transparent = false;
                this.depthWrite = true;
                break;
            case config_1.EShaderMode.XRay:
                this._paramCopy = {
                    side: this.side,
                    blending: this.blending,
                    transparent: this.transparent,
                    depthWrite: this.depthWrite
                };
                this.defines["MODE_XRAY"] = true;
                this.side = THREE.DoubleSide;
                this.blending = THREE.AdditiveBlending;
                this.transparent = true;
                this.depthWrite = false;
                break;
            case config_1.EShaderMode.Wireframe:
                this._paramCopy = {
                    wireframe: this.wireframe
                };
                this.wireframe = true;
                break;
        }
    }
    enableCutPlane(enabled) {
        this.defines["CUT_PLANE"] = enabled;
        if (enabled) {
            this._sideCopy = this.side;
            this.side = THREE.DoubleSide;
        }
        else {
            this.side = this._sideCopy;
        }
    }
    enableObjectSpaceNormalMap(useObjectSpace) {
        if (this.defines["OBJECTSPACE_NORMALMAP"] !== useObjectSpace) {
            this.needsUpdate = true;
        }
        this.defines["OBJECTSPACE_NORMALMAP"] = useObjectSpace;
    }
    copyStandardMaterial(material) {
        this.color = material.color;
        this.roughness = material.roughness;
        this.roughnessMap = material.roughnessMap;
        this.metalness = material.metalness;
        this.metalnessMap = material.metalnessMap;
        this.map = material.map;
        this.aoMap = material.aoMap;
        this.aoMapIntensity = material.aoMapIntensity;
        this.normalMap = material.normalMap;
        return this;
    }
}
exports.default = UberPBRMaterial;


/***/ }),

/***/ "./explorer/ui/ContentView.ts":
/*!************************************!*\
  !*** ./explorer/ui/ContentView.ts ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
const ManipTarget_1 = __webpack_require__(/*! @ff/browser/ManipTarget */ "../../libs/ff-browser/source/ManipTarget.ts");
const RenderQuadView_1 = __webpack_require__(/*! @ff/scene/RenderQuadView */ "../../libs/ff-scene/source/RenderQuadView.ts");
const QuadSplitter_1 = __webpack_require__(/*! @ff/ui/QuadSplitter */ "../../libs/ff-ui/source/QuadSplitter.ts");
const CustomElement_1 = __webpack_require__(/*! @ff/ui/CustomElement */ "../../libs/ff-ui/source/CustomElement.ts");
////////////////////////////////////////////////////////////////////////////////
let ContentView = class ContentView extends CustomElement_1.default {
    constructor(system) {
        super();
        this.view = null;
        this.canvas = null;
        this.overlay = null;
        this.splitter = null;
        this.onResize = this.onResize.bind(this);
        this.system = system;
        this.manipTarget = new ManipTarget_1.default();
        this.addEventListener("pointerdown", this.manipTarget.onPointerDown);
        this.addEventListener("pointermove", this.manipTarget.onPointerMove);
        this.addEventListener("pointerup", this.manipTarget.onPointerUpOrCancel);
        this.addEventListener("pointercancel", this.manipTarget.onPointerUpOrCancel);
        this.addEventListener("wheel", this.manipTarget.onWheel);
        this.addEventListener("contextmenu", this.manipTarget.onContextMenu);
    }
    firstConnected() {
        this.setStyle({
            position: "absolute",
            top: "0", bottom: "0", left: "0", right: "0"
        });
        this.canvas = this.createElement("canvas", {
            display: "block",
            width: "100%",
            height: "100%"
        }, this);
        this.overlay = this.createElement("div", {
            position: "absolute",
            top: "0", bottom: "0", left: "0", right: "0",
            overflow: "hidden"
        }, this);
        this.splitter = this.createElement(QuadSplitter_1.default, {
            position: "absolute",
            top: "0", bottom: "0", left: "0", right: "0",
            overflow: "hidden"
        }, this);
        this.splitter.onChange = (message) => {
            this.view.horizontalSplit = message.horizontalSplit;
            this.view.verticalSplit = message.verticalSplit;
        };
        this.view = new RenderQuadView_1.default(this.system, this.canvas, this.overlay);
        this.view.on("layout", event => this.splitter.layout = event.layout);
        this.view.layout = QuadSplitter_1.EQuadViewLayout.Single;
        this.splitter.layout = QuadSplitter_1.EQuadViewLayout.Single;
        //this.view.viewports[0].enableCameraManip(true);
        //this.view.addViewport().setSize(0, 0, 0.5, 1);
        //this.view.addViewport().setSize(0.5, 0, 0.5, 1);
        this.manipTarget.next = this.view;
    }
    connected() {
        this.view.attach();
        window.addEventListener("resize", this.onResize);
        window.dispatchEvent(new CustomEvent("resize"));
    }
    disconnected() {
        this.view.detach();
        window.removeEventListener("resize", this.onResize);
    }
    onResize() {
        this.view.resize();
    }
};
__decorate([
    CustomElement_1.property({ attribute: false })
], ContentView.prototype, "system", void 0);
ContentView = __decorate([
    CustomElement_1.customElement("sv-content-view")
], ContentView);
exports.default = ContentView;


/***/ }),

/***/ "./mini/MiniApplication.ts":
/*!*********************************!*\
  !*** ./mini/MiniApplication.ts ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const parseUrlParameter_1 = __webpack_require__(/*! @ff/browser/parseUrlParameter */ "../../libs/ff-browser/source/parseUrlParameter.ts");
const Commander_1 = __webpack_require__(/*! @ff/core/Commander */ "../../libs/ff-core/source/Commander.ts");
const Registry_1 = __webpack_require__(/*! @ff/graph/Registry */ "../../libs/ff-graph/source/Registry.ts");
const System_1 = __webpack_require__(/*! @ff/graph/System */ "../../libs/ff-graph/source/System.ts");
const CPulse_1 = __webpack_require__(/*! @ff/graph/components/CPulse */ "../../libs/ff-graph/source/components/CPulse.ts");
const CRenderer_1 = __webpack_require__(/*! @ff/scene/components/CRenderer */ "../../libs/ff-scene/source/components/CRenderer.ts");
const CVLoaders_1 = __webpack_require__(/*! ../core/components/CVLoaders */ "./core/components/CVLoaders.ts");
const CVOrbitNavigation_1 = __webpack_require__(/*! ../core/components/CVOrbitNavigation */ "./core/components/CVOrbitNavigation.ts");
const CMini_1 = __webpack_require__(/*! ./components/CMini */ "./mini/components/CMini.ts");
const components_1 = __webpack_require__(/*! @ff/graph/components */ "../../libs/ff-graph/source/components/index.ts");
const components_2 = __webpack_require__(/*! @ff/scene/components */ "../../libs/ff-scene/source/components/index.ts");
const components_3 = __webpack_require__(/*! ../core/components */ "./core/components/index.ts");
const components_4 = __webpack_require__(/*! ./components */ "./mini/components/index.ts");
const MainView_1 = __webpack_require__(/*! ./ui/MainView */ "./mini/ui/MainView.ts");
class MiniApplication {
    constructor(element, props) {
        console.log(MiniApplication.splashMessage);
        // register components
        const registry = new Registry_1.default();
        registry.registerComponentType(components_1.componentTypes);
        registry.registerComponentType(components_2.componentTypes);
        registry.registerComponentType(components_3.componentTypes);
        registry.registerComponentType(components_4.componentTypes);
        this.commander = new Commander_1.default();
        const system = this.system = new System_1.default(registry);
        const node = system.graph.createNode("Mini");
        node.createComponent(CPulse_1.default);
        node.createComponent(CRenderer_1.default);
        node.createComponent(CVLoaders_1.default);
        node.createComponent(CVOrbitNavigation_1.default);
        node.createComponent(CMini_1.default).createActions(this.commander);
        // create main view if not given
        if (element) {
            new MainView_1.default(this).appendTo(element);
        }
        // start rendering
        node.components.get(CPulse_1.default).start();
        // start loading from properties
        this.props = this.initFromProps(props);
    }
    initFromProps(props) {
        const miniController = this.system.components.get(CMini_1.default);
        props.item = props.item || parseUrlParameter_1.default("item") || parseUrlParameter_1.default("i");
        props.model = props.model || parseUrlParameter_1.default("model") || parseUrlParameter_1.default("m");
        props.geometry = props.geometry || parseUrlParameter_1.default("geometry") || parseUrlParameter_1.default("g");
        props.texture = props.texture || parseUrlParameter_1.default("texture") || parseUrlParameter_1.default("tex");
        if (props.item) {
            miniController.loadItem(props.item);
        }
        else if (props.model) {
            miniController.loadModel(props.model);
        }
        else if (props.geometry) {
            miniController.loadGeometryAndTexture(props.geometry, props.texture);
        }
        return props;
    }
}
MiniApplication.splashMessage = [
    "Voyager - 3D Explorer and Tool Suite",
    "3D Foundation Project",
    "(c) 2018 Smithsonian Institution",
    "https://3d.si.edu"
].join("\n");
exports.default = MiniApplication;
window["VoyagerMini"] = MiniApplication;


/***/ }),

/***/ "./mini/components/CMini.ts":
/*!**********************************!*\
  !*** ./mini/components/CMini.ts ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const CController_1 = __webpack_require__(/*! @ff/graph/components/CController */ "../../libs/ff-graph/source/components/CController.ts");
/**
 * Voyager Mini controller component. Manages presentation of items and assets.
 */
class CMini extends CController_1.default {
    loadItem(itemUrl, templateUrl) {
        // TODO: Implement
    }
    loadModel(modelUrl, quality, itemUrl, templateUrl) {
        // TODO: Implement
    }
    loadGeometryAndTexture(geometryUrl, textureUrl, quality, itemUrl, templateUrl) {
        // TODO: Implement
    }
}
CMini.type = "CMini";
exports.default = CMini;


/***/ }),

/***/ "./mini/components/index.ts":
/*!**********************************!*\
  !*** ./mini/components/index.ts ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
Object.defineProperty(exports, "__esModule", { value: true });
const CMini_1 = __webpack_require__(/*! ./CMini */ "./mini/components/CMini.ts");
exports.CMini = CMini_1.default;
exports.componentTypes = [
    CMini_1.default
];


/***/ }),

/***/ "./mini/ui/MainView.ts":
/*!*****************************!*\
  !*** ./mini/ui/MainView.ts ***!
  \*****************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * 3D Foundation Project
 * Copyright 2018 Smithsonian Institution
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
Object.defineProperty(exports, "__esModule", { value: true });
const CustomElement_1 = __webpack_require__(/*! @ff/ui/CustomElement */ "../../libs/ff-ui/source/CustomElement.ts");
const MiniApplication_1 = __webpack_require__(/*! ../MiniApplication */ "./mini/MiniApplication.ts");
const ContentView_1 = __webpack_require__(/*! ../../explorer/ui/ContentView */ "./explorer/ui/ContentView.ts");
__webpack_require__(/*! ./styles.scss */ "./mini/ui/styles.scss");
////////////////////////////////////////////////////////////////////////////////
/**
 * Main UI view for the Voyager Mini application.
 */
let MainView = class MainView extends CustomElement_1.default {
    constructor(application) {
        super();
        if (application) {
            this.application = application;
        }
        else {
            const props = {
                item: this.getAttribute("item"),
                model: this.getAttribute("model"),
                geometry: this.getAttribute("geometry"),
                texture: this.getAttribute("texture")
            };
            this.application = new MiniApplication_1.default(null, props);
        }
    }
    firstConnected() {
        const system = this.application.system;
        new ContentView_1.default(system).appendTo(this);
    }
};
MainView = __decorate([
    CustomElement_1.customElement("voyager-mini")
], MainView);
exports.default = MainView;


/***/ }),

/***/ "./mini/ui/styles.scss":
/*!*****************************!*\
  !*** ./mini/ui/styles.scss ***!
  \*****************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "three":
/*!************************!*\
  !*** external "THREE" ***!
  \************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = THREE;

/***/ })

/******/ });
//# sourceMappingURL=voyager-mini.dev.js.map