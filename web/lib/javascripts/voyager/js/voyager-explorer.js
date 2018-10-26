! function(e) {
    var t = {};

    function n(r) {
        if (t[r]) return t[r].exports;
        var i = t[r] = {
            i: r,
            l: !1,
            exports: {}
        };
        return e[r].call(i.exports, i, i.exports, n), i.l = !0, i.exports
    }
    n.m = e, n.c = t, n.d = function(e, t, r) {
        n.o(e, t) || Object.defineProperty(e, t, {
            enumerable: !0,
            get: r
        })
    }, n.r = function(e) {
        "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {
            value: "Module"
        }), Object.defineProperty(e, "__esModule", {
            value: !0
        })
    }, n.t = function(e, t) {
        if (1 & t && (e = n(e)), 8 & t) return e;
        if (4 & t && "object" == typeof e && e && e.__esModule) return e;
        var r = Object.create(null);
        if (n.r(r), Object.defineProperty(r, "default", {
                enumerable: !0,
                value: e
            }), 2 & t && "string" != typeof e)
            for (var i in e) n.d(r, i, function(t) {
                return e[t]
            }.bind(null, i));
        return r
    }, n.n = function(e) {
        var t = e && e.__esModule ? function() {
            return e.default
        } : function() {
            return e
        };
        return n.d(t, "a", t), t
    }, n.o = function(e, t) {
        return Object.prototype.hasOwnProperty.call(e, t)
    }, n.p = "", n(n.s = 292)
}([function(e, t) {
    e.exports = THREE
}, function(e, t) {
    e.exports = React
}, function(e, t, n) {
    "use strict";
    n.r(t), n.d(t, "UniformsUtils", function() {
        return r
    });
    var r = {
        merge: function(e) {
            for (var t = {}, n = 0; n < e.length; n++) {
                var r = this.clone(e[n]);
                for (var i in r) t[i] = r[i]
            }
            return t
        },
        clone: function(e) {
            var t = {};
            for (var n in e)
                for (var r in t[n] = {}, e[n]) {
                    var i = e[n][r];
                    i && (i.isColor || i.isMatrix3 || i.isMatrix4 || i.isVector2 || i.isVector3 || i.isVector4 || i.isTexture) ? t[n][r] = i.clone() : Array.isArray(i) ? t[n][r] = i.slice() : t[n][r] = i
                }
            return t
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(20),
        i = n(6),
        a = n(194);
    t.Entity = a.default;
    const o = n(199);

    function s(e) {
        return "string" == typeof e ? e : e.type
    }
    t.getType = s;
    class l {
        constructor(e, t, n, r) {
            this.didAdd = n, this.willRemove = r, this.component = e.getComponent(t), this.component && n && n(this.component)
        }
    }
    t.ComponentTracker = l;
    class c {
        constructor(e, t) {
            this._type = t ? s(t) : null, this._id = t instanceof d ? t.id : void 0, this._system = e.system
        }
        get component() {
            return this._id && this._system.getComponentById(this._id) || null
        }
        set component(e) {
            if (e && this._type && !(e instanceof this._system.registry.getComponentType(this._type))) throw new Error(`can't assign component of type '${e.type}' to link of type '${this._type}'`);
            this._id = e ? e.id : void 0
        }
    }
    t.ComponentLink = c;
    class d extends i.default {
        constructor(e) {
            super(), this.ins = new o.default(this), this.outs = new o.default(this), this.changed = !0, this._name = "", this._tracked = {}, this.addEvents("change", "dispose"), this.id = e || r.default(), this.entity = null
        }
        init(e) {
            this.entity = e, e.addComponent(this), this.create()
        }
        get type() {
            return this.constructor.type
        }
        get system() {
            return this.entity.system
        }
        get name() {
            return this._name
        }
        set name(e) {
            this._name = e, this.emit("change", {
                what: "name"
            })
        }
        create() {}
        update(e) {}
        tick(e) {}
        dispose() {
            this.emit("dispose"), this.unlink(), this.entity.removeComponent(this)
        }
        inflate() {}
        deflate() {} in (e) {
            return this.ins.getProperty(e).property
        }
        out(e) {
            return this.outs.getProperty(e).property
        }
        setValue(e, t) {
            const n = this.ins.properties.find(t => t.path === e);
            if (!n) throw new Error(`property '${e}' not found on '${this.name}': `);
            n.setValue(t)
        }
        getValue(e) {
            const t = this.ins.properties.find(t => t.path === e);
            if (!t) throw new Error(`property '${e}' not found on '${this.name}': `);
            return t.value
        }
        unlink() {
            this.ins.properties.forEach(e => e.unlink()), this.outs.properties.forEach(e => e.unlink())
        }
        resetChanged() {
            this.changed = !1;
            const e = this.ins.properties;
            for (let t = 0, n = e.length; t < n; ++t) e[t].changed = !1
        }
        createEntity(e) {
            return this.system.createEntity(e)
        }
        createComponent(e, t) {
            return this.system.createComponent(this.entity, e, t)
        }
        hasComponents(e, t) {
            return this.entity.hasComponents(e, t)
        }
        countComponents(e, t) {
            return this.entity.countComponents(e, t)
        }
        getComponents(e, t) {
            return this.entity.getComponents(e, t)
        }
        getComponent(e, t) {
            return this.entity.getComponent(e, t)
        }
        trackComponent(e, t, n) {
            const r = s(e);
            if (this._tracked[r]) throw new Error(`component type already tracked: '${r}'`);
            const i = new l(this, e, t, n);
            return this._tracked[r] = i, i
        }
        linkComponent(e) {
            return new c(this, e)
        }
        getComponentById(e) {
            return this.system.getComponentById(e)
        }
        findComponentByName(e, t, n) {
            return this.entity.findComponentByName(e, t, n)
        }
        is(e) {
            return this.type === s(e)
        }
        isEntitySingleton() {
            return this.constructor.isEntitySingleton
        }
        isSystemSingleton() {
            return this.constructor.isSystemSingleton
        }
        didAddComponent(e) {
            const t = this._tracked[e.type];
            t && !t.component && (t.component = e, t.didAdd && t.didAdd(e))
        }
        willRemoveComponent(e) {
            const t = this._tracked[e.type];
            t && t.component === e && (t.willRemove && t.willRemove(e), t.component = null)
        }
        toString() {
            return `${this.type}${this.name?" ("+this.name+")":""}`
        }
        makeProps(e) {
            return new o.default(this, e)
        }
        mergeProps(e, t) {
            return e.merge(t)
        }
    }
    d.type = "Component", d.isEntitySingleton = !0, d.isSystemSingleton = !1, t.default = d
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(203),
        i = n(204);
    var a;
    ! function(e) {
        e.Integer = {
            preset: 0,
            step: 1
        }, e.ColorRGB = {
            preset: [1, 1, 1],
            semantic: "color"
        }, e.ColorRGBA = {
            preset: [1, 1, 1, 1],
            semantic: "color"
        }
    }(a = t.Schemas || (t.Schemas = {})), t.default = {
        getOptionIndex: function(e, t) {
            const n = e.length,
                r = Math.trunc(t);
            return r < 0 ? 0 : r > n ? 0 : r
        },
        getOptionValue: function(e, t) {
            const n = e.length,
                r = Math.trunc(t);
            return e[r < 0 ? 0 : r > n ? 0 : r]
        },
        getEnumEntry: function(e, t) {
            const n = Math.trunc(t);
            return e[n] ? n : 0
        },
        getEnumName: function(e, t) {
            return e[Math.trunc(t)] || e[0]
        },
        isEnumEntry: function(e, t) {
            return e === Math.trunc(t)
        },
        toInt: function(e) {
            return Math.trunc(e)
        },
        Property: (e, t, n) => new i.default(e, t, n),
        Event: e => new i.default(e, {
            event: !0,
            preset: 0
        }),
        Number: (e, t, n) => new i.default(e, t || 0, n),
        Boolean: (e, t, n) => new i.default(e, t || !1, n),
        String: (e, t, n) => new i.default(e, t || "", n),
        Enum: (e, t, n) => new i.default(e, {
            options: r.enumToArray(t),
            preset: n || 0
        }),
        Option: (e, t, n) => new i.default(e, {
            options: t,
            preset: n || 0
        }),
        Object: (e, t) => new i.default(e, t || null),
        Vector2: (e, t, n) => new i.default(e, t || [0, 0], n),
        Vector3: (e, t, n) => new i.default(e, t || [0, 0, 0], n),
        Vector4: (e, t, n) => new i.default(e, t || [0, 0, 0, 0], n),
        Matrix3: e => new i.default(e, [1, 0, 0, 0, 1, 0, 0, 0, 1]),
        Matrix4: e => new i.default(e, [1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1]),
        ColorRGB: (e, t) => new i.default(e, a.ColorRGB, t),
        ColorRGBA: (e, t) => new i.default(e, a.ColorRGBA, t)
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(3),
        i = n(17);
    class a extends r.default {
        constructor(e) {
            super(e), this._transform = null, this._object = null, this.addEvent("object")
        }
        get transform() {
            return this._transform
        }
        get object3D() {
            return this._object
        }
        set object3D(e) {
            this._object && this._transform && this._transform.removeObject3D(this._object), this.emit("object", {
                current: this._object,
                next: e
            }), this._object = e, e && (e.matrixAutoUpdate = !1, this._transform && this._transform.addObject3D(e))
        }
        create() {
            this.trackComponent(i.default, e => {
                this._transform = e, this._object && e.addObject3D(this._object)
            }, e => {
                this._transform = null, this._object && e.removeObject3D(this._object)
            })
        }
        dispose() {
            this._object && this._transform && this._transform.removeObject3D(this._object), super.dispose()
        }
        toString() {
            return super.toString() + (this._object ? ` - type: ${this._object.type}` : " - (null)")
        }
    }
    a.type = "Object3D", t.default = a
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = Symbol("Publisher private data"),
        i = Symbol("Publisher strict option");
    t.default = class {
        constructor(e) {
            const t = !e || e.knownEvents;
            this[r] = {
                [i]: t
            }
        }
        on(e, t, n) {
            if (Array.isArray(e)) return void e.forEach(e => {
                this.on(e, t, n)
            });
            let a = this[r][e];
            if (!a) {
                if (this[r][i]) throw new Error(`can't subscribe to unknown event: '${e}'`);
                a = this[r][e] = []
            }
            let o = {
                callback: t,
                context: n
            };
            a.push(o)
        }
        once(e, t, n) {
            if (Array.isArray(e)) return void e.forEach(e => {
                this.once(e, t, n)
            });
            const r = i => {
                this.off(e, r, n), t.call(n, i)
            };
            r.cb = t, this.on(e, r, n)
        }
        off(e, t, n) {
            if (Array.isArray(e)) return void e.forEach(e => {
                this.off(e, t, n)
            });
            let i = this[r][e];
            if (!i) throw new Error(`can't unsubscribe from unknown event: '${e}'`);
            let a = [];
            i.forEach(e => {
                (t && t !== e.callback && t !== e.callback.cb || n && n !== e.context) && a.push(e)
            }), this[r][e] = a
        }
        emit(e, t) {
            let n = this[r][e];
            if (n) {
                if (n.length > 0) {
                    let e = t;
                    e ? e.sender || (e.sender = this) : e = {
                        sender: this
                    };
                    for (let t = 0, r = n.length; t < r; ++t) {
                        const r = n[t];
                        r.context ? r.callback.call(r.context, e) : r.callback(e)
                    }
                }
            } else if (this[r][i]) throw new Error(`can't emit unknown event: "${e}"`)
        }
        emitAny(e, t) {
            const n = this[r][e];
            if (n && n.length > 0)
                for (let e = 0, r = n.length; e < r; ++e) {
                    const r = n[e];
                    r.context ? r.callback.call(r.context, t) : r.callback(t)
                }
        }
        addEvent(e) {
            this[r][e] || (this[r][e] = [])
        }
        addEvents(...e) {
            e.forEach(e => {
                this[r][e] || (this[r][e] = [])
            })
        }
        hasEvent(e) {
            return !!this[r][e]
        }
        listEvents() {
            return Object.getOwnPropertyNames(this[r])
        }
    }
}, function(e, t, n) {
    "use strict";

    function r(e, t, n) {
        var r = n ? " !== " : " === ",
            i = n ? " || " : " && ",
            a = n ? "!" : "",
            o = n ? "" : "!";
        switch (e) {
            case "null":
                return t + r + "null";
            case "array":
                return a + "Array.isArray(" + t + ")";
            case "object":
                return "(" + a + t + i + "typeof " + t + r + '"object"' + i + o + "Array.isArray(" + t + "))";
            case "integer":
                return "(typeof " + t + r + '"number"' + i + o + "(" + t + " % 1)" + i + t + r + t + ")";
            default:
                return "typeof " + t + r + '"' + e + '"'
        }
    }
    e.exports = {
        copy: function(e, t) {
            for (var n in t = t || {}, e) t[n] = e[n];
            return t
        },
        checkDataType: r,
        checkDataTypes: function(e, t) {
            switch (e.length) {
                case 1:
                    return r(e[0], t, !0);
                default:
                    var n = "",
                        i = a(e);
                    for (var o in i.array && i.object && (n = i.null ? "(" : "(!" + t + " || ", n += "typeof " + t + ' !== "object")', delete i.null, delete i.array, delete i.object), i.number && delete i.integer, i) n += (n ? " && " : "") + r(o, t, !0);
                    return n
            }
        },
        coerceToTypes: function(e, t) {
            if (Array.isArray(t)) {
                for (var n = [], r = 0; r < t.length; r++) {
                    var a = t[r];
                    i[a] ? n[n.length] = a : "array" === e && "array" === a && (n[n.length] = a)
                }
                if (n.length) return n
            } else {
                if (i[t]) return [t];
                if ("array" === e && "array" === t) return ["array"]
            }
        },
        toHash: a,
        getProperty: l,
        escapeQuotes: c,
        equal: n(30),
        ucs2length: n(218),
        varOccurences: function(e, t) {
            t += "[^0-9]";
            var n = e.match(new RegExp(t, "g"));
            return n ? n.length : 0
        },
        varReplace: function(e, t, n) {
            return t += "([^0-9])", n = n.replace(/\$/g, "$$$$"), e.replace(new RegExp(t, "g"), n + "$1")
        },
        cleanUpCode: function(e) {
            return e.replace(d, "").replace(u, "").replace(h, "if (!($1))")
        },
        finalCleanUpCode: function(e, t) {
            var n = e.match(p);
            n && 2 == n.length && (e = t ? e.replace(m, "").replace(y, _) : e.replace(f, "").replace(v, g));
            return (n = e.match(x)) && 3 === n.length ? e.replace(E, "") : e
        },
        schemaHasRules: function(e, t) {
            if ("boolean" == typeof e) return !e;
            for (var n in e)
                if (t[n]) return !0
        },
        schemaHasRulesExcept: function(e, t, n) {
            if ("boolean" == typeof e) return !e && "not" != n;
            for (var r in e)
                if (r != n && t[r]) return !0
        },
        toQuotedString: b,
        getPathExpr: function(e, t, n, r) {
            return S(e, n ? "'/' + " + t + (r ? "" : ".replace(/~/g, '~0').replace(/\\//g, '~1')") : r ? "'[' + " + t + " + ']'" : "'[\\'' + " + t + " + '\\']'")
        },
        getPath: function(e, t, n) {
            var r = b(n ? "/" + M(t) : l(t));
            return S(e, r)
        },
        getData: function(e, t, n) {
            var r, i, a, o;
            if ("" === e) return "rootData";
            if ("/" == e[0]) {
                if (!P.test(e)) throw new Error("Invalid JSON-pointer: " + e);
                i = e, a = "rootData"
            } else {
                if (!(o = e.match(w))) throw new Error("Invalid JSON-pointer: " + e);
                if (r = +o[1], "#" == (i = o[2])) {
                    if (r >= t) throw new Error("Cannot access property/index " + r + " levels up, current level is " + t);
                    return n[t - r]
                }
                if (r > t) throw new Error("Cannot access data " + r + " levels up, current level is " + t);
                if (a = "data" + (t - r || ""), !i) return a
            }
            for (var s = a, c = i.split("/"), d = 0; d < c.length; d++) {
                var u = c[d];
                u && (a += l(L(u)), s += " && " + a)
            }
            return s
        },
        unescapeFragment: function(e) {
            return L(decodeURIComponent(e))
        },
        unescapeJsonPointer: L,
        escapeFragment: function(e) {
            return encodeURIComponent(M(e))
        },
        escapeJsonPointer: M
    };
    var i = a(["string", "number", "integer", "boolean", "null"]);

    function a(e) {
        for (var t = {}, n = 0; n < e.length; n++) t[e[n]] = !0;
        return t
    }
    var o = /^[a-z$_][a-z$_0-9]*$/i,
        s = /'|\\/g;

    function l(e) {
        return "number" == typeof e ? "[" + e + "]" : o.test(e) ? "." + e : "['" + c(e) + "']"
    }

    function c(e) {
        return e.replace(s, "\\$&").replace(/\n/g, "\\n").replace(/\r/g, "\\r").replace(/\f/g, "\\f").replace(/\t/g, "\\t")
    }
    var d = /else\s*{\s*}/g,
        u = /if\s*\([^)]+\)\s*\{\s*\}(?!\s*else)/g,
        h = /if\s*\(([^)]+)\)\s*\{\s*\}\s*else(?!\s*if)/g;
    var p = /[^v.]errors/g,
        f = /var errors = 0;|var vErrors = null;|validate.errors = vErrors;/g,
        m = /var errors = 0;|var vErrors = null;/g,
        v = "return errors === 0;",
        g = "validate.errors = null; return true;",
        y = /if \(errors === 0\) return data;\s*else throw new ValidationError\(vErrors\);/,
        _ = "return data;",
        x = /[^A-Za-z_$]rootData[^A-Za-z0-9_$]/g,
        E = /if \(rootData === undefined\) rootData = data;/;

    function b(e) {
        return "'" + c(e) + "'"
    }
    var P = /^\/(?:[^~]|~0|~1)*$/,
        w = /^([0-9]+)(#|\/(?:[^~]|~0|~1)*)?$/;

    function S(e, t) {
        return '""' == e ? t : (e + " + " + t).replace(/' \+ '/g, "")
    }

    function M(e) {
        return e.replace(/~/g, "~0").replace(/\//g, "~1")
    }

    function L(e) {
        return e.replace(/~1/g, "/").replace(/~0/g, "~")
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(20),
        i = n(3),
        a = n(11);
    class o extends i.default {
        constructor() {
            super(...arguments), this.items = {}
        }
        has(e) {
            return !!this.items[e]
        }
        get(e) {
            return this.items[e]
        }
        insert(e) {
            return e.id || (e.id = r.default(8)), this.items[e.id] = e, e.id
        }
        remove(e) {
            const t = this.items[e];
            return this.items[e] = void 0, t
        }
        count() {
            return this.getArray().length
        }
        findRootCollection() {
            const e = this.getComponent(a.default);
            if (e) {
                const t = e.getRoot();
                if (t !== e) return t.getComponent(this)
            }
            return null
        }
        getDictionary() {
            if (!this.items) return {};
            const e = {};
            return Object.keys(this.items).forEach(t => {
                const n = this.items[t];
                void 0 !== n && (e[n.id] = n)
            }), e
        }
        getArray() {
            return this.items ? Object.keys(this.items).map(e => this.items[e]).filter(e => void 0 !== e) : []
        }
        toString() {
            return super.toString() + ` - items: ${this.count()}`
        }
    }
    o.type = "Collection", t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(2),
        a = n(193),
        o = n(186),
        s = n(187),
        l = n(0);
    var c;
    ! function(e) {
        e[e.Inherit = 0] = "Inherit", e[e.Default = 1] = "Default", e[e.PBR = 2] = "PBR", e[e.Phong = 3] = "Phong", e[e.Clay = 4] = "Clay", e[e.Normals = 5] = "Normals", e[e.Wireframe = 6] = "Wireframe", e[e.XRay = 7] = "XRay"
    }(c = t.EShaderMode || (t.EShaderMode = {}));
    t.default = class extends l.Material {
        constructor(e) {
            super(), this._params = {}, this._clayColor = new r.Color("#a67a6c"), this.type = "UberMaterial", this.isMeshStandardMaterial = !0, this.isUberMaterial = !0, this.defines = {
                PHYSICAL: !0,
                USE_OBJECTSPACE_NORMALMAP: !1,
                MODE_NORMALS: !1,
                MODE_XRAY: !1
            }, this.uniforms = i.UniformsUtils.merge([a.ShaderLib.standard.uniforms, {
                aoMapMix: {
                    value: new r.Vector3(.25, .25, .25)
                }
            }]), this.vertexShader = s, this.fragmentShader = o, this.color = new r.Color(16777215), this.roughness = .7, this.metalness = 0, this.map = null, this.lightMap = null, this.lightMapIntensity = 1, this.aoMap = null, this.aoMapIntensity = 1, this.emissive = new r.Color(0), this.emissiveIntensity = 1, this.emissiveMap = null, this.bumpMap = null, this.bumpScale = 1, this.normalMap = null, this.normalScale = new r.Vector2(1, 1), this.displacementMap = null, this.displacementScale = 1, this.displacementBias = 0, this.roughnessMap = null, this.metalnessMap = null, this.alphaMap = null, this.envMap = null, this.envMapIntensity = 1, this.refractionRatio = .98, this.wireframe = !1, this.wireframeLinewidth = 1, this.wireframeLinecap = "round", this.wireframeLinejoin = "round", this.skinning = !1, this.morphTargets = !1, this.morphNormals = !1, e && this.setValues(e)
        }
        setShaderMode(e) {
            switch (Object.assign(this, this._params), this.defines.MODE_NORMALS = !1, this.defines.MODE_XRAY = !1, this.needsUpdate = !0, e) {
                case c.Clay:
                    this._params = {
                        color: this.color,
                        map: this.map,
                        roughness: this.roughness,
                        metalness: this.metalness,
                        aoMapIntensity: this.aoMapIntensity,
                        side: this.side,
                        blending: this.blending,
                        transparent: this.transparent,
                        depthWrite: this.depthWrite
                    }, this.color = this._clayColor, this.map = null, this.roughness = 1, this.metalness = 0, this.aoMapIntensity *= 1, this.side = r.FrontSide, this.blending = r.NoBlending, this.transparent = !1, this.depthWrite = !0;
                    break;
                case c.Normals:
                    this._params = {
                        side: this.side,
                        blending: this.blending,
                        transparent: this.transparent,
                        depthWrite: this.depthWrite
                    }, this.defines.MODE_NORMALS = !0, this.side = r.FrontSide, this.blending = r.NoBlending, this.transparent = !1, this.depthWrite = !0;
                    break;
                case c.XRay:
                    this._params = {
                        side: this.side,
                        blending: this.blending,
                        transparent: this.transparent,
                        depthWrite: this.depthWrite
                    }, this.defines.MODE_XRAY = !0, this.side = r.DoubleSide, this.blending = r.AdditiveBlending, this.transparent = !0, this.depthWrite = !1;
                    break;
                case c.Wireframe:
                    this._params = {
                        wireframe: this.wireframe
                    }, this.wireframe = !0
            }
        }
        setOcclusionMix(e) {
            this.uniforms.aoMapMix.value.set(e[0], e[1], e[2])
        }
        setNormalMapObjectSpace(e) {
            this.defines.USE_OBJECTSPACE_NORMALMAP !== e && (this.needsUpdate = !0), this.defines.USE_OBJECTSPACE_NORMALMAP = e
        }
        copyStandardMaterial(e) {
            return this.color = e.color, this.roughness = e.roughness, this.roughnessMap = e.roughnessMap, this.metalness = e.metalness, this.metalnessMap = e.metalnessMap, this.map = e.map, this.aoMap = e.aoMap, this.aoMapIntensity = e.aoMapIntensity, this.normalMap = e.normalMap, this
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = {
            boxSizing: "border-box",
            display: "flex"
        },
        a = function(e) {
            const {
                className: t,
                style: n,
                direction: a,
                position: o,
                justifyContent: s,
                alignContent: l,
                alignItems: c,
                wrap: d,
                grow: u,
                shrink: h,
                basis: p,
                children: f
            } = e, m = t + " ff-" + a, v = Object.assign({}, i, n);
            switch (v.flexDirection = "vertical" === a ? "column" : "row", s && (v.justifyContent = s), l && (v.alignContent = l), c && (v.alignItems = c), d && (v.flexWrap = d), o) {
                case "fill":
                    v.position = "absolute", v.top = 0, v.right = 0, v.bottom = 0, v.left = 0;
                    break;
                case "relative":
                    v.position = "relative", v.flex = `${u} ${h} ${p}`;
                    break;
                case "absolute":
                    v.position = "absolute"
            }
            return r.createElement("div", {
                className: m,
                style: v
            }, f)
        };
    a.defaultProps = {
        className: "ff-flex-container",
        direction: "horizontal",
        position: "relative",
        grow: 1,
        shrink: 1,
        basis: "auto"
    }, t.default = a
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(3),
        i = (e, t) => {
            if (e.entity.name === t) return e.entity;
            const n = e.children;
            for (let e = 0, r = n.length; e < r; ++e) {
                const r = i(n[e], t);
                if (r) return r
            }
            return null
        },
        a = (e, t) => {
            const n = e.getComponent(t);
            if (n) return n;
            const r = e.children;
            for (let e = 0, n = r.length; e < n; ++e) {
                const n = a(r[e], t);
                if (n) return n
            }
            return null
        },
        o = (e, t) => {
            let n = e.getComponents(t);
            const r = e.children;
            for (let e = 0, i = r.length; e < i; ++e) {
                const i = o(r[e], t);
                i.length > 0 && (n = n.concat(i))
            }
            return n
        };
    class s extends r.default {
        constructor() {
            super(...arguments), this._parent = null, this._children = []
        }
        get parent() {
            return this._parent
        }
        get children() {
            return this._children || []
        }
        dispose() {
            this._parent && this._parent.removeChild(this), this._children.slice().forEach(e => this.removeChild(e)), super.dispose()
        }
        addChild(e) {
            if (e._parent) throw new Error("component should not have a parent");
            e._parent = this, this._children.push(e), e.emit("change", {
                what: "add-parent",
                component: this
            }), this.emit("change", {
                what: "add-child",
                component: e
            })
        }
        removeChild(e) {
            if (e._parent !== this) throw new Error("component not a child of this");
            const t = this._children.indexOf(e);
            this._children.splice(t, 1), e._parent = null, e.emit("change", {
                what: "remove-parent",
                component: this
            }), this.emit("change", {
                what: "remove-child",
                component: e
            })
        }
        getRoot() {
            let e = this;
            for (; e._parent;) e = e._parent;
            return e
        }
        findEntityInSubtree(e) {
            return i(this, e)
        }
        getComponentInSubtree(e) {
            return a(this, e)
        }
        getComponentsInSubtree(e) {
            return o(this, e)
        }
        hasComponentsInSubtree(e) {
            return !!a(this, e)
        }
        getNearestAncestor(e) {
            let t = this,
                n = void 0;
            for (; !n && t;) n = t.getComponent(e), t = t._parent;
            return n
        }
        toString() {
            return super.toString() + ` - children: ${this.children.length}`
        }
    }
    s.type = "Hierarchy", t.default = s
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = {
        PI: 3.141592653589793,
        DOUBLE_PI: 6.283185307179586,
        HALF_PI: 1.5707963267948966,
        QUARTER_PI: .7853981633974483,
        DEG2RAD: .017453292519943295,
        RAD2DEG: 57.29577951308232,
        limit: (e, t, n) => e < t ? t : e > n ? n : e,
        limitInt: function(e, t, n) {
            return (e = Math.trunc(e)) < t ? t : e > n ? n : e
        },
        normalize: (e, t, n) => (e - t) / (n - t),
        normalizeLimit: (e, t, n) => (e = (e - t) / (n - t)) < 0 ? 0 : e > 1 ? 1 : e,
        denormalize: (e, t, n) => (t + e) * (n - t),
        deg2rad: function(e) {
            return .017453292519943295 * e
        },
        rad2deg: function(e) {
            return 57.29577951308232 * e
        },
        deltaRadians: function(e, t) {
            return e = (e %= r.DOUBLE_PI) < 0 ? e + r.DOUBLE_PI : e, (t = (t %= r.DOUBLE_PI) < 0 ? t + r.DOUBLE_PI : t) - e > r.PI && (e += r.DOUBLE_PI), t - e
        },
        deltaDegrees: function(e, t) {
            return e = (e %= r.DOUBLE_PI) < 0 ? e + r.DOUBLE_PI : e, (t = (t %= r.DOUBLE_PI) < 0 ? t + r.DOUBLE_PI : t) - e > r.PI && (e += r.DOUBLE_PI), t - e
        },
        curves: {
            linear: e => e,
            easeIn: e => Math.sin(e * r.HALF_PI),
            easeOut: e => Math.cos(e * r.HALF_PI - r.PI) + 1,
            ease: e => .5 * Math.cos(e * r.PI - r.PI) + .5,
            easeInQuad: e => e * e,
            easeOutQuad: e => e * (2 - e),
            easeQuad: e => e < .5 ? 2 * e * e : (4 - 2 * e) * e - 1,
            easeInCubic: e => e * e * e,
            easeOutCubic: e => --e * e * e + 1,
            easeCubic: e => e < .5 ? 4 * e * e * e : (e - 1) * (2 * e - 2) * (2 * e - 2) + 1,
            easeInQuart: e => e * e * e * e,
            easeOutQuart: e => 1 - --e * e * e * e,
            easeQuart: e => e < .5 ? 8 * e * e * e * e : 1 - 8 * --e * e * e * e
        }
    };
    t.default = r
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(5);
    class i extends r.default {get light() {
            return this.object3D
        }
        fromData(e) {
            throw new Error("abstract method, must be overridden")
        }
        toData() {
            throw new Error("abstract method, must be overridden")
        }
    }
    i.type = "Light", t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(160);
    t.Commander = r.default;
    const i = n(3);
    class a extends i.default {
        constructor() {
            super(...arguments), this.actions = null
        }
        createActions(e) {}
    }
    a.type = "Controller", t.default = a
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(3);
    class i extends r.default {
        create() {
            super.create(), this.next = new r.ComponentLink(this)
        }
        onPointer(e) {
            return !!this.next.component && this.next.component.onPointer(e)
        }
        onTrigger(e) {
            return !!this.next.component && this.next.component.onTrigger(e)
        }
    }
    i.type = "Manip", t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(4),
        a = n(206),
        o = n(18),
        s = n(5),
        l = new r.Vector3;
    class c extends s.default {
        constructor() {
            super(...arguments), this.ins = this.makeProps({
                alo: i.default.Boolean("Auto.Load", !0),
                asc: i.default.Boolean("Auto.Pose", !0),
                qua: i.default.Enum("Quality", o.EDerivativeQuality, o.EDerivativeQuality.High)
            }), this.currentModel = null
        }
        get model() {
            return this.object3D
        }
        create() {
            super.create();
            const e = this.object3D = new a.default;
            e.onLoad = this.onLoad.bind(this), this.object3D = e
        }
        update() {
            const {
                alo: e,
                qua: t
            } = this.ins;
            !this.currentModel && e.value && this.model.autoLoad(t.value).catch(e => {
                console.warn("Model.update - failed to load derivative"), console.warn(e)
            })
        }
        addWebModelDerivative(e, t) {
            this.model.addWebModelDerivative(e, t)
        }
        addGeometryAndTextureDerivative(e, t, n) {
            this.model.addGeometryAndTextureDerivative(e, t, n)
        }
        setShaderMode(e) {
            this.model.setShaderMode(e)
        }
        setAssetLoader(e, t) {
            this.model.setAssetLoader(e, t)
        }
        fromData(e) {
            return this.model.fromData(e), this
        }
        toData() {
            return this.model.toData()
        }
        onLoad() {
            if (this.ins.asc && this.transform) {
                const e = this.model,
                    t = e.boundingBox.getSize(l),
                    n = 10 / Math.max(t.x, t.y, t.z),
                    r = e.boundingBox.getCenter(l);
                this.transform.setValue("Scale", [n, n, n]), this.transform.setValue("Position", [-r.x * n, -r.y * n, -r.z * n])
            }
        }
    }
    c.type = "Model", t.default = c
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(12),
        a = n(4),
        o = n(11),
        s = new r.Vector3,
        l = new r.Quaternion,
        c = new r.Vector3;
    var d;
    ! function(e) {
        e[e.XYZ = 0] = "XYZ", e[e.YZX = 1] = "YZX", e[e.ZXY = 2] = "ZXY", e[e.XZY = 3] = "XZY", e[e.YXZ = 4] = "YXZ", e[e.ZYX = 5] = "ZYX"
    }(d = t.ERotationOrder || (t.ERotationOrder = {}));
    class u extends o.default {
        constructor(e) {
            super(e), this.ins = this.makeProps({
                pos: a.default.Vector3("Position"),
                rot: a.default.Vector3("Rotation"),
                ord: a.default.Enum("Order", d),
                sca: a.default.Vector3("Scale", [1, 1, 1]),
                mat: a.default.Matrix4("Matrix")
            }), this.outs = this.makeProps({
                mat: a.default.Matrix4("Matrix")
            }), this._object = new r.Object3D, this._object.matrixAutoUpdate = !1
        }
        update() {
            const e = this._object,
                {
                    pos: t,
                    rot: n,
                    ord: r,
                    sca: o,
                    mat: s
                } = this.ins;
            s.changed ? (e.matrix.fromArray(s.value), e.matrixWorldNeedsUpdate = !0) : (t.changed && e.position.fromArray(t.value), n.changed && e.rotation.set(n.value[0] * i.default.DEG2RAD, n.value[1] * i.default.DEG2RAD, n.value[2] * i.default.DEG2RAD), r.changed && (e.rotation.order = a.default.getEnumName(d, r.value)), o.changed && e.scale.fromArray(o.value), e.updateMatrix()), e.matrix.toArray(this.outs.mat.value)
        }
        dispose() {
            this._object && (this._object.parent.remove(this._object), this._object.children.slice().forEach(e => this._object.remove(e)), super.dispose())
        }
        get object3D() {
            return this._object
        }
        get children() {
            return this._children || []
        }
        get matrix() {
            return this._object.matrix
        }
        addChild(e) {
            super.addChild(e), this._object.add(e._object)
        }
        removeChild(e) {
            this._object.remove(e._object), super.removeChild(e)
        }
        addObject3D(e) {
            this._object.add(e)
        }
        removeObject3D(e) {
            this._object.remove(e)
        }
        fromData(e) {
            const t = this.ins;
            if (e.matrix) t.mat.setValue(e.matrix), t.pos.changed = !1, t.rot.changed = !1, t.ord.changed = !1, t.sca.changed = !1;
            else {
                if (e.translation && t.pos.setValue(e.translation), e.rotation) {
                    const n = (new r.Quaternion).fromArray(e.rotation),
                        i = (new r.Euler).setFromQuaternion(n);
                    t.rot.setValue(i.toVector3().toArray())
                }
                e.scale && t.sca.setValue(e.scale), t.mat.changed = !1, this.update()
            }
        }
        toData() {
            this._object.matrix.decompose(s, l, c);
            const e = {};
            return 0 === s.x && 0 === s.y && 0 === s.z || (e.translation = s.toArray()), 0 === l.x && 0 === l.y && 0 === l.z && 1 === l.w || (e.rotation = l.toArray()), 1 === c.x && 1 === c.y && 1 === c.z || (e.scale = c.toArray()), e
        }
    }
    u.type = "Transform", t.default = u
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(162),
        a = n(9),
        o = n(27);
    var s, l;
    t.Asset = o.default, t.EAssetType = o.EAssetType,
        function(e) {
            e[e.Web = 0] = "Web", e[e.Print = 1] = "Print", e[e.Editorial = 2] = "Editorial"
        }(s = t.EDerivativeUsage || (t.EDerivativeUsage = {})),
        function(e) {
            e[e.Thumb = 0] = "Thumb", e[e.Low = 1] = "Low", e[e.Medium = 2] = "Medium", e[e.High = 3] = "High", e[e.Highest = 4] = "Highest", e[e.LOD = 5] = "LOD", e[e.Stream = 6] = "Stream"
        }(l = t.EDerivativeQuality || (t.EDerivativeQuality = {}));
    t.default = class {
        constructor(e, t, n) {
            this.id = "", this.usage = e, this.quality = t, this.assets = n || [], this.model = null, this.boundingBox = new r.Box3
        }
        load(e, t) {
            const n = this.findAsset(o.EAssetType.Model);
            if (n) return e.loadModel(n, t).then(e => (this.model = e, this.boundingBox.makeEmpty().expandByObject(e), this));
            const i = this.findAsset(o.EAssetType.Geometry),
                s = this.findAssets(o.EAssetType.Image);
            return i ? e.loadGeometry(i, t).then(n => (this.model = new r.Mesh(n, new a.default), this.boundingBox.makeEmpty().expandByObject(this.model), Promise.all(s.map(n => e.loadTexture(n, t))).catch(e => (console.warn("failed to load texture files"), [])))).then(e => {
                const t = this.model.material;
                return this.assignTextures(s, e, t), t.map || (t.color.setScalar(.5), t.roughness = .8, t.metalness = 0), this
            }) : void 0
        }
        addAsset(e, t, n) {
            if (!e) throw new Error("uri must be specified");
            const r = {
                uri: e,
                type: o.EAssetType[t]
            };
            t === o.EAssetType.Image && void 0 !== n && (r.mapType = o.EMapType[n]), this.assets.push(r)
        }
        toData() {
            return {
                usage: s[this.usage],
                quality: l[this.quality],
                assets: i.default(this.assets)
            }
        }
        findAsset(e) {
            const t = o.EAssetType[e];
            return this.assets.find(e => e.type === t)
        }
        findAssets(e) {
            const t = o.EAssetType[e];
            return this.assets.filter(e => e.type === t)
        }
        assignTextures(e, t, n) {
            for (let r = 0; r < e.length; ++r) {
                const i = e[r],
                    a = t[r];
                switch (i.mapType) {
                    case o.EMapType[o.EMapType.Color]:
                        n.map = a;
                        break;
                    case o.EMapType[o.EMapType.Occlusion]:
                        n.aoMap = a;
                        break;
                    case o.EMapType[o.EMapType.Emissive]:
                        n.emissiveMap = a;
                        break;
                    case o.EMapType[o.EMapType.MetallicRoughness]:
                        n.metalnessMap = a, n.roughnessMap = a;
                        break;
                    case o.EMapType[o.EMapType.Normal]:
                        n.normalMap = a
                }
            }
        }
    }
}, function(e, t, n) {
    "use strict";

    function r(e) {
        return "/" === e.charAt(0)
    }

    function i(e, t) {
        for (var n = t, r = n + 1, i = e.length; r < i; n += 1, r += 1) e[n] = e[r];
        e.pop()
    }
    n.r(t), t.default = function(e) {
        var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : "",
            n = e && e.split("/") || [],
            a = t && t.split("/") || [],
            o = e && r(e),
            s = t && r(t),
            l = o || s;
        if (e && r(e) ? a = n : n.length && (a.pop(), a = a.concat(n)), !a.length) return "/";
        var c = void 0;
        if (a.length) {
            var d = a[a.length - 1];
            c = "." === d || ".." === d || "" === d
        } else c = !1;
        for (var u = 0, h = a.length; h >= 0; h--) {
            var p = a[h];
            "." === p ? i(a, h) : ".." === p ? (i(a, h), u++) : u && (i(a, h), u--)
        }
        if (!l)
            for (; u--; u) a.unshift("..");
        !l || "" === a[0] || a[0] && r(a[0]) || a.unshift("");
        var f = a.join("/");
        return c && "/" !== f.substr(-1) && (f += "/"), f
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    let r = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    t.default = function(e) {
        e && "number" == typeof e || (e = 12);
        let t = "";
        for (let n = 0; n < e; ++n) t += r[62 * Math.random() | 0];
        return t
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(12),
        a = n(24),
        o = n(4),
        s = n(163),
        l = n(15),
        c = n(26);
    var d;
    t.EProjectionType = c.EProjectionType,
        function(e) {
            e[e.Left = 0] = "Left", e[e.Right = 1] = "Right", e[e.Top = 2] = "Top", e[e.Bottom = 3] = "Bottom", e[e.Front = 4] = "Front", e[e.Back = 5] = "Back", e[e.None = 6] = "None"
        }(d = t.EViewPreset || (t.EViewPreset = {}));
    const u = [
            [0, 90, 0],
            [0, -90, 0],
            [90, 0, 0],
            [-90, 0, 0],
            [0, 0, 0],
            [0, 180, 0]
        ],
        h = Number.MAX_VALUE,
        p = new r.Vector3,
        f = new r.Vector3,
        m = new r.Matrix4;
    class v extends l.default {
        constructor() {
            super(...arguments), this.ins = this.makeProps({
                pro: o.default.Enum("View.Projection", c.EProjectionType, c.EProjectionType.Perspective),
                pre: o.default.Enum("View.Preset", d, d.None),
                ena: o.default.Boolean("Override.Enabled", !1),
                pus: o.default.Event("Override.Push"),
                ori: o.default.Vector3("Override.Orientation"),
                ofs: o.default.Vector3("Override.Offset", [0, 0, 50]),
                minOri: o.default.Vector3("Min.Orientation", [-90, -h, -180]),
                minOfs: o.default.Vector3("Min.Offset", [-h, -h, -h]),
                maxOri: o.default.Vector3("Max.Orientation", [90, h, 180]),
                maxOfs: o.default.Vector3("Max.Offset", [h, h, h])
            }), this.outs = this.makeProps({
                pro: o.default.Enum("View.Projection", c.EProjectionType),
                pre: o.default.Enum("View.Preset", d),
                siz: o.default.Number("View.Size"),
                ori: o.default.Vector3("Orbit.Orientation"),
                ior: o.default.Vector3("Orbit.InverseOrientation"),
                ofs: o.default.Vector3("Orbit.Offset"),
                mat: o.default.Matrix4("Orbit.Matrix"),
                man: o.default.Event("Orbit.Manip")
            }), this.manip = new s.default, this.viewportWidth = 100, this.viewportHeight = 100, this.updateMatrix = !1, this.onPreset = !1
        }
        update() {
            const e = this.ins,
                t = this.outs;
            e.pro.changed && t.pro.pushValue(e.pro.value), e.pre.changed && (t.pre.pushValue(e.pre.value), t.ori.value = o.default.getOptionValue(u, e.pre.value), t.ofs.value[0] = 0, t.ofs.value[1] = 0, this.onPreset = !0), e.pus.changed && (t.ori.value = e.ori.value.slice(), t.ofs.value = e.ofs.value.slice(), this.onPreset = !1), e.ena.value && (e.ena.changed && (t.ori.value = e.ori.value.slice(), t.ofs.value = e.ofs.value.slice(), this.onPreset = !1), e.ori.changed && (t.ori.value = e.ori.value, this.onPreset = !1), e.ofs.changed && (t.ofs.value = e.ofs.value, this.onPreset = !1)), this.updateMatrix = !0
        }
        tick() {
            const e = this.ins,
                {
                    pre: t,
                    siz: n,
                    ori: r,
                    ofs: s,
                    mat: l,
                    ior: u,
                    man: h
                } = this.outs,
                v = this.manip.getDeltaPose();
            if (v && !this.ins.ena.value) {
                const {
                    minOri: t,
                    maxOri: n,
                    minOfs: a,
                    maxOfs: o
                } = e, l = r.value[0] + 300 * v.dPitch / this.viewportHeight, c = r.value[1] + 300 * v.dHead / this.viewportHeight, d = r.value[2] + 300 * v.dRoll / this.viewportHeight;
                r.value[0] = i.default.limit(l, t[0], n[0]), r.value[1] = i.default.limit(c, t[1], n[1]), r.value[2] = i.default.limit(d, t[2], n[2]);
                const u = Math.max(s.value[2], .1) * v.dScale,
                    p = s.value[0] - v.dX * u / this.viewportHeight,
                    f = s.value[1] + v.dY * u / this.viewportHeight;
                s.value[0] = i.default.limit(p, a[0], o[0]), s.value[1] = i.default.limit(f, a[1], o[1]), s.value[2] = i.default.limit(u, a[2], o[2]), h.push(), this.updateMatrix = !0, this.onPreset = !1
            }
            this.updateMatrix && (this.updateMatrix = !1, p.fromArray(r.value), p.multiplyScalar(i.default.DEG2RAD), f.fromArray(s.value), o.default.isEnumEntry(c.EProjectionType.Orthographic, e.pro.value) && (n.pushValue(f.z), f.z = 1e3), a.default.composeOrbitMatrix(p, f, m), m.toArray(l.value), u.value[0] = -r.value[0], u.value[1] = -r.value[1], u.value[2] = -r.value[2], r.push(), u.push(), s.push(), l.push()), this.onPreset || t.value === d.None || t.pushValue(d.None)
        }
        onPointer(e) {
            const t = e.viewport;
            return t && t.useSceneCamera ? (this.viewportWidth = t.width, this.viewportHeight = t.height, this.manip.onPointer(e)) : super.onPointer(e)
        }
        onTrigger(e) {
            const t = e.viewport;
            return t && t.useSceneCamera ? this.manip.onTrigger(e) : super.onTrigger(e)
        }
        setFromMatrix(e) {
            const {
                ori: t,
                ofs: n,
                pus: r
            } = this.ins;
            t.hasInLinks() || n.hasInLinks() ? console.warn("OrbitController.setFromMatrix - can't set, inputs are linked") : (a.default.decomposeOrbitMatrix(e, p, f), p.multiplyScalar(i.default.RAD2DEG), p.toArray(t.value), f.toArray(n.value), r.set())
        }
    }
    v.type = "OrbitManip", t.default = v
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1);
    class i extends r.Component {
        constructor(e) {
            super(e), this.state = {
                selected: e.selected
            }, this.elementRef = r.createRef(), this.pointerId = -1, this.onPointerDown = this.onPointerDown.bind(this), this.onPointerUp = this.onPointerUp.bind(this), this.onPointerCancel = this.onPointerCancel.bind(this), this.onKeyDown = this.onKeyDown.bind(this), this.onKeyUp = this.onKeyUp.bind(this)
        }
        setFocus() {
            this.elementRef.current && this.elementRef.current.focus()
        }
        isSelected() {
            return this.state.selected
        }
        componentDidMount() {
            this.props.focused && this.elementRef.current && this.elementRef.current.focus()
        }
        render() {
            const {
                className: e,
                style: t,
                text: n,
                icon: a,
                faIcon: o,
                image: s,
                title: l,
                disabled: c,
                children: d
            } = this.props, u = e + (this.state.selected ? " ff-selected" : "") + (!0 === c ? " ff-disabled" : ""), h = Object.assign({}, i.mainStyle, t), p = i.contentStyle, f = {
                ref: this.elementRef,
                className: u,
                style: h,
                title: l,
                tabIndex: 0,
                "touch-action": "none",
                onPointerDown: c ? null : this.onPointerDown,
                onPointerUp: c ? null : this.onPointerUp,
                onPointerCancel: c ? null : this.onPointerCancel,
                onKeyDown: c ? null : this.onKeyDown,
                onKeyUp: c ? null : this.onKeyUp
            };
            return r.createElement("div", Object.assign({}, f), a ? r.createElement("span", {
                className: "ff-content ff-icon " + a,
                style: p
            }) : null, o ? r.createElement("span", {
                className: "ff-content ff-icon fa fas fa-" + o,
                style: p
            }) : null, s ? r.createElement("img", {
                className: "ff-content ff-image",
                src: s,
                style: p
            }) : null, n ? r.createElement("span", {
                className: "ff-content ff-text",
                style: p
            }, n) : null, d)
        }
        componentWillReceiveProps(e) {
            this.setState({
                selected: e.selected
            })
        }
        onPointerDown(e) {
            if (-1 === this.pointerId) {
                this.pointerId = e.pointerId;
                const {
                    id: t,
                    index: n,
                    onDown: r
                } = this.props;
                r && r({
                    id: t,
                    index: n,
                    sender: this
                })
            }
            e.stopPropagation()
        }
        onPointerUp(e) {
            if (this.pointerId === e.pointerId) {
                this.pointerId = -1;
                const {
                    id: e,
                    index: t,
                    selectable: n,
                    onSelect: r,
                    onUp: i,
                    onTap: a
                } = this.props;
                n && this.setState(n => {
                    const i = !n.selected;
                    return r && r({
                        selected: i,
                        id: e,
                        index: t,
                        sender: this
                    }), {
                        selected: i
                    }
                }), i && i({
                    id: e,
                    index: t,
                    sender: this
                }), a && a({
                    id: e,
                    index: t,
                    sender: this
                })
            }
            e.stopPropagation()
        }
        onPointerCancel(e) {
            if (this.pointerId === e.pointerId) {
                this.pointerId = -1;
                const {
                    id: e,
                    index: t,
                    onUp: n
                } = this.props;
                n && n({
                    id: e,
                    index: t,
                    sender: this
                })
            }
        }
        onKeyDown(e) {
            if (32 === e.keyCode) {
                const {
                    id: e,
                    index: t,
                    onDown: n
                } = this.props;
                n && n({
                    id: e,
                    index: t,
                    sender: this
                })
            }
        }
        onKeyUp(e) {
            if (32 === e.keyCode) {
                const {
                    id: e,
                    index: t,
                    selectable: n,
                    onSelect: r,
                    onUp: i,
                    onTap: a
                } = this.props;
                n && this.setState(n => {
                    const i = !n.selected;
                    return r && r({
                        selected: i,
                        id: e,
                        index: t,
                        sender: this
                    }), {
                        selected: i
                    }
                }), i && i({
                    id: e,
                    index: t,
                    sender: this
                }), a && a({
                    id: e,
                    index: t,
                    sender: this
                })
            }
        }
    }
    i.defaultProps = {
        className: "ff-control ff-button"
    }, i.mainStyle = {
        touchAction: "none",
        cursor: "pointer"
    }, i.contentStyle = {
        pointerEvents: "none",
        userSelect: "none"
    }, t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(5),
        a = n(15),
        o = new r.Vector2;
    class s extends a.default {
        constructor(e) {
            super(e), this.objectComponents = {}, this.raycaster = new r.Raycaster, this.activePick = null, this.startX = 0, this.startY = 0, this.addEvents("down", "up")
        }
        create() {
            super.create(), this.system.addComponentEventListener(i.default, this.onObject3D, this)
        }
        dispose() {
            this.system.removeComponentEventListener(i.default, this.onObject3D, this), super.dispose()
        }
        setRoot(e) {
            this.root = e
        }
        onPointer(e) {
            const t = e.viewport,
                n = t ? t.camera : null;
            if (n && e.isPrimary)
                if ("down" === e.type) {
                    this.startX = e.centerX, this.startY = e.centerY;
                    const t = this.pick(e.deviceX, e.deviceY, n),
                        r = this.activePick = t[0];
                    this.emit("down", {
                        pointerEvent: e,
                        component: r ? r.component : null,
                        object: r ? r.object : null,
                        point: r ? r.point : null,
                        normal: r ? r.normal : null
                    })
                } else if ("up" === e.type) {
                const t = e.centerX - this.startX,
                    n = e.centerY - this.startY;
                if (Math.abs(t) + Math.abs(n) < 3) {
                    const t = this.activePick;
                    this.emit("up", {
                        pointerEvent: e,
                        component: t ? t.component : null,
                        object: t ? t.object : null,
                        point: t ? t.point : null,
                        normal: t ? t.normal : null
                    })
                }
            }
            return super.onPointer(e)
        }
        pick(e, t, n) {
            if (!this.root) return [];
            o.set(e, t), this.raycaster.setFromCamera(o, n);
            const r = this.raycaster.intersectObject(this.root, !0);
            if (0 === r.length) return [];
            const i = [];
            return r.forEach(e => {
                let t = void 0,
                    n = e.object;
                for (; n && void 0 === (t = this.objectComponents[n.id]);) n = n === this.root ? null : n.parent;
                t && i.push({
                    component: t,
                    object: e.object,
                    point: e.point,
                    normal: e.face.normal
                })
            }), i
        }
        onObject3D(e) {
            const t = e.component;
            e.add ? (t.on("object", this.onObject3DObject, this), t.object3D && (this.objectComponents[t.object3D.id] = t)) : e.remove && (t.off("object", this.onObject3DObject, this), delete this.objectComponents[t.object3D.id])
        }
        onObject3DObject(e) {
            e.current && delete this.objectComponents[e.current.id], e.next && (this.objectComponents[e.next.id] = e.sender)
        }
    }
    s.type = "PickManip", s.isSystemSingleton = !0, t.default = s
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(12),
        a = new r.Vector4,
        o = (new r.Vector4, new r.Vector3),
        s = new r.Vector3,
        l = new r.Matrix4,
        c = new r.Euler,
        d = new r.Quaternion,
        u = {
            PI: 3.141592653589793,
            DOUBLE_PI: 6.283185307179586,
            HALF_PI: 1.5707963267948966,
            QUARTER_PI: .7853981633974483,
            DEG2RAD: .017453292519943295,
            RAD2DEG: 57.29577951308232,
            composeOrbitMatrix: function(e, t, n) {
                const i = -e.x,
                    a = -e.y,
                    o = -e.z,
                    s = t.x,
                    l = t.y,
                    c = t.z,
                    d = Math.sin(i),
                    u = Math.cos(i),
                    h = Math.sin(a),
                    p = Math.cos(a),
                    f = Math.sin(o),
                    m = Math.cos(o),
                    v = p * m,
                    g = m * h * d - f * u,
                    y = m * h * u + f * d,
                    _ = p * f,
                    x = d * h * f + m * u,
                    E = f * h * u - m * d,
                    b = -h,
                    P = p * d,
                    w = p * u,
                    S = (n = n || new r.Matrix4).elements;
                return S[0] = v, S[1] = _, S[2] = b, S[3] = 0, S[4] = g, S[5] = x, S[6] = P, S[7] = 0, S[8] = y, S[9] = E, S[10] = w, S[11] = 0, S[12] = s * v + l * g + c * y, S[13] = s * _ + l * x + c * E, S[14] = s * b + l * P + c * w, S[15] = 1, n
            },
            decomposeOrbitMatrix: function(e, t, n) {
                c.setFromRotationMatrix(e, "ZYX"), c.toVector3(t), l.getInverse(e), a.set(0, 0, 0, 1), a.applyMatrix4(l), n.x = -a.x, n.y = -a.y, n.z = -a.z
            },
            isMatrix4Identity: function(e) {
                const t = e.elements;
                return 1 === t[0] && 0 === t[1] && 0 === t[2] && 0 === t[3] && 0 === t[4] && 1 === t[5] && 0 === t[6] && 0 === t[7] && 0 === t[8] && 0 === t[9] && 1 === t[10] && 0 === t[11] && 0 === t[12] && 0 === t[13] && 0 === t[14] && 1 === t[15]
            },
            decomposeTransformMatrix: function(e, t, n, r) {
                l.fromArray(e), l.decompose(o, d, s), c.setFromQuaternion(d, "XYZ"), o.toArray(t), s.toArray(r), c.toVector3(o), a.multiplyScalar(i.default.RAD2DEG), o.toArray(n)
            }
        };
    t.default = u
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(14),
        i = n(156),
        a = n(200);
    class o extends r.default {
        constructor() {
            super(...arguments), this.ins = this.makeProps({}), this.actions = null, this.context = new a.default, this.views = [], this.layoutMode = i.EViewportLayoutMode.Single, this.nextManip = null
        }
        createActions(e) {
            const t = {};
            return this.actions = t, t
        }
        renderViews(e, t) {
            const n = this.context;
            this.views.forEach(r => {
                const {
                    view: i,
                    viewportLayout: a
                } = r;
                i && (i.renderer.clear(), a.forEachViewport((r, a) => {
                    r.sceneCamera = t, r.updateCamera();
                    const o = r.camera;
                    n.set(r, o, e), this.system.render(n), r.render(i.renderer, e)
                }))
            })
        }
        setViewportLayout(e) {
            this.layoutMode = e, this.views.forEach(t => {
                t.viewportLayout.layoutMode = e
            })
        }
        setNextManip(e) {
            this.nextManip = e, this.views.forEach(t => {
                t.viewportLayout.next = e
            })
        }
        registerView(e) {
            const t = this.views.find(e => null === e.view);
            if (t) return t.view = e, t.viewportLayout;
            const n = new i.default;
            n.setCanvasSize(e.canvasWidth, e.canvasHeight), n.next = this.nextManip, n.layoutMode = this.layoutMode;
            const r = {
                viewportLayout: n,
                view: e
            };
            return this.views.push(r), n
        }
        unregisterView(e) {
            const t = this.views.find(t => t.view === e);
            if (!t) throw new Error("view not found");
            t.view = null
        }
    }
    o.type = "RenderController", t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(4),
        a = n(5);
    var o;
    ! function(e) {
        e[e.Perspective = 0] = "Perspective", e[e.Orthographic = 1] = "Orthographic"
    }(o = t.EProjectionType || (t.EProjectionType = {}));
    class s extends a.default {
        constructor() {
            super(...arguments), this.ins = this.makeProps({
                pro: i.default.Enum("Projection", o),
                fov: i.default.Number("FovY", 50),
                siz: i.default.Number("Size", 50),
                zn: i.default.Number("Frustum.Near", .001),
                zf: i.default.Number("Frustum.Far", 1e4)
            })
        }
        get camera() {
            return this.object3D
        }
        update() {
            const {
                pro: e,
                fov: t,
                siz: n,
                zn: a,
                zf: s
            } = this.ins, l = this.camera, c = l ? l.userData.aspect : 1, d = .5 * n.value, u = d * c;
            if (e.changed) this.object3D = i.default.isEnumEntry(o.Perspective, e.value) ? new r.PerspectiveCamera(t.value, c, a.value, s.value) : new r.OrthographicCamera(-u, u, d, -d, a.value, s.value), this.object3D.userData.aspect = c;
            else if ("PerspectiveCamera" === l.type) {
                const e = l;
                e.fov = t.value, e.near = a.value, e.far = s.value, e.updateProjectionMatrix()
            } else if ("OrthographicCamera" === l.type) {
                const e = l;
                e.left = -u, e.right = u, e.top = d, e.bottom = -d, e.near = a.value, e.far = s.value, e.updateProjectionMatrix()
            }
        }
        fromData(e) {
            "perspective" === e.type ? this.ins.setValues({
                pro: o.Perspective,
                fov: e.perspective.yfov,
                zn: e.perspective.znear,
                zf: e.perspective.zfar
            }) : this.ins.setValues({
                pro: o.Orthographic,
                siz: e.orthographic.ymag,
                zn: e.orthographic.znear,
                zf: e.orthographic.zfar
            })
        }
        toData() {
            const e = {},
                t = this.ins;
            return i.default.isEnumEntry(o.Perspective, t.pro.value) ? (e.type = "perspective", e.perspective = {
                yfov: t.fov.value,
                znear: t.zn.value,
                zfar: t.zf.value
            }) : (e.type = "orthographic", e.orthographic = {
                ymag: t.siz.value,
                znear: t.zn.value,
                zfar: t.zf.value
            }), e
        }
    }
    s.type = "Camera", t.default = s
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
            value: !0
        }),
        function(e) {
            e[e.Model = 0] = "Model", e[e.Geometry = 1] = "Geometry", e[e.Image = 2] = "Image", e[e.Points = 3] = "Points", e[e.Volume = 4] = "Volume"
        }(t.EAssetType || (t.EAssetType = {})),
        function(e) {
            e[e.Color = 0] = "Color", e[e.Normal = 1] = "Normal", e[e.Occlusion = 2] = "Occlusion", e[e.Emissive = 3] = "Emissive", e[e.MetallicRoughness = 4] = "MetallicRoughness", e[e.Zone = 5] = "Zone"
        }(t.EMapType || (t.EMapType = {}));
    t.default = class {}
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(19),
        i = n(207),
        a = n(18),
        o = n(23),
        s = n(21),
        l = n(208),
        c = n(259),
        d = n(179),
        u = n(14);
    class h extends u.default {
        constructor() {
            super(...arguments), this.actions = null, this.loaders = new l.default, this.presentations = [], this.presentation = null
        }
        create() {
            super.create(), this.addEvents("presentation")
        }
        createActions(e) {
            const t = {
                setInputValue: e.register({
                    name: "Set Value",
                    do: this.setInputValue,
                    target: this
                }),
                loadPresentation: e.register({
                    name: "Load Presentation",
                    do: this.loadPresentation,
                    target: this
                })
            };
            return this.actions = t, t
        }
        get activePresentation() {
            return this.presentation
        }
        loadModel(e, t) {
            return t = void 0 !== t ? t : a.EDerivativeQuality.Medium, Promise.resolve().then(() => {
                console.log(`Creating new 3D item with a web derivative, quality: ${a.EDerivativeQuality[t]}\n`, `model url: ${e}`);
                const n = new d.default(this.system.createEntity("Item"), this.loaders);
                return n.addWebModelDerivative(e, t), this.openDefaultPresentation(e, n)
            })
        }
        loadGeometryAndTexture(e, t, n) {
            return n = void 0 !== n ? n : a.EDerivativeQuality.Medium, Promise.resolve().then(() => {
                console.log(`Creating a new 3D item with a web derivative of quality: ${a.EDerivativeQuality[n]}\n`, `geometry url: ${e}, texture url: ${t}`);
                const r = new d.default(this.system.createEntity("Item"), this.loaders);
                return r.addGeometryAndTextureDerivative(e, t, n), this.openDefaultPresentation(e, r)
            })
        }
        loadItem(e, t) {
            return this.loaders.loadJSON(e).then(n => this.openItem(n, e, t))
        }
        openItem(e, t, n) {
            t = t || window.location.href;
            const i = n ? n.substr(r.default(".", n).length) : "";
            return this.loaders.validateItem(e).then(e => {
                const a = new d.default(this.system.createEntity("Item"), this.loaders);
                if (a.inflate(e, t), a.templateName) {
                    const e = r.default(i, a.templateName, n || t);
                    return console.log(`Loading presentation template: ${e}`), this.loadPresentation(e, a)
                }
                return this.openDefaultPresentation(t, a)
            })
        }
        loadPresentation(e, t) {
            return this.loaders.loadJSON(e).then(n => this.openPresentation(n, e, t))
        }
        openPresentation(e, t, n) {
            return t = t || window.location.href, this.loaders.validatePresentation(e).then(e => {
                const r = new c.default(this.system, this.loaders);
                r.inflate(e, t, n), this.presentations.push(r), this.setActivePresentation(this.presentations.length - 1)
            })
        }
        openDefaultPresentation(e, t) {
            return console.log("opening presentation from default template"), this.openPresentation(i, e, t)
        }
        writePresentation() {
            return this.activePresentation.deflate()
        }
        closeAll() {
            this.setActivePresentation(null), this.presentations.forEach(e => {
                e.dispose()
            }), this.presentations.length = 0
        }
        addInputListener(e, t, n, r) {
            this.getSafeComponent(e).in(t).on("value", n, r)
        }
        removeInputListener(e, t, n, r) {
            this.getSafeComponent(e).in(t).off("value", n, r)
        }
        addOutputListener(e, t, n, r) {
            this.getSafeComponent(e).out(t).on("value", n, r)
        }
        removeOutputListener(e, t, n, r) {
            this.getSafeComponent(e).out(t).off("value", n, r)
        }
        getInputValue(e, t) {
            return this.getSafeComponent(e).in(t).value
        }
        getOutputVaue(e, t) {
            return this.getSafeComponent(e).out(t).value
        }
        setInputValue(e, t, n) {
            this.getSafeComponent(e).in(t).setValue(n)
        }
        getSafeComponent(e) {
            const t = this.presentation;
            if (!t) throw new Error("PresentationController - no active presentation");
            const n = t.entity.getComponent(e);
            if (!n) throw new Error(`PresentationController, component type not found: ${e}`);
            return n
        }
        setActivePresentation(e) {
            const t = this.presentation,
                n = this.presentation = this.presentations[e];
            this.onPresentationChange(t, n), this.emit("presentation", {
                current: t,
                next: n
            })
        }
        onPresentationChange(e, t) {
            const n = this.system.getComponent(o.default),
                r = this.system.getComponent(s.default);
            if (e) {
                n.setRoot(null);
                const t = e.cameraTransform;
                t && t.in("Matrix").unlinkFrom(r.out("Orbit.Matrix"));
                const i = e.cameraComponent;
                i && (i.in("Projection").unlinkFrom(r.out("View.Projection")), i.in("Size").unlinkFrom(r.out("View.Size")));
                const a = e.lightsTransform;
                a && a.in("Rotation").unlinkFrom(r.out("Orbit.InverseOrientation"))
            }
            if (t) {
                n.setRoot(t.scene);
                const e = t.cameraTransform;
                e && (r.setFromMatrix(e.matrix), e.in("Matrix").linkFrom(r.out("Orbit.Matrix")));
                const i = t.cameraComponent;
                i && (i.in("Projection").linkFrom(r.out("View.Projection")), i.in("Size").linkFrom(r.out("View.Size")));
                const a = t.lightsTransform;
                a && (a.in("Order").setValue(4), a.in("Rotation").linkFrom(r.out("Orbit.InverseOrientation")))
            }
        }
    }
    h.type = "PresentationController", t.default = h
}, function(e, t, n) {
    "use strict";
    var r = n(217),
        i = n(30),
        a = n(7),
        o = n(166),
        s = n(219);

    function l(e, t, n) {
        var r = this._refs[n];
        if ("string" == typeof r) {
            if (!this._refs[r]) return l.call(this, e, t, r);
            r = this._refs[r]
        }
        if ((r = r || this._schemas[n]) instanceof o) return p(r.schema, this._opts.inlineRefs) ? r.schema : r.validate || this._compile(r);
        var i, a, s, d = c.call(this, t, n);
        return d && (i = d.schema, t = d.root, s = d.baseId), i instanceof o ? a = i.validate || e.call(this, i.schema, t, void 0, s) : void 0 !== i && (a = p(i, this._opts.inlineRefs) ? i : e.call(this, i, t, void 0, s)), a
    }

    function c(e, t) {
        var n = r.parse(t),
            i = m(n),
            a = f(this._getId(e.schema));
        if (0 === Object.keys(e.schema).length || i !== a) {
            var s = g(i),
                l = this._refs[s];
            if ("string" == typeof l) return function(e, t, n) {
                var r = c.call(this, e, t);
                if (r) {
                    var i = r.schema,
                        a = r.baseId;
                    e = r.root;
                    var o = this._getId(i);
                    return o && (a = y(a, o)), u.call(this, n, a, i, e)
                }
            }.call(this, e, l, n);
            if (l instanceof o) l.validate || this._compile(l), e = l;
            else {
                if (!((l = this._schemas[s]) instanceof o)) return;
                if (l.validate || this._compile(l), s == g(t)) return {
                    schema: l,
                    root: e,
                    baseId: a
                };
                e = l
            }
            if (!e.schema) return;
            a = f(this._getId(e.schema))
        }
        return u.call(this, n, a, e.schema, e)
    }
    e.exports = l, l.normalizeId = g, l.fullPath = f, l.url = y, l.ids = function(e) {
        var t = g(this._getId(e)),
            n = {
                "": t
            },
            o = {
                "": f(t, !1)
            },
            l = {},
            c = this;
        return s(e, {
            allKeys: !0
        }, function(e, t, s, d, u, h, p) {
            if ("" !== t) {
                var f = c._getId(e),
                    m = n[d],
                    v = o[d] + "/" + u;
                if (void 0 !== p && (v += "/" + ("number" == typeof p ? p : a.escapeFragment(p))), "string" == typeof f) {
                    f = m = g(m ? r.resolve(m, f) : f);
                    var y = c._refs[f];
                    if ("string" == typeof y && (y = c._refs[y]), y && y.schema) {
                        if (!i(e, y.schema)) throw new Error('id "' + f + '" resolves to more than one schema')
                    } else if (f != g(v))
                        if ("#" == f[0]) {
                            if (l[f] && !i(e, l[f])) throw new Error('id "' + f + '" resolves to more than one schema');
                            l[f] = e
                        } else c._refs[f] = v
                }
                n[t] = m, o[t] = v
            }
        }), l
    }, l.inlineRef = p, l.schema = c;
    var d = a.toHash(["properties", "patternProperties", "enum", "dependencies", "definitions"]);

    function u(e, t, n, r) {
        if (e.fragment = e.fragment || "", "/" == e.fragment.slice(0, 1)) {
            for (var i = e.fragment.split("/"), o = 1; o < i.length; o++) {
                var s = i[o];
                if (s) {
                    if (void 0 === (n = n[s = a.unescapeFragment(s)])) break;
                    var l;
                    if (!d[s] && ((l = this._getId(n)) && (t = y(t, l)), n.$ref)) {
                        var u = y(t, n.$ref),
                            h = c.call(this, r, u);
                        h && (n = h.schema, r = h.root, t = h.baseId)
                    }
                }
            }
            return void 0 !== n && n !== r.schema ? {
                schema: n,
                root: r,
                baseId: t
            } : void 0
        }
    }
    var h = a.toHash(["type", "format", "pattern", "maxLength", "minLength", "maxProperties", "minProperties", "maxItems", "minItems", "maximum", "minimum", "uniqueItems", "multipleOf", "required", "enum"]);

    function p(e, t) {
        return !1 !== t && (void 0 === t || !0 === t ? function e(t) {
            var n;
            if (Array.isArray(t)) {
                for (var r = 0; r < t.length; r++)
                    if ("object" == typeof(n = t[r]) && !e(n)) return !1
            } else
                for (var i in t) {
                    if ("$ref" == i) return !1;
                    if ("object" == typeof(n = t[i]) && !e(n)) return !1
                }
            return !0
        }(e) : t ? function e(t) {
            var n, r = 0;
            if (Array.isArray(t)) {
                for (var i = 0; i < t.length; i++)
                    if ("object" == typeof(n = t[i]) && (r += e(n)), r == 1 / 0) return 1 / 0
            } else
                for (var a in t) {
                    if ("$ref" == a) return 1 / 0;
                    if (h[a]) r++;
                    else if ("object" == typeof(n = t[a]) && (r += e(n) + 1), r == 1 / 0) return 1 / 0
                }
            return r
        }(e) <= t : void 0)
    }

    function f(e, t) {
        return !1 !== t && (e = g(e)), m(r.parse(e))
    }

    function m(e) {
        return r.serialize(e).split("#")[0] + "#"
    }
    var v = /#\/?$/;

    function g(e) {
        return e ? e.replace(v, "") : ""
    }

    function y(e, t) {
        return t = g(t), r.resolve(e, t)
    }
}, function(e, t, n) {
    "use strict";
    var r = Array.isArray,
        i = Object.keys,
        a = Object.prototype.hasOwnProperty;
    e.exports = function e(t, n) {
        if (t === n) return !0;
        if (t && n && "object" == typeof t && "object" == typeof n) {
            var o, s, l, c = r(t),
                d = r(n);
            if (c && d) {
                if ((s = t.length) != n.length) return !1;
                for (o = s; 0 != o--;)
                    if (!e(t[o], n[o])) return !1;
                return !0
            }
            if (c != d) return !1;
            var u = t instanceof Date,
                h = n instanceof Date;
            if (u != h) return !1;
            if (u && h) return t.getTime() == n.getTime();
            var p = t instanceof RegExp,
                f = n instanceof RegExp;
            if (p != f) return !1;
            if (p && f) return t.toString() == n.toString();
            var m = i(t);
            if ((s = m.length) !== i(n).length) return !1;
            for (o = s; 0 != o--;)
                if (!a.call(n, m[o])) return !1;
            for (o = s; 0 != o--;)
                if (!e(t[l = m[o]], n[l])) return !1;
            return !0
        }
        return t != t && n != n
    }
}, function(e, t, n) {
    "use strict";
    var r = n(29);

    function i(e, t, n) {
        this.message = n || i.message(e, t), this.missingRef = r.url(e, t), this.missingSchema = r.normalizeId(r.fullPath(this.missingRef))
    }

    function a(e) {
        return e.prototype = Object.create(Error.prototype), e.prototype.constructor = e, e
    }
    e.exports = {
        Validation: a(function(e) {
            this.message = "validation failed", this.errors = e, this.ajv = this.validation = !0
        }),
        MissingRef: a(i)
    }, i.message = function(e, t) {
        return "can't resolve reference " + t + " from id " + e
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(8);
    class i extends r.default {
        constructor() {
            super(...arguments), this.rootCollection = null
        }
        create() {
            this.rootCollection = this.findRootCollection()
        }
        createDocument() {
            return this.addDocument({
                title: "New Document",
                description: "",
                mimeType: "text/plain",
                uri: "",
                thumbnailUri: ""
            })
        }
        addDocument(e) {
            const t = this.insert(e);
            return this.rootCollection && this.rootCollection.addDocument(e), this.emit("change", {
                what: "add",
                document: e
            }), t
        }
        removeDocument(e) {
            const t = this.remove(e);
            return this.rootCollection && this.rootCollection.removeDocument(e), this.emit("change", {
                what: "remove",
                document: t
            }), t
        }
        fromData(e) {
            return e.map(e => this.addDocument({
                title: e.title,
                description: e.description || "",
                mimeType: e.mimeType || "",
                uri: e.uri,
                thumbnailUri: e.thumbnailUri || ""
            }))
        }
        toData() {
            const e = {
                data: [],
                ids: {}
            };
            return this.getArray().forEach((t, n) => {
                e.ids[t.id] = n;
                const r = {
                    title: t.title,
                    uri: t.uri
                };
                t.description && (r.description = t.description), t.mimeType && (r.mimeType = t.mimeType), t.thumbnailUri && (r.thumbnailUri = t.thumbnailUri), e.data.push(r)
            }), e
        }
    }
    i.type = "Documents", t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(8);
    class i extends r.default {
        constructor() {
            super(...arguments), this.rootCollection = null
        }
        create() {
            this.rootCollection = this.findRootCollection()
        }
        createGroup() {
            return this.addGroup({
                title: "New Group",
                description: ""
            })
        }
        addGroup(e) {
            const t = this.insert(e);
            return this.rootCollection && this.rootCollection.addGroup(e), this.emit("change", {
                what: "add",
                group: e
            }), t
        }
        removeGroup(e) {
            const t = this.remove(e);
            return this.rootCollection && this.rootCollection.removeGroup(e), this.emit("change", {
                what: "remove",
                group: t
            }), t
        }
        fromData(e) {
            return e.map(e => this.addGroup({
                title: e.title,
                description: e.description || ""
            }))
        }
        toData() {
            const e = {
                data: [],
                ids: {}
            };
            return this.getArray().forEach((t, n) => {
                e.ids[t.id] = n;
                const r = {
                    title: t.title
                };
                t.description && (r.description = t.description), e.data.push(r)
            }), e
        }
    }
    i.type = "Groups", t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(8);
    class i extends r.default {
        constructor() {
            super(...arguments), this.rootCollection = null
        }
        create() {
            this.rootCollection = this.findRootCollection()
        }
        addTour(e) {
            this.insert(e), this.rootCollection && this.rootCollection.addTour(e), this.emit("changed")
        }
        removeTour(e) {
            const t = this.remove(e);
            return this.rootCollection && this.rootCollection.removeTour(e), this.emit("changed"), t
        }
        fromData(e, t) {
            e.forEach(e => {
                this.addTour({
                    title: e.title,
                    description: e.description || "",
                    steps: e.steps.map(e => ({
                        snapshotId: t[e.snapshot],
                        transitionTime: e.transitionTime,
                        transitionCurve: e.transitionCurve,
                        transitionCutPoint: e.transitionCutPoint
                    }))
                })
            })
        }
        toData(e) {
            return this.getArray().map(t => {
                const n = {
                    title: t.title,
                    steps: t.steps.map(t => ({
                        snapshot: e[t.snapshotId],
                        transitionTime: t.transitionTime,
                        transitionCurve: t.transitionCurve,
                        transitionCutPoint: t.transitionCutPoint
                    }))
                };
                return t.description && (n.description = t.description), n
            })
        }
    }
    i.type = "Tours", t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(3);
    class i extends r.default {
        constructor() {
            super(...arguments), this.data = {}
        }
        set(e, t) {
            this.data[e] = t
        }
        get(e) {
            return this.data[e]
        }
        remove(e) {
            delete this.data[e]
        }
        clear() {
            this.data = {}
        }
        fromData(e) {
            this.data = Object.assign({}, e)
        }
        toData() {
            return Object.assign({}, this.data)
        }
    }
    i.type = "Meta", t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(8);
    class i extends r.default {
        createAnnotation(e, t, n = -1) {
            const r = {
                title: "New Annotation",
                description: "",
                expanded: !0,
                snapshot: "",
                documents: [],
                groups: [],
                position: e,
                direction: t,
                index: n
            };
            return this.addAnnotation(r)
        }
        addAnnotation(e) {
            const t = this.insert(e);
            return this.emit("change", {
                what: "add",
                annotation: e
            }), t
        }
        removeAnnotation(e) {
            const t = this.remove(e);
            return this.emit("change", {
                what: "remove",
                annotation: t
            }), t
        }
        fromData(e, t, n, r) {
            e.forEach(e => {
                const i = {
                    title: e.title || "",
                    description: e.description || "",
                    expanded: e.expanded || !1,
                    snapshot: void 0 !== e.snapshot ? r[e.snapshot] : "",
                    documents: e.documents ? e.documents.map(e => n[e]) : [],
                    groups: e.groups ? e.groups.map(e => t[e]) : [],
                    position: e.position,
                    direction: e.direction,
                    index: void 0 !== e.zoneIndex ? e.zoneIndex : -1
                };
                this.addAnnotation(i)
            })
        }
        toData(e, t, n) {
            return this.getArray().map(r => {
                const i = {
                    title: r.title,
                    position: r.position.slice(),
                    direction: r.direction.slice()
                };
                return r.description && (i.description = r.description), r.expanded && (i.expanded = r.expanded), r.snapshot && (i.snapshot = n[r.snapshot]), r.documents.length > 0 && (i.documents = r.documents.map(e => t[e])), r.groups.length > 0 && (i.groups = r.groups.map(t => e[t])), r.index > -1 && (i.zoneIndex = r.index), i
            })
        }
    }
    i.type = "Annotations", t.default = i
}, function(e, t) {
    e.exports = "#ifdef USE_ALPHAMAP\n\n\tdiffuseColor.a *= texture2D( alphaMap, vUv ).g;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_ALPHAMAP\n\n\tuniform sampler2D alphaMap;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef ALPHATEST\n\n\tif ( diffuseColor.a < ALPHATEST ) discard;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_AOMAP\n\n\t// reads channel R, compatible with a combined OcclusionRoughnessMetallic (RGB) texture\n\tfloat ambientOcclusion = ( texture2D( aoMap, vUv2 ).r - 1.0 ) * aoMapIntensity + 1.0;\n\n\treflectedLight.indirectDiffuse *= ambientOcclusion;\n\n\t#if defined( USE_ENVMAP ) && defined( PHYSICAL )\n\n\t\tfloat dotNV = saturate( dot( geometry.normal, geometry.viewDir ) );\n\n\t\treflectedLight.indirectSpecular *= computeSpecularOcclusion( dotNV, ambientOcclusion, material.specularRoughness );\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_AOMAP\n\n\tuniform sampler2D aoMap;\n\tuniform float aoMapIntensity;\n\n#endif"
}, function(e, t) {
    e.exports = "\nvec3 transformed = vec3( position );\n"
}, function(e, t) {
    e.exports = "\nvec3 objectNormal = vec3( normal );\n"
}, function(e, t) {
    e.exports = 'float punctualLightIntensityToIrradianceFactor( const in float lightDistance, const in float cutoffDistance, const in float decayExponent ) {\n\n#if defined ( PHYSICALLY_CORRECT_LIGHTS )\n\n\t// based upon Frostbite 3 Moving to Physically-based Rendering\n\t// page 32, equation 26: E[window1]\n\t// https://seblagarde.files.wordpress.com/2015/07/course_notes_moving_frostbite_to_pbr_v32.pdf\n\t// this is intended to be used on spot and point lights who are represented as luminous intensity\n\t// but who must be converted to luminous irradiance for surface lighting calculation\n\tfloat distanceFalloff = 1.0 / max( pow( lightDistance, decayExponent ), 0.01 );\n\n\tif( cutoffDistance > 0.0 ) {\n\n\t\tdistanceFalloff *= pow2( saturate( 1.0 - pow4( lightDistance / cutoffDistance ) ) );\n\n\t}\n\n\treturn distanceFalloff;\n\n#else\n\n\tif( cutoffDistance > 0.0 ) {\n\n\t\treturn pow( saturate( -lightDistance / cutoffDistance + 1.0 ), decayExponent );\n\n\t}\n\n\treturn 1.0;\n\n#endif\n\n}\n\nvec3 BRDF_Diffuse_Lambert( const in vec3 diffuseColor ) {\n\n\treturn RECIPROCAL_PI * diffuseColor;\n\n} // validated\n\nvec3 F_Schlick( const in vec3 specularColor, const in float dotLH ) {\n\n\t// Original approximation by Christophe Schlick \'94\n\t// float fresnel = pow( 1.0 - dotLH, 5.0 );\n\n\t// Optimized variant (presented by Epic at SIGGRAPH \'13)\n\t// https://cdn2.unrealengine.com/Resources/files/2013SiggraphPresentationsNotes-26915738.pdf\n\tfloat fresnel = exp2( ( -5.55473 * dotLH - 6.98316 ) * dotLH );\n\n\treturn ( 1.0 - specularColor ) * fresnel + specularColor;\n\n} // validated\n\n// Microfacet Models for Refraction through Rough Surfaces - equation (34)\n// http://graphicrants.blogspot.com/2013/08/specular-brdf-reference.html\n// alpha is "roughness squared" in Disney’s reparameterization\nfloat G_GGX_Smith( const in float alpha, const in float dotNL, const in float dotNV ) {\n\n\t// geometry term (normalized) = G(l)⋅G(v) / 4(n⋅l)(n⋅v)\n\t// also see #12151\n\n\tfloat a2 = pow2( alpha );\n\n\tfloat gl = dotNL + sqrt( a2 + ( 1.0 - a2 ) * pow2( dotNL ) );\n\tfloat gv = dotNV + sqrt( a2 + ( 1.0 - a2 ) * pow2( dotNV ) );\n\n\treturn 1.0 / ( gl * gv );\n\n} // validated\n\n// Moving Frostbite to Physically Based Rendering 3.0 - page 12, listing 2\n// https://seblagarde.files.wordpress.com/2015/07/course_notes_moving_frostbite_to_pbr_v32.pdf\nfloat G_GGX_SmithCorrelated( const in float alpha, const in float dotNL, const in float dotNV ) {\n\n\tfloat a2 = pow2( alpha );\n\n\t// dotNL and dotNV are explicitly swapped. This is not a mistake.\n\tfloat gv = dotNL * sqrt( a2 + ( 1.0 - a2 ) * pow2( dotNV ) );\n\tfloat gl = dotNV * sqrt( a2 + ( 1.0 - a2 ) * pow2( dotNL ) );\n\n\treturn 0.5 / max( gv + gl, EPSILON );\n\n}\n\n// Microfacet Models for Refraction through Rough Surfaces - equation (33)\n// http://graphicrants.blogspot.com/2013/08/specular-brdf-reference.html\n// alpha is "roughness squared" in Disney’s reparameterization\nfloat D_GGX( const in float alpha, const in float dotNH ) {\n\n\tfloat a2 = pow2( alpha );\n\n\tfloat denom = pow2( dotNH ) * ( a2 - 1.0 ) + 1.0; // avoid alpha = 0 with dotNH = 1\n\n\treturn RECIPROCAL_PI * a2 / pow2( denom );\n\n}\n\n// GGX Distribution, Schlick Fresnel, GGX-Smith Visibility\nvec3 BRDF_Specular_GGX( const in IncidentLight incidentLight, const in GeometricContext geometry, const in vec3 specularColor, const in float roughness ) {\n\n\tfloat alpha = pow2( roughness ); // UE4\'s roughness\n\n\tvec3 halfDir = normalize( incidentLight.direction + geometry.viewDir );\n\n\tfloat dotNL = saturate( dot( geometry.normal, incidentLight.direction ) );\n\tfloat dotNV = saturate( dot( geometry.normal, geometry.viewDir ) );\n\tfloat dotNH = saturate( dot( geometry.normal, halfDir ) );\n\tfloat dotLH = saturate( dot( incidentLight.direction, halfDir ) );\n\n\tvec3 F = F_Schlick( specularColor, dotLH );\n\n\tfloat G = G_GGX_SmithCorrelated( alpha, dotNL, dotNV );\n\n\tfloat D = D_GGX( alpha, dotNH );\n\n\treturn F * ( G * D );\n\n} // validated\n\n// Rect Area Light\n\n// Real-Time Polygonal-Light Shading with Linearly Transformed Cosines\n// by Eric Heitz, Jonathan Dupuy, Stephen Hill and David Neubelt\n// code: https://github.com/selfshadow/ltc_code/\n\nvec2 LTC_Uv( const in vec3 N, const in vec3 V, const in float roughness ) {\n\n\tconst float LUT_SIZE  = 64.0;\n\tconst float LUT_SCALE = ( LUT_SIZE - 1.0 ) / LUT_SIZE;\n\tconst float LUT_BIAS  = 0.5 / LUT_SIZE;\n\n\tfloat dotNV = saturate( dot( N, V ) );\n\n\t// texture parameterized by sqrt( GGX alpha ) and sqrt( 1 - cos( theta ) )\n\tvec2 uv = vec2( roughness, sqrt( 1.0 - dotNV ) );\n\n\tuv = uv * LUT_SCALE + LUT_BIAS;\n\n\treturn uv;\n\n}\n\nfloat LTC_ClippedSphereFormFactor( const in vec3 f ) {\n\n\t// Real-Time Area Lighting: a Journey from Research to Production (p.102)\n\t// An approximation of the form factor of a horizon-clipped rectangle.\n\n\tfloat l = length( f );\n\n\treturn max( ( l * l + f.z ) / ( l + 1.0 ), 0.0 );\n\n}\n\nvec3 LTC_EdgeVectorFormFactor( const in vec3 v1, const in vec3 v2 ) {\n\n\tfloat x = dot( v1, v2 );\n\n\tfloat y = abs( x );\n\n\t// rational polynomial approximation to theta / sin( theta ) / 2PI\n\tfloat a = 0.8543985 + ( 0.4965155 + 0.0145206 * y ) * y;\n\tfloat b = 3.4175940 + ( 4.1616724 + y ) * y;\n\tfloat v = a / b;\n\n\tfloat theta_sintheta = ( x > 0.0 ) ? v : 0.5 * inversesqrt( max( 1.0 - x * x, 1e-7 ) ) - v;\n\n\treturn cross( v1, v2 ) * theta_sintheta;\n\n}\n\nvec3 LTC_Evaluate( const in vec3 N, const in vec3 V, const in vec3 P, const in mat3 mInv, const in vec3 rectCoords[ 4 ] ) {\n\n\t// bail if point is on back side of plane of light\n\t// assumes ccw winding order of light vertices\n\tvec3 v1 = rectCoords[ 1 ] - rectCoords[ 0 ];\n\tvec3 v2 = rectCoords[ 3 ] - rectCoords[ 0 ];\n\tvec3 lightNormal = cross( v1, v2 );\n\n\tif( dot( lightNormal, P - rectCoords[ 0 ] ) < 0.0 ) return vec3( 0.0 );\n\n\t// construct orthonormal basis around N\n\tvec3 T1, T2;\n\tT1 = normalize( V - N * dot( V, N ) );\n\tT2 = - cross( N, T1 ); // negated from paper; possibly due to a different handedness of world coordinate system\n\n\t// compute transform\n\tmat3 mat = mInv * transposeMat3( mat3( T1, T2, N ) );\n\n\t// transform rect\n\tvec3 coords[ 4 ];\n\tcoords[ 0 ] = mat * ( rectCoords[ 0 ] - P );\n\tcoords[ 1 ] = mat * ( rectCoords[ 1 ] - P );\n\tcoords[ 2 ] = mat * ( rectCoords[ 2 ] - P );\n\tcoords[ 3 ] = mat * ( rectCoords[ 3 ] - P );\n\n\t// project rect onto sphere\n\tcoords[ 0 ] = normalize( coords[ 0 ] );\n\tcoords[ 1 ] = normalize( coords[ 1 ] );\n\tcoords[ 2 ] = normalize( coords[ 2 ] );\n\tcoords[ 3 ] = normalize( coords[ 3 ] );\n\n\t// calculate vector form factor\n\tvec3 vectorFormFactor = vec3( 0.0 );\n\tvectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 0 ], coords[ 1 ] );\n\tvectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 1 ], coords[ 2 ] );\n\tvectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 2 ], coords[ 3 ] );\n\tvectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 3 ], coords[ 0 ] );\n\n\t// adjust for horizon clipping\n\tfloat result = LTC_ClippedSphereFormFactor( vectorFormFactor );\n\n/*\n\t// alternate method of adjusting for horizon clipping (see referece)\n\t// refactoring required\n\tfloat len = length( vectorFormFactor );\n\tfloat z = vectorFormFactor.z / len;\n\n\tconst float LUT_SIZE  = 64.0;\n\tconst float LUT_SCALE = ( LUT_SIZE - 1.0 ) / LUT_SIZE;\n\tconst float LUT_BIAS  = 0.5 / LUT_SIZE;\n\n\t// tabulated horizon-clipped sphere, apparently...\n\tvec2 uv = vec2( z * 0.5 + 0.5, len );\n\tuv = uv * LUT_SCALE + LUT_BIAS;\n\n\tfloat scale = texture2D( ltc_2, uv ).w;\n\n\tfloat result = len * scale;\n*/\n\n\treturn vec3( result );\n\n}\n\n// End Rect Area Light\n\n// ref: https://www.unrealengine.com/blog/physically-based-shading-on-mobile - environmentBRDF for GGX on mobile\nvec3 BRDF_Specular_GGX_Environment( const in GeometricContext geometry, const in vec3 specularColor, const in float roughness ) {\n\n\tfloat dotNV = saturate( dot( geometry.normal, geometry.viewDir ) );\n\n\tconst vec4 c0 = vec4( - 1, - 0.0275, - 0.572, 0.022 );\n\n\tconst vec4 c1 = vec4( 1, 0.0425, 1.04, - 0.04 );\n\n\tvec4 r = roughness * c0 + c1;\n\n\tfloat a004 = min( r.x * r.x, exp2( - 9.28 * dotNV ) ) * r.x + r.y;\n\n\tvec2 AB = vec2( -1.04, 1.04 ) * a004 + r.zw;\n\n\treturn specularColor * AB.x + AB.y;\n\n} // validated\n\n\nfloat G_BlinnPhong_Implicit( /* const in float dotNL, const in float dotNV */ ) {\n\n\t// geometry term is (n dot l)(n dot v) / 4(n dot l)(n dot v)\n\treturn 0.25;\n\n}\n\nfloat D_BlinnPhong( const in float shininess, const in float dotNH ) {\n\n\treturn RECIPROCAL_PI * ( shininess * 0.5 + 1.0 ) * pow( dotNH, shininess );\n\n}\n\nvec3 BRDF_Specular_BlinnPhong( const in IncidentLight incidentLight, const in GeometricContext geometry, const in vec3 specularColor, const in float shininess ) {\n\n\tvec3 halfDir = normalize( incidentLight.direction + geometry.viewDir );\n\n\t//float dotNL = saturate( dot( geometry.normal, incidentLight.direction ) );\n\t//float dotNV = saturate( dot( geometry.normal, geometry.viewDir ) );\n\tfloat dotNH = saturate( dot( geometry.normal, halfDir ) );\n\tfloat dotLH = saturate( dot( incidentLight.direction, halfDir ) );\n\n\tvec3 F = F_Schlick( specularColor, dotLH );\n\n\tfloat G = G_BlinnPhong_Implicit( /* dotNL, dotNV */ );\n\n\tfloat D = D_BlinnPhong( shininess, dotNH );\n\n\treturn F * ( G * D );\n\n} // validated\n\n// source: http://simonstechblog.blogspot.ca/2011/12/microfacet-brdf.html\nfloat GGXRoughnessToBlinnExponent( const in float ggxRoughness ) {\n\treturn ( 2.0 / pow2( ggxRoughness + 0.0001 ) - 2.0 );\n}\n\nfloat BlinnExponentToGGXRoughness( const in float blinnExponent ) {\n\treturn sqrt( 2.0 / ( blinnExponent + 2.0 ) );\n}\n'
}, function(e, t) {
    e.exports = "#ifdef USE_BUMPMAP\n\n\tuniform sampler2D bumpMap;\n\tuniform float bumpScale;\n\n\t// Bump Mapping Unparametrized Surfaces on the GPU by Morten S. Mikkelsen\n\t// http://api.unrealengine.com/attachments/Engine/Rendering/LightingAndShadows/BumpMappingWithoutTangentSpace/mm_sfgrad_bump.pdf\n\n\t// Evaluate the derivative of the height w.r.t. screen-space using forward differencing (listing 2)\n\n\tvec2 dHdxy_fwd() {\n\n\t\tvec2 dSTdx = dFdx( vUv );\n\t\tvec2 dSTdy = dFdy( vUv );\n\n\t\tfloat Hll = bumpScale * texture2D( bumpMap, vUv ).x;\n\t\tfloat dBx = bumpScale * texture2D( bumpMap, vUv + dSTdx ).x - Hll;\n\t\tfloat dBy = bumpScale * texture2D( bumpMap, vUv + dSTdy ).x - Hll;\n\n\t\treturn vec2( dBx, dBy );\n\n\t}\n\n\tvec3 perturbNormalArb( vec3 surf_pos, vec3 surf_norm, vec2 dHdxy ) {\n\n\t\t// Workaround for Adreno 3XX dFd*( vec3 ) bug. See #9988\n\n\t\tvec3 vSigmaX = vec3( dFdx( surf_pos.x ), dFdx( surf_pos.y ), dFdx( surf_pos.z ) );\n\t\tvec3 vSigmaY = vec3( dFdy( surf_pos.x ), dFdy( surf_pos.y ), dFdy( surf_pos.z ) );\n\t\tvec3 vN = surf_norm;\t\t// normalized\n\n\t\tvec3 R1 = cross( vSigmaY, vN );\n\t\tvec3 R2 = cross( vN, vSigmaX );\n\n\t\tfloat fDet = dot( vSigmaX, R1 );\n\n\t\tfDet *= ( float( gl_FrontFacing ) * 2.0 - 1.0 );\n\n\t\tvec3 vGrad = sign( fDet ) * ( dHdxy.x * R1 + dHdxy.y * R2 );\n\t\treturn normalize( abs( fDet ) * surf_norm - vGrad );\n\n\t}\n\n#endif\n"
}, function(e, t) {
    e.exports = "#if NUM_CLIPPING_PLANES > 0\n\n\tvec4 plane;\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < UNION_CLIPPING_PLANES; i ++ ) {\n\n\t\tplane = clippingPlanes[ i ];\n\t\tif ( dot( vViewPosition, plane.xyz ) > plane.w ) discard;\n\n\t}\n\n\t#if UNION_CLIPPING_PLANES < NUM_CLIPPING_PLANES\n\n\t\tbool clipped = true;\n\n\t\t#pragma unroll_loop\n\t\tfor ( int i = UNION_CLIPPING_PLANES; i < NUM_CLIPPING_PLANES; i ++ ) {\n\n\t\t\tplane = clippingPlanes[ i ];\n\t\t\tclipped = ( dot( vViewPosition, plane.xyz ) > plane.w ) && clipped;\n\n\t\t}\n\n\t\tif ( clipped ) discard;\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#if NUM_CLIPPING_PLANES > 0\n\n\t#if ! defined( PHYSICAL ) && ! defined( PHONG ) && ! defined( MATCAP )\n\t\tvarying vec3 vViewPosition;\n\t#endif\n\n\tuniform vec4 clippingPlanes[ NUM_CLIPPING_PLANES ];\n\n#endif\n"
}, function(e, t) {
    e.exports = "#if NUM_CLIPPING_PLANES > 0 && ! defined( PHYSICAL ) && ! defined( PHONG ) && ! defined( MATCAP )\n\tvarying vec3 vViewPosition;\n#endif\n"
}, function(e, t) {
    e.exports = "#if NUM_CLIPPING_PLANES > 0 && ! defined( PHYSICAL ) && ! defined( PHONG ) && ! defined( MATCAP )\n\tvViewPosition = - mvPosition.xyz;\n#endif\n\n"
}, function(e, t) {
    e.exports = "#ifdef USE_COLOR\n\n\tdiffuseColor.rgb *= vColor;\n\n#endif"
}, function(e, t) {
    e.exports = "#ifdef USE_COLOR\n\n\tvarying vec3 vColor;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_COLOR\n\n\tvarying vec3 vColor;\n\n#endif"
}, function(e, t) {
    e.exports = "#ifdef USE_COLOR\n\n\tvColor.xyz = color.xyz;\n\n#endif"
}, function(e, t) {
    e.exports = "#define PI 3.14159265359\n#define PI2 6.28318530718\n#define PI_HALF 1.5707963267949\n#define RECIPROCAL_PI 0.31830988618\n#define RECIPROCAL_PI2 0.15915494\n#define LOG2 1.442695\n#define EPSILON 1e-6\n\n#define saturate(a) clamp( a, 0.0, 1.0 )\n#define whiteCompliment(a) ( 1.0 - saturate( a ) )\n\nfloat pow2( const in float x ) { return x*x; }\nfloat pow3( const in float x ) { return x*x*x; }\nfloat pow4( const in float x ) { float x2 = x*x; return x2*x2; }\nfloat average( const in vec3 color ) { return dot( color, vec3( 0.3333 ) ); }\n// expects values in the range of [0,1]x[0,1], returns values in the [0,1] range.\n// do not collapse into a single function per: http://byteblacksmith.com/improvements-to-the-canonical-one-liner-glsl-rand-for-opengl-es-2-0/\nhighp float rand( const in vec2 uv ) {\n\tconst highp float a = 12.9898, b = 78.233, c = 43758.5453;\n\thighp float dt = dot( uv.xy, vec2( a,b ) ), sn = mod( dt, PI );\n\treturn fract(sin(sn) * c);\n}\n\nstruct IncidentLight {\n\tvec3 color;\n\tvec3 direction;\n\tbool visible;\n};\n\nstruct ReflectedLight {\n\tvec3 directDiffuse;\n\tvec3 directSpecular;\n\tvec3 indirectDiffuse;\n\tvec3 indirectSpecular;\n};\n\nstruct GeometricContext {\n\tvec3 position;\n\tvec3 normal;\n\tvec3 viewDir;\n};\n\nvec3 transformDirection( in vec3 dir, in mat4 matrix ) {\n\n\treturn normalize( ( matrix * vec4( dir, 0.0 ) ).xyz );\n\n}\n\n// http://en.wikibooks.org/wiki/GLSL_Programming/Applying_Matrix_Transformations\nvec3 inverseTransformDirection( in vec3 dir, in mat4 matrix ) {\n\n\treturn normalize( ( vec4( dir, 0.0 ) * matrix ).xyz );\n\n}\n\nvec3 projectOnPlane(in vec3 point, in vec3 pointOnPlane, in vec3 planeNormal ) {\n\n\tfloat distance = dot( planeNormal, point - pointOnPlane );\n\n\treturn - distance * planeNormal + point;\n\n}\n\nfloat sideOfPlane( in vec3 point, in vec3 pointOnPlane, in vec3 planeNormal ) {\n\n\treturn sign( dot( point - pointOnPlane, planeNormal ) );\n\n}\n\nvec3 linePlaneIntersect( in vec3 pointOnLine, in vec3 lineDirection, in vec3 pointOnPlane, in vec3 planeNormal ) {\n\n\treturn lineDirection * ( dot( planeNormal, pointOnPlane - pointOnLine ) / dot( planeNormal, lineDirection ) ) + pointOnLine;\n\n}\n\nmat3 transposeMat3( const in mat3 m ) {\n\n\tmat3 tmp;\n\n\ttmp[ 0 ] = vec3( m[ 0 ].x, m[ 1 ].x, m[ 2 ].x );\n\ttmp[ 1 ] = vec3( m[ 0 ].y, m[ 1 ].y, m[ 2 ].y );\n\ttmp[ 2 ] = vec3( m[ 0 ].z, m[ 1 ].z, m[ 2 ].z );\n\n\treturn tmp;\n\n}\n\n// https://en.wikipedia.org/wiki/Relative_luminance\nfloat linearToRelativeLuminance( const in vec3 color ) {\n\n\tvec3 weights = vec3( 0.2126, 0.7152, 0.0722 );\n\n\treturn dot( weights, color.rgb );\n\n}\n"
}, function(e, t) {
    e.exports = "#ifdef ENVMAP_TYPE_CUBE_UV\n\n#define cubeUV_textureSize (1024.0)\n\nint getFaceFromDirection(vec3 direction) {\n\tvec3 absDirection = abs(direction);\n\tint face = -1;\n\tif( absDirection.x > absDirection.z ) {\n\t\tif(absDirection.x > absDirection.y )\n\t\t\tface = direction.x > 0.0 ? 0 : 3;\n\t\telse\n\t\t\tface = direction.y > 0.0 ? 1 : 4;\n\t}\n\telse {\n\t\tif(absDirection.z > absDirection.y )\n\t\t\tface = direction.z > 0.0 ? 2 : 5;\n\t\telse\n\t\t\tface = direction.y > 0.0 ? 1 : 4;\n\t}\n\treturn face;\n}\n#define cubeUV_maxLods1  (log2(cubeUV_textureSize*0.25) - 1.0)\n#define cubeUV_rangeClamp (exp2((6.0 - 1.0) * 2.0))\n\nvec2 MipLevelInfo( vec3 vec, float roughnessLevel, float roughness ) {\n\tfloat scale = exp2(cubeUV_maxLods1 - roughnessLevel);\n\tfloat dxRoughness = dFdx(roughness);\n\tfloat dyRoughness = dFdy(roughness);\n\tvec3 dx = dFdx( vec * scale * dxRoughness );\n\tvec3 dy = dFdy( vec * scale * dyRoughness );\n\tfloat d = max( dot( dx, dx ), dot( dy, dy ) );\n\t// Clamp the value to the max mip level counts. hard coded to 6 mips\n\td = clamp(d, 1.0, cubeUV_rangeClamp);\n\tfloat mipLevel = 0.5 * log2(d);\n\treturn vec2(floor(mipLevel), fract(mipLevel));\n}\n\n#define cubeUV_maxLods2 (log2(cubeUV_textureSize*0.25) - 2.0)\n#define cubeUV_rcpTextureSize (1.0 / cubeUV_textureSize)\n\nvec2 getCubeUV(vec3 direction, float roughnessLevel, float mipLevel) {\n\tmipLevel = roughnessLevel > cubeUV_maxLods2 - 3.0 ? 0.0 : mipLevel;\n\tfloat a = 16.0 * cubeUV_rcpTextureSize;\n\n\tvec2 exp2_packed = exp2( vec2( roughnessLevel, mipLevel ) );\n\tvec2 rcp_exp2_packed = vec2( 1.0 ) / exp2_packed;\n\t// float powScale = exp2(roughnessLevel + mipLevel);\n\tfloat powScale = exp2_packed.x * exp2_packed.y;\n\t// float scale =  1.0 / exp2(roughnessLevel + 2.0 + mipLevel);\n\tfloat scale = rcp_exp2_packed.x * rcp_exp2_packed.y * 0.25;\n\t// float mipOffset = 0.75*(1.0 - 1.0/exp2(mipLevel))/exp2(roughnessLevel);\n\tfloat mipOffset = 0.75*(1.0 - rcp_exp2_packed.y) * rcp_exp2_packed.x;\n\n\tbool bRes = mipLevel == 0.0;\n\tscale =  bRes && (scale < a) ? a : scale;\n\n\tvec3 r;\n\tvec2 offset;\n\tint face = getFaceFromDirection(direction);\n\n\tfloat rcpPowScale = 1.0 / powScale;\n\n\tif( face == 0) {\n\t\tr = vec3(direction.x, -direction.z, direction.y);\n\t\toffset = vec2(0.0+mipOffset,0.75 * rcpPowScale);\n\t\toffset.y = bRes && (offset.y < 2.0*a) ? a : offset.y;\n\t}\n\telse if( face == 1) {\n\t\tr = vec3(direction.y, direction.x, direction.z);\n\t\toffset = vec2(scale+mipOffset, 0.75 * rcpPowScale);\n\t\toffset.y = bRes && (offset.y < 2.0*a) ? a : offset.y;\n\t}\n\telse if( face == 2) {\n\t\tr = vec3(direction.z, direction.x, direction.y);\n\t\toffset = vec2(2.0*scale+mipOffset, 0.75 * rcpPowScale);\n\t\toffset.y = bRes && (offset.y < 2.0*a) ? a : offset.y;\n\t}\n\telse if( face == 3) {\n\t\tr = vec3(direction.x, direction.z, direction.y);\n\t\toffset = vec2(0.0+mipOffset,0.5 * rcpPowScale);\n\t\toffset.y = bRes && (offset.y < 2.0*a) ? 0.0 : offset.y;\n\t}\n\telse if( face == 4) {\n\t\tr = vec3(direction.y, direction.x, -direction.z);\n\t\toffset = vec2(scale+mipOffset, 0.5 * rcpPowScale);\n\t\toffset.y = bRes && (offset.y < 2.0*a) ? 0.0 : offset.y;\n\t}\n\telse {\n\t\tr = vec3(direction.z, -direction.x, direction.y);\n\t\toffset = vec2(2.0*scale+mipOffset, 0.5 * rcpPowScale);\n\t\toffset.y = bRes && (offset.y < 2.0*a) ? 0.0 : offset.y;\n\t}\n\tr = normalize(r);\n\tfloat texelOffset = 0.5 * cubeUV_rcpTextureSize;\n\tvec2 s = ( r.yz / abs( r.x ) + vec2( 1.0 ) ) * 0.5;\n\tvec2 base = offset + vec2( texelOffset );\n\treturn base + s * ( scale - 2.0 * texelOffset );\n}\n\n#define cubeUV_maxLods3 (log2(cubeUV_textureSize*0.25) - 3.0)\n\nvec4 textureCubeUV( sampler2D envMap, vec3 reflectedDirection, float roughness ) {\n\tfloat roughnessVal = roughness* cubeUV_maxLods3;\n\tfloat r1 = floor(roughnessVal);\n\tfloat r2 = r1 + 1.0;\n\tfloat t = fract(roughnessVal);\n\tvec2 mipInfo = MipLevelInfo(reflectedDirection, r1, roughness);\n\tfloat s = mipInfo.y;\n\tfloat level0 = mipInfo.x;\n\tfloat level1 = level0 + 1.0;\n\tlevel1 = level1 > 5.0 ? 5.0 : level1;\n\n\t// round to nearest mipmap if we are not interpolating.\n\tlevel0 += min( floor( s + 0.5 ), 5.0 );\n\n\t// Tri linear interpolation.\n\tvec2 uv_10 = getCubeUV(reflectedDirection, r1, level0);\n\tvec4 color10 = envMapTexelToLinear(texture2D(envMap, uv_10));\n\n\tvec2 uv_20 = getCubeUV(reflectedDirection, r2, level0);\n\tvec4 color20 = envMapTexelToLinear(texture2D(envMap, uv_20));\n\n\tvec4 result = mix(color10, color20, t);\n\n\treturn vec4(result.rgb, 1.0);\n}\n\n#endif\n"
}, function(e, t) {
    e.exports = "vec3 transformedNormal = normalMatrix * objectNormal;\n\n#ifdef FLIP_SIDED\n\n\ttransformedNormal = - transformedNormal;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_DISPLACEMENTMAP\n\n\tuniform sampler2D displacementMap;\n\tuniform float displacementScale;\n\tuniform float displacementBias;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_DISPLACEMENTMAP\n\n\ttransformed += normalize( objectNormal ) * ( texture2D( displacementMap, uv ).x * displacementScale + displacementBias );\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_EMISSIVEMAP\n\n\tvec4 emissiveColor = texture2D( emissiveMap, vUv );\n\n\temissiveColor.rgb = emissiveMapTexelToLinear( emissiveColor ).rgb;\n\n\ttotalEmissiveRadiance *= emissiveColor.rgb;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_EMISSIVEMAP\n\n\tuniform sampler2D emissiveMap;\n\n#endif\n"
}, function(e, t) {
    e.exports = "  gl_FragColor = linearToOutputTexel( gl_FragColor );\n"
}, function(e, t) {
    e.exports = "// For a discussion of what this is, please read this: http://lousodrome.net/blog/light/2013/05/26/gamma-correct-and-hdr-rendering-in-a-32-bits-buffer/\n\nvec4 LinearToLinear( in vec4 value ) {\n\treturn value;\n}\n\nvec4 GammaToLinear( in vec4 value, in float gammaFactor ) {\n\treturn vec4( pow( value.rgb, vec3( gammaFactor ) ), value.a );\n}\n\nvec4 LinearToGamma( in vec4 value, in float gammaFactor ) {\n\treturn vec4( pow( value.rgb, vec3( 1.0 / gammaFactor ) ), value.a );\n}\n\nvec4 sRGBToLinear( in vec4 value ) {\n\treturn vec4( mix( pow( value.rgb * 0.9478672986 + vec3( 0.0521327014 ), vec3( 2.4 ) ), value.rgb * 0.0773993808, vec3( lessThanEqual( value.rgb, vec3( 0.04045 ) ) ) ), value.a );\n}\n\nvec4 LinearTosRGB( in vec4 value ) {\n\treturn vec4( mix( pow( value.rgb, vec3( 0.41666 ) ) * 1.055 - vec3( 0.055 ), value.rgb * 12.92, vec3( lessThanEqual( value.rgb, vec3( 0.0031308 ) ) ) ), value.a );\n}\n\nvec4 RGBEToLinear( in vec4 value ) {\n\treturn vec4( value.rgb * exp2( value.a * 255.0 - 128.0 ), 1.0 );\n}\n\nvec4 LinearToRGBE( in vec4 value ) {\n\tfloat maxComponent = max( max( value.r, value.g ), value.b );\n\tfloat fExp = clamp( ceil( log2( maxComponent ) ), -128.0, 127.0 );\n\treturn vec4( value.rgb / exp2( fExp ), ( fExp + 128.0 ) / 255.0 );\n//  return vec4( value.brg, ( 3.0 + 128.0 ) / 256.0 );\n}\n\n// reference: http://iwasbeingirony.blogspot.ca/2010/06/difference-between-rgbm-and-rgbd.html\nvec4 RGBMToLinear( in vec4 value, in float maxRange ) {\n\treturn vec4( value.rgb * value.a * maxRange, 1.0 );\n}\n\nvec4 LinearToRGBM( in vec4 value, in float maxRange ) {\n\tfloat maxRGB = max( value.r, max( value.g, value.b ) );\n\tfloat M = clamp( maxRGB / maxRange, 0.0, 1.0 );\n\tM = ceil( M * 255.0 ) / 255.0;\n\treturn vec4( value.rgb / ( M * maxRange ), M );\n}\n\n// reference: http://iwasbeingirony.blogspot.ca/2010/06/difference-between-rgbm-and-rgbd.html\nvec4 RGBDToLinear( in vec4 value, in float maxRange ) {\n\treturn vec4( value.rgb * ( ( maxRange / 255.0 ) / value.a ), 1.0 );\n}\n\nvec4 LinearToRGBD( in vec4 value, in float maxRange ) {\n\tfloat maxRGB = max( value.r, max( value.g, value.b ) );\n\tfloat D = max( maxRange / maxRGB, 1.0 );\n\tD = min( floor( D ) / 255.0, 1.0 );\n\treturn vec4( value.rgb * ( D * ( 255.0 / maxRange ) ), D );\n}\n\n// LogLuv reference: http://graphicrants.blogspot.ca/2009/04/rgbm-color-encoding.html\n\n// M matrix, for encoding\nconst mat3 cLogLuvM = mat3( 0.2209, 0.3390, 0.4184, 0.1138, 0.6780, 0.7319, 0.0102, 0.1130, 0.2969 );\nvec4 LinearToLogLuv( in vec4 value )  {\n\tvec3 Xp_Y_XYZp = value.rgb * cLogLuvM;\n\tXp_Y_XYZp = max( Xp_Y_XYZp, vec3( 1e-6, 1e-6, 1e-6 ) );\n\tvec4 vResult;\n\tvResult.xy = Xp_Y_XYZp.xy / Xp_Y_XYZp.z;\n\tfloat Le = 2.0 * log2(Xp_Y_XYZp.y) + 127.0;\n\tvResult.w = fract( Le );\n\tvResult.z = ( Le - ( floor( vResult.w * 255.0 ) ) / 255.0 ) / 255.0;\n\treturn vResult;\n}\n\n// Inverse M matrix, for decoding\nconst mat3 cLogLuvInverseM = mat3( 6.0014, -2.7008, -1.7996, -1.3320, 3.1029, -5.7721, 0.3008, -1.0882, 5.6268 );\nvec4 LogLuvToLinear( in vec4 value ) {\n\tfloat Le = value.z * 255.0 + value.w;\n\tvec3 Xp_Y_XYZp;\n\tXp_Y_XYZp.y = exp2( ( Le - 127.0 ) / 2.0 );\n\tXp_Y_XYZp.z = Xp_Y_XYZp.y / value.y;\n\tXp_Y_XYZp.x = value.x * Xp_Y_XYZp.z;\n\tvec3 vRGB = Xp_Y_XYZp.rgb * cLogLuvInverseM;\n\treturn vec4( max( vRGB, 0.0 ), 1.0 );\n}\n"
}, function(e, t) {
    e.exports = "#ifdef USE_ENVMAP\n\n\t#if defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( PHONG )\n\n\t\tvec3 cameraToVertex = normalize( vWorldPosition - cameraPosition );\n\n\t\t// Transforming Normal Vectors with the Inverse Transformation\n\t\tvec3 worldNormal = inverseTransformDirection( normal, viewMatrix );\n\n\t\t#ifdef ENVMAP_MODE_REFLECTION\n\n\t\t\tvec3 reflectVec = reflect( cameraToVertex, worldNormal );\n\n\t\t#else\n\n\t\t\tvec3 reflectVec = refract( cameraToVertex, worldNormal, refractionRatio );\n\n\t\t#endif\n\n\t#else\n\n\t\tvec3 reflectVec = vReflect;\n\n\t#endif\n\n\t#ifdef ENVMAP_TYPE_CUBE\n\n\t\tvec4 envColor = textureCube( envMap, vec3( flipEnvMap * reflectVec.x, reflectVec.yz ) );\n\n\t#elif defined( ENVMAP_TYPE_EQUIREC )\n\n\t\tvec2 sampleUV;\n\n\t\treflectVec = normalize( reflectVec );\n\n\t\tsampleUV.y = asin( clamp( reflectVec.y, - 1.0, 1.0 ) ) * RECIPROCAL_PI + 0.5;\n\n\t\tsampleUV.x = atan( reflectVec.z, reflectVec.x ) * RECIPROCAL_PI2 + 0.5;\n\n\t\tvec4 envColor = texture2D( envMap, sampleUV );\n\n\t#elif defined( ENVMAP_TYPE_SPHERE )\n\n\t\treflectVec = normalize( reflectVec );\n\n\t\tvec3 reflectView = normalize( ( viewMatrix * vec4( reflectVec, 0.0 ) ).xyz + vec3( 0.0, 0.0, 1.0 ) );\n\n\t\tvec4 envColor = texture2D( envMap, reflectView.xy * 0.5 + 0.5 );\n\n\t#else\n\n\t\tvec4 envColor = vec4( 0.0 );\n\n\t#endif\n\n\tenvColor = envMapTexelToLinear( envColor );\n\n\t#ifdef ENVMAP_BLENDING_MULTIPLY\n\n\t\toutgoingLight = mix( outgoingLight, outgoingLight * envColor.xyz, specularStrength * reflectivity );\n\n\t#elif defined( ENVMAP_BLENDING_MIX )\n\n\t\toutgoingLight = mix( outgoingLight, envColor.xyz, specularStrength * reflectivity );\n\n\t#elif defined( ENVMAP_BLENDING_ADD )\n\n\t\toutgoingLight += envColor.xyz * specularStrength * reflectivity;\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#if defined( USE_ENVMAP ) || defined( PHYSICAL )\n\tuniform float reflectivity;\n\tuniform float envMapIntensity;\n#endif\n\n#ifdef USE_ENVMAP\n\n\t#if ! defined( PHYSICAL ) && ( defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( PHONG ) )\n\t\tvarying vec3 vWorldPosition;\n\t#endif\n\n\t#ifdef ENVMAP_TYPE_CUBE\n\t\tuniform samplerCube envMap;\n\t#else\n\t\tuniform sampler2D envMap;\n\t#endif\n\tuniform float flipEnvMap;\n\tuniform int maxMipLevel;\n\n\t#if defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( PHONG ) || defined( PHYSICAL )\n\t\tuniform float refractionRatio;\n\t#else\n\t\tvarying vec3 vReflect;\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_ENVMAP\n\n\t#if defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( PHONG )\n\t\tvarying vec3 vWorldPosition;\n\n\t#else\n\n\t\tvarying vec3 vReflect;\n\t\tuniform float refractionRatio;\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_ENVMAP\n\n\t#if defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( PHONG )\n\n\t\tvWorldPosition = worldPosition.xyz;\n\n\t#else\n\n\t\tvec3 cameraToVertex = normalize( worldPosition.xyz - cameraPosition );\n\n\t\tvec3 worldNormal = inverseTransformDirection( transformedNormal, viewMatrix );\n\n\t\t#ifdef ENVMAP_MODE_REFLECTION\n\n\t\t\tvReflect = reflect( cameraToVertex, worldNormal );\n\n\t\t#else\n\n\t\t\tvReflect = refract( cameraToVertex, worldNormal, refractionRatio );\n\n\t\t#endif\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_FOG\n\n\tfogDepth = -mvPosition.z;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_FOG\n\n\tvarying float fogDepth;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_FOG\n\n\t#ifdef FOG_EXP2\n\n\t\tfloat fogFactor = whiteCompliment( exp2( - fogDensity * fogDensity * fogDepth * fogDepth * LOG2 ) );\n\n\t#else\n\n\t\tfloat fogFactor = smoothstep( fogNear, fogFar, fogDepth );\n\n\t#endif\n\n\tgl_FragColor.rgb = mix( gl_FragColor.rgb, fogColor, fogFactor );\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_FOG\n\n\tuniform vec3 fogColor;\n\tvarying float fogDepth;\n\n\t#ifdef FOG_EXP2\n\n\t\tuniform float fogDensity;\n\n\t#else\n\n\t\tuniform float fogNear;\n\t\tuniform float fogFar;\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef TOON\n\n\tuniform sampler2D gradientMap;\n\n\tvec3 getGradientIrradiance( vec3 normal, vec3 lightDirection ) {\n\n\t\t// dotNL will be from -1.0 to 1.0\n\t\tfloat dotNL = dot( normal, lightDirection );\n\t\tvec2 coord = vec2( dotNL * 0.5 + 0.5, 0.0 );\n\n\t\t#ifdef USE_GRADIENTMAP\n\n\t\t\treturn texture2D( gradientMap, coord ).rgb;\n\n\t\t#else\n\n\t\t\treturn ( coord.x < 0.7 ) ? vec3( 0.7 ) : vec3( 1.0 );\n\n\t\t#endif\n\n\n\t}\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_LIGHTMAP\n\n\treflectedLight.indirectDiffuse += PI * texture2D( lightMap, vUv2 ).xyz * lightMapIntensity; // factor of PI should not be present; included here to prevent breakage\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_LIGHTMAP\n\n\tuniform sampler2D lightMap;\n\tuniform float lightMapIntensity;\n\n#endif"
}, function(e, t) {
    e.exports = "vec3 diffuse = vec3( 1.0 );\n\nGeometricContext geometry;\ngeometry.position = mvPosition.xyz;\ngeometry.normal = normalize( transformedNormal );\ngeometry.viewDir = normalize( -mvPosition.xyz );\n\nGeometricContext backGeometry;\nbackGeometry.position = geometry.position;\nbackGeometry.normal = -geometry.normal;\nbackGeometry.viewDir = geometry.viewDir;\n\nvLightFront = vec3( 0.0 );\n\n#ifdef DOUBLE_SIDED\n\tvLightBack = vec3( 0.0 );\n#endif\n\nIncidentLight directLight;\nfloat dotNL;\nvec3 directLightColor_Diffuse;\n\n#if NUM_POINT_LIGHTS > 0\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_POINT_LIGHTS; i ++ ) {\n\n\t\tgetPointDirectLightIrradiance( pointLights[ i ], geometry, directLight );\n\n\t\tdotNL = dot( geometry.normal, directLight.direction );\n\t\tdirectLightColor_Diffuse = PI * directLight.color;\n\n\t\tvLightFront += saturate( dotNL ) * directLightColor_Diffuse;\n\n\t\t#ifdef DOUBLE_SIDED\n\n\t\t\tvLightBack += saturate( -dotNL ) * directLightColor_Diffuse;\n\n\t\t#endif\n\n\t}\n\n#endif\n\n#if NUM_SPOT_LIGHTS > 0\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_SPOT_LIGHTS; i ++ ) {\n\n\t\tgetSpotDirectLightIrradiance( spotLights[ i ], geometry, directLight );\n\n\t\tdotNL = dot( geometry.normal, directLight.direction );\n\t\tdirectLightColor_Diffuse = PI * directLight.color;\n\n\t\tvLightFront += saturate( dotNL ) * directLightColor_Diffuse;\n\n\t\t#ifdef DOUBLE_SIDED\n\n\t\t\tvLightBack += saturate( -dotNL ) * directLightColor_Diffuse;\n\n\t\t#endif\n\t}\n\n#endif\n\n/*\n#if NUM_RECT_AREA_LIGHTS > 0\n\n\tfor ( int i = 0; i < NUM_RECT_AREA_LIGHTS; i ++ ) {\n\n\t\t// TODO (abelnation): implement\n\n\t}\n\n#endif\n*/\n\n#if NUM_DIR_LIGHTS > 0\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_DIR_LIGHTS; i ++ ) {\n\n\t\tgetDirectionalDirectLightIrradiance( directionalLights[ i ], geometry, directLight );\n\n\t\tdotNL = dot( geometry.normal, directLight.direction );\n\t\tdirectLightColor_Diffuse = PI * directLight.color;\n\n\t\tvLightFront += saturate( dotNL ) * directLightColor_Diffuse;\n\n\t\t#ifdef DOUBLE_SIDED\n\n\t\t\tvLightBack += saturate( -dotNL ) * directLightColor_Diffuse;\n\n\t\t#endif\n\n\t}\n\n#endif\n\n#if NUM_HEMI_LIGHTS > 0\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_HEMI_LIGHTS; i ++ ) {\n\n\t\tvLightFront += getHemisphereLightIrradiance( hemisphereLights[ i ], geometry );\n\n\t\t#ifdef DOUBLE_SIDED\n\n\t\t\tvLightBack += getHemisphereLightIrradiance( hemisphereLights[ i ], backGeometry );\n\n\t\t#endif\n\n\t}\n\n#endif\n"
}, function(e, t) {
    e.exports = "uniform vec3 ambientLightColor;\n\nvec3 getAmbientLightIrradiance( const in vec3 ambientLightColor ) {\n\n\tvec3 irradiance = ambientLightColor;\n\n\t#ifndef PHYSICALLY_CORRECT_LIGHTS\n\n\t\tirradiance *= PI;\n\n\t#endif\n\n\treturn irradiance;\n\n}\n\n#if NUM_DIR_LIGHTS > 0\n\n\tstruct DirectionalLight {\n\t\tvec3 direction;\n\t\tvec3 color;\n\n\t\tint shadow;\n\t\tfloat shadowBias;\n\t\tfloat shadowRadius;\n\t\tvec2 shadowMapSize;\n\t};\n\n\tuniform DirectionalLight directionalLights[ NUM_DIR_LIGHTS ];\n\n\tvoid getDirectionalDirectLightIrradiance( const in DirectionalLight directionalLight, const in GeometricContext geometry, out IncidentLight directLight ) {\n\n\t\tdirectLight.color = directionalLight.color;\n\t\tdirectLight.direction = directionalLight.direction;\n\t\tdirectLight.visible = true;\n\n\t}\n\n#endif\n\n\n#if NUM_POINT_LIGHTS > 0\n\n\tstruct PointLight {\n\t\tvec3 position;\n\t\tvec3 color;\n\t\tfloat distance;\n\t\tfloat decay;\n\n\t\tint shadow;\n\t\tfloat shadowBias;\n\t\tfloat shadowRadius;\n\t\tvec2 shadowMapSize;\n\t\tfloat shadowCameraNear;\n\t\tfloat shadowCameraFar;\n\t};\n\n\tuniform PointLight pointLights[ NUM_POINT_LIGHTS ];\n\n\t// directLight is an out parameter as having it as a return value caused compiler errors on some devices\n\tvoid getPointDirectLightIrradiance( const in PointLight pointLight, const in GeometricContext geometry, out IncidentLight directLight ) {\n\n\t\tvec3 lVector = pointLight.position - geometry.position;\n\t\tdirectLight.direction = normalize( lVector );\n\n\t\tfloat lightDistance = length( lVector );\n\n\t\tdirectLight.color = pointLight.color;\n\t\tdirectLight.color *= punctualLightIntensityToIrradianceFactor( lightDistance, pointLight.distance, pointLight.decay );\n\t\tdirectLight.visible = ( directLight.color != vec3( 0.0 ) );\n\n\t}\n\n#endif\n\n\n#if NUM_SPOT_LIGHTS > 0\n\n\tstruct SpotLight {\n\t\tvec3 position;\n\t\tvec3 direction;\n\t\tvec3 color;\n\t\tfloat distance;\n\t\tfloat decay;\n\t\tfloat coneCos;\n\t\tfloat penumbraCos;\n\n\t\tint shadow;\n\t\tfloat shadowBias;\n\t\tfloat shadowRadius;\n\t\tvec2 shadowMapSize;\n\t};\n\n\tuniform SpotLight spotLights[ NUM_SPOT_LIGHTS ];\n\n\t// directLight is an out parameter as having it as a return value caused compiler errors on some devices\n\tvoid getSpotDirectLightIrradiance( const in SpotLight spotLight, const in GeometricContext geometry, out IncidentLight directLight  ) {\n\n\t\tvec3 lVector = spotLight.position - geometry.position;\n\t\tdirectLight.direction = normalize( lVector );\n\n\t\tfloat lightDistance = length( lVector );\n\t\tfloat angleCos = dot( directLight.direction, spotLight.direction );\n\n\t\tif ( angleCos > spotLight.coneCos ) {\n\n\t\t\tfloat spotEffect = smoothstep( spotLight.coneCos, spotLight.penumbraCos, angleCos );\n\n\t\t\tdirectLight.color = spotLight.color;\n\t\t\tdirectLight.color *= spotEffect * punctualLightIntensityToIrradianceFactor( lightDistance, spotLight.distance, spotLight.decay );\n\t\t\tdirectLight.visible = true;\n\n\t\t} else {\n\n\t\t\tdirectLight.color = vec3( 0.0 );\n\t\t\tdirectLight.visible = false;\n\n\t\t}\n\t}\n\n#endif\n\n\n#if NUM_RECT_AREA_LIGHTS > 0\n\n\tstruct RectAreaLight {\n\t\tvec3 color;\n\t\tvec3 position;\n\t\tvec3 halfWidth;\n\t\tvec3 halfHeight;\n\t};\n\n\t// Pre-computed values of LinearTransformedCosine approximation of BRDF\n\t// BRDF approximation Texture is 64x64\n\tuniform sampler2D ltc_1; // RGBA Float\n\tuniform sampler2D ltc_2; // RGBA Float\n\n\tuniform RectAreaLight rectAreaLights[ NUM_RECT_AREA_LIGHTS ];\n\n#endif\n\n\n#if NUM_HEMI_LIGHTS > 0\n\n\tstruct HemisphereLight {\n\t\tvec3 direction;\n\t\tvec3 skyColor;\n\t\tvec3 groundColor;\n\t};\n\n\tuniform HemisphereLight hemisphereLights[ NUM_HEMI_LIGHTS ];\n\n\tvec3 getHemisphereLightIrradiance( const in HemisphereLight hemiLight, const in GeometricContext geometry ) {\n\n\t\tfloat dotNL = dot( geometry.normal, hemiLight.direction );\n\t\tfloat hemiDiffuseWeight = 0.5 * dotNL + 0.5;\n\n\t\tvec3 irradiance = mix( hemiLight.groundColor, hemiLight.skyColor, hemiDiffuseWeight );\n\n\t\t#ifndef PHYSICALLY_CORRECT_LIGHTS\n\n\t\t\tirradiance *= PI;\n\n\t\t#endif\n\n\t\treturn irradiance;\n\n\t}\n\n#endif\n"
}, function(e, t) {
    e.exports = "#if defined( USE_ENVMAP ) && defined( PHYSICAL )\n\n\tvec3 getLightProbeIndirectIrradiance( /*const in SpecularLightProbe specularLightProbe,*/ const in GeometricContext geometry, const in int maxMIPLevel ) {\n\n\t\tvec3 worldNormal = inverseTransformDirection( geometry.normal, viewMatrix );\n\n\t\t#ifdef ENVMAP_TYPE_CUBE\n\n\t\t\tvec3 queryVec = vec3( flipEnvMap * worldNormal.x, worldNormal.yz );\n\n\t\t\t// TODO: replace with properly filtered cubemaps and access the irradiance LOD level, be it the last LOD level\n\t\t\t// of a specular cubemap, or just the default level of a specially created irradiance cubemap.\n\n\t\t\t#ifdef TEXTURE_LOD_EXT\n\n\t\t\t\tvec4 envMapColor = textureCubeLodEXT( envMap, queryVec, float( maxMIPLevel ) );\n\n\t\t\t#else\n\n\t\t\t\t// force the bias high to get the last LOD level as it is the most blurred.\n\t\t\t\tvec4 envMapColor = textureCube( envMap, queryVec, float( maxMIPLevel ) );\n\n\t\t\t#endif\n\n\t\t\tenvMapColor.rgb = envMapTexelToLinear( envMapColor ).rgb;\n\n\t\t#elif defined( ENVMAP_TYPE_CUBE_UV )\n\n\t\t\tvec3 queryVec = vec3( flipEnvMap * worldNormal.x, worldNormal.yz );\n\t\t\tvec4 envMapColor = textureCubeUV( envMap, queryVec, 1.0 );\n\n\t\t#else\n\n\t\t\tvec4 envMapColor = vec4( 0.0 );\n\n\t\t#endif\n\n\t\treturn PI * envMapColor.rgb * envMapIntensity;\n\n\t}\n\n\t// taken from here: http://casual-effects.blogspot.ca/2011/08/plausible-environment-lighting-in-two.html\n\tfloat getSpecularMIPLevel( const in float blinnShininessExponent, const in int maxMIPLevel ) {\n\n\t\t//float envMapWidth = pow( 2.0, maxMIPLevelScalar );\n\t\t//float desiredMIPLevel = log2( envMapWidth * sqrt( 3.0 ) ) - 0.5 * log2( pow2( blinnShininessExponent ) + 1.0 );\n\n\t\tfloat maxMIPLevelScalar = float( maxMIPLevel );\n\t\tfloat desiredMIPLevel = maxMIPLevelScalar + 0.79248 - 0.5 * log2( pow2( blinnShininessExponent ) + 1.0 );\n\n\t\t// clamp to allowable LOD ranges.\n\t\treturn clamp( desiredMIPLevel, 0.0, maxMIPLevelScalar );\n\n\t}\n\n\tvec3 getLightProbeIndirectRadiance( /*const in SpecularLightProbe specularLightProbe,*/ const in GeometricContext geometry, const in float blinnShininessExponent, const in int maxMIPLevel ) {\n\n\t\t#ifdef ENVMAP_MODE_REFLECTION\n\n\t\t\tvec3 reflectVec = reflect( -geometry.viewDir, geometry.normal );\n\n\t\t#else\n\n\t\t\tvec3 reflectVec = refract( -geometry.viewDir, geometry.normal, refractionRatio );\n\n\t\t#endif\n\n\t\treflectVec = inverseTransformDirection( reflectVec, viewMatrix );\n\n\t\tfloat specularMIPLevel = getSpecularMIPLevel( blinnShininessExponent, maxMIPLevel );\n\n\t\t#ifdef ENVMAP_TYPE_CUBE\n\n\t\t\tvec3 queryReflectVec = vec3( flipEnvMap * reflectVec.x, reflectVec.yz );\n\n\t\t\t#ifdef TEXTURE_LOD_EXT\n\n\t\t\t\tvec4 envMapColor = textureCubeLodEXT( envMap, queryReflectVec, specularMIPLevel );\n\n\t\t\t#else\n\n\t\t\t\tvec4 envMapColor = textureCube( envMap, queryReflectVec, specularMIPLevel );\n\n\t\t\t#endif\n\n\t\t\tenvMapColor.rgb = envMapTexelToLinear( envMapColor ).rgb;\n\n\t\t#elif defined( ENVMAP_TYPE_CUBE_UV )\n\n\t\t\tvec3 queryReflectVec = vec3( flipEnvMap * reflectVec.x, reflectVec.yz );\n\t\t\tvec4 envMapColor = textureCubeUV( envMap, queryReflectVec, BlinnExponentToGGXRoughness(blinnShininessExponent ));\n\n\t\t#elif defined( ENVMAP_TYPE_EQUIREC )\n\n\t\t\tvec2 sampleUV;\n\t\t\tsampleUV.y = asin( clamp( reflectVec.y, - 1.0, 1.0 ) ) * RECIPROCAL_PI + 0.5;\n\t\t\tsampleUV.x = atan( reflectVec.z, reflectVec.x ) * RECIPROCAL_PI2 + 0.5;\n\n\t\t\t#ifdef TEXTURE_LOD_EXT\n\n\t\t\t\tvec4 envMapColor = texture2DLodEXT( envMap, sampleUV, specularMIPLevel );\n\n\t\t\t#else\n\n\t\t\t\tvec4 envMapColor = texture2D( envMap, sampleUV, specularMIPLevel );\n\n\t\t\t#endif\n\n\t\t\tenvMapColor.rgb = envMapTexelToLinear( envMapColor ).rgb;\n\n\t\t#elif defined( ENVMAP_TYPE_SPHERE )\n\n\t\t\tvec3 reflectView = normalize( ( viewMatrix * vec4( reflectVec, 0.0 ) ).xyz + vec3( 0.0,0.0,1.0 ) );\n\n\t\t\t#ifdef TEXTURE_LOD_EXT\n\n\t\t\t\tvec4 envMapColor = texture2DLodEXT( envMap, reflectView.xy * 0.5 + 0.5, specularMIPLevel );\n\n\t\t\t#else\n\n\t\t\t\tvec4 envMapColor = texture2D( envMap, reflectView.xy * 0.5 + 0.5, specularMIPLevel );\n\n\t\t\t#endif\n\n\t\t\tenvMapColor.rgb = envMapTexelToLinear( envMapColor ).rgb;\n\n\t\t#endif\n\n\t\treturn envMapColor.rgb * envMapIntensity;\n\n\t}\n\n#endif\n"
}, function(e, t) {
    e.exports = "BlinnPhongMaterial material;\nmaterial.diffuseColor = diffuseColor.rgb;\nmaterial.specularColor = specular;\nmaterial.specularShininess = shininess;\nmaterial.specularStrength = specularStrength;\n"
}, function(e, t) {
    e.exports = "varying vec3 vViewPosition;\n\n#ifndef FLAT_SHADED\n\n\tvarying vec3 vNormal;\n\n#endif\n\n\nstruct BlinnPhongMaterial {\n\n\tvec3\tdiffuseColor;\n\tvec3\tspecularColor;\n\tfloat\tspecularShininess;\n\tfloat\tspecularStrength;\n\n};\n\nvoid RE_Direct_BlinnPhong( const in IncidentLight directLight, const in GeometricContext geometry, const in BlinnPhongMaterial material, inout ReflectedLight reflectedLight ) {\n\n\t#ifdef TOON\n\n\t\tvec3 irradiance = getGradientIrradiance( geometry.normal, directLight.direction ) * directLight.color;\n\n\t#else\n\n\t\tfloat dotNL = saturate( dot( geometry.normal, directLight.direction ) );\n\t\tvec3 irradiance = dotNL * directLight.color;\n\n\t#endif\n\n\t#ifndef PHYSICALLY_CORRECT_LIGHTS\n\n\t\tirradiance *= PI; // punctual light\n\n\t#endif\n\n\treflectedLight.directDiffuse += irradiance * BRDF_Diffuse_Lambert( material.diffuseColor );\n\n\treflectedLight.directSpecular += irradiance * BRDF_Specular_BlinnPhong( directLight, geometry, material.specularColor, material.specularShininess ) * material.specularStrength;\n\n}\n\nvoid RE_IndirectDiffuse_BlinnPhong( const in vec3 irradiance, const in GeometricContext geometry, const in BlinnPhongMaterial material, inout ReflectedLight reflectedLight ) {\n\n\treflectedLight.indirectDiffuse += irradiance * BRDF_Diffuse_Lambert( material.diffuseColor );\n\n}\n\n#define RE_Direct\t\t\t\tRE_Direct_BlinnPhong\n#define RE_IndirectDiffuse\t\tRE_IndirectDiffuse_BlinnPhong\n\n#define Material_LightProbeLOD( material )\t(0)\n"
}, function(e, t) {
    e.exports = "PhysicalMaterial material;\nmaterial.diffuseColor = diffuseColor.rgb * ( 1.0 - metalnessFactor );\nmaterial.specularRoughness = clamp( roughnessFactor, 0.04, 1.0 );\n#ifdef STANDARD\n\tmaterial.specularColor = mix( vec3( DEFAULT_SPECULAR_COEFFICIENT ), diffuseColor.rgb, metalnessFactor );\n#else\n\tmaterial.specularColor = mix( vec3( MAXIMUM_SPECULAR_COEFFICIENT * pow2( reflectivity ) ), diffuseColor.rgb, metalnessFactor );\n\tmaterial.clearCoat = saturate( clearCoat ); // Burley clearcoat model\n\tmaterial.clearCoatRoughness = clamp( clearCoatRoughness, 0.04, 1.0 );\n#endif\n"
}, function(e, t) {
    e.exports = "struct PhysicalMaterial {\n\n\tvec3\tdiffuseColor;\n\tfloat\tspecularRoughness;\n\tvec3\tspecularColor;\n\n\t#ifndef STANDARD\n\t\tfloat clearCoat;\n\t\tfloat clearCoatRoughness;\n\t#endif\n\n};\n\n#define MAXIMUM_SPECULAR_COEFFICIENT 0.16\n#define DEFAULT_SPECULAR_COEFFICIENT 0.04\n\n// Clear coat directional hemishperical reflectance (this approximation should be improved)\nfloat clearCoatDHRApprox( const in float roughness, const in float dotNL ) {\n\n\treturn DEFAULT_SPECULAR_COEFFICIENT + ( 1.0 - DEFAULT_SPECULAR_COEFFICIENT ) * ( pow( 1.0 - dotNL, 5.0 ) * pow( 1.0 - roughness, 2.0 ) );\n\n}\n\n#if NUM_RECT_AREA_LIGHTS > 0\n\n\tvoid RE_Direct_RectArea_Physical( const in RectAreaLight rectAreaLight, const in GeometricContext geometry, const in PhysicalMaterial material, inout ReflectedLight reflectedLight ) {\n\n\t\tvec3 normal = geometry.normal;\n\t\tvec3 viewDir = geometry.viewDir;\n\t\tvec3 position = geometry.position;\n\t\tvec3 lightPos = rectAreaLight.position;\n\t\tvec3 halfWidth = rectAreaLight.halfWidth;\n\t\tvec3 halfHeight = rectAreaLight.halfHeight;\n\t\tvec3 lightColor = rectAreaLight.color;\n\t\tfloat roughness = material.specularRoughness;\n\n\t\tvec3 rectCoords[ 4 ];\n\t\trectCoords[ 0 ] = lightPos - halfWidth - halfHeight; // counterclockwise\n\t\trectCoords[ 1 ] = lightPos + halfWidth - halfHeight;\n\t\trectCoords[ 2 ] = lightPos + halfWidth + halfHeight;\n\t\trectCoords[ 3 ] = lightPos - halfWidth + halfHeight;\n\n\t\tvec2 uv = LTC_Uv( normal, viewDir, roughness );\n\n\t\tvec4 t1 = texture2D( ltc_1, uv );\n\t\tvec4 t2 = texture2D( ltc_2, uv );\n\n\t\tmat3 mInv = mat3(\n\t\t\tvec3( t1.x, 0, t1.y ),\n\t\t\tvec3(    0, 1,    0 ),\n\t\t\tvec3( t1.z, 0, t1.w )\n\t\t);\n\n\t\t// LTC Fresnel Approximation by Stephen Hill\n\t\t// http://blog.selfshadow.com/publications/s2016-advances/s2016_ltc_fresnel.pdf\n\t\tvec3 fresnel = ( material.specularColor * t2.x + ( vec3( 1.0 ) - material.specularColor ) * t2.y );\n\n\t\treflectedLight.directSpecular += lightColor * fresnel * LTC_Evaluate( normal, viewDir, position, mInv, rectCoords );\n\n\t\treflectedLight.directDiffuse += lightColor * material.diffuseColor * LTC_Evaluate( normal, viewDir, position, mat3( 1.0 ), rectCoords );\n\n\t}\n\n#endif\n\nvoid RE_Direct_Physical( const in IncidentLight directLight, const in GeometricContext geometry, const in PhysicalMaterial material, inout ReflectedLight reflectedLight ) {\n\n\tfloat dotNL = saturate( dot( geometry.normal, directLight.direction ) );\n\n\tvec3 irradiance = dotNL * directLight.color;\n\n\t#ifndef PHYSICALLY_CORRECT_LIGHTS\n\n\t\tirradiance *= PI; // punctual light\n\n\t#endif\n\n\t#ifndef STANDARD\n\t\tfloat clearCoatDHR = material.clearCoat * clearCoatDHRApprox( material.clearCoatRoughness, dotNL );\n\t#else\n\t\tfloat clearCoatDHR = 0.0;\n\t#endif\n\n\treflectedLight.directSpecular += ( 1.0 - clearCoatDHR ) * irradiance * BRDF_Specular_GGX( directLight, geometry, material.specularColor, material.specularRoughness );\n\n\treflectedLight.directDiffuse += ( 1.0 - clearCoatDHR ) * irradiance * BRDF_Diffuse_Lambert( material.diffuseColor );\n\n\t#ifndef STANDARD\n\n\t\treflectedLight.directSpecular += irradiance * material.clearCoat * BRDF_Specular_GGX( directLight, geometry, vec3( DEFAULT_SPECULAR_COEFFICIENT ), material.clearCoatRoughness );\n\n\t#endif\n\n}\n\nvoid RE_IndirectDiffuse_Physical( const in vec3 irradiance, const in GeometricContext geometry, const in PhysicalMaterial material, inout ReflectedLight reflectedLight ) {\n\n\treflectedLight.indirectDiffuse += irradiance * BRDF_Diffuse_Lambert( material.diffuseColor );\n\n}\n\nvoid RE_IndirectSpecular_Physical( const in vec3 radiance, const in vec3 clearCoatRadiance, const in GeometricContext geometry, const in PhysicalMaterial material, inout ReflectedLight reflectedLight ) {\n\n\t#ifndef STANDARD\n\t\tfloat dotNV = saturate( dot( geometry.normal, geometry.viewDir ) );\n\t\tfloat dotNL = dotNV;\n\t\tfloat clearCoatDHR = material.clearCoat * clearCoatDHRApprox( material.clearCoatRoughness, dotNL );\n\t#else\n\t\tfloat clearCoatDHR = 0.0;\n\t#endif\n\n\treflectedLight.indirectSpecular += ( 1.0 - clearCoatDHR ) * radiance * BRDF_Specular_GGX_Environment( geometry, material.specularColor, material.specularRoughness );\n\n\t#ifndef STANDARD\n\n\t\treflectedLight.indirectSpecular += clearCoatRadiance * material.clearCoat * BRDF_Specular_GGX_Environment( geometry, vec3( DEFAULT_SPECULAR_COEFFICIENT ), material.clearCoatRoughness );\n\n\t#endif\n\n}\n\n#define RE_Direct\t\t\t\tRE_Direct_Physical\n#define RE_Direct_RectArea\t\tRE_Direct_RectArea_Physical\n#define RE_IndirectDiffuse\t\tRE_IndirectDiffuse_Physical\n#define RE_IndirectSpecular\t\tRE_IndirectSpecular_Physical\n\n#define Material_BlinnShininessExponent( material )   GGXRoughnessToBlinnExponent( material.specularRoughness )\n#define Material_ClearCoat_BlinnShininessExponent( material )   GGXRoughnessToBlinnExponent( material.clearCoatRoughness )\n\n// ref: https://seblagarde.files.wordpress.com/2015/07/course_notes_moving_frostbite_to_pbr_v32.pdf\nfloat computeSpecularOcclusion( const in float dotNV, const in float ambientOcclusion, const in float roughness ) {\n\n\treturn saturate( pow( dotNV + ambientOcclusion, exp2( - 16.0 * roughness - 1.0 ) ) - 1.0 + ambientOcclusion );\n\n}\n"
}, function(e, t) {
    e.exports = "/**\n * This is a template that can be used to light a material, it uses pluggable\n * RenderEquations (RE)for specific lighting scenarios.\n *\n * Instructions for use:\n * - Ensure that both RE_Direct, RE_IndirectDiffuse and RE_IndirectSpecular are defined\n * - If you have defined an RE_IndirectSpecular, you need to also provide a Material_LightProbeLOD. <---- ???\n * - Create a material parameter that is to be passed as the third parameter to your lighting functions.\n *\n * TODO:\n * - Add area light support.\n * - Add sphere light support.\n * - Add diffuse light probe (irradiance cubemap) support.\n */\n\nGeometricContext geometry;\n\ngeometry.position = - vViewPosition;\ngeometry.normal = normal;\ngeometry.viewDir = normalize( vViewPosition );\n\nIncidentLight directLight;\n\n#if ( NUM_POINT_LIGHTS > 0 ) && defined( RE_Direct )\n\n\tPointLight pointLight;\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_POINT_LIGHTS; i ++ ) {\n\n\t\tpointLight = pointLights[ i ];\n\n\t\tgetPointDirectLightIrradiance( pointLight, geometry, directLight );\n\n\t\t#ifdef USE_SHADOWMAP\n\t\tdirectLight.color *= all( bvec2( pointLight.shadow, directLight.visible ) ) ? getPointShadow( pointShadowMap[ i ], pointLight.shadowMapSize, pointLight.shadowBias, pointLight.shadowRadius, vPointShadowCoord[ i ], pointLight.shadowCameraNear, pointLight.shadowCameraFar ) : 1.0;\n\t\t#endif\n\n\t\tRE_Direct( directLight, geometry, material, reflectedLight );\n\n\t}\n\n#endif\n\n#if ( NUM_SPOT_LIGHTS > 0 ) && defined( RE_Direct )\n\n\tSpotLight spotLight;\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_SPOT_LIGHTS; i ++ ) {\n\n\t\tspotLight = spotLights[ i ];\n\n\t\tgetSpotDirectLightIrradiance( spotLight, geometry, directLight );\n\n\t\t#ifdef USE_SHADOWMAP\n\t\tdirectLight.color *= all( bvec2( spotLight.shadow, directLight.visible ) ) ? getShadow( spotShadowMap[ i ], spotLight.shadowMapSize, spotLight.shadowBias, spotLight.shadowRadius, vSpotShadowCoord[ i ] ) : 1.0;\n\t\t#endif\n\n\t\tRE_Direct( directLight, geometry, material, reflectedLight );\n\n\t}\n\n#endif\n\n#if ( NUM_DIR_LIGHTS > 0 ) && defined( RE_Direct )\n\n\tDirectionalLight directionalLight;\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_DIR_LIGHTS; i ++ ) {\n\n\t\tdirectionalLight = directionalLights[ i ];\n\n\t\tgetDirectionalDirectLightIrradiance( directionalLight, geometry, directLight );\n\n\t\t#ifdef USE_SHADOWMAP\n\t\tdirectLight.color *= all( bvec2( directionalLight.shadow, directLight.visible ) ) ? getShadow( directionalShadowMap[ i ], directionalLight.shadowMapSize, directionalLight.shadowBias, directionalLight.shadowRadius, vDirectionalShadowCoord[ i ] ) : 1.0;\n\t\t#endif\n\n\t\tRE_Direct( directLight, geometry, material, reflectedLight );\n\n\t}\n\n#endif\n\n#if ( NUM_RECT_AREA_LIGHTS > 0 ) && defined( RE_Direct_RectArea )\n\n\tRectAreaLight rectAreaLight;\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_RECT_AREA_LIGHTS; i ++ ) {\n\n\t\trectAreaLight = rectAreaLights[ i ];\n\t\tRE_Direct_RectArea( rectAreaLight, geometry, material, reflectedLight );\n\n\t}\n\n#endif\n\n#if defined( RE_IndirectDiffuse )\n\n\tvec3 irradiance = getAmbientLightIrradiance( ambientLightColor );\n\n\t#if ( NUM_HEMI_LIGHTS > 0 )\n\n\t\t#pragma unroll_loop\n\t\tfor ( int i = 0; i < NUM_HEMI_LIGHTS; i ++ ) {\n\n\t\t\tirradiance += getHemisphereLightIrradiance( hemisphereLights[ i ], geometry );\n\n\t\t}\n\n\t#endif\n\n#endif\n\n#if defined( RE_IndirectSpecular )\n\n\tvec3 radiance = vec3( 0.0 );\n\tvec3 clearCoatRadiance = vec3( 0.0 );\n\n#endif\n"
}, function(e, t) {
    e.exports = "#if defined( RE_IndirectDiffuse )\n\n\t#ifdef USE_LIGHTMAP\n\n\t\tvec3 lightMapIrradiance = texture2D( lightMap, vUv2 ).xyz * lightMapIntensity;\n\n\t\t#ifndef PHYSICALLY_CORRECT_LIGHTS\n\n\t\t\tlightMapIrradiance *= PI; // factor of PI should not be present; included here to prevent breakage\n\n\t\t#endif\n\n\t\tirradiance += lightMapIrradiance;\n\n\t#endif\n\n\t#if defined( USE_ENVMAP ) && defined( PHYSICAL ) && defined( ENVMAP_TYPE_CUBE_UV )\n\n\t\tirradiance += getLightProbeIndirectIrradiance( /*lightProbe,*/ geometry, maxMipLevel );\n\n\t#endif\n\n#endif\n\n#if defined( USE_ENVMAP ) && defined( RE_IndirectSpecular )\n\n\tradiance += getLightProbeIndirectRadiance( /*specularLightProbe,*/ geometry, Material_BlinnShininessExponent( material ), maxMipLevel );\n\n\t#ifndef STANDARD\n\t\tclearCoatRadiance += getLightProbeIndirectRadiance( /*specularLightProbe,*/ geometry, Material_ClearCoat_BlinnShininessExponent( material ), maxMipLevel );\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#if defined( RE_IndirectDiffuse )\n\n\tRE_IndirectDiffuse( irradiance, geometry, material, reflectedLight );\n\n#endif\n\n#if defined( RE_IndirectSpecular )\n\n\tRE_IndirectSpecular( radiance, clearCoatRadiance, geometry, material, reflectedLight );\n\n#endif\n"
}, function(e, t) {
    e.exports = "#if defined( USE_LOGDEPTHBUF ) && defined( USE_LOGDEPTHBUF_EXT )\n\n\tgl_FragDepthEXT = log2( vFragDepth ) * logDepthBufFC * 0.5;\n\n#endif"
}, function(e, t) {
    e.exports = "#if defined( USE_LOGDEPTHBUF ) && defined( USE_LOGDEPTHBUF_EXT )\n\n\tuniform float logDepthBufFC;\n\tvarying float vFragDepth;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_LOGDEPTHBUF\n\n\t#ifdef USE_LOGDEPTHBUF_EXT\n\n\t\tvarying float vFragDepth;\n\n\t#else\n\n\t\tuniform float logDepthBufFC;\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_LOGDEPTHBUF\n\n\t#ifdef USE_LOGDEPTHBUF_EXT\n\n\t\tvFragDepth = 1.0 + gl_Position.w;\n\n\t#else\n\n\t\tgl_Position.z = log2( max( EPSILON, gl_Position.w + 1.0 ) ) * logDepthBufFC - 1.0;\n\n\t\tgl_Position.z *= gl_Position.w;\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_MAP\n\n\tvec4 texelColor = texture2D( map, vUv );\n\n\ttexelColor = mapTexelToLinear( texelColor );\n\tdiffuseColor *= texelColor;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_MAP\n\n\tuniform sampler2D map;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_MAP\n\n\tvec2 uv = ( uvTransform * vec3( gl_PointCoord.x, 1.0 - gl_PointCoord.y, 1 ) ).xy;\n\tvec4 mapTexel = texture2D( map, uv );\n\tdiffuseColor *= mapTexelToLinear( mapTexel );\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_MAP\n\n\tuniform mat3 uvTransform;\n\tuniform sampler2D map;\n\n#endif\n"
}, function(e, t) {
    e.exports = "float metalnessFactor = metalness;\n\n#ifdef USE_METALNESSMAP\n\n\tvec4 texelMetalness = texture2D( metalnessMap, vUv );\n\n\t// reads channel B, compatible with a combined OcclusionRoughnessMetallic (RGB) texture\n\tmetalnessFactor *= texelMetalness.b;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_METALNESSMAP\n\n\tuniform sampler2D metalnessMap;\n\n#endif"
}, function(e, t) {
    e.exports = "#ifdef USE_MORPHNORMALS\n\n\tobjectNormal += ( morphNormal0 - normal ) * morphTargetInfluences[ 0 ];\n\tobjectNormal += ( morphNormal1 - normal ) * morphTargetInfluences[ 1 ];\n\tobjectNormal += ( morphNormal2 - normal ) * morphTargetInfluences[ 2 ];\n\tobjectNormal += ( morphNormal3 - normal ) * morphTargetInfluences[ 3 ];\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_MORPHTARGETS\n\n\t#ifndef USE_MORPHNORMALS\n\n\tuniform float morphTargetInfluences[ 8 ];\n\n\t#else\n\n\tuniform float morphTargetInfluences[ 4 ];\n\n\t#endif\n\n#endif"
}, function(e, t) {
    e.exports = "#ifdef USE_MORPHTARGETS\n\n\ttransformed += ( morphTarget0 - position ) * morphTargetInfluences[ 0 ];\n\ttransformed += ( morphTarget1 - position ) * morphTargetInfluences[ 1 ];\n\ttransformed += ( morphTarget2 - position ) * morphTargetInfluences[ 2 ];\n\ttransformed += ( morphTarget3 - position ) * morphTargetInfluences[ 3 ];\n\n\t#ifndef USE_MORPHNORMALS\n\n\ttransformed += ( morphTarget4 - position ) * morphTargetInfluences[ 4 ];\n\ttransformed += ( morphTarget5 - position ) * morphTargetInfluences[ 5 ];\n\ttransformed += ( morphTarget6 - position ) * morphTargetInfluences[ 6 ];\n\ttransformed += ( morphTarget7 - position ) * morphTargetInfluences[ 7 ];\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef FLAT_SHADED\n\n\t// Workaround for Adreno/Nexus5 not able able to do dFdx( vViewPosition ) ...\n\n\tvec3 fdx = vec3( dFdx( vViewPosition.x ), dFdx( vViewPosition.y ), dFdx( vViewPosition.z ) );\n\tvec3 fdy = vec3( dFdy( vViewPosition.x ), dFdy( vViewPosition.y ), dFdy( vViewPosition.z ) );\n\tvec3 normal = normalize( cross( fdx, fdy ) );\n\n#else\n\n\tvec3 normal = normalize( vNormal );\n\n\t#ifdef DOUBLE_SIDED\n\n\t\tnormal = normal * ( float( gl_FrontFacing ) * 2.0 - 1.0 );\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_NORMALMAP\n\n\t#ifdef OBJECTSPACE_NORMALMAP\n\n\t\tnormal = texture2D( normalMap, vUv ).xyz * 2.0 - 1.0; // overrides both flatShading and attribute normals\n\n\t\t#ifdef FLIP_SIDED\n\n\t\t\tnormal = - normal;\n\n\t\t#endif\n\n\t\t#ifdef DOUBLE_SIDED\n\n\t\t\tnormal = normal * ( float( gl_FrontFacing ) * 2.0 - 1.0 );\n\n\t\t#endif\n\n\t\tnormal = normalize( normalMatrix * normal );\n\n\t#else // tangent-space normal map\n\n\t\tnormal = perturbNormal2Arb( -vViewPosition, normal );\n\n\t#endif\n\n#elif defined( USE_BUMPMAP )\n\n\tnormal = perturbNormalArb( -vViewPosition, normal, dHdxy_fwd() );\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_NORMALMAP\n\n\tuniform sampler2D normalMap;\n\tuniform vec2 normalScale;\n\n\t#ifdef OBJECTSPACE_NORMALMAP\n\n\t\tuniform mat3 normalMatrix;\n\n\t#else\n\n\t\t// Per-Pixel Tangent Space Normal Mapping\n\t\t// http://hacksoflife.blogspot.ch/2009/11/per-pixel-tangent-space-normal-mapping.html\n\n\t\tvec3 perturbNormal2Arb( vec3 eye_pos, vec3 surf_norm ) {\n\n\t\t\t// Workaround for Adreno 3XX dFd*( vec3 ) bug. See #9988\n\n\t\t\tvec3 q0 = vec3( dFdx( eye_pos.x ), dFdx( eye_pos.y ), dFdx( eye_pos.z ) );\n\t\t\tvec3 q1 = vec3( dFdy( eye_pos.x ), dFdy( eye_pos.y ), dFdy( eye_pos.z ) );\n\t\t\tvec2 st0 = dFdx( vUv.st );\n\t\t\tvec2 st1 = dFdy( vUv.st );\n\n\t\t\tfloat scale = sign( st1.t * st0.s - st0.t * st1.s ); // we do not care about the magnitude\n\n\t\t\tvec3 S = normalize( ( q0 * st1.t - q1 * st0.t ) * scale );\n\t\t\tvec3 T = normalize( ( - q0 * st1.s + q1 * st0.s ) * scale );\n\t\t\tvec3 N = normalize( surf_norm );\n\t\t\tmat3 tsn = mat3( S, T, N );\n\n\t\t\tvec3 mapN = texture2D( normalMap, vUv ).xyz * 2.0 - 1.0;\n\n\t\t\tmapN.xy *= normalScale;\n\t\t\tmapN.xy *= ( float( gl_FrontFacing ) * 2.0 - 1.0 );\n\n\t\t\treturn normalize( tsn * mapN );\n\n\t\t}\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "vec3 packNormalToRGB( const in vec3 normal ) {\n\treturn normalize( normal ) * 0.5 + 0.5;\n}\n\nvec3 unpackRGBToNormal( const in vec3 rgb ) {\n\treturn 2.0 * rgb.xyz - 1.0;\n}\n\nconst float PackUpscale = 256. / 255.; // fraction -> 0..1 (including 1)\nconst float UnpackDownscale = 255. / 256.; // 0..1 -> fraction (excluding 1)\n\nconst vec3 PackFactors = vec3( 256. * 256. * 256., 256. * 256.,  256. );\nconst vec4 UnpackFactors = UnpackDownscale / vec4( PackFactors, 1. );\n\nconst float ShiftRight8 = 1. / 256.;\n\nvec4 packDepthToRGBA( const in float v ) {\n\tvec4 r = vec4( fract( v * PackFactors ), v );\n\tr.yzw -= r.xyz * ShiftRight8; // tidy overflow\n\treturn r * PackUpscale;\n}\n\nfloat unpackRGBAToDepth( const in vec4 v ) {\n\treturn dot( v, UnpackFactors );\n}\n\n// NOTE: viewZ/eyeZ is < 0 when in front of the camera per OpenGL conventions\n\nfloat viewZToOrthographicDepth( const in float viewZ, const in float near, const in float far ) {\n\treturn ( viewZ + near ) / ( near - far );\n}\nfloat orthographicDepthToViewZ( const in float linearClipZ, const in float near, const in float far ) {\n\treturn linearClipZ * ( near - far ) - near;\n}\n\nfloat viewZToPerspectiveDepth( const in float viewZ, const in float near, const in float far ) {\n\treturn (( near + viewZ ) * far ) / (( far - near ) * viewZ );\n}\nfloat perspectiveDepthToViewZ( const in float invClipZ, const in float near, const in float far ) {\n\treturn ( near * far ) / ( ( far - near ) * invClipZ - far );\n}\n"
}, function(e, t) {
    e.exports = "#ifdef PREMULTIPLIED_ALPHA\n\n\t// Get get normal blending with premultipled, use with CustomBlending, OneFactor, OneMinusSrcAlphaFactor, AddEquation.\n\tgl_FragColor.rgb *= gl_FragColor.a;\n\n#endif\n"
}, function(e, t) {
    e.exports = "vec4 mvPosition = modelViewMatrix * vec4( transformed, 1.0 );\n\ngl_Position = projectionMatrix * mvPosition;\n"
}, function(e, t) {
    e.exports = "#if defined( DITHERING )\n\n  gl_FragColor.rgb = dithering( gl_FragColor.rgb );\n\n#endif\n"
}, function(e, t) {
    e.exports = "#if defined( DITHERING )\n\n\t// based on https://www.shadertoy.com/view/MslGR8\n\tvec3 dithering( vec3 color ) {\n\t\t//Calculate grid position\n\t\tfloat grid_position = rand( gl_FragCoord.xy );\n\n\t\t//Shift the individual colors differently, thus making it even harder to see the dithering pattern\n\t\tvec3 dither_shift_RGB = vec3( 0.25 / 255.0, -0.25 / 255.0, 0.25 / 255.0 );\n\n\t\t//modify shift acording to grid position.\n\t\tdither_shift_RGB = mix( 2.0 * dither_shift_RGB, -2.0 * dither_shift_RGB, grid_position );\n\n\t\t//shift the color by dither_shift\n\t\treturn color + dither_shift_RGB;\n\t}\n\n#endif\n"
}, function(e, t) {
    e.exports = "float roughnessFactor = roughness;\n\n#ifdef USE_ROUGHNESSMAP\n\n\tvec4 texelRoughness = texture2D( roughnessMap, vUv );\n\n\t// reads channel G, compatible with a combined OcclusionRoughnessMetallic (RGB) texture\n\troughnessFactor *= texelRoughness.g;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_ROUGHNESSMAP\n\n\tuniform sampler2D roughnessMap;\n\n#endif"
}, function(e, t) {
    e.exports = "#ifdef USE_SHADOWMAP\n\n\t#if NUM_DIR_LIGHTS > 0\n\n\t\tuniform sampler2D directionalShadowMap[ NUM_DIR_LIGHTS ];\n\t\tvarying vec4 vDirectionalShadowCoord[ NUM_DIR_LIGHTS ];\n\n\t#endif\n\n\t#if NUM_SPOT_LIGHTS > 0\n\n\t\tuniform sampler2D spotShadowMap[ NUM_SPOT_LIGHTS ];\n\t\tvarying vec4 vSpotShadowCoord[ NUM_SPOT_LIGHTS ];\n\n\t#endif\n\n\t#if NUM_POINT_LIGHTS > 0\n\n\t\tuniform sampler2D pointShadowMap[ NUM_POINT_LIGHTS ];\n\t\tvarying vec4 vPointShadowCoord[ NUM_POINT_LIGHTS ];\n\n\t#endif\n\n\t/*\n\t#if NUM_RECT_AREA_LIGHTS > 0\n\n\t\t// TODO (abelnation): create uniforms for area light shadows\n\n\t#endif\n\t*/\n\n\tfloat texture2DCompare( sampler2D depths, vec2 uv, float compare ) {\n\n\t\treturn step( compare, unpackRGBAToDepth( texture2D( depths, uv ) ) );\n\n\t}\n\n\tfloat texture2DShadowLerp( sampler2D depths, vec2 size, vec2 uv, float compare ) {\n\n\t\tconst vec2 offset = vec2( 0.0, 1.0 );\n\n\t\tvec2 texelSize = vec2( 1.0 ) / size;\n\t\tvec2 centroidUV = floor( uv * size + 0.5 ) / size;\n\n\t\tfloat lb = texture2DCompare( depths, centroidUV + texelSize * offset.xx, compare );\n\t\tfloat lt = texture2DCompare( depths, centroidUV + texelSize * offset.xy, compare );\n\t\tfloat rb = texture2DCompare( depths, centroidUV + texelSize * offset.yx, compare );\n\t\tfloat rt = texture2DCompare( depths, centroidUV + texelSize * offset.yy, compare );\n\n\t\tvec2 f = fract( uv * size + 0.5 );\n\n\t\tfloat a = mix( lb, lt, f.y );\n\t\tfloat b = mix( rb, rt, f.y );\n\t\tfloat c = mix( a, b, f.x );\n\n\t\treturn c;\n\n\t}\n\n\tfloat getShadow( sampler2D shadowMap, vec2 shadowMapSize, float shadowBias, float shadowRadius, vec4 shadowCoord ) {\n\n\t\tfloat shadow = 1.0;\n\n\t\tshadowCoord.xyz /= shadowCoord.w;\n\t\tshadowCoord.z += shadowBias;\n\n\t\t// if ( something && something ) breaks ATI OpenGL shader compiler\n\t\t// if ( all( something, something ) ) using this instead\n\n\t\tbvec4 inFrustumVec = bvec4 ( shadowCoord.x >= 0.0, shadowCoord.x <= 1.0, shadowCoord.y >= 0.0, shadowCoord.y <= 1.0 );\n\t\tbool inFrustum = all( inFrustumVec );\n\n\t\tbvec2 frustumTestVec = bvec2( inFrustum, shadowCoord.z <= 1.0 );\n\n\t\tbool frustumTest = all( frustumTestVec );\n\n\t\tif ( frustumTest ) {\n\n\t\t#if defined( SHADOWMAP_TYPE_PCF )\n\n\t\t\tvec2 texelSize = vec2( 1.0 ) / shadowMapSize;\n\n\t\t\tfloat dx0 = - texelSize.x * shadowRadius;\n\t\t\tfloat dy0 = - texelSize.y * shadowRadius;\n\t\t\tfloat dx1 = + texelSize.x * shadowRadius;\n\t\t\tfloat dy1 = + texelSize.y * shadowRadius;\n\n\t\t\tshadow = (\n\t\t\t\ttexture2DCompare( shadowMap, shadowCoord.xy + vec2( dx0, dy0 ), shadowCoord.z ) +\n\t\t\t\ttexture2DCompare( shadowMap, shadowCoord.xy + vec2( 0.0, dy0 ), shadowCoord.z ) +\n\t\t\t\ttexture2DCompare( shadowMap, shadowCoord.xy + vec2( dx1, dy0 ), shadowCoord.z ) +\n\t\t\t\ttexture2DCompare( shadowMap, shadowCoord.xy + vec2( dx0, 0.0 ), shadowCoord.z ) +\n\t\t\t\ttexture2DCompare( shadowMap, shadowCoord.xy, shadowCoord.z ) +\n\t\t\t\ttexture2DCompare( shadowMap, shadowCoord.xy + vec2( dx1, 0.0 ), shadowCoord.z ) +\n\t\t\t\ttexture2DCompare( shadowMap, shadowCoord.xy + vec2( dx0, dy1 ), shadowCoord.z ) +\n\t\t\t\ttexture2DCompare( shadowMap, shadowCoord.xy + vec2( 0.0, dy1 ), shadowCoord.z ) +\n\t\t\t\ttexture2DCompare( shadowMap, shadowCoord.xy + vec2( dx1, dy1 ), shadowCoord.z )\n\t\t\t) * ( 1.0 / 9.0 );\n\n\t\t#elif defined( SHADOWMAP_TYPE_PCF_SOFT )\n\n\t\t\tvec2 texelSize = vec2( 1.0 ) / shadowMapSize;\n\n\t\t\tfloat dx0 = - texelSize.x * shadowRadius;\n\t\t\tfloat dy0 = - texelSize.y * shadowRadius;\n\t\t\tfloat dx1 = + texelSize.x * shadowRadius;\n\t\t\tfloat dy1 = + texelSize.y * shadowRadius;\n\n\t\t\tshadow = (\n\t\t\t\ttexture2DShadowLerp( shadowMap, shadowMapSize, shadowCoord.xy + vec2( dx0, dy0 ), shadowCoord.z ) +\n\t\t\t\ttexture2DShadowLerp( shadowMap, shadowMapSize, shadowCoord.xy + vec2( 0.0, dy0 ), shadowCoord.z ) +\n\t\t\t\ttexture2DShadowLerp( shadowMap, shadowMapSize, shadowCoord.xy + vec2( dx1, dy0 ), shadowCoord.z ) +\n\t\t\t\ttexture2DShadowLerp( shadowMap, shadowMapSize, shadowCoord.xy + vec2( dx0, 0.0 ), shadowCoord.z ) +\n\t\t\t\ttexture2DShadowLerp( shadowMap, shadowMapSize, shadowCoord.xy, shadowCoord.z ) +\n\t\t\t\ttexture2DShadowLerp( shadowMap, shadowMapSize, shadowCoord.xy + vec2( dx1, 0.0 ), shadowCoord.z ) +\n\t\t\t\ttexture2DShadowLerp( shadowMap, shadowMapSize, shadowCoord.xy + vec2( dx0, dy1 ), shadowCoord.z ) +\n\t\t\t\ttexture2DShadowLerp( shadowMap, shadowMapSize, shadowCoord.xy + vec2( 0.0, dy1 ), shadowCoord.z ) +\n\t\t\t\ttexture2DShadowLerp( shadowMap, shadowMapSize, shadowCoord.xy + vec2( dx1, dy1 ), shadowCoord.z )\n\t\t\t) * ( 1.0 / 9.0 );\n\n\t\t#else // no percentage-closer filtering:\n\n\t\t\tshadow = texture2DCompare( shadowMap, shadowCoord.xy, shadowCoord.z );\n\n\t\t#endif\n\n\t\t}\n\n\t\treturn shadow;\n\n\t}\n\n\t// cubeToUV() maps a 3D direction vector suitable for cube texture mapping to a 2D\n\t// vector suitable for 2D texture mapping. This code uses the following layout for the\n\t// 2D texture:\n\t//\n\t// xzXZ\n\t//  y Y\n\t//\n\t// Y - Positive y direction\n\t// y - Negative y direction\n\t// X - Positive x direction\n\t// x - Negative x direction\n\t// Z - Positive z direction\n\t// z - Negative z direction\n\t//\n\t// Source and test bed:\n\t// https://gist.github.com/tschw/da10c43c467ce8afd0c4\n\n\tvec2 cubeToUV( vec3 v, float texelSizeY ) {\n\n\t\t// Number of texels to avoid at the edge of each square\n\n\t\tvec3 absV = abs( v );\n\n\t\t// Intersect unit cube\n\n\t\tfloat scaleToCube = 1.0 / max( absV.x, max( absV.y, absV.z ) );\n\t\tabsV *= scaleToCube;\n\n\t\t// Apply scale to avoid seams\n\n\t\t// two texels less per square (one texel will do for NEAREST)\n\t\tv *= scaleToCube * ( 1.0 - 2.0 * texelSizeY );\n\n\t\t// Unwrap\n\n\t\t// space: -1 ... 1 range for each square\n\t\t//\n\t\t// #X##\t\tdim    := ( 4 , 2 )\n\t\t//  # #\t\tcenter := ( 1 , 1 )\n\n\t\tvec2 planar = v.xy;\n\n\t\tfloat almostATexel = 1.5 * texelSizeY;\n\t\tfloat almostOne = 1.0 - almostATexel;\n\n\t\tif ( absV.z >= almostOne ) {\n\n\t\t\tif ( v.z > 0.0 )\n\t\t\t\tplanar.x = 4.0 - v.x;\n\n\t\t} else if ( absV.x >= almostOne ) {\n\n\t\t\tfloat signX = sign( v.x );\n\t\t\tplanar.x = v.z * signX + 2.0 * signX;\n\n\t\t} else if ( absV.y >= almostOne ) {\n\n\t\t\tfloat signY = sign( v.y );\n\t\t\tplanar.x = v.x + 2.0 * signY + 2.0;\n\t\t\tplanar.y = v.z * signY - 2.0;\n\n\t\t}\n\n\t\t// Transform to UV space\n\n\t\t// scale := 0.5 / dim\n\t\t// translate := ( center + 0.5 ) / dim\n\t\treturn vec2( 0.125, 0.25 ) * planar + vec2( 0.375, 0.75 );\n\n\t}\n\n\tfloat getPointShadow( sampler2D shadowMap, vec2 shadowMapSize, float shadowBias, float shadowRadius, vec4 shadowCoord, float shadowCameraNear, float shadowCameraFar ) {\n\n\t\tvec2 texelSize = vec2( 1.0 ) / ( shadowMapSize * vec2( 4.0, 2.0 ) );\n\n\t\t// for point lights, the uniform @vShadowCoord is re-purposed to hold\n\t\t// the vector from the light to the world-space position of the fragment.\n\t\tvec3 lightToPosition = shadowCoord.xyz;\n\n\t\t// dp = normalized distance from light to fragment position\n\t\tfloat dp = ( length( lightToPosition ) - shadowCameraNear ) / ( shadowCameraFar - shadowCameraNear ); // need to clamp?\n\t\tdp += shadowBias;\n\n\t\t// bd3D = base direction 3D\n\t\tvec3 bd3D = normalize( lightToPosition );\n\n\t\t#if defined( SHADOWMAP_TYPE_PCF ) || defined( SHADOWMAP_TYPE_PCF_SOFT )\n\n\t\t\tvec2 offset = vec2( - 1, 1 ) * shadowRadius * texelSize.y;\n\n\t\t\treturn (\n\t\t\t\ttexture2DCompare( shadowMap, cubeToUV( bd3D + offset.xyy, texelSize.y ), dp ) +\n\t\t\t\ttexture2DCompare( shadowMap, cubeToUV( bd3D + offset.yyy, texelSize.y ), dp ) +\n\t\t\t\ttexture2DCompare( shadowMap, cubeToUV( bd3D + offset.xyx, texelSize.y ), dp ) +\n\t\t\t\ttexture2DCompare( shadowMap, cubeToUV( bd3D + offset.yyx, texelSize.y ), dp ) +\n\t\t\t\ttexture2DCompare( shadowMap, cubeToUV( bd3D, texelSize.y ), dp ) +\n\t\t\t\ttexture2DCompare( shadowMap, cubeToUV( bd3D + offset.xxy, texelSize.y ), dp ) +\n\t\t\t\ttexture2DCompare( shadowMap, cubeToUV( bd3D + offset.yxy, texelSize.y ), dp ) +\n\t\t\t\ttexture2DCompare( shadowMap, cubeToUV( bd3D + offset.xxx, texelSize.y ), dp ) +\n\t\t\t\ttexture2DCompare( shadowMap, cubeToUV( bd3D + offset.yxx, texelSize.y ), dp )\n\t\t\t) * ( 1.0 / 9.0 );\n\n\t\t#else // no percentage-closer filtering\n\n\t\t\treturn texture2DCompare( shadowMap, cubeToUV( bd3D, texelSize.y ), dp );\n\n\t\t#endif\n\n\t}\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_SHADOWMAP\n\n\t#if NUM_DIR_LIGHTS > 0\n\n\t\tuniform mat4 directionalShadowMatrix[ NUM_DIR_LIGHTS ];\n\t\tvarying vec4 vDirectionalShadowCoord[ NUM_DIR_LIGHTS ];\n\n\t#endif\n\n\t#if NUM_SPOT_LIGHTS > 0\n\n\t\tuniform mat4 spotShadowMatrix[ NUM_SPOT_LIGHTS ];\n\t\tvarying vec4 vSpotShadowCoord[ NUM_SPOT_LIGHTS ];\n\n\t#endif\n\n\t#if NUM_POINT_LIGHTS > 0\n\n\t\tuniform mat4 pointShadowMatrix[ NUM_POINT_LIGHTS ];\n\t\tvarying vec4 vPointShadowCoord[ NUM_POINT_LIGHTS ];\n\n\t#endif\n\n\t/*\n\t#if NUM_RECT_AREA_LIGHTS > 0\n\n\t\t// TODO (abelnation): uniforms for area light shadows\n\n\t#endif\n\t*/\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_SHADOWMAP\n\n\t#if NUM_DIR_LIGHTS > 0\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_DIR_LIGHTS; i ++ ) {\n\n\t\tvDirectionalShadowCoord[ i ] = directionalShadowMatrix[ i ] * worldPosition;\n\n\t}\n\n\t#endif\n\n\t#if NUM_SPOT_LIGHTS > 0\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_SPOT_LIGHTS; i ++ ) {\n\n\t\tvSpotShadowCoord[ i ] = spotShadowMatrix[ i ] * worldPosition;\n\n\t}\n\n\t#endif\n\n\t#if NUM_POINT_LIGHTS > 0\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_POINT_LIGHTS; i ++ ) {\n\n\t\tvPointShadowCoord[ i ] = pointShadowMatrix[ i ] * worldPosition;\n\n\t}\n\n\t#endif\n\n\t/*\n\t#if NUM_RECT_AREA_LIGHTS > 0\n\n\t\t// TODO (abelnation): update vAreaShadowCoord with area light info\n\n\t#endif\n\t*/\n\n#endif\n"
}, function(e, t) {
    e.exports = "float getShadowMask() {\n\n\tfloat shadow = 1.0;\n\n\t#ifdef USE_SHADOWMAP\n\n\t#if NUM_DIR_LIGHTS > 0\n\n\tDirectionalLight directionalLight;\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_DIR_LIGHTS; i ++ ) {\n\n\t\tdirectionalLight = directionalLights[ i ];\n\t\tshadow *= bool( directionalLight.shadow ) ? getShadow( directionalShadowMap[ i ], directionalLight.shadowMapSize, directionalLight.shadowBias, directionalLight.shadowRadius, vDirectionalShadowCoord[ i ] ) : 1.0;\n\n\t}\n\n\t#endif\n\n\t#if NUM_SPOT_LIGHTS > 0\n\n\tSpotLight spotLight;\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_SPOT_LIGHTS; i ++ ) {\n\n\t\tspotLight = spotLights[ i ];\n\t\tshadow *= bool( spotLight.shadow ) ? getShadow( spotShadowMap[ i ], spotLight.shadowMapSize, spotLight.shadowBias, spotLight.shadowRadius, vSpotShadowCoord[ i ] ) : 1.0;\n\n\t}\n\n\t#endif\n\n\t#if NUM_POINT_LIGHTS > 0\n\n\tPointLight pointLight;\n\n\t#pragma unroll_loop\n\tfor ( int i = 0; i < NUM_POINT_LIGHTS; i ++ ) {\n\n\t\tpointLight = pointLights[ i ];\n\t\tshadow *= bool( pointLight.shadow ) ? getPointShadow( pointShadowMap[ i ], pointLight.shadowMapSize, pointLight.shadowBias, pointLight.shadowRadius, vPointShadowCoord[ i ], pointLight.shadowCameraNear, pointLight.shadowCameraFar ) : 1.0;\n\n\t}\n\n\t#endif\n\n\t/*\n\t#if NUM_RECT_AREA_LIGHTS > 0\n\n\t\t// TODO (abelnation): update shadow for Area light\n\n\t#endif\n\t*/\n\n\t#endif\n\n\treturn shadow;\n\n}\n"
}, function(e, t) {
    e.exports = "#ifdef USE_SKINNING\n\n\tmat4 boneMatX = getBoneMatrix( skinIndex.x );\n\tmat4 boneMatY = getBoneMatrix( skinIndex.y );\n\tmat4 boneMatZ = getBoneMatrix( skinIndex.z );\n\tmat4 boneMatW = getBoneMatrix( skinIndex.w );\n\n#endif"
}, function(e, t) {
    e.exports = "#ifdef USE_SKINNING\n\n\tuniform mat4 bindMatrix;\n\tuniform mat4 bindMatrixInverse;\n\n\t#ifdef BONE_TEXTURE\n\n\t\tuniform sampler2D boneTexture;\n\t\tuniform int boneTextureSize;\n\n\t\tmat4 getBoneMatrix( const in float i ) {\n\n\t\t\tfloat j = i * 4.0;\n\t\t\tfloat x = mod( j, float( boneTextureSize ) );\n\t\t\tfloat y = floor( j / float( boneTextureSize ) );\n\n\t\t\tfloat dx = 1.0 / float( boneTextureSize );\n\t\t\tfloat dy = 1.0 / float( boneTextureSize );\n\n\t\t\ty = dy * ( y + 0.5 );\n\n\t\t\tvec4 v1 = texture2D( boneTexture, vec2( dx * ( x + 0.5 ), y ) );\n\t\t\tvec4 v2 = texture2D( boneTexture, vec2( dx * ( x + 1.5 ), y ) );\n\t\t\tvec4 v3 = texture2D( boneTexture, vec2( dx * ( x + 2.5 ), y ) );\n\t\t\tvec4 v4 = texture2D( boneTexture, vec2( dx * ( x + 3.5 ), y ) );\n\n\t\t\tmat4 bone = mat4( v1, v2, v3, v4 );\n\n\t\t\treturn bone;\n\n\t\t}\n\n\t#else\n\n\t\tuniform mat4 boneMatrices[ MAX_BONES ];\n\n\t\tmat4 getBoneMatrix( const in float i ) {\n\n\t\t\tmat4 bone = boneMatrices[ int(i) ];\n\t\t\treturn bone;\n\n\t\t}\n\n\t#endif\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_SKINNING\n\n\tvec4 skinVertex = bindMatrix * vec4( transformed, 1.0 );\n\n\tvec4 skinned = vec4( 0.0 );\n\tskinned += boneMatX * skinVertex * skinWeight.x;\n\tskinned += boneMatY * skinVertex * skinWeight.y;\n\tskinned += boneMatZ * skinVertex * skinWeight.z;\n\tskinned += boneMatW * skinVertex * skinWeight.w;\n\n\ttransformed = ( bindMatrixInverse * skinned ).xyz;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifdef USE_SKINNING\n\n\tmat4 skinMatrix = mat4( 0.0 );\n\tskinMatrix += skinWeight.x * boneMatX;\n\tskinMatrix += skinWeight.y * boneMatY;\n\tskinMatrix += skinWeight.z * boneMatZ;\n\tskinMatrix += skinWeight.w * boneMatW;\n\tskinMatrix  = bindMatrixInverse * skinMatrix * bindMatrix;\n\n\tobjectNormal = vec4( skinMatrix * vec4( objectNormal, 0.0 ) ).xyz;\n\n#endif\n"
}, function(e, t) {
    e.exports = "float specularStrength;\n\n#ifdef USE_SPECULARMAP\n\n\tvec4 texelSpecular = texture2D( specularMap, vUv );\n\tspecularStrength = texelSpecular.r;\n\n#else\n\n\tspecularStrength = 1.0;\n\n#endif"
}, function(e, t) {
    e.exports = "#ifdef USE_SPECULARMAP\n\n\tuniform sampler2D specularMap;\n\n#endif"
}, function(e, t) {
    e.exports = "#if defined( TONE_MAPPING )\n\n  gl_FragColor.rgb = toneMapping( gl_FragColor.rgb );\n\n#endif\n"
}, function(e, t) {
    e.exports = "#ifndef saturate\n\t#define saturate(a) clamp( a, 0.0, 1.0 )\n#endif\n\nuniform float toneMappingExposure;\nuniform float toneMappingWhitePoint;\n\n// exposure only\nvec3 LinearToneMapping( vec3 color ) {\n\n\treturn toneMappingExposure * color;\n\n}\n\n// source: https://www.cs.utah.edu/~reinhard/cdrom/\nvec3 ReinhardToneMapping( vec3 color ) {\n\n\tcolor *= toneMappingExposure;\n\treturn saturate( color / ( vec3( 1.0 ) + color ) );\n\n}\n\n// source: http://filmicgames.com/archives/75\n#define Uncharted2Helper( x ) max( ( ( x * ( 0.15 * x + 0.10 * 0.50 ) + 0.20 * 0.02 ) / ( x * ( 0.15 * x + 0.50 ) + 0.20 * 0.30 ) ) - 0.02 / 0.30, vec3( 0.0 ) )\nvec3 Uncharted2ToneMapping( vec3 color ) {\n\n\t// John Hable's filmic operator from Uncharted 2 video game\n\tcolor *= toneMappingExposure;\n\treturn saturate( Uncharted2Helper( color ) / Uncharted2Helper( vec3( toneMappingWhitePoint ) ) );\n\n}\n\n// source: http://filmicgames.com/archives/75\nvec3 OptimizedCineonToneMapping( vec3 color ) {\n\n\t// optimized filmic operator by Jim Hejl and Richard Burgess-Dawson\n\tcolor *= toneMappingExposure;\n\tcolor = max( vec3( 0.0 ), color - 0.004 );\n\treturn pow( ( color * ( 6.2 * color + 0.5 ) ) / ( color * ( 6.2 * color + 1.7 ) + 0.06 ), vec3( 2.2 ) );\n\n}\n"
}, function(e, t) {
    e.exports = "#if defined( USE_MAP ) || defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( USE_SPECULARMAP ) || defined( USE_ALPHAMAP ) || defined( USE_EMISSIVEMAP ) || defined( USE_ROUGHNESSMAP ) || defined( USE_METALNESSMAP )\n\n\tvarying vec2 vUv;\n\n#endif"
}, function(e, t) {
    e.exports = "#if defined( USE_MAP ) || defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( USE_SPECULARMAP ) || defined( USE_ALPHAMAP ) || defined( USE_EMISSIVEMAP ) || defined( USE_ROUGHNESSMAP ) || defined( USE_METALNESSMAP )\n\n\tvarying vec2 vUv;\n\tuniform mat3 uvTransform;\n\n#endif\n"
}, function(e, t) {
    e.exports = "#if defined( USE_MAP ) || defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( USE_SPECULARMAP ) || defined( USE_ALPHAMAP ) || defined( USE_EMISSIVEMAP ) || defined( USE_ROUGHNESSMAP ) || defined( USE_METALNESSMAP )\n\n\tvUv = ( uvTransform * vec3( uv, 1 ) ).xy;\n\n#endif"
}, function(e, t) {
    e.exports = "#if defined( USE_LIGHTMAP ) || defined( USE_AOMAP )\n\n\tvarying vec2 vUv2;\n\n#endif"
}, function(e, t) {
    e.exports = "#if defined( USE_LIGHTMAP ) || defined( USE_AOMAP )\n\n\tattribute vec2 uv2;\n\tvarying vec2 vUv2;\n\n#endif"
}, function(e, t) {
    e.exports = "#if defined( USE_LIGHTMAP ) || defined( USE_AOMAP )\n\n\tvUv2 = uv2;\n\n#endif"
}, function(e, t) {
    e.exports = "#if defined( USE_ENVMAP ) || defined( DISTANCE ) || defined ( USE_SHADOWMAP )\n\n\tvec4 worldPosition = modelMatrix * vec4( transformed, 1.0 );\n\n#endif\n"
}, function(e, t) {
    e.exports = "uniform sampler2D t2D;\n\nvarying vec2 vUv;\n\nvoid main() {\n\n\tgl_FragColor = texture2D( t2D, vUv );\n\n}\n"
}, function(e, t) {
    e.exports = "varying vec2 vUv;\n\nvoid main() {\n\n\tvUv = uv;\n\n\tgl_Position = vec4( position, 1.0 );\n\tgl_Position.z = 1.0;\n\n}\n"
}, function(e, t) {
    e.exports = "uniform samplerCube tCube;\nuniform float tFlip;\nuniform float opacity;\n\nvarying vec3 vWorldPosition;\n\nvoid main() {\n\n\tgl_FragColor = textureCube( tCube, vec3( tFlip * vWorldPosition.x, vWorldPosition.yz ) );\n\tgl_FragColor.a *= opacity;\n\n}\n"
}, function(e, t) {
    e.exports = "varying vec3 vWorldPosition;\n\n#include <common>\n\nvoid main() {\n\n\tvWorldPosition = transformDirection( position, modelMatrix );\n\n\t#include <begin_vertex>\n\t#include <project_vertex>\n\n\tgl_Position.z = gl_Position.w; // set z to camera.far\n\n}\n"
}, function(e, t) {
    e.exports = "#if DEPTH_PACKING == 3200\n\n\tuniform float opacity;\n\n#endif\n\n#include <common>\n#include <packing>\n#include <uv_pars_fragment>\n#include <map_pars_fragment>\n#include <alphamap_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\nvoid main() {\n\n\t#include <clipping_planes_fragment>\n\n\tvec4 diffuseColor = vec4( 1.0 );\n\n\t#if DEPTH_PACKING == 3200\n\n\t\tdiffuseColor.a = opacity;\n\n\t#endif\n\n\t#include <map_fragment>\n\t#include <alphamap_fragment>\n\t#include <alphatest_fragment>\n\n\t#include <logdepthbuf_fragment>\n\n\t#if DEPTH_PACKING == 3200\n\n\t\tgl_FragColor = vec4( vec3( 1.0 - gl_FragCoord.z ), opacity );\n\n\t#elif DEPTH_PACKING == 3201\n\n\t\tgl_FragColor = packDepthToRGBA( gl_FragCoord.z );\n\n\t#endif\n\n}\n"
}, function(e, t) {
    e.exports = "#include <common>\n#include <uv_pars_vertex>\n#include <displacementmap_pars_vertex>\n#include <morphtarget_pars_vertex>\n#include <skinning_pars_vertex>\n#include <logdepthbuf_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\nvoid main() {\n\n\t#include <uv_vertex>\n\n\t#include <skinbase_vertex>\n\n\t#ifdef USE_DISPLACEMENTMAP\n\n\t\t#include <beginnormal_vertex>\n\t\t#include <morphnormal_vertex>\n\t\t#include <skinnormal_vertex>\n\n\t#endif\n\n\t#include <begin_vertex>\n\t#include <morphtarget_vertex>\n\t#include <skinning_vertex>\n\t#include <displacementmap_vertex>\n\t#include <project_vertex>\n\t#include <logdepthbuf_vertex>\n\t#include <clipping_planes_vertex>\n\n}\n"
}, function(e, t) {
    e.exports = "#define DISTANCE\n\nuniform vec3 referencePosition;\nuniform float nearDistance;\nuniform float farDistance;\nvarying vec3 vWorldPosition;\n\n#include <common>\n#include <packing>\n#include <uv_pars_fragment>\n#include <map_pars_fragment>\n#include <alphamap_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\nvoid main () {\n\n\t#include <clipping_planes_fragment>\n\n\tvec4 diffuseColor = vec4( 1.0 );\n\n\t#include <map_fragment>\n\t#include <alphamap_fragment>\n\t#include <alphatest_fragment>\n\n\tfloat dist = length( vWorldPosition - referencePosition );\n\tdist = ( dist - nearDistance ) / ( farDistance - nearDistance );\n\tdist = saturate( dist ); // clamp to [ 0, 1 ]\n\n\tgl_FragColor = packDepthToRGBA( dist );\n\n}\n"
}, function(e, t) {
    e.exports = "#define DISTANCE\n\nvarying vec3 vWorldPosition;\n\n#include <common>\n#include <uv_pars_vertex>\n#include <displacementmap_pars_vertex>\n#include <morphtarget_pars_vertex>\n#include <skinning_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\nvoid main() {\n\n\t#include <uv_vertex>\n\n\t#include <skinbase_vertex>\n\n\t#ifdef USE_DISPLACEMENTMAP\n\n\t\t#include <beginnormal_vertex>\n\t\t#include <morphnormal_vertex>\n\t\t#include <skinnormal_vertex>\n\n\t#endif\n\n\t#include <begin_vertex>\n\t#include <morphtarget_vertex>\n\t#include <skinning_vertex>\n\t#include <displacementmap_vertex>\n\t#include <project_vertex>\n\t#include <worldpos_vertex>\n\t#include <clipping_planes_vertex>\n\n\tvWorldPosition = worldPosition.xyz;\n\n}\n"
}, function(e, t) {
    e.exports = "uniform sampler2D tEquirect;\n\nvarying vec3 vWorldPosition;\n\n#include <common>\n\nvoid main() {\n\n\tvec3 direction = normalize( vWorldPosition );\n\n\tvec2 sampleUV;\n\n\tsampleUV.y = asin( clamp( direction.y, - 1.0, 1.0 ) ) * RECIPROCAL_PI + 0.5;\n\n\tsampleUV.x = atan( direction.z, direction.x ) * RECIPROCAL_PI2 + 0.5;\n\n\tgl_FragColor = texture2D( tEquirect, sampleUV );\n\n}\n"
}, function(e, t) {
    e.exports = "varying vec3 vWorldPosition;\n\n#include <common>\n\nvoid main() {\n\n\tvWorldPosition = transformDirection( position, modelMatrix );\n\n\t#include <begin_vertex>\n\t#include <project_vertex>\n\n}\n"
}, function(e, t) {
    e.exports = "uniform vec3 diffuse;\nuniform float opacity;\n\nuniform float dashSize;\nuniform float totalSize;\n\nvarying float vLineDistance;\n\n#include <common>\n#include <color_pars_fragment>\n#include <fog_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\nvoid main() {\n\n\t#include <clipping_planes_fragment>\n\n\tif ( mod( vLineDistance, totalSize ) > dashSize ) {\n\n\t\tdiscard;\n\n\t}\n\n\tvec3 outgoingLight = vec3( 0.0 );\n\tvec4 diffuseColor = vec4( diffuse, opacity );\n\n\t#include <logdepthbuf_fragment>\n\t#include <color_fragment>\n\n\toutgoingLight = diffuseColor.rgb; // simple shader\n\n\tgl_FragColor = vec4( outgoingLight, diffuseColor.a );\n\n\t#include <premultiplied_alpha_fragment>\n\t#include <tonemapping_fragment>\n\t#include <encodings_fragment>\n\t#include <fog_fragment>\n\n}\n"
}, function(e, t) {
    e.exports = "uniform float scale;\nattribute float lineDistance;\n\nvarying float vLineDistance;\n\n#include <common>\n#include <color_pars_vertex>\n#include <fog_pars_vertex>\n#include <logdepthbuf_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\nvoid main() {\n\n\t#include <color_vertex>\n\n\tvLineDistance = scale * lineDistance;\n\n\tvec4 mvPosition = modelViewMatrix * vec4( position, 1.0 );\n\tgl_Position = projectionMatrix * mvPosition;\n\n\t#include <logdepthbuf_vertex>\n\t#include <clipping_planes_vertex>\n\t#include <fog_vertex>\n\n}\n"
}, function(e, t) {
    e.exports = "uniform vec3 diffuse;\nuniform float opacity;\n\n#ifndef FLAT_SHADED\n\n\tvarying vec3 vNormal;\n\n#endif\n\n#include <common>\n#include <color_pars_fragment>\n#include <uv_pars_fragment>\n#include <uv2_pars_fragment>\n#include <map_pars_fragment>\n#include <alphamap_pars_fragment>\n#include <aomap_pars_fragment>\n#include <lightmap_pars_fragment>\n#include <envmap_pars_fragment>\n#include <fog_pars_fragment>\n#include <specularmap_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\nvoid main() {\n\n\t#include <clipping_planes_fragment>\n\n\tvec4 diffuseColor = vec4( diffuse, opacity );\n\n\t#include <logdepthbuf_fragment>\n\t#include <map_fragment>\n\t#include <color_fragment>\n\t#include <alphamap_fragment>\n\t#include <alphatest_fragment>\n\t#include <specularmap_fragment>\n\n\tReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );\n\n\t// accumulation (baked indirect lighting only)\n\t#ifdef USE_LIGHTMAP\n\n\t\treflectedLight.indirectDiffuse += texture2D( lightMap, vUv2 ).xyz * lightMapIntensity;\n\n\t#else\n\n\t\treflectedLight.indirectDiffuse += vec3( 1.0 );\n\n\t#endif\n\n\t// modulation\n\t#include <aomap_fragment>\n\n\treflectedLight.indirectDiffuse *= diffuseColor.rgb;\n\n\tvec3 outgoingLight = reflectedLight.indirectDiffuse;\n\n\t#include <envmap_fragment>\n\n\tgl_FragColor = vec4( outgoingLight, diffuseColor.a );\n\n\t#include <premultiplied_alpha_fragment>\n\t#include <tonemapping_fragment>\n\t#include <encodings_fragment>\n\t#include <fog_fragment>\n\n}\n"
}, function(e, t) {
    e.exports = "#include <common>\n#include <uv_pars_vertex>\n#include <uv2_pars_vertex>\n#include <envmap_pars_vertex>\n#include <color_pars_vertex>\n#include <fog_pars_vertex>\n#include <morphtarget_pars_vertex>\n#include <skinning_pars_vertex>\n#include <logdepthbuf_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\nvoid main() {\n\n\t#include <uv_vertex>\n\t#include <uv2_vertex>\n\t#include <color_vertex>\n\t#include <skinbase_vertex>\n\n\t#ifdef USE_ENVMAP\n\n\t#include <beginnormal_vertex>\n\t#include <morphnormal_vertex>\n\t#include <skinnormal_vertex>\n\t#include <defaultnormal_vertex>\n\n\t#endif\n\n\t#include <begin_vertex>\n\t#include <morphtarget_vertex>\n\t#include <skinning_vertex>\n\t#include <project_vertex>\n\t#include <logdepthbuf_vertex>\n\n\t#include <worldpos_vertex>\n\t#include <clipping_planes_vertex>\n\t#include <envmap_vertex>\n\t#include <fog_vertex>\n\n}\n"
}, function(e, t) {
    e.exports = "uniform vec3 diffuse;\nuniform vec3 emissive;\nuniform float opacity;\n\nvarying vec3 vLightFront;\n\n#ifdef DOUBLE_SIDED\n\n\tvarying vec3 vLightBack;\n\n#endif\n\n#include <common>\n#include <packing>\n#include <dithering_pars_fragment>\n#include <color_pars_fragment>\n#include <uv_pars_fragment>\n#include <uv2_pars_fragment>\n#include <map_pars_fragment>\n#include <alphamap_pars_fragment>\n#include <aomap_pars_fragment>\n#include <lightmap_pars_fragment>\n#include <emissivemap_pars_fragment>\n#include <envmap_pars_fragment>\n#include <bsdfs>\n#include <lights_pars_begin>\n#include <fog_pars_fragment>\n#include <shadowmap_pars_fragment>\n#include <shadowmask_pars_fragment>\n#include <specularmap_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\nvoid main() {\n\n\t#include <clipping_planes_fragment>\n\n\tvec4 diffuseColor = vec4( diffuse, opacity );\n\tReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );\n\tvec3 totalEmissiveRadiance = emissive;\n\n\t#include <logdepthbuf_fragment>\n\t#include <map_fragment>\n\t#include <color_fragment>\n\t#include <alphamap_fragment>\n\t#include <alphatest_fragment>\n\t#include <specularmap_fragment>\n\t#include <emissivemap_fragment>\n\n\t// accumulation\n\treflectedLight.indirectDiffuse = getAmbientLightIrradiance( ambientLightColor );\n\n\t#include <lightmap_fragment>\n\n\treflectedLight.indirectDiffuse *= BRDF_Diffuse_Lambert( diffuseColor.rgb );\n\n\t#ifdef DOUBLE_SIDED\n\n\t\treflectedLight.directDiffuse = ( gl_FrontFacing ) ? vLightFront : vLightBack;\n\n\t#else\n\n\t\treflectedLight.directDiffuse = vLightFront;\n\n\t#endif\n\n\treflectedLight.directDiffuse *= BRDF_Diffuse_Lambert( diffuseColor.rgb ) * getShadowMask();\n\n\t// modulation\n\t#include <aomap_fragment>\n\n\tvec3 outgoingLight = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse + totalEmissiveRadiance;\n\n\t#include <envmap_fragment>\n\n\tgl_FragColor = vec4( outgoingLight, diffuseColor.a );\n\n\t#include <tonemapping_fragment>\n\t#include <encodings_fragment>\n\t#include <fog_fragment>\n\t#include <premultiplied_alpha_fragment>\n\t#include <dithering_fragment>\n\n}\n"
}, function(e, t) {
    e.exports = "#define LAMBERT\n\nvarying vec3 vLightFront;\n\n#ifdef DOUBLE_SIDED\n\n\tvarying vec3 vLightBack;\n\n#endif\n\n#include <common>\n#include <uv_pars_vertex>\n#include <uv2_pars_vertex>\n#include <envmap_pars_vertex>\n#include <bsdfs>\n#include <lights_pars_begin>\n#include <color_pars_vertex>\n#include <fog_pars_vertex>\n#include <morphtarget_pars_vertex>\n#include <skinning_pars_vertex>\n#include <shadowmap_pars_vertex>\n#include <logdepthbuf_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\nvoid main() {\n\n\t#include <uv_vertex>\n\t#include <uv2_vertex>\n\t#include <color_vertex>\n\n\t#include <beginnormal_vertex>\n\t#include <morphnormal_vertex>\n\t#include <skinbase_vertex>\n\t#include <skinnormal_vertex>\n\t#include <defaultnormal_vertex>\n\n\t#include <begin_vertex>\n\t#include <morphtarget_vertex>\n\t#include <skinning_vertex>\n\t#include <project_vertex>\n\t#include <logdepthbuf_vertex>\n\t#include <clipping_planes_vertex>\n\n\t#include <worldpos_vertex>\n\t#include <envmap_vertex>\n\t#include <lights_lambert_vertex>\n\t#include <shadowmap_vertex>\n\t#include <fog_vertex>\n\n}\n"
}, function(e, t) {
    e.exports = "#define MATCAP\n\nuniform vec3 diffuse;\nuniform float opacity;\nuniform sampler2D matcap;\n\nvarying vec3 vViewPosition;\n\n#ifndef FLAT_SHADED\n\n\tvarying vec3 vNormal;\n\n#endif\n\n#include <common>\n#include <uv_pars_fragment>\n#include <map_pars_fragment>\n#include <alphamap_pars_fragment>\n\n#include <fog_pars_fragment>\n#include <bumpmap_pars_fragment>\n#include <normalmap_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\nvoid main() {\n\n\t#include <clipping_planes_fragment>\n\n\tvec4 diffuseColor = vec4( diffuse, opacity );\n\n\t#include <logdepthbuf_fragment>\n\t#include <map_fragment>\n\t#include <alphamap_fragment>\n\t#include <alphatest_fragment>\n\t#include <normal_fragment_begin>\n\t#include <normal_fragment_maps>\n\n\tvec3 viewDir = normalize( vViewPosition );\n\tvec3 x = normalize( vec3( viewDir.z, 0.0, - viewDir.x ) );\n\tvec3 y = cross( viewDir, x );\n\tvec2 uv = vec2( dot( x, normal ), dot( y, normal ) ) * 0.495 + 0.5; // 0.495 to remove artifacts caused by undersized matcap disks\n\n\tvec4 matcapColor = texture2D( matcap, uv );\n\n\tmatcapColor = matcapTexelToLinear( matcapColor );\n\n\tvec3 outgoingLight = diffuseColor.rgb * matcapColor.rgb;\n\n\tgl_FragColor = vec4( outgoingLight, diffuseColor.a );\n\n\t#include <premultiplied_alpha_fragment>\n\t#include <tonemapping_fragment>\n\t#include <encodings_fragment>\n\t#include <fog_fragment>\n\n}\n"
}, function(e, t) {
    e.exports = "#define MATCAP\n\nvarying vec3 vViewPosition;\n\n#ifndef FLAT_SHADED\n\n\tvarying vec3 vNormal;\n\n#endif\n\n#include <common>\n#include <uv_pars_vertex>\n#include <displacementmap_pars_vertex>\n#include <fog_pars_vertex>\n#include <morphtarget_pars_vertex>\n#include <skinning_pars_vertex>\n\n#include <logdepthbuf_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\nvoid main() {\n\n\t#include <uv_vertex>\n\n\t#include <beginnormal_vertex>\n\t#include <morphnormal_vertex>\n\t#include <skinbase_vertex>\n\t#include <skinnormal_vertex>\n\t#include <defaultnormal_vertex>\n\n\t#ifndef FLAT_SHADED // Normal computed with derivatives when FLAT_SHADED\n\n\t\tvNormal = normalize( transformedNormal );\n\n\t#endif\n\n\t#include <begin_vertex>\n\t#include <morphtarget_vertex>\n\t#include <skinning_vertex>\n\t#include <displacementmap_vertex>\n\t#include <project_vertex>\n\n\t#include <logdepthbuf_vertex>\n\t#include <clipping_planes_vertex>\n\t#include <fog_vertex>\n\n\tvViewPosition = - mvPosition.xyz;\n\n}\n"
}, function(e, t) {
    e.exports = "#define PHONG\n\nuniform vec3 diffuse;\nuniform vec3 emissive;\nuniform vec3 specular;\nuniform float shininess;\nuniform float opacity;\n\n#include <common>\n#include <packing>\n#include <dithering_pars_fragment>\n#include <color_pars_fragment>\n#include <uv_pars_fragment>\n#include <uv2_pars_fragment>\n#include <map_pars_fragment>\n#include <alphamap_pars_fragment>\n#include <aomap_pars_fragment>\n#include <lightmap_pars_fragment>\n#include <emissivemap_pars_fragment>\n#include <envmap_pars_fragment>\n#include <gradientmap_pars_fragment>\n#include <fog_pars_fragment>\n#include <bsdfs>\n#include <lights_pars_begin>\n#include <lights_phong_pars_fragment>\n#include <shadowmap_pars_fragment>\n#include <bumpmap_pars_fragment>\n#include <normalmap_pars_fragment>\n#include <specularmap_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\nvoid main() {\n\n\t#include <clipping_planes_fragment>\n\n\tvec4 diffuseColor = vec4( diffuse, opacity );\n\tReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );\n\tvec3 totalEmissiveRadiance = emissive;\n\n\t#include <logdepthbuf_fragment>\n\t#include <map_fragment>\n\t#include <color_fragment>\n\t#include <alphamap_fragment>\n\t#include <alphatest_fragment>\n\t#include <specularmap_fragment>\n\t#include <normal_fragment_begin>\n\t#include <normal_fragment_maps>\n\t#include <emissivemap_fragment>\n\n\t// accumulation\n\t#include <lights_phong_fragment>\n\t#include <lights_fragment_begin>\n\t#include <lights_fragment_maps>\n\t#include <lights_fragment_end>\n\n\t// modulation\n\t#include <aomap_fragment>\n\n\tvec3 outgoingLight = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse + reflectedLight.directSpecular + reflectedLight.indirectSpecular + totalEmissiveRadiance;\n\n\t#include <envmap_fragment>\n\n\tgl_FragColor = vec4( outgoingLight, diffuseColor.a );\n\n\t#include <tonemapping_fragment>\n\t#include <encodings_fragment>\n\t#include <fog_fragment>\n\t#include <premultiplied_alpha_fragment>\n\t#include <dithering_fragment>\n\n}\n"
}, function(e, t) {
    e.exports = "#define PHONG\n\nvarying vec3 vViewPosition;\n\n#ifndef FLAT_SHADED\n\n\tvarying vec3 vNormal;\n\n#endif\n\n#include <common>\n#include <uv_pars_vertex>\n#include <uv2_pars_vertex>\n#include <displacementmap_pars_vertex>\n#include <envmap_pars_vertex>\n#include <color_pars_vertex>\n#include <fog_pars_vertex>\n#include <morphtarget_pars_vertex>\n#include <skinning_pars_vertex>\n#include <shadowmap_pars_vertex>\n#include <logdepthbuf_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\nvoid main() {\n\n\t#include <uv_vertex>\n\t#include <uv2_vertex>\n\t#include <color_vertex>\n\n\t#include <beginnormal_vertex>\n\t#include <morphnormal_vertex>\n\t#include <skinbase_vertex>\n\t#include <skinnormal_vertex>\n\t#include <defaultnormal_vertex>\n\n#ifndef FLAT_SHADED // Normal computed with derivatives when FLAT_SHADED\n\n\tvNormal = normalize( transformedNormal );\n\n#endif\n\n\t#include <begin_vertex>\n\t#include <morphtarget_vertex>\n\t#include <skinning_vertex>\n\t#include <displacementmap_vertex>\n\t#include <project_vertex>\n\t#include <logdepthbuf_vertex>\n\t#include <clipping_planes_vertex>\n\n\tvViewPosition = - mvPosition.xyz;\n\n\t#include <worldpos_vertex>\n\t#include <envmap_vertex>\n\t#include <shadowmap_vertex>\n\t#include <fog_vertex>\n\n}\n"
}, function(e, t) {
    e.exports = "#define PHYSICAL\n\nuniform vec3 diffuse;\nuniform vec3 emissive;\nuniform float roughness;\nuniform float metalness;\nuniform float opacity;\n\n#ifndef STANDARD\n\tuniform float clearCoat;\n\tuniform float clearCoatRoughness;\n#endif\n\nvarying vec3 vViewPosition;\n\n#ifndef FLAT_SHADED\n\n\tvarying vec3 vNormal;\n\n#endif\n\n#include <common>\n#include <packing>\n#include <dithering_pars_fragment>\n#include <color_pars_fragment>\n#include <uv_pars_fragment>\n#include <uv2_pars_fragment>\n#include <map_pars_fragment>\n#include <alphamap_pars_fragment>\n#include <aomap_pars_fragment>\n#include <lightmap_pars_fragment>\n#include <emissivemap_pars_fragment>\n#include <bsdfs>\n#include <cube_uv_reflection_fragment>\n#include <envmap_pars_fragment>\n#include <envmap_physical_pars_fragment>\n#include <fog_pars_fragment>\n#include <lights_pars_begin>\n#include <lights_physical_pars_fragment>\n#include <shadowmap_pars_fragment>\n#include <bumpmap_pars_fragment>\n#include <normalmap_pars_fragment>\n#include <roughnessmap_pars_fragment>\n#include <metalnessmap_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\nvoid main() {\n\n\t#include <clipping_planes_fragment>\n\n\tvec4 diffuseColor = vec4( diffuse, opacity );\n\tReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );\n\tvec3 totalEmissiveRadiance = emissive;\n\n\t#include <logdepthbuf_fragment>\n\t#include <map_fragment>\n\t#include <color_fragment>\n\t#include <alphamap_fragment>\n\t#include <alphatest_fragment>\n\t#include <roughnessmap_fragment>\n\t#include <metalnessmap_fragment>\n\t#include <normal_fragment_begin>\n\t#include <normal_fragment_maps>\n\t#include <emissivemap_fragment>\n\n\t// accumulation\n\t#include <lights_physical_fragment>\n\t#include <lights_fragment_begin>\n\t#include <lights_fragment_maps>\n\t#include <lights_fragment_end>\n\n\t// modulation\n\t#include <aomap_fragment>\n\n\tvec3 outgoingLight = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse + reflectedLight.directSpecular + reflectedLight.indirectSpecular + totalEmissiveRadiance;\n\n\tgl_FragColor = vec4( outgoingLight, diffuseColor.a );\n\n\t#include <tonemapping_fragment>\n\t#include <encodings_fragment>\n\t#include <fog_fragment>\n\t#include <premultiplied_alpha_fragment>\n\t#include <dithering_fragment>\n\n}\n"
}, function(e, t) {
    e.exports = "#define PHYSICAL\n\nvarying vec3 vViewPosition;\n\n#ifndef FLAT_SHADED\n\n\tvarying vec3 vNormal;\n\n#endif\n\n#include <common>\n#include <uv_pars_vertex>\n#include <uv2_pars_vertex>\n#include <displacementmap_pars_vertex>\n#include <color_pars_vertex>\n#include <fog_pars_vertex>\n#include <morphtarget_pars_vertex>\n#include <skinning_pars_vertex>\n#include <shadowmap_pars_vertex>\n#include <logdepthbuf_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\nvoid main() {\n\n\t#include <uv_vertex>\n\t#include <uv2_vertex>\n\t#include <color_vertex>\n\n\t#include <beginnormal_vertex>\n\t#include <morphnormal_vertex>\n\t#include <skinbase_vertex>\n\t#include <skinnormal_vertex>\n\t#include <defaultnormal_vertex>\n\n#ifndef FLAT_SHADED // Normal computed with derivatives when FLAT_SHADED\n\n\tvNormal = normalize( transformedNormal );\n\n#endif\n\n\t#include <begin_vertex>\n\t#include <morphtarget_vertex>\n\t#include <skinning_vertex>\n\t#include <displacementmap_vertex>\n\t#include <project_vertex>\n\t#include <logdepthbuf_vertex>\n\t#include <clipping_planes_vertex>\n\n\tvViewPosition = - mvPosition.xyz;\n\n\t#include <worldpos_vertex>\n\t#include <shadowmap_vertex>\n\t#include <fog_vertex>\n\n}\n"
}, function(e, t) {
    e.exports = "#define NORMAL\n\nuniform float opacity;\n\n#if defined( FLAT_SHADED ) || defined( USE_BUMPMAP ) || ( defined( USE_NORMALMAP ) && ! defined( OBJECTSPACE_NORMALMAP ) )\n\n\tvarying vec3 vViewPosition;\n\n#endif\n\n#ifndef FLAT_SHADED\n\n\tvarying vec3 vNormal;\n\n#endif\n\n#include <packing>\n#include <uv_pars_fragment>\n#include <bumpmap_pars_fragment>\n#include <normalmap_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n\nvoid main() {\n\n\t#include <logdepthbuf_fragment>\n\t#include <normal_fragment_begin>\n\t#include <normal_fragment_maps>\n\n\tgl_FragColor = vec4( packNormalToRGB( normal ), opacity );\n\n}\n"
}, function(e, t) {
    e.exports = "#define NORMAL\n\n#if defined( FLAT_SHADED ) || defined( USE_BUMPMAP ) || ( defined( USE_NORMALMAP ) && ! defined( OBJECTSPACE_NORMALMAP ) )\n\n\tvarying vec3 vViewPosition;\n\n#endif\n\n#ifndef FLAT_SHADED\n\n\tvarying vec3 vNormal;\n\n#endif\n\n#include <uv_pars_vertex>\n#include <displacementmap_pars_vertex>\n#include <morphtarget_pars_vertex>\n#include <skinning_pars_vertex>\n#include <logdepthbuf_pars_vertex>\n\nvoid main() {\n\n\t#include <uv_vertex>\n\n\t#include <beginnormal_vertex>\n\t#include <morphnormal_vertex>\n\t#include <skinbase_vertex>\n\t#include <skinnormal_vertex>\n\t#include <defaultnormal_vertex>\n\n#ifndef FLAT_SHADED // Normal computed with derivatives when FLAT_SHADED\n\n\tvNormal = normalize( transformedNormal );\n\n#endif\n\n\t#include <begin_vertex>\n\t#include <morphtarget_vertex>\n\t#include <skinning_vertex>\n\t#include <displacementmap_vertex>\n\t#include <project_vertex>\n\t#include <logdepthbuf_vertex>\n\n#if defined( FLAT_SHADED ) || defined( USE_BUMPMAP ) || ( defined( USE_NORMALMAP ) && ! defined( OBJECTSPACE_NORMALMAP ) )\n\n\tvViewPosition = - mvPosition.xyz;\n\n#endif\n\n}\n"
}, function(e, t) {
    e.exports = "uniform vec3 diffuse;\nuniform float opacity;\n\n#include <common>\n#include <color_pars_fragment>\n#include <map_particle_pars_fragment>\n#include <fog_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\nvoid main() {\n\n\t#include <clipping_planes_fragment>\n\n\tvec3 outgoingLight = vec3( 0.0 );\n\tvec4 diffuseColor = vec4( diffuse, opacity );\n\n\t#include <logdepthbuf_fragment>\n\t#include <map_particle_fragment>\n\t#include <color_fragment>\n\t#include <alphatest_fragment>\n\n\toutgoingLight = diffuseColor.rgb;\n\n\tgl_FragColor = vec4( outgoingLight, diffuseColor.a );\n\n\t#include <premultiplied_alpha_fragment>\n\t#include <tonemapping_fragment>\n\t#include <encodings_fragment>\n\t#include <fog_fragment>\n\n}\n"
}, function(e, t) {
    e.exports = "uniform float size;\nuniform float scale;\n\n#include <common>\n#include <color_pars_vertex>\n#include <fog_pars_vertex>\n#include <morphtarget_pars_vertex>\n#include <logdepthbuf_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\nvoid main() {\n\n\t#include <color_vertex>\n\t#include <begin_vertex>\n\t#include <morphtarget_vertex>\n\t#include <project_vertex>\n\n\tgl_PointSize = size;\n\n\t#ifdef USE_SIZEATTENUATION\n\n\t\tbool isPerspective = ( projectionMatrix[ 2 ][ 3 ] == - 1.0 );\n\n\t\tif ( isPerspective ) gl_PointSize *= ( scale / - mvPosition.z );\n\n\t#endif\n\n\t#include <logdepthbuf_vertex>\n\t#include <clipping_planes_vertex>\n\t#include <worldpos_vertex>\n\t#include <fog_vertex>\n\n}\n"
}, function(e, t) {
    e.exports = "uniform vec3 color;\nuniform float opacity;\n\n#include <common>\n#include <packing>\n#include <fog_pars_fragment>\n#include <bsdfs>\n#include <lights_pars_begin>\n#include <shadowmap_pars_fragment>\n#include <shadowmask_pars_fragment>\n\nvoid main() {\n\n\tgl_FragColor = vec4( color, opacity * ( 1.0 - getShadowMask() ) );\n\n\t#include <fog_fragment>\n\n}\n"
}, function(e, t) {
    e.exports = "#include <fog_pars_vertex>\n#include <shadowmap_pars_vertex>\n\nvoid main() {\n\n\t#include <begin_vertex>\n\t#include <project_vertex>\n\t#include <worldpos_vertex>\n\t#include <shadowmap_vertex>\n\t#include <fog_vertex>\n\n}\n"
}, function(e, t) {
    e.exports = "uniform vec3 diffuse;\nuniform float opacity;\n\n#include <common>\n#include <uv_pars_fragment>\n#include <map_pars_fragment>\n#include <fog_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\nvoid main() {\n\n\t#include <clipping_planes_fragment>\n\n\tvec3 outgoingLight = vec3( 0.0 );\n\tvec4 diffuseColor = vec4( diffuse, opacity );\n\n\t#include <logdepthbuf_fragment>\n\t#include <map_fragment>\n\t#include <alphatest_fragment>\n\n\toutgoingLight = diffuseColor.rgb;\n\n\tgl_FragColor = vec4( outgoingLight, diffuseColor.a );\n\n\t#include <tonemapping_fragment>\n\t#include <encodings_fragment>\n\t#include <fog_fragment>\n\n}\n"
}, function(e, t) {
    e.exports = "uniform float rotation;\nuniform vec2 center;\n\n#include <common>\n#include <uv_pars_vertex>\n#include <fog_pars_vertex>\n#include <logdepthbuf_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\nvoid main() {\n\n\t#include <uv_vertex>\n\n\tvec4 mvPosition = modelViewMatrix * vec4( 0.0, 0.0, 0.0, 1.0 );\n\n\tvec2 scale;\n\tscale.x = length( vec3( modelMatrix[ 0 ].x, modelMatrix[ 0 ].y, modelMatrix[ 0 ].z ) );\n\tscale.y = length( vec3( modelMatrix[ 1 ].x, modelMatrix[ 1 ].y, modelMatrix[ 1 ].z ) );\n\n\t#ifndef USE_SIZEATTENUATION\n\n\t\tbool isPerspective = ( projectionMatrix[ 2 ][ 3 ] == - 1.0 );\n\n\t\tif ( isPerspective ) scale *= - mvPosition.z;\n\n\t#endif\n\n\tvec2 alignedPosition = ( position.xy - ( center - vec2( 0.5 ) ) ) * scale;\n\n\tvec2 rotatedPosition;\n\trotatedPosition.x = cos( rotation ) * alignedPosition.x - sin( rotation ) * alignedPosition.y;\n\trotatedPosition.y = sin( rotation ) * alignedPosition.x + cos( rotation ) * alignedPosition.y;\n\n\tmvPosition.xy += rotatedPosition;\n\n\tgl_Position = projectionMatrix * mvPosition;\n\n\t#include <logdepthbuf_vertex>\n\t#include <clipping_planes_vertex>\n\t#include <fog_vertex>\n\n}\n"
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(195),
        a = n(6),
        o = new r.Vector2;
    var s;
    ! function(e) {
        e[e.Single = 0] = "Single", e[e.HorizontalSplit = 1] = "HorizontalSplit", e[e.VerticalSplit = 2] = "VerticalSplit", e[e.Quad = 3] = "Quad"
    }(s = t.EViewportLayoutMode || (t.EViewportLayoutMode = {}));
    class l extends a.default {
        constructor() {
            super(), this._layoutMode = -1, this._horizontalSplit = .5, this._verticalSplit = .5, this.canvasWidth = 100, this.canvasHeight = 100, this.viewports = [], this.activeViewport = null, this.addEvent("layout"), this.layoutMode = s.Single
        }
        forEachViewport(e) {
            this.viewports.forEach(e)
        }
        get layoutMode() {
            return this._layoutMode
        }
        set layoutMode(e) {
            if (e === this._layoutMode) return;
            this._layoutMode = e;
            const t = this.viewports,
                n = this._horizontalSplit,
                r = this._verticalSplit;
            switch (e) {
                case s.Single:
                    t.length = 1, t[0] = new i.default(0, 0, 1, 1);
                    break;
                case s.HorizontalSplit:
                    t.length = 2, t[0] = new i.default(0, 0, n, 1), t[1] = new i.default(n, 0, 1 - n, 1);
                    break;
                case s.VerticalSplit:
                    t.length = 2, t[0] = new i.default(0, 0, 1, r), t[1] = new i.default(0, r, 1, 1 - r);
                    break;
                case s.Quad:
                    t.length = 4, t[0] = new i.default(0, 0, n, r), t[1] = new i.default(n, 0, 1 - n, r).setCamera(i.EViewportCameraType.Orthographic, i.EViewportCameraView.Top), t[2] = new i.default(0, r, n, 1 - r).setCamera(i.EViewportCameraType.Orthographic, i.EViewportCameraView.Left), t[3] = new i.default(n, r, 1 - n, 1 - r).setCamera(i.EViewportCameraType.Orthographic, i.EViewportCameraView.Front)
            }
            t.forEach((e, t) => {
                e.index = t, e.setCanvasSize(this.canvasWidth, this.canvasHeight)
            }), this.emit("layout", {
                viewports: t,
                layoutMode: e
            })
        }
        get horizontalSplit() {
            return this._horizontalSplit
        }
        get verticalSplit() {
            return this._verticalSplit
        }
        setSplit(e, t) {
            const n = this.viewports,
                r = this._layoutMode;
            switch (this._horizontalSplit = e, this._verticalSplit = t, r) {
                case s.HorizontalSplit:
                    n[0].set(0, 0, e, 1), n[1].set(e, 0, 1 - e, 1);
                    break;
                case s.VerticalSplit:
                    n[0].set(0, 0, 1, t), n[1].set(0, t, 1, 1 - t);
                    break;
                case s.Quad:
                    n[0].set(0, 0, e, t), n[1].set(e, 0, 1 - e, t), n[2].set(0, t, e, 1 - t), n[3].set(e, t, 1 - e, 1 - t)
            }
        }
        setCanvasSize(e, t) {
            this.canvasWidth = e, this.canvasHeight = t, this.viewports.forEach(n => n.setCanvasSize(e, t))
        }
        onPointer(e) {
            const t = e,
                n = t.originalEvent.currentTarget.getBoundingClientRect(),
                r = t.centerX - n.left,
                i = t.centerY - n.top;
            return 0 === e.downPointerCount || e.isPrimary && "down" === e.type ? this.activeViewport = t.viewport = this.viewports.find(e => e.isPointInside(r, i)) : t.viewport = this.activeViewport, t.viewport ? (t.viewport.getDeviceCoords(r, i, o), t.deviceX = o.x, t.deviceY = o.y) : (t.deviceX = 0, t.deviceY = 0), !!this.next && this.next.onPointer(t)
        }
        onTrigger(e) {
            const t = e,
                n = t.originalEvent.currentTarget.getBoundingClientRect(),
                r = t.centerX - n.left,
                i = t.centerY - n.top;
            return t.viewport = this.viewports.find(e => e.isPointInside(r, i)) || null, t.viewport ? (t.viewport.getDeviceCoords(r, i, o), t.deviceX = o.x, t.deviceY = o.y) : (t.deviceX = 0, t.deviceY = 0), !!this.next && this.next.onTrigger(t)
        }
    }
    l.type = "Viewports", t.default = l
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(14);
    class i extends r.default {
        constructor() {
            super(...arguments), this.actions = null
        }
        create() {
            super.create()
        }
        createActions(e) {
            const t = {
                setInputValue: e.register({
                    name: "Set Value",
                    do: this.setInputValue,
                    target: this
                })
            };
            return this.actions = t, t
        }
        addInputListener(e, t, n, r) {
            this.getSafeComponent(e).in(t).on("value", n, r)
        }
        removeInputListener(e, t, n, r) {
            this.getSafeComponent(e).in(t).off("value", n, r)
        }
        addOutputListener(e, t, n, r) {
            this.getSafeComponent(e).out(t).on("value", n, r)
        }
        removeOutputListener(e, t, n, r) {
            this.getSafeComponent(e).out(t).off("value", n, r)
        }
        getInputValue(e, t) {
            return this.getSafeComponent(e).in(t).value
        }
        getOutputValue(e, t) {
            return this.getSafeComponent(e).out(t).value
        }
        setInputValue(e, t, n) {
            this.getSafeComponent(e).in(t).setValue(n)
        }
        getSafeComponent(e) {
            const t = this.getComponent(e);
            if (!t) throw new Error(`SystemController, component type not found: ${e}`);
            return t
        }
    }
    i.type = "SystemController", t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(4),
        a = n(11),
        o = n(9);
    t.EShaderMode = o.EShaderMode;
    const s = n(16),
        l = new r.Color;
    class c extends a.default {
        constructor() {
            super(...arguments), this.ins = this.makeProps({
                col: i.default.ColorRGB("Background.Color", [0, 0, 0]),
                sha: i.default.Enum("Shader.Mode", o.EShaderMode, o.EShaderMode.Default)
            }), this._scene = new r.Scene
        }
        get scene() {
            return this._scene
        }
        get children() {
            return this._children || []
        }
        update() {
            const {
                col: e,
                sha: t
            } = this.ins;
            if (e.changed && (l.fromArray(e.value), this._scene.background = l), t.changed) {
                const e = i.default.getEnumEntry(o.EShaderMode, t.value);
                this.getComponentsInSubtree(s.default).forEach(t => t.setShaderMode(e))
            }
        }
        addChild(e) {
            super.addChild(e), this._scene.add(e.object3D)
        }
        removeChild(e) {
            this._scene.remove(e.object3D), super.removeChild(e)
        }
    }
    c.type = "Scene", t.default = c
}, function(e, t) {
    e.exports = ReactDOM
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(6),
        i = n(197);
    class a extends r.default {
        constructor(e) {
            super(), this.addEvent("change"), this.stack = [], this.pointer = -1, this.capacity = void 0 !== e ? e : a.defaultCapacity
        }
        register(e) {
            let t;
            t = "function" == typeof e ? e : t => new i.default(t, e);
            return (...e) => {
                const n = t(e);
                this.do(n)
            }
        }
        setCapacity(e) {
            for (this.capacity = e; this.stack.length > e;) this.stack.shift(), this.pointer--;
            this.pointer < 0 && (this.stack = [], this.pointer = -1)
        }
        do(e) {
            console.log(`Commander.do - '${e.name}'`), e.do(), e.canUndo() && (this.stack.splice(this.pointer + 1), this.stack.push(e), this.stack.length > this.capacity && this.stack.shift(), this.pointer = this.stack.length - 1, this.emit("change"))
        }
        undo() {
            if (this.pointer >= 0) {
                this.stack[this.pointer].undo(), this.pointer--, this.emit("change")
            }
        }
        redo() {
            if (this.pointer < this.stack.length - 1) {
                this.pointer++, this.stack[this.pointer].do(), this.emit("change")
            }
        }
        clear() {
            this.stack.length > 0 && (this.stack = [], this.pointer = -1, this.emit("change"))
        }
        canUndo() {
            return this.pointer >= 0
        }
        canRedo() {
            return this.pointer < this.stack.length - 1
        }
        getUndoText() {
            return this.pointer >= 0 ? "Undo " + this.stack[this.pointer].name : "Can't Undo"
        }
        getRedoText() {
            return this.pointer < this.stack.length - 1 ? "Redo " + this.stack[this.pointer + 1].name : "Can't Redo"
        }
    }
    a.defaultCapacity = 30, t.default = a
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = {
            flexShrink: 0,
            flexGrow: 0
        },
        a = function(e) {
            const {
                className: t,
                style: n,
                text: a,
                children: o
            } = e, s = Object.assign({}, i, n);
            return r.createElement("label", {
                className: t,
                style: s
            }, a, o)
        };
    a.defaultProps = {
        className: "ff-control ff-label"
    }, t.default = a
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    }), t.default = function e(t) {
        if (null === t || "object" != typeof t) return t;
        let n;
        if (Array.isArray(t)) {
            n = [];
            for (let r = 0, i = t.length; r < i; ++r) n[r] = e(t[r]);
            return n
        }
        if (void 0 !== t.BYTES_PER_ELEMENT) return t.slice();
        if (t instanceof Date) return new Date(t);
        if (t.constructor === Object) {
            n = {};
            for (let r in t) t.hasOwnProperty(r) && (n[r] = e(t[r]));
            return n
        }
        return t
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(12);
    t.default = class {
        constructor() {
            this.mode = "off", this.phase = "off", this.prevEvent = null, this.prevPinchDist = 0, this.deltaX = 0, this.deltaY = 0, this.deltaPinch = 0, this.deltaWheel = 0, this.deltaOrbit = {
                dX: 0,
                dY: 0,
                dScale: 1,
                dHead: 0,
                dPitch: 0,
                dRoll: 0
            }
        }
        getDeltaPose() {
            if ("off" === this.phase && 0 === this.deltaWheel) return null;
            if (0 !== this.deltaWheel && (this.deltaOrbit.dScale = .07 * this.deltaWheel + 1, this.deltaWheel = 0), "active" === this.phase) {
                if (0 === this.deltaX && 0 === this.deltaY && 1 === this.deltaPinch) return null;
                this.setDeltaOrbit(), this.deltaX = 0, this.deltaY = 0, this.deltaPinch = 1
            } else "release" === this.phase && (this.deltaX *= .85, this.deltaY *= .85, this.deltaPinch = 1, this.setDeltaOrbit(), Math.abs(this.deltaX) + Math.abs(this.deltaY) < .1 && (this.mode = "off", this.phase = "off"));
            return this.deltaOrbit
        }
        onPointer(e) {
            if (e.isPrimary)
                if ("down" === e.type) this.phase = "active";
                else {
                    if ("up" === e.type) return this.phase = "release", this.prevEvent = null, !0;
                    if (0 === e.downPointerCount) return !1
                }
            let t = this.prevEvent;
            if (this.prevEvent = e, "up" === e.type || "down" === e.type) {
                t = e;
                const n = this.getModeFromEvent(e);
                n !== this.mode && "down" === e.type && (this.mode = n)
            }
            if (this.deltaX += e.centerX - t.centerX, this.deltaY += e.centerY - t.centerY, 2 === e.activePointerCount) {
                const t = e.activePointerList,
                    n = t[1].clientX - t[0].clientX,
                    r = t[1].clientY - t[0].clientY,
                    i = Math.sqrt(n * n + r * r),
                    a = this.prevPinchDist || i;
                this.deltaPinch = a > 0 ? i / a : 1, this.prevPinchDist = a
            } else this.deltaPinch = 1, this.prevPinchDist = 0;
            return !0
        }
        onTrigger(e) {
            return "wheel" === e.type && (this.deltaWheel += r.default.limit(e.wheel, -1, 1), !0)
        }
        setDeltaOrbit() {
            const e = this.deltaOrbit;
            switch (e.dX = 0, e.dY = 0, e.dScale = 1, e.dHead = 0, e.dPitch = 0, e.dRoll = 0, this.mode) {
                case "orbit":
                    e.dHead = this.deltaX, e.dPitch = this.deltaY;
                    break;
                case "pan":
                    e.dX = this.deltaX, e.dY = this.deltaY;
                    break;
                case "roll":
                    e.dRoll = this.deltaX;
                    break;
                case "dolly":
                    e.dScale = .0075 * this.deltaY + 1;
                    break;
                case "pan-dolly":
                    e.dX = this.deltaX, e.dY = this.deltaY;
                    const t = .5 * (this.deltaPinch - 1) + 1;
                    e.dScale = 1 / t
            }
        }
        getModeFromEvent(e) {
            if ("mouse" === e.source) {
                const t = e.originalEvent.button;
                if (0 === t) return e.ctrlKey ? "pan" : e.altKey ? "dolly" : "orbit";
                if (2 === t) return e.altKey ? "roll" : "pan";
                if (1 === t) return "dolly"
            } else if ("touch" === e.source) {
                const t = e.activePointerCount;
                return 1 === t ? "orbit" : 2 === t ? "pan-dolly" : "pan"
            }
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = [function(e) {
            return e
        }, function(e, t) {
            for (let n = 0, r = t.length; n < r; ++n) t[n] = e[n];
            return t
        }],
        i = [function(e) {
            return !!e
        }, function(e, t) {
            for (let n = 0, r = t.length; n < r; ++n) t[n] = !!e[n];
            return t
        }],
        a = [function(e) {
            return String(e)
        }, function(e, t) {
            for (let n = 0, r = t.length; n < r; ++n) t[n] = String(e[n]);
            return t
        }],
        o = [function(e, t) {
            throw new Error(`illegal value conversion from ${typeof e} to ${typeof t}`)
        }, function(e, t, n) {
            throw new Error(`illegal array conversion from ${typeof e[0]} to ${typeof t[0]}`)
        }],
        s = {
            number: {
                number: r,
                boolean: i,
                string: a,
                object: o
            },
            boolean: {
                number: [function(e) {
                    return e ? 1 : 0
                }, function(e, t) {
                    for (let n = 0, r = t.length; n < r; ++n) t[n] = e[n] ? 1 : 0;
                    return t
                }],
                boolean: r,
                string: a,
                object: o
            },
            string: {
                number: [function(e) {
                    return parseFloat(e) || 0
                }, function(e, t) {
                    for (let n = 0, r = t.length; n < r; ++n) t[n] = parseFloat(e[n]) || 0;
                    return t
                }],
                boolean: i,
                string: r,
                object: o
            },
            object: {
                number: o,
                boolean: i,
                string: a,
                object: r
            }
        },
        l = {
            number: {
                number: !0,
                boolean: !0,
                string: !0,
                object: !1
            },
            boolean: {
                number: !0,
                boolean: !0,
                string: !0,
                object: !1
            },
            string: {
                number: !0,
                boolean: !0,
                string: !0,
                object: !1
            },
            object: {
                number: !1,
                boolean: !0,
                string: !0,
                object: !0
            }
        },
        c = [
            [function(e, t, n) {
                return n(e, t)
            }, function(e, t, n) {
                return t[0] = n(e), t
            }, function(e, t, n) {
                return t[1] = n(e), t
            }, function(e, t, n) {
                return t[2] = n(e), t
            }, function(e, t, n) {
                return t[3] = n(e), t
            }],
            [function(e, t, n) {
                return n(e[0])
            }, function(e, t, n) {
                return t[0] = n(e[0]), t
            }, function(e, t, n) {
                return t[1] = n(e[0]), t
            }, function(e, t, n) {
                return t[2] = n(e[0]), t
            }, function(e, t, n) {
                return t[3] = n(e[0]), t
            }],
            [function(e, t, n) {
                return n(e[1])
            }, function(e, t, n) {
                return t[0] = n(e[1]), t
            }, function(e, t, n) {
                return t[1] = n(e[1]), t
            }, function(e, t, n) {
                return t[2] = n(e[1]), t
            }, function(e, t, n) {
                return t[3] = n(e[1]), t
            }],
            [function(e, t, n) {
                return n(e[2])
            }, function(e, t, n) {
                return t[0] = n(e[2]), t
            }, function(e, t, n) {
                return t[1] = n(e[2]), t
            }, function(e, t, n) {
                return t[2] = n(e[2]), t
            }, function(e, t, n) {
                return t[3] = n(e[2]), t
            }],
            [function(e, t, n) {
                return n(e[3])
            }, function(e, t, n) {
                return t[0] = n(e[3]), t
            }, function(e, t, n) {
                return t[1] = n(e[3]), t
            }, function(e, t, n) {
                return t[2] = n(e[3]), t
            }, function(e, t, n) {
                return t[3] = n(e[3]), t
            }]
        ];
    t.getConversionFunction = function(e, t, n) {
        const r = n ? 1 : 0;
        return s[e][t][r]
    }, t.canConvert = function(e, t) {
        return l[e][t]
    }, t.getElementCopyFunction = function(e, t, n) {
        return -1 === e && -1 === t ? n : e <= 3 && t <= 3 ? c[e + 1][t + 1] : function(n, r, i) {
            return r[t] = i(n[e]), r
        }
    }, t.getMultiCopyFunction = function(e, t, n) {
        return !1 === e ? !1 === t ? n : function(e, t, r) {
            for (let r = 0, i = t.length; r < i; ++r) t[r] = n(e, t[r]);
            return t
        } : !1 === t ? function(e, t, r) {
            return e.length > 0 && (t = n(e[0], t)), t
        } : function(e, t, r) {
            for (let r = 0, i = e.length, a = t.length; r < a; ++r) t[r] = n(e[r % i], t[r]);
            return t
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    class r {
        constructor(e) {
            this.object = e || null
        }
        get type() {
            return this.constructor.type
        }
        toString() {
            return this.type
        }
    }
    r.type = "Object", t.default = r
}, function(e, t, n) {
    "use strict";
    var r = n(7);
    e.exports = function(e) {
        r.copy(e, this)
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t) {
        t || (t = {}), "function" == typeof t && (t = {
            cmp: t
        });
        var n = "boolean" == typeof t.cycles && t.cycles,
            r = t.cmp && function(e) {
                return function(t) {
                    return function(n, r) {
                        var i = {
                                key: n,
                                value: t[n]
                            },
                            a = {
                                key: r,
                                value: t[r]
                            };
                        return e(i, a)
                    }
                }
            }(t.cmp),
            i = [];
        return function e(t) {
            if (t && t.toJSON && "function" == typeof t.toJSON && (t = t.toJSON()), void 0 !== t) {
                if ("number" == typeof t) return isFinite(t) ? "" + t : "null";
                if ("object" != typeof t) return JSON.stringify(t);
                var a, o;
                if (Array.isArray(t)) {
                    for (o = "[", a = 0; a < t.length; a++) a && (o += ","), o += e(t[a]) || "null";
                    return o + "]"
                }
                if (null === t) return "null";
                if (-1 !== i.indexOf(t)) {
                    if (n) return JSON.stringify("__cycle__");
                    throw new TypeError("Converting circular structure to JSON")
                }
                var s = i.push(t) - 1,
                    l = Object.keys(t).sort(r && r(t));
                for (o = "", a = 0; a < l.length; a++) {
                    var c = l[a],
                        d = e(t[c]);
                    d && (o && (o += ","), o += JSON.stringify(c) + ":" + d)
                }
                return i.splice(s, 1), "{" + o + "}"
            }
        }(e)
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = "",
            i = !0 === e.schema.$async,
            a = e.util.schemaHasRulesExcept(e.schema, e.RULES.all, "$ref"),
            o = e.self._getId(e.schema);
        if (e.isTop && (r += " var validate = ", i && (e.async = !0, r += "async "), r += "function(data, dataPath, parentData, parentDataProperty, rootData) { 'use strict'; ", o && (e.opts.sourceCode || e.opts.processCode) && (r += " /*# sourceURL=" + o + " */ ")), "boolean" == typeof e.schema || !a && !e.schema.$ref) {
            var s = e.level,
                l = e.dataLevel,
                c = e.schema["false schema"],
                d = e.schemaPath + e.util.getProperty("false schema"),
                u = e.errSchemaPath + "/false schema",
                h = !e.opts.allErrors,
                p = "data" + (l || ""),
                f = "valid" + s;
            if (!1 === e.schema) {
                e.isTop ? h = !0 : r += " var " + f + " = false; ", (Q = Q || []).push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: '" + (g || "false schema") + "' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(u) + " , params: {} ", !1 !== e.opts.messages && (r += " , message: 'boolean schema is false' "), e.opts.verbose && (r += " , schema: false , parentSchema: validate.schema" + e.schemaPath + " , data: " + p + " "), r += " } ") : r += " {} ";
                var m = r;
                r = Q.pop(), !e.compositeRule && h ? e.async ? r += " throw new ValidationError([" + m + "]); " : r += " validate.errors = [" + m + "]; return false; " : r += " var err = " + m + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; "
            } else e.isTop ? r += i ? " return data; " : " validate.errors = null; return true; " : r += " var " + f + " = true; ";
            return e.isTop && (r += " }; return validate; "), r
        }
        if (e.isTop) {
            var v = e.isTop;
            s = e.level = 0, l = e.dataLevel = 0, p = "data";
            e.rootId = e.resolve.fullPath(e.self._getId(e.root.schema)), e.baseId = e.baseId || e.rootId, delete e.isTop, e.dataPathArr = [void 0], r += " var vErrors = null; ", r += " var errors = 0;     ", r += " if (rootData === undefined) rootData = data; "
        } else {
            s = e.level, p = "data" + ((l = e.dataLevel) || "");
            if (o && (e.baseId = e.resolve.url(e.baseId, o)), i && !e.async) throw new Error("async schema in sync schema");
            r += " var errs_" + s + " = errors;"
        }
        f = "valid" + s, h = !e.opts.allErrors;
        var g, y = "",
            _ = "",
            x = e.schema.type,
            E = Array.isArray(x);
        if (E && 1 == x.length && (x = x[0], E = !1), e.schema.$ref && a) {
            if ("fail" == e.opts.extendRefs) throw new Error('$ref: validation keywords used in schema at path "' + e.errSchemaPath + '" (see option extendRefs)');
            !0 !== e.opts.extendRefs && (a = !1, e.logger.warn('$ref: keywords ignored in schema at path "' + e.errSchemaPath + '"'))
        }
        if (e.schema.$comment && e.opts.$comment && (r += " " + e.RULES.all.$comment.code(e, "$comment")), x) {
            if (e.opts.coerceTypes) var b = e.util.coerceToTypes(e.opts.coerceTypes, x);
            var P = e.RULES.types[x];
            if (b || E || !0 === P || P && !K(P)) {
                d = e.schemaPath + ".type", u = e.errSchemaPath + "/type", d = e.schemaPath + ".type", u = e.errSchemaPath + "/type";
                var w = E ? "checkDataTypes" : "checkDataType";
                if (r += " if (" + e.util[w](x, p, !0) + ") { ", b) {
                    var S = "dataType" + s,
                        M = "coerced" + s;
                    r += " var " + S + " = typeof " + p + "; ", "array" == e.opts.coerceTypes && (r += " if (" + S + " == 'object' && Array.isArray(" + p + ")) " + S + " = 'array'; "), r += " var " + M + " = undefined; ";
                    var L = "",
                        C = b;
                    if (C)
                        for (var T, D = -1, R = C.length - 1; D < R;) T = C[D += 1], D && (r += " if (" + M + " === undefined) { ", L += "}"), "array" == e.opts.coerceTypes && "array" != T && (r += " if (" + S + " == 'array' && " + p + ".length == 1) { " + M + " = " + p + " = " + p + "[0]; " + S + " = typeof " + p + ";  } "), "string" == T ? r += " if (" + S + " == 'number' || " + S + " == 'boolean') " + M + " = '' + " + p + "; else if (" + p + " === null) " + M + " = ''; " : "number" == T || "integer" == T ? (r += " if (" + S + " == 'boolean' || " + p + " === null || (" + S + " == 'string' && " + p + " && " + p + " == +" + p + " ", "integer" == T && (r += " && !(" + p + " % 1)"), r += ")) " + M + " = +" + p + "; ") : "boolean" == T ? r += " if (" + p + " === 'false' || " + p + " === 0 || " + p + " === null) " + M + " = false; else if (" + p + " === 'true' || " + p + " === 1) " + M + " = true; " : "null" == T ? r += " if (" + p + " === '' || " + p + " === 0 || " + p + " === false) " + M + " = null; " : "array" == e.opts.coerceTypes && "array" == T && (r += " if (" + S + " == 'string' || " + S + " == 'number' || " + S + " == 'boolean' || " + p + " == null) " + M + " = [" + p + "]; ");
                    r += " " + L + " if (" + M + " === undefined) {   ", (Q = Q || []).push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: '" + (g || "type") + "' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(u) + " , params: { type: '", r += E ? "" + x.join(",") : "" + x, r += "' } ", !1 !== e.opts.messages && (r += " , message: 'should be ", r += E ? "" + x.join(",") : "" + x, r += "' "), e.opts.verbose && (r += " , schema: validate.schema" + d + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + p + " "), r += " } ") : r += " {} ";
                    m = r;
                    r = Q.pop(), !e.compositeRule && h ? e.async ? r += " throw new ValidationError([" + m + "]); " : r += " validate.errors = [" + m + "]; return false; " : r += " var err = " + m + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", r += " } else {  ";
                    var A = l ? "data" + (l - 1 || "") : "parentData",
                        I = l ? e.dataPathArr[l] : "parentDataProperty";
                    r += " " + p + " = " + M + "; ", l || (r += "if (" + A + " !== undefined)"), r += " " + A + "[" + I + "] = " + M + "; } "
                } else {
                    (Q = Q || []).push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: '" + (g || "type") + "' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(u) + " , params: { type: '", r += E ? "" + x.join(",") : "" + x, r += "' } ", !1 !== e.opts.messages && (r += " , message: 'should be ", r += E ? "" + x.join(",") : "" + x, r += "' "), e.opts.verbose && (r += " , schema: validate.schema" + d + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + p + " "), r += " } ") : r += " {} ";
                    m = r;
                    r = Q.pop(), !e.compositeRule && h ? e.async ? r += " throw new ValidationError([" + m + "]); " : r += " validate.errors = [" + m + "]; return false; " : r += " var err = " + m + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; "
                }
                r += " } "
            }
        }
        if (e.schema.$ref && !a) r += " " + e.RULES.all.$ref.code(e, "$ref") + " ", h && (r += " } if (errors === ", r += v ? "0" : "errs_" + s, r += ") { ", _ += "}");
        else {
            var O = e.RULES;
            if (O)
                for (var U = -1, N = O.length - 1; U < N;)
                    if (K(P = O[U += 1])) {
                        if (P.type && (r += " if (" + e.util.checkDataType(P.type, p) + ") { "), e.opts.useDefaults && !e.compositeRule)
                            if ("object" == P.type && e.schema.properties) {
                                c = e.schema.properties;
                                var F = Object.keys(c);
                                if (F)
                                    for (var z, H = -1, j = F.length - 1; H < j;) {
                                        if (void 0 !== (k = c[z = F[H += 1]]).default) r += "  if (" + (G = p + e.util.getProperty(z)) + " === undefined) " + G + " = ", "shared" == e.opts.useDefaults ? r += " " + e.useDefault(k.default) + " " : r += " " + JSON.stringify(k.default) + " ", r += "; "
                                    }
                            } else if ("array" == P.type && Array.isArray(e.schema.items)) {
                            var V = e.schema.items;
                            if (V) {
                                D = -1;
                                for (var k, B = V.length - 1; D < B;) {
                                    var G;
                                    if (void 0 !== (k = V[D += 1]).default) r += "  if (" + (G = p + "[" + D + "]") + " === undefined) " + G + " = ", "shared" == e.opts.useDefaults ? r += " " + e.useDefault(k.default) + " " : r += " " + JSON.stringify(k.default) + " ", r += "; "
                                }
                            }
                        }
                        var $ = P.rules;
                        if ($)
                            for (var q, X = -1, Y = $.length - 1; X < Y;)
                                if (Z(q = $[X += 1])) {
                                    var W = q.code(e, q.keyword, P.type);
                                    W && (r += " " + W + " ", h && (y += "}"))
                                }
                        if (h && (r += " " + y + " ", y = ""), P.type && (r += " } ", x && x === P.type && !b)) {
                            r += " else { ";
                            var Q;
                            d = e.schemaPath + ".type", u = e.errSchemaPath + "/type";
                            (Q = Q || []).push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: '" + (g || "type") + "' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(u) + " , params: { type: '", r += E ? "" + x.join(",") : "" + x, r += "' } ", !1 !== e.opts.messages && (r += " , message: 'should be ", r += E ? "" + x.join(",") : "" + x, r += "' "), e.opts.verbose && (r += " , schema: validate.schema" + d + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + p + " "), r += " } ") : r += " {} ";
                            m = r;
                            r = Q.pop(), !e.compositeRule && h ? e.async ? r += " throw new ValidationError([" + m + "]); " : r += " validate.errors = [" + m + "]; return false; " : r += " var err = " + m + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", r += " } "
                        }
                        h && (r += " if (errors === ", r += v ? "0" : "errs_" + s, r += ") { ", _ += "}")
                    }
        }

        function K(e) {
            for (var t = e.rules, n = 0; n < t.length; n++)
                if (Z(t[n])) return !0
        }

        function Z(t) {
            return void 0 !== e.schema[t.keyword] || t.implements && function(t) {
                for (var n = t.implements, r = 0; r < n.length; r++)
                    if (void 0 !== e.schema[n[r]]) return !0
            }(t)
        }
        return h && (r += " " + _ + " "), v ? (i ? (r += " if (errors === 0) return data;           ", r += " else throw new ValidationError(vErrors); ") : (r += " validate.errors = vErrors; ", r += " return errors === 0;       "), r += " }; return validate;") : r += " var " + f + " = errors === errs_" + s + ";", r = e.util.cleanUpCode(r), v && (r = e.util.finalCleanUpCode(r, i)), r
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r, i = " ",
            a = e.level,
            o = e.dataLevel,
            s = e.schema[t],
            l = e.schemaPath + e.util.getProperty(t),
            c = e.errSchemaPath + "/" + t,
            d = !e.opts.allErrors,
            u = "data" + (o || ""),
            h = e.opts.$data && s && s.$data;
        h ? (i += " var schema" + a + " = " + e.util.getData(s.$data, o, e.dataPathArr) + "; ", r = "schema" + a) : r = s;
        var p = "maximum" == t,
            f = p ? "exclusiveMaximum" : "exclusiveMinimum",
            m = e.schema[f],
            v = e.opts.$data && m && m.$data,
            g = p ? "<" : ">",
            y = p ? ">" : "<",
            _ = void 0;
        if (v) {
            var x = e.util.getData(m.$data, o, e.dataPathArr),
                E = "exclusive" + a,
                b = "exclType" + a,
                P = "exclIsNumber" + a,
                w = "' + " + (L = "op" + a) + " + '";
            i += " var schemaExcl" + a + " = " + x + "; ", i += " var " + E + "; var " + b + " = typeof " + (x = "schemaExcl" + a) + "; if (" + b + " != 'boolean' && " + b + " != 'undefined' && " + b + " != 'number') { ";
            var S;
            _ = f;
            (S = S || []).push(i), i = "", !1 !== e.createErrors ? (i += " { keyword: '" + (_ || "_exclusiveLimit") + "' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(c) + " , params: {} ", !1 !== e.opts.messages && (i += " , message: '" + f + " should be boolean' "), e.opts.verbose && (i += " , schema: validate.schema" + l + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + u + " "), i += " } ") : i += " {} ";
            var M = i;
            i = S.pop(), !e.compositeRule && d ? e.async ? i += " throw new ValidationError([" + M + "]); " : i += " validate.errors = [" + M + "]; return false; " : i += " var err = " + M + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", i += " } else if ( ", h && (i += " (" + r + " !== undefined && typeof " + r + " != 'number') || "), i += " " + b + " == 'number' ? ( (" + E + " = " + r + " === undefined || " + x + " " + g + "= " + r + ") ? " + u + " " + y + "= " + x + " : " + u + " " + y + " " + r + " ) : ( (" + E + " = " + x + " === true) ? " + u + " " + y + "= " + r + " : " + u + " " + y + " " + r + " ) || " + u + " !== " + u + ") { var op" + a + " = " + E + " ? '" + g + "' : '" + g + "='; ", void 0 === s && (_ = f, c = e.errSchemaPath + "/" + f, r = x, h = v)
        } else {
            w = g;
            if ((P = "number" == typeof m) && h) {
                var L = "'" + w + "'";
                i += " if ( ", h && (i += " (" + r + " !== undefined && typeof " + r + " != 'number') || "), i += " ( " + r + " === undefined || " + m + " " + g + "= " + r + " ? " + u + " " + y + "= " + m + " : " + u + " " + y + " " + r + " ) || " + u + " !== " + u + ") { "
            } else {
                P && void 0 === s ? (E = !0, _ = f, c = e.errSchemaPath + "/" + f, r = m, y += "=") : (P && (r = Math[p ? "min" : "max"](m, s)), m === (!P || r) ? (E = !0, _ = f, c = e.errSchemaPath + "/" + f, y += "=") : (E = !1, w += "="));
                L = "'" + w + "'";
                i += " if ( ", h && (i += " (" + r + " !== undefined && typeof " + r + " != 'number') || "), i += " " + u + " " + y + " " + r + " || " + u + " !== " + u + ") { "
            }
        }
        _ = _ || t, (S = S || []).push(i), i = "", !1 !== e.createErrors ? (i += " { keyword: '" + (_ || "_limit") + "' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(c) + " , params: { comparison: " + L + ", limit: " + r + ", exclusive: " + E + " } ", !1 !== e.opts.messages && (i += " , message: 'should be " + w + " ", i += h ? "' + " + r : r + "'"), e.opts.verbose && (i += " , schema:  ", i += h ? "validate.schema" + l : "" + s, i += "         , parentSchema: validate.schema" + e.schemaPath + " , data: " + u + " "), i += " } ") : i += " {} ";
        M = i;
        return i = S.pop(), !e.compositeRule && d ? e.async ? i += " throw new ValidationError([" + M + "]); " : i += " validate.errors = [" + M + "]; return false; " : i += " var err = " + M + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", i += " } ", d && (i += " else { "), i
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r, i = " ",
            a = e.level,
            o = e.dataLevel,
            s = e.schema[t],
            l = e.schemaPath + e.util.getProperty(t),
            c = e.errSchemaPath + "/" + t,
            d = !e.opts.allErrors,
            u = "data" + (o || ""),
            h = e.opts.$data && s && s.$data;
        h ? (i += " var schema" + a + " = " + e.util.getData(s.$data, o, e.dataPathArr) + "; ", r = "schema" + a) : r = s, i += "if ( ", h && (i += " (" + r + " !== undefined && typeof " + r + " != 'number') || "), i += " " + u + ".length " + ("maxItems" == t ? ">" : "<") + " " + r + ") { ";
        var p = t,
            f = f || [];
        f.push(i), i = "", !1 !== e.createErrors ? (i += " { keyword: '" + (p || "_limitItems") + "' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(c) + " , params: { limit: " + r + " } ", !1 !== e.opts.messages && (i += " , message: 'should NOT have ", i += "maxItems" == t ? "more" : "fewer", i += " than ", i += h ? "' + " + r + " + '" : "" + s, i += " items' "), e.opts.verbose && (i += " , schema:  ", i += h ? "validate.schema" + l : "" + s, i += "         , parentSchema: validate.schema" + e.schemaPath + " , data: " + u + " "), i += " } ") : i += " {} ";
        var m = i;
        return i = f.pop(), !e.compositeRule && d ? e.async ? i += " throw new ValidationError([" + m + "]); " : i += " validate.errors = [" + m + "]; return false; " : i += " var err = " + m + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", i += "} ", d && (i += " else { "), i
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r, i = " ",
            a = e.level,
            o = e.dataLevel,
            s = e.schema[t],
            l = e.schemaPath + e.util.getProperty(t),
            c = e.errSchemaPath + "/" + t,
            d = !e.opts.allErrors,
            u = "data" + (o || ""),
            h = e.opts.$data && s && s.$data;
        h ? (i += " var schema" + a + " = " + e.util.getData(s.$data, o, e.dataPathArr) + "; ", r = "schema" + a) : r = s;
        var p = "maxLength" == t ? ">" : "<";
        i += "if ( ", h && (i += " (" + r + " !== undefined && typeof " + r + " != 'number') || "), !1 === e.opts.unicode ? i += " " + u + ".length " : i += " ucs2length(" + u + ") ", i += " " + p + " " + r + ") { ";
        var f = t,
            m = m || [];
        m.push(i), i = "", !1 !== e.createErrors ? (i += " { keyword: '" + (f || "_limitLength") + "' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(c) + " , params: { limit: " + r + " } ", !1 !== e.opts.messages && (i += " , message: 'should NOT be ", i += "maxLength" == t ? "longer" : "shorter", i += " than ", i += h ? "' + " + r + " + '" : "" + s, i += " characters' "), e.opts.verbose && (i += " , schema:  ", i += h ? "validate.schema" + l : "" + s, i += "         , parentSchema: validate.schema" + e.schemaPath + " , data: " + u + " "), i += " } ") : i += " {} ";
        var v = i;
        return i = m.pop(), !e.compositeRule && d ? e.async ? i += " throw new ValidationError([" + v + "]); " : i += " validate.errors = [" + v + "]; return false; " : i += " var err = " + v + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", i += "} ", d && (i += " else { "), i
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r, i = " ",
            a = e.level,
            o = e.dataLevel,
            s = e.schema[t],
            l = e.schemaPath + e.util.getProperty(t),
            c = e.errSchemaPath + "/" + t,
            d = !e.opts.allErrors,
            u = "data" + (o || ""),
            h = e.opts.$data && s && s.$data;
        h ? (i += " var schema" + a + " = " + e.util.getData(s.$data, o, e.dataPathArr) + "; ", r = "schema" + a) : r = s, i += "if ( ", h && (i += " (" + r + " !== undefined && typeof " + r + " != 'number') || "), i += " Object.keys(" + u + ").length " + ("maxProperties" == t ? ">" : "<") + " " + r + ") { ";
        var p = t,
            f = f || [];
        f.push(i), i = "", !1 !== e.createErrors ? (i += " { keyword: '" + (p || "_limitProperties") + "' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(c) + " , params: { limit: " + r + " } ", !1 !== e.opts.messages && (i += " , message: 'should NOT have ", i += "maxProperties" == t ? "more" : "fewer", i += " than ", i += h ? "' + " + r + " + '" : "" + s, i += " properties' "), e.opts.verbose && (i += " , schema:  ", i += h ? "validate.schema" + l : "" + s, i += "         , parentSchema: validate.schema" + e.schemaPath + " , data: " + u + " "), i += " } ") : i += " {} ";
        var m = i;
        return i = f.pop(), !e.compositeRule && d ? e.async ? i += " throw new ValidationError([" + m + "]); " : i += " validate.errors = [" + m + "]; return false; " : i += " var err = " + m + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", i += "} ", d && (i += " else { "), i
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(3);
    class i extends r.default {
        constructor() {
            super(...arguments), this.uri = "", this.mimeType = ""
        }
        setReference(e, t) {
            this.uri = e, this.mimeType = t || "", this.load(), this.emit("change")
        }
        fromData(e) {
            this.uri = e.uri, this.mimeType = e.mimeType || ""
        }
        toData() {
            const e = {
                uri: this.uri
            };
            return this.mimeType && (e.mimeType = this.mimeType), e
        }
        load() {}
    }
    i.type = "Reference", t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(4),
        a = n(13);
    class o extends a.default {
        constructor() {
            super(...arguments), this.ins = this.makeProps({
                col: i.default.ColorRGB("Color"),
                int: i.default.Number("Intensity", 1),
                pos: i.default.Vector3("Position", [0, 1, 0]),
                tgt: i.default.Vector3("Target")
            })
        }
        get light() {
            return this.object3D
        }
        create() {
            super.create(), this.object3D = new r.DirectionalLight
        }
        update() {
            const e = this.light,
                {
                    col: t,
                    int: n,
                    pos: r,
                    tgt: i
                } = this.ins;
            e.color.fromArray(t.value), e.intensity = n.value, e.position.fromArray(r.value), e.target.position.fromArray(i.value), e.updateMatrix()
        }
        fromData(e) {
            this.ins.setValues({
                col: void 0 !== e.color ? e.color.slice() : [1, 1, 1],
                int: void 0 !== e.intensity ? e.intensity : 1,
                pos: [0, 0, 0]
            })
        }
        toData() {
            const e = {},
                t = this.ins;
            return e.type = "directional", e.color = t.col.value.slice(), e.intensity = t.int.value, e
        }
    }
    o.type = "DirectionalLight", t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(4),
        a = n(13);
    class o extends a.default {
        constructor() {
            super(...arguments), this.ins = this.makeProps({
                col: i.default.ColorRGB("Color"),
                int: i.default.Number("Intensity", 1),
                dst: i.default.Number("Distance"),
                dcy: i.default.Number("Decay", 1)
            })
        }
        get light() {
            return this.object3D
        }
        create() {
            super.create(), this.object3D = new r.PointLight
        }
        update() {
            const e = this.light,
                {
                    col: t,
                    int: n,
                    dst: r,
                    dcy: i
                } = this.ins;
            e.color.fromArray(t.value), e.intensity = n.value, e.distance = r.value, e.decay = i.value
        }
        fromData(e) {
            this.ins.setValues({
                col: void 0 !== e.color ? e.color.slice() : [1, 1, 1],
                int: void 0 !== e.intensity ? e.intensity : 1,
                dst: e.point.distance || 0,
                dcy: void 0 !== e.point.decay ? e.point.decay : 1
            })
        }
        toData() {
            const e = {},
                t = this.ins;
            return e.type = "point", e.color = t.col.value.slice(), e.intensity = t.int.value, e.point = {
                distance: t.dst.value,
                decay: t.dcy.value
            }, e
        }
    }
    o.type = "PointLight", t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(4),
        a = n(13);
    class o extends a.default {
        constructor() {
            super(...arguments), this.ins = this.makeProps({
                col: i.default.ColorRGB("Color"),
                int: i.default.Number("Intensity", 1),
                dst: i.default.Number("Distance"),
                dcy: i.default.Number("Decay", 1),
                ang: i.default.Number("Angle", 45),
                pen: i.default.Number("Penumbra", .5)
            })
        }
        get light() {
            return this.object3D
        }
        create() {
            super.create(), this.object3D = new r.SpotLight
        }
        update() {
            const e = this.light,
                {
                    col: t,
                    int: n,
                    dst: r,
                    dcy: i,
                    ang: a,
                    pen: o
                } = this.ins;
            e.color.fromArray(t.value), e.intensity = n.value, e.distance = r.value, e.decay = i.value, e.angle = a.value, e.penumbra = o.value
        }
        fromData(e) {
            this.ins.setValues({
                col: void 0 !== e.color ? e.color.slice() : [1, 1, 1],
                int: void 0 !== e.intensity ? e.intensity : 1,
                dst: e.point.distance || 0,
                dcy: void 0 !== e.point.decay ? e.point.decay : 1,
                ang: void 0 !== e.spot.angle ? e.spot.angle : Math.PI / 4,
                pen: e.spot.penumbra || 0
            })
        }
        toData() {
            const e = {},
                t = this.ins;
            return e.type = "spot", e.color = t.col.value.slice(), e.intensity = t.int.value, e.spot = {
                distance: t.dst.value,
                decay: t.dcy.value,
                angle: t.ang.value,
                penumbra: t.pen.value
            }, e
        }
    }
    o.type = "SpotLight", t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(3),
        i = n(9);
    class a extends r.default {
        constructor() {
            super(...arguments), this.units = "cm", this.shader = i.EShaderMode.Default, this.exposure = 1, this.gamma = 1
        }
        fromData(e) {
            this.units = e.units, this.shader = i.EShaderMode[e.shader], this.exposure = e.exposure, this.gamma = e.gamma
        }
        toData() {
            return {
                units: this.units,
                shader: i.EShaderMode[this.shader],
                exposure: this.exposure,
                gamma: this.gamma
            }
        }
    }
    a.type = "Renderer", t.default = a
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(3);
    class i extends r.default {
        constructor() {
            super(...arguments), this.enabled = !1, this.document = ""
        }
        fromData(e) {
            this.enabled = e.enabled, this.document = e.document || ""
        }
        toData() {
            const e = {
                enabled: this.enabled
            };
            return this.document && (e.document = this.document), e
        }
    }
    i.type = "Reader", t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(19),
        i = n(35),
        a = n(180),
        o = n(16),
        s = n(32),
        l = n(33),
        c = n(36),
        d = n(181),
        u = n(34),
        h = n(182),
        p = n(17);
    t.default = class {
        constructor(e, t) {
            this.entity = e, this.entity.getOrCreateComponent(p.default), this.itemUrl = "", this.templateUri = "", this.loaders = t
        }
        get name() {
            return this.entity.name
        }
        get url() {
            return this.itemUrl
        }
        get path() {
            return r.default(".", this.itemUrl)
        }
        get templateName() {
            return this.templateUri
        }
        addWebModelDerivative(e, t) {
            this.itemUrl = e;
            const n = e.substr(r.default(".", e).length),
                i = this.entity.getOrCreateComponent(o.default);
            i.setAssetLoader(this.loaders.assetLoader, this.path), i.addWebModelDerivative(n, t)
        }
        addGeometryAndTextureDerivative(e, t, n) {
            this.itemUrl = e;
            const i = e.substr(r.default(".", e).length),
                a = t ? t.substr(r.default(".", t).length) : void 0,
                s = this.entity.getOrCreateComponent(o.default);
            s.setAssetLoader(this.loaders.assetLoader, this.path), s.addGeometryAndTextureDerivative(i, a, n)
        }
        inflate(e, t) {
            const n = this.entity;
            t && (this.itemUrl = t);
            let r = [],
                p = [],
                f = [];
            if (n.createComponent(i.default).fromData(e.meta), e.process && n.createComponent(a.default).fromData(e.process), e.model && n.createComponent(o.default).fromData(e.model).setAssetLoader(this.loaders.assetLoader, this.path), e.documents) {
                const t = e.documents;
                r = n.createComponent(s.default).fromData(t.documents)
            }
            if (e.story) {
                const t = e.story;
                this.templateUri = t.templateUri, f = n.createComponent(h.default).fromData(t.snapshots), t.tours && n.createComponent(u.default).fromData(t.tours, f)
            }
            if (e.annotations) {
                const t = e.annotations;
                t.groups && (p = n.createComponent(l.default).fromData(t.groups)), n.createComponent(c.default).fromData(t.annotations, p, r, f), n.createComponent(d.default)
            }
            return this
        }
        deflate() {
            const e = this.entity,
                t = {};
            let n = {},
                r = {},
                d = {};
            const p = e.getComponent(i.default);
            p && (t.meta = p.toData());
            const f = e.getComponent(a.default);
            f && (t.process = f.toData());
            const m = e.getComponent(o.default);
            m && (t.model = m.toData());
            const v = e.getComponent(s.default);
            if (v) {
                const {
                    data: e,
                    ids: r
                } = v.toData();
                e.length > 0 && (t.documents = {
                    documents: e
                }, n = r)
            }
            const g = e.getComponent(h.default);
            if (g) {
                const {
                    data: n,
                    ids: r
                } = g.toData();
                n.length > 0 && (t.story = {
                    snapshots: n
                }, d = r);
                const i = e.getComponent(u.default);
                i && (t.story.tours = i.toData(d))
            }
            const y = e.getComponent(l.default),
                _ = e.getComponent(c.default);
            if (y || _) {
                let e = null;
                if (y) {
                    const {
                        data: t,
                        ids: n
                    } = y.toData();
                    t.length > 0 && (e = t, r = n)
                }
                const i = _.toData(r, n, d);
                i.length > 0 && (t.annotations = {
                    annotations: i,
                    groups: e
                })
            }
            return t
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(3);
    class i extends r.default {
        constructor() {
            super(...arguments), this.data = {}
        }
        set(e, t) {
            this.data[e] = t
        }
        get(e) {
            return this.data[e]
        }
        remove(e) {
            delete this.data[e]
        }
        clear() {
            this.data = {}
        }
        fromData(e) {
            this.data = Object.assign({}, e)
        }
        toData() {
            return Object.assign({}, this.data)
        }
    }
    i.type = "Process", t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(260),
        a = n(36),
        o = n(16),
        s = n(5);
    class l extends s.default {
        constructor() {
            super(...arguments), this.annotationsTracker = null, this.modelTracker = null, this.views = {}, this.activeView = null
        }
        create() {
            super.create(), this.object3D = new r.Group, this.object3D.updateMatrix(), this.modelTracker = this.trackComponent(o.default, e => {
                e.object3D.add(this.object3D)
            }, e => {
                e.object3D.remove(this.object3D)
            }), this.annotationsTracker = this.trackComponent(a.default, e => {
                e.getArray().forEach(e => this.addView(e)), e.on("change", this.onAnnotationsChange, this)
            }, e => {
                e.off("change", this.onAnnotationsChange, this), e.getArray().forEach(e => this.removeView(e))
            })
        }
        setFactory(e) {
            this.factory = e
        }
        onPointer(e, t) {
            if (e.isPrimary)
                if ("down" === e.type) {
                    const e = this.findViewByObject(t.object);
                    e && (this.activeView = e)
                } else "up" === e.type && (this.activeView && console.log(this.activeView.annotation.title), this.activeView = null);
            return !1
        }
        onTrigger(e, t) {
            return !1
        }
        onAnnotationsChange(e) {
            "add" === e.what ? this.addView(e.annotation) : "remove" === e.what && this.removeView(e.annotation)
        }
        addView(e) {
            const t = new i.default(e);
            this.views[t.id] = t, this.object3D.add(t)
        }
        removeView(e) {
            const t = this.findViewByAnnotation(e);
            this.object3D.remove(t), delete this.views[t.id]
        }
        findViewByAnnotation(e) {
            const t = this.views,
                n = Object.keys(t);
            for (let r = 0, i = n.length; r < i; ++r) {
                const i = t[n[r]];
                if (i.annotation === e) return i
            }
            return null
        }
        findViewByObject(e) {
            let t;
            for (; e && void 0 === (t = this.views[e.id]);) e = e.parent;
            return t
        }
    }
    l.type = "AnnotationsView", t.default = l
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(8),
        i = n(162);
    class a extends r.default {
        addSnapshot(e) {
            const t = this.insert(e);
            return this.emit("changed"), t
        }
        removeSnapshot(e) {
            const t = this.remove(e);
            return this.emit("changed"), t
        }
        fromData(e) {
            return e.map(e => this.addSnapshot({
                title: e.title || "",
                description: e.description || "",
                properties: e.properties
            }))
        }
        toData() {
            const e = {
                data: [],
                ids: {}
            };
            return this.getArray().forEach((t, n) => {
                e.ids[t.id] = n;
                const r = {
                    properties: i.default(t.properties)
                };
                t.title && (r.title = t.title), t.description && (r.description = t.description), e.data.push(r)
            }), e
        }
    }
    a.type = "Snapshots", t.default = a
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    t.default = class {
        constructor() {
            this.types = {}
        }
        createComponent(e, t, n) {
            const r = new(this.getComponentType(e))(n);
            return r.init(t), r
        }
        getComponentType(e) {
            const t = this.types[e];
            if (!t) throw new Error(`component type not found for type id: '${e}'`);
            return t
        }
        registerComponentType(e) {
            if (Array.isArray(e)) e.forEach(e => this.registerComponentType(e));
            else {
                if (this.types[e.type]) throw console.warn(e), new Error(`component type already registered: '${e.type}'`);
                this.types[e.type] = e
            }
        }
    }
}, , function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1);
    class i extends r.Component {
        constructor(e) {
            super(e), this.state = {
                isDragging: !1
            }, this.element = r.createRef(), this.isActive = !1, this.startX = 0, this.startY = 0, this.lastX = 0, this.lastY = 0, this.onPointerDown = this.onPointerDown.bind(this), this.onPointerMove = this.onPointerMove.bind(this), this.onPointerUp = this.onPointerUp.bind(this), this.onDoubleClick = this.onDoubleClick.bind(this), this.onContextMenu = this.onContextMenu.bind(this)
        }
        render() {
            const {
                className: e,
                style: t,
                children: n
            } = this.props, i = {
                className: e,
                style: t,
                ref: this.element,
                "touch-action": "none",
                onPointerDown: this.onPointerDown,
                onPointerMove: this.onPointerMove,
                onPointerUp: this.onPointerUp,
                onDoubleClick: this.onDoubleClick,
                onContextMenu: this.onContextMenu
            };
            return r.createElement("div", Object.assign({}, i), n)
        }
        onPointerDown(e) {
            const t = this.props;
            if (t.onPointerDown && t.onPointerDown(e), e.isPrimary) {
                this.element.current.setPointerCapture(e.pointerId), this.isActive = !0, this.startX = e.clientX, this.startY = e.clientY, this.lastX = this.startX, this.lastY = this.startY;
                const {
                    id: t,
                    index: n,
                    onPress: r
                } = this.props;
                r && r({
                    id: t,
                    index: n,
                    sender: this
                })
            }
            e.stopPropagation(), e.preventDefault()
        }
        onPointerMove(e) {
            const t = this.props,
                n = this.state,
                r = e.clientX - this.lastX,
                i = e.clientY - this.lastY;
            if (this.lastX = e.clientX, this.lastY = e.clientY, t.onPointerMove && t.onPointerMove(e, r, i), e.isPrimary && this.isActive) {
                if (n.isDragging) this.onDragMove(e, r, i);
                else if (!1 !== t.draggable) {
                    let t = e.clientX - this.startX,
                        n = e.clientY - this.startY;
                    Math.abs(t) + Math.abs(n) > 2 && (this.setState({
                        isDragging: !0
                    }), this.onDragBegin(e))
                }
                e.stopPropagation(), e.preventDefault()
            }
        }
        onPointerUp(e) {
            const t = this.state,
                {
                    id: n,
                    index: r,
                    onPointerUp: i,
                    onRelease: a,
                    onTap: o
                } = this.props;
            i && i(e), e.isPrimary && this.isActive && (this.isActive = !1, a && a({
                id: n,
                index: r,
                sender: this
            }), t.isDragging ? (this.setState({
                isDragging: !1
            }), this.onDragEnd(e)) : o && o({
                id: n,
                index: r,
                sender: this
            }), e.stopPropagation(), e.preventDefault())
        }
        onDoubleClick() {
            if (!this.state.isDragging) {
                const {
                    id: e,
                    index: t,
                    onDoubleTap: n
                } = this.props;
                n && n({
                    id: e,
                    index: t,
                    sender: this
                })
            }
        }
        onContextMenu(e) {
            e.preventDefault(), !this.state.isDragging && this.props.onContextMenu && this.props.onContextMenu(e)
        }
        onDragBegin(e) {
            this.props.onDragBegin && this.props.onDragBegin(e)
        }
        onDragMove(e, t, n) {
            this.props.onDragMove && this.props.onDragMove(e, t, n)
        }
        onDragEnd(e) {
            this.props.onDragEnd && this.props.onDragEnd(e)
        }
    }
    i.defaultProps = {
        className: "ff-draggable"
    }, t.default = i
}, function(e, t) {
    e.exports = "#define STANDARD\n\nuniform vec3 diffuse;\nuniform vec3 emissive;\nuniform float roughness;\nuniform float metalness;\nuniform float opacity;\n\n#ifndef STANDARD\n\tuniform float clearCoat;\n\tuniform float clearCoatRoughness;\n#endif\n\nvarying vec3 vViewPosition;\n\n#ifndef FLAT_SHADED\n\tvarying vec3 vNormal;\n#endif\n\n#include <common>\n#include <packing>\n#include <dithering_pars_fragment>\n#include <color_pars_fragment>\n//#include <uv_pars_fragment>\n// REPLACED WITH\n#if defined(USE_MAP) || defined(USE_BUMPMAP) || defined(USE_NORMALMAP) || defined(USE_SPECULARMAP) || defined(USE_ALPHAMAP) || defined(USE_EMISSIVEMAP) || defined(USE_ROUGHNESSMAP) || defined(USE_METALNESSMAP) || defined(USE_LIGHTMAP) || defined(USE_AOMAP)\n\tvarying vec2 vUv;\n#endif\n\n//#include <uv2_pars_fragment>\n#include <map_pars_fragment>\n#include <alphamap_pars_fragment>\n#include <aomap_pars_fragment>\n#include <lightmap_pars_fragment>\n#include <emissivemap_pars_fragment>\n#include <bsdfs>\n#include <cube_uv_reflection_fragment>\n#include <envmap_pars_fragment>\n#include <envmap_physical_pars_fragment>\n#include <fog_pars_fragment>\n#include <lights_pars_begin>\n//#include <lights_pars_maps>\n#include <lights_physical_pars_fragment>\n#include <shadowmap_pars_fragment>\n#include <bumpmap_pars_fragment>\n#include <normalmap_pars_fragment>\n#include <roughnessmap_pars_fragment>\n#include <metalnessmap_pars_fragment>\n#include <logdepthbuf_pars_fragment>\n#include <clipping_planes_pars_fragment>\n\n#ifdef USE_AOMAP\nuniform vec3 aoMapMix;\n#endif\n\n#if defined(USE_NORMALMAP) && defined(USE_OBJECTSPACE_NORMALMAP)\nuniform mat3 normalMatrix;\n#endif\n\n#ifdef MODE_XRAY\nvarying float vIntensity;\n#endif\n\nvoid main() {\n\n\t#include <clipping_planes_fragment>\n\n\tvec4 diffuseColor = vec4( diffuse, opacity );\n\tReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );\n\tvec3 totalEmissiveRadiance = emissive;\n\n\t#include <logdepthbuf_fragment>\n\t#include <map_fragment>\n\t#include <color_fragment>\n\t#include <alphamap_fragment>\n\t#include <alphatest_fragment>\n\t#include <roughnessmap_fragment>\n\t#include <metalnessmap_fragment>\n\n\t//#include <normal_fragment>\n\t// REPLACED WITH\n\t#ifdef FLAT_SHADED\n    \t// Workaround for Adreno/Nexus5 not able able to do dFdx( vViewPosition ) ...\n    \tvec3 fdx = vec3( dFdx( vViewPosition.x ), dFdx( vViewPosition.y ), dFdx( vViewPosition.z ) );\n    \tvec3 fdy = vec3( dFdy( vViewPosition.x ), dFdy( vViewPosition.y ), dFdy( vViewPosition.z ) );\n    \tvec3 normal = normalize( cross( fdx, fdy ) );\n    #else\n    \tvec3 normal = normalize( vNormal );\n\n      #ifdef DOUBLE_SIDED\n    \tnormal = normal * ( float( gl_FrontFacing ) * 2.0 - 1.0 );\n      #endif\n    #endif\n\n    #ifdef USE_NORMALMAP\n      #ifdef USE_OBJECTSPACE_NORMALMAP\n        normal = normalize(normalMatrix * (texture2D(normalMap, vUv).xyz * 2.0 - 1.0));\n      #else\n    \tnormal = perturbNormal2Arb( -vViewPosition, normal );\n      #endif\n    #elif defined( USE_BUMPMAP )\n    \tnormal = perturbNormalArb( -vViewPosition, normal, dHdxy_fwd() );\n    #endif\n\n\t#include <emissivemap_fragment>\n\n\t// accumulation\n    #if defined(USE_LIGHTMAP) || defined(USE_AOMAP)\n        vec2 vUv2 = vUv;\n    #endif\n\n\t#include <lights_physical_fragment>\n\t#include <lights_fragment_begin>\n\t#include <lights_fragment_maps>\n\t#include <lights_fragment_end>\n\n\t// modulation\n\t//#include <aomap_fragment>\n\t// REPLACED WITH\n\t#ifdef USE_AOMAP\n    \t// reads channel R, compatible with a combined OcclusionRoughnessMetallic (RGB) texture\n    \tvec3 aoSample = texture2D(aoMap, vUv).rgb;\n    \tvec3 aoFactors = mix(vec3(1.0), aoSample, clamp(aoMapMix * aoMapIntensity, 0.0, 1.0));\n    \tfloat ambientOcclusion = aoFactors.x * aoFactors.y * aoFactors.z;\n    \tfloat ambientOcclusion2 = ambientOcclusion * ambientOcclusion;\n    \treflectedLight.directDiffuse *= ambientOcclusion2;\n    \treflectedLight.directSpecular *= ambientOcclusion;\n    \t//reflectedLight.indirectDiffuse *= ambientOcclusion;\n\n    \t#if defined(USE_ENVMAP) && defined(PHYSICAL)\n    \t\tfloat dotNV = saturate(dot(geometry.normal, geometry.viewDir));\n    \t\treflectedLight.indirectSpecular *= computeSpecularOcclusion(dotNV, ambientOcclusion, material.specularRoughness);\n    \t#endif\n    #endif\n\n\tvec3 outgoingLight = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse + reflectedLight.directSpecular + reflectedLight.indirectSpecular + totalEmissiveRadiance;\n\n\tgl_FragColor = vec4(outgoingLight, diffuseColor.a);\n\n\t#include <tonemapping_fragment>\n\t#include <encodings_fragment>\n\t#include <fog_fragment>\n\t#include <premultiplied_alpha_fragment>\n\t#include <dithering_fragment>\n\n    #ifdef MODE_NORMALS\n        gl_FragColor = vec4(vec3(normal * 0.5 + 0.5), 1.0);\n    #endif\n\n    #ifdef MODE_XRAY\n        gl_FragColor = vec4(vec3(0.4, 0.7, 1.0) * vIntensity, 1.0);\n    #endif\n}\n"
}, function(e, t) {
    e.exports = "#define STANDARD\n\nvarying vec3 vViewPosition;\n\n#ifndef FLAT_SHADED\n\n\tvarying vec3 vNormal;\n\n#endif\n\n#include <common>\n//#include <uv_pars_vertex>\n// REPLACED WITH\n#if defined(USE_MAP) || defined(USE_BUMPMAP) || defined(USE_NORMALMAP) || defined(USE_SPECULARMAP) || defined(USE_ALPHAMAP) || defined(USE_EMISSIVEMAP) || defined(USE_ROUGHNESSMAP) || defined(USE_METALNESSMAP) || defined(USE_LIGHTMAP) || defined(USE_AOMAP)\n\tvarying vec2 vUv;\n\tuniform mat3 uvTransform;\n#endif\n\n//#include <uv2_pars_vertex>\n#include <displacementmap_pars_vertex>\n#include <color_pars_vertex>\n#include <fog_pars_vertex>\n#include <morphtarget_pars_vertex>\n#include <skinning_pars_vertex>\n#include <shadowmap_pars_vertex>\n#include <logdepthbuf_pars_vertex>\n#include <clipping_planes_pars_vertex>\n\n#ifdef MODE_XRAY\nvarying float vIntensity;\n#endif\n\nvoid main() {\n\n//\t#include <uv_vertex>\n//  REPLACED WITH\n#if defined(USE_MAP) || defined(USE_BUMPMAP) || defined(USE_NORMALMAP) || defined(USE_SPECULARMAP) || defined(USE_ALPHAMAP) || defined(USE_EMISSIVEMAP) || defined(USE_ROUGHNESSMAP) || defined(USE_METALNESSMAP) || defined(USE_LIGHTMAP) || defined(USE_AOMAP)\n\tvUv = (uvTransform * vec3(uv, 1)).xy;\n#endif\n\n//\t#include <uv2_vertex>\n\t#include <color_vertex>\n\n\t#include <beginnormal_vertex>\n\t#include <morphnormal_vertex>\n\t#include <skinbase_vertex>\n\t#include <skinnormal_vertex>\n\t#include <defaultnormal_vertex>\n\n#ifndef FLAT_SHADED // Normal computed with derivatives when FLAT_SHADED\n\n\tvNormal = normalize(transformedNormal);\n\n#endif\n\n\t#include <begin_vertex>\n\t#include <morphtarget_vertex>\n\t#include <skinning_vertex>\n\t#include <displacementmap_vertex>\n\t#include <project_vertex>\n\t#include <logdepthbuf_vertex>\n\t#include <clipping_planes_vertex>\n\n\tvViewPosition = - mvPosition.xyz;\n\n\t#include <worldpos_vertex>\n\t#include <shadowmap_vertex>\n\t#include <fog_vertex>\n\n#ifdef MODE_XRAY\n    vec3 viewNormal = normalize(normalMatrix * objectNormal);\n    vIntensity = pow(abs(1.0 - abs(dot(viewNormal, vec3(0.0, 0.0, 1.0)))), 3.0);\n#endif\n\n#ifdef MODE_NORMALS\n    vNormal = normal;\n#endif\n}\n"
}, function(e, t) {
    THREE.GLTFLoader = function() {
        function e(e) {
            this.manager = void 0 !== e ? e : THREE.DefaultLoadingManager, this.dracoLoader = null
        }
        e.prototype = {
            constructor: e,
            crossOrigin: "anonymous",
            load: function(e, t, n, r) {
                var i, a = this;
                i = void 0 !== this.resourcePath ? this.resourcePath : void 0 !== this.path ? this.path : THREE.LoaderUtils.extractUrlBase(e), a.manager.itemStart(e);
                var o = function(t) {
                        r ? r(t) : console.error(t), a.manager.itemEnd(e), a.manager.itemError(e)
                    },
                    s = new THREE.FileLoader(a.manager);
                s.setPath(this.path), s.setResponseType("arraybuffer"), s.load(e, function(n) {
                    try {
                        a.parse(n, i, function(n) {
                            t(n), a.manager.itemEnd(e)
                        }, o)
                    } catch (e) {
                        o(e)
                    }
                }, n, o)
            },
            setCrossOrigin: function(e) {
                return this.crossOrigin = e, this
            },
            setPath: function(e) {
                return this.path = e, this
            },
            setResourcePath: function(e) {
                return this.resourcePath = e, this
            },
            setDRACOLoader: function(e) {
                return this.dracoLoader = e, this
            },
            parse: function(e, d, u, h) {
                var p, f = {};
                if ("string" == typeof e) p = e;
                else if (THREE.LoaderUtils.decodeText(new Uint8Array(e, 0, 4)) === a) {
                    try {
                        f[t.KHR_BINARY_GLTF] = new function(e) {
                            this.name = t.KHR_BINARY_GLTF, this.content = null, this.body = null;
                            var n = new DataView(e, 0, o);
                            if (this.header = {
                                    magic: THREE.LoaderUtils.decodeText(new Uint8Array(e.slice(0, 4))),
                                    version: n.getUint32(4, !0),
                                    length: n.getUint32(8, !0)
                                }, this.header.magic !== a) throw new Error("THREE.GLTFLoader: Unsupported glTF-Binary header.");
                            if (this.header.version < 2) throw new Error("THREE.GLTFLoader: Legacy binary file detected. Use LegacyGLTFLoader instead.");
                            var r = new DataView(e, o),
                                i = 0;
                            for (; i < r.byteLength;) {
                                var l = r.getUint32(i, !0);
                                i += 4;
                                var c = r.getUint32(i, !0);
                                if (i += 4, c === s.JSON) {
                                    var d = new Uint8Array(e, o + i, l);
                                    this.content = THREE.LoaderUtils.decodeText(d)
                                } else if (c === s.BIN) {
                                    var u = o + i;
                                    this.body = e.slice(u, u + l)
                                }
                                i += l
                            }
                            if (null === this.content) throw new Error("THREE.GLTFLoader: JSON content not found.")
                        }(e)
                    } catch (e) {
                        return void(h && h(e))
                    }
                    p = f[t.KHR_BINARY_GLTF].content
                } else p = THREE.LoaderUtils.decodeText(new Uint8Array(e));
                var m = JSON.parse(p);
                if (void 0 === m.asset || m.asset.version[0] < 2) h && h(new Error("THREE.GLTFLoader: Unsupported asset. glTF versions >=2.0 are supported. Use LegacyGLTFLoader instead."));
                else {
                    if (m.extensionsUsed)
                        for (var v = 0; v < m.extensionsUsed.length; ++v) {
                            var g = m.extensionsUsed[v],
                                y = m.extensionsRequired || [];
                            switch (g) {
                                case t.KHR_LIGHTS_PUNCTUAL:
                                    f[g] = new r(m);
                                    break;
                                case t.KHR_MATERIALS_UNLIT:
                                    f[g] = new i(m);
                                    break;
                                case t.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS:
                                    f[g] = new c;
                                    break;
                                case t.KHR_DRACO_MESH_COMPRESSION:
                                    f[g] = new l(m, this.dracoLoader);
                                    break;
                                case t.MSFT_TEXTURE_DDS:
                                    f[t.MSFT_TEXTURE_DDS] = new n;
                                    break;
                                default:
                                    y.indexOf(g) >= 0 && console.warn('THREE.GLTFLoader: Unknown extension "' + g + '".')
                            }
                        }
                    var _ = new z(m, f, {
                        path: d || this.resourcePath || "",
                        crossOrigin: this.crossOrigin,
                        manager: this.manager
                    });
                    _.parse(function(e, t, n, r, i) {
                        var a = {
                            scene: e,
                            scenes: t,
                            cameras: n,
                            animations: r,
                            asset: i.asset,
                            parser: _,
                            userData: {}
                        };
                        D(f, a, i), u(a)
                    }, h)
                }
            }
        };
        var t = {
            KHR_BINARY_GLTF: "KHR_binary_glTF",
            KHR_DRACO_MESH_COMPRESSION: "KHR_draco_mesh_compression",
            KHR_LIGHTS_PUNCTUAL: "KHR_lights_punctual",
            KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS: "KHR_materials_pbrSpecularGlossiness",
            KHR_MATERIALS_UNLIT: "KHR_materials_unlit",
            MSFT_TEXTURE_DDS: "MSFT_texture_dds"
        };

        function n() {
            if (!THREE.DDSLoader) throw new Error("THREE.GLTFLoader: Attempting to load .dds texture without importing THREE.DDSLoader");
            this.name = t.MSFT_TEXTURE_DDS, this.ddsLoader = new THREE.DDSLoader
        }

        function r(e) {
            this.name = t.KHR_LIGHTS_PUNCTUAL, this.lights = [];
            for (var n = (e.extensions && e.extensions[t.KHR_LIGHTS_PUNCTUAL] || {}).lights || [], r = 0; r < n.length; r++) {
                var i, a = n[r],
                    o = new THREE.Color(16777215);
                void 0 !== a.color && o.fromArray(a.color);
                var s = void 0 !== a.range ? a.range : 0;
                switch (a.type) {
                    case "directional":
                        (i = new THREE.DirectionalLight(o)).target.position.set(0, 0, -1), i.add(i.target);
                        break;
                    case "point":
                        (i = new THREE.PointLight(o)).distance = s;
                        break;
                    case "spot":
                        (i = new THREE.SpotLight(o)).distance = s, a.spot = a.spot || {}, a.spot.innerConeAngle = void 0 !== a.spot.innerConeAngle ? a.spot.innerConeAngle : 0, a.spot.outerConeAngle = void 0 !== a.spot.outerConeAngle ? a.spot.outerConeAngle : Math.PI / 4, i.angle = a.spot.outerConeAngle, i.penumbra = 1 - a.spot.innerConeAngle / a.spot.outerConeAngle, i.target.position.set(0, 0, -1), i.add(i.target);
                        break;
                    default:
                        throw new Error('THREE.GLTFLoader: Unexpected light type, "' + a.type + '".')
                }
                i.decay = 2, void 0 !== a.intensity && (i.intensity = a.intensity), i.name = a.name || "light_" + r, this.lights.push(i)
            }
        }

        function i(e) {
            this.name = t.KHR_MATERIALS_UNLIT
        }
        i.prototype.getMaterialType = function(e) {
            return THREE.MeshBasicMaterial
        }, i.prototype.extendParams = function(e, t, n) {
            var r = [];
            e.color = new THREE.Color(1, 1, 1), e.opacity = 1;
            var i = t.pbrMetallicRoughness;
            if (i) {
                if (Array.isArray(i.baseColorFactor)) {
                    var a = i.baseColorFactor;
                    e.color.fromArray(a), e.opacity = a[3]
                }
                void 0 !== i.baseColorTexture && r.push(n.assignTexture(e, "map", i.baseColorTexture.index))
            }
            return Promise.all(r)
        };
        var a = "glTF",
            o = 12,
            s = {
                JSON: 1313821514,
                BIN: 5130562
            };

        function l(e, n) {
            if (!n) throw new Error("THREE.GLTFLoader: No DRACOLoader instance provided.");
            this.name = t.KHR_DRACO_MESH_COMPRESSION, this.json = e, this.dracoLoader = n
        }

        function c() {
            return {
                name: t.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS,
                specularGlossinessParams: ["color", "map", "lightMap", "lightMapIntensity", "aoMap", "aoMapIntensity", "emissive", "emissiveIntensity", "emissiveMap", "bumpMap", "bumpScale", "normalMap", "displacementMap", "displacementScale", "displacementBias", "specularMap", "specular", "glossinessMap", "glossiness", "alphaMap", "envMap", "envMapIntensity", "refractionRatio"],
                getMaterialType: function() {
                    return THREE.ShaderMaterial
                },
                extendParams: function(e, t, n) {
                    var r = t.extensions[this.name],
                        i = THREE.ShaderLib.standard,
                        a = THREE.UniformsUtils.clone(i.uniforms),
                        o = ["#ifdef USE_SPECULARMAP", "\tuniform sampler2D specularMap;", "#endif"].join("\n"),
                        s = ["#ifdef USE_GLOSSINESSMAP", "\tuniform sampler2D glossinessMap;", "#endif"].join("\n"),
                        l = ["vec3 specularFactor = specular;", "#ifdef USE_SPECULARMAP", "\tvec4 texelSpecular = texture2D( specularMap, vUv );", "\ttexelSpecular = sRGBToLinear( texelSpecular );", "\t// reads channel RGB, compatible with a glTF Specular-Glossiness (RGBA) texture", "\tspecularFactor *= texelSpecular.rgb;", "#endif"].join("\n"),
                        c = ["float glossinessFactor = glossiness;", "#ifdef USE_GLOSSINESSMAP", "\tvec4 texelGlossiness = texture2D( glossinessMap, vUv );", "\t// reads channel A, compatible with a glTF Specular-Glossiness (RGBA) texture", "\tglossinessFactor *= texelGlossiness.a;", "#endif"].join("\n"),
                        d = ["PhysicalMaterial material;", "material.diffuseColor = diffuseColor.rgb;", "material.specularRoughness = clamp( 1.0 - glossinessFactor, 0.04, 1.0 );", "material.specularColor = specularFactor.rgb;"].join("\n"),
                        u = i.fragmentShader.replace("uniform float roughness;", "uniform vec3 specular;").replace("uniform float metalness;", "uniform float glossiness;").replace("#include <roughnessmap_pars_fragment>", o).replace("#include <metalnessmap_pars_fragment>", s).replace("#include <roughnessmap_fragment>", l).replace("#include <metalnessmap_fragment>", c).replace("#include <lights_physical_fragment>", d);
                    delete a.roughness, delete a.metalness, delete a.roughnessMap, delete a.metalnessMap, a.specular = {
                        value: (new THREE.Color).setHex(1118481)
                    }, a.glossiness = {
                        value: .5
                    }, a.specularMap = {
                        value: null
                    }, a.glossinessMap = {
                        value: null
                    }, e.vertexShader = i.vertexShader, e.fragmentShader = u, e.uniforms = a, e.defines = {
                        STANDARD: ""
                    }, e.color = new THREE.Color(1, 1, 1), e.opacity = 1;
                    var h = [];
                    if (Array.isArray(r.diffuseFactor)) {
                        var p = r.diffuseFactor;
                        e.color.fromArray(p), e.opacity = p[3]
                    }
                    if (void 0 !== r.diffuseTexture && h.push(n.assignTexture(e, "map", r.diffuseTexture.index)), e.emissive = new THREE.Color(0, 0, 0), e.glossiness = void 0 !== r.glossinessFactor ? r.glossinessFactor : 1, e.specular = new THREE.Color(1, 1, 1), Array.isArray(r.specularFactor) && e.specular.fromArray(r.specularFactor), void 0 !== r.specularGlossinessTexture) {
                        var f = r.specularGlossinessTexture.index;
                        h.push(n.assignTexture(e, "glossinessMap", f)), h.push(n.assignTexture(e, "specularMap", f))
                    }
                    return Promise.all(h)
                },
                createMaterial: function(e) {
                    var t = new THREE.ShaderMaterial({
                        defines: e.defines,
                        vertexShader: e.vertexShader,
                        fragmentShader: e.fragmentShader,
                        uniforms: e.uniforms,
                        fog: !0,
                        lights: !0,
                        opacity: e.opacity,
                        transparent: e.transparent
                    });
                    return t.isGLTFSpecularGlossinessMaterial = !0, t.color = e.color, t.map = void 0 === e.map ? null : e.map, t.lightMap = null, t.lightMapIntensity = 1, t.aoMap = void 0 === e.aoMap ? null : e.aoMap, t.aoMapIntensity = 1, t.emissive = e.emissive, t.emissiveIntensity = 1, t.emissiveMap = void 0 === e.emissiveMap ? null : e.emissiveMap, t.bumpMap = void 0 === e.bumpMap ? null : e.bumpMap, t.bumpScale = 1, t.normalMap = void 0 === e.normalMap ? null : e.normalMap, e.normalScale && (t.normalScale = e.normalScale), t.displacementMap = null, t.displacementScale = 1, t.displacementBias = 0, t.specularMap = void 0 === e.specularMap ? null : e.specularMap, t.specular = e.specular, t.glossinessMap = void 0 === e.glossinessMap ? null : e.glossinessMap, t.glossiness = e.glossiness, t.alphaMap = null, t.envMap = void 0 === e.envMap ? null : e.envMap, t.envMapIntensity = 1, t.refractionRatio = .98, t.extensions.derivatives = !0, t
                },
                cloneMaterial: function(e) {
                    var t = e.clone();
                    t.isGLTFSpecularGlossinessMaterial = !0;
                    for (var n = this.specularGlossinessParams, r = 0, i = n.length; r < i; r++) t[n[r]] = e[n[r]];
                    return t
                },
                refreshUniforms: function(e, t, n, r, i, a) {
                    if (!0 === i.isGLTFSpecularGlossinessMaterial) {
                        var o, s = i.uniforms,
                            l = i.defines;
                        s.opacity.value = i.opacity, s.diffuse.value.copy(i.color), s.emissive.value.copy(i.emissive).multiplyScalar(i.emissiveIntensity), s.map.value = i.map, s.specularMap.value = i.specularMap, s.alphaMap.value = i.alphaMap, s.lightMap.value = i.lightMap, s.lightMapIntensity.value = i.lightMapIntensity, s.aoMap.value = i.aoMap, s.aoMapIntensity.value = i.aoMapIntensity, i.map ? o = i.map : i.specularMap ? o = i.specularMap : i.displacementMap ? o = i.displacementMap : i.normalMap ? o = i.normalMap : i.bumpMap ? o = i.bumpMap : i.glossinessMap ? o = i.glossinessMap : i.alphaMap ? o = i.alphaMap : i.emissiveMap && (o = i.emissiveMap), void 0 !== o && (o.isWebGLRenderTarget && (o = o.texture), !0 === o.matrixAutoUpdate && o.updateMatrix(), s.uvTransform.value.copy(o.matrix)), s.envMap.value = i.envMap, s.envMapIntensity.value = i.envMapIntensity, s.flipEnvMap.value = i.envMap && i.envMap.isCubeTexture ? -1 : 1, s.refractionRatio.value = i.refractionRatio, s.specular.value.copy(i.specular), s.glossiness.value = i.glossiness, s.glossinessMap.value = i.glossinessMap, s.emissiveMap.value = i.emissiveMap, s.bumpMap.value = i.bumpMap, s.normalMap.value = i.normalMap, s.displacementMap.value = i.displacementMap, s.displacementScale.value = i.displacementScale, s.displacementBias.value = i.displacementBias, null !== s.glossinessMap.value && void 0 === l.USE_GLOSSINESSMAP && (l.USE_GLOSSINESSMAP = "", l.USE_ROUGHNESSMAP = ""), null === s.glossinessMap.value && void 0 !== l.USE_GLOSSINESSMAP && (delete l.USE_GLOSSINESSMAP, delete l.USE_ROUGHNESSMAP)
                    }
                }
            }
        }

        function d(e, t, n, r) {
            THREE.Interpolant.call(this, e, t, n, r)
        }
        l.prototype.decodePrimitive = function(e, t) {
            var n = this.json,
                r = this.dracoLoader,
                i = e.extensions[this.name].bufferView,
                a = e.extensions[this.name].attributes,
                o = {},
                s = {},
                l = {};
            for (var c in a) c in b && (o[b[c]] = a[c]);
            for (c in e.attributes)
                if (void 0 !== b[c] && void 0 !== a[c]) {
                    var d = n.accessors[e.attributes[c]],
                        u = y[d.componentType];
                    l[b[c]] = u, s[b[c]] = !0 === d.normalized
                }
            return t.getDependency("bufferView", i).then(function(e) {
                return new Promise(function(t) {
                    r.decodeDracoFile(e, function(e) {
                        for (var n in e.attributes) {
                            var r = e.attributes[n],
                                i = s[n];
                            void 0 !== i && (r.normalized = i)
                        }
                        t(e)
                    }, o, l)
                })
            })
        }, d.prototype = Object.create(THREE.Interpolant.prototype), d.prototype.constructor = d, d.prototype.copySampleValue_ = function(e) {
            for (var t = this.resultBuffer, n = this.sampleValues, r = this.valueSize, i = e * r * 3 + r, a = 0; a !== r; a++) t[a] = n[i + a];
            return t
        }, d.prototype.beforeStart_ = d.prototype.copySampleValue_, d.prototype.afterEnd_ = d.prototype.copySampleValue_, d.prototype.interpolate_ = function(e, t, n, r) {
            for (var i = this.resultBuffer, a = this.sampleValues, o = this.valueSize, s = 2 * o, l = 3 * o, c = r - t, d = (n - t) / c, u = d * d, h = u * d, p = e * l, f = p - l, m = 2 * h - 3 * u + 1, v = h - 2 * u + d, g = -2 * h + 3 * u, y = h - u, _ = 0; _ !== o; _++) {
                var x = a[f + _ + o],
                    E = a[f + _ + s] * c,
                    b = a[p + _ + o],
                    P = a[p + _] * c;
                i[_] = m * x + v * E + g * b + y * P
            }
            return i
        };
        var u = 0,
            h = 1,
            p = 2,
            f = 3,
            m = 4,
            v = 5,
            g = 6,
            y = (Number, THREE.Matrix3, THREE.Matrix4, THREE.Vector2, THREE.Vector3, THREE.Vector4, THREE.Texture, {
                5120: Int8Array,
                5121: Uint8Array,
                5122: Int16Array,
                5123: Uint16Array,
                5125: Uint32Array,
                5126: Float32Array
            }),
            _ = {
                9728: THREE.NearestFilter,
                9729: THREE.LinearFilter,
                9984: THREE.NearestMipMapNearestFilter,
                9985: THREE.LinearMipMapNearestFilter,
                9986: THREE.NearestMipMapLinearFilter,
                9987: THREE.LinearMipMapLinearFilter
            },
            x = {
                33071: THREE.ClampToEdgeWrapping,
                33648: THREE.MirroredRepeatWrapping,
                10497: THREE.RepeatWrapping
            },
            E = (THREE.BackSide, THREE.FrontSide, THREE.NeverDepth, THREE.LessDepth, THREE.EqualDepth, THREE.LessEqualDepth, THREE.GreaterEqualDepth, THREE.NotEqualDepth, THREE.GreaterEqualDepth, THREE.AlwaysDepth, THREE.AddEquation, THREE.SubtractEquation, THREE.ReverseSubtractEquation, THREE.ZeroFactor, THREE.OneFactor, THREE.SrcColorFactor, THREE.OneMinusSrcColorFactor, THREE.SrcAlphaFactor, THREE.OneMinusSrcAlphaFactor, THREE.DstAlphaFactor, THREE.OneMinusDstAlphaFactor, THREE.DstColorFactor, THREE.OneMinusDstColorFactor, THREE.SrcAlphaSaturateFactor, {
                SCALAR: 1,
                VEC2: 2,
                VEC3: 3,
                VEC4: 4,
                MAT2: 4,
                MAT3: 9,
                MAT4: 16
            }),
            b = {
                POSITION: "position",
                NORMAL: "normal",
                TEXCOORD_0: "uv",
                TEXCOORD0: "uv",
                TEXCOORD: "uv",
                TEXCOORD_1: "uv2",
                COLOR_0: "color",
                COLOR0: "color",
                COLOR: "color",
                WEIGHTS_0: "skinWeight",
                WEIGHT: "skinWeight",
                JOINTS_0: "skinIndex",
                JOINT: "skinIndex"
            },
            P = {
                scale: "scale",
                translation: "position",
                rotation: "quaternion",
                weights: "morphTargetInfluences"
            },
            w = {
                CUBICSPLINE: THREE.InterpolateSmooth,
                LINEAR: THREE.InterpolateLinear,
                STEP: THREE.InterpolateDiscrete
            },
            S = "OPAQUE",
            M = "MASK",
            L = "BLEND",
            C = {
                "image/png": THREE.RGBAFormat,
                "image/jpeg": THREE.RGBFormat
            };

        function T(e, t) {
            return "string" != typeof e || "" === e ? "" : /^(https?:)?\/\//i.test(e) ? e : /^data:.*,.*$/i.test(e) ? e : /^blob:.*$/i.test(e) ? e : t + e
        }

        function D(e, t, n) {
            for (var r in n.extensions) void 0 === e[r] && (t.userData.gltfExtensions = t.userData.gltfExtensions || {}, t.userData.gltfExtensions[r] = n.extensions[r])
        }

        function R(e, t) {
            void 0 !== t.extras && ("object" == typeof t.extras ? e.userData = t.extras : console.warn("THREE.GLTFLoader: Ignoring primitive type .extras, " + t.extras))
        }

        function A(e, t) {
            if (e.updateMorphTargets(), void 0 !== t.weights)
                for (var n = 0, r = t.weights.length; n < r; n++) e.morphTargetInfluences[n] = t.weights[n];
            if (t.extras && Array.isArray(t.extras.targetNames)) {
                var i = t.extras.targetNames;
                if (e.morphTargetInfluences.length === i.length) {
                    e.morphTargetDictionary = {};
                    for (n = 0, r = i.length; n < r; n++) e.morphTargetDictionary[i[n]] = n
                } else console.warn("THREE.GLTFLoader: Invalid extras.targetNames length. Ignoring names.")
            }
        }

        function I(e, t) {
            return e.indices === t.indices && O(e.attributes, t.attributes)
        }

        function O(e, t) {
            if (Object.keys(e).length !== Object.keys(t).length) return !1;
            for (var n in e)
                if (e[n] !== t[n]) return !1;
            return !0
        }

        function U(e, t) {
            if (e.length !== t.length) return !1;
            for (var n = 0, r = e.length; n < r; n++)
                if (e[n] !== t[n]) return !1;
            return !0
        }

        function N(e, t) {
            for (var n = 0, r = e.length; n < r; n++) {
                var i = e[n];
                if (I(i.primitive, t)) return i.promise
            }
            return null
        }

        function F(e) {
            if (e.isInterleavedBufferAttribute) {
                for (var t = e.count, n = e.itemSize, r = e.array.slice(0, t * n), i = 0; i < t; ++i) r[i] = e.getX(i), n >= 2 && (r[i + 1] = e.getY(i)), n >= 3 && (r[i + 2] = e.getZ(i)), n >= 4 && (r[i + 3] = e.getW(i));
                return new THREE.BufferAttribute(r, n, e.normalized)
            }
            return e.clone()
        }

        function z(e, t, n) {
            this.json = e || {}, this.extensions = t || {}, this.options = n || {}, this.cache = new function() {
                var e = {};
                return {
                    get: function(t) {
                        return e[t]
                    },
                    add: function(t, n) {
                        e[t] = n
                    },
                    remove: function(t) {
                        delete e[t]
                    },
                    removeAll: function() {
                        e = {}
                    }
                }
            }, this.primitiveCache = [], this.multiplePrimitivesCache = [], this.multiPassGeometryCache = [], this.textureLoader = new THREE.TextureLoader(this.options.manager), this.textureLoader.setCrossOrigin(this.options.crossOrigin), this.fileLoader = new THREE.FileLoader(this.options.manager), this.fileLoader.setResponseType("arraybuffer")
        }

        function H(e, t, n) {
            var r = t.attributes;
            for (var i in r) {
                var a = b[i],
                    o = n[r[i]];
                a && (a in e.attributes || e.addAttribute(a, o))
            }
            void 0 === t.indices || e.index || e.setIndex(n[t.indices]), void 0 !== t.targets && function(e, t, n) {
                for (var r = !1, i = !1, a = 0, o = t.length; a < o && (void 0 !== (c = t[a]).POSITION && (r = !0), void 0 !== c.NORMAL && (i = !0), !r || !i); a++);
                if (r || i) {
                    var s = [],
                        l = [];
                    for (a = 0, o = t.length; a < o; a++) {
                        var c = t[a],
                            d = "morphTarget" + a;
                        if (r) {
                            if (void 0 !== c.POSITION) {
                                var u = F(n[c.POSITION]);
                                u.name = d;
                                for (var h = e.attributes.position, p = 0, f = u.count; p < f; p++) u.setXYZ(p, u.getX(p) + h.getX(p), u.getY(p) + h.getY(p), u.getZ(p) + h.getZ(p))
                            } else u = e.attributes.position;
                            s.push(u)
                        }
                        if (i) {
                            if (void 0 !== c.NORMAL) {
                                var m;
                                (m = F(n[c.NORMAL])).name = d;
                                var v = e.attributes.normal;
                                for (p = 0, f = m.count; p < f; p++) m.setXYZ(p, m.getX(p) + v.getX(p), m.getY(p) + v.getY(p), m.getZ(p) + v.getZ(p))
                            } else m = e.attributes.normal;
                            l.push(m)
                        }
                    }
                    r && (e.morphAttributes.position = s), i && (e.morphAttributes.normal = l)
                }
            }(e, t.targets, n), R(e, t)
        }
        return z.prototype.parse = function(e, t) {
            var n = this.json;
            this.cache.removeAll(), this.markDefs(), this.getMultiDependencies(["scene", "animation", "camera"]).then(function(t) {
                var r = t.scenes || [],
                    i = r[n.scene || 0],
                    a = t.animations || [],
                    o = t.cameras || [];
                e(i, r, o, a, n)
            }).catch(t)
        }, z.prototype.markDefs = function() {
            for (var e = this.json.nodes || [], t = this.json.skins || [], n = this.json.meshes || [], r = {}, i = {}, a = 0, o = t.length; a < o; a++)
                for (var s = t[a].joints, l = 0, c = s.length; l < c; l++) e[s[l]].isBone = !0;
            for (var d = 0, u = e.length; d < u; d++) {
                var h = e[d];
                void 0 !== h.mesh && (void 0 === r[h.mesh] && (r[h.mesh] = i[h.mesh] = 0), r[h.mesh]++, void 0 !== h.skin && (n[h.mesh].isSkinnedMesh = !0))
            }
            this.json.meshReferences = r, this.json.meshUses = i
        }, z.prototype.getDependency = function(e, t) {
            var n = e + ":" + t,
                r = this.cache.get(n);
            if (!r) {
                switch (e) {
                    case "scene":
                        r = this.loadScene(t);
                        break;
                    case "node":
                        r = this.loadNode(t);
                        break;
                    case "mesh":
                        r = this.loadMesh(t);
                        break;
                    case "accessor":
                        r = this.loadAccessor(t);
                        break;
                    case "bufferView":
                        r = this.loadBufferView(t);
                        break;
                    case "buffer":
                        r = this.loadBuffer(t);
                        break;
                    case "material":
                        r = this.loadMaterial(t);
                        break;
                    case "texture":
                        r = this.loadTexture(t);
                        break;
                    case "skin":
                        r = this.loadSkin(t);
                        break;
                    case "animation":
                        r = this.loadAnimation(t);
                        break;
                    case "camera":
                        r = this.loadCamera(t);
                        break;
                    default:
                        throw new Error("Unknown type: " + e)
                }
                this.cache.add(n, r)
            }
            return r
        }, z.prototype.getDependencies = function(e) {
            var t = this.cache.get(e);
            if (!t) {
                var n = this,
                    r = this.json[e + ("mesh" === e ? "es" : "s")] || [];
                t = Promise.all(r.map(function(t, r) {
                    return n.getDependency(e, r)
                })), this.cache.add(e, t)
            }
            return t
        }, z.prototype.getMultiDependencies = function(e) {
            for (var t = {}, n = [], r = 0, i = e.length; r < i; r++) {
                var a = e[r],
                    o = this.getDependencies(a);
                o = o.then(function(e, n) {
                    t[e] = n
                }.bind(this, a + ("mesh" === a ? "es" : "s"))), n.push(o)
            }
            return Promise.all(n).then(function() {
                return t
            })
        }, z.prototype.loadBuffer = function(e) {
            var n = this.json.buffers[e],
                r = this.fileLoader;
            if (n.type && "arraybuffer" !== n.type) throw new Error("THREE.GLTFLoader: " + n.type + " buffer type is not supported.");
            if (void 0 === n.uri && 0 === e) return Promise.resolve(this.extensions[t.KHR_BINARY_GLTF].body);
            var i = this.options;
            return new Promise(function(e, t) {
                r.load(T(n.uri, i.path), e, void 0, function() {
                    t(new Error('THREE.GLTFLoader: Failed to load buffer "' + n.uri + '".'))
                })
            })
        }, z.prototype.loadBufferView = function(e) {
            var t = this.json.bufferViews[e];
            return this.getDependency("buffer", t.buffer).then(function(e) {
                var n = t.byteLength || 0,
                    r = t.byteOffset || 0;
                return e.slice(r, r + n)
            })
        }, z.prototype.loadAccessor = function(e) {
            var t = this,
                n = this.json,
                r = this.json.accessors[e];
            if (void 0 === r.bufferView && void 0 === r.sparse) return null;
            var i = [];
            return void 0 !== r.bufferView ? i.push(this.getDependency("bufferView", r.bufferView)) : i.push(null), void 0 !== r.sparse && (i.push(this.getDependency("bufferView", r.sparse.indices.bufferView)), i.push(this.getDependency("bufferView", r.sparse.values.bufferView))), Promise.all(i).then(function(e) {
                var i, a, o = e[0],
                    s = E[r.type],
                    l = y[r.componentType],
                    c = l.BYTES_PER_ELEMENT,
                    d = c * s,
                    u = r.byteOffset || 0,
                    h = void 0 !== r.bufferView ? n.bufferViews[r.bufferView].byteStride : void 0,
                    p = !0 === r.normalized;
                if (h && h !== d) {
                    var f = "InterleavedBuffer:" + r.bufferView + ":" + r.componentType,
                        m = t.cache.get(f);
                    m || (i = new l(o), m = new THREE.InterleavedBuffer(i, h / c), t.cache.add(f, m)), a = new THREE.InterleavedBufferAttribute(m, s, u / c, p)
                } else i = null === o ? new l(r.count * s) : new l(o, u, r.count * s), a = new THREE.BufferAttribute(i, s, p);
                if (void 0 !== r.sparse) {
                    var v = E.SCALAR,
                        g = y[r.sparse.indices.componentType],
                        _ = r.sparse.indices.byteOffset || 0,
                        x = r.sparse.values.byteOffset || 0,
                        b = new g(e[1], _, r.sparse.count * v),
                        P = new l(e[2], x, r.sparse.count * s);
                    null !== o && a.setArray(a.array.slice());
                    for (var w = 0, S = b.length; w < S; w++) {
                        var M = b[w];
                        if (a.setX(M, P[w * s]), s >= 2 && a.setY(M, P[w * s + 1]), s >= 3 && a.setZ(M, P[w * s + 2]), s >= 4 && a.setW(M, P[w * s + 3]), s >= 5) throw new Error("THREE.GLTFLoader: Unsupported itemSize in sparse BufferAttribute.")
                    }
                }
                return a
            })
        }, z.prototype.loadTexture = function(e) {
            var n, r = this,
                i = this.json,
                a = this.options,
                o = this.textureLoader,
                s = window.URL || window.webkitURL,
                l = i.textures[e],
                c = l.extensions || {},
                d = (n = c[t.MSFT_TEXTURE_DDS] ? i.images[c[t.MSFT_TEXTURE_DDS].source] : i.images[l.source]).uri,
                u = !1;
            return void 0 !== n.bufferView && (d = r.getDependency("bufferView", n.bufferView).then(function(e) {
                u = !0;
                var t = new Blob([e], {
                    type: n.mimeType
                });
                return d = s.createObjectURL(t)
            })), Promise.resolve(d).then(function(e) {
                var n = THREE.Loader.Handlers.get(e);
                return n || (n = c[t.MSFT_TEXTURE_DDS] ? r.extensions[t.MSFT_TEXTURE_DDS].ddsLoader : o), new Promise(function(t, r) {
                    n.load(T(e, a.path), t, void 0, r)
                })
            }).then(function(e) {
                !0 === u && s.revokeObjectURL(d), e.flipY = !1, void 0 !== l.name && (e.name = l.name), n.mimeType in C && (e.format = C[n.mimeType]);
                var t = (i.samplers || {})[l.sampler] || {};
                return e.magFilter = _[t.magFilter] || THREE.LinearFilter, e.minFilter = _[t.minFilter] || THREE.LinearMipMapLinearFilter, e.wrapS = x[t.wrapS] || THREE.RepeatWrapping, e.wrapT = x[t.wrapT] || THREE.RepeatWrapping, e
            })
        }, z.prototype.assignTexture = function(e, t, n) {
            return this.getDependency("texture", n).then(function(n) {
                e[t] = n
            })
        }, z.prototype.loadMaterial = function(e) {
            var n, r = this.json,
                i = this.extensions,
                a = r.materials[e],
                o = {},
                s = a.extensions || {},
                l = [];
            if (s[t.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS]) {
                var c = i[t.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS];
                n = c.getMaterialType(a), l.push(c.extendParams(o, a, this))
            } else if (s[t.KHR_MATERIALS_UNLIT]) {
                var d = i[t.KHR_MATERIALS_UNLIT];
                n = d.getMaterialType(a), l.push(d.extendParams(o, a, this))
            } else {
                n = THREE.MeshStandardMaterial;
                var u = a.pbrMetallicRoughness || {};
                if (o.color = new THREE.Color(1, 1, 1), o.opacity = 1, Array.isArray(u.baseColorFactor)) {
                    var h = u.baseColorFactor;
                    o.color.fromArray(h), o.opacity = h[3]
                }
                if (void 0 !== u.baseColorTexture && l.push(this.assignTexture(o, "map", u.baseColorTexture.index)), o.metalness = void 0 !== u.metallicFactor ? u.metallicFactor : 1, o.roughness = void 0 !== u.roughnessFactor ? u.roughnessFactor : 1, void 0 !== u.metallicRoughnessTexture) {
                    var p = u.metallicRoughnessTexture.index;
                    l.push(this.assignTexture(o, "metalnessMap", p)), l.push(this.assignTexture(o, "roughnessMap", p))
                }
            }!0 === a.doubleSided && (o.side = THREE.DoubleSide);
            var f = a.alphaMode || S;
            return f === L ? o.transparent = !0 : (o.transparent = !1, f === M && (o.alphaTest = void 0 !== a.alphaCutoff ? a.alphaCutoff : .5)), void 0 !== a.normalTexture && n !== THREE.MeshBasicMaterial && (l.push(this.assignTexture(o, "normalMap", a.normalTexture.index)), o.normalScale = new THREE.Vector2(1, 1), void 0 !== a.normalTexture.scale && o.normalScale.set(a.normalTexture.scale, a.normalTexture.scale)), void 0 !== a.occlusionTexture && n !== THREE.MeshBasicMaterial && (l.push(this.assignTexture(o, "aoMap", a.occlusionTexture.index)), void 0 !== a.occlusionTexture.strength && (o.aoMapIntensity = a.occlusionTexture.strength)), void 0 !== a.emissiveFactor && n !== THREE.MeshBasicMaterial && (o.emissive = (new THREE.Color).fromArray(a.emissiveFactor)), void 0 !== a.emissiveTexture && n !== THREE.MeshBasicMaterial && l.push(this.assignTexture(o, "emissiveMap", a.emissiveTexture.index)), Promise.all(l).then(function() {
                var e;
                return e = n === THREE.ShaderMaterial ? i[t.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS].createMaterial(o) : new n(o), void 0 !== a.name && (e.name = a.name), e.normalScale && (e.normalScale.y = -e.normalScale.y), e.map && (e.map.encoding = THREE.sRGBEncoding), e.emissiveMap && (e.emissiveMap.encoding = THREE.sRGBEncoding), e.specularMap && (e.specularMap.encoding = THREE.sRGBEncoding), R(e, a), a.extensions && D(i, e, a), e
            })
        }, z.prototype.loadGeometries = function(e) {
            var n, r = this,
                i = this.extensions,
                a = this.primitiveCache,
                o = function(e) {
                    if (e.length < 2) return !1;
                    var t = e[0],
                        n = t.targets || [];
                    if (void 0 === t.indices) return !1;
                    for (var r = 1, i = e.length; r < i; r++) {
                        var a = e[r];
                        if (t.mode !== a.mode) return !1;
                        if (void 0 === a.indices) return !1;
                        if (!O(t.attributes, a.attributes)) return !1;
                        var o = a.targets || [];
                        if (n.length !== o.length) return !1;
                        for (var s = 0, l = n.length; s < l; s++)
                            if (!O(n[s], o[s])) return !1
                    }
                    return !0
                }(e);
            return o && (n = e, e = [e[0]]), this.getDependencies("accessor").then(function(s) {
                for (var l = [], c = 0, d = e.length; c < d; c++) {
                    var u = e[c],
                        h = N(a, u);
                    if (h) l.push(h);
                    else if (u.extensions && u.extensions[t.KHR_DRACO_MESH_COMPRESSION]) {
                        var p = i[t.KHR_DRACO_MESH_COMPRESSION].decodePrimitive(u, r).then(function(e) {
                            return H(e, u, s), e
                        });
                        a.push({
                            primitive: u,
                            promise: p
                        }), l.push(p)
                    } else {
                        var f = new THREE.BufferGeometry;
                        H(f, u, s);
                        p = Promise.resolve(f);
                        a.push({
                            primitive: u,
                            promise: p
                        }), l.push(p)
                    }
                }
                return Promise.all(l).then(function(t) {
                    if (o) {
                        var i = t[0];
                        if (null !== (g = function(e, t, n) {
                                for (var r = 0, i = e.length; r < i; r++) {
                                    var a = e[r];
                                    if (t === a.baseGeometry && U(n, a.primitives)) return a.geometry
                                }
                                return null
                            }(v = r.multiPassGeometryCache, i, n))) return [g.geometry];
                        var a = new THREE.BufferGeometry;
                        for (var l in a.name = i.name, a.userData = i.userData, i.attributes) a.addAttribute(l, i.attributes[l]);
                        for (var l in i.morphAttributes) a.morphAttributes[l] = i.morphAttributes[l];
                        for (var c = [], d = 0, u = 0, h = n.length; u < h; u++) {
                            for (var p = s[n[u].indices], f = 0, m = p.count; f < m; f++) c.push(p.array[f]);
                            a.addGroup(d, p.count, u), d += p.count
                        }
                        return a.setIndex(c), v.push({
                            geometry: a,
                            baseGeometry: i,
                            primitives: n
                        }), [a]
                    }
                    if (t.length > 1 && void 0 !== THREE.BufferGeometryUtils) {
                        for (u = 1, h = e.length; u < h; u++)
                            if (e[0].mode !== e[u].mode) return t;
                        var v, g;
                        if (g = function(e, t) {
                                for (var n = 0, r = e.length; n < r; n++) {
                                    var i = e[n];
                                    if (U(t, i.baseGeometries)) return i.geometry
                                }
                                return null
                            }(v = r.multiplePrimitivesCache, t)) {
                            if (null !== g.geometry) return [g.geometry]
                        } else {
                            a = THREE.BufferGeometryUtils.mergeBufferGeometries(t, !0);
                            if (v.push({
                                    geometry: a,
                                    baseGeometries: t
                                }), null !== a) return [a]
                        }
                    }
                    return t
                })
            })
        }, z.prototype.loadMesh = function(e) {
            var n = this,
                r = this.json,
                i = this.extensions,
                a = r.meshes[e];
            return this.getMultiDependencies(["accessor", "material"]).then(function(r) {
                for (var o = a.primitives, s = [], l = 0, c = o.length; l < c; l++) s[l] = void 0 === o[l].material ? new THREE.MeshStandardMaterial({
                    color: 16777215,
                    emissive: 0,
                    metalness: 1,
                    roughness: 1,
                    transparent: !1,
                    depthTest: !0,
                    side: THREE.FrontSide
                }) : r.materials[o[l].material];
                return n.loadGeometries(o).then(function(r) {
                    for (var l = 1 === r.length && r[0].groups.length > 0, c = [], d = 0, y = r.length; d < y; d++) {
                        var _, x = r[d],
                            E = o[d],
                            b = l ? s : s[d];
                        if (E.mode === m || E.mode === v || E.mode === g || void 0 === E.mode) _ = !0 === a.isSkinnedMesh ? new THREE.SkinnedMesh(x, b) : new THREE.Mesh(x, b), E.mode === v ? _.drawMode = THREE.TriangleStripDrawMode : E.mode === g && (_.drawMode = THREE.TriangleFanDrawMode);
                        else if (E.mode === h) _ = new THREE.LineSegments(x, b);
                        else if (E.mode === f) _ = new THREE.Line(x, b);
                        else if (E.mode === p) _ = new THREE.LineLoop(x, b);
                        else {
                            if (E.mode !== u) throw new Error("THREE.GLTFLoader: Primitive mode unsupported: " + E.mode);
                            _ = new THREE.Points(x, b)
                        }
                        Object.keys(_.geometry.morphAttributes).length > 0 && A(_, a), _.name = a.name || "mesh_" + e, r.length > 1 && (_.name += "_" + d), R(_, a), c.push(_);
                        for (var P = l ? _.material : [_.material], w = void 0 !== x.attributes.color, S = void 0 === x.attributes.normal, M = !0 === _.isSkinnedMesh, L = Object.keys(x.morphAttributes).length > 0, C = L && void 0 !== x.morphAttributes.normal, T = 0, D = P.length; T < D; T++) {
                            b = P[T];
                            if (_.isPoints) {
                                var I = "PointsMaterial:" + b.uuid,
                                    O = n.cache.get(I);
                                O || (O = new THREE.PointsMaterial, THREE.Material.prototype.copy.call(O, b), O.color.copy(b.color), O.map = b.map, O.lights = !1, n.cache.add(I, O)), b = O
                            } else if (_.isLine) {
                                I = "LineBasicMaterial:" + b.uuid;
                                var U = n.cache.get(I);
                                U || (U = new THREE.LineBasicMaterial, THREE.Material.prototype.copy.call(U, b), U.color.copy(b.color), U.lights = !1, n.cache.add(I, U)), b = U
                            }
                            if (w || S || M || L) {
                                I = "ClonedMaterial:" + b.uuid + ":";
                                b.isGLTFSpecularGlossinessMaterial && (I += "specular-glossiness:"), M && (I += "skinning:"), w && (I += "vertex-colors:"), S && (I += "flat-shading:"), L && (I += "morph-targets:"), C && (I += "morph-normals:");
                                var N = n.cache.get(I);
                                N || (N = b.isGLTFSpecularGlossinessMaterial ? i[t.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS].cloneMaterial(b) : b.clone(), M && (N.skinning = !0), w && (N.vertexColors = THREE.VertexColors), S && (N.flatShading = !0), L && (N.morphTargets = !0), C && (N.morphNormals = !0), n.cache.add(I, N)), b = N
                            }
                            P[T] = b, b.aoMap && void 0 === x.attributes.uv2 && void 0 !== x.attributes.uv && (console.log("THREE.GLTFLoader: Duplicating UVs to support aoMap."), x.addAttribute("uv2", new THREE.BufferAttribute(x.attributes.uv.array, 2))), b.isGLTFSpecularGlossinessMaterial && (_.onBeforeRender = i[t.KHR_MATERIALS_PBR_SPECULAR_GLOSSINESS].refreshUniforms)
                        }
                        _.material = l ? P : P[0]
                    }
                    if (1 === c.length) return c[0];
                    var F = new THREE.Group;
                    for (d = 0, y = c.length; d < y; d++) F.add(c[d]);
                    return F
                })
            })
        }, z.prototype.loadCamera = function(e) {
            var t, n = this.json.cameras[e],
                r = n[n.type];
            if (r) return "perspective" === n.type ? t = new THREE.PerspectiveCamera(THREE.Math.radToDeg(r.yfov), r.aspectRatio || 1, r.znear || 1, r.zfar || 2e6) : "orthographic" === n.type && (t = new THREE.OrthographicCamera(r.xmag / -2, r.xmag / 2, r.ymag / 2, r.ymag / -2, r.znear, r.zfar)), void 0 !== n.name && (t.name = n.name), R(t, n), Promise.resolve(t);
            console.warn("THREE.GLTFLoader: Missing camera parameters.")
        }, z.prototype.loadSkin = function(e) {
            var t = this.json.skins[e],
                n = {
                    joints: t.joints
                };
            return void 0 === t.inverseBindMatrices ? Promise.resolve(n) : this.getDependency("accessor", t.inverseBindMatrices).then(function(e) {
                return n.inverseBindMatrices = e, n
            })
        }, z.prototype.loadAnimation = function(e) {
            var t = this.json.animations[e];
            return this.getMultiDependencies(["accessor", "node"]).then(function(n) {
                for (var r = [], i = 0, a = t.channels.length; i < a; i++) {
                    var o = t.channels[i],
                        s = t.samplers[o.sampler];
                    if (s) {
                        var l = o.target,
                            c = void 0 !== l.node ? l.node : l.id,
                            u = void 0 !== t.parameters ? t.parameters[s.input] : s.input,
                            h = void 0 !== t.parameters ? t.parameters[s.output] : s.output,
                            p = n.accessors[u],
                            f = n.accessors[h],
                            m = n.nodes[c];
                        if (m) {
                            var v;
                            switch (m.updateMatrix(), m.matrixAutoUpdate = !0, P[l.path]) {
                                case P.weights:
                                    v = THREE.NumberKeyframeTrack;
                                    break;
                                case P.rotation:
                                    v = THREE.QuaternionKeyframeTrack;
                                    break;
                                case P.position:
                                case P.scale:
                                default:
                                    v = THREE.VectorKeyframeTrack
                            }
                            var g = m.name ? m.name : m.uuid,
                                y = void 0 !== s.interpolation ? w[s.interpolation] : THREE.InterpolateLinear,
                                _ = [];
                            P[l.path] === P.weights ? m.traverse(function(e) {
                                !0 === e.isMesh && e.morphTargetInfluences && _.push(e.name ? e.name : e.uuid)
                            }) : _.push(g);
                            for (var x = 0, E = _.length; x < E; x++) {
                                var b = new v(_[x] + "." + P[l.path], THREE.AnimationUtils.arraySlice(p.array, 0), THREE.AnimationUtils.arraySlice(f.array, 0), y);
                                "CUBICSPLINE" === s.interpolation && (b.createInterpolant = function(e) {
                                    return new d(this.times, this.values, this.getValueSize() / 3, e)
                                }, b.createInterpolant.isInterpolantFactoryMethodGLTFCubicSpline = !0), r.push(b)
                            }
                        }
                    }
                }
                c = void 0 !== t.name ? t.name : "animation_" + e;
                return new THREE.AnimationClip(c, void 0, r)
            })
        }, z.prototype.loadNode = function(e) {
            var n = this.json,
                r = this.extensions,
                i = n.meshReferences,
                a = n.meshUses,
                o = n.nodes[e];
            return this.getMultiDependencies(["mesh", "skin", "camera", "light"]).then(function(e) {
                var n;
                if (!0 === o.isBone) n = new THREE.Bone;
                else if (void 0 !== o.mesh) {
                    var s = e.meshes[o.mesh];
                    if (i[o.mesh] > 1) {
                        var l = a[o.mesh]++;
                        (n = s.clone()).name += "_instance_" + l, n.onBeforeRender = s.onBeforeRender;
                        for (var c = 0, d = n.children.length; c < d; c++) n.children[c].name += "_instance_" + l, n.children[c].onBeforeRender = s.children[c].onBeforeRender
                    } else n = s
                } else if (void 0 !== o.camera) n = e.cameras[o.camera];
                else if (o.extensions && o.extensions[t.KHR_LIGHTS_PUNCTUAL] && void 0 !== o.extensions[t.KHR_LIGHTS_PUNCTUAL].light) {
                    n = r[t.KHR_LIGHTS_PUNCTUAL].lights[o.extensions[t.KHR_LIGHTS_PUNCTUAL].light]
                } else n = new THREE.Object3D;
                if (void 0 !== o.name && (n.name = THREE.PropertyBinding.sanitizeNodeName(o.name)), R(n, o), o.extensions && D(r, n, o), void 0 !== o.matrix) {
                    var u = new THREE.Matrix4;
                    u.fromArray(o.matrix), n.applyMatrix(u)
                } else void 0 !== o.translation && n.position.fromArray(o.translation), void 0 !== o.rotation && n.quaternion.fromArray(o.rotation), void 0 !== o.scale && n.scale.fromArray(o.scale);
                return n
            })
        }, z.prototype.loadScene = function() {
            function e(t, n, r, i, a) {
                var o = i[t],
                    s = r.nodes[t];
                if (void 0 !== s.skin)
                    for (var l = !0 === o.isGroup ? o.children : [o], c = 0, d = l.length; c < d; c++) {
                        for (var u = l[c], h = a[s.skin], p = [], f = [], m = 0, v = h.joints.length; m < v; m++) {
                            var g = h.joints[m],
                                y = i[g];
                            if (y) {
                                p.push(y);
                                var _ = new THREE.Matrix4;
                                void 0 !== h.inverseBindMatrices && _.fromArray(h.inverseBindMatrices.array, 16 * m), f.push(_)
                            } else console.warn('THREE.GLTFLoader: Joint "%s" could not be found.', g)
                        }
                        u.bind(new THREE.Skeleton(p, f), u.matrixWorld)
                    }
                if (n.add(o), s.children) {
                    var x = s.children;
                    for (c = 0, d = x.length; c < d; c++) {
                        e(x[c], o, r, i, a)
                    }
                }
            }
            return function(t) {
                var n = this.json,
                    r = this.extensions,
                    i = this.json.scenes[t];
                return this.getMultiDependencies(["node", "skin"]).then(function(t) {
                    var a = new THREE.Scene;
                    void 0 !== i.name && (a.name = i.name), R(a, i), i.extensions && D(r, a, i);
                    for (var o = i.nodes || [], s = 0, l = o.length; s < l; s++) e(o[s], a, n, t.nodes, t.skins);
                    return a
                })
            }
        }(), e
    }()
}, function(e, t, n) {
    "use strict";
    THREE.DRACOLoader = function(e) {
        this.timeLoaded = 0, this.manager = e || THREE.DefaultLoadingManager, this.materials = null, this.verbosity = 0, this.attributeOptions = {}, this.drawMode = THREE.TrianglesDrawMode, this.nativeAttributeMap = {
            position: "POSITION",
            normal: "NORMAL",
            color: "COLOR",
            uv: "TEX_COORD"
        }
    }, THREE.DRACOLoader.prototype = {
        constructor: THREE.DRACOLoader,
        load: function(e, t, n, r) {
            var i = this,
                a = new THREE.FileLoader(i.manager);
            a.setPath(this.path), a.setResponseType("arraybuffer"), a.load(e, function(e) {
                i.decodeDracoFile(e, t)
            }, n, r)
        },
        setPath: function(e) {
            return this.path = e, this
        },
        setVerbosity: function(e) {
            return this.verbosity = e, this
        },
        setDrawMode: function(e) {
            return this.drawMode = e, this
        },
        setSkipDequantization: function(e, t) {
            var n = !0;
            return void 0 !== t && (n = t), this.getAttributeOptions(e).skipDequantization = n, this
        },
        decodeDracoFile: function(e, t, n, r) {
            var i = this;
            THREE.DRACOLoader.getDecoderModule().then(function(a) {
                i.decodeDracoFileInternal(e, a.decoder, t, n || {}, r || {})
            })
        },
        decodeDracoFileInternal: function(e, t, n, r, i) {
            var a = new t.DecoderBuffer;
            a.Init(new Int8Array(e), e.byteLength);
            var o = new t.Decoder,
                s = o.GetEncodedGeometryType(a);
            if (s == t.TRIANGULAR_MESH) this.verbosity > 0 && console.log("Loaded a mesh.");
            else {
                if (s != t.POINT_CLOUD) {
                    var l = "THREE.DRACOLoader: Unknown geometry type.";
                    throw console.error(l), new Error(l)
                }
                this.verbosity > 0 && console.log("Loaded a point cloud.")
            }
            n(this.convertDracoGeometryTo3JS(t, o, s, a, r, i))
        },
        addAttributeToGeometry: function(e, t, n, r, i, a, o, s) {
            if (0 === a.ptr) {
                var l = "THREE.DRACOLoader: No attribute " + r;
                throw console.error(l), new Error(l)
            }
            var c, d, u = a.num_components(),
                h = n.num_points() * u;
            switch (i) {
                case Float32Array:
                    c = new e.DracoFloat32Array, t.GetAttributeFloatForAllPoints(n, a, c), s[r] = new Float32Array(h), d = THREE.Float32BufferAttribute;
                    break;
                case Int8Array:
                    c = new e.DracoInt8Array, t.GetAttributeInt8ForAllPoints(n, a, c), s[r] = new Int8Array(h), d = THREE.Int8BufferAttribute;
                    break;
                case Int16Array:
                    c = new e.DracoInt16Array, t.GetAttributeInt16ForAllPoints(n, a, c), s[r] = new Int16Array(h), d = THREE.Int16BufferAttribute;
                    break;
                case Int32Array:
                    c = new e.DracoInt32Array, t.GetAttributeInt32ForAllPoints(n, a, c), s[r] = new Int32Array(h), d = THREE.Int32BufferAttribute;
                    break;
                case Uint8Array:
                    c = new e.DracoUInt8Array, t.GetAttributeUInt8ForAllPoints(n, a, c), s[r] = new Uint8Array(h), d = THREE.Uint8BufferAttribute;
                    break;
                case Uint16Array:
                    c = new e.DracoUInt16Array, t.GetAttributeUInt16ForAllPoints(n, a, c), s[r] = new Uint16Array(h), d = THREE.Uint16BufferAttribute;
                    break;
                case Uint32Array:
                    c = new e.DracoUInt32Array, t.GetAttributeUInt32ForAllPoints(n, a, c), s[r] = new Uint32Array(h), d = THREE.Uint32BufferAttribute;
                    break;
                default:
                    l = "THREE.DRACOLoader: Unexpected attribute type.";
                    throw console.error(l), new Error(l)
            }
            for (var p = 0; p < h; p++) s[r][p] = c.GetValue(p);
            o.addAttribute(r, new d(s[r], u)), e.destroy(c)
        },
        convertDracoGeometryTo3JS: function(e, t, n, r, i, a) {
            var o, s;
            !0 === this.getAttributeOptions("position").skipDequantization && t.SkipAttributeTransform(e.POSITION);
            const l = performance.now();
            if (n === e.TRIANGULAR_MESH ? (o = new e.Mesh, s = t.DecodeBufferToMesh(r, o)) : (o = new e.PointCloud, s = t.DecodeBufferToPointCloud(r, o)), !s.ok() || 0 == o.ptr) {
                var c = "THREE.DRACOLoader: Decoding failed: ";
                throw c += s.error_msg(), console.error(c), e.destroy(t), e.destroy(o), new Error(c)
            }
            var d, u = performance.now();
            e.destroy(r), n == e.TRIANGULAR_MESH ? (d = o.num_faces(), this.verbosity > 0 && console.log("Number of faces loaded: " + d.toString())) : d = 0;
            var h = o.num_points(),
                p = o.num_attributes();
            this.verbosity > 0 && (console.log("Number of points loaded: " + h.toString()), console.log("Number of attributes loaded: " + p.toString()));
            var f = t.GetAttributeId(o, e.POSITION);
            if (-1 == f) {
                c = "THREE.DRACOLoader: No position attribute found.";
                throw console.error(c), e.destroy(t), e.destroy(o), new Error(c)
            }
            var m = t.GetAttribute(o, f),
                v = {},
                g = new THREE.BufferGeometry;
            for (var y in this.nativeAttributeMap)
                if (void 0 === i[y]) {
                    var _ = t.GetAttributeId(o, e[this.nativeAttributeMap[y]]);
                    if (-1 !== _) {
                        this.verbosity > 0 && console.log("Loaded " + y + " attribute.");
                        var x = t.GetAttribute(o, _);
                        this.addAttributeToGeometry(e, t, o, y, Float32Array, x, g, v)
                    }
                }
            for (var y in i) {
                var E = a[y] || Float32Array,
                    b = i[y];
                x = t.GetAttributeByUniqueId(o, b);
                this.addAttributeToGeometry(e, t, o, y, E, x, g, v)
            }
            if (n == e.TRIANGULAR_MESH)
                if (this.drawMode === THREE.TriangleStripDrawMode) {
                    var P = new e.DracoInt32Array;
                    t.GetTriangleStripsFromMesh(o, P);
                    v.indices = new Uint32Array(P.size());
                    for (var w = 0; w < P.size(); ++w) v.indices[w] = P.GetValue(w);
                    e.destroy(P)
                } else {
                    var S = 3 * d;
                    v.indices = new Uint32Array(S);
                    var M = new e.DracoInt32Array;
                    for (w = 0; w < d; ++w) {
                        t.GetFaceFromMesh(o, w, M);
                        var L = 3 * w;
                        v.indices[L] = M.GetValue(0), v.indices[L + 1] = M.GetValue(1), v.indices[L + 2] = M.GetValue(2)
                    }
                    e.destroy(M)
                }
            g.drawMode = this.drawMode, n == e.TRIANGULAR_MESH && g.setIndex(new(v.indices.length > 65535 ? THREE.Uint32BufferAttribute : THREE.Uint16BufferAttribute)(v.indices, 1));
            var C = new e.AttributeQuantizationTransform;
            if (C.InitFromAttribute(m)) {
                g.attributes.position.isQuantized = !0, g.attributes.position.maxRange = C.range(), g.attributes.position.numQuantizationBits = C.quantization_bits(), g.attributes.position.minValues = new Float32Array(3);
                for (w = 0; w < 3; ++w) g.attributes.position.minValues[w] = C.min_value(w)
            }
            return e.destroy(C), e.destroy(t), e.destroy(o), this.decode_time = u - l, this.import_time = performance.now() - u, this.verbosity > 0 && (console.log("Decode time: " + this.decode_time), console.log("Import time: " + this.import_time)), g
        },
        isVersionSupported: function(e, t) {
            THREE.DRACOLoader.getDecoderModule().then(function(n) {
                t(n.decoder.isVersionSupported(e))
            })
        },
        getAttributeOptions: function(e) {
            return void 0 === this.attributeOptions[e] && (this.attributeOptions[e] = {}), this.attributeOptions[e]
        }
    }, THREE.DRACOLoader.decoderPath = "./", THREE.DRACOLoader.decoderConfig = {}, THREE.DRACOLoader.decoderModulePromise = null, THREE.DRACOLoader.setDecoderPath = function(e) {
        THREE.DRACOLoader.decoderPath = e
    }, THREE.DRACOLoader.setDecoderConfig = function(e) {
        var t = THREE.DRACOLoader.decoderConfig.wasmBinary;
        THREE.DRACOLoader.decoderConfig = e || {}, THREE.DRACOLoader.releaseDecoderModule(), t && (THREE.DRACOLoader.decoderConfig.wasmBinary = t)
    }, THREE.DRACOLoader.releaseDecoderModule = function() {
        THREE.DRACOLoader.decoderModulePromise = null
    }, THREE.DRACOLoader.getDecoderModule = function() {
        var e = this,
            t = THREE.DRACOLoader.decoderPath,
            n = THREE.DRACOLoader.decoderConfig,
            r = THREE.DRACOLoader.decoderModulePromise;
        return r || ("undefined" != typeof DracoDecoderModule ? r = Promise.resolve() : "object" != typeof WebAssembly || "js" === n.type ? r = THREE.DRACOLoader._loadScript(t + "draco_decoder.js") : (n.wasmBinaryFile = t + "draco_decoder.wasm", r = THREE.DRACOLoader._loadScript(t + "draco_wasm_wrapper.js").then(function() {
            return THREE.DRACOLoader._loadArrayBuffer(n.wasmBinaryFile)
        }).then(function(e) {
            n.wasmBinary = e
        })), r = r.then(function() {
            return new Promise(function(t) {
                n.onModuleLoaded = function(n) {
                    e.timeLoaded = performance.now(), t({
                        decoder: n
                    })
                }, DracoDecoderModule(n)
            })
        }), THREE.DRACOLoader.decoderModulePromise = r, r)
    }, THREE.DRACOLoader._loadScript = function(e) {
        var t = document.getElementById("decoder_script");
        null !== t && t.parentNode.removeChild(t);
        var n = document.getElementsByTagName("head")[0],
            r = document.createElement("script");
        return r.id = "decoder_script", r.type = "text/javascript", r.src = e, new Promise(function(e) {
            r.onload = e, n.appendChild(r)
        })
    }, THREE.DRACOLoader._loadArrayBuffer = function(e) {
        var t = new THREE.FileLoader;
        return t.setResponseType("arraybuffer"), new Promise(function(n, r) {
            t.load(e, n, void 0, r)
        })
    }
}, function(e, t) {
    THREE.OBJLoader = function() {
        var e = /^[og]\s*(.+)?/,
            t = /^mtllib /,
            n = /^usemtl /;

        function r(e) {
            this.manager = void 0 !== e ? e : THREE.DefaultLoadingManager, this.materials = null
        }
        return r.prototype = {
            constructor: r,
            load: function(e, t, n, r) {
                var i = this,
                    a = new THREE.FileLoader(i.manager);
                a.setPath(this.path), a.load(e, function(e) {
                    t(i.parse(e))
                }, n, r)
            },
            setPath: function(e) {
                return this.path = e, this
            },
            setMaterials: function(e) {
                return this.materials = e, this
            },
            parse: function(r) {
                console.time("OBJLoader");
                var i = new function() {
                    var e = {
                        objects: [],
                        object: {},
                        vertices: [],
                        normals: [],
                        colors: [],
                        uvs: [],
                        materialLibraries: [],
                        startObject: function(e, t) {
                            if (this.object && !1 === this.object.fromDeclaration) return this.object.name = e, void(this.object.fromDeclaration = !1 !== t);
                            var n = this.object && "function" == typeof this.object.currentMaterial ? this.object.currentMaterial() : void 0;
                            if (this.object && "function" == typeof this.object._finalize && this.object._finalize(!0), this.object = {
                                    name: e || "",
                                    fromDeclaration: !1 !== t,
                                    geometry: {
                                        vertices: [],
                                        normals: [],
                                        colors: [],
                                        uvs: []
                                    },
                                    materials: [],
                                    smooth: !0,
                                    startMaterial: function(e, t) {
                                        var n = this._finalize(!1);
                                        n && (n.inherited || n.groupCount <= 0) && this.materials.splice(n.index, 1);
                                        var r = {
                                            index: this.materials.length,
                                            name: e || "",
                                            mtllib: Array.isArray(t) && t.length > 0 ? t[t.length - 1] : "",
                                            smooth: void 0 !== n ? n.smooth : this.smooth,
                                            groupStart: void 0 !== n ? n.groupEnd : 0,
                                            groupEnd: -1,
                                            groupCount: -1,
                                            inherited: !1,
                                            clone: function(e) {
                                                var t = {
                                                    index: "number" == typeof e ? e : this.index,
                                                    name: this.name,
                                                    mtllib: this.mtllib,
                                                    smooth: this.smooth,
                                                    groupStart: 0,
                                                    groupEnd: -1,
                                                    groupCount: -1,
                                                    inherited: !1
                                                };
                                                return t.clone = this.clone.bind(t), t
                                            }
                                        };
                                        return this.materials.push(r), r
                                    },
                                    currentMaterial: function() {
                                        if (this.materials.length > 0) return this.materials[this.materials.length - 1]
                                    },
                                    _finalize: function(e) {
                                        var t = this.currentMaterial();
                                        if (t && -1 === t.groupEnd && (t.groupEnd = this.geometry.vertices.length / 3, t.groupCount = t.groupEnd - t.groupStart, t.inherited = !1), e && this.materials.length > 1)
                                            for (var n = this.materials.length - 1; n >= 0; n--) this.materials[n].groupCount <= 0 && this.materials.splice(n, 1);
                                        return e && 0 === this.materials.length && this.materials.push({
                                            name: "",
                                            smooth: this.smooth
                                        }), t
                                    }
                                }, n && n.name && "function" == typeof n.clone) {
                                var r = n.clone(0);
                                r.inherited = !0, this.object.materials.push(r)
                            }
                            this.objects.push(this.object)
                        },
                        finalize: function() {
                            this.object && "function" == typeof this.object._finalize && this.object._finalize(!0)
                        },
                        parseVertexIndex: function(e, t) {
                            var n = parseInt(e, 10);
                            return 3 * (n >= 0 ? n - 1 : n + t / 3)
                        },
                        parseNormalIndex: function(e, t) {
                            var n = parseInt(e, 10);
                            return 3 * (n >= 0 ? n - 1 : n + t / 3)
                        },
                        parseUVIndex: function(e, t) {
                            var n = parseInt(e, 10);
                            return 2 * (n >= 0 ? n - 1 : n + t / 2)
                        },
                        addVertex: function(e, t, n) {
                            var r = this.vertices,
                                i = this.object.geometry.vertices;
                            i.push(r[e + 0], r[e + 1], r[e + 2]), i.push(r[t + 0], r[t + 1], r[t + 2]), i.push(r[n + 0], r[n + 1], r[n + 2])
                        },
                        addVertexPoint: function(e) {
                            var t = this.vertices;
                            this.object.geometry.vertices.push(t[e + 0], t[e + 1], t[e + 2])
                        },
                        addVertexLine: function(e) {
                            var t = this.vertices;
                            this.object.geometry.vertices.push(t[e + 0], t[e + 1], t[e + 2])
                        },
                        addNormal: function(e, t, n) {
                            var r = this.normals,
                                i = this.object.geometry.normals;
                            i.push(r[e + 0], r[e + 1], r[e + 2]), i.push(r[t + 0], r[t + 1], r[t + 2]), i.push(r[n + 0], r[n + 1], r[n + 2])
                        },
                        addColor: function(e, t, n) {
                            var r = this.colors,
                                i = this.object.geometry.colors;
                            i.push(r[e + 0], r[e + 1], r[e + 2]), i.push(r[t + 0], r[t + 1], r[t + 2]), i.push(r[n + 0], r[n + 1], r[n + 2])
                        },
                        addUV: function(e, t, n) {
                            var r = this.uvs,
                                i = this.object.geometry.uvs;
                            i.push(r[e + 0], r[e + 1]), i.push(r[t + 0], r[t + 1]), i.push(r[n + 0], r[n + 1])
                        },
                        addUVLine: function(e) {
                            var t = this.uvs;
                            this.object.geometry.uvs.push(t[e + 0], t[e + 1])
                        },
                        addFace: function(e, t, n, r, i, a, o, s, l) {
                            var c = this.vertices.length,
                                d = this.parseVertexIndex(e, c),
                                u = this.parseVertexIndex(t, c),
                                h = this.parseVertexIndex(n, c);
                            if (this.addVertex(d, u, h), void 0 !== r && "" !== r) {
                                var p = this.uvs.length;
                                d = this.parseUVIndex(r, p), u = this.parseUVIndex(i, p), h = this.parseUVIndex(a, p), this.addUV(d, u, h)
                            }
                            if (void 0 !== o && "" !== o) {
                                var f = this.normals.length;
                                d = this.parseNormalIndex(o, f), u = o === s ? d : this.parseNormalIndex(s, f), h = o === l ? d : this.parseNormalIndex(l, f), this.addNormal(d, u, h)
                            }
                            this.colors.length > 0 && this.addColor(d, u, h)
                        },
                        addPointGeometry: function(e) {
                            this.object.geometry.type = "Points";
                            for (var t = this.vertices.length, n = 0, r = e.length; n < r; n++) this.addVertexPoint(this.parseVertexIndex(e[n], t))
                        },
                        addLineGeometry: function(e, t) {
                            this.object.geometry.type = "Line";
                            for (var n = this.vertices.length, r = this.uvs.length, i = 0, a = e.length; i < a; i++) this.addVertexLine(this.parseVertexIndex(e[i], n));
                            var o = 0;
                            for (a = t.length; o < a; o++) this.addUVLine(this.parseUVIndex(t[o], r))
                        }
                    };
                    return e.startObject("", !1), e
                }; - 1 !== r.indexOf("\r\n") && (r = r.replace(/\r\n/g, "\n")), -1 !== r.indexOf("\\\n") && (r = r.replace(/\\\n/g, ""));
                for (var a = r.split("\n"), o = "", s = "", l = [], c = "function" == typeof "".trimLeft, d = 0, u = a.length; d < u; d++)
                    if (o = a[d], 0 !== (o = c ? o.trimLeft() : o.trim()).length && "#" !== (s = o.charAt(0)))
                        if ("v" === s) {
                            var h = o.split(/\s+/);
                            switch (h[0]) {
                                case "v":
                                    i.vertices.push(parseFloat(h[1]), parseFloat(h[2]), parseFloat(h[3])), 8 === h.length && i.colors.push(parseFloat(h[4]), parseFloat(h[5]), parseFloat(h[6]));
                                    break;
                                case "vn":
                                    i.normals.push(parseFloat(h[1]), parseFloat(h[2]), parseFloat(h[3]));
                                    break;
                                case "vt":
                                    i.uvs.push(parseFloat(h[1]), parseFloat(h[2]))
                            }
                        } else if ("f" === s) {
                    for (var p = o.substr(1).trim().split(/\s+/), f = [], m = 0, v = p.length; m < v; m++) {
                        var g = p[m];
                        if (g.length > 0) {
                            var y = g.split("/");
                            f.push(y)
                        }
                    }
                    var _ = f[0];
                    for (m = 1, v = f.length - 1; m < v; m++) {
                        var x = f[m],
                            E = f[m + 1];
                        i.addFace(_[0], x[0], E[0], _[1], x[1], E[1], _[2], x[2], E[2])
                    }
                } else if ("l" === s) {
                    var b = o.substring(1).trim().split(" "),
                        P = [],
                        w = [];
                    if (-1 === o.indexOf("/")) P = b;
                    else
                        for (var S = 0, M = b.length; S < M; S++) {
                            var L = b[S].split("/");
                            "" !== L[0] && P.push(L[0]), "" !== L[1] && w.push(L[1])
                        }
                    i.addLineGeometry(P, w)
                } else if ("p" === s) {
                    var C = o.substr(1).trim().split(" ");
                    i.addPointGeometry(C)
                } else if (null !== (l = e.exec(o))) {
                    var T = (" " + l[0].substr(1).trim()).substr(1);
                    i.startObject(T)
                } else if (n.test(o)) i.object.startMaterial(o.substring(7).trim(), i.materialLibraries);
                else if (t.test(o)) i.materialLibraries.push(o.substring(7).trim());
                else {
                    if ("s" !== s) {
                        if ("\0" === o) continue;
                        throw new Error('THREE.OBJLoader: Unexpected line: "' + o + '"')
                    }
                    if ((l = o.split(" ")).length > 1) {
                        var D = l[1].trim().toLowerCase();
                        i.object.smooth = "0" !== D && "off" !== D
                    } else i.object.smooth = !0;
                    (G = i.object.currentMaterial()) && (G.smooth = i.object.smooth)
                }
                i.finalize();
                var R = new THREE.Group;
                R.materialLibraries = [].concat(i.materialLibraries);
                for (d = 0, u = i.objects.length; d < u; d++) {
                    var A = i.objects[d],
                        I = A.geometry,
                        O = A.materials,
                        U = "Line" === I.type,
                        N = "Points" === I.type,
                        F = !1;
                    if (0 !== I.vertices.length) {
                        var z = new THREE.BufferGeometry;
                        z.addAttribute("position", new THREE.Float32BufferAttribute(I.vertices, 3)), I.normals.length > 0 ? z.addAttribute("normal", new THREE.Float32BufferAttribute(I.normals, 3)) : z.computeVertexNormals(), I.colors.length > 0 && (F = !0, z.addAttribute("color", new THREE.Float32BufferAttribute(I.colors, 3))), I.uvs.length > 0 && z.addAttribute("uv", new THREE.Float32BufferAttribute(I.uvs, 2));
                        for (var H, j = [], V = 0, k = O.length; V < k; V++) {
                            var B = O[V],
                                G = void 0;
                            if (null !== this.materials)
                                if (G = this.materials.create(B.name), !U || !G || G instanceof THREE.LineBasicMaterial) {
                                    if (N && G && !(G instanceof THREE.PointsMaterial)) {
                                        var $ = new THREE.PointsMaterial({
                                            size: 10,
                                            sizeAttenuation: !1
                                        });
                                        q.copy(G), G = $
                                    }
                                } else {
                                    var q = new THREE.LineBasicMaterial;
                                    q.copy(G), q.lights = !1, G = q
                                }
                            G || ((G = U ? new THREE.LineBasicMaterial : N ? new THREE.PointsMaterial({
                                size: 1,
                                sizeAttenuation: !1
                            }) : new THREE.MeshPhongMaterial).name = B.name), G.flatShading = !B.smooth, G.vertexColors = F ? THREE.VertexColors : THREE.NoColors, j.push(G)
                        }
                        if (j.length > 1) {
                            for (V = 0, k = O.length; V < k; V++) {
                                B = O[V];
                                z.addGroup(B.groupStart, B.groupCount, V)
                            }
                            H = U ? new THREE.LineSegments(z, j) : N ? new THREE.Points(z, j) : new THREE.Mesh(z, j)
                        } else H = U ? new THREE.LineSegments(z, j[0]) : N ? new THREE.Points(z, j[0]) : new THREE.Mesh(z, j[0]);
                        H.name = A.name, R.add(H)
                    }
                }
                return console.timeEnd("OBJLoader"), R
            }
        }, r
    }()
}, function(e, t) {
    THREE.PLYLoader = function(e) {
        this.manager = void 0 !== e ? e : THREE.DefaultLoadingManager, this.propertyNameMapping = {}
    }, THREE.PLYLoader.prototype = {
        constructor: THREE.PLYLoader,
        load: function(e, t, n, r) {
            var i = this,
                a = new THREE.FileLoader(this.manager);
            a.setPath(this.path), a.setResponseType("arraybuffer"), a.load(e, function(e) {
                t(i.parse(e))
            }, n, r)
        },
        setPath: function(e) {
            return this.path = e, this
        },
        setPropertyNameMapping: function(e) {
            this.propertyNameMapping = e
        },
        parse: function(e) {
            function t(e) {
                var t = "",
                    n = 0,
                    r = /ply([\s\S]*)end_header\s/.exec(e);
                null !== r && (t = r[1], n = r[0].length);
                var i, a, o, s = {
                        comments: [],
                        elements: [],
                        headerLength: n
                    },
                    l = t.split("\n");

                function c(e, t) {
                    var n = {
                        type: e[0]
                    };
                    return "list" === n.type ? (n.name = e[3], n.countType = e[1], n.itemType = e[2]) : n.name = e[1], n.name in t && (n.name = t[n.name]), n
                }
                for (var u = 0; u < l.length; u++) {
                    var h = l[u];
                    if ("" !== (h = h.trim())) switch (a = (o = h.split(/\s+/)).shift(), h = o.join(" "), a) {
                        case "format":
                            s.format = o[0], s.version = o[1];
                            break;
                        case "comment":
                            s.comments.push(h);
                            break;
                        case "element":
                            void 0 !== i && s.elements.push(i), (i = {}).name = o[0], i.count = parseInt(o[1]), i.properties = [];
                            break;
                        case "property":
                            i.properties.push(c(o, d.propertyNameMapping));
                            break;
                        default:
                            console.log("unhandled", a, o)
                    }
                }
                return void 0 !== i && s.elements.push(i), s
            }

            function n(e, t) {
                switch (t) {
                    case "char":
                    case "uchar":
                    case "short":
                    case "ushort":
                    case "int":
                    case "uint":
                    case "int8":
                    case "uint8":
                    case "int16":
                    case "uint16":
                    case "int32":
                    case "uint32":
                        return parseInt(e);
                    case "float":
                    case "double":
                    case "float32":
                    case "float64":
                        return parseFloat(e)
                }
            }

            function r(e, t) {
                for (var r = t.split(/\s+/), i = {}, a = 0; a < e.length; a++)
                    if ("list" === e[a].type) {
                        for (var o = [], s = n(r.shift(), e[a].countType), l = 0; l < s; l++) o.push(n(r.shift(), e[a].itemType));
                        i[e[a].name] = o
                    } else i[e[a].name] = n(r.shift(), e[a].type);
                return i
            }

            function i(e, t) {
                var n, i = {
                        indices: [],
                        vertices: [],
                        normals: [],
                        uvs: [],
                        faceVertexUvs: [],
                        colors: []
                    },
                    s = "";
                null !== (n = /end_header\s([\s\S]*)$/.exec(e)) && (s = n[1]);
                for (var l = s.split("\n"), c = 0, d = 0, u = 0; u < l.length; u++) {
                    var h = l[u];
                    if ("" !== (h = h.trim())) {
                        d >= t.elements[c].count && (c++, d = 0);
                        var p = r(t.elements[c].properties, h);
                        o(i, t.elements[c].name, p), d++
                    }
                }
                return a(i)
            }

            function a(e) {
                var t = new THREE.BufferGeometry;
                return e.indices.length > 0 && t.setIndex(e.indices), t.addAttribute("position", new THREE.Float32BufferAttribute(e.vertices, 3)), e.normals.length > 0 && t.addAttribute("normal", new THREE.Float32BufferAttribute(e.normals, 3)), e.uvs.length > 0 && t.addAttribute("uv", new THREE.Float32BufferAttribute(e.uvs, 2)), e.colors.length > 0 && t.addAttribute("color", new THREE.Float32BufferAttribute(e.colors, 3)), e.faceVertexUvs.length > 0 && (t = t.toNonIndexed()).addAttribute("uv", new THREE.Float32BufferAttribute(e.faceVertexUvs, 2)), t.computeBoundingSphere(), t
            }

            function o(e, t, n) {
                if ("vertex" === t) e.vertices.push(n.x, n.y, n.z), "nx" in n && "ny" in n && "nz" in n && e.normals.push(n.nx, n.ny, n.nz), "s" in n && "t" in n && e.uvs.push(n.s, n.t), "red" in n && "green" in n && "blue" in n && e.colors.push(n.red / 255, n.green / 255, n.blue / 255);
                else if ("face" === t) {
                    var r = n.vertex_indices || n.vertex_index,
                        i = n.texcoord;
                    3 === r.length ? (e.indices.push(r[0], r[1], r[2]), i && 6 === i.length && (e.faceVertexUvs.push(i[0], i[1]), e.faceVertexUvs.push(i[2], i[3]), e.faceVertexUvs.push(i[4], i[5]))) : 4 === r.length && (e.indices.push(r[0], r[1], r[3]), e.indices.push(r[1], r[2], r[3]))
                }
            }

            function s(e, t, n, r) {
                switch (n) {
                    case "int8":
                    case "char":
                        return [e.getInt8(t), 1];
                    case "uint8":
                    case "uchar":
                        return [e.getUint8(t), 1];
                    case "int16":
                    case "short":
                        return [e.getInt16(t, r), 2];
                    case "uint16":
                    case "ushort":
                        return [e.getUint16(t, r), 2];
                    case "int32":
                    case "int":
                        return [e.getInt32(t, r), 4];
                    case "uint32":
                    case "uint":
                        return [e.getUint32(t, r), 4];
                    case "float32":
                    case "float":
                        return [e.getFloat32(t, r), 4];
                    case "float64":
                    case "double":
                        return [e.getFloat64(t, r), 8]
                }
            }

            function l(e, t, n, r) {
                for (var i, a = {}, o = 0, l = 0; l < n.length; l++)
                    if ("list" === n[l].type) {
                        var c = [],
                            d = (i = s(e, t + o, n[l].countType, r))[0];
                        o += i[1];
                        for (var u = 0; u < d; u++) i = s(e, t + o, n[l].itemType, r), c.push(i[0]), o += i[1];
                        a[n[l].name] = c
                    } else i = s(e, t + o, n[l].type, r), a[n[l].name] = i[0], o += i[1];
                return [a, o]
            }
            var c, d = this;
            if (e instanceof ArrayBuffer) {
                var u = THREE.LoaderUtils.decodeText(new Uint8Array(e)),
                    h = t(u);
                c = "ascii" === h.format ? i(u, h) : function(e, t) {
                    for (var n, r = {
                            indices: [],
                            vertices: [],
                            normals: [],
                            uvs: [],
                            faceVertexUvs: [],
                            colors: []
                        }, i = "binary_little_endian" === t.format, s = new DataView(e, t.headerLength), c = 0, d = 0; d < t.elements.length; d++)
                        for (var u = 0; u < t.elements[d].count; u++) {
                            c += (n = l(s, c, t.elements[d].properties, i))[1];
                            var h = n[0];
                            o(r, t.elements[d].name, h)
                        }
                    return a(r)
                }(e, h)
            } else c = i(e, t(e));
            return c
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(6),
        i = n(3);
    t.Component = i.default, t.Entity = i.Entity;
    const a = n(183),
        o = n(271),
        s = n(11),
        l = [];
    t.default = class extends r.default {
        constructor(e) {
            super({
                knownEvents: !1
            }), this.addEvents("entity", "component"), this.registry = e || new a.default, this._entitiesById = {}, this._entityList = [], this._componentsByType = {}, this._componentsById = {}, this._componentList = [], this._updateWaitList = [], this._sorter = new o.default
        }
        update(e) {
            const t = this._componentList;
            for (let n = 0, r = t.length; n < r; ++n) {
                const r = t[n];
                r.changed && (r.update(e), r.resetChanged())
            }
            this._updateWaitList.forEach(e => e()), this._updateWaitList.length = 0
        }
        tick(e) {
            const t = this._componentList;
            for (let n = 0, r = t.length; n < r; ++n) t[n].tick(e)
        }
        waitForUpdate() {
            return new Promise((e, t) => {
                this._updateWaitList.push(e)
            })
        }
        sort() {
            this._componentList = this._sorter.sort(this._componentList)
        }
        addComponentEventListener(e, t, n) {
            this.on(i.getType(e), t, n)
        }
        removeComponentEventListener(e, t, n) {
            this.off(i.getType(e), t, n)
        }
        createEntity(e) {
            const t = this.doCreateEntity();
            return t.init(this), e && (t.name = e), t
        }
        findOrCreateEntity(e) {
            const t = this.findEntityByName(e);
            return t || this.createEntity(e)
        }
        createComponent(e, t, n) {
            let r;
            return "string" == typeof t ? r = this.registry.createComponent(t, e) : (r = new(t instanceof i.default ? t.constructor : t)).init(e), r && n && (r.name = n), r
        }
        getOrCreateComponent(e, t, n) {
            const r = this.getComponent(t);
            return r || this.createComponent(e, t, n)
        }
        addEntity(e) {
            this._entitiesById[e.id] = e, this._entityList.push(e), this.didAddEntity(e), this.emit("entity", {
                add: !0,
                remove: !1,
                entity: e
            })
        }
        removeEntity(e) {
            this.willRemoveEntity(e), delete this._entitiesById[e.id];
            const t = this._entityList.indexOf(e);
            this._entityList.splice(t, 1), this.emit("entity", {
                add: !1,
                remove: !0,
                entity: e
            })
        }
        getEntities() {
            return this._entityList
        }
        findEntityByName(e) {
            const t = this._entityList;
            for (let n = 0, r = t.length; n < r; ++n)
                if (t[n].name === e) return t[n];
            return null
        }
        getEntityById(e) {
            return this._entitiesById[e]
        }
        getRootEntities() {
            const e = this._entityList,
                t = [];
            for (let n = 0, r = e.length; n < r; ++n) {
                const r = e[n].getComponent(s.default);
                r && r.parent || t.push(e[n])
            }
            return t
        }
        addComponent(e) {
            this._componentList.push(e), this._componentsById[e.id] = e, this.getComponentArrayByType(e.type).push(e), this.didAddComponent(e), this.emit("component", {
                add: !0,
                remove: !1,
                component: e
            }), this.emit(e.type, {
                add: !0,
                remove: !1,
                component: e
            })
        }
        removeComponent(e) {
            if (!this._componentsById[e.id]) return !1;
            this.willRemoveComponent(e), delete this._componentsById[e.id];
            let t = this._componentList.indexOf(e);
            this._componentList.splice(t, 1);
            const n = this._componentsByType[e.type];
            return t = n.indexOf(e), n.splice(t, 1), this.emit("component", {
                add: !1,
                remove: !0,
                component: e
            }), this.emit(e.type, {
                add: !1,
                remove: !0,
                component: e
            }), !0
        }
        addBaseComponent(e, t) {
            const n = i.getType(t);
            this.getComponentArrayByType(n).push(e), this.emit(n, {
                add: !0,
                remove: !1,
                component: e
            })
        }
        removeBaseComponent(e, t) {
            const n = i.getType(t),
                r = this._componentsByType[n],
                a = r.indexOf(e);
            r.splice(a, 1), this.emit(n, {
                add: !1,
                remove: !0,
                component: e
            })
        }
        hasComponents(e) {
            const t = i.getType(e),
                n = this._componentsByType[t];
            return n && n.length > 0
        }
        countComponents(e) {
            const t = e ? this._componentsByType[i.getType(e)] : this._componentList;
            return t ? t.length : 0
        }
        getComponents(e) {
            return e ? this._componentsByType[i.getType(e)] || l : this._componentList
        }
        getComponent(e) {
            const t = this._componentsByType[i.getType(e)];
            return t ? t[0] : void 0
        }
        getComponentById(e) {
            return this._componentsById[e]
        }
        findComponentByName(e, t) {
            return this._componentList.find(n => n.name === e && (!t || n.type === i.getType(t)))
        }
        toString(e = !1) {
            const t = this._entityList,
                n = this._componentList.length,
                r = `System - ${t.length} entities, ${n} components.`;
            return e ? r + "\n" + t.map(e => e.toString(!0)).join("\n") : r
        }
        doCreateEntity() {
            return new i.Entity
        }
        didAddEntity(e) {}
        willRemoveEntity(e) {}
        didAddComponent(e) {}
        willRemoveComponent(e) {}
        getComponentArrayByType(e) {
            let t = this._componentsByType[e];
            return t || (t = this._componentsByType[e] = []), t
        }
    }
}, function(e, t, n) {
    "use strict";
    n.r(t);
    var r = n(37),
        i = n.n(r),
        a = n(38),
        o = n.n(a),
        s = n(39),
        l = n.n(s),
        c = n(40),
        d = n.n(c),
        u = n(41),
        h = n.n(u),
        p = n(42),
        f = n.n(p),
        m = n(43),
        v = n.n(m),
        g = n(44),
        y = n.n(g),
        _ = n(45),
        x = n.n(_),
        E = n(46),
        b = n.n(E),
        P = n(47),
        w = n.n(P),
        S = n(48),
        M = n.n(S),
        L = n(49),
        C = n.n(L),
        T = n(50),
        D = n.n(T),
        R = n(51),
        A = n.n(R),
        I = n(52),
        O = n.n(I),
        U = n(53),
        N = n.n(U),
        F = n(54),
        z = n.n(F),
        H = n(55),
        j = n.n(H),
        V = n(56),
        k = n.n(V),
        B = n(57),
        G = n.n(B),
        $ = n(58),
        q = n.n($),
        X = n(59),
        Y = n.n(X),
        W = n(60),
        Q = n.n(W),
        K = n(61),
        Z = n.n(K),
        J = n(62),
        ee = n.n(J),
        te = n(63),
        ne = n.n(te),
        re = n(64),
        ie = n.n(re),
        ae = n(65),
        oe = n.n(ae),
        se = n(66),
        le = n.n(se),
        ce = n(67),
        de = n.n(ce),
        ue = n(68),
        he = n.n(ue),
        pe = n(69),
        fe = n.n(pe),
        me = n(70),
        ve = n.n(me),
        ge = n(71),
        ye = n.n(ge),
        _e = n(72),
        xe = n.n(_e),
        Ee = n(73),
        be = n.n(Ee),
        Pe = n(74),
        we = n.n(Pe),
        Se = n(75),
        Me = n.n(Se),
        Le = n(76),
        Ce = n.n(Le),
        Te = n(77),
        De = n.n(Te),
        Re = n(78),
        Ae = n.n(Re),
        Ie = n(79),
        Oe = n.n(Ie),
        Ue = n(80),
        Ne = n.n(Ue),
        Fe = n(81),
        ze = n.n(Fe),
        He = n(82),
        je = n.n(He),
        Ve = n(83),
        ke = n.n(Ve),
        Be = n(84),
        Ge = n.n(Be),
        $e = n(85),
        qe = n.n($e),
        Xe = n(86),
        Ye = n.n(Xe),
        We = n(87),
        Qe = n.n(We),
        Ke = n(88),
        Ze = n.n(Ke),
        Je = n(89),
        et = n.n(Je),
        tt = n(90),
        nt = n.n(tt),
        rt = n(91),
        it = n.n(rt),
        at = n(92),
        ot = n.n(at),
        st = n(93),
        lt = n.n(st),
        ct = n(94),
        dt = n.n(ct),
        ut = n(95),
        ht = n.n(ut),
        pt = n(96),
        ft = n.n(pt),
        mt = n(97),
        vt = n.n(mt),
        gt = n(98),
        yt = n.n(gt),
        _t = n(99),
        xt = n.n(_t),
        Et = n(100),
        bt = n.n(Et),
        Pt = n(101),
        wt = n.n(Pt),
        St = n(102),
        Mt = n.n(St),
        Lt = n(103),
        Ct = n.n(Lt),
        Tt = n(104),
        Dt = n.n(Tt),
        Rt = n(105),
        At = n.n(Rt),
        It = n(106),
        Ot = n.n(It),
        Ut = n(107),
        Nt = n.n(Ut),
        Ft = n(108),
        zt = n.n(Ft),
        Ht = n(109),
        jt = n.n(Ht),
        Vt = n(110),
        kt = n.n(Vt),
        Bt = n(111),
        Gt = n.n(Bt),
        $t = n(112),
        qt = n.n($t),
        Xt = n(113),
        Yt = n.n(Xt),
        Wt = n(114),
        Qt = n.n(Wt),
        Kt = n(115),
        Zt = n.n(Kt),
        Jt = n(116),
        en = n.n(Jt),
        tn = n(117),
        nn = n.n(tn),
        rn = n(118),
        an = n.n(rn),
        on = n(119),
        sn = n.n(on),
        ln = n(120),
        cn = n.n(ln),
        dn = n(121),
        un = n.n(dn),
        hn = n(122),
        pn = n.n(hn),
        fn = n(123),
        mn = n.n(fn),
        vn = n(124),
        gn = n.n(vn),
        yn = n(125),
        _n = n.n(yn),
        xn = n(126),
        En = n.n(xn),
        bn = n(127),
        Pn = n.n(bn),
        wn = n(128),
        Sn = n.n(wn),
        Mn = n(129),
        Ln = n.n(Mn),
        Cn = n(130),
        Tn = n.n(Cn),
        Dn = n(131),
        Rn = n.n(Dn),
        An = n(132),
        In = n.n(An),
        On = n(133),
        Un = n.n(On),
        Nn = n(134),
        Fn = n.n(Nn),
        zn = n(135),
        Hn = n.n(zn),
        jn = n(136),
        Vn = n.n(jn),
        kn = n(137),
        Bn = n.n(kn),
        Gn = n(138),
        $n = n.n(Gn),
        qn = n(139),
        Xn = n.n(qn),
        Yn = n(140),
        Wn = n.n(Yn),
        Qn = n(141),
        Kn = n.n(Qn),
        Zn = n(142),
        Jn = n.n(Zn),
        er = n(143),
        tr = n.n(er),
        nr = n(144),
        rr = n.n(nr),
        ir = n(145),
        ar = n.n(ir),
        or = n(146),
        sr = n.n(or),
        lr = n(147),
        cr = n.n(lr),
        dr = n(148),
        ur = n.n(dr),
        hr = n(149),
        pr = n.n(hr),
        fr = n(150),
        mr = n.n(fr),
        vr = n(151),
        gr = n.n(vr),
        yr = n(152),
        _r = n.n(yr),
        xr = n(153),
        Er = n.n(xr),
        br = n(154),
        Pr = n.n(br),
        wr = n(155),
        Sr = n.n(wr),
        Mr = {
            alphamap_fragment: i.a,
            alphamap_pars_fragment: o.a,
            alphatest_fragment: l.a,
            aomap_fragment: d.a,
            aomap_pars_fragment: h.a,
            begin_vertex: f.a,
            beginnormal_vertex: v.a,
            bsdfs: y.a,
            bumpmap_pars_fragment: x.a,
            clipping_planes_fragment: b.a,
            clipping_planes_pars_fragment: w.a,
            clipping_planes_pars_vertex: M.a,
            clipping_planes_vertex: C.a,
            color_fragment: D.a,
            color_pars_fragment: A.a,
            color_pars_vertex: O.a,
            color_vertex: N.a,
            common: z.a,
            cube_uv_reflection_fragment: j.a,
            defaultnormal_vertex: k.a,
            displacementmap_pars_vertex: G.a,
            displacementmap_vertex: q.a,
            emissivemap_fragment: Y.a,
            emissivemap_pars_fragment: Q.a,
            encodings_fragment: Z.a,
            encodings_pars_fragment: ee.a,
            envmap_fragment: ne.a,
            envmap_pars_fragment: ie.a,
            envmap_pars_vertex: oe.a,
            envmap_physical_pars_fragment: Ce.a,
            envmap_vertex: le.a,
            fog_vertex: de.a,
            fog_pars_vertex: he.a,
            fog_fragment: fe.a,
            fog_pars_fragment: ve.a,
            gradientmap_pars_fragment: ye.a,
            lightmap_fragment: xe.a,
            lightmap_pars_fragment: be.a,
            lights_lambert_vertex: we.a,
            lights_pars_begin: Me.a,
            lights_phong_fragment: De.a,
            lights_phong_pars_fragment: Ae.a,
            lights_physical_fragment: Oe.a,
            lights_physical_pars_fragment: Ne.a,
            lights_fragment_begin: ze.a,
            lights_fragment_maps: je.a,
            lights_fragment_end: ke.a,
            logdepthbuf_fragment: Ge.a,
            logdepthbuf_pars_fragment: qe.a,
            logdepthbuf_pars_vertex: Ye.a,
            logdepthbuf_vertex: Qe.a,
            map_fragment: Ze.a,
            map_pars_fragment: et.a,
            map_particle_fragment: nt.a,
            map_particle_pars_fragment: it.a,
            metalnessmap_fragment: ot.a,
            metalnessmap_pars_fragment: lt.a,
            morphnormal_vertex: dt.a,
            morphtarget_pars_vertex: ht.a,
            morphtarget_vertex: ft.a,
            normal_fragment_begin: vt.a,
            normal_fragment_maps: yt.a,
            normalmap_pars_fragment: xt.a,
            packing: bt.a,
            premultiplied_alpha_fragment: wt.a,
            project_vertex: Mt.a,
            dithering_fragment: Ct.a,
            dithering_pars_fragment: Dt.a,
            roughnessmap_fragment: At.a,
            roughnessmap_pars_fragment: Ot.a,
            shadowmap_pars_fragment: Nt.a,
            shadowmap_pars_vertex: zt.a,
            shadowmap_vertex: jt.a,
            shadowmask_pars_fragment: kt.a,
            skinbase_vertex: Gt.a,
            skinning_pars_vertex: qt.a,
            skinning_vertex: Yt.a,
            skinnormal_vertex: Qt.a,
            specularmap_fragment: Zt.a,
            specularmap_pars_fragment: en.a,
            tonemapping_fragment: nn.a,
            tonemapping_pars_fragment: an.a,
            uv_pars_fragment: sn.a,
            uv_pars_vertex: cn.a,
            uv_vertex: un.a,
            uv2_pars_fragment: pn.a,
            uv2_pars_vertex: mn.a,
            uv2_vertex: gn.a,
            worldpos_vertex: _n.a,
            background_frag: En.a,
            background_vert: Pn.a,
            cube_frag: Sn.a,
            cube_vert: Ln.a,
            depth_frag: Tn.a,
            depth_vert: Rn.a,
            distanceRGBA_frag: In.a,
            distanceRGBA_vert: Un.a,
            equirect_frag: Fn.a,
            equirect_vert: Hn.a,
            linedashed_frag: Vn.a,
            linedashed_vert: Bn.a,
            meshbasic_frag: $n.a,
            meshbasic_vert: Xn.a,
            meshlambert_frag: Wn.a,
            meshlambert_vert: Kn.a,
            meshmatcap_frag: Jn.a,
            meshmatcap_vert: tr.a,
            meshphong_frag: rr.a,
            meshphong_vert: ar.a,
            meshphysical_frag: sr.a,
            meshphysical_vert: cr.a,
            normal_frag: ur.a,
            normal_vert: pr.a,
            points_frag: mr.a,
            points_vert: gr.a,
            shadow_frag: _r.a,
            shadow_vert: Er.a,
            sprite_frag: Pr.a,
            sprite_vert: Sr.a
        },
        Lr = n(2),
        Cr = {
            DEG2RAD: Math.PI / 180,
            RAD2DEG: 180 / Math.PI,
            generateUUID: function() {
                for (var e = [], t = 0; t < 256; t++) e[t] = (t < 16 ? "0" : "") + t.toString(16);
                return function() {
                    var t = 4294967295 * Math.random() | 0,
                        n = 4294967295 * Math.random() | 0,
                        r = 4294967295 * Math.random() | 0,
                        i = 4294967295 * Math.random() | 0;
                    return (e[255 & t] + e[t >> 8 & 255] + e[t >> 16 & 255] + e[t >> 24 & 255] + "-" + e[255 & n] + e[n >> 8 & 255] + "-" + e[n >> 16 & 15 | 64] + e[n >> 24 & 255] + "-" + e[63 & r | 128] + e[r >> 8 & 255] + "-" + e[r >> 16 & 255] + e[r >> 24 & 255] + e[255 & i] + e[i >> 8 & 255] + e[i >> 16 & 255] + e[i >> 24 & 255]).toUpperCase()
                }
            }(),
            clamp: function(e, t, n) {
                return Math.max(t, Math.min(n, e))
            },
            euclideanModulo: function(e, t) {
                return (e % t + t) % t
            },
            mapLinear: function(e, t, n, r, i) {
                return r + (e - t) * (i - r) / (n - t)
            },
            lerp: function(e, t, n) {
                return (1 - n) * e + n * t
            },
            smoothstep: function(e, t, n) {
                return e <= t ? 0 : e >= n ? 1 : (e = (e - t) / (n - t)) * e * (3 - 2 * e)
            },
            smootherstep: function(e, t, n) {
                return e <= t ? 0 : e >= n ? 1 : (e = (e - t) / (n - t)) * e * e * (e * (6 * e - 15) + 10)
            },
            randInt: function(e, t) {
                return e + Math.floor(Math.random() * (t - e + 1))
            },
            randFloat: function(e, t) {
                return e + Math.random() * (t - e)
            },
            randFloatSpread: function(e) {
                return e * (.5 - Math.random())
            },
            degToRad: function(e) {
                return e * Cr.DEG2RAD
            },
            radToDeg: function(e) {
                return e * Cr.RAD2DEG
            },
            isPowerOfTwo: function(e) {
                return 0 == (e & e - 1) && 0 !== e
            },
            ceilPowerOfTwo: function(e) {
                return Math.pow(2, Math.ceil(Math.log(e) / Math.LN2))
            },
            floorPowerOfTwo: function(e) {
                return Math.pow(2, Math.floor(Math.log(e) / Math.LN2))
            }
        };

    function Tr() {
        this.elements = [1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1], arguments.length > 0 && console.error("THREE.Matrix4: the constructor no longer reads arguments. use .set() instead.")
    }

    function Dr(e, t, n, r) {
        this._x = e || 0, this._y = t || 0, this._z = n || 0, this._w = void 0 !== r ? r : 1
    }

    function Rr(e, t, n) {
        this.x = e || 0, this.y = t || 0, this.z = n || 0
    }
    Object.assign(Tr.prototype, {
        isMatrix4: !0,
        set: function(e, t, n, r, i, a, o, s, l, c, d, u, h, p, f, m) {
            var v = this.elements;
            return v[0] = e, v[4] = t, v[8] = n, v[12] = r, v[1] = i, v[5] = a, v[9] = o, v[13] = s, v[2] = l, v[6] = c, v[10] = d, v[14] = u, v[3] = h, v[7] = p, v[11] = f, v[15] = m, this
        },
        identity: function() {
            return this.set(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1), this
        },
        clone: function() {
            return (new Tr).fromArray(this.elements)
        },
        copy: function(e) {
            var t = this.elements,
                n = e.elements;
            return t[0] = n[0], t[1] = n[1], t[2] = n[2], t[3] = n[3], t[4] = n[4], t[5] = n[5], t[6] = n[6], t[7] = n[7], t[8] = n[8], t[9] = n[9], t[10] = n[10], t[11] = n[11], t[12] = n[12], t[13] = n[13], t[14] = n[14], t[15] = n[15], this
        },
        copyPosition: function(e) {
            var t = this.elements,
                n = e.elements;
            return t[12] = n[12], t[13] = n[13], t[14] = n[14], this
        },
        extractBasis: function(e, t, n) {
            return e.setFromMatrixColumn(this, 0), t.setFromMatrixColumn(this, 1), n.setFromMatrixColumn(this, 2), this
        },
        makeBasis: function(e, t, n) {
            return this.set(e.x, t.x, n.x, 0, e.y, t.y, n.y, 0, e.z, t.z, n.z, 0, 0, 0, 0, 1), this
        },
        extractRotation: function() {
            var e = new Rr;
            return function(t) {
                var n = this.elements,
                    r = t.elements,
                    i = 1 / e.setFromMatrixColumn(t, 0).length(),
                    a = 1 / e.setFromMatrixColumn(t, 1).length(),
                    o = 1 / e.setFromMatrixColumn(t, 2).length();
                return n[0] = r[0] * i, n[1] = r[1] * i, n[2] = r[2] * i, n[3] = 0, n[4] = r[4] * a, n[5] = r[5] * a, n[6] = r[6] * a, n[7] = 0, n[8] = r[8] * o, n[9] = r[9] * o, n[10] = r[10] * o, n[11] = 0, n[12] = 0, n[13] = 0, n[14] = 0, n[15] = 1, this
            }
        }(),
        makeRotationFromEuler: function(e) {
            e && e.isEuler || console.error("THREE.Matrix4: .makeRotationFromEuler() now expects a Euler rotation rather than a Vector3 and order.");
            var t = this.elements,
                n = e.x,
                r = e.y,
                i = e.z,
                a = Math.cos(n),
                o = Math.sin(n),
                s = Math.cos(r),
                l = Math.sin(r),
                c = Math.cos(i),
                d = Math.sin(i);
            if ("XYZ" === e.order) {
                var u = a * c,
                    h = a * d,
                    p = o * c,
                    f = o * d;
                t[0] = s * c, t[4] = -s * d, t[8] = l, t[1] = h + p * l, t[5] = u - f * l, t[9] = -o * s, t[2] = f - u * l, t[6] = p + h * l, t[10] = a * s
            } else if ("YXZ" === e.order) {
                var m = s * c,
                    v = s * d,
                    g = l * c,
                    y = l * d;
                t[0] = m + y * o, t[4] = g * o - v, t[8] = a * l, t[1] = a * d, t[5] = a * c, t[9] = -o, t[2] = v * o - g, t[6] = y + m * o, t[10] = a * s
            } else if ("ZXY" === e.order) {
                m = s * c, v = s * d, g = l * c, y = l * d;
                t[0] = m - y * o, t[4] = -a * d, t[8] = g + v * o, t[1] = v + g * o, t[5] = a * c, t[9] = y - m * o, t[2] = -a * l, t[6] = o, t[10] = a * s
            } else if ("ZYX" === e.order) {
                u = a * c, h = a * d, p = o * c, f = o * d;
                t[0] = s * c, t[4] = p * l - h, t[8] = u * l + f, t[1] = s * d, t[5] = f * l + u, t[9] = h * l - p, t[2] = -l, t[6] = o * s, t[10] = a * s
            } else if ("YZX" === e.order) {
                var _ = a * s,
                    x = a * l,
                    E = o * s,
                    b = o * l;
                t[0] = s * c, t[4] = b - _ * d, t[8] = E * d + x, t[1] = d, t[5] = a * c, t[9] = -o * c, t[2] = -l * c, t[6] = x * d + E, t[10] = _ - b * d
            } else if ("XZY" === e.order) {
                _ = a * s, x = a * l, E = o * s, b = o * l;
                t[0] = s * c, t[4] = -d, t[8] = l * c, t[1] = _ * d + b, t[5] = a * c, t[9] = x * d - E, t[2] = E * d - x, t[6] = o * c, t[10] = b * d + _
            }
            return t[3] = 0, t[7] = 0, t[11] = 0, t[12] = 0, t[13] = 0, t[14] = 0, t[15] = 1, this
        },
        makeRotationFromQuaternion: function() {
            var e = new Rr(0, 0, 0),
                t = new Rr(1, 1, 1);
            return function(n) {
                return this.compose(e, n, t)
            }
        }(),
        lookAt: function() {
            var e = new Rr,
                t = new Rr,
                n = new Rr;
            return function(r, i, a) {
                var o = this.elements;
                return n.subVectors(r, i), 0 === n.lengthSq() && (n.z = 1), n.normalize(), e.crossVectors(a, n), 0 === e.lengthSq() && (1 === Math.abs(a.z) ? n.x += 1e-4 : n.z += 1e-4, n.normalize(), e.crossVectors(a, n)), e.normalize(), t.crossVectors(n, e), o[0] = e.x, o[4] = t.x, o[8] = n.x, o[1] = e.y, o[5] = t.y, o[9] = n.y, o[2] = e.z, o[6] = t.z, o[10] = n.z, this
            }
        }(),
        multiply: function(e, t) {
            return void 0 !== t ? (console.warn("THREE.Matrix4: .multiply() now only accepts one argument. Use .multiplyMatrices( a, b ) instead."), this.multiplyMatrices(e, t)) : this.multiplyMatrices(this, e)
        },
        premultiply: function(e) {
            return this.multiplyMatrices(e, this)
        },
        multiplyMatrices: function(e, t) {
            var n = e.elements,
                r = t.elements,
                i = this.elements,
                a = n[0],
                o = n[4],
                s = n[8],
                l = n[12],
                c = n[1],
                d = n[5],
                u = n[9],
                h = n[13],
                p = n[2],
                f = n[6],
                m = n[10],
                v = n[14],
                g = n[3],
                y = n[7],
                _ = n[11],
                x = n[15],
                E = r[0],
                b = r[4],
                P = r[8],
                w = r[12],
                S = r[1],
                M = r[5],
                L = r[9],
                C = r[13],
                T = r[2],
                D = r[6],
                R = r[10],
                A = r[14],
                I = r[3],
                O = r[7],
                U = r[11],
                N = r[15];
            return i[0] = a * E + o * S + s * T + l * I, i[4] = a * b + o * M + s * D + l * O, i[8] = a * P + o * L + s * R + l * U, i[12] = a * w + o * C + s * A + l * N, i[1] = c * E + d * S + u * T + h * I, i[5] = c * b + d * M + u * D + h * O, i[9] = c * P + d * L + u * R + h * U, i[13] = c * w + d * C + u * A + h * N, i[2] = p * E + f * S + m * T + v * I, i[6] = p * b + f * M + m * D + v * O, i[10] = p * P + f * L + m * R + v * U, i[14] = p * w + f * C + m * A + v * N, i[3] = g * E + y * S + _ * T + x * I, i[7] = g * b + y * M + _ * D + x * O, i[11] = g * P + y * L + _ * R + x * U, i[15] = g * w + y * C + _ * A + x * N, this
        },
        multiplyScalar: function(e) {
            var t = this.elements;
            return t[0] *= e, t[4] *= e, t[8] *= e, t[12] *= e, t[1] *= e, t[5] *= e, t[9] *= e, t[13] *= e, t[2] *= e, t[6] *= e, t[10] *= e, t[14] *= e, t[3] *= e, t[7] *= e, t[11] *= e, t[15] *= e, this
        },
        applyToBufferAttribute: function() {
            var e = new Rr;
            return function(t) {
                for (var n = 0, r = t.count; n < r; n++) e.x = t.getX(n), e.y = t.getY(n), e.z = t.getZ(n), e.applyMatrix4(this), t.setXYZ(n, e.x, e.y, e.z);
                return t
            }
        }(),
        determinant: function() {
            var e = this.elements,
                t = e[0],
                n = e[4],
                r = e[8],
                i = e[12],
                a = e[1],
                o = e[5],
                s = e[9],
                l = e[13],
                c = e[2],
                d = e[6],
                u = e[10],
                h = e[14];
            return e[3] * (+i * s * d - r * l * d - i * o * u + n * l * u + r * o * h - n * s * h) + e[7] * (+t * s * h - t * l * u + i * a * u - r * a * h + r * l * c - i * s * c) + e[11] * (+t * l * d - t * o * h - i * a * d + n * a * h + i * o * c - n * l * c) + e[15] * (-r * o * c - t * s * d + t * o * u + r * a * d - n * a * u + n * s * c)
        },
        transpose: function() {
            var e, t = this.elements;
            return e = t[1], t[1] = t[4], t[4] = e, e = t[2], t[2] = t[8], t[8] = e, e = t[6], t[6] = t[9], t[9] = e, e = t[3], t[3] = t[12], t[12] = e, e = t[7], t[7] = t[13], t[13] = e, e = t[11], t[11] = t[14], t[14] = e, this
        },
        setPosition: function(e) {
            var t = this.elements;
            return t[12] = e.x, t[13] = e.y, t[14] = e.z, this
        },
        getInverse: function(e, t) {
            var n = this.elements,
                r = e.elements,
                i = r[0],
                a = r[1],
                o = r[2],
                s = r[3],
                l = r[4],
                c = r[5],
                d = r[6],
                u = r[7],
                h = r[8],
                p = r[9],
                f = r[10],
                m = r[11],
                v = r[12],
                g = r[13],
                y = r[14],
                _ = r[15],
                x = p * y * u - g * f * u + g * d * m - c * y * m - p * d * _ + c * f * _,
                E = v * f * u - h * y * u - v * d * m + l * y * m + h * d * _ - l * f * _,
                b = h * g * u - v * p * u + v * c * m - l * g * m - h * c * _ + l * p * _,
                P = v * p * d - h * g * d - v * c * f + l * g * f + h * c * y - l * p * y,
                w = i * x + a * E + o * b + s * P;
            if (0 === w) {
                var S = "THREE.Matrix4: .getInverse() can't invert matrix, determinant is 0";
                if (!0 === t) throw new Error(S);
                return console.warn(S), this.identity()
            }
            var M = 1 / w;
            return n[0] = x * M, n[1] = (g * f * s - p * y * s - g * o * m + a * y * m + p * o * _ - a * f * _) * M, n[2] = (c * y * s - g * d * s + g * o * u - a * y * u - c * o * _ + a * d * _) * M, n[3] = (p * d * s - c * f * s - p * o * u + a * f * u + c * o * m - a * d * m) * M, n[4] = E * M, n[5] = (h * y * s - v * f * s + v * o * m - i * y * m - h * o * _ + i * f * _) * M, n[6] = (v * d * s - l * y * s - v * o * u + i * y * u + l * o * _ - i * d * _) * M, n[7] = (l * f * s - h * d * s + h * o * u - i * f * u - l * o * m + i * d * m) * M, n[8] = b * M, n[9] = (v * p * s - h * g * s - v * a * m + i * g * m + h * a * _ - i * p * _) * M, n[10] = (l * g * s - v * c * s + v * a * u - i * g * u - l * a * _ + i * c * _) * M, n[11] = (h * c * s - l * p * s - h * a * u + i * p * u + l * a * m - i * c * m) * M, n[12] = P * M, n[13] = (h * g * o - v * p * o + v * a * f - i * g * f - h * a * y + i * p * y) * M, n[14] = (v * c * o - l * g * o - v * a * d + i * g * d + l * a * y - i * c * y) * M, n[15] = (l * p * o - h * c * o + h * a * d - i * p * d - l * a * f + i * c * f) * M, this
        },
        scale: function(e) {
            var t = this.elements,
                n = e.x,
                r = e.y,
                i = e.z;
            return t[0] *= n, t[4] *= r, t[8] *= i, t[1] *= n, t[5] *= r, t[9] *= i, t[2] *= n, t[6] *= r, t[10] *= i, t[3] *= n, t[7] *= r, t[11] *= i, this
        },
        getMaxScaleOnAxis: function() {
            var e = this.elements,
                t = e[0] * e[0] + e[1] * e[1] + e[2] * e[2],
                n = e[4] * e[4] + e[5] * e[5] + e[6] * e[6],
                r = e[8] * e[8] + e[9] * e[9] + e[10] * e[10];
            return Math.sqrt(Math.max(t, n, r))
        },
        makeTranslation: function(e, t, n) {
            return this.set(1, 0, 0, e, 0, 1, 0, t, 0, 0, 1, n, 0, 0, 0, 1), this
        },
        makeRotationX: function(e) {
            var t = Math.cos(e),
                n = Math.sin(e);
            return this.set(1, 0, 0, 0, 0, t, -n, 0, 0, n, t, 0, 0, 0, 0, 1), this
        },
        makeRotationY: function(e) {
            var t = Math.cos(e),
                n = Math.sin(e);
            return this.set(t, 0, n, 0, 0, 1, 0, 0, -n, 0, t, 0, 0, 0, 0, 1), this
        },
        makeRotationZ: function(e) {
            var t = Math.cos(e),
                n = Math.sin(e);
            return this.set(t, -n, 0, 0, n, t, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1), this
        },
        makeRotationAxis: function(e, t) {
            var n = Math.cos(t),
                r = Math.sin(t),
                i = 1 - n,
                a = e.x,
                o = e.y,
                s = e.z,
                l = i * a,
                c = i * o;
            return this.set(l * a + n, l * o - r * s, l * s + r * o, 0, l * o + r * s, c * o + n, c * s - r * a, 0, l * s - r * o, c * s + r * a, i * s * s + n, 0, 0, 0, 0, 1), this
        },
        makeScale: function(e, t, n) {
            return this.set(e, 0, 0, 0, 0, t, 0, 0, 0, 0, n, 0, 0, 0, 0, 1), this
        },
        makeShear: function(e, t, n) {
            return this.set(1, t, n, 0, e, 1, n, 0, e, t, 1, 0, 0, 0, 0, 1), this
        },
        compose: function(e, t, n) {
            var r = this.elements,
                i = t._x,
                a = t._y,
                o = t._z,
                s = t._w,
                l = i + i,
                c = a + a,
                d = o + o,
                u = i * l,
                h = i * c,
                p = i * d,
                f = a * c,
                m = a * d,
                v = o * d,
                g = s * l,
                y = s * c,
                _ = s * d,
                x = n.x,
                E = n.y,
                b = n.z;
            return r[0] = (1 - (f + v)) * x, r[1] = (h + _) * x, r[2] = (p - y) * x, r[3] = 0, r[4] = (h - _) * E, r[5] = (1 - (u + v)) * E, r[6] = (m + g) * E, r[7] = 0, r[8] = (p + y) * b, r[9] = (m - g) * b, r[10] = (1 - (u + f)) * b, r[11] = 0, r[12] = e.x, r[13] = e.y, r[14] = e.z, r[15] = 1, this
        },
        decompose: function() {
            var e = new Rr,
                t = new Tr;
            return function(n, r, i) {
                var a = this.elements,
                    o = e.set(a[0], a[1], a[2]).length(),
                    s = e.set(a[4], a[5], a[6]).length(),
                    l = e.set(a[8], a[9], a[10]).length();
                this.determinant() < 0 && (o = -o), n.x = a[12], n.y = a[13], n.z = a[14], t.copy(this);
                var c = 1 / o,
                    d = 1 / s,
                    u = 1 / l;
                return t.elements[0] *= c, t.elements[1] *= c, t.elements[2] *= c, t.elements[4] *= d, t.elements[5] *= d, t.elements[6] *= d, t.elements[8] *= u, t.elements[9] *= u, t.elements[10] *= u, r.setFromRotationMatrix(t), i.x = o, i.y = s, i.z = l, this
            }
        }(),
        makePerspective: function(e, t, n, r, i, a) {
            void 0 === a && console.warn("THREE.Matrix4: .makePerspective() has been redefined and has a new signature. Please check the docs.");
            var o = this.elements,
                s = 2 * i / (t - e),
                l = 2 * i / (n - r),
                c = (t + e) / (t - e),
                d = (n + r) / (n - r),
                u = -(a + i) / (a - i),
                h = -2 * a * i / (a - i);
            return o[0] = s, o[4] = 0, o[8] = c, o[12] = 0, o[1] = 0, o[5] = l, o[9] = d, o[13] = 0, o[2] = 0, o[6] = 0, o[10] = u, o[14] = h, o[3] = 0, o[7] = 0, o[11] = -1, o[15] = 0, this
        },
        makeOrthographic: function(e, t, n, r, i, a) {
            var o = this.elements,
                s = 1 / (t - e),
                l = 1 / (n - r),
                c = 1 / (a - i),
                d = (t + e) * s,
                u = (n + r) * l,
                h = (a + i) * c;
            return o[0] = 2 * s, o[4] = 0, o[8] = 0, o[12] = -d, o[1] = 0, o[5] = 2 * l, o[9] = 0, o[13] = -u, o[2] = 0, o[6] = 0, o[10] = -2 * c, o[14] = -h, o[3] = 0, o[7] = 0, o[11] = 0, o[15] = 1, this
        },
        equals: function(e) {
            for (var t = this.elements, n = e.elements, r = 0; r < 16; r++)
                if (t[r] !== n[r]) return !1;
            return !0
        },
        fromArray: function(e, t) {
            void 0 === t && (t = 0);
            for (var n = 0; n < 16; n++) this.elements[n] = e[n + t];
            return this
        },
        toArray: function(e, t) {
            void 0 === e && (e = []), void 0 === t && (t = 0);
            var n = this.elements;
            return e[t] = n[0], e[t + 1] = n[1], e[t + 2] = n[2], e[t + 3] = n[3], e[t + 4] = n[4], e[t + 5] = n[5], e[t + 6] = n[6], e[t + 7] = n[7], e[t + 8] = n[8], e[t + 9] = n[9], e[t + 10] = n[10], e[t + 11] = n[11], e[t + 12] = n[12], e[t + 13] = n[13], e[t + 14] = n[14], e[t + 15] = n[15], e
        }
    }), Object.assign(Dr, {
        slerp: function(e, t, n, r) {
            return n.copy(e).slerp(t, r)
        },
        slerpFlat: function(e, t, n, r, i, a, o) {
            var s = n[r + 0],
                l = n[r + 1],
                c = n[r + 2],
                d = n[r + 3],
                u = i[a + 0],
                h = i[a + 1],
                p = i[a + 2],
                f = i[a + 3];
            if (d !== f || s !== u || l !== h || c !== p) {
                var m = 1 - o,
                    v = s * u + l * h + c * p + d * f,
                    g = v >= 0 ? 1 : -1,
                    y = 1 - v * v;
                if (y > Number.EPSILON) {
                    var _ = Math.sqrt(y),
                        x = Math.atan2(_, v * g);
                    m = Math.sin(m * x) / _, o = Math.sin(o * x) / _
                }
                var E = o * g;
                if (s = s * m + u * E, l = l * m + h * E, c = c * m + p * E, d = d * m + f * E, m === 1 - o) {
                    var b = 1 / Math.sqrt(s * s + l * l + c * c + d * d);
                    s *= b, l *= b, c *= b, d *= b
                }
            }
            e[t] = s, e[t + 1] = l, e[t + 2] = c, e[t + 3] = d
        }
    }), Object.defineProperties(Dr.prototype, {
        x: {
            get: function() {
                return this._x
            },
            set: function(e) {
                this._x = e, this.onChangeCallback()
            }
        },
        y: {
            get: function() {
                return this._y
            },
            set: function(e) {
                this._y = e, this.onChangeCallback()
            }
        },
        z: {
            get: function() {
                return this._z
            },
            set: function(e) {
                this._z = e, this.onChangeCallback()
            }
        },
        w: {
            get: function() {
                return this._w
            },
            set: function(e) {
                this._w = e, this.onChangeCallback()
            }
        }
    }), Object.assign(Dr.prototype, {
        isQuaternion: !0,
        set: function(e, t, n, r) {
            return this._x = e, this._y = t, this._z = n, this._w = r, this.onChangeCallback(), this
        },
        clone: function() {
            return new this.constructor(this._x, this._y, this._z, this._w)
        },
        copy: function(e) {
            return this._x = e.x, this._y = e.y, this._z = e.z, this._w = e.w, this.onChangeCallback(), this
        },
        setFromEuler: function(e, t) {
            if (!e || !e.isEuler) throw new Error("THREE.Quaternion: .setFromEuler() now expects an Euler rotation rather than a Vector3 and order.");
            var n = e._x,
                r = e._y,
                i = e._z,
                a = e.order,
                o = Math.cos,
                s = Math.sin,
                l = o(n / 2),
                c = o(r / 2),
                d = o(i / 2),
                u = s(n / 2),
                h = s(r / 2),
                p = s(i / 2);
            return "XYZ" === a ? (this._x = u * c * d + l * h * p, this._y = l * h * d - u * c * p, this._z = l * c * p + u * h * d, this._w = l * c * d - u * h * p) : "YXZ" === a ? (this._x = u * c * d + l * h * p, this._y = l * h * d - u * c * p, this._z = l * c * p - u * h * d, this._w = l * c * d + u * h * p) : "ZXY" === a ? (this._x = u * c * d - l * h * p, this._y = l * h * d + u * c * p, this._z = l * c * p + u * h * d, this._w = l * c * d - u * h * p) : "ZYX" === a ? (this._x = u * c * d - l * h * p, this._y = l * h * d + u * c * p, this._z = l * c * p - u * h * d, this._w = l * c * d + u * h * p) : "YZX" === a ? (this._x = u * c * d + l * h * p, this._y = l * h * d + u * c * p, this._z = l * c * p - u * h * d, this._w = l * c * d - u * h * p) : "XZY" === a && (this._x = u * c * d - l * h * p, this._y = l * h * d - u * c * p, this._z = l * c * p + u * h * d, this._w = l * c * d + u * h * p), !1 !== t && this.onChangeCallback(), this
        },
        setFromAxisAngle: function(e, t) {
            var n = t / 2,
                r = Math.sin(n);
            return this._x = e.x * r, this._y = e.y * r, this._z = e.z * r, this._w = Math.cos(n), this.onChangeCallback(), this
        },
        setFromRotationMatrix: function(e) {
            var t, n = e.elements,
                r = n[0],
                i = n[4],
                a = n[8],
                o = n[1],
                s = n[5],
                l = n[9],
                c = n[2],
                d = n[6],
                u = n[10],
                h = r + s + u;
            return h > 0 ? (t = .5 / Math.sqrt(h + 1), this._w = .25 / t, this._x = (d - l) * t, this._y = (a - c) * t, this._z = (o - i) * t) : r > s && r > u ? (t = 2 * Math.sqrt(1 + r - s - u), this._w = (d - l) / t, this._x = .25 * t, this._y = (i + o) / t, this._z = (a + c) / t) : s > u ? (t = 2 * Math.sqrt(1 + s - r - u), this._w = (a - c) / t, this._x = (i + o) / t, this._y = .25 * t, this._z = (l + d) / t) : (t = 2 * Math.sqrt(1 + u - r - s), this._w = (o - i) / t, this._x = (a + c) / t, this._y = (l + d) / t, this._z = .25 * t), this.onChangeCallback(), this
        },
        setFromUnitVectors: function() {
            var e, t = new Rr;
            return function(n, r) {
                return void 0 === t && (t = new Rr), (e = n.dot(r) + 1) < 1e-6 ? (e = 0, Math.abs(n.x) > Math.abs(n.z) ? t.set(-n.y, n.x, 0) : t.set(0, -n.z, n.y)) : t.crossVectors(n, r), this._x = t.x, this._y = t.y, this._z = t.z, this._w = e, this.normalize()
            }
        }(),
        angleTo: function(e) {
            return 2 * Math.acos(Math.abs(Cr.clamp(this.dot(e), -1, 1)))
        },
        rotateTowards: function(e, t) {
            var n = this.angleTo(e);
            if (0 === n) return this;
            var r = Math.min(1, t / n);
            return this.slerp(e, r), this
        },
        inverse: function() {
            return this.conjugate()
        },
        conjugate: function() {
            return this._x *= -1, this._y *= -1, this._z *= -1, this.onChangeCallback(), this
        },
        dot: function(e) {
            return this._x * e._x + this._y * e._y + this._z * e._z + this._w * e._w
        },
        lengthSq: function() {
            return this._x * this._x + this._y * this._y + this._z * this._z + this._w * this._w
        },
        length: function() {
            return Math.sqrt(this._x * this._x + this._y * this._y + this._z * this._z + this._w * this._w)
        },
        normalize: function() {
            var e = this.length();
            return 0 === e ? (this._x = 0, this._y = 0, this._z = 0, this._w = 1) : (e = 1 / e, this._x = this._x * e, this._y = this._y * e, this._z = this._z * e, this._w = this._w * e), this.onChangeCallback(), this
        },
        multiply: function(e, t) {
            return void 0 !== t ? (console.warn("THREE.Quaternion: .multiply() now only accepts one argument. Use .multiplyQuaternions( a, b ) instead."), this.multiplyQuaternions(e, t)) : this.multiplyQuaternions(this, e)
        },
        premultiply: function(e) {
            return this.multiplyQuaternions(e, this)
        },
        multiplyQuaternions: function(e, t) {
            var n = e._x,
                r = e._y,
                i = e._z,
                a = e._w,
                o = t._x,
                s = t._y,
                l = t._z,
                c = t._w;
            return this._x = n * c + a * o + r * l - i * s, this._y = r * c + a * s + i * o - n * l, this._z = i * c + a * l + n * s - r * o, this._w = a * c - n * o - r * s - i * l, this.onChangeCallback(), this
        },
        slerp: function(e, t) {
            if (0 === t) return this;
            if (1 === t) return this.copy(e);
            var n = this._x,
                r = this._y,
                i = this._z,
                a = this._w,
                o = a * e._w + n * e._x + r * e._y + i * e._z;
            if (o < 0 ? (this._w = -e._w, this._x = -e._x, this._y = -e._y, this._z = -e._z, o = -o) : this.copy(e), o >= 1) return this._w = a, this._x = n, this._y = r, this._z = i, this;
            var s = 1 - o * o;
            if (s <= Number.EPSILON) {
                var l = 1 - t;
                return this._w = l * a + t * this._w, this._x = l * n + t * this._x, this._y = l * r + t * this._y, this._z = l * i + t * this._z, this.normalize()
            }
            var c = Math.sqrt(s),
                d = Math.atan2(c, o),
                u = Math.sin((1 - t) * d) / c,
                h = Math.sin(t * d) / c;
            return this._w = a * u + this._w * h, this._x = n * u + this._x * h, this._y = r * u + this._y * h, this._z = i * u + this._z * h, this.onChangeCallback(), this
        },
        equals: function(e) {
            return e._x === this._x && e._y === this._y && e._z === this._z && e._w === this._w
        },
        fromArray: function(e, t) {
            return void 0 === t && (t = 0), this._x = e[t], this._y = e[t + 1], this._z = e[t + 2], this._w = e[t + 3], this.onChangeCallback(), this
        },
        toArray: function(e, t) {
            return void 0 === e && (e = []), void 0 === t && (t = 0), e[t] = this._x, e[t + 1] = this._y, e[t + 2] = this._z, e[t + 3] = this._w, e
        },
        onChange: function(e) {
            return this.onChangeCallback = e, this
        },
        onChangeCallback: function() {}
    }), Object.assign(Rr.prototype, {
        isVector3: !0,
        set: function(e, t, n) {
            return this.x = e, this.y = t, this.z = n, this
        },
        setScalar: function(e) {
            return this.x = e, this.y = e, this.z = e, this
        },
        setX: function(e) {
            return this.x = e, this
        },
        setY: function(e) {
            return this.y = e, this
        },
        setZ: function(e) {
            return this.z = e, this
        },
        setComponent: function(e, t) {
            switch (e) {
                case 0:
                    this.x = t;
                    break;
                case 1:
                    this.y = t;
                    break;
                case 2:
                    this.z = t;
                    break;
                default:
                    throw new Error("index is out of range: " + e)
            }
            return this
        },
        getComponent: function(e) {
            switch (e) {
                case 0:
                    return this.x;
                case 1:
                    return this.y;
                case 2:
                    return this.z;
                default:
                    throw new Error("index is out of range: " + e)
            }
        },
        clone: function() {
            return new this.constructor(this.x, this.y, this.z)
        },
        copy: function(e) {
            return this.x = e.x, this.y = e.y, this.z = e.z, this
        },
        add: function(e, t) {
            return void 0 !== t ? (console.warn("THREE.Vector3: .add() now only accepts one argument. Use .addVectors( a, b ) instead."), this.addVectors(e, t)) : (this.x += e.x, this.y += e.y, this.z += e.z, this)
        },
        addScalar: function(e) {
            return this.x += e, this.y += e, this.z += e, this
        },
        addVectors: function(e, t) {
            return this.x = e.x + t.x, this.y = e.y + t.y, this.z = e.z + t.z, this
        },
        addScaledVector: function(e, t) {
            return this.x += e.x * t, this.y += e.y * t, this.z += e.z * t, this
        },
        sub: function(e, t) {
            return void 0 !== t ? (console.warn("THREE.Vector3: .sub() now only accepts one argument. Use .subVectors( a, b ) instead."), this.subVectors(e, t)) : (this.x -= e.x, this.y -= e.y, this.z -= e.z, this)
        },
        subScalar: function(e) {
            return this.x -= e, this.y -= e, this.z -= e, this
        },
        subVectors: function(e, t) {
            return this.x = e.x - t.x, this.y = e.y - t.y, this.z = e.z - t.z, this
        },
        multiply: function(e, t) {
            return void 0 !== t ? (console.warn("THREE.Vector3: .multiply() now only accepts one argument. Use .multiplyVectors( a, b ) instead."), this.multiplyVectors(e, t)) : (this.x *= e.x, this.y *= e.y, this.z *= e.z, this)
        },
        multiplyScalar: function(e) {
            return this.x *= e, this.y *= e, this.z *= e, this
        },
        multiplyVectors: function(e, t) {
            return this.x = e.x * t.x, this.y = e.y * t.y, this.z = e.z * t.z, this
        },
        applyEuler: function() {
            var e = new Dr;
            return function(t) {
                return t && t.isEuler || console.error("THREE.Vector3: .applyEuler() now expects an Euler rotation rather than a Vector3 and order."), this.applyQuaternion(e.setFromEuler(t))
            }
        }(),
        applyAxisAngle: function() {
            var e = new Dr;
            return function(t, n) {
                return this.applyQuaternion(e.setFromAxisAngle(t, n))
            }
        }(),
        applyMatrix3: function(e) {
            var t = this.x,
                n = this.y,
                r = this.z,
                i = e.elements;
            return this.x = i[0] * t + i[3] * n + i[6] * r, this.y = i[1] * t + i[4] * n + i[7] * r, this.z = i[2] * t + i[5] * n + i[8] * r, this
        },
        applyMatrix4: function(e) {
            var t = this.x,
                n = this.y,
                r = this.z,
                i = e.elements,
                a = 1 / (i[3] * t + i[7] * n + i[11] * r + i[15]);
            return this.x = (i[0] * t + i[4] * n + i[8] * r + i[12]) * a, this.y = (i[1] * t + i[5] * n + i[9] * r + i[13]) * a, this.z = (i[2] * t + i[6] * n + i[10] * r + i[14]) * a, this
        },
        applyQuaternion: function(e) {
            var t = this.x,
                n = this.y,
                r = this.z,
                i = e.x,
                a = e.y,
                o = e.z,
                s = e.w,
                l = s * t + a * r - o * n,
                c = s * n + o * t - i * r,
                d = s * r + i * n - a * t,
                u = -i * t - a * n - o * r;
            return this.x = l * s + u * -i + c * -o - d * -a, this.y = c * s + u * -a + d * -i - l * -o, this.z = d * s + u * -o + l * -a - c * -i, this
        },
        project: function(e) {
            return this.applyMatrix4(e.matrixWorldInverse).applyMatrix4(e.projectionMatrix)
        },
        unproject: function() {
            var e = new Tr;
            return function(t) {
                return this.applyMatrix4(e.getInverse(t.projectionMatrix)).applyMatrix4(t.matrixWorld)
            }
        }(),
        transformDirection: function(e) {
            var t = this.x,
                n = this.y,
                r = this.z,
                i = e.elements;
            return this.x = i[0] * t + i[4] * n + i[8] * r, this.y = i[1] * t + i[5] * n + i[9] * r, this.z = i[2] * t + i[6] * n + i[10] * r, this.normalize()
        },
        divide: function(e) {
            return this.x /= e.x, this.y /= e.y, this.z /= e.z, this
        },
        divideScalar: function(e) {
            return this.multiplyScalar(1 / e)
        },
        min: function(e) {
            return this.x = Math.min(this.x, e.x), this.y = Math.min(this.y, e.y), this.z = Math.min(this.z, e.z), this
        },
        max: function(e) {
            return this.x = Math.max(this.x, e.x), this.y = Math.max(this.y, e.y), this.z = Math.max(this.z, e.z), this
        },
        clamp: function(e, t) {
            return this.x = Math.max(e.x, Math.min(t.x, this.x)), this.y = Math.max(e.y, Math.min(t.y, this.y)), this.z = Math.max(e.z, Math.min(t.z, this.z)), this
        },
        clampScalar: function() {
            var e = new Rr,
                t = new Rr;
            return function(n, r) {
                return e.set(n, n, n), t.set(r, r, r), this.clamp(e, t)
            }
        }(),
        clampLength: function(e, t) {
            var n = this.length();
            return this.divideScalar(n || 1).multiplyScalar(Math.max(e, Math.min(t, n)))
        },
        floor: function() {
            return this.x = Math.floor(this.x), this.y = Math.floor(this.y), this.z = Math.floor(this.z), this
        },
        ceil: function() {
            return this.x = Math.ceil(this.x), this.y = Math.ceil(this.y), this.z = Math.ceil(this.z), this
        },
        round: function() {
            return this.x = Math.round(this.x), this.y = Math.round(this.y), this.z = Math.round(this.z), this
        },
        roundToZero: function() {
            return this.x = this.x < 0 ? Math.ceil(this.x) : Math.floor(this.x), this.y = this.y < 0 ? Math.ceil(this.y) : Math.floor(this.y), this.z = this.z < 0 ? Math.ceil(this.z) : Math.floor(this.z), this
        },
        negate: function() {
            return this.x = -this.x, this.y = -this.y, this.z = -this.z, this
        },
        dot: function(e) {
            return this.x * e.x + this.y * e.y + this.z * e.z
        },
        lengthSq: function() {
            return this.x * this.x + this.y * this.y + this.z * this.z
        },
        length: function() {
            return Math.sqrt(this.x * this.x + this.y * this.y + this.z * this.z)
        },
        manhattanLength: function() {
            return Math.abs(this.x) + Math.abs(this.y) + Math.abs(this.z)
        },
        normalize: function() {
            return this.divideScalar(this.length() || 1)
        },
        setLength: function(e) {
            return this.normalize().multiplyScalar(e)
        },
        lerp: function(e, t) {
            return this.x += (e.x - this.x) * t, this.y += (e.y - this.y) * t, this.z += (e.z - this.z) * t, this
        },
        lerpVectors: function(e, t, n) {
            return this.subVectors(t, e).multiplyScalar(n).add(e)
        },
        cross: function(e, t) {
            return void 0 !== t ? (console.warn("THREE.Vector3: .cross() now only accepts one argument. Use .crossVectors( a, b ) instead."), this.crossVectors(e, t)) : this.crossVectors(this, e)
        },
        crossVectors: function(e, t) {
            var n = e.x,
                r = e.y,
                i = e.z,
                a = t.x,
                o = t.y,
                s = t.z;
            return this.x = r * s - i * o, this.y = i * a - n * s, this.z = n * o - r * a, this
        },
        projectOnVector: function(e) {
            var t = e.dot(this) / e.lengthSq();
            return this.copy(e).multiplyScalar(t)
        },
        projectOnPlane: function() {
            var e = new Rr;
            return function(t) {
                return e.copy(this).projectOnVector(t), this.sub(e)
            }
        }(),
        reflect: function() {
            var e = new Rr;
            return function(t) {
                return this.sub(e.copy(t).multiplyScalar(2 * this.dot(t)))
            }
        }(),
        angleTo: function(e) {
            var t = this.dot(e) / Math.sqrt(this.lengthSq() * e.lengthSq());
            return Math.acos(Cr.clamp(t, -1, 1))
        },
        distanceTo: function(e) {
            return Math.sqrt(this.distanceToSquared(e))
        },
        distanceToSquared: function(e) {
            var t = this.x - e.x,
                n = this.y - e.y,
                r = this.z - e.z;
            return t * t + n * n + r * r
        },
        manhattanDistanceTo: function(e) {
            return Math.abs(this.x - e.x) + Math.abs(this.y - e.y) + Math.abs(this.z - e.z)
        },
        setFromSpherical: function(e) {
            return this.setFromSphericalCoords(e.radius, e.phi, e.theta)
        },
        setFromSphericalCoords: function(e, t, n) {
            var r = Math.sin(t) * e;
            return this.x = r * Math.sin(n), this.y = Math.cos(t) * e, this.z = r * Math.cos(n), this
        },
        setFromCylindrical: function(e) {
            return this.setFromCylindricalCoords(e.radius, e.theta, e.y)
        },
        setFromCylindricalCoords: function(e, t, n) {
            return this.x = e * Math.sin(t), this.y = n, this.z = e * Math.cos(t), this
        },
        setFromMatrixPosition: function(e) {
            var t = e.elements;
            return this.x = t[12], this.y = t[13], this.z = t[14], this
        },
        setFromMatrixScale: function(e) {
            var t = this.setFromMatrixColumn(e, 0).length(),
                n = this.setFromMatrixColumn(e, 1).length(),
                r = this.setFromMatrixColumn(e, 2).length();
            return this.x = t, this.y = n, this.z = r, this
        },
        setFromMatrixColumn: function(e, t) {
            return this.fromArray(e.elements, 4 * t)
        },
        equals: function(e) {
            return e.x === this.x && e.y === this.y && e.z === this.z
        },
        fromArray: function(e, t) {
            return void 0 === t && (t = 0), this.x = e[t], this.y = e[t + 1], this.z = e[t + 2], this
        },
        toArray: function(e, t) {
            return void 0 === e && (e = []), void 0 === t && (t = 0), e[t] = this.x, e[t + 1] = this.y, e[t + 2] = this.z, e
        },
        fromBufferAttribute: function(e, t, n) {
            return void 0 !== n && console.warn("THREE.Vector3: offset has been removed from .fromBufferAttribute()."), this.x = e.getX(t), this.y = e.getY(t), this.z = e.getZ(t), this
        }
    });
    var Ar = {
        aliceblue: 15792383,
        antiquewhite: 16444375,
        aqua: 65535,
        aquamarine: 8388564,
        azure: 15794175,
        beige: 16119260,
        bisque: 16770244,
        black: 0,
        blanchedalmond: 16772045,
        blue: 255,
        blueviolet: 9055202,
        brown: 10824234,
        burlywood: 14596231,
        cadetblue: 6266528,
        chartreuse: 8388352,
        chocolate: 13789470,
        coral: 16744272,
        cornflowerblue: 6591981,
        cornsilk: 16775388,
        crimson: 14423100,
        cyan: 65535,
        darkblue: 139,
        darkcyan: 35723,
        darkgoldenrod: 12092939,
        darkgray: 11119017,
        darkgreen: 25600,
        darkgrey: 11119017,
        darkkhaki: 12433259,
        darkmagenta: 9109643,
        darkolivegreen: 5597999,
        darkorange: 16747520,
        darkorchid: 10040012,
        darkred: 9109504,
        darksalmon: 15308410,
        darkseagreen: 9419919,
        darkslateblue: 4734347,
        darkslategray: 3100495,
        darkslategrey: 3100495,
        darkturquoise: 52945,
        darkviolet: 9699539,
        deeppink: 16716947,
        deepskyblue: 49151,
        dimgray: 6908265,
        dimgrey: 6908265,
        dodgerblue: 2003199,
        firebrick: 11674146,
        floralwhite: 16775920,
        forestgreen: 2263842,
        fuchsia: 16711935,
        gainsboro: 14474460,
        ghostwhite: 16316671,
        gold: 16766720,
        goldenrod: 14329120,
        gray: 8421504,
        green: 32768,
        greenyellow: 11403055,
        grey: 8421504,
        honeydew: 15794160,
        hotpink: 16738740,
        indianred: 13458524,
        indigo: 4915330,
        ivory: 16777200,
        khaki: 15787660,
        lavender: 15132410,
        lavenderblush: 16773365,
        lawngreen: 8190976,
        lemonchiffon: 16775885,
        lightblue: 11393254,
        lightcoral: 15761536,
        lightcyan: 14745599,
        lightgoldenrodyellow: 16448210,
        lightgray: 13882323,
        lightgreen: 9498256,
        lightgrey: 13882323,
        lightpink: 16758465,
        lightsalmon: 16752762,
        lightseagreen: 2142890,
        lightskyblue: 8900346,
        lightslategray: 7833753,
        lightslategrey: 7833753,
        lightsteelblue: 11584734,
        lightyellow: 16777184,
        lime: 65280,
        limegreen: 3329330,
        linen: 16445670,
        magenta: 16711935,
        maroon: 8388608,
        mediumaquamarine: 6737322,
        mediumblue: 205,
        mediumorchid: 12211667,
        mediumpurple: 9662683,
        mediumseagreen: 3978097,
        mediumslateblue: 8087790,
        mediumspringgreen: 64154,
        mediumturquoise: 4772300,
        mediumvioletred: 13047173,
        midnightblue: 1644912,
        mintcream: 16121850,
        mistyrose: 16770273,
        moccasin: 16770229,
        navajowhite: 16768685,
        navy: 128,
        oldlace: 16643558,
        olive: 8421376,
        olivedrab: 7048739,
        orange: 16753920,
        orangered: 16729344,
        orchid: 14315734,
        palegoldenrod: 15657130,
        palegreen: 10025880,
        paleturquoise: 11529966,
        palevioletred: 14381203,
        papayawhip: 16773077,
        peachpuff: 16767673,
        peru: 13468991,
        pink: 16761035,
        plum: 14524637,
        powderblue: 11591910,
        purple: 8388736,
        rebeccapurple: 6697881,
        red: 16711680,
        rosybrown: 12357519,
        royalblue: 4286945,
        saddlebrown: 9127187,
        salmon: 16416882,
        sandybrown: 16032864,
        seagreen: 3050327,
        seashell: 16774638,
        sienna: 10506797,
        silver: 12632256,
        skyblue: 8900331,
        slateblue: 6970061,
        slategray: 7372944,
        slategrey: 7372944,
        snow: 16775930,
        springgreen: 65407,
        steelblue: 4620980,
        tan: 13808780,
        teal: 32896,
        thistle: 14204888,
        tomato: 16737095,
        turquoise: 4251856,
        violet: 15631086,
        wheat: 16113331,
        white: 16777215,
        whitesmoke: 16119285,
        yellow: 16776960,
        yellowgreen: 10145074
    };

    function Ir(e, t, n) {
        return void 0 === t && void 0 === n ? this.set(e) : this.setRGB(e, t, n)
    }

    function Or(e, t) {
        this.x = e || 0, this.y = t || 0
    }

    function Ur() {
        this.elements = [1, 0, 0, 0, 1, 0, 0, 0, 1], arguments.length > 0 && console.error("THREE.Matrix3: the constructor no longer reads arguments. use .set() instead.")
    }
    Object.assign(Ir.prototype, {
        isColor: !0,
        r: 1,
        g: 1,
        b: 1,
        set: function(e) {
            return e && e.isColor ? this.copy(e) : "number" == typeof e ? this.setHex(e) : "string" == typeof e && this.setStyle(e), this
        },
        setScalar: function(e) {
            return this.r = e, this.g = e, this.b = e, this
        },
        setHex: function(e) {
            return e = Math.floor(e), this.r = (e >> 16 & 255) / 255, this.g = (e >> 8 & 255) / 255, this.b = (255 & e) / 255, this
        },
        setRGB: function(e, t, n) {
            return this.r = e, this.g = t, this.b = n, this
        },
        setHSL: function() {
            function e(e, t, n) {
                return n < 0 && (n += 1), n > 1 && (n -= 1), n < 1 / 6 ? e + 6 * (t - e) * n : n < .5 ? t : n < 2 / 3 ? e + 6 * (t - e) * (2 / 3 - n) : e
            }
            return function(t, n, r) {
                if (t = Cr.euclideanModulo(t, 1), n = Cr.clamp(n, 0, 1), r = Cr.clamp(r, 0, 1), 0 === n) this.r = this.g = this.b = r;
                else {
                    var i = r <= .5 ? r * (1 + n) : r + n - r * n,
                        a = 2 * r - i;
                    this.r = e(a, i, t + 1 / 3), this.g = e(a, i, t), this.b = e(a, i, t - 1 / 3)
                }
                return this
            }
        }(),
        setStyle: function(e) {
            function t(t) {
                void 0 !== t && parseFloat(t) < 1 && console.warn("THREE.Color: Alpha component of " + e + " will be ignored.")
            }
            var n;
            if (n = /^((?:rgb|hsl)a?)\(\s*([^\)]*)\)/.exec(e)) {
                var r, i = n[1],
                    a = n[2];
                switch (i) {
                    case "rgb":
                    case "rgba":
                        if (r = /^(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(,\s*([0-9]*\.?[0-9]+)\s*)?$/.exec(a)) return this.r = Math.min(255, parseInt(r[1], 10)) / 255, this.g = Math.min(255, parseInt(r[2], 10)) / 255, this.b = Math.min(255, parseInt(r[3], 10)) / 255, t(r[5]), this;
                        if (r = /^(\d+)\%\s*,\s*(\d+)\%\s*,\s*(\d+)\%\s*(,\s*([0-9]*\.?[0-9]+)\s*)?$/.exec(a)) return this.r = Math.min(100, parseInt(r[1], 10)) / 100, this.g = Math.min(100, parseInt(r[2], 10)) / 100, this.b = Math.min(100, parseInt(r[3], 10)) / 100, t(r[5]), this;
                        break;
                    case "hsl":
                    case "hsla":
                        if (r = /^([0-9]*\.?[0-9]+)\s*,\s*(\d+)\%\s*,\s*(\d+)\%\s*(,\s*([0-9]*\.?[0-9]+)\s*)?$/.exec(a)) {
                            var o = parseFloat(r[1]) / 360,
                                s = parseInt(r[2], 10) / 100,
                                l = parseInt(r[3], 10) / 100;
                            return t(r[5]), this.setHSL(o, s, l)
                        }
                }
            } else if (n = /^\#([A-Fa-f0-9]+)$/.exec(e)) {
                var c, d = (c = n[1]).length;
                if (3 === d) return this.r = parseInt(c.charAt(0) + c.charAt(0), 16) / 255, this.g = parseInt(c.charAt(1) + c.charAt(1), 16) / 255, this.b = parseInt(c.charAt(2) + c.charAt(2), 16) / 255, this;
                if (6 === d) return this.r = parseInt(c.charAt(0) + c.charAt(1), 16) / 255, this.g = parseInt(c.charAt(2) + c.charAt(3), 16) / 255, this.b = parseInt(c.charAt(4) + c.charAt(5), 16) / 255, this
            }
            e && e.length > 0 && (void 0 !== (c = Ar[e]) ? this.setHex(c) : console.warn("THREE.Color: Unknown color " + e));
            return this
        },
        clone: function() {
            return new this.constructor(this.r, this.g, this.b)
        },
        copy: function(e) {
            return this.r = e.r, this.g = e.g, this.b = e.b, this
        },
        copyGammaToLinear: function(e, t) {
            return void 0 === t && (t = 2), this.r = Math.pow(e.r, t), this.g = Math.pow(e.g, t), this.b = Math.pow(e.b, t), this
        },
        copyLinearToGamma: function(e, t) {
            void 0 === t && (t = 2);
            var n = t > 0 ? 1 / t : 1;
            return this.r = Math.pow(e.r, n), this.g = Math.pow(e.g, n), this.b = Math.pow(e.b, n), this
        },
        convertGammaToLinear: function(e) {
            return this.copyGammaToLinear(this, e), this
        },
        convertLinearToGamma: function(e) {
            return this.copyLinearToGamma(this, e), this
        },
        copySRGBToLinear: function() {
            function e(e) {
                return e < .04045 ? .0773993808 * e : Math.pow(.9478672986 * e + .0521327014, 2.4)
            }
            return function(t) {
                return this.r = e(t.r), this.g = e(t.g), this.b = e(t.b), this
            }
        }(),
        copyLinearToSRGB: function() {
            function e(e) {
                return e < .0031308 ? 12.92 * e : 1.055 * Math.pow(e, .41666) - .055
            }
            return function(t) {
                return this.r = e(t.r), this.g = e(t.g), this.b = e(t.b), this
            }
        }(),
        convertSRGBToLinear: function() {
            return this.copySRGBToLinear(this), this
        },
        convertLinearToSRGB: function() {
            return this.copyLinearToSRGB(this), this
        },
        getHex: function() {
            return 255 * this.r << 16 ^ 255 * this.g << 8 ^ 255 * this.b << 0
        },
        getHexString: function() {
            return ("000000" + this.getHex().toString(16)).slice(-6)
        },
        getHSL: function(e) {
            void 0 === e && (console.warn("THREE.Color: .getHSL() target is now required"), e = {
                h: 0,
                s: 0,
                l: 0
            });
            var t, n, r = this.r,
                i = this.g,
                a = this.b,
                o = Math.max(r, i, a),
                s = Math.min(r, i, a),
                l = (s + o) / 2;
            if (s === o) t = 0, n = 0;
            else {
                var c = o - s;
                switch (n = l <= .5 ? c / (o + s) : c / (2 - o - s), o) {
                    case r:
                        t = (i - a) / c + (i < a ? 6 : 0);
                        break;
                    case i:
                        t = (a - r) / c + 2;
                        break;
                    case a:
                        t = (r - i) / c + 4
                }
                t /= 6
            }
            return e.h = t, e.s = n, e.l = l, e
        },
        getStyle: function() {
            return "rgb(" + (255 * this.r | 0) + "," + (255 * this.g | 0) + "," + (255 * this.b | 0) + ")"
        },
        offsetHSL: function() {
            var e = {};
            return function(t, n, r) {
                return this.getHSL(e), e.h += t, e.s += n, e.l += r, this.setHSL(e.h, e.s, e.l), this
            }
        }(),
        add: function(e) {
            return this.r += e.r, this.g += e.g, this.b += e.b, this
        },
        addColors: function(e, t) {
            return this.r = e.r + t.r, this.g = e.g + t.g, this.b = e.b + t.b, this
        },
        addScalar: function(e) {
            return this.r += e, this.g += e, this.b += e, this
        },
        sub: function(e) {
            return this.r = Math.max(0, this.r - e.r), this.g = Math.max(0, this.g - e.g), this.b = Math.max(0, this.b - e.b), this
        },
        multiply: function(e) {
            return this.r *= e.r, this.g *= e.g, this.b *= e.b, this
        },
        multiplyScalar: function(e) {
            return this.r *= e, this.g *= e, this.b *= e, this
        },
        lerp: function(e, t) {
            return this.r += (e.r - this.r) * t, this.g += (e.g - this.g) * t, this.b += (e.b - this.b) * t, this
        },
        lerpHSL: function() {
            var e = {
                    h: 0,
                    s: 0,
                    l: 0
                },
                t = {
                    h: 0,
                    s: 0,
                    l: 0
                };
            return function(n, r) {
                this.getHSL(e), n.getHSL(t);
                var i = Cr.lerp(e.h, t.h, r),
                    a = Cr.lerp(e.s, t.s, r),
                    o = Cr.lerp(e.l, t.l, r);
                return this.setHSL(i, a, o), this
            }
        }(),
        equals: function(e) {
            return e.r === this.r && e.g === this.g && e.b === this.b
        },
        fromArray: function(e, t) {
            return void 0 === t && (t = 0), this.r = e[t], this.g = e[t + 1], this.b = e[t + 2], this
        },
        toArray: function(e, t) {
            return void 0 === e && (e = []), void 0 === t && (t = 0), e[t] = this.r, e[t + 1] = this.g, e[t + 2] = this.b, e
        },
        toJSON: function() {
            return this.getHex()
        }
    }), Object.defineProperties(Or.prototype, {
        width: {
            get: function() {
                return this.x
            },
            set: function(e) {
                this.x = e
            }
        },
        height: {
            get: function() {
                return this.y
            },
            set: function(e) {
                this.y = e
            }
        }
    }), Object.assign(Or.prototype, {
        isVector2: !0,
        set: function(e, t) {
            return this.x = e, this.y = t, this
        },
        setScalar: function(e) {
            return this.x = e, this.y = e, this
        },
        setX: function(e) {
            return this.x = e, this
        },
        setY: function(e) {
            return this.y = e, this
        },
        setComponent: function(e, t) {
            switch (e) {
                case 0:
                    this.x = t;
                    break;
                case 1:
                    this.y = t;
                    break;
                default:
                    throw new Error("index is out of range: " + e)
            }
            return this
        },
        getComponent: function(e) {
            switch (e) {
                case 0:
                    return this.x;
                case 1:
                    return this.y;
                default:
                    throw new Error("index is out of range: " + e)
            }
        },
        clone: function() {
            return new this.constructor(this.x, this.y)
        },
        copy: function(e) {
            return this.x = e.x, this.y = e.y, this
        },
        add: function(e, t) {
            return void 0 !== t ? (console.warn("THREE.Vector2: .add() now only accepts one argument. Use .addVectors( a, b ) instead."), this.addVectors(e, t)) : (this.x += e.x, this.y += e.y, this)
        },
        addScalar: function(e) {
            return this.x += e, this.y += e, this
        },
        addVectors: function(e, t) {
            return this.x = e.x + t.x, this.y = e.y + t.y, this
        },
        addScaledVector: function(e, t) {
            return this.x += e.x * t, this.y += e.y * t, this
        },
        sub: function(e, t) {
            return void 0 !== t ? (console.warn("THREE.Vector2: .sub() now only accepts one argument. Use .subVectors( a, b ) instead."), this.subVectors(e, t)) : (this.x -= e.x, this.y -= e.y, this)
        },
        subScalar: function(e) {
            return this.x -= e, this.y -= e, this
        },
        subVectors: function(e, t) {
            return this.x = e.x - t.x, this.y = e.y - t.y, this
        },
        multiply: function(e) {
            return this.x *= e.x, this.y *= e.y, this
        },
        multiplyScalar: function(e) {
            return this.x *= e, this.y *= e, this
        },
        divide: function(e) {
            return this.x /= e.x, this.y /= e.y, this
        },
        divideScalar: function(e) {
            return this.multiplyScalar(1 / e)
        },
        applyMatrix3: function(e) {
            var t = this.x,
                n = this.y,
                r = e.elements;
            return this.x = r[0] * t + r[3] * n + r[6], this.y = r[1] * t + r[4] * n + r[7], this
        },
        min: function(e) {
            return this.x = Math.min(this.x, e.x), this.y = Math.min(this.y, e.y), this
        },
        max: function(e) {
            return this.x = Math.max(this.x, e.x), this.y = Math.max(this.y, e.y), this
        },
        clamp: function(e, t) {
            return this.x = Math.max(e.x, Math.min(t.x, this.x)), this.y = Math.max(e.y, Math.min(t.y, this.y)), this
        },
        clampScalar: function() {
            var e = new Or,
                t = new Or;
            return function(n, r) {
                return e.set(n, n), t.set(r, r), this.clamp(e, t)
            }
        }(),
        clampLength: function(e, t) {
            var n = this.length();
            return this.divideScalar(n || 1).multiplyScalar(Math.max(e, Math.min(t, n)))
        },
        floor: function() {
            return this.x = Math.floor(this.x), this.y = Math.floor(this.y), this
        },
        ceil: function() {
            return this.x = Math.ceil(this.x), this.y = Math.ceil(this.y), this
        },
        round: function() {
            return this.x = Math.round(this.x), this.y = Math.round(this.y), this
        },
        roundToZero: function() {
            return this.x = this.x < 0 ? Math.ceil(this.x) : Math.floor(this.x), this.y = this.y < 0 ? Math.ceil(this.y) : Math.floor(this.y), this
        },
        negate: function() {
            return this.x = -this.x, this.y = -this.y, this
        },
        dot: function(e) {
            return this.x * e.x + this.y * e.y
        },
        cross: function(e) {
            return this.x * e.y - this.y * e.x
        },
        lengthSq: function() {
            return this.x * this.x + this.y * this.y
        },
        length: function() {
            return Math.sqrt(this.x * this.x + this.y * this.y)
        },
        manhattanLength: function() {
            return Math.abs(this.x) + Math.abs(this.y)
        },
        normalize: function() {
            return this.divideScalar(this.length() || 1)
        },
        angle: function() {
            var e = Math.atan2(this.y, this.x);
            return e < 0 && (e += 2 * Math.PI), e
        },
        distanceTo: function(e) {
            return Math.sqrt(this.distanceToSquared(e))
        },
        distanceToSquared: function(e) {
            var t = this.x - e.x,
                n = this.y - e.y;
            return t * t + n * n
        },
        manhattanDistanceTo: function(e) {
            return Math.abs(this.x - e.x) + Math.abs(this.y - e.y)
        },
        setLength: function(e) {
            return this.normalize().multiplyScalar(e)
        },
        lerp: function(e, t) {
            return this.x += (e.x - this.x) * t, this.y += (e.y - this.y) * t, this
        },
        lerpVectors: function(e, t, n) {
            return this.subVectors(t, e).multiplyScalar(n).add(e)
        },
        equals: function(e) {
            return e.x === this.x && e.y === this.y
        },
        fromArray: function(e, t) {
            return void 0 === t && (t = 0), this.x = e[t], this.y = e[t + 1], this
        },
        toArray: function(e, t) {
            return void 0 === e && (e = []), void 0 === t && (t = 0), e[t] = this.x, e[t + 1] = this.y, e
        },
        fromBufferAttribute: function(e, t, n) {
            return void 0 !== n && console.warn("THREE.Vector2: offset has been removed from .fromBufferAttribute()."), this.x = e.getX(t), this.y = e.getY(t), this
        },
        rotateAround: function(e, t) {
            var n = Math.cos(t),
                r = Math.sin(t),
                i = this.x - e.x,
                a = this.y - e.y;
            return this.x = i * n - a * r + e.x, this.y = i * r + a * n + e.y, this
        }
    }), Object.assign(Ur.prototype, {
        isMatrix3: !0,
        set: function(e, t, n, r, i, a, o, s, l) {
            var c = this.elements;
            return c[0] = e, c[1] = r, c[2] = o, c[3] = t, c[4] = i, c[5] = s, c[6] = n, c[7] = a, c[8] = l, this
        },
        identity: function() {
            return this.set(1, 0, 0, 0, 1, 0, 0, 0, 1), this
        },
        clone: function() {
            return (new this.constructor).fromArray(this.elements)
        },
        copy: function(e) {
            var t = this.elements,
                n = e.elements;
            return t[0] = n[0], t[1] = n[1], t[2] = n[2], t[3] = n[3], t[4] = n[4], t[5] = n[5], t[6] = n[6], t[7] = n[7], t[8] = n[8], this
        },
        setFromMatrix4: function(e) {
            var t = e.elements;
            return this.set(t[0], t[4], t[8], t[1], t[5], t[9], t[2], t[6], t[10]), this
        },
        applyToBufferAttribute: function() {
            var e = new Rr;
            return function(t) {
                for (var n = 0, r = t.count; n < r; n++) e.x = t.getX(n), e.y = t.getY(n), e.z = t.getZ(n), e.applyMatrix3(this), t.setXYZ(n, e.x, e.y, e.z);
                return t
            }
        }(),
        multiply: function(e) {
            return this.multiplyMatrices(this, e)
        },
        premultiply: function(e) {
            return this.multiplyMatrices(e, this)
        },
        multiplyMatrices: function(e, t) {
            var n = e.elements,
                r = t.elements,
                i = this.elements,
                a = n[0],
                o = n[3],
                s = n[6],
                l = n[1],
                c = n[4],
                d = n[7],
                u = n[2],
                h = n[5],
                p = n[8],
                f = r[0],
                m = r[3],
                v = r[6],
                g = r[1],
                y = r[4],
                _ = r[7],
                x = r[2],
                E = r[5],
                b = r[8];
            return i[0] = a * f + o * g + s * x, i[3] = a * m + o * y + s * E, i[6] = a * v + o * _ + s * b, i[1] = l * f + c * g + d * x, i[4] = l * m + c * y + d * E, i[7] = l * v + c * _ + d * b, i[2] = u * f + h * g + p * x, i[5] = u * m + h * y + p * E, i[8] = u * v + h * _ + p * b, this
        },
        multiplyScalar: function(e) {
            var t = this.elements;
            return t[0] *= e, t[3] *= e, t[6] *= e, t[1] *= e, t[4] *= e, t[7] *= e, t[2] *= e, t[5] *= e, t[8] *= e, this
        },
        determinant: function() {
            var e = this.elements,
                t = e[0],
                n = e[1],
                r = e[2],
                i = e[3],
                a = e[4],
                o = e[5],
                s = e[6],
                l = e[7],
                c = e[8];
            return t * a * c - t * o * l - n * i * c + n * o * s + r * i * l - r * a * s
        },
        getInverse: function(e, t) {
            e && e.isMatrix4 && console.error("THREE.Matrix3: .getInverse() no longer takes a Matrix4 argument.");
            var n = e.elements,
                r = this.elements,
                i = n[0],
                a = n[1],
                o = n[2],
                s = n[3],
                l = n[4],
                c = n[5],
                d = n[6],
                u = n[7],
                h = n[8],
                p = h * l - c * u,
                f = c * d - h * s,
                m = u * s - l * d,
                v = i * p + a * f + o * m;
            if (0 === v) {
                var g = "THREE.Matrix3: .getInverse() can't invert matrix, determinant is 0";
                if (!0 === t) throw new Error(g);
                return console.warn(g), this.identity()
            }
            var y = 1 / v;
            return r[0] = p * y, r[1] = (o * u - h * a) * y, r[2] = (c * a - o * l) * y, r[3] = f * y, r[4] = (h * i - o * d) * y, r[5] = (o * s - c * i) * y, r[6] = m * y, r[7] = (a * d - u * i) * y, r[8] = (l * i - a * s) * y, this
        },
        transpose: function() {
            var e, t = this.elements;
            return e = t[1], t[1] = t[3], t[3] = e, e = t[2], t[2] = t[6], t[6] = e, e = t[5], t[5] = t[7], t[7] = e, this
        },
        getNormalMatrix: function(e) {
            return this.setFromMatrix4(e).getInverse(this).transpose()
        },
        transposeIntoArray: function(e) {
            var t = this.elements;
            return e[0] = t[0], e[1] = t[3], e[2] = t[6], e[3] = t[1], e[4] = t[4], e[5] = t[7], e[6] = t[2], e[7] = t[5], e[8] = t[8], this
        },
        setUvTransform: function(e, t, n, r, i, a, o) {
            var s = Math.cos(i),
                l = Math.sin(i);
            this.set(n * s, n * l, -n * (s * a + l * o) + a + e, -r * l, r * s, -r * (-l * a + s * o) + o + t, 0, 0, 1)
        },
        scale: function(e, t) {
            var n = this.elements;
            return n[0] *= e, n[3] *= e, n[6] *= e, n[1] *= t, n[4] *= t, n[7] *= t, this
        },
        rotate: function(e) {
            var t = Math.cos(e),
                n = Math.sin(e),
                r = this.elements,
                i = r[0],
                a = r[3],
                o = r[6],
                s = r[1],
                l = r[4],
                c = r[7];
            return r[0] = t * i + n * s, r[3] = t * a + n * l, r[6] = t * o + n * c, r[1] = -n * i + t * s, r[4] = -n * a + t * l, r[7] = -n * o + t * c, this
        },
        translate: function(e, t) {
            var n = this.elements;
            return n[0] += e * n[2], n[3] += e * n[5], n[6] += e * n[8], n[1] += t * n[2], n[4] += t * n[5], n[7] += t * n[8], this
        },
        equals: function(e) {
            for (var t = this.elements, n = e.elements, r = 0; r < 9; r++)
                if (t[r] !== n[r]) return !1;
            return !0
        },
        fromArray: function(e, t) {
            void 0 === t && (t = 0);
            for (var n = 0; n < 9; n++) this.elements[n] = e[n + t];
            return this
        },
        toArray: function(e, t) {
            void 0 === e && (e = []), void 0 === t && (t = 0);
            var n = this.elements;
            return e[t] = n[0], e[t + 1] = n[1], e[t + 2] = n[2], e[t + 3] = n[3], e[t + 4] = n[4], e[t + 5] = n[5], e[t + 6] = n[6], e[t + 7] = n[7], e[t + 8] = n[8], e
        }
    });
    var Nr = {
        common: {
            diffuse: {
                value: new Ir(15658734)
            },
            opacity: {
                value: 1
            },
            map: {
                value: null
            },
            uvTransform: {
                value: new Ur
            },
            alphaMap: {
                value: null
            }
        },
        specularmap: {
            specularMap: {
                value: null
            }
        },
        envmap: {
            envMap: {
                value: null
            },
            flipEnvMap: {
                value: -1
            },
            reflectivity: {
                value: 1
            },
            refractionRatio: {
                value: .98
            },
            maxMipLevel: {
                value: 0
            }
        },
        aomap: {
            aoMap: {
                value: null
            },
            aoMapIntensity: {
                value: 1
            }
        },
        lightmap: {
            lightMap: {
                value: null
            },
            lightMapIntensity: {
                value: 1
            }
        },
        emissivemap: {
            emissiveMap: {
                value: null
            }
        },
        bumpmap: {
            bumpMap: {
                value: null
            },
            bumpScale: {
                value: 1
            }
        },
        normalmap: {
            normalMap: {
                value: null
            },
            normalScale: {
                value: new Or(1, 1)
            }
        },
        displacementmap: {
            displacementMap: {
                value: null
            },
            displacementScale: {
                value: 1
            },
            displacementBias: {
                value: 0
            }
        },
        roughnessmap: {
            roughnessMap: {
                value: null
            }
        },
        metalnessmap: {
            metalnessMap: {
                value: null
            }
        },
        gradientmap: {
            gradientMap: {
                value: null
            }
        },
        fog: {
            fogDensity: {
                value: 25e-5
            },
            fogNear: {
                value: 1
            },
            fogFar: {
                value: 2e3
            },
            fogColor: {
                value: new Ir(16777215)
            }
        },
        lights: {
            ambientLightColor: {
                value: []
            },
            directionalLights: {
                value: [],
                properties: {
                    direction: {},
                    color: {},
                    shadow: {},
                    shadowBias: {},
                    shadowRadius: {},
                    shadowMapSize: {}
                }
            },
            directionalShadowMap: {
                value: []
            },
            directionalShadowMatrix: {
                value: []
            },
            spotLights: {
                value: [],
                properties: {
                    color: {},
                    position: {},
                    direction: {},
                    distance: {},
                    coneCos: {},
                    penumbraCos: {},
                    decay: {},
                    shadow: {},
                    shadowBias: {},
                    shadowRadius: {},
                    shadowMapSize: {}
                }
            },
            spotShadowMap: {
                value: []
            },
            spotShadowMatrix: {
                value: []
            },
            pointLights: {
                value: [],
                properties: {
                    color: {},
                    position: {},
                    decay: {},
                    distance: {},
                    shadow: {},
                    shadowBias: {},
                    shadowRadius: {},
                    shadowMapSize: {},
                    shadowCameraNear: {},
                    shadowCameraFar: {}
                }
            },
            pointShadowMap: {
                value: []
            },
            pointShadowMatrix: {
                value: []
            },
            hemisphereLights: {
                value: [],
                properties: {
                    direction: {},
                    skyColor: {},
                    groundColor: {}
                }
            },
            rectAreaLights: {
                value: [],
                properties: {
                    color: {},
                    position: {},
                    width: {},
                    height: {}
                }
            }
        },
        points: {
            diffuse: {
                value: new Ir(15658734)
            },
            opacity: {
                value: 1
            },
            size: {
                value: 1
            },
            scale: {
                value: 1
            },
            map: {
                value: null
            },
            uvTransform: {
                value: new Ur
            }
        },
        sprite: {
            diffuse: {
                value: new Ir(15658734)
            },
            opacity: {
                value: 1
            },
            center: {
                value: new Or(.5, .5)
            },
            rotation: {
                value: 0
            },
            map: {
                value: null
            },
            uvTransform: {
                value: new Ur
            }
        }
    };
    n.d(t, "ShaderLib", function() {
        return Fr
    });
    var Fr = {
        basic: {
            uniforms: Lr.UniformsUtils.merge([Nr.common, Nr.specularmap, Nr.envmap, Nr.aomap, Nr.lightmap, Nr.fog]),
            vertexShader: Mr.meshbasic_vert,
            fragmentShader: Mr.meshbasic_frag
        },
        lambert: {
            uniforms: Lr.UniformsUtils.merge([Nr.common, Nr.specularmap, Nr.envmap, Nr.aomap, Nr.lightmap, Nr.emissivemap, Nr.fog, Nr.lights, {
                emissive: {
                    value: new Ir(0)
                }
            }]),
            vertexShader: Mr.meshlambert_vert,
            fragmentShader: Mr.meshlambert_frag
        },
        phong: {
            uniforms: Lr.UniformsUtils.merge([Nr.common, Nr.specularmap, Nr.envmap, Nr.aomap, Nr.lightmap, Nr.emissivemap, Nr.bumpmap, Nr.normalmap, Nr.displacementmap, Nr.gradientmap, Nr.fog, Nr.lights, {
                emissive: {
                    value: new Ir(0)
                },
                specular: {
                    value: new Ir(1118481)
                },
                shininess: {
                    value: 30
                }
            }]),
            vertexShader: Mr.meshphong_vert,
            fragmentShader: Mr.meshphong_frag
        },
        standard: {
            uniforms: Lr.UniformsUtils.merge([Nr.common, Nr.envmap, Nr.aomap, Nr.lightmap, Nr.emissivemap, Nr.bumpmap, Nr.normalmap, Nr.displacementmap, Nr.roughnessmap, Nr.metalnessmap, Nr.fog, Nr.lights, {
                emissive: {
                    value: new Ir(0)
                },
                roughness: {
                    value: .5
                },
                metalness: {
                    value: .5
                },
                envMapIntensity: {
                    value: 1
                }
            }]),
            vertexShader: Mr.meshphysical_vert,
            fragmentShader: Mr.meshphysical_frag
        },
        matcap: {
            uniforms: Lr.UniformsUtils.merge([Nr.common, Nr.bumpmap, Nr.normalmap, Nr.displacementmap, Nr.fog, {
                matcap: {
                    value: null
                }
            }]),
            vertexShader: Mr.meshmatcap_vert,
            fragmentShader: Mr.meshmatcap_frag
        },
        points: {
            uniforms: Lr.UniformsUtils.merge([Nr.points, Nr.fog]),
            vertexShader: Mr.points_vert,
            fragmentShader: Mr.points_frag
        },
        dashed: {
            uniforms: Lr.UniformsUtils.merge([Nr.common, Nr.fog, {
                scale: {
                    value: 1
                },
                dashSize: {
                    value: 1
                },
                totalSize: {
                    value: 2
                }
            }]),
            vertexShader: Mr.linedashed_vert,
            fragmentShader: Mr.linedashed_frag
        },
        depth: {
            uniforms: Lr.UniformsUtils.merge([Nr.common, Nr.displacementmap]),
            vertexShader: Mr.depth_vert,
            fragmentShader: Mr.depth_frag
        },
        normal: {
            uniforms: Lr.UniformsUtils.merge([Nr.common, Nr.bumpmap, Nr.normalmap, Nr.displacementmap, {
                opacity: {
                    value: 1
                }
            }]),
            vertexShader: Mr.normal_vert,
            fragmentShader: Mr.normal_frag
        },
        sprite: {
            uniforms: Lr.UniformsUtils.merge([Nr.sprite, Nr.fog]),
            vertexShader: Mr.sprite_vert,
            fragmentShader: Mr.sprite_frag
        },
        background: {
            uniforms: {
                t2D: {
                    value: null
                }
            },
            vertexShader: Mr.background_vert,
            fragmentShader: Mr.background_frag
        },
        cube: {
            uniforms: {
                tCube: {
                    value: null
                },
                tFlip: {
                    value: -1
                },
                opacity: {
                    value: 1
                }
            },
            vertexShader: Mr.cube_vert,
            fragmentShader: Mr.cube_frag
        },
        equirect: {
            uniforms: {
                tEquirect: {
                    value: null
                }
            },
            vertexShader: Mr.equirect_vert,
            fragmentShader: Mr.equirect_frag
        },
        distanceRGBA: {
            uniforms: Lr.UniformsUtils.merge([Nr.common, Nr.displacementmap, {
                referencePosition: {
                    value: new Rr
                },
                nearDistance: {
                    value: 1
                },
                farDistance: {
                    value: 1e3
                }
            }]),
            vertexShader: Mr.distanceRGBA_vert,
            fragmentShader: Mr.distanceRGBA_frag
        },
        shadow: {
            uniforms: Lr.UniformsUtils.merge([Nr.lights, Nr.fog, {
                color: {
                    value: new Ir(0)
                },
                opacity: {
                    value: 1
                }
            }]),
            vertexShader: Mr.shadow_vert,
            fragmentShader: Mr.shadow_frag
        }
    };
    Fr.physical = {
        uniforms: Lr.UniformsUtils.merge([Fr.standard.uniforms, {
            clearCoat: {
                value: 0
            },
            clearCoatRoughness: {
                value: 0
            }
        }]),
        vertexShader: Mr.meshphysical_vert,
        fragmentShader: Mr.meshphysical_frag
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(20),
        i = n(6),
        a = n(3),
        o = [];
    t.default = class extends i.default {
        constructor(e) {
            super(), this.addEvents("component", "change", "dispose"), this.id = e || r.default(8), this.system = null, this._name = "", this._componentList = [], this._componentsByType = {}
        }
        init(e) {
            this.system = e, e.addEntity(this)
        }
        dispose() {
            this.emit("dispose"), this._componentList.slice().forEach(e => e.dispose()), this.system.removeEntity(this)
        }
        get name() {
            return this._name
        }
        set name(e) {
            this._name = e, this.emit("change", {
                what: "name"
            })
        }
        createEntity(e) {
            return this.system.createEntity(e)
        }
        createComponent(e, t) {
            return this.system.createComponent(this, e, t)
        }
        getOrCreateComponent(e, t) {
            const n = this.getComponent(e);
            return n || this.createComponent(e, t)
        }
        addComponent(e) {
            if (e.isSystemSingleton() && this.hasComponents(e, !0)) throw new Error(`only one component of type '${e.type}' allowed per system`);
            if (e.isEntitySingleton() && this.hasComponents(e)) throw new Error(`only one component of type '${e.type}' allowed per entity`);
            let t = Object.getPrototypeOf(e);
            for (;
                (t = Object.getPrototypeOf(t)).type !== a.default.type;) this.addBaseComponent(e, t);
            this._componentList.push(e), this.getComponentArrayByType(e.type).push(e), this.system.addComponent(e), this._componentList.forEach(t => {
                t !== e && t.didAddComponent(e)
            }), this.emit("component", {
                add: !0,
                remove: !1,
                component: e
            })
        }
        removeComponent(e) {
            let t = this._componentList.indexOf(e);
            if (t < 0) return !1;
            this._componentList.forEach(t => {
                t !== e && t.willRemoveComponent(e)
            }), this.system.removeComponent(e), this._componentList.splice(t, 1);
            const n = this._componentsByType[e.type];
            t = n.indexOf(e), n.splice(t, 1);
            let r = Object.getPrototypeOf(e);
            for (;
                (r = Object.getPrototypeOf(r)).type !== a.default.type;) this.removeBaseComponent(e, r);
            return this.emit("component", {
                add: !1,
                remove: !0,
                component: e
            }), !0
        }
        hasComponents(e, t) {
            if (t) return this.system.hasComponents(e);
            const n = this._componentsByType[a.getType(e)];
            return n && n.length > 0
        }
        countComponents(e, t) {
            if (t) return this.system.countComponents(e);
            const n = e ? this._componentsByType[a.getType(e)] : this._componentList;
            return n ? n.length : 0
        }
        getComponents(e, t) {
            return e ? t ? this.system.getComponents(e) : this._componentsByType[a.getType(e)] || o : this._componentList
        }
        getComponent(e, t) {
            if (t) return this.system.getComponent(e);
            const n = this._componentsByType[a.getType(e)];
            return n ? n[0] : void 0
        }
        getComponentById(e) {
            return this.system.getComponentById(e)
        }
        findComponentByName(e, t, n) {
            return n ? this.system.findComponentByName(e, t) : this._componentList.find(n => n.name === e && (!t || n.type === a.getType(t)))
        }
        toJSON() {
            return {
                id: this.id,
                name: this._name
            }
        }
        toString(e = !1) {
            const t = `Entity '${this.name}' - ${this.countComponents()} components`;
            return e ? t + "\n" + this._componentList.map(e => "  " + e.toString()).join("\n") : t
        }
        addBaseComponent(e, t) {
            this.getComponentArrayByType(a.getType(t)).push(e), this.system.addBaseComponent(e, t)
        }
        removeBaseComponent(e, t) {
            const n = this._componentsByType[a.getType(t)];
            if (!n) throw new Error(`can't remove unregistered base: '${a.getType(t)}' of component: '${a.getType(e)}'`);
            const r = n.indexOf(e);
            n.splice(r, 1), this.system.removeBaseComponent(e, t)
        }
        getComponentArrayByType(e) {
            let t = this._componentsByType[e];
            return t || (t = this._componentsByType[e] = []), t
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(163),
        a = n(196),
        o = .5 * Math.PI,
        s = [new r.Vector3(0, -o, 0), new r.Vector3(0, o, 0), new r.Vector3(o, 0, 0), new r.Vector3(-o, 0, 0), new r.Vector3(0, 0, 0), new r.Vector3(0, 0, Math.PI)];
    var l;
    ! function(e) {
        e[e.Left = 0] = "Left", e[e.Right = 1] = "Right", e[e.Top = 2] = "Top", e[e.Bottom = 3] = "Bottom", e[e.Front = 4] = "Front", e[e.Back = 5] = "Back"
    }(t.EViewportCameraView || (t.EViewportCameraView = {})),
    function(e) {
        e[e.Perspective = 0] = "Perspective", e[e.Orthographic = 1] = "Orthographic"
    }(l = t.EViewportCameraType || (t.EViewportCameraType = {}));
    t.default = class {
        constructor(e, t, n, o) {
            this.x = 0, this.y = 0, this.width = 100, this.height = 100, this.index = 0, this.changed = !0, this.canvasWidth = 100, this.canvasHeight = 100, this.sceneCamera = null, this.useSceneCamera = !0, this._x = e || 0, this._y = t || 0, this._width = void 0 !== n ? n : 1, this._height = void 0 !== o ? o : 1, this.vpCamera = new r.OrthographicCamera(-10, 10, 10, -10, .001, 1e4), this.vpManip = new i.default, this.vpController = new a.default
        }
        get camera() {
            return this.useSceneCamera ? this.sceneCamera : this.vpCamera
        }
        apply(e) {
            e.setViewport(this.x, this.y, this.width, this.height);
            const t = this.camera;
            return this.updateCameraAspect(t, this.width / this.height), t
        }
        render(e, t) {
            const n = this.apply(e);
            e.render(t, n)
        }
        updateCamera(e) {
            const t = this.vpManip.getDeltaPose();
            if (t || e) {
                const e = this.vpController;
                e.update(t);
                const n = this.vpCamera;
                if (e.toMatrix(n.matrix), n.matrixWorldNeedsUpdate = !0, n.isOrthographicCamera) {
                    const t = n.userData.aspect,
                        r = .5 * e.size;
                    n.top = r, n.bottom = -r, n.left = -r * t, n.right = r * t, n.updateProjectionMatrix()
                }
            }
        }
        setCamera(e, t) {
            return this.useSceneCamera = !1, this.setCameraType(e), this.setCameraView(t), this
        }
        setCameraType(e) {
            const t = this.vpCamera;
            t && this.vpController.fromMatrix(t.matrix), e === l.Perspective ? (this.vpCamera = new r.PerspectiveCamera(45, 1, .001, 1e4), this.vpController.orthographicMode = !1) : e === l.Orthographic && (this.vpCamera = new r.OrthographicCamera(-10, 10, 10, -10, .001, 1e4), this.vpController.orthographicMode = !0, this.vpController.orientationEnabled = !1), this.vpCamera.matrixAutoUpdate = !1, this.updateCamera(!0)
        }
        setCameraView(e) {
            this.vpController.orientation.copy(s[e]), this.vpController.offset.set(0, 0, 1e3), this.vpController.toMatrix(this.vpCamera.matrix), this.vpCamera.matrixWorldNeedsUpdate = !0
        }
        set(e = 0, t = 0, n = 1, r = 1) {
            return this._x = e, this._y = t, this._width = n, this._height = r, this.updateViewport(), this
        }
        isPointInside(e, t) {
            return e >= this.x && e < this.x + this.width && t >= this.y && t < this.y + this.height
        }
        getDeviceCoords(e, t, n) {
            const i = (e - this.x) / this.width * 2 - 1,
                a = 1 - (t - this.y) / this.height * 2;
            return n ? n.set(i, a) : new r.Vector2(i, a)
        }
        setCanvasSize(e, t) {
            this.canvasWidth = e, this.canvasHeight = t, this.updateViewport()
        }
        onPointer(e) {
            return this.vpManip.onPointer(e)
        }
        onTrigger(e) {
            return this.vpManip.onTrigger(e)
        }
        updateViewport() {
            this.x = this._x * this.canvasWidth, this.y = this._y * this.canvasHeight, this.width = this._width * this.canvasWidth, this.height = this._height * this.canvasHeight, this.vpController.setViewportSize(this.width, this.height)
        }
        updateCameraAspect(e, t) {
            if (e.userData.aspect !== t)
                if (e.userData.aspect = t, "PerspectiveCamera" === e.type) {
                    const n = e;
                    n.aspect = t, n.updateProjectionMatrix()
                } else if ("OrthographicCamera" === e.type) {
                const n = e,
                    r = .5 * (n.top - n.bottom) * t;
                n.left = -r, n.right = r, n.updateProjectionMatrix()
            }
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(24);
    t.default = class {
        constructor(e = !1) {
            this.orientation = new r.Vector3, this.offset = new r.Vector3, this.size = 50, this.orientationEnabled = !0, this.orthographicMode = e, this.viewportWidth = 100, this.viewportHeight = 100
        }
        update(e) {
            if (!e) return !1;
            const {
                orientation: t,
                offset: n
            } = this;
            let r;
            this.orientationEnabled && (t.x += 300 * e.dPitch / this.viewportHeight, t.y += 300 * e.dHead / this.viewportHeight, t.z += 300 * e.dRoll / this.viewportHeight), r = this.orthographicMode ? this.size = Math.max(this.size, .1) * e.dScale : this.offset.z = Math.max(this.offset.z, .1) * e.dScale, n.x -= e.dX * r / this.viewportHeight, n.y += e.dY * r / this.viewportHeight
        }
        toMatrix(e) {
            return e = e || new r.Matrix4, i.default.composeOrbitMatrix(this.orientation, this.offset, e), e
        }
        fromMatrix(e) {
            i.default.decomposeOrbitMatrix(e, this.orientation, this.offset)
        }
        setViewportSize(e, t) {
            this.viewportWidth = e, this.viewportHeight = t
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(198);
    t.default = class {
        constructor(e, t) {
            this._args = e, this._props = t, this._args = e, this._state = null
        }
        get name() {
            return this._props.name || r.normalize(this._props.do.name)
        }
        do() {
            if (this._state) throw new Error("undo should be called before execute can be applied again");
            this._state = this._props.do.apply(this._props.target, this._args)
        }
        undo() {
            if (!this._props.undo) throw new Error("can't undo this command");
            if (!this._state) throw new Error("execute should be called before undo can be applied");
            this._props.undo.call(this._props.target, this._state), this._state = null
        }
        canDo() {
            return !this._props.canDo || this._props.canDo()
        }
        canUndo() {
            return !!this._props.undo
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    }), t.camelize = function(e) {
        return e.replace(/(?:^\w|[A-Z]|\b\w)/g, (e, t) => 0 == t ? e.toLowerCase() : e.toUpperCase()).replace(/\s+/g, "")
    }, t.normalize = function(e) {
        return e.replace(/([A-Z])/g, " $1").replace(/^./, e => e.toUpperCase())
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(6),
        i = /^(\S*?)(\[(\d+)\])*$/;
    t.default = class extends r.default {
        constructor(e, t) {
            super(), this.addEvents("change"), this.linkable = e, this.properties = [], this._propsByPath = {}, t && Object.keys(t).forEach(e => this.add(e, t[e]))
        }
        merge(e) {
            return Object.keys(e).forEach(t => this.add(t, e[t])), this
        }
        add(e, t) {
            if (this[e]) throw new Error(`key already exists in properties: '${e}'`);
            return t.props = this, t.key = e, this[e] = t, this.properties.push(t), this._propsByPath[t.path] = t, this
        }
        remove(e) {
            const t = this[e];
            if (!t) return !1;
            delete this[e];
            const n = this.properties,
                r = n.indexOf(t);
            n.slice(r, 1), delete this._propsByPath[t.path]
        }
        getProperty(e) {
            const t = e.match(i);
            if (!t) throw new Error(`malformed path '${e}'`);
            const n = this._propsByPath[t[1]];
            if (!n) throw new Error(`no property found at path '${e}'`);
            const r = {
                property: n
            };
            return void 0 !== t[2] && (r.index = parseInt(t[2])), r
        }
        setValue(e, t) {
            const {
                property: n
            } = this.getProperty(e);
            n.setValue(t)
        }
        setValues(e) {
            Object.keys(e).forEach(t => this[t].setValue(e[t]))
        }
        setAll() {
            const e = this.properties;
            for (let t = 0, n = e.length; t < n; ++t) e[t].set()
        }
        pushAll() {
            const e = this.properties;
            for (let t = 0, n = e.length; t < n; ++t) e[t].push()
        }
        getValue(e) {
            const {
                property: t
            } = this.getProperty(e);
            return t.value
        }
        hasChanged(e) {
            const {
                property: t
            } = this.getProperty(e);
            return t.changed
        }
        linkTo(e, t, n) {
            t.linkFrom(this, e, n)
        }
        linkFrom(e, t, n) {
            const r = e.getProperty(t),
                i = this.getProperty(n);
            i.property.linkFrom(r.property, r.index, i.index)
        }
        unlinkTo(e, t, n) {
            t.unlinkFrom(this, e, n)
        }
        unlinkFrom(e, t, n) {
            const r = e.getProperty(t),
                i = this.getProperty(n);
            i.property.unlinkFrom(r.property, r.index, i.index)
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    t.default = class {
        constructor() {
            this.viewport = null, this.camera = null, this.scene = null
        }
        set(e, t, n) {
            this.viewport = e, this.camera = t, this.scene = n
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = n(185);
    class a extends r.Component {
        render() {
            const e = this.props;
            let t = Object.assign({}, a.sectionStyle);
            return t.flexBasis = e.size || "0%", r.createElement("div", {
                className: e.className,
                style: t
            }, e.children)
        }
    }
    a.defaultProps = {
        className: "ff-splitter-section"
    }, a.sectionStyle = {
        position: "relative",
        flex: "1 1 0%",
        overflow: "hidden"
    }, t.SplitterSection = a;
    class o extends r.Component {
        constructor(e) {
            super(e), this.isVertical = "vertical" === this.props.direction, this.element = null, this.handleElements = [], this.sectionElements = [], this.sections = [], this.activeHandleIndex = -1, this.containerSize = 0, this.lastResizeEvent = null, this.onRef = this.onRef.bind(this), this.onDragBegin = this.onDragBegin.bind(this), this.onDragMove = this.onDragMove.bind(this), this.onDragEnd = this.onDragEnd.bind(this)
        }
        render() {
            const e = this.props.children;
            let t = [];
            if (Array.isArray(e) && e.length > 0) {
                const n = this.sections = e.filter(e => e.type === a),
                    s = n.length,
                    l = [];
                let c = 0,
                    d = 0;
                for (let e = 0; e < s; ++e) {
                    let t = n[e].props.size,
                        r = 0;
                    t && (r = "string" == typeof t ? t.endsWith("%") ? Number.parseFloat(t) / 100 : Number.parseFloat(t) : t), r > 0 && (c += r, d++), l.push(r)
                }
                let u = 0;
                d < s && (c += (u = c < 1 ? (1 - c) / (s - d) : c / s) * (s - d));
                let h = this.isVertical ? o.verticalHandleStyle : o.horizontalHandleStyle;
                for (let e = 0; e < s; ++e) {
                    let a;
                    a = l[e] > 0 ? (l[e] / c * 100).toFixed(3) + "%" : (u / c * 100).toFixed(3) + "%";
                    const o = n[e].key || e,
                        d = n[e].props.className + (this.isVertical ? " ff-vertical" : " ff-horizontal");
                    t.push(r.cloneElement(n[e], {
                        key: "s" + o,
                        size: a,
                        className: d
                    })), e < s - 1 && t.push(r.createElement(i.default, {
                        key: "d" + o,
                        className: "ff-splitter-handle",
                        style: h,
                        onDragBegin: this.onDragBegin,
                        onDragMove: this.onDragMove,
                        onDragEnd: this.onDragEnd
                    }))
                }
            }
            let n = Object.assign({}, o.containerStyle);
            return n.flexDirection = this.isVertical ? "column" : "row", r.createElement("div", {
                className: this.props.className,
                style: n,
                ref: this.onRef
            }, t)
        }
        componentDidMount() {
            this.updateConfiguration()
        }
        componentDidUpdate() {
            this.updateConfiguration()
        }
        updateConfiguration() {
            this.handleElements.length = 0, this.sectionElements.length = 0;
            const e = this.element.children;
            for (let t = 0; t < e.length; ++t) {
                let n = e[t];
                (t % 2 == 0 ? this.sectionElements : this.handleElements).push(n)
            }
        }
        onRef(e) {
            this.element = e
        }
        onDragBegin(e) {
            this.activeHandleIndex = this.handleElements.indexOf(e.target), this.containerSize = this.isVertical ? this.element.clientHeight : this.element.clientWidth
        }
        onDragMove(e, t, n) {
            const r = this.activeHandleIndex;
            if (r >= 0) {
                const e = this.isVertical,
                    i = this.containerSize,
                    a = this.sectionElements[r],
                    o = this.sectionElements[r + 1];
                let s = e ? n : t,
                    l = (e ? a.offsetHeight : a.offsetWidth) + s,
                    c = (e ? o.offsetHeight : o.offsetWidth) - s;
                const d = this.props.margin;
                l < d ? (c += l - d, l = d) : c < d && (l += c - d, c = d), l /= i, c /= i, a.style.flexBasis = (100 * l).toFixed(3) + "%", o.style.flexBasis = (100 * c).toFixed(3) + "%", (this.props.onResize || this.props.resizeEvent) && window.dispatchEvent(new Event("resize")), this.props.onResize && (this.lastResizeEvent = {
                    index: r,
                    sectionIds: [this.sections[r].props.id, this.sections[r + 1].props.id],
                    sizes: [l, c],
                    isDragging: !0,
                    id: this.props.id,
                    sender: this
                }, this.props.onResize(this.lastResizeEvent))
            }
        }
        onDragEnd() {
            this.containerSize = 0, this.activeHandleIndex = -1, this.lastResizeEvent && this.props.onResize && (this.lastResizeEvent.isDragging = !1, this.props.onResize(this.lastResizeEvent), this.lastResizeEvent = null)
        }
    }
    o.defaultProps = {
        className: "ff-splitter-container",
        direction: "horizontal",
        margin: 20
    }, o.containerStyle = {
        position: "absolute",
        left: 0,
        right: 0,
        top: 0,
        bottom: 0,
        overflow: "hidden",
        display: "flex",
        flexDirection: "row"
    }, o.horizontalHandleStyle = {
        position: "relative",
        zIndex: 1,
        padding: "0 5px",
        margin: "0 -5px",
        cursor: "col-resize"
    }, o.verticalHandleStyle = {
        position: "relative",
        zIndex: 1,
        padding: "5px 0",
        margin: "-5px 0",
        cursor: "row-resize"
    }, t.SplitterContainer = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = function(e) {
            const {
                className: t,
                style: n,
                grow: i,
                shrink: a,
                basis: o
            } = e, s = {
                flex: `${i} ${a} ${o}`
            }, l = Object.assign({}, s, n);
            return r.createElement("div", {
                className: t,
                style: l
            })
        };
    i.defaultProps = {
        className: "ff-flex-spacer",
        grow: 1,
        shrink: 0,
        basis: "auto"
    }, t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    }), t.enumToArray = function(e) {
        return Object.keys(e).filter(e => isNaN(Number(e)))
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(6),
        i = n(164),
        a = n(205),
        o = n(165);
    t.default = class extends r.default {
        constructor(e, t, n) {
            let r;
            super(), this.addEvent("value");
            const i = t;
            r = i.prototype instanceof o.default ? {
                objectType: i,
                preset: null
            } : "object" != typeof t || null === t || Array.isArray(t) ? {
                preset: t
            } : t, n = void 0 !== n ? n : r.preset;
            const a = Array.isArray(n);
            this.props = null, this.key = null, this.path = e, this.preset = n, this.elements = a ? n.length : 1, this.type = typeof(a ? n[0] : n), this.schema = r, this.value = null, this.changed = !r.event, this.inLinks = [], this.outLinks = [], this.reset()
        }
        setValue(e) {
            this.value = e, this.changed = !0;
            const t = this.outLinks;
            for (let e = 0, n = t.length; e < n; ++e) t[e].push();
            this.emitAny("value", e), this.props && (this.props.linkable.changed = !0)
        }
        set() {
            this.changed = !0;
            const e = this.outLinks;
            for (let t = 0, n = e.length; t < n; ++t) e[t].push();
            this.emitAny("value", this.value), this.props && (this.props.linkable.changed = !0)
        }
        pushValue(e) {
            this.value = e, this.changed = !0;
            const t = this.outLinks;
            for (let e = 0, n = t.length; e < n; ++e) t[e].push();
            this.emitAny("value", this.value)
        }
        push() {
            this.changed = !0;
            const e = this.outLinks;
            for (let t = 0, n = e.length; t < n; ++t) e[t].push();
            this.emitAny("value", this.value)
        }
        linkTo(e, t, n) {
            e.linkFrom(this, t, n)
        }
        linkFrom(e, t, n) {
            if (!this.canLinkFrom(e, t, n)) throw new Error("can't link");
            const r = new a.default(e, this, t, n);
            this.addInLink(r), e.addOutLink(r)
        }
        unlinkTo(e, t, n) {
            return e.unlinkFrom(this, t, n)
        }
        unlinkFrom(e, t, n) {
            const r = this.inLinks.find(r => r.source === e && r.sourceIndex === t && r.destinationIndex === n);
            return !!r && (this.removeInLink(r), e.removeOutLink(r), !0)
        }
        unlink() {
            this.inLinks.forEach(e => e.source.removeOutLink(e)), this.inLinks.length = 0, this.outLinks.forEach(e => e.destination.removeInLink(e)), this.outLinks.length = 0
        }
        addInLink(e) {
            if (e.destination !== this) throw new Error("input link's destination must equal this");
            this.inLinks.push(e)
        }
        addOutLink(e) {
            if (e.source !== this) throw new Error("output link's source must equal this");
            this.outLinks.push(e), e.push()
        }
        removeInLink(e) {
            const t = this.inLinks.indexOf(e);
            if (t < 0) throw new Error("input link not found");
            this.inLinks.splice(t, 1), 0 === this.inLinks.length && "object" === this.type && this.reset()
        }
        removeOutLink(e) {
            const t = this.outLinks.indexOf(e);
            if (t < 0) throw new Error("output link not found");
            this.outLinks.splice(t, 1)
        }
        canLinkTo(e, t, n) {
            return e.canLinkFrom(this, t, n)
        }
        canLinkFrom(e, t, n) {
            if (this.props !== this.props.linkable.ins) return !1;
            const r = t >= 0,
                a = n >= 0;
            if (1 === e.elements && r) throw new Error("non-array source property; can't link to element");
            if (1 === this.elements && a) throw new Error("non-array destination property; can't link to element");
            const o = e.elements > 1 && !r;
            return o === (this.elements > 1 && !a) && (!o || e.elements === this.elements) && ("object" !== e.type || "object" !== this.type || e.schema.objectType === this.schema.objectType) && i.canConvert(e.type, this.type)
        }
        reset() {
            if (this.hasInLinks()) throw new Error("can't reset property with input links");
            let e;
            if (this.isMulti()) {
                let t = this.value;
                t ? t.length = 1 : e = t = [], t[0] = this.clonePreset()
            } else e = this.clonePreset();
            this.setValue(e)
        }
        setMultiChannelCount(e) {
            if (!this.isMulti()) throw new Error("can't set multi channel count on non-multi property");
            const t = this.value,
                n = t.length;
            t.length = e;
            for (let r = n; r < e; ++r) t[r] = this.clonePreset();
            this.changed = !0
        }
        isArray() {
            return Array.isArray(this.preset)
        }
        isMulti() {
            return !!this.schema.multi
        }
        isDefault() {
            const e = this.schema.multi ? this.value[0] : this.value,
                t = this.preset,
                n = Array.isArray(e) ? e.length : -1;
            if (n !== (Array.isArray(t) ? t.length : -1)) return !1;
            if (n >= 0) {
                for (let r = 0; r < n; ++r)
                    if (e[r] !== t[r]) return !1;
                return !0
            }
            return e === t
        }
        hasInLinks() {
            return this.inLinks.length > 0
        }
        hasOutLinks() {
            return this.outLinks.length > 0
        }
        inLinkCount() {
            return this.inLinks.length
        }
        outLinkCount() {
            return this.outLinks.length
        }
        toJSON() {
            const e = {
                key: this.key
            };
            this.hasInLinks() || this.isDefault() || (e.value = this.value), this.hasOutLinks() && (e.links = this.outLinks.map(e => ({
                component: e.destination.props.linkable.id,
                key: e.destination.key
            })))
        }
        toString() {}
        copyValue() {
            const e = this.value;
            return Array.isArray(e) ? e.slice() : e
        }
        clonePreset() {
            const e = this.preset;
            return Array.isArray(e) ? e.slice() : e
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(164);
    t.default = class {
        constructor(e, t, n, i) {
            if (1 === e.elements && n >= 0) throw new Error("non-array source property; can't link to element");
            if (1 === t.elements && i >= 0) throw new Error("non-array destination property; can't link to element");
            this.source = e, this.destination = t, this.sourceIndex = n, this.destinationIndex = i;
            const a = void 0 === n ? -1 : n,
                o = void 0 === i ? -1 : i,
                s = e.elements > 1 && a < 0 && o < 0;
            this.fnConvert = r.getConversionFunction(e.type, t.type, s);
            const l = r.getElementCopyFunction(a, o, this.fnConvert);
            this.fnCopy = r.getMultiCopyFunction(e.isMulti(), t.isMulti(), l)
        }
        push() {
            this.destination.setValue(this.fnCopy(this.source.value, this.destination.value, this.fnConvert))
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(24),
        a = n(27),
        o = n(18),
        s = n(9),
        l = [o.EDerivativeQuality.Thumb, o.EDerivativeQuality.Low, o.EDerivativeQuality.Medium, o.EDerivativeQuality.High, o.EDerivativeQuality.Highest];
    t.default = class extends r.Group {
        constructor() {
            super(), this.isModel = !0, this.onLoad = null, this.units = "cm", this.boundingBox = null, this.boxFrame = null, this.derivatives = [], this.activeDerivative = null, this.assetLoader = null, this.assetPath = ""
        }
        autoLoad(e) {
            const t = [],
                n = this.findDerivative(o.EDerivativeQuality.Thumb);
            n && t.push(n);
            const r = this.selectDerivative(e);
            return r && t.push(r), 0 === t.length ? Promise.reject(new Error("no suitable web-derivatives available")) : t.reduce((e, t) => e.then(() => this.loadDerivative(t)), Promise.resolve())
        }
        loadDerivative(e) {
            return e.load(this.assetLoader, this.assetPath).then(() => {
                if (!e.model) return;
                this.boxFrame && this.remove(this.boxFrame), this.activeDerivative && this.remove(this.activeDerivative.model), !this.boundingBox && e.boundingBox && (this.boundingBox = e.boundingBox.clone()), this.activeDerivative = e, this.add(e.model), this.onLoad && this.onLoad();
                const t = e.boundingBox,
                    n = {
                        min: t.min.toArray(),
                        max: t.max.toArray()
                    };
                console.log("derivative bounding box: ", n)
            })
        }
        addDerivative(e) {
            this.derivatives.push(e)
        }
        addWebModelDerivative(e, t) {
            const n = new o.default(o.EDerivativeUsage.Web, t);
            n.addAsset(e, a.EAssetType.Model), this.addDerivative(n)
        }
        addGeometryAndTextureDerivative(e, t, n) {
            const r = new o.default(o.EDerivativeUsage.Web, n);
            r.addAsset(e, a.EAssetType.Geometry), t && r.addAsset(t, a.EAssetType.Image, a.EMapType.Color), this.addDerivative(r)
        }
        removeDerivative(e) {
            const t = this.derivatives.indexOf(e);
            this.derivatives.splice(t, 1)
        }
        selectDerivative(e, t) {
            t = void 0 !== t ? t : o.EDerivativeUsage.Web;
            const n = l.indexOf(e);
            if (n < 0) throw new Error(`derivative quality not supported: '${o.EDerivativeQuality[e]}'`);
            const r = this.findDerivative(e, t);
            if (r) return r;
            for (let r = n + 1; r < l.length; ++r) {
                const n = this.findDerivative(l[r], t);
                if (n) return console.warn(`derivative quality '${o.EDerivativeQuality[e]}' not available, using higher quality`), n
            }
            for (let r = n - 1; r >= 0; --r) {
                const n = this.findDerivative(l[r], t);
                if (n) return console.warn(`derivative quality '${o.EDerivativeQuality[e]}' not available, using lower quality`), n
            }
            return console.warn(`no suitable derivative found for quality '${o.EDerivativeQuality[e]}'` + ` and usage '${o.EDerivativeUsage[t]}'`), null
        }
        findDerivative(e, t) {
            t = void 0 !== t ? t : o.EDerivativeUsage.Web;
            for (let n = 0, r = this.derivatives.length; n < r; ++n) {
                const r = this.derivatives[n];
                if (r && r.usage === t && r.quality === e) return r
            }
            return null
        }
        setShaderMode(e) {
            this.traverse(t => {
                const n = t.material;
                n && n instanceof s.default && n.setShaderMode(e)
            })
        }
        setAssetLoader(e, t) {
            this.assetLoader = e, this.assetPath = t
        }
        fromData(e) {
            this.units = e.units, e.derivatives.forEach(e => {
                const t = o.EDerivativeUsage[e.usage],
                    n = o.EDerivativeQuality[e.quality];
                this.addDerivative(new o.default(t, n, e.assets))
            }), e.transform && (this.matrix.fromArray(e.transform), this.matrixWorldNeedsUpdate = !0), e.boundingBox && (this.boundingBox = new r.Box3, this.boundingBox.min.fromArray(e.boundingBox.min), this.boundingBox.max.fromArray(e.boundingBox.max), this.boxFrame = new r.Box3Helper(this.boundingBox, "#ffffff"), this.add(this.boxFrame), this.onLoad && this.onLoad())
        }
        toData() {
            const e = {
                units: this.units,
                derivatives: this.derivatives.map(e => e.toData())
            };
            return this.boundingBox && (e.boundingBox = {
                min: this.boundingBox.min.toArray(),
                max: this.boundingBox.max.toArray()
            }), i.default.isMatrix4Identity(this.matrix) || (e.transform = this.matrix.toArray()), e
        }
    }
}, function(e) {
    e.exports = {
        asset: {
            copyright: "Copyright Smithsonian Institution",
            generator: "Voyager Presentation Parser",
            version: "1.0"
        },
        scene: {
            nodes: [0, 1, 2]
        },
        nodes: [{
            reference: 0
        }, {
            translation: [0, 0, 15],
            camera: 0
        }, {
            name: "Lights",
            children: [3, 4, 5, 6]
        }, {
            translation: [-3, 1, 2],
            name: "Light",
            light: 0
        }, {
            translation: [2, 0, 3],
            name: "Light",
            light: 1
        }, {
            translation: [0, 2, -.5],
            name: "Light",
            light: 2
        }, {
            translation: [0, -2, -1.2],
            name: "Light",
            light: 3
        }],
        references: [{
            mimeType: "application/si-dpo-3d.item+json",
            uri: "0"
        }],
        cameras: [{
            type: "perspective",
            perspective: {
                yfov: 45,
                znear: .1,
                zfar: 1e4
            }
        }],
        lights: [{
            type: "directional",
            color: [1, .95, .9],
            intensity: .8
        }, {
            type: "directional",
            color: [1, 1, 1],
            intensity: .8
        }, {
            type: "directional",
            color: [1, .95, .85],
            intensity: .5
        }, {
            type: "directional",
            color: [.8, .85, 1],
            intensity: 1
        }],
        explorer: {
            renderer: {
                units: "cm",
                shader: "inherit",
                exposure: 1,
                gamma: 1
            },
            reader: {
                enabled: !1
            }
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(209),
        a = n(210),
        o = n(214);
    t.default = class {
        constructor() {
            this.manager = new r.LoadingManager, this.jsonLoader = new i.default(this.manager), this.assetLoader = new a.default(this.manager), this.validator = new o.default
        }
        loadJSON(e) {
            return this.jsonLoader.load(e)
        }
        validatePresentation(e) {
            return new Promise((t, n) => this.validator.validatePresentation(e) ? t(e) : n(new Error("invalid presentation data, validation failed")))
        }
        validateItem(e) {
            return new Promise((t, n) => this.validator.validateItem(e) ? t(e) : n(new Error("invalid item data, validation failed")))
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    t.default = class {
        constructor(e) {
            this.loadingManager = e
        }
        load(e) {
            return this.loadingManager.itemStart(e), fetch(e, {
                headers: {
                    Accept: "application/json"
                }
            }).then(t => {
                if (!t.ok) throw this.loadingManager.itemError(e), new Error(`failed to fetch from '${e}', status: ${t.status} ${t.statusText}`);
                return this.loadingManager.itemEnd(e), t.json()
            })
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(19),
        i = n(211),
        a = n(212),
        o = n(213),
        s = n(27);
    t.default = class {
        constructor(e) {
            this.modelLoader = new i.default(e), this.geometryLoader = new a.default(e), this.textureLoader = new o.default(e)
        }
        loadModel(e, t) {
            const n = r.default(e.uri, t);
            return this.modelLoader.load(n)
        }
        loadGeometry(e, t) {
            const n = r.default(e.uri, t);
            return this.geometryLoader.load(n)
        }
        loadTexture(e, t) {
            const n = r.default(e.uri, t);
            return this.textureLoader.load(n)
        }
        getAssetType(e) {
            if (e.type) return s.EAssetType[e.type];
            if (e.mimeType) {
                if ("model/gltf+json" === e.mimeType || "model/gltf-binary" === e.mimeType) return s.EAssetType.Model;
                if ("image/jpeg" === e.mimeType || "image/png" === e.mimeType) return s.EAssetType.Image
            }
            const t = e.uri.split(".").pop().toLowerCase();
            if ("gltf" === t || "glb" === t) return s.EAssetType.Model;
            if ("obj" === t || "ply" === t) return s.EAssetType.Geometry;
            if ("jpg" === t || "png" === t) return s.EAssetType.Image;
            throw new Error(`failed to determine asset type from asset: ${e.uri}`)
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0);
    n(188), n(189);
    const i = r.GLTFLoader,
        a = r.DRACOLoader;
    a.setDecoderPath("/lib/javascripts/voyager/js/draco/");
    const o = n(9);
    class s {
        constructor(e) {
            this.loadingManager = e, this.gltfLoader = new i(e), this.gltfLoader.setDRACOLoader(new a)
        }
        canLoad(e) {
            const t = e.split(".").pop().toLowerCase();
            return s.extensions.indexOf(t) >= 0
        }
        canLoadMimeType(e) {
            return s.mimeTypes.indexOf(e) >= 0
        }
        load(e) {
            return new Promise((t, n) => {
                this.gltfLoader.load(e, e => {
                    t(this.createModelGroup(e))
                }, null, t => {
                    console.error(`failed to load '${e}': ${t}`), n(new Error(t))
                })
            })
        }
        createModelGroup(e) {
            const t = e.scene;
            if ("Scene" !== t.type) throw new Error("not a valid gltf scene");
            const n = new r.Group;
            return t.children.forEach(e => n.add(e)), n.traverse(e => {
                if ("Mesh" === e.type) {
                    const t = e,
                        n = t.material;
                    n.map && (n.map.encoding = r.LinearEncoding), t.geometry.computeBoundingBox();
                    const i = new o.default;
                    "MeshStandardMaterial" === n.type && i.copyStandardMaterial(n), i.roughness = .6, i.metalness = 0, i.setNormalMapObjectSpace(!1), i.map || i.color.set("#c0c0c0"), t.material = i
                }
            }), n
        }
    }
    s.extensions = ["gltf", "glb"], s.mimeTypes = ["model/gltf+json", "model/gltf-binary"], t.default = s
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0);
    n(190);
    const i = r.OBJLoader;
    n(191);
    const a = r.PLYLoader;
    class o {
        constructor(e) {
            this.objLoader = new i(e), this.plyLoader = new a(e)
        }
        canLoad(e) {
            const t = e.split(".").pop().toLowerCase();
            return o.extensions.indexOf(t) >= 0
        }
        load(e) {
            const t = e.split(".").pop().toLowerCase();
            return new Promise((n, r) => {
                if ("obj" === t) this.objLoader.load(e, t => {
                    const i = t.children[0].geometry;
                    return console.log(i), i && "Geometry" === i.type || "BufferGeometry" === i.type ? n(i) : r(new Error(`Can't parse geometry from '${e}'`))
                });
                else {
                    if ("ply" !== t) throw new Error(`Can't load geometry, unknown extension: '${t}' in '${e}'`);
                    this.plyLoader.load(e, t => t && "Geometry" === t.type || "BufferGeometry" === t.type ? n(t) : r(new Error(`Can't parse geometry from '${e}'`)))
                }
            })
        }
    }
    o.extensions = ["obj", "ply"], t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0);
    class i {
        constructor(e) {
            this.textureLoader = new r.TextureLoader(e)
        }
        canLoad(e) {
            const t = e.split(".").pop().toLowerCase();
            return i.extensions.indexOf(t) >= 0
        }
        canLoadMimeType(e) {
            return i.mimeTypes.indexOf(e) >= 0
        }
        load(e) {
            return new Promise((t, n) => {
                this.textureLoader.load(e, e => {
                    t(e)
                }, null, e => {
                    console.error(e), n(new Error(e.message))
                })
            })
        }
        loadImmediate(e) {
            return this.textureLoader.load(e, null, null, e => {
                console.error(e)
            })
        }
    }
    i.extensions = ["jpg", "png"], i.mimeTypes = ["image/jpeg", "image/png"], t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(215),
        i = n(249),
        a = n(250),
        o = n(251),
        s = n(252),
        l = n(253),
        c = n(254),
        d = n(255),
        u = n(256),
        h = n(257),
        p = n(258);
    t.default = class {
        constructor() {
            this._schemaValidator = new r({
                schemas: [i, a, o, s, l, c, d, p, u, h],
                allErrors: !0
            }), this._validatePresentation = this._schemaValidator.getSchema("https://schemas.3d.si.edu/public_api/presentation.schema.json"), this._validateItem = this._schemaValidator.getSchema("https://schemas.3d.si.edu/public_api/item.schema.json")
        }
        validatePresentation(e) {
            return !!this._validatePresentation(e) || (console.warn(this._schemaValidator.errorsText(this._validatePresentation.errors, {
                separator: ", ",
                dataVar: "presentation"
            })), !1)
        }
        validateItem(e) {
            return !!this._validateItem(e) || (console.warn(this._schemaValidator.errorsText(this._validateItem.errors, {
                separator: ", ",
                dataVar: "item"
            })), !1)
        }
    }
}, function(e, t, n) {
    "use strict";
    var r = n(216),
        i = n(29),
        a = n(220),
        o = n(166),
        s = n(167),
        l = n(221),
        c = n(222),
        d = n(243),
        u = n(7);
    e.exports = g, g.prototype.validate = function(e, t) {
        var n;
        if ("string" == typeof e) {
            if (!(n = this.getSchema(e))) throw new Error('no schema with key or ref "' + e + '"')
        } else {
            var r = this._addSchema(e);
            n = r.validate || this._compile(r)
        }
        var i = n(t);
        !0 !== n.$async && (this.errors = n.errors);
        return i
    }, g.prototype.compile = function(e, t) {
        var n = this._addSchema(e, void 0, t);
        return n.validate || this._compile(n)
    }, g.prototype.addSchema = function(e, t, n, r) {
        if (Array.isArray(e)) {
            for (var a = 0; a < e.length; a++) this.addSchema(e[a], void 0, n, r);
            return this
        }
        var o = this._getId(e);
        if (void 0 !== o && "string" != typeof o) throw new Error("schema id must be string");
        return P(this, t = i.normalizeId(t || o)), this._schemas[t] = this._addSchema(e, n, r, !0), this
    }, g.prototype.addMetaSchema = function(e, t, n) {
        return this.addSchema(e, t, n, !0), this
    }, g.prototype.validateSchema = function(e, t) {
        var n = e.$schema;
        if (void 0 !== n && "string" != typeof n) throw new Error("$schema must be a string");
        if (!(n = n || this._opts.defaultMeta || function(e) {
                var t = e._opts.meta;
                return e._opts.defaultMeta = "object" == typeof t ? e._getId(t) || t : e.getSchema(f) ? f : void 0, e._opts.defaultMeta
            }(this))) return this.logger.warn("meta-schema not available"), this.errors = null, !0;
        var r, i = this._formats.uri;
        this._formats.uri = "function" == typeof i ? this._schemaUriFormatFunc : this._schemaUriFormat;
        try {
            r = this.validate(n, e)
        } finally {
            this._formats.uri = i
        }
        if (!r && t) {
            var a = "schema is invalid: " + this.errorsText();
            if ("log" != this._opts.validateSchema) throw new Error(a);
            this.logger.error(a)
        }
        return r
    }, g.prototype.getSchema = function(e) {
        var t = y(this, e);
        switch (typeof t) {
            case "object":
                return t.validate || this._compile(t);
            case "string":
                return this.getSchema(t);
            case "undefined":
                return function(e, t) {
                    var n = i.schema.call(e, {
                        schema: {}
                    }, t);
                    if (n) {
                        var a = n.schema,
                            s = n.root,
                            l = n.baseId,
                            c = r.call(e, a, s, void 0, l);
                        return e._fragments[t] = new o({
                            ref: t,
                            fragment: !0,
                            schema: a,
                            root: s,
                            baseId: l,
                            validate: c
                        }), c
                    }
                }(this, e)
        }
    }, g.prototype.removeSchema = function(e) {
        if (e instanceof RegExp) return _(this, this._schemas, e), _(this, this._refs, e), this;
        switch (typeof e) {
            case "undefined":
                return _(this, this._schemas), _(this, this._refs), this._cache.clear(), this;
            case "string":
                var t = y(this, e);
                return t && this._cache.del(t.cacheKey), delete this._schemas[e], delete this._refs[e], this;
            case "object":
                var n = this._opts.serialize,
                    r = n ? n(e) : e;
                this._cache.del(r);
                var a = this._getId(e);
                a && (a = i.normalizeId(a), delete this._schemas[a], delete this._refs[a])
        }
        return this
    }, g.prototype.addFormat = function(e, t) {
        "string" == typeof t && (t = new RegExp(t));
        return this._formats[e] = t, this
    }, g.prototype.errorsText = function(e, t) {
        if (!(e = e || this.errors)) return "No errors";
        for (var n = void 0 === (t = t || {}).separator ? ", " : t.separator, r = void 0 === t.dataVar ? "data" : t.dataVar, i = "", a = 0; a < e.length; a++) {
            var o = e[a];
            o && (i += r + o.dataPath + " " + o.message + n)
        }
        return i.slice(0, -n.length)
    }, g.prototype._addSchema = function(e, t, n, r) {
        if ("object" != typeof e && "boolean" != typeof e) throw new Error("schema should be object or boolean");
        var a = this._opts.serialize,
            s = a ? a(e) : e,
            l = this._cache.get(s);
        if (l) return l;
        r = r || !1 !== this._opts.addUsedSchema;
        var c = i.normalizeId(this._getId(e));
        c && r && P(this, c);
        var d, u = !1 !== this._opts.validateSchema && !t;
        u && !(d = c && c == i.normalizeId(e.$schema)) && this.validateSchema(e, !0);
        var h = i.ids.call(this, e),
            p = new o({
                id: c,
                schema: e,
                localRefs: h,
                cacheKey: s,
                meta: n
            });
        "#" != c[0] && r && (this._refs[c] = p);
        this._cache.put(s, p), u && d && this.validateSchema(e, !0);
        return p
    }, g.prototype._compile = function(e, t) {
        if (e.compiling) return e.validate = a, a.schema = e.schema, a.errors = null, a.root = t || a, !0 === e.schema.$async && (a.$async = !0), a;
        var n, i;
        e.compiling = !0, e.meta && (n = this._opts, this._opts = this._metaOpts);
        try {
            i = r.call(this, e.schema, t, e.localRefs)
        } catch (t) {
            throw delete e.validate, t
        } finally {
            e.compiling = !1, e.meta && (this._opts = n)
        }
        return e.validate = i, e.refs = i.refs, e.refVal = i.refVal, e.root = i.root, i;

        function a() {
            var t = e.validate,
                n = t.apply(this, arguments);
            return a.errors = t.errors, n
        }
    }, g.prototype.compileAsync = n(244);
    var h = n(245);
    g.prototype.addKeyword = h.add, g.prototype.getKeyword = h.get, g.prototype.removeKeyword = h.remove;
    var p = n(31);
    g.ValidationError = p.Validation, g.MissingRefError = p.MissingRef, g.$dataMetaSchema = d;
    var f = "http://json-schema.org/draft-07/schema",
        m = ["removeAdditional", "useDefaults", "coerceTypes"],
        v = ["/properties"];

    function g(e) {
        if (!(this instanceof g)) return new g(e);
        e = this._opts = u.copy(e) || {},
            function(e) {
                var t = e._opts.logger;
                if (!1 === t) e.logger = {
                    log: w,
                    warn: w,
                    error: w
                };
                else {
                    if (void 0 === t && (t = console), !("object" == typeof t && t.log && t.warn && t.error)) throw new Error("logger must implement log, warn and error methods");
                    e.logger = t
                }
            }(this), this._schemas = {}, this._refs = {}, this._fragments = {}, this._formats = l(e.format);
        var t = this._schemaUriFormat = this._formats["uri-reference"];
        this._schemaUriFormatFunc = function(e) {
                return t.test(e)
            }, this._cache = e.cache || new a, this._loadingSchemas = {}, this._compilations = [], this.RULES = c(), this._getId = function(e) {
                switch (e.schemaId) {
                    case "auto":
                        return b;
                    case "id":
                        return x;
                    default:
                        return E
                }
            }(e), e.loopRequired = e.loopRequired || 1 / 0, "property" == e.errorDataPath && (e._errorDataPathProperty = !0), void 0 === e.serialize && (e.serialize = s), this._metaOpts = function(e) {
                for (var t = u.copy(e._opts), n = 0; n < m.length; n++) delete t[m[n]];
                return t
            }(this), e.formats && function(e) {
                for (var t in e._opts.formats) {
                    var n = e._opts.formats[t];
                    e.addFormat(t, n)
                }
            }(this),
            function(e) {
                var t;
                e._opts.$data && (t = n(247), e.addMetaSchema(t, t.$id, !0));
                if (!1 === e._opts.meta) return;
                var r = n(248);
                e._opts.$data && (r = d(r, v));
                e.addMetaSchema(r, f, !0), e._refs["http://json-schema.org/schema"] = f
            }(this), "object" == typeof e.meta && this.addMetaSchema(e.meta),
            function(e) {
                var t = e._opts.schemas;
                if (!t) return;
                if (Array.isArray(t)) e.addSchema(t);
                else
                    for (var n in t) e.addSchema(t[n], n)
            }(this)
    }

    function y(e, t) {
        return t = i.normalizeId(t), e._schemas[t] || e._refs[t] || e._fragments[t]
    }

    function _(e, t, n) {
        for (var r in t) {
            var i = t[r];
            i.meta || n && !n.test(r) || (e._cache.del(i.cacheKey), delete t[r])
        }
    }

    function x(e) {
        return e.$id && this.logger.warn("schema $id ignored", e.$id), e.id
    }

    function E(e) {
        return e.id && this.logger.warn("schema id ignored", e.id), e.$id
    }

    function b(e) {
        if (e.$id && e.id && e.$id != e.id) throw new Error("schema $id is different from id");
        return e.$id || e.id
    }

    function P(e, t) {
        if (e._schemas[t] || e._refs[t]) throw new Error('schema with key or id "' + t + '" already exists')
    }

    function w() {}
}, function(e, t, n) {
    "use strict";
    var r = n(29),
        i = n(7),
        a = n(31),
        o = n(167),
        s = n(168),
        l = i.ucs2length,
        c = n(30),
        d = a.Validation;

    function u(e, t, n) {
        for (var r = 0; r < this._compilations.length; r++) {
            var i = this._compilations[r];
            if (i.schema == e && i.root == t && i.baseId == n) return r
        }
        return -1
    }

    function h(e, t) {
        return "var pattern" + e + " = new RegExp(" + i.toQuotedString(t[e]) + ");"
    }

    function p(e) {
        return "var default" + e + " = defaults[" + e + "];"
    }

    function f(e, t) {
        return void 0 === t[e] ? "" : "var refVal" + e + " = refVal[" + e + "];"
    }

    function m(e) {
        return "var customRule" + e + " = customRules[" + e + "];"
    }

    function v(e, t) {
        if (!e.length) return "";
        for (var n = "", r = 0; r < e.length; r++) n += t(r, e);
        return n
    }
    e.exports = function e(t, n, g, y) {
        var _ = this,
            x = this._opts,
            E = [void 0],
            b = {},
            P = [],
            w = {},
            S = [],
            M = {},
            L = [];
        n = n || {
            schema: t,
            refVal: E,
            refs: b
        };
        var C = function(e, t, n) {
            var r = u.call(this, e, t, n);
            return r >= 0 ? {
                index: r,
                compiling: !0
            } : (r = this._compilations.length, this._compilations[r] = {
                schema: e,
                root: t,
                baseId: n
            }, {
                index: r,
                compiling: !1
            })
        }.call(this, t, n, y);
        var T = this._compilations[C.index];
        if (C.compiling) return T.callValidate = function e() {
            var t = T.validate;
            var n = t.apply(this, arguments);
            e.errors = t.errors;
            return n
        };
        var D = this._formats;
        var R = this.RULES;
        try {
            var A = O(t, n, g, y);
            T.validate = A;
            var I = T.callValidate;
            return I && (I.schema = A.schema, I.errors = null, I.refs = A.refs, I.refVal = A.refVal, I.root = A.root, I.$async = A.$async, x.sourceCode && (I.source = A.source)), A
        } finally {
            (function(e, t, n) {
                var r = u.call(this, e, t, n);
                r >= 0 && this._compilations.splice(r, 1)
            }).call(this, t, n, y)
        }

        function O(t, o, u, g) {
            var y = !o || o && o.schema == t;
            if (o.schema != n.schema) return e.call(_, t, o, u, g);
            var w, M = !0 === t.$async,
                C = s({
                    isTop: !0,
                    schema: t,
                    isRoot: y,
                    baseId: g,
                    root: o,
                    schemaPath: "",
                    errSchemaPath: "#",
                    errorPath: '""',
                    MissingRefError: a.MissingRef,
                    RULES: R,
                    validate: s,
                    util: i,
                    resolve: r,
                    resolveRef: U,
                    usePattern: z,
                    useDefault: H,
                    useCustomRule: j,
                    opts: x,
                    formats: D,
                    logger: _.logger,
                    self: _
                });
            C = v(E, f) + v(P, h) + v(S, p) + v(L, m) + C, x.processCode && (C = x.processCode(C));
            try {
                var T = new Function("self", "RULES", "formats", "root", "refVal", "defaults", "customRules", "equal", "ucs2length", "ValidationError", C);
                w = T(_, R, D, n, E, S, L, c, l, d), E[0] = w
            } catch (e) {
                throw _.logger.error("Error compiling schema, function code:", C), e
            }
            return w.schema = t, w.errors = null, w.refs = b, w.refVal = E, w.root = y ? w : o, M && (w.$async = !0), !0 === x.sourceCode && (w.source = {
                code: C,
                patterns: P,
                defaults: S
            }), w
        }

        function U(t, i, a) {
            i = r.url(t, i);
            var o, s, l = b[i];
            if (void 0 !== l) return F(o = E[l], s = "refVal[" + l + "]");
            if (!a && n.refs) {
                var c = n.refs[i];
                if (void 0 !== c) return o = n.refVal[c], s = N(i, o), F(o, s)
            }
            s = N(i);
            var d = r.call(_, O, n, i);
            if (void 0 === d) {
                var u = g && g[i];
                u && (d = r.inlineRef(u, x.inlineRefs) ? u : e.call(_, u, n, g, t))
            }
            if (void 0 !== d) return function(e, t) {
                var n = b[e];
                E[n] = t
            }(i, d), F(d, s);
            ! function(e) {
                delete b[e]
            }(i)
        }

        function N(e, t) {
            var n = E.length;
            return E[n] = t, b[e] = n, "refVal" + n
        }

        function F(e, t) {
            return "object" == typeof e || "boolean" == typeof e ? {
                code: t,
                schema: e,
                inline: !0
            } : {
                code: t,
                $async: e && !!e.$async
            }
        }

        function z(e) {
            var t = w[e];
            return void 0 === t && (t = w[e] = P.length, P[t] = e), "pattern" + t
        }

        function H(e) {
            switch (typeof e) {
                case "boolean":
                case "number":
                    return "" + e;
                case "string":
                    return i.toQuotedString(e);
                case "object":
                    if (null === e) return "null";
                    var t = o(e),
                        n = M[t];
                    return void 0 === n && (n = M[t] = S.length, S[n] = e), "default" + n
            }
        }

        function j(e, t, n, r) {
            var i = e.definition.validateSchema;
            if (i && !1 !== _._opts.validateSchema) {
                var a = i(t);
                if (!a) {
                    var o = "keyword schema is invalid: " + _.errorsText(i.errors);
                    if ("log" != _._opts.validateSchema) throw new Error(o);
                    _.logger.error(o)
                }
            }
            var s, l = e.definition.compile,
                c = e.definition.inline,
                d = e.definition.macro;
            if (l) s = l.call(_, t, n, r);
            else if (d) s = d.call(_, t, n, r), !1 !== x.validateSchema && _.validateSchema(s, !0);
            else if (c) s = c.call(_, r, e.keyword, t, n);
            else if (!(s = e.definition.validate)) return;
            if (void 0 === s) throw new Error('custom keyword "' + e.keyword + '"failed to compile');
            var u = L.length;
            return L[u] = s, {
                code: "customRule" + u,
                validate: s
            }
        }
    }
}, function(e, t, n) {
    /** @license URI.js v4.2.1 (c) 2011 Gary Court. License: http://github.com/garycourt/uri-js */
    ! function(e) {
        "use strict";

        function t() {
            for (var e = arguments.length, t = Array(e), n = 0; n < e; n++) t[n] = arguments[n];
            if (t.length > 1) {
                t[0] = t[0].slice(0, -1);
                for (var r = t.length - 1, i = 1; i < r; ++i) t[i] = t[i].slice(1, -1);
                return t[r] = t[r].slice(1), t.join("")
            }
            return t[0]
        }

        function n(e) {
            return "(?:" + e + ")"
        }

        function r(e) {
            return void 0 === e ? "undefined" : null === e ? "null" : Object.prototype.toString.call(e).split(" ").pop().split("]").shift().toLowerCase()
        }

        function i(e) {
            return e.toUpperCase()
        }

        function a(e) {
            var r = t("[0-9]", "[A-Fa-f]"),
                i = n(n("%[EFef]" + r + "%" + r + r + "%" + r + r) + "|" + n("%[89A-Fa-f]" + r + "%" + r + r) + "|" + n("%" + r + r)),
                a = "[\\!\\$\\&\\'\\(\\)\\*\\+\\,\\;\\=]",
                o = t("[\\:\\/\\?\\#\\[\\]\\@]", a),
                s = e ? "[\\xA0-\\u200D\\u2010-\\u2029\\u202F-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFEF]" : "[]",
                l = e ? "[\\uE000-\\uF8FF]" : "[]",
                c = t("[A-Za-z]", "[0-9]", "[\\-\\.\\_\\~]", s),
                d = n("[A-Za-z]" + t("[A-Za-z]", "[0-9]", "[\\+\\-\\.]") + "*"),
                u = n(n(i + "|" + t(c, a, "[\\:]")) + "*"),
                h = (n(n("25[0-5]") + "|" + n("2[0-4][0-9]") + "|" + n("1[0-9][0-9]") + "|" + n("[1-9][0-9]") + "|[0-9]"), n(n("25[0-5]") + "|" + n("2[0-4][0-9]") + "|" + n("1[0-9][0-9]") + "|" + n("0?[1-9][0-9]") + "|0?0?[0-9]")),
                p = n(h + "\\." + h + "\\." + h + "\\." + h),
                f = n(r + "{1,4}"),
                m = n(n(f + "\\:" + f) + "|" + p),
                v = n(n(f + "\\:") + "{6}" + m),
                g = n("\\:\\:" + n(f + "\\:") + "{5}" + m),
                y = n(n(f) + "?\\:\\:" + n(f + "\\:") + "{4}" + m),
                _ = n(n(n(f + "\\:") + "{0,1}" + f) + "?\\:\\:" + n(f + "\\:") + "{3}" + m),
                x = n(n(n(f + "\\:") + "{0,2}" + f) + "?\\:\\:" + n(f + "\\:") + "{2}" + m),
                E = n(n(n(f + "\\:") + "{0,3}" + f) + "?\\:\\:" + f + "\\:" + m),
                b = n(n(n(f + "\\:") + "{0,4}" + f) + "?\\:\\:" + m),
                P = n(n(n(f + "\\:") + "{0,5}" + f) + "?\\:\\:" + f),
                w = n(n(n(f + "\\:") + "{0,6}" + f) + "?\\:\\:"),
                S = n([v, g, y, _, x, E, b, P, w].join("|")),
                M = n(n(c + "|" + i) + "+"),
                L = (n(S + "\\%25" + M), n(S + n("\\%25|\\%(?!" + r + "{2})") + M)),
                C = n("[vV]" + r + "+\\." + t(c, a, "[\\:]") + "+"),
                T = n("\\[" + n(L + "|" + S + "|" + C) + "\\]"),
                D = n(n(i + "|" + t(c, a)) + "*"),
                R = n(T + "|" + p + "(?!" + D + ")|" + D),
                A = n("[0-9]*"),
                I = n(n(u + "@") + "?" + R + n("\\:" + A) + "?"),
                O = n(i + "|" + t(c, a, "[\\:\\@]")),
                U = n(O + "*"),
                N = n(O + "+"),
                F = n(n(i + "|" + t(c, a, "[\\@]")) + "+"),
                z = n(n("\\/" + U) + "*"),
                H = n("\\/" + n(N + z) + "?"),
                j = n(F + z),
                V = n(N + z),
                k = "(?!" + O + ")",
                B = (n(z + "|" + H + "|" + j + "|" + V + "|" + k), n(n(O + "|" + t("[\\/\\?]", l)) + "*")),
                G = n(n(O + "|[\\/\\?]") + "*"),
                $ = n(n("\\/\\/" + I + z) + "|" + H + "|" + V + "|" + k),
                q = n(d + "\\:" + $ + n("\\?" + B) + "?" + n("\\#" + G) + "?"),
                X = n(n("\\/\\/" + I + z) + "|" + H + "|" + j + "|" + k),
                Y = n(X + n("\\?" + B) + "?" + n("\\#" + G) + "?");
            return n(q + "|" + Y), n(d + "\\:" + $ + n("\\?" + B) + "?"), n(n("\\/\\/(" + n("(" + u + ")@") + "?(" + R + ")" + n("\\:(" + A + ")") + "?)") + "?(" + z + "|" + H + "|" + V + "|" + k + ")"), n("\\?(" + B + ")"), n("\\#(" + G + ")"), n(n("\\/\\/(" + n("(" + u + ")@") + "?(" + R + ")" + n("\\:(" + A + ")") + "?)") + "?(" + z + "|" + H + "|" + j + "|" + k + ")"), n("\\?(" + B + ")"), n("\\#(" + G + ")"), n(n("\\/\\/(" + n("(" + u + ")@") + "?(" + R + ")" + n("\\:(" + A + ")") + "?)") + "?(" + z + "|" + H + "|" + V + "|" + k + ")"), n("\\?(" + B + ")"), n("\\#(" + G + ")"), n("(" + u + ")@"), n("\\:(" + A + ")"), {
                NOT_SCHEME: new RegExp(t("[^]", "[A-Za-z]", "[0-9]", "[\\+\\-\\.]"), "g"),
                NOT_USERINFO: new RegExp(t("[^\\%\\:]", c, a), "g"),
                NOT_HOST: new RegExp(t("[^\\%\\[\\]\\:]", c, a), "g"),
                NOT_PATH: new RegExp(t("[^\\%\\/\\:\\@]", c, a), "g"),
                NOT_PATH_NOSCHEME: new RegExp(t("[^\\%\\/\\@]", c, a), "g"),
                NOT_QUERY: new RegExp(t("[^\\%]", c, a, "[\\:\\@\\/\\?]", l), "g"),
                NOT_FRAGMENT: new RegExp(t("[^\\%]", c, a, "[\\:\\@\\/\\?]"), "g"),
                ESCAPE: new RegExp(t("[^]", c, a), "g"),
                UNRESERVED: new RegExp(c, "g"),
                OTHER_CHARS: new RegExp(t("[^\\%]", c, o), "g"),
                PCT_ENCODED: new RegExp(i, "g"),
                IPV4ADDRESS: new RegExp("^(" + p + ")$"),
                IPV6ADDRESS: new RegExp("^\\[?(" + S + ")" + n(n("\\%25|\\%(?!" + r + "{2})") + "(" + M + ")") + "?\\]?$")
            }
        }
        var o = a(!1),
            s = a(!0),
            l = function(e, t) {
                if (Array.isArray(e)) return e;
                if (Symbol.iterator in Object(e)) return function(e, t) {
                    var n = [],
                        r = !0,
                        i = !1,
                        a = void 0;
                    try {
                        for (var o, s = e[Symbol.iterator](); !(r = (o = s.next()).done) && (n.push(o.value), !t || n.length !== t); r = !0);
                    } catch (e) {
                        i = !0, a = e
                    } finally {
                        try {
                            !r && s.return && s.return()
                        } finally {
                            if (i) throw a
                        }
                    }
                    return n
                }(e, t);
                throw new TypeError("Invalid attempt to destructure non-iterable instance")
            },
            c = 2147483647,
            d = /^xn--/,
            u = /[^\0-\x7E]/,
            h = /[\x2E\u3002\uFF0E\uFF61]/g,
            p = {
                overflow: "Overflow: input needs wider integers to process",
                "not-basic": "Illegal input >= 0x80 (not a basic code point)",
                "invalid-input": "Invalid input"
            },
            f = Math.floor,
            m = String.fromCharCode;

        function v(e) {
            throw new RangeError(p[e])
        }

        function g(e, t) {
            var n = e.split("@"),
                r = "";
            n.length > 1 && (r = n[0] + "@", e = n[1]);
            var i = (e = e.replace(h, ".")).split("."),
                a = function(e, t) {
                    for (var n = [], r = e.length; r--;) n[r] = t(e[r]);
                    return n
                }(i, t).join(".");
            return r + a
        }

        function y(e) {
            for (var t = [], n = 0, r = e.length; n < r;) {
                var i = e.charCodeAt(n++);
                if (i >= 55296 && i <= 56319 && n < r) {
                    var a = e.charCodeAt(n++);
                    56320 == (64512 & a) ? t.push(((1023 & i) << 10) + (1023 & a) + 65536) : (t.push(i), n--)
                } else t.push(i)
            }
            return t
        }
        var _ = function(e) {
                return e - 48 < 10 ? e - 22 : e - 65 < 26 ? e - 65 : e - 97 < 26 ? e - 97 : 36
            },
            x = function(e, t) {
                return e + 22 + 75 * (e < 26) - ((0 != t) << 5)
            },
            E = function(e, t, n) {
                var r = 0;
                for (e = n ? f(e / 700) : e >> 1, e += f(e / t); e > 455; r += 36) e = f(e / 35);
                return f(r + 36 * e / (e + 38))
            },
            b = function(e) {
                var t = [],
                    n = e.length,
                    r = 0,
                    i = 128,
                    a = 72,
                    o = e.lastIndexOf("-");
                o < 0 && (o = 0);
                for (var s = 0; s < o; ++s) e.charCodeAt(s) >= 128 && v("not-basic"), t.push(e.charCodeAt(s));
                for (var l = o > 0 ? o + 1 : 0; l < n;) {
                    for (var d = r, u = 1, h = 36;; h += 36) {
                        l >= n && v("invalid-input");
                        var p = _(e.charCodeAt(l++));
                        (p >= 36 || p > f((c - r) / u)) && v("overflow"), r += p * u;
                        var m = h <= a ? 1 : h >= a + 26 ? 26 : h - a;
                        if (p < m) break;
                        var g = 36 - m;
                        u > f(c / g) && v("overflow"), u *= g
                    }
                    var y = t.length + 1;
                    a = E(r - d, y, 0 == d), f(r / y) > c - i && v("overflow"), i += f(r / y), r %= y, t.splice(r++, 0, i)
                }
                return String.fromCodePoint.apply(String, t)
            },
            P = function(e) {
                var t = [],
                    n = (e = y(e)).length,
                    r = 128,
                    i = 0,
                    a = 72,
                    o = !0,
                    s = !1,
                    l = void 0;
                try {
                    for (var d, u = e[Symbol.iterator](); !(o = (d = u.next()).done); o = !0) {
                        var h = d.value;
                        h < 128 && t.push(m(h))
                    }
                } catch (e) {
                    s = !0, l = e
                } finally {
                    try {
                        !o && u.return && u.return()
                    } finally {
                        if (s) throw l
                    }
                }
                var p = t.length,
                    g = p;
                for (p && t.push("-"); g < n;) {
                    var _ = c,
                        b = !0,
                        P = !1,
                        w = void 0;
                    try {
                        for (var S, M = e[Symbol.iterator](); !(b = (S = M.next()).done); b = !0) {
                            var L = S.value;
                            L >= r && L < _ && (_ = L)
                        }
                    } catch (e) {
                        P = !0, w = e
                    } finally {
                        try {
                            !b && M.return && M.return()
                        } finally {
                            if (P) throw w
                        }
                    }
                    var C = g + 1;
                    _ - r > f((c - i) / C) && v("overflow"), i += (_ - r) * C, r = _;
                    var T = !0,
                        D = !1,
                        R = void 0;
                    try {
                        for (var A, I = e[Symbol.iterator](); !(T = (A = I.next()).done); T = !0) {
                            var O = A.value;
                            if (O < r && ++i > c && v("overflow"), O == r) {
                                for (var U = i, N = 36;; N += 36) {
                                    var F = N <= a ? 1 : N >= a + 26 ? 26 : N - a;
                                    if (U < F) break;
                                    var z = U - F,
                                        H = 36 - F;
                                    t.push(m(x(F + z % H, 0))), U = f(z / H)
                                }
                                t.push(m(x(U, 0))), a = E(i, C, g == p), i = 0, ++g
                            }
                        }
                    } catch (e) {
                        D = !0, R = e
                    } finally {
                        try {
                            !T && I.return && I.return()
                        } finally {
                            if (D) throw R
                        }
                    }++i, ++r
                }
                return t.join("")
            },
            w = {
                version: "2.1.0",
                ucs2: {
                    decode: y,
                    encode: function(e) {
                        return String.fromCodePoint.apply(String, function(e) {
                            if (Array.isArray(e)) {
                                for (var t = 0, n = Array(e.length); t < e.length; t++) n[t] = e[t];
                                return n
                            }
                            return Array.from(e)
                        }(e))
                    }
                },
                decode: b,
                encode: P,
                toASCII: function(e) {
                    return g(e, function(e) {
                        return u.test(e) ? "xn--" + P(e) : e
                    })
                },
                toUnicode: function(e) {
                    return g(e, function(e) {
                        return d.test(e) ? b(e.slice(4).toLowerCase()) : e
                    })
                }
            },
            S = {};

        function M(e) {
            var t = e.charCodeAt(0);
            return t < 16 ? "%0" + t.toString(16).toUpperCase() : t < 128 ? "%" + t.toString(16).toUpperCase() : t < 2048 ? "%" + (t >> 6 | 192).toString(16).toUpperCase() + "%" + (63 & t | 128).toString(16).toUpperCase() : "%" + (t >> 12 | 224).toString(16).toUpperCase() + "%" + (t >> 6 & 63 | 128).toString(16).toUpperCase() + "%" + (63 & t | 128).toString(16).toUpperCase()
        }

        function L(e) {
            for (var t = "", n = 0, r = e.length; n < r;) {
                var i = parseInt(e.substr(n + 1, 2), 16);
                if (i < 128) t += String.fromCharCode(i), n += 3;
                else if (i >= 194 && i < 224) {
                    if (r - n >= 6) {
                        var a = parseInt(e.substr(n + 4, 2), 16);
                        t += String.fromCharCode((31 & i) << 6 | 63 & a)
                    } else t += e.substr(n, 6);
                    n += 6
                } else if (i >= 224) {
                    if (r - n >= 9) {
                        var o = parseInt(e.substr(n + 4, 2), 16),
                            s = parseInt(e.substr(n + 7, 2), 16);
                        t += String.fromCharCode((15 & i) << 12 | (63 & o) << 6 | 63 & s)
                    } else t += e.substr(n, 9);
                    n += 9
                } else t += e.substr(n, 3), n += 3
            }
            return t
        }

        function C(e, t) {
            function n(e) {
                var n = L(e);
                return n.match(t.UNRESERVED) ? n : e
            }
            return e.scheme && (e.scheme = String(e.scheme).replace(t.PCT_ENCODED, n).toLowerCase().replace(t.NOT_SCHEME, "")), void 0 !== e.userinfo && (e.userinfo = String(e.userinfo).replace(t.PCT_ENCODED, n).replace(t.NOT_USERINFO, M).replace(t.PCT_ENCODED, i)), void 0 !== e.host && (e.host = String(e.host).replace(t.PCT_ENCODED, n).toLowerCase().replace(t.NOT_HOST, M).replace(t.PCT_ENCODED, i)), void 0 !== e.path && (e.path = String(e.path).replace(t.PCT_ENCODED, n).replace(e.scheme ? t.NOT_PATH : t.NOT_PATH_NOSCHEME, M).replace(t.PCT_ENCODED, i)), void 0 !== e.query && (e.query = String(e.query).replace(t.PCT_ENCODED, n).replace(t.NOT_QUERY, M).replace(t.PCT_ENCODED, i)), void 0 !== e.fragment && (e.fragment = String(e.fragment).replace(t.PCT_ENCODED, n).replace(t.NOT_FRAGMENT, M).replace(t.PCT_ENCODED, i)), e
        }

        function T(e) {
            return e.replace(/^0*(.*)/, "$1") || "0"
        }

        function D(e, t) {
            var n = e.match(t.IPV4ADDRESS) || [],
                r = l(n, 2),
                i = r[1];
            return i ? i.split(".").map(T).join(".") : e
        }

        function R(e, t) {
            var n = e.match(t.IPV6ADDRESS) || [],
                r = l(n, 3),
                i = r[1],
                a = r[2];
            if (i) {
                for (var o = i.toLowerCase().split("::").reverse(), s = l(o, 2), c = s[0], d = s[1], u = d ? d.split(":").map(T) : [], h = c.split(":").map(T), p = t.IPV4ADDRESS.test(h[h.length - 1]), f = p ? 7 : 8, m = h.length - f, v = Array(f), g = 0; g < f; ++g) v[g] = u[g] || h[m + g] || "";
                p && (v[f - 1] = D(v[f - 1], t));
                var y = v.reduce(function(e, t, n) {
                        if (!t || "0" === t) {
                            var r = e[e.length - 1];
                            r && r.index + r.length === n ? r.length++ : e.push({
                                index: n,
                                length: 1
                            })
                        }
                        return e
                    }, []),
                    _ = y.sort(function(e, t) {
                        return t.length - e.length
                    })[0],
                    x = void 0;
                if (_ && _.length > 1) {
                    var E = v.slice(0, _.index),
                        b = v.slice(_.index + _.length);
                    x = E.join(":") + "::" + b.join(":")
                } else x = v.join(":");
                return a && (x += "%" + a), x
            }
            return e
        }
        var A = /^(?:([^:\/?#]+):)?(?:\/\/((?:([^\/?#@]*)@)?(\[[^\/?#\]]+\]|[^\/?#:]*)(?:\:(\d*))?))?([^?#]*)(?:\?([^#]*))?(?:#((?:.|\n|\r)*))?/i,
            I = void 0 === "".match(/(){0}/)[1];

        function O(e) {
            var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {},
                n = {},
                r = !1 !== t.iri ? s : o;
            "suffix" === t.reference && (e = (t.scheme ? t.scheme + ":" : "") + "//" + e);
            var i = e.match(A);
            if (i) {
                I ? (n.scheme = i[1], n.userinfo = i[3], n.host = i[4], n.port = parseInt(i[5], 10), n.path = i[6] || "", n.query = i[7], n.fragment = i[8], isNaN(n.port) && (n.port = i[5])) : (n.scheme = i[1] || void 0, n.userinfo = -1 !== e.indexOf("@") ? i[3] : void 0, n.host = -1 !== e.indexOf("//") ? i[4] : void 0, n.port = parseInt(i[5], 10), n.path = i[6] || "", n.query = -1 !== e.indexOf("?") ? i[7] : void 0, n.fragment = -1 !== e.indexOf("#") ? i[8] : void 0, isNaN(n.port) && (n.port = e.match(/\/\/(?:.|\n)*\:(?:\/|\?|\#|$)/) ? i[4] : void 0)), n.host && (n.host = R(D(n.host, r), r)), void 0 !== n.scheme || void 0 !== n.userinfo || void 0 !== n.host || void 0 !== n.port || n.path || void 0 !== n.query ? void 0 === n.scheme ? n.reference = "relative" : void 0 === n.fragment ? n.reference = "absolute" : n.reference = "uri" : n.reference = "same-document", t.reference && "suffix" !== t.reference && t.reference !== n.reference && (n.error = n.error || "URI is not a " + t.reference + " reference.");
                var a = S[(t.scheme || n.scheme || "").toLowerCase()];
                if (t.unicodeSupport || a && a.unicodeSupport) C(n, r);
                else {
                    if (n.host && (t.domainHost || a && a.domainHost)) try {
                        n.host = w.toASCII(n.host.replace(r.PCT_ENCODED, L).toLowerCase())
                    } catch (e) {
                        n.error = n.error || "Host's domain name can not be converted to ASCII via punycode: " + e
                    }
                    C(n, o)
                }
                a && a.parse && a.parse(n, t)
            } else n.error = n.error || "URI can not be parsed.";
            return n
        }
        var U = /^\.\.?\//,
            N = /^\/\.(\/|$)/,
            F = /^\/\.\.(\/|$)/,
            z = /^\/?(?:.|\n)*?(?=\/|$)/;

        function H(e) {
            for (var t = []; e.length;)
                if (e.match(U)) e = e.replace(U, "");
                else if (e.match(N)) e = e.replace(N, "/");
            else if (e.match(F)) e = e.replace(F, "/"), t.pop();
            else if ("." === e || ".." === e) e = "";
            else {
                var n = e.match(z);
                if (!n) throw new Error("Unexpected dot segment condition");
                var r = n[0];
                e = e.slice(r.length), t.push(r)
            }
            return t.join("")
        }

        function j(e) {
            var t = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {},
                n = t.iri ? s : o,
                r = [],
                i = S[(t.scheme || e.scheme || "").toLowerCase()];
            if (i && i.serialize && i.serialize(e, t), e.host)
                if (n.IPV6ADDRESS.test(e.host));
                else if (t.domainHost || i && i.domainHost) try {
                e.host = t.iri ? w.toUnicode(e.host) : w.toASCII(e.host.replace(n.PCT_ENCODED, L).toLowerCase())
            } catch (n) {
                e.error = e.error || "Host's domain name can not be converted to " + (t.iri ? "Unicode" : "ASCII") + " via punycode: " + n
            }
            C(e, n), "suffix" !== t.reference && e.scheme && (r.push(e.scheme), r.push(":"));
            var a = function(e, t) {
                var n = !1 !== t.iri ? s : o,
                    r = [];
                return void 0 !== e.userinfo && (r.push(e.userinfo), r.push("@")), void 0 !== e.host && r.push(R(D(String(e.host), n), n).replace(n.IPV6ADDRESS, function(e, t, n) {
                    return "[" + t + (n ? "%25" + n : "") + "]"
                })), "number" == typeof e.port && (r.push(":"), r.push(e.port.toString(10))), r.length ? r.join("") : void 0
            }(e, t);
            if (void 0 !== a && ("suffix" !== t.reference && r.push("//"), r.push(a), e.path && "/" !== e.path.charAt(0) && r.push("/")), void 0 !== e.path) {
                var l = e.path;
                t.absolutePath || i && i.absolutePath || (l = H(l)), void 0 === a && (l = l.replace(/^\/\//, "/%2F")), r.push(l)
            }
            return void 0 !== e.query && (r.push("?"), r.push(e.query)), void 0 !== e.fragment && (r.push("#"), r.push(e.fragment)), r.join("")
        }

        function V(e, t) {
            var n = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : {},
                r = arguments[3],
                i = {};
            return r || (e = O(j(e, n), n), t = O(j(t, n), n)), !(n = n || {}).tolerant && t.scheme ? (i.scheme = t.scheme, i.userinfo = t.userinfo, i.host = t.host, i.port = t.port, i.path = H(t.path || ""), i.query = t.query) : (void 0 !== t.userinfo || void 0 !== t.host || void 0 !== t.port ? (i.userinfo = t.userinfo, i.host = t.host, i.port = t.port, i.path = H(t.path || ""), i.query = t.query) : (t.path ? ("/" === t.path.charAt(0) ? i.path = H(t.path) : (void 0 === e.userinfo && void 0 === e.host && void 0 === e.port || e.path ? e.path ? i.path = e.path.slice(0, e.path.lastIndexOf("/") + 1) + t.path : i.path = t.path : i.path = "/" + t.path, i.path = H(i.path)), i.query = t.query) : (i.path = e.path, void 0 !== t.query ? i.query = t.query : i.query = e.query), i.userinfo = e.userinfo, i.host = e.host, i.port = e.port), i.scheme = e.scheme), i.fragment = t.fragment, i
        }

        function k(e, t) {
            return e && e.toString().replace(t && t.iri ? s.PCT_ENCODED : o.PCT_ENCODED, L)
        }
        var B = {
                scheme: "http",
                domainHost: !0,
                parse: function(e, t) {
                    return e.host || (e.error = e.error || "HTTP URIs must have a host."), e
                },
                serialize: function(e, t) {
                    return e.port !== ("https" !== String(e.scheme).toLowerCase() ? 80 : 443) && "" !== e.port || (e.port = void 0), e.path || (e.path = "/"), e
                }
            },
            G = {
                scheme: "https",
                domainHost: B.domainHost,
                parse: B.parse,
                serialize: B.serialize
            },
            $ = {},
            q = "[A-Za-z0-9\\-\\.\\_\\~\\xA0-\\u200D\\u2010-\\u2029\\u202F-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFEF]",
            X = "[0-9A-Fa-f]",
            Y = n(n("%[EFef][0-9A-Fa-f]%" + X + X + "%" + X + X) + "|" + n("%[89A-Fa-f][0-9A-Fa-f]%" + X + X) + "|" + n("%" + X + X)),
            W = t("[\\!\\$\\%\\'\\(\\)\\*\\+\\,\\-\\.0-9\\<\\>A-Z\\x5E-\\x7E]", '[\\"\\\\]'),
            Q = new RegExp(q, "g"),
            K = new RegExp(Y, "g"),
            Z = new RegExp(t("[^]", "[A-Za-z0-9\\!\\$\\%\\'\\*\\+\\-\\^\\_\\`\\{\\|\\}\\~]", "[\\.]", '[\\"]', W), "g"),
            J = new RegExp(t("[^]", q, "[\\!\\$\\'\\(\\)\\*\\+\\,\\;\\:\\@]"), "g"),
            ee = J;

        function te(e) {
            var t = L(e);
            return t.match(Q) ? t : e
        }
        var ne = {
                scheme: "mailto",
                parse: function(e, t) {
                    var n = e,
                        r = n.to = n.path ? n.path.split(",") : [];
                    if (n.path = void 0, n.query) {
                        for (var i = !1, a = {}, o = n.query.split("&"), s = 0, l = o.length; s < l; ++s) {
                            var c = o[s].split("=");
                            switch (c[0]) {
                                case "to":
                                    for (var d = c[1].split(","), u = 0, h = d.length; u < h; ++u) r.push(d[u]);
                                    break;
                                case "subject":
                                    n.subject = k(c[1], t);
                                    break;
                                case "body":
                                    n.body = k(c[1], t);
                                    break;
                                default:
                                    i = !0, a[k(c[0], t)] = k(c[1], t)
                            }
                        }
                        i && (n.headers = a)
                    }
                    n.query = void 0;
                    for (var p = 0, f = r.length; p < f; ++p) {
                        var m = r[p].split("@");
                        if (m[0] = k(m[0]), t.unicodeSupport) m[1] = k(m[1], t).toLowerCase();
                        else try {
                            m[1] = w.toASCII(k(m[1], t).toLowerCase())
                        } catch (e) {
                            n.error = n.error || "Email address's domain name can not be converted to ASCII via punycode: " + e
                        }
                        r[p] = m.join("@")
                    }
                    return n
                },
                serialize: function(e, t) {
                    var n = e,
                        r = function(e) {
                            return void 0 !== e && null !== e ? e instanceof Array ? e : "number" != typeof e.length || e.split || e.setInterval || e.call ? [e] : Array.prototype.slice.call(e) : []
                        }(e.to);
                    if (r) {
                        for (var a = 0, o = r.length; a < o; ++a) {
                            var s = String(r[a]),
                                l = s.lastIndexOf("@"),
                                c = s.slice(0, l).replace(K, te).replace(K, i).replace(Z, M),
                                d = s.slice(l + 1);
                            try {
                                d = t.iri ? w.toUnicode(d) : w.toASCII(k(d, t).toLowerCase())
                            } catch (e) {
                                n.error = n.error || "Email address's domain name can not be converted to " + (t.iri ? "Unicode" : "ASCII") + " via punycode: " + e
                            }
                            r[a] = c + "@" + d
                        }
                        n.path = r.join(",")
                    }
                    var u = e.headers = e.headers || {};
                    e.subject && (u.subject = e.subject), e.body && (u.body = e.body);
                    var h = [];
                    for (var p in u) u[p] !== $[p] && h.push(p.replace(K, te).replace(K, i).replace(J, M) + "=" + u[p].replace(K, te).replace(K, i).replace(ee, M));
                    return h.length && (n.query = h.join("&")), n
                }
            },
            re = /^([^\:]+)\:(.*)/,
            ie = {
                scheme: "urn",
                parse: function(e, t) {
                    var n = e.path && e.path.match(re),
                        r = e;
                    if (n) {
                        var i = t.scheme || r.scheme || "urn",
                            a = n[1].toLowerCase(),
                            o = n[2],
                            s = i + ":" + (t.nid || a),
                            l = S[s];
                        r.nid = a, r.nss = o, r.path = void 0, l && (r = l.parse(r, t))
                    } else r.error = r.error || "URN can not be parsed.";
                    return r
                },
                serialize: function(e, t) {
                    var n = t.scheme || e.scheme || "urn",
                        r = e.nid,
                        i = n + ":" + (t.nid || r),
                        a = S[i];
                    a && (e = a.serialize(e, t));
                    var o = e,
                        s = e.nss;
                    return o.path = (r || t.nid) + ":" + s, o
                }
            },
            ae = /^[0-9A-Fa-f]{8}(?:\-[0-9A-Fa-f]{4}){3}\-[0-9A-Fa-f]{12}$/,
            oe = {
                scheme: "urn:uuid",
                parse: function(e, t) {
                    var n = e;
                    return n.uuid = n.nss, n.nss = void 0, t.tolerant || n.uuid && n.uuid.match(ae) || (n.error = n.error || "UUID is not valid."), n
                },
                serialize: function(e, t) {
                    var n = e;
                    return n.nss = (e.uuid || "").toLowerCase(), n
                }
            };
        S[B.scheme] = B, S[G.scheme] = G, S[ne.scheme] = ne, S[ie.scheme] = ie, S[oe.scheme] = oe, e.SCHEMES = S, e.pctEncChar = M, e.pctDecChars = L, e.parse = O, e.removeDotSegments = H, e.serialize = j, e.resolveComponents = V, e.resolve = function(e, t, n) {
            var r = function(e, t) {
                var n = e;
                if (t)
                    for (var r in t) n[r] = t[r];
                return n
            }({
                scheme: "null"
            }, n);
            return j(V(O(e, r), O(t, r), r, !0), r)
        }, e.normalize = function(e, t) {
            return "string" == typeof e ? e = j(O(e, t), t) : "object" === r(e) && (e = O(j(e, t), t)), e
        }, e.equal = function(e, t, n) {
            return "string" == typeof e ? e = j(O(e, n), n) : "object" === r(e) && (e = j(e, n)), "string" == typeof t ? t = j(O(t, n), n) : "object" === r(t) && (t = j(t, n)), e === t
        }, e.escapeComponent = function(e, t) {
            return e && e.toString().replace(t && t.iri ? s.ESCAPE : o.ESCAPE, M)
        }, e.unescapeComponent = k, Object.defineProperty(e, "__esModule", {
            value: !0
        })
    }(t)
}, function(e, t, n) {
    "use strict";
    e.exports = function(e) {
        for (var t, n = 0, r = e.length, i = 0; i < r;) n++, (t = e.charCodeAt(i++)) >= 55296 && t <= 56319 && i < r && 56320 == (64512 & (t = e.charCodeAt(i))) && i++;
        return n
    }
}, function(e, t, n) {
    "use strict";
    var r = e.exports = function(e, t, n) {
        "function" == typeof t && (n = t, t = {}),
            function e(t, n, a, o, s, l, c, d, u, h) {
                if (o && "object" == typeof o && !Array.isArray(o)) {
                    for (var p in n(o, s, l, c, d, u, h), o) {
                        var f = o[p];
                        if (Array.isArray(f)) {
                            if (p in r.arrayKeywords)
                                for (var m = 0; m < f.length; m++) e(t, n, a, f[m], s + "/" + p + "/" + m, l, s, p, o, m)
                        } else if (p in r.propsKeywords) {
                            if (f && "object" == typeof f)
                                for (var v in f) e(t, n, a, f[v], s + "/" + p + "/" + i(v), l, s, p, o, v)
                        } else(p in r.keywords || t.allKeys && !(p in r.skipKeywords)) && e(t, n, a, f, s + "/" + p, l, s, p, o)
                    }
                    a(o, s, l, c, d, u, h)
                }
            }(t, "function" == typeof(n = t.cb || n) ? n : n.pre || function() {}, n.post || function() {}, e, "", e)
    };

    function i(e) {
        return e.replace(/~/g, "~0").replace(/\//g, "~1")
    }
    r.keywords = {
        additionalItems: !0,
        items: !0,
        contains: !0,
        additionalProperties: !0,
        propertyNames: !0,
        not: !0
    }, r.arrayKeywords = {
        items: !0,
        allOf: !0,
        anyOf: !0,
        oneOf: !0
    }, r.propsKeywords = {
        definitions: !0,
        properties: !0,
        patternProperties: !0,
        dependencies: !0
    }, r.skipKeywords = {
        default: !0,
        enum: !0,
        const: !0,
        required: !0,
        maximum: !0,
        minimum: !0,
        exclusiveMaximum: !0,
        exclusiveMinimum: !0,
        multipleOf: !0,
        maxLength: !0,
        minLength: !0,
        pattern: !0,
        format: !0,
        maxItems: !0,
        minItems: !0,
        uniqueItems: !0,
        maxProperties: !0,
        minProperties: !0
    }
}, function(e, t, n) {
    "use strict";
    var r = e.exports = function() {
        this._cache = {}
    };
    r.prototype.put = function(e, t) {
        this._cache[e] = t
    }, r.prototype.get = function(e) {
        return this._cache[e]
    }, r.prototype.del = function(e) {
        delete this._cache[e]
    }, r.prototype.clear = function() {
        this._cache = {}
    }
}, function(e, t, n) {
    "use strict";
    var r = n(7),
        i = /^(\d\d\d\d)-(\d\d)-(\d\d)$/,
        a = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
        o = /^(\d\d):(\d\d):(\d\d)(\.\d+)?(z|[+-]\d\d:\d\d)?$/i,
        s = /^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[-0-9a-z]{0,61}[0-9a-z])?)*$/i,
        l = /^(?:[a-z][a-z0-9+\-.]*:)(?:\/?\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:]|%[0-9a-f]{2})*@)?(?:\[(?:(?:(?:(?:[0-9a-f]{1,4}:){6}|::(?:[0-9a-f]{1,4}:){5}|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}|(?:(?:[0-9a-f]{1,4}:){0,1}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::)(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?))|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|[Vv][0-9a-f]+\.[a-z0-9\-._~!$&'()*+,;=:]+)\]|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)|(?:[a-z0-9\-._~!$&'()*+,;=]|%[0-9a-f]{2})*)(?::\d*)?(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*|\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*)?|(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'()*+,;=:@]|%[0-9a-f]{2})*)*)(?:\?(?:[a-z0-9\-._~!$&'()*+,;=:@/?]|%[0-9a-f]{2})*)?(?:#(?:[a-z0-9\-._~!$&'()*+,;=:@/?]|%[0-9a-f]{2})*)?$/i,
        c = /^(?:(?:[^\x00-\x20"'<>%\\^`{|}]|%[0-9a-f]{2})|\{[+#./;?&=,!@|]?(?:[a-z0-9_]|%[0-9a-f]{2})+(?::[1-9][0-9]{0,3}|\*)?(?:,(?:[a-z0-9_]|%[0-9a-f]{2})+(?::[1-9][0-9]{0,3}|\*)?)*\})*$/i,
        d = /^(?:(?:http[s\u017F]?|ftp):\/\/)(?:(?:[\0-\x08\x0E-\x1F!-\x9F\xA1-\u167F\u1681-\u1FFF\u200B-\u2027\u202A-\u202E\u2030-\u205E\u2060-\u2FFF\u3001-\uD7FF\uE000-\uFEFE\uFF00-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+(?::(?:[\0-\x08\x0E-\x1F!-\x9F\xA1-\u167F\u1681-\u1FFF\u200B-\u2027\u202A-\u202E\u2030-\u205E\u2060-\u2FFF\u3001-\uD7FF\uE000-\uFEFE\uFF00-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])*)?@)?(?:(?!10(?:\.[0-9]{1,3}){3})(?!127(?:\.[0-9]{1,3}){3})(?!169\.254(?:\.[0-9]{1,3}){2})(?!192\.168(?:\.[0-9]{1,3}){2})(?!172\.(?:1[6-9]|2[0-9]|3[01])(?:\.[0-9]{1,3}){2})(?:[1-9][0-9]?|1[0-9][0-9]|2[01][0-9]|22[0-3])(?:\.(?:1?[0-9]{1,2}|2[0-4][0-9]|25[0-5])){2}(?:\.(?:[1-9][0-9]?|1[0-9][0-9]|2[0-4][0-9]|25[0-4]))|(?:(?:(?:[0-9KSa-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+-?)*(?:[0-9KSa-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+)(?:\.(?:(?:[0-9KSa-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+-?)*(?:[0-9KSa-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])+)*(?:\.(?:(?:[KSa-z\xA1-\uD7FF\uE000-\uFFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF]){2,})))(?::[0-9]{2,5})?(?:\/(?:[\0-\x08\x0E-\x1F!-\x9F\xA1-\u167F\u1681-\u1FFF\u200B-\u2027\u202A-\u202E\u2030-\u205E\u2060-\u2FFF\u3001-\uD7FF\uE000-\uFEFE\uFF00-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDBFF](?![\uDC00-\uDFFF])|(?:[^\uD800-\uDBFF]|^)[\uDC00-\uDFFF])*)?$/i,
        u = /^(?:urn:uuid:)?[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}$/i,
        h = /^(?:\/(?:[^~/]|~0|~1)*)*$/,
        p = /^#(?:\/(?:[a-z0-9_\-.!$&'()*+,;:=@]|%[0-9a-f]{2}|~0|~1)*)*$/i,
        f = /^(?:0|[1-9][0-9]*)(?:#|(?:\/(?:[^~/]|~0|~1)*)*)$/;

    function m(e) {
        return e = "full" == e ? "full" : "fast", r.copy(m[e])
    }

    function v(e) {
        var t = e.match(i);
        if (!t) return !1;
        var n = +t[1],
            r = +t[2],
            o = +t[3];
        return r >= 1 && r <= 12 && o >= 1 && o <= (2 == r && function(e) {
            return e % 4 == 0 && (e % 100 != 0 || e % 400 == 0)
        }(n) ? 29 : a[r])
    }

    function g(e, t) {
        var n = e.match(o);
        if (!n) return !1;
        var r = n[1],
            i = n[2],
            a = n[3],
            s = n[5];
        return (r <= 23 && i <= 59 && a <= 59 || 23 == r && 59 == i && 60 == a) && (!t || s)
    }
    e.exports = m, m.fast = {
        date: /^\d\d\d\d-[0-1]\d-[0-3]\d$/,
        time: /^(?:[0-2]\d:[0-5]\d:[0-5]\d|23:59:60)(?:\.\d+)?(?:z|[+-]\d\d:\d\d)?$/i,
        "date-time": /^\d\d\d\d-[0-1]\d-[0-3]\d[t\s](?:[0-2]\d:[0-5]\d:[0-5]\d|23:59:60)(?:\.\d+)?(?:z|[+-]\d\d:\d\d)$/i,
        uri: /^(?:[a-z][a-z0-9+-.]*:)(?:\/?\/)?[^\s]*$/i,
        "uri-reference": /^(?:(?:[a-z][a-z0-9+-.]*:)?\/?\/)?(?:[^\\\s#][^\s#]*)?(?:#[^\\\s]*)?$/i,
        "uri-template": c,
        url: d,
        email: /^[a-z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/i,
        hostname: s,
        ipv4: /^(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)$/,
        ipv6: /^\s*(?:(?:(?:[0-9a-f]{1,4}:){7}(?:[0-9a-f]{1,4}|:))|(?:(?:[0-9a-f]{1,4}:){6}(?::[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){5}(?:(?:(?::[0-9a-f]{1,4}){1,2})|:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){4}(?:(?:(?::[0-9a-f]{1,4}){1,3})|(?:(?::[0-9a-f]{1,4})?:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){3}(?:(?:(?::[0-9a-f]{1,4}){1,4})|(?:(?::[0-9a-f]{1,4}){0,2}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){2}(?:(?:(?::[0-9a-f]{1,4}){1,5})|(?:(?::[0-9a-f]{1,4}){0,3}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){1}(?:(?:(?::[0-9a-f]{1,4}){1,6})|(?:(?::[0-9a-f]{1,4}){0,4}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?::(?:(?:(?::[0-9a-f]{1,4}){1,7})|(?:(?::[0-9a-f]{1,4}){0,5}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(?:%.+)?\s*$/i,
        regex: E,
        uuid: u,
        "json-pointer": h,
        "json-pointer-uri-fragment": p,
        "relative-json-pointer": f
    }, m.full = {
        date: v,
        time: g,
        "date-time": function(e) {
            var t = e.split(y);
            return 2 == t.length && v(t[0]) && g(t[1], !0)
        },
        uri: function(e) {
            return _.test(e) && l.test(e)
        },
        "uri-reference": /^(?:[a-z][a-z0-9+\-.]*:)?(?:\/?\/(?:(?:[a-z0-9\-._~!$&'()*+,;=:]|%[0-9a-f]{2})*@)?(?:\[(?:(?:(?:(?:[0-9a-f]{1,4}:){6}|::(?:[0-9a-f]{1,4}:){5}|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}|(?:(?:[0-9a-f]{1,4}:){0,1}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::)(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?))|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|[Vv][0-9a-f]+\.[a-z0-9\-._~!$&'()*+,;=:]+)\]|(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)|(?:[a-z0-9\-._~!$&'"()*+,;=]|%[0-9a-f]{2})*)(?::\d*)?(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*|\/(?:(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*)?|(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})+(?:\/(?:[a-z0-9\-._~!$&'"()*+,;=:@]|%[0-9a-f]{2})*)*)?(?:\?(?:[a-z0-9\-._~!$&'"()*+,;=:@/?]|%[0-9a-f]{2})*)?(?:#(?:[a-z0-9\-._~!$&'"()*+,;=:@/?]|%[0-9a-f]{2})*)?$/i,
        "uri-template": c,
        url: d,
        email: /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&''*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i,
        hostname: function(e) {
            return e.length <= 255 && s.test(e)
        },
        ipv4: /^(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)$/,
        ipv6: /^\s*(?:(?:(?:[0-9a-f]{1,4}:){7}(?:[0-9a-f]{1,4}|:))|(?:(?:[0-9a-f]{1,4}:){6}(?::[0-9a-f]{1,4}|(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){5}(?:(?:(?::[0-9a-f]{1,4}){1,2})|:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(?:(?:[0-9a-f]{1,4}:){4}(?:(?:(?::[0-9a-f]{1,4}){1,3})|(?:(?::[0-9a-f]{1,4})?:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){3}(?:(?:(?::[0-9a-f]{1,4}){1,4})|(?:(?::[0-9a-f]{1,4}){0,2}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){2}(?:(?:(?::[0-9a-f]{1,4}){1,5})|(?:(?::[0-9a-f]{1,4}){0,3}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?:(?:[0-9a-f]{1,4}:){1}(?:(?:(?::[0-9a-f]{1,4}){1,6})|(?:(?::[0-9a-f]{1,4}){0,4}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(?::(?:(?:(?::[0-9a-f]{1,4}){1,7})|(?:(?::[0-9a-f]{1,4}){0,5}:(?:(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(?:\.(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(?:%.+)?\s*$/i,
        regex: E,
        uuid: u,
        "json-pointer": h,
        "json-pointer-uri-fragment": p,
        "relative-json-pointer": f
    };
    var y = /t|\s/i;
    var _ = /\/|:/;
    var x = /[^\\]\\Z/;

    function E(e) {
        if (x.test(e)) return !1;
        try {
            return new RegExp(e), !0
        } catch (e) {
            return !1
        }
    }
}, function(e, t, n) {
    "use strict";
    var r = n(223),
        i = n(7).toHash;
    e.exports = function() {
        var e = [{
                type: "number",
                rules: [{
                    maximum: ["exclusiveMaximum"]
                }, {
                    minimum: ["exclusiveMinimum"]
                }, "multipleOf", "format"]
            }, {
                type: "string",
                rules: ["maxLength", "minLength", "pattern", "format"]
            }, {
                type: "array",
                rules: ["maxItems", "minItems", "items", "contains", "uniqueItems"]
            }, {
                type: "object",
                rules: ["maxProperties", "minProperties", "required", "dependencies", "propertyNames", {
                    properties: ["additionalProperties", "patternProperties"]
                }]
            }, {
                rules: ["$ref", "const", "enum", "not", "anyOf", "oneOf", "allOf", "if"]
            }],
            t = ["type", "$comment"];
        return e.all = i(t), e.types = i(["number", "integer", "string", "array", "object", "boolean", "null"]), e.forEach(function(n) {
            n.rules = n.rules.map(function(n) {
                var i;
                if ("object" == typeof n) {
                    var a = Object.keys(n)[0];
                    i = n[a], n = a, i.forEach(function(n) {
                        t.push(n), e.all[n] = !0
                    })
                }
                return t.push(n), e.all[n] = {
                    keyword: n,
                    code: r[n],
                    implements: i
                }
            }), e.all.$comment = {
                keyword: "$comment",
                code: r.$comment
            }, n.type && (e.types[n.type] = n)
        }), e.keywords = i(t.concat(["$schema", "$id", "id", "$data", "title", "description", "default", "definitions", "examples", "readOnly", "writeOnly", "contentMediaType", "contentEncoding", "additionalItems", "then", "else"])), e.custom = {}, e
    }
}, function(e, t, n) {
    "use strict";
    e.exports = {
        $ref: n(224),
        allOf: n(225),
        anyOf: n(226),
        $comment: n(227),
        const: n(228),
        contains: n(229),
        dependencies: n(230),
        enum: n(231),
        format: n(232),
        if: n(233),
        items: n(234),
        maximum: n(169),
        minimum: n(169),
        maxItems: n(170),
        minItems: n(170),
        maxLength: n(171),
        minLength: n(171),
        maxProperties: n(172),
        minProperties: n(172),
        multipleOf: n(235),
        not: n(236),
        oneOf: n(237),
        pattern: n(238),
        properties: n(239),
        propertyNames: n(240),
        required: n(241),
        uniqueItems: n(242),
        validate: n(168)
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r, i, a = " ",
            o = e.level,
            s = e.dataLevel,
            l = e.schema[t],
            c = e.errSchemaPath + "/" + t,
            d = !e.opts.allErrors,
            u = "data" + (s || ""),
            h = "valid" + o;
        if ("#" == l || "#/" == l) e.isRoot ? (r = e.async, i = "validate") : (r = !0 === e.root.schema.$async, i = "root.refVal[0]");
        else {
            var p = e.resolveRef(e.baseId, l, e.isRoot);
            if (void 0 === p) {
                var f = e.MissingRefError.message(e.baseId, l);
                if ("fail" == e.opts.missingRefs) {
                    e.logger.error(f), (y = y || []).push(a), a = "", !1 !== e.createErrors ? (a += " { keyword: '$ref' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(c) + " , params: { ref: '" + e.util.escapeQuotes(l) + "' } ", !1 !== e.opts.messages && (a += " , message: 'can\\'t resolve reference " + e.util.escapeQuotes(l) + "' "), e.opts.verbose && (a += " , schema: " + e.util.toQuotedString(l) + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + u + " "), a += " } ") : a += " {} ";
                    var m = a;
                    a = y.pop(), !e.compositeRule && d ? e.async ? a += " throw new ValidationError([" + m + "]); " : a += " validate.errors = [" + m + "]; return false; " : a += " var err = " + m + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", d && (a += " if (false) { ")
                } else {
                    if ("ignore" != e.opts.missingRefs) throw new e.MissingRefError(e.baseId, l, f);
                    e.logger.warn(f), d && (a += " if (true) { ")
                }
            } else if (p.inline) {
                var v = e.util.copy(e);
                v.level++;
                var g = "valid" + v.level;
                v.schema = p.schema, v.schemaPath = "", v.errSchemaPath = l, a += " " + e.validate(v).replace(/validate\.schema/g, p.code) + " ", d && (a += " if (" + g + ") { ")
            } else r = !0 === p.$async || e.async && !1 !== p.$async, i = p.code
        }
        if (i) {
            var y;
            (y = y || []).push(a), a = "", e.opts.passContext ? a += " " + i + ".call(this, " : a += " " + i + "( ", a += " " + u + ", (dataPath || '')", '""' != e.errorPath && (a += " + " + e.errorPath);
            var _ = a += " , " + (s ? "data" + (s - 1 || "") : "parentData") + " , " + (s ? e.dataPathArr[s] : "parentDataProperty") + ", rootData)  ";
            if (a = y.pop(), r) {
                if (!e.async) throw new Error("async schema referenced by sync schema");
                d && (a += " var " + h + "; "), a += " try { await " + _ + "; ", d && (a += " " + h + " = true; "), a += " } catch (e) { if (!(e instanceof ValidationError)) throw e; if (vErrors === null) vErrors = e.errors; else vErrors = vErrors.concat(e.errors); errors = vErrors.length; ", d && (a += " " + h + " = false; "), a += " } ", d && (a += " if (" + h + ") { ")
            } else a += " if (!" + _ + ") { if (vErrors === null) vErrors = " + i + ".errors; else vErrors = vErrors.concat(" + i + ".errors); errors = vErrors.length; } ", d && (a += " else { ")
        }
        return a
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.schema[t],
            a = e.schemaPath + e.util.getProperty(t),
            o = e.errSchemaPath + "/" + t,
            s = !e.opts.allErrors,
            l = e.util.copy(e),
            c = "";
        l.level++;
        var d = "valid" + l.level,
            u = l.baseId,
            h = !0,
            p = i;
        if (p)
            for (var f, m = -1, v = p.length - 1; m < v;) f = p[m += 1], e.util.schemaHasRules(f, e.RULES.all) && (h = !1, l.schema = f, l.schemaPath = a + "[" + m + "]", l.errSchemaPath = o + "/" + m, r += "  " + e.validate(l) + " ", l.baseId = u, s && (r += " if (" + d + ") { ", c += "}"));
        return s && (r += h ? " if (true) { " : " " + c.slice(0, -1) + " "), r = e.util.cleanUpCode(r)
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "valid" + i,
            h = "errs__" + i,
            p = e.util.copy(e),
            f = "";
        p.level++;
        var m = "valid" + p.level;
        if (o.every(function(t) {
                return e.util.schemaHasRules(t, e.RULES.all)
            })) {
            var v = p.baseId;
            r += " var " + h + " = errors; var " + u + " = false;  ";
            var g = e.compositeRule;
            e.compositeRule = p.compositeRule = !0;
            var y = o;
            if (y)
                for (var _, x = -1, E = y.length - 1; x < E;) _ = y[x += 1], p.schema = _, p.schemaPath = s + "[" + x + "]", p.errSchemaPath = l + "/" + x, r += "  " + e.validate(p) + " ", p.baseId = v, r += " " + u + " = " + u + " || " + m + "; if (!" + u + ") { ", f += "}";
            e.compositeRule = p.compositeRule = g, r += " " + f + " if (!" + u + ") {   var err =   ", !1 !== e.createErrors ? (r += " { keyword: 'anyOf' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: {} ", !1 !== e.opts.messages && (r += " , message: 'should match some schema in anyOf' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ", r += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", !e.compositeRule && c && (e.async ? r += " throw new ValidationError(vErrors); " : r += " validate.errors = vErrors; return false; "), r += " } else {  errors = " + h + "; if (vErrors !== null) { if (" + h + ") vErrors.length = " + h + "; else vErrors = null; } ", e.opts.allErrors && (r += " } "), r = e.util.cleanUpCode(r)
        } else c && (r += " if (true) { ");
        return r
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.schema[t],
            a = e.errSchemaPath + "/" + t,
            o = (e.opts.allErrors, e.util.toQuotedString(i));
        return !0 === e.opts.$comment ? r += " console.log(" + o + ");" : "function" == typeof e.opts.$comment && (r += " self._opts.$comment(" + o + ", " + e.util.toQuotedString(a) + ", validate.root.schema);"), r
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "valid" + i,
            h = e.opts.$data && o && o.$data;
        h && (r += " var schema" + i + " = " + e.util.getData(o.$data, a, e.dataPathArr) + "; "), h || (r += " var schema" + i + " = validate.schema" + s + ";"), r += "var " + u + " = equal(" + d + ", schema" + i + "); if (!" + u + ") {   ";
        var p = p || [];
        p.push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: 'const' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { allowedValue: schema" + i + " } ", !1 !== e.opts.messages && (r += " , message: 'should be equal to constant' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ";
        var f = r;
        return r = p.pop(), !e.compositeRule && c ? e.async ? r += " throw new ValidationError([" + f + "]); " : r += " validate.errors = [" + f + "]; return false; " : r += " var err = " + f + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", r += " }", c && (r += " else { "), r
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "valid" + i,
            h = "errs__" + i,
            p = e.util.copy(e);
        p.level++;
        var f = "valid" + p.level,
            m = "i" + i,
            v = p.dataLevel = e.dataLevel + 1,
            g = "data" + v,
            y = e.baseId,
            _ = e.util.schemaHasRules(o, e.RULES.all);
        if (r += "var " + h + " = errors;var " + u + ";", _) {
            var x = e.compositeRule;
            e.compositeRule = p.compositeRule = !0, p.schema = o, p.schemaPath = s, p.errSchemaPath = l, r += " var " + f + " = false; for (var " + m + " = 0; " + m + " < " + d + ".length; " + m + "++) { ", p.errorPath = e.util.getPathExpr(e.errorPath, m, e.opts.jsonPointers, !0);
            var E = d + "[" + m + "]";
            p.dataPathArr[v] = m;
            var b = e.validate(p);
            p.baseId = y, e.util.varOccurences(b, g) < 2 ? r += " " + e.util.varReplace(b, g, E) + " " : r += " var " + g + " = " + E + "; " + b + " ", r += " if (" + f + ") break; }  ", e.compositeRule = p.compositeRule = x, r += "  if (!" + f + ") {"
        } else r += " if (" + d + ".length == 0) {";
        var P = P || [];
        P.push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: 'contains' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: {} ", !1 !== e.opts.messages && (r += " , message: 'should contain a valid item' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ";
        var w = r;
        return r = P.pop(), !e.compositeRule && c ? e.async ? r += " throw new ValidationError([" + w + "]); " : r += " validate.errors = [" + w + "]; return false; " : r += " var err = " + w + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", r += " } else { ", _ && (r += "  errors = " + h + "; if (vErrors !== null) { if (" + h + ") vErrors.length = " + h + "; else vErrors = null; } "), e.opts.allErrors && (r += " } "), r = e.util.cleanUpCode(r)
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "errs__" + i,
            h = e.util.copy(e),
            p = "";
        h.level++;
        var f = "valid" + h.level,
            m = {},
            v = {},
            g = e.opts.ownProperties;
        for (E in o) {
            var y = o[E],
                _ = Array.isArray(y) ? v : m;
            _[E] = y
        }
        r += "var " + u + " = errors;";
        var x = e.errorPath;
        for (var E in r += "var missing" + i + ";", v)
            if ((_ = v[E]).length) {
                if (r += " if ( " + d + e.util.getProperty(E) + " !== undefined ", g && (r += " && Object.prototype.hasOwnProperty.call(" + d + ", '" + e.util.escapeQuotes(E) + "') "), c) {
                    r += " && ( ";
                    var b = _;
                    if (b)
                        for (var P = -1, w = b.length - 1; P < w;) {
                            D = b[P += 1], P && (r += " || "), r += " ( ( " + (O = d + (I = e.util.getProperty(D))) + " === undefined ", g && (r += " || ! Object.prototype.hasOwnProperty.call(" + d + ", '" + e.util.escapeQuotes(D) + "') "), r += ") && (missing" + i + " = " + e.util.toQuotedString(e.opts.jsonPointers ? D : I) + ") ) "
                        }
                    r += ")) {  ";
                    var S = "missing" + i,
                        M = "' + " + S + " + '";
                    e.opts._errorDataPathProperty && (e.errorPath = e.opts.jsonPointers ? e.util.getPathExpr(x, S, !0) : x + " + " + S);
                    var L = L || [];
                    L.push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: 'dependencies' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { property: '" + e.util.escapeQuotes(E) + "', missingProperty: '" + M + "', depsCount: " + _.length + ", deps: '" + e.util.escapeQuotes(1 == _.length ? _[0] : _.join(", ")) + "' } ", !1 !== e.opts.messages && (r += " , message: 'should have ", 1 == _.length ? r += "property " + e.util.escapeQuotes(_[0]) : r += "properties " + e.util.escapeQuotes(_.join(", ")), r += " when property " + e.util.escapeQuotes(E) + " is present' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ";
                    var C = r;
                    r = L.pop(), !e.compositeRule && c ? e.async ? r += " throw new ValidationError([" + C + "]); " : r += " validate.errors = [" + C + "]; return false; " : r += " var err = " + C + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; "
                } else {
                    r += " ) { ";
                    var T = _;
                    if (T)
                        for (var D, R = -1, A = T.length - 1; R < A;) {
                            D = T[R += 1];
                            var I = e.util.getProperty(D),
                                O = (M = e.util.escapeQuotes(D), d + I);
                            e.opts._errorDataPathProperty && (e.errorPath = e.util.getPath(x, D, e.opts.jsonPointers)), r += " if ( " + O + " === undefined ", g && (r += " || ! Object.prototype.hasOwnProperty.call(" + d + ", '" + e.util.escapeQuotes(D) + "') "), r += ") {  var err =   ", !1 !== e.createErrors ? (r += " { keyword: 'dependencies' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { property: '" + e.util.escapeQuotes(E) + "', missingProperty: '" + M + "', depsCount: " + _.length + ", deps: '" + e.util.escapeQuotes(1 == _.length ? _[0] : _.join(", ")) + "' } ", !1 !== e.opts.messages && (r += " , message: 'should have ", 1 == _.length ? r += "property " + e.util.escapeQuotes(_[0]) : r += "properties " + e.util.escapeQuotes(_.join(", ")), r += " when property " + e.util.escapeQuotes(E) + " is present' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ", r += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } "
                        }
                }
                r += " }   ", c && (p += "}", r += " else { ")
            }
        e.errorPath = x;
        var U = h.baseId;
        for (var E in m) {
            y = m[E];
            e.util.schemaHasRules(y, e.RULES.all) && (r += " " + f + " = true; if ( " + d + e.util.getProperty(E) + " !== undefined ", g && (r += " && Object.prototype.hasOwnProperty.call(" + d + ", '" + e.util.escapeQuotes(E) + "') "), r += ") { ", h.schema = y, h.schemaPath = s + e.util.getProperty(E), h.errSchemaPath = l + "/" + e.util.escapeFragment(E), r += "  " + e.validate(h) + " ", h.baseId = U, r += " }  ", c && (r += " if (" + f + ") { ", p += "}"))
        }
        return c && (r += "   " + p + " if (" + u + " == errors) {"), r = e.util.cleanUpCode(r)
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "valid" + i,
            h = e.opts.$data && o && o.$data;
        h && (r += " var schema" + i + " = " + e.util.getData(o.$data, a, e.dataPathArr) + "; ");
        var p = "i" + i,
            f = "schema" + i;
        h || (r += " var " + f + " = validate.schema" + s + ";"), r += "var " + u + ";", h && (r += " if (schema" + i + " === undefined) " + u + " = true; else if (!Array.isArray(schema" + i + ")) " + u + " = false; else {"), r += u + " = false;for (var " + p + "=0; " + p + "<" + f + ".length; " + p + "++) if (equal(" + d + ", " + f + "[" + p + "])) { " + u + " = true; break; }", h && (r += "  }  "), r += " if (!" + u + ") {   ";
        var m = m || [];
        m.push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: 'enum' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { allowedValues: schema" + i + " } ", !1 !== e.opts.messages && (r += " , message: 'should be equal to one of the allowed values' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ";
        var v = r;
        return r = m.pop(), !e.compositeRule && c ? e.async ? r += " throw new ValidationError([" + v + "]); " : r += " validate.errors = [" + v + "]; return false; " : r += " var err = " + v + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", r += " }", c && (r += " else { "), r
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || "");
        if (!1 === e.opts.format) return c && (r += " if (true) { "), r;
        var u, h = e.opts.$data && o && o.$data;
        h ? (r += " var schema" + i + " = " + e.util.getData(o.$data, a, e.dataPathArr) + "; ", u = "schema" + i) : u = o;
        var p = e.opts.unknownFormats,
            f = Array.isArray(p);
        if (h) {
            r += " var " + (m = "format" + i) + " = formats[" + u + "]; var " + (v = "isObject" + i) + " = typeof " + m + " == 'object' && !(" + m + " instanceof RegExp) && " + m + ".validate; var " + (g = "formatType" + i) + " = " + v + " && " + m + ".type || 'string'; if (" + v + ") { ", e.async && (r += " var async" + i + " = " + m + ".async; "), r += " " + m + " = " + m + ".validate; } if (  ", h && (r += " (" + u + " !== undefined && typeof " + u + " != 'string') || "), r += " (", "ignore" != p && (r += " (" + u + " && !" + m + " ", f && (r += " && self._opts.unknownFormats.indexOf(" + u + ") == -1 "), r += ") || "), r += " (" + m + " && " + g + " == '" + n + "' && !(typeof " + m + " == 'function' ? ", e.async ? r += " (async" + i + " ? await " + m + "(" + d + ") : " + m + "(" + d + ")) " : r += " " + m + "(" + d + ") ", r += " : " + m + ".test(" + d + "))))) {"
        } else {
            var m;
            if (!(m = e.formats[o])) {
                if ("ignore" == p) return e.logger.warn('unknown format "' + o + '" ignored in schema at path "' + e.errSchemaPath + '"'), c && (r += " if (true) { "), r;
                if (f && p.indexOf(o) >= 0) return c && (r += " if (true) { "), r;
                throw new Error('unknown format "' + o + '" is used in schema at path "' + e.errSchemaPath + '"')
            }
            var v, g = (v = "object" == typeof m && !(m instanceof RegExp) && m.validate) && m.type || "string";
            if (v) {
                var y = !0 === m.async;
                m = m.validate
            }
            if (g != n) return c && (r += " if (true) { "), r;
            if (y) {
                if (!e.async) throw new Error("async format in sync schema");
                r += " if (!(await " + (_ = "formats" + e.util.getProperty(o) + ".validate") + "(" + d + "))) { "
            } else {
                r += " if (! ";
                var _ = "formats" + e.util.getProperty(o);
                v && (_ += ".validate"), r += "function" == typeof m ? " " + _ + "(" + d + ") " : " " + _ + ".test(" + d + ") ", r += ") { "
            }
        }
        var x = x || [];
        x.push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: 'format' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { format:  ", r += h ? "" + u : "" + e.util.toQuotedString(o), r += "  } ", !1 !== e.opts.messages && (r += " , message: 'should match format \"", r += h ? "' + " + u + " + '" : "" + e.util.escapeQuotes(o), r += "\"' "), e.opts.verbose && (r += " , schema:  ", r += h ? "validate.schema" + s : "" + e.util.toQuotedString(o), r += "         , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ";
        var E = r;
        return r = x.pop(), !e.compositeRule && c ? e.async ? r += " throw new ValidationError([" + E + "]); " : r += " validate.errors = [" + E + "]; return false; " : r += " var err = " + E + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", r += " } ", c && (r += " else { "), r
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "valid" + i,
            h = "errs__" + i,
            p = e.util.copy(e);
        p.level++;
        var f = "valid" + p.level,
            m = e.schema.then,
            v = e.schema.else,
            g = void 0 !== m && e.util.schemaHasRules(m, e.RULES.all),
            y = void 0 !== v && e.util.schemaHasRules(v, e.RULES.all),
            _ = p.baseId;
        if (g || y) {
            var x;
            p.createErrors = !1, p.schema = o, p.schemaPath = s, p.errSchemaPath = l, r += " var " + h + " = errors; var " + u + " = true;  ";
            var E = e.compositeRule;
            e.compositeRule = p.compositeRule = !0, r += "  " + e.validate(p) + " ", p.baseId = _, p.createErrors = !0, r += "  errors = " + h + "; if (vErrors !== null) { if (" + h + ") vErrors.length = " + h + "; else vErrors = null; }  ", e.compositeRule = p.compositeRule = E, g ? (r += " if (" + f + ") {  ", p.schema = e.schema.then, p.schemaPath = e.schemaPath + ".then", p.errSchemaPath = e.errSchemaPath + "/then", r += "  " + e.validate(p) + " ", p.baseId = _, r += " " + u + " = " + f + "; ", g && y ? r += " var " + (x = "ifClause" + i) + " = 'then'; " : x = "'then'", r += " } ", y && (r += " else { ")) : r += " if (!" + f + ") { ", y && (p.schema = e.schema.else, p.schemaPath = e.schemaPath + ".else", p.errSchemaPath = e.errSchemaPath + "/else", r += "  " + e.validate(p) + " ", p.baseId = _, r += " " + u + " = " + f + "; ", g && y ? r += " var " + (x = "ifClause" + i) + " = 'else'; " : x = "'else'", r += " } "), r += " if (!" + u + ") {   var err =   ", !1 !== e.createErrors ? (r += " { keyword: 'if' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { failingKeyword: " + x + " } ", !1 !== e.opts.messages && (r += " , message: 'should match \"' + " + x + " + '\" schema' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ", r += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", !e.compositeRule && c && (e.async ? r += " throw new ValidationError(vErrors); " : r += " validate.errors = vErrors; return false; "), r += " }   ", c && (r += " else { "), r = e.util.cleanUpCode(r)
        } else c && (r += " if (true) { ");
        return r
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "valid" + i,
            h = "errs__" + i,
            p = e.util.copy(e),
            f = "";
        p.level++;
        var m = "valid" + p.level,
            v = "i" + i,
            g = p.dataLevel = e.dataLevel + 1,
            y = "data" + g,
            _ = e.baseId;
        if (r += "var " + h + " = errors;var " + u + ";", Array.isArray(o)) {
            var x = e.schema.additionalItems;
            if (!1 === x) {
                r += " " + u + " = " + d + ".length <= " + o.length + "; ";
                var E = l;
                l = e.errSchemaPath + "/additionalItems", r += "  if (!" + u + ") {   ";
                var b = b || [];
                b.push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: 'additionalItems' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { limit: " + o.length + " } ", !1 !== e.opts.messages && (r += " , message: 'should NOT have more than " + o.length + " items' "), e.opts.verbose && (r += " , schema: false , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ";
                var P = r;
                r = b.pop(), !e.compositeRule && c ? e.async ? r += " throw new ValidationError([" + P + "]); " : r += " validate.errors = [" + P + "]; return false; " : r += " var err = " + P + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", r += " } ", l = E, c && (f += "}", r += " else { ")
            }
            var w = o;
            if (w)
                for (var S, M = -1, L = w.length - 1; M < L;)
                    if (S = w[M += 1], e.util.schemaHasRules(S, e.RULES.all)) {
                        r += " " + m + " = true; if (" + d + ".length > " + M + ") { ";
                        var C = d + "[" + M + "]";
                        p.schema = S, p.schemaPath = s + "[" + M + "]", p.errSchemaPath = l + "/" + M, p.errorPath = e.util.getPathExpr(e.errorPath, M, e.opts.jsonPointers, !0), p.dataPathArr[g] = M;
                        var T = e.validate(p);
                        p.baseId = _, e.util.varOccurences(T, y) < 2 ? r += " " + e.util.varReplace(T, y, C) + " " : r += " var " + y + " = " + C + "; " + T + " ", r += " }  ", c && (r += " if (" + m + ") { ", f += "}")
                    }
            if ("object" == typeof x && e.util.schemaHasRules(x, e.RULES.all)) {
                p.schema = x, p.schemaPath = e.schemaPath + ".additionalItems", p.errSchemaPath = e.errSchemaPath + "/additionalItems", r += " " + m + " = true; if (" + d + ".length > " + o.length + ") {  for (var " + v + " = " + o.length + "; " + v + " < " + d + ".length; " + v + "++) { ", p.errorPath = e.util.getPathExpr(e.errorPath, v, e.opts.jsonPointers, !0);
                C = d + "[" + v + "]";
                p.dataPathArr[g] = v;
                T = e.validate(p);
                p.baseId = _, e.util.varOccurences(T, y) < 2 ? r += " " + e.util.varReplace(T, y, C) + " " : r += " var " + y + " = " + C + "; " + T + " ", c && (r += " if (!" + m + ") break; "), r += " } }  ", c && (r += " if (" + m + ") { ", f += "}")
            }
        } else if (e.util.schemaHasRules(o, e.RULES.all)) {
            p.schema = o, p.schemaPath = s, p.errSchemaPath = l, r += "  for (var " + v + " = 0; " + v + " < " + d + ".length; " + v + "++) { ", p.errorPath = e.util.getPathExpr(e.errorPath, v, e.opts.jsonPointers, !0);
            C = d + "[" + v + "]";
            p.dataPathArr[g] = v;
            T = e.validate(p);
            p.baseId = _, e.util.varOccurences(T, y) < 2 ? r += " " + e.util.varReplace(T, y, C) + " " : r += " var " + y + " = " + C + "; " + T + " ", c && (r += " if (!" + m + ") break; "), r += " }"
        }
        return c && (r += " " + f + " if (" + h + " == errors) {"), r = e.util.cleanUpCode(r)
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r, i = " ",
            a = e.level,
            o = e.dataLevel,
            s = e.schema[t],
            l = e.schemaPath + e.util.getProperty(t),
            c = e.errSchemaPath + "/" + t,
            d = !e.opts.allErrors,
            u = "data" + (o || ""),
            h = e.opts.$data && s && s.$data;
        h ? (i += " var schema" + a + " = " + e.util.getData(s.$data, o, e.dataPathArr) + "; ", r = "schema" + a) : r = s, i += "var division" + a + ";if (", h && (i += " " + r + " !== undefined && ( typeof " + r + " != 'number' || "), i += " (division" + a + " = " + u + " / " + r + ", ", e.opts.multipleOfPrecision ? i += " Math.abs(Math.round(division" + a + ") - division" + a + ") > 1e-" + e.opts.multipleOfPrecision + " " : i += " division" + a + " !== parseInt(division" + a + ") ", i += " ) ", h && (i += "  )  "), i += " ) {   ";
        var p = p || [];
        p.push(i), i = "", !1 !== e.createErrors ? (i += " { keyword: 'multipleOf' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(c) + " , params: { multipleOf: " + r + " } ", !1 !== e.opts.messages && (i += " , message: 'should be multiple of ", i += h ? "' + " + r : r + "'"), e.opts.verbose && (i += " , schema:  ", i += h ? "validate.schema" + l : "" + s, i += "         , parentSchema: validate.schema" + e.schemaPath + " , data: " + u + " "), i += " } ") : i += " {} ";
        var f = i;
        return i = p.pop(), !e.compositeRule && d ? e.async ? i += " throw new ValidationError([" + f + "]); " : i += " validate.errors = [" + f + "]; return false; " : i += " var err = " + f + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", i += "} ", d && (i += " else { "), i
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "errs__" + i,
            h = e.util.copy(e);
        h.level++;
        var p = "valid" + h.level;
        if (e.util.schemaHasRules(o, e.RULES.all)) {
            h.schema = o, h.schemaPath = s, h.errSchemaPath = l, r += " var " + u + " = errors;  ";
            var f, m = e.compositeRule;
            e.compositeRule = h.compositeRule = !0, h.createErrors = !1, h.opts.allErrors && (f = h.opts.allErrors, h.opts.allErrors = !1), r += " " + e.validate(h) + " ", h.createErrors = !0, f && (h.opts.allErrors = f), e.compositeRule = h.compositeRule = m, r += " if (" + p + ") {   ";
            var v = v || [];
            v.push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: 'not' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: {} ", !1 !== e.opts.messages && (r += " , message: 'should NOT be valid' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ";
            var g = r;
            r = v.pop(), !e.compositeRule && c ? e.async ? r += " throw new ValidationError([" + g + "]); " : r += " validate.errors = [" + g + "]; return false; " : r += " var err = " + g + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", r += " } else {  errors = " + u + "; if (vErrors !== null) { if (" + u + ") vErrors.length = " + u + "; else vErrors = null; } ", e.opts.allErrors && (r += " } ")
        } else r += "  var err =   ", !1 !== e.createErrors ? (r += " { keyword: 'not' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: {} ", !1 !== e.opts.messages && (r += " , message: 'should NOT be valid' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ", r += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", c && (r += " if (false) { ");
        return r
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "valid" + i,
            h = "errs__" + i,
            p = e.util.copy(e),
            f = "";
        p.level++;
        var m = "valid" + p.level,
            v = p.baseId,
            g = "prevValid" + i,
            y = "passingSchemas" + i;
        r += "var " + h + " = errors , " + g + " = false , " + u + " = false , " + y + " = null; ";
        var _ = e.compositeRule;
        e.compositeRule = p.compositeRule = !0;
        var x = o;
        if (x)
            for (var E, b = -1, P = x.length - 1; b < P;) E = x[b += 1], e.util.schemaHasRules(E, e.RULES.all) ? (p.schema = E, p.schemaPath = s + "[" + b + "]", p.errSchemaPath = l + "/" + b, r += "  " + e.validate(p) + " ", p.baseId = v) : r += " var " + m + " = true; ", b && (r += " if (" + m + " && " + g + ") { " + u + " = false; " + y + " = [" + y + ", " + b + "]; } else { ", f += "}"), r += " if (" + m + ") { " + u + " = " + g + " = true; " + y + " = " + b + "; }";
        return e.compositeRule = p.compositeRule = _, r += f + "if (!" + u + ") {   var err =   ", !1 !== e.createErrors ? (r += " { keyword: 'oneOf' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { passingSchemas: " + y + " } ", !1 !== e.opts.messages && (r += " , message: 'should match exactly one schema in oneOf' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ", r += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", !e.compositeRule && c && (e.async ? r += " throw new ValidationError(vErrors); " : r += " validate.errors = vErrors; return false; "), r += "} else {  errors = " + h + "; if (vErrors !== null) { if (" + h + ") vErrors.length = " + h + "; else vErrors = null; }", e.opts.allErrors && (r += " } "), r
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r, i = " ",
            a = e.level,
            o = e.dataLevel,
            s = e.schema[t],
            l = e.schemaPath + e.util.getProperty(t),
            c = e.errSchemaPath + "/" + t,
            d = !e.opts.allErrors,
            u = "data" + (o || ""),
            h = e.opts.$data && s && s.$data;
        h ? (i += " var schema" + a + " = " + e.util.getData(s.$data, o, e.dataPathArr) + "; ", r = "schema" + a) : r = s;
        var p = h ? "(new RegExp(" + r + "))" : e.usePattern(s);
        i += "if ( ", h && (i += " (" + r + " !== undefined && typeof " + r + " != 'string') || "), i += " !" + p + ".test(" + u + ") ) {   ";
        var f = f || [];
        f.push(i), i = "", !1 !== e.createErrors ? (i += " { keyword: 'pattern' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(c) + " , params: { pattern:  ", i += h ? "" + r : "" + e.util.toQuotedString(s), i += "  } ", !1 !== e.opts.messages && (i += " , message: 'should match pattern \"", i += h ? "' + " + r + " + '" : "" + e.util.escapeQuotes(s), i += "\"' "), e.opts.verbose && (i += " , schema:  ", i += h ? "validate.schema" + l : "" + e.util.toQuotedString(s), i += "         , parentSchema: validate.schema" + e.schemaPath + " , data: " + u + " "), i += " } ") : i += " {} ";
        var m = i;
        return i = f.pop(), !e.compositeRule && d ? e.async ? i += " throw new ValidationError([" + m + "]); " : i += " validate.errors = [" + m + "]; return false; " : i += " var err = " + m + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", i += "} ", d && (i += " else { "), i
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "errs__" + i,
            h = e.util.copy(e),
            p = "";
        h.level++;
        var f = "valid" + h.level,
            m = "key" + i,
            v = "idx" + i,
            g = h.dataLevel = e.dataLevel + 1,
            y = "data" + g,
            _ = "dataProperties" + i,
            x = Object.keys(o || {}),
            E = e.schema.patternProperties || {},
            b = Object.keys(E),
            P = e.schema.additionalProperties,
            w = x.length || b.length,
            S = !1 === P,
            M = "object" == typeof P && Object.keys(P).length,
            L = e.opts.removeAdditional,
            C = S || M || L,
            T = e.opts.ownProperties,
            D = e.baseId,
            R = e.schema.required;
        if (R && (!e.opts.$data || !R.$data) && R.length < e.opts.loopRequired) var A = e.util.toHash(R);
        if (r += "var " + u + " = errors;var " + f + " = true;", T && (r += " var " + _ + " = undefined;"), C) {
            if (r += T ? " " + _ + " = " + _ + " || Object.keys(" + d + "); for (var " + v + "=0; " + v + "<" + _ + ".length; " + v + "++) { var " + m + " = " + _ + "[" + v + "]; " : " for (var " + m + " in " + d + ") { ", w) {
                if (r += " var isAdditional" + i + " = !(false ", x.length)
                    if (x.length > 8) r += " || validate.schema" + s + ".hasOwnProperty(" + m + ") ";
                    else {
                        var I = x;
                        if (I)
                            for (var O = -1, U = I.length - 1; O < U;) Y = I[O += 1], r += " || " + m + " == " + e.util.toQuotedString(Y) + " "
                    }
                if (b.length) {
                    var N = b;
                    if (N)
                        for (var F = -1, z = N.length - 1; F < z;) ie = N[F += 1], r += " || " + e.usePattern(ie) + ".test(" + m + ") "
                }
                r += " ); if (isAdditional" + i + ") { "
            }
            if ("all" == L) r += " delete " + d + "[" + m + "]; ";
            else {
                var H = e.errorPath,
                    j = "' + " + m + " + '";
                if (e.opts._errorDataPathProperty && (e.errorPath = e.util.getPathExpr(e.errorPath, m, e.opts.jsonPointers)), S)
                    if (L) r += " delete " + d + "[" + m + "]; ";
                    else {
                        r += " " + f + " = false; ";
                        var V = l;
                        l = e.errSchemaPath + "/additionalProperties", (te = te || []).push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: 'additionalProperties' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { additionalProperty: '" + j + "' } ", !1 !== e.opts.messages && (r += " , message: '", e.opts._errorDataPathProperty ? r += "is an invalid additional property" : r += "should NOT have additional properties", r += "' "), e.opts.verbose && (r += " , schema: false , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ";
                        var k = r;
                        r = te.pop(), !e.compositeRule && c ? e.async ? r += " throw new ValidationError([" + k + "]); " : r += " validate.errors = [" + k + "]; return false; " : r += " var err = " + k + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", l = V, c && (r += " break; ")
                    } else if (M)
                    if ("failing" == L) {
                        r += " var " + u + " = errors;  ";
                        var B = e.compositeRule;
                        e.compositeRule = h.compositeRule = !0, h.schema = P, h.schemaPath = e.schemaPath + ".additionalProperties", h.errSchemaPath = e.errSchemaPath + "/additionalProperties", h.errorPath = e.opts._errorDataPathProperty ? e.errorPath : e.util.getPathExpr(e.errorPath, m, e.opts.jsonPointers);
                        var G = d + "[" + m + "]";
                        h.dataPathArr[g] = m;
                        var $ = e.validate(h);
                        h.baseId = D, e.util.varOccurences($, y) < 2 ? r += " " + e.util.varReplace($, y, G) + " " : r += " var " + y + " = " + G + "; " + $ + " ", r += " if (!" + f + ") { errors = " + u + "; if (validate.errors !== null) { if (errors) validate.errors.length = errors; else validate.errors = null; } delete " + d + "[" + m + "]; }  ", e.compositeRule = h.compositeRule = B
                    } else {
                        h.schema = P, h.schemaPath = e.schemaPath + ".additionalProperties", h.errSchemaPath = e.errSchemaPath + "/additionalProperties", h.errorPath = e.opts._errorDataPathProperty ? e.errorPath : e.util.getPathExpr(e.errorPath, m, e.opts.jsonPointers);
                        G = d + "[" + m + "]";
                        h.dataPathArr[g] = m;
                        $ = e.validate(h);
                        h.baseId = D, e.util.varOccurences($, y) < 2 ? r += " " + e.util.varReplace($, y, G) + " " : r += " var " + y + " = " + G + "; " + $ + " ", c && (r += " if (!" + f + ") break; ")
                    }
                e.errorPath = H
            }
            w && (r += " } "), r += " }  ", c && (r += " if (" + f + ") { ", p += "}")
        }
        var q = e.opts.useDefaults && !e.compositeRule;
        if (x.length) {
            var X = x;
            if (X)
                for (var Y, W = -1, Q = X.length - 1; W < Q;) {
                    var K = o[Y = X[W += 1]];
                    if (e.util.schemaHasRules(K, e.RULES.all)) {
                        var Z = e.util.getProperty(Y),
                            J = (G = d + Z, q && void 0 !== K.default);
                        h.schema = K, h.schemaPath = s + Z, h.errSchemaPath = l + "/" + e.util.escapeFragment(Y), h.errorPath = e.util.getPath(e.errorPath, Y, e.opts.jsonPointers), h.dataPathArr[g] = e.util.toQuotedString(Y);
                        $ = e.validate(h);
                        if (h.baseId = D, e.util.varOccurences($, y) < 2) {
                            $ = e.util.varReplace($, y, G);
                            var ee = G
                        } else {
                            ee = y;
                            r += " var " + y + " = " + G + "; "
                        }
                        if (J) r += " " + $ + " ";
                        else {
                            if (A && A[Y]) {
                                r += " if ( " + ee + " === undefined ", T && (r += " || ! Object.prototype.hasOwnProperty.call(" + d + ", '" + e.util.escapeQuotes(Y) + "') "), r += ") { " + f + " = false; ";
                                H = e.errorPath, V = l;
                                var te, ne = e.util.escapeQuotes(Y);
                                e.opts._errorDataPathProperty && (e.errorPath = e.util.getPath(H, Y, e.opts.jsonPointers)), l = e.errSchemaPath + "/required", (te = te || []).push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: 'required' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { missingProperty: '" + ne + "' } ", !1 !== e.opts.messages && (r += " , message: '", e.opts._errorDataPathProperty ? r += "is a required property" : r += "should have required property \\'" + ne + "\\'", r += "' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ";
                                k = r;
                                r = te.pop(), !e.compositeRule && c ? e.async ? r += " throw new ValidationError([" + k + "]); " : r += " validate.errors = [" + k + "]; return false; " : r += " var err = " + k + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", l = V, e.errorPath = H, r += " } else { "
                            } else c ? (r += " if ( " + ee + " === undefined ", T && (r += " || ! Object.prototype.hasOwnProperty.call(" + d + ", '" + e.util.escapeQuotes(Y) + "') "), r += ") { " + f + " = true; } else { ") : (r += " if (" + ee + " !== undefined ", T && (r += " &&   Object.prototype.hasOwnProperty.call(" + d + ", '" + e.util.escapeQuotes(Y) + "') "), r += " ) { ");
                            r += " " + $ + " } "
                        }
                    }
                    c && (r += " if (" + f + ") { ", p += "}")
                }
        }
        if (b.length) {
            var re = b;
            if (re)
                for (var ie, ae = -1, oe = re.length - 1; ae < oe;) {
                    K = E[ie = re[ae += 1]];
                    if (e.util.schemaHasRules(K, e.RULES.all)) {
                        h.schema = K, h.schemaPath = e.schemaPath + ".patternProperties" + e.util.getProperty(ie), h.errSchemaPath = e.errSchemaPath + "/patternProperties/" + e.util.escapeFragment(ie), r += T ? " " + _ + " = " + _ + " || Object.keys(" + d + "); for (var " + v + "=0; " + v + "<" + _ + ".length; " + v + "++) { var " + m + " = " + _ + "[" + v + "]; " : " for (var " + m + " in " + d + ") { ", r += " if (" + e.usePattern(ie) + ".test(" + m + ")) { ", h.errorPath = e.util.getPathExpr(e.errorPath, m, e.opts.jsonPointers);
                        G = d + "[" + m + "]";
                        h.dataPathArr[g] = m;
                        $ = e.validate(h);
                        h.baseId = D, e.util.varOccurences($, y) < 2 ? r += " " + e.util.varReplace($, y, G) + " " : r += " var " + y + " = " + G + "; " + $ + " ", c && (r += " if (!" + f + ") break; "), r += " } ", c && (r += " else " + f + " = true; "), r += " }  ", c && (r += " if (" + f + ") { ", p += "}")
                    }
                }
        }
        return c && (r += " " + p + " if (" + u + " == errors) {"), r = e.util.cleanUpCode(r)
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "errs__" + i,
            h = e.util.copy(e);
        h.level++;
        var p = "valid" + h.level;
        if (r += "var " + u + " = errors;", e.util.schemaHasRules(o, e.RULES.all)) {
            h.schema = o, h.schemaPath = s, h.errSchemaPath = l;
            var f = "key" + i,
                m = "idx" + i,
                v = "i" + i,
                g = "' + " + f + " + '",
                y = "data" + (h.dataLevel = e.dataLevel + 1),
                _ = "dataProperties" + i,
                x = e.opts.ownProperties,
                E = e.baseId;
            x && (r += " var " + _ + " = undefined; "), r += x ? " " + _ + " = " + _ + " || Object.keys(" + d + "); for (var " + m + "=0; " + m + "<" + _ + ".length; " + m + "++) { var " + f + " = " + _ + "[" + m + "]; " : " for (var " + f + " in " + d + ") { ", r += " var startErrs" + i + " = errors; ";
            var b = f,
                P = e.compositeRule;
            e.compositeRule = h.compositeRule = !0;
            var w = e.validate(h);
            h.baseId = E, e.util.varOccurences(w, y) < 2 ? r += " " + e.util.varReplace(w, y, b) + " " : r += " var " + y + " = " + b + "; " + w + " ", e.compositeRule = h.compositeRule = P, r += " if (!" + p + ") { for (var " + v + "=startErrs" + i + "; " + v + "<errors; " + v + "++) { vErrors[" + v + "].propertyName = " + f + "; }   var err =   ", !1 !== e.createErrors ? (r += " { keyword: 'propertyNames' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { propertyName: '" + g + "' } ", !1 !== e.opts.messages && (r += " , message: 'property name \\'" + g + "\\' is invalid' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ", r += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", !e.compositeRule && c && (e.async ? r += " throw new ValidationError(vErrors); " : r += " validate.errors = vErrors; return false; "), c && (r += " break; "), r += " } }"
        }
        return c && (r += "  if (" + u + " == errors) {"), r = e.util.cleanUpCode(r)
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r = " ",
            i = e.level,
            a = e.dataLevel,
            o = e.schema[t],
            s = e.schemaPath + e.util.getProperty(t),
            l = e.errSchemaPath + "/" + t,
            c = !e.opts.allErrors,
            d = "data" + (a || ""),
            u = "valid" + i,
            h = e.opts.$data && o && o.$data;
        h && (r += " var schema" + i + " = " + e.util.getData(o.$data, a, e.dataPathArr) + "; ");
        var p = "schema" + i;
        if (!h)
            if (o.length < e.opts.loopRequired && e.schema.properties && Object.keys(e.schema.properties).length) {
                var f = [],
                    m = o;
                if (m)
                    for (var v, g = -1, y = m.length - 1; g < y;) {
                        v = m[g += 1];
                        var _ = e.schema.properties[v];
                        _ && e.util.schemaHasRules(_, e.RULES.all) || (f[f.length] = v)
                    }
            } else f = o;
        if (h || f.length) {
            var x = e.errorPath,
                E = h || f.length >= e.opts.loopRequired,
                b = e.opts.ownProperties;
            if (c)
                if (r += " var missing" + i + "; ", E) {
                    h || (r += " var " + p + " = validate.schema" + s + "; ");
                    var P = "' + " + (T = "schema" + i + "[" + (M = "i" + i) + "]") + " + '";
                    e.opts._errorDataPathProperty && (e.errorPath = e.util.getPathExpr(x, T, e.opts.jsonPointers)), r += " var " + u + " = true; ", h && (r += " if (schema" + i + " === undefined) " + u + " = true; else if (!Array.isArray(schema" + i + ")) " + u + " = false; else {"), r += " for (var " + M + " = 0; " + M + " < " + p + ".length; " + M + "++) { " + u + " = " + d + "[" + p + "[" + M + "]] !== undefined ", b && (r += " &&   Object.prototype.hasOwnProperty.call(" + d + ", " + p + "[" + M + "]) "), r += "; if (!" + u + ") break; } ", h && (r += "  }  "), r += "  if (!" + u + ") {   ", (C = C || []).push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: 'required' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { missingProperty: '" + P + "' } ", !1 !== e.opts.messages && (r += " , message: '", e.opts._errorDataPathProperty ? r += "is a required property" : r += "should have required property \\'" + P + "\\'", r += "' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ";
                    var w = r;
                    r = C.pop(), !e.compositeRule && c ? e.async ? r += " throw new ValidationError([" + w + "]); " : r += " validate.errors = [" + w + "]; return false; " : r += " var err = " + w + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", r += " } else { "
                } else {
                    r += " if ( ";
                    var S = f;
                    if (S)
                        for (var M = -1, L = S.length - 1; M < L;) {
                            R = S[M += 1], M && (r += " || "), r += " ( ( " + (U = d + (O = e.util.getProperty(R))) + " === undefined ", b && (r += " || ! Object.prototype.hasOwnProperty.call(" + d + ", '" + e.util.escapeQuotes(R) + "') "), r += ") && (missing" + i + " = " + e.util.toQuotedString(e.opts.jsonPointers ? R : O) + ") ) "
                        }
                    r += ") {  ";
                    var C;
                    P = "' + " + (T = "missing" + i) + " + '";
                    e.opts._errorDataPathProperty && (e.errorPath = e.opts.jsonPointers ? e.util.getPathExpr(x, T, !0) : x + " + " + T), (C = C || []).push(r), r = "", !1 !== e.createErrors ? (r += " { keyword: 'required' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { missingProperty: '" + P + "' } ", !1 !== e.opts.messages && (r += " , message: '", e.opts._errorDataPathProperty ? r += "is a required property" : r += "should have required property \\'" + P + "\\'", r += "' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ";
                    w = r;
                    r = C.pop(), !e.compositeRule && c ? e.async ? r += " throw new ValidationError([" + w + "]); " : r += " validate.errors = [" + w + "]; return false; " : r += " var err = " + w + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", r += " } else { "
                } else if (E) {
                h || (r += " var " + p + " = validate.schema" + s + "; ");
                var T;
                P = "' + " + (T = "schema" + i + "[" + (M = "i" + i) + "]") + " + '";
                e.opts._errorDataPathProperty && (e.errorPath = e.util.getPathExpr(x, T, e.opts.jsonPointers)), h && (r += " if (" + p + " && !Array.isArray(" + p + ")) {  var err =   ", !1 !== e.createErrors ? (r += " { keyword: 'required' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { missingProperty: '" + P + "' } ", !1 !== e.opts.messages && (r += " , message: '", e.opts._errorDataPathProperty ? r += "is a required property" : r += "should have required property \\'" + P + "\\'", r += "' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ", r += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } else if (" + p + " !== undefined) { "), r += " for (var " + M + " = 0; " + M + " < " + p + ".length; " + M + "++) { if (" + d + "[" + p + "[" + M + "]] === undefined ", b && (r += " || ! Object.prototype.hasOwnProperty.call(" + d + ", " + p + "[" + M + "]) "), r += ") {  var err =   ", !1 !== e.createErrors ? (r += " { keyword: 'required' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { missingProperty: '" + P + "' } ", !1 !== e.opts.messages && (r += " , message: '", e.opts._errorDataPathProperty ? r += "is a required property" : r += "should have required property \\'" + P + "\\'", r += "' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ", r += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } } ", h && (r += "  }  ")
            } else {
                var D = f;
                if (D)
                    for (var R, A = -1, I = D.length - 1; A < I;) {
                        R = D[A += 1];
                        var O = e.util.getProperty(R),
                            U = (P = e.util.escapeQuotes(R), d + O);
                        e.opts._errorDataPathProperty && (e.errorPath = e.util.getPath(x, R, e.opts.jsonPointers)), r += " if ( " + U + " === undefined ", b && (r += " || ! Object.prototype.hasOwnProperty.call(" + d + ", '" + e.util.escapeQuotes(R) + "') "), r += ") {  var err =   ", !1 !== e.createErrors ? (r += " { keyword: 'required' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(l) + " , params: { missingProperty: '" + P + "' } ", !1 !== e.opts.messages && (r += " , message: '", e.opts._errorDataPathProperty ? r += "is a required property" : r += "should have required property \\'" + P + "\\'", r += "' "), e.opts.verbose && (r += " , schema: validate.schema" + s + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + d + " "), r += " } ") : r += " {} ", r += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; } "
                    }
            }
            e.errorPath = x
        } else c && (r += " if (true) {");
        return r
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r, i = " ",
            a = e.level,
            o = e.dataLevel,
            s = e.schema[t],
            l = e.schemaPath + e.util.getProperty(t),
            c = e.errSchemaPath + "/" + t,
            d = !e.opts.allErrors,
            u = "data" + (o || ""),
            h = "valid" + a,
            p = e.opts.$data && s && s.$data;
        if (p ? (i += " var schema" + a + " = " + e.util.getData(s.$data, o, e.dataPathArr) + "; ", r = "schema" + a) : r = s, (s || p) && !1 !== e.opts.uniqueItems) {
            p && (i += " var " + h + "; if (" + r + " === false || " + r + " === undefined) " + h + " = true; else if (typeof " + r + " != 'boolean') " + h + " = false; else { "), i += " var i = " + u + ".length , " + h + " = true , j; if (i > 1) { ";
            var f = e.schema.items && e.schema.items.type,
                m = Array.isArray(f);
            if (!f || "object" == f || "array" == f || m && (f.indexOf("object") >= 0 || f.indexOf("array") >= 0)) i += " outer: for (;i--;) { for (j = i; j--;) { if (equal(" + u + "[i], " + u + "[j])) { " + h + " = false; break outer; } } } ";
            else {
                i += " var itemIndices = {}, item; for (;i--;) { var item = " + u + "[i]; ";
                var v = "checkDataType" + (m ? "s" : "");
                i += " if (" + e.util[v](f, "item", !0) + ") continue; ", m && (i += " if (typeof item == 'string') item = '\"' + item; "), i += " if (typeof itemIndices[item] == 'number') { " + h + " = false; j = itemIndices[item]; break; } itemIndices[item] = i; } "
            }
            i += " } ", p && (i += "  }  "), i += " if (!" + h + ") {   ";
            var g = g || [];
            g.push(i), i = "", !1 !== e.createErrors ? (i += " { keyword: 'uniqueItems' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(c) + " , params: { i: i, j: j } ", !1 !== e.opts.messages && (i += " , message: 'should NOT have duplicate items (items ## ' + j + ' and ' + i + ' are identical)' "), e.opts.verbose && (i += " , schema:  ", i += p ? "validate.schema" + l : "" + s, i += "         , parentSchema: validate.schema" + e.schemaPath + " , data: " + u + " "), i += " } ") : i += " {} ";
            var y = i;
            i = g.pop(), !e.compositeRule && d ? e.async ? i += " throw new ValidationError([" + y + "]); " : i += " validate.errors = [" + y + "]; return false; " : i += " var err = " + y + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", i += " } ", d && (i += " else { ")
        } else d && (i += " if (true) { ");
        return i
    }
}, function(e, t, n) {
    "use strict";
    var r = ["multipleOf", "maximum", "exclusiveMaximum", "minimum", "exclusiveMinimum", "maxLength", "minLength", "pattern", "additionalItems", "maxItems", "minItems", "uniqueItems", "maxProperties", "minProperties", "required", "additionalProperties", "enum", "format", "const"];
    e.exports = function(e, t) {
        for (var n = 0; n < t.length; n++) {
            e = JSON.parse(JSON.stringify(e));
            var i, a = t[n].split("/"),
                o = e;
            for (i = 1; i < a.length; i++) o = o[a[i]];
            for (i = 0; i < r.length; i++) {
                var s = r[i],
                    l = o[s];
                l && (o[s] = {
                    anyOf: [l, {
                        $ref: "https://raw.githubusercontent.com/epoberezkin/ajv/master/lib/refs/data.json#"
                    }]
                })
            }
        }
        return e
    }
}, function(e, t, n) {
    "use strict";
    var r = n(31).MissingRef;
    e.exports = function e(t, n, i) {
        var a = this;
        if ("function" != typeof this._opts.loadSchema) throw new Error("options.loadSchema should be a function");
        "function" == typeof n && (i = n, n = void 0);
        var o = s(t).then(function() {
            var e = a._addSchema(t, void 0, n);
            return e.validate || function e(t) {
                try {
                    return a._compile(t)
                } catch (e) {
                    if (e instanceof r) return function i(e) {
                        var i = e.missingSchema;
                        if (c(i)) throw new Error("Schema " + i + " is loaded but " + e.missingRef + " cannot be resolved");
                        var o = a._loadingSchemas[i];
                        o || (o = a._loadingSchemas[i] = a._opts.loadSchema(i)).then(l, l);
                        return o.then(function(e) {
                            if (!c(i)) return s(e).then(function() {
                                c(i) || a.addSchema(e, i, void 0, n)
                            })
                        }).then(function() {
                            return function e(t) {
                                try {
                                    return a._compile(t)
                                } catch (e) {
                                    if (e instanceof r) return i(e);
                                    throw e
                                }

                                function i(r) {
                                    var i = r.missingSchema;
                                    if (c(i)) throw new Error("Schema " + i + " is loaded but " + r.missingRef + " cannot be resolved");
                                    var o = a._loadingSchemas[i];
                                    return o || (o = a._loadingSchemas[i] = a._opts.loadSchema(i)).then(l, l), o.then(function(e) {
                                        if (!c(i)) return s(e).then(function() {
                                            c(i) || a.addSchema(e, i, void 0, n)
                                        })
                                    }).then(function() {
                                        return e(t)
                                    });

                                    function l() {
                                        delete a._loadingSchemas[i]
                                    }

                                    function c(e) {
                                        return a._refs[e] || a._schemas[e]
                                    }
                                }
                            }(t)
                        });

                        function l() {
                            delete a._loadingSchemas[i]
                        }

                        function c(e) {
                            return a._refs[e] || a._schemas[e]
                        }
                    }(e);
                    throw e
                }

                function i(r) {
                    var i = r.missingSchema;
                    if (c(i)) throw new Error("Schema " + i + " is loaded but " + r.missingRef + " cannot be resolved");
                    var o = a._loadingSchemas[i];
                    return o || (o = a._loadingSchemas[i] = a._opts.loadSchema(i)).then(l, l), o.then(function(e) {
                        if (!c(i)) return s(e).then(function() {
                            c(i) || a.addSchema(e, i, void 0, n)
                        })
                    }).then(function() {
                        return e(t)
                    });

                    function l() {
                        delete a._loadingSchemas[i]
                    }

                    function c(e) {
                        return a._refs[e] || a._schemas[e]
                    }
                }
            }(e)
        });
        i && o.then(function(e) {
            i(null, e)
        }, i);
        return o;

        function s(t) {
            var n = t.$schema;
            return n && !a.getSchema(n) ? e.call(a, {
                $ref: n
            }, !0) : Promise.resolve()
        }
    }
}, function(e, t, n) {
    "use strict";
    var r = /^[a-z_$][a-z0-9_$-]*$/i,
        i = n(246);
    e.exports = {
        add: function(e, t) {
            var n = this.RULES;
            if (n.keywords[e]) throw new Error("Keyword " + e + " is already defined");
            if (!r.test(e)) throw new Error("Keyword " + e + " is not a valid identifier");
            if (t) {
                if (t.macro && void 0 !== t.valid) throw new Error('"valid" option cannot be used with macro keywords');
                var a = t.type;
                if (Array.isArray(a)) {
                    var o, s = a.length;
                    for (o = 0; o < s; o++) u(a[o]);
                    for (o = 0; o < s; o++) d(e, a[o], t)
                } else a && u(a), d(e, a, t);
                var l = !0 === t.$data && this._opts.$data;
                if (l && !t.validate) throw new Error('$data support: "validate" function is not defined');
                var c = t.metaSchema;
                c && (l && (c = {
                    anyOf: [c, {
                        $ref: "https://raw.githubusercontent.com/epoberezkin/ajv/master/lib/refs/data.json#"
                    }]
                }), t.validateSchema = this.compile(c, !0))
            }

            function d(e, t, r) {
                for (var a, o = 0; o < n.length; o++) {
                    var s = n[o];
                    if (s.type == t) {
                        a = s;
                        break
                    }
                }
                a || (a = {
                    type: t,
                    rules: []
                }, n.push(a));
                var l = {
                    keyword: e,
                    definition: r,
                    custom: !0,
                    code: i,
                    implements: r.implements
                };
                a.rules.push(l), n.custom[e] = l
            }

            function u(e) {
                if (!n.types[e]) throw new Error("Unknown type " + e)
            }
            return n.keywords[e] = n.all[e] = !0, this
        },
        get: function(e) {
            var t = this.RULES.custom[e];
            return t ? t.definition : this.RULES.keywords[e] || !1
        },
        remove: function(e) {
            var t = this.RULES;
            delete t.keywords[e], delete t.all[e], delete t.custom[e];
            for (var n = 0; n < t.length; n++)
                for (var r = t[n].rules, i = 0; i < r.length; i++)
                    if (r[i].keyword == e) {
                        r.splice(i, 1);
                        break
                    }
            return this
        }
    }
}, function(e, t, n) {
    "use strict";
    e.exports = function(e, t, n) {
        var r, i, a = " ",
            o = e.level,
            s = e.dataLevel,
            l = e.schema[t],
            c = e.schemaPath + e.util.getProperty(t),
            d = e.errSchemaPath + "/" + t,
            u = !e.opts.allErrors,
            h = "data" + (s || ""),
            p = "valid" + o,
            f = "errs__" + o,
            m = e.opts.$data && l && l.$data;
        m ? (a += " var schema" + o + " = " + e.util.getData(l.$data, s, e.dataPathArr) + "; ", i = "schema" + o) : i = l;
        var v, g, y, _, x, E = "definition" + o,
            b = this.definition,
            P = "";
        if (m && b.$data) {
            x = "keywordValidate" + o;
            var w = b.validateSchema;
            a += " var " + E + " = RULES.custom['" + t + "'].definition; var " + x + " = " + E + ".validate;"
        } else {
            if (!(_ = e.useCustomRule(this, l, e.schema, e))) return;
            i = "validate.schema" + c, x = _.code, v = b.compile, g = b.inline, y = b.macro
        }
        var S = x + ".errors",
            M = "i" + o,
            L = "ruleErr" + o,
            C = b.async;
        if (C && !e.async) throw new Error("async keyword in sync schema");
        if (g || y || (a += S + " = null;"), a += "var " + f + " = errors;var " + p + ";", m && b.$data && (P += "}", a += " if (" + i + " === undefined) { " + p + " = true; } else { ", w && (P += "}", a += " " + p + " = " + E + ".validateSchema(" + i + "); if (" + p + ") { ")), g) b.statements ? a += " " + _.validate + " " : a += " " + p + " = " + _.validate + "; ";
        else if (y) {
            var T = e.util.copy(e);
            P = "";
            T.level++;
            var D = "valid" + T.level;
            T.schema = _.validate, T.schemaPath = "";
            var R = e.compositeRule;
            e.compositeRule = T.compositeRule = !0;
            var A = e.validate(T).replace(/validate\.schema/g, x);
            e.compositeRule = T.compositeRule = R, a += " " + A
        } else {
            (N = N || []).push(a), a = "", a += "  " + x + ".call( ", e.opts.passContext ? a += "this" : a += "self", v || !1 === b.schema ? a += " , " + h + " " : a += " , " + i + " , " + h + " , validate.schema" + e.schemaPath + " ", a += " , (dataPath || '')", '""' != e.errorPath && (a += " + " + e.errorPath);
            var I = s ? "data" + (s - 1 || "") : "parentData",
                O = s ? e.dataPathArr[s] : "parentDataProperty",
                U = a += " , " + I + " , " + O + " , rootData )  ";
            a = N.pop(), !1 === b.errors ? (a += " " + p + " = ", C && (a += "await "), a += U + "; ") : a += C ? " var " + (S = "customErrors" + o) + " = null; try { " + p + " = await " + U + "; } catch (e) { " + p + " = false; if (e instanceof ValidationError) " + S + " = e.errors; else throw e; } " : " " + S + " = null; " + p + " = " + U + "; "
        }
        if (b.modifying && (a += " if (" + I + ") " + h + " = " + I + "[" + O + "];"), a += "" + P, b.valid) u && (a += " if (true) { ");
        else {
            var N;
            a += " if ( ", void 0 === b.valid ? (a += " !", a += y ? "" + D : "" + p) : a += " " + !b.valid + " ", a += ") { ", r = this.keyword, (N = N || []).push(a), a = "", (N = N || []).push(a), a = "", !1 !== e.createErrors ? (a += " { keyword: '" + (r || "custom") + "' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(d) + " , params: { keyword: '" + this.keyword + "' } ", !1 !== e.opts.messages && (a += " , message: 'should pass \"" + this.keyword + "\" keyword validation' "), e.opts.verbose && (a += " , schema: validate.schema" + c + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + h + " "), a += " } ") : a += " {} ";
            var F = a;
            a = N.pop(), !e.compositeRule && u ? e.async ? a += " throw new ValidationError([" + F + "]); " : a += " validate.errors = [" + F + "]; return false; " : a += " var err = " + F + ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ";
            var z = a;
            a = N.pop(), g ? b.errors ? "full" != b.errors && (a += "  for (var " + M + "=" + f + "; " + M + "<errors; " + M + "++) { var " + L + " = vErrors[" + M + "]; if (" + L + ".dataPath === undefined) " + L + ".dataPath = (dataPath || '') + " + e.errorPath + "; if (" + L + ".schemaPath === undefined) { " + L + '.schemaPath = "' + d + '"; } ', e.opts.verbose && (a += " " + L + ".schema = " + i + "; " + L + ".data = " + h + "; "), a += " } ") : !1 === b.errors ? a += " " + z + " " : (a += " if (" + f + " == errors) { " + z + " } else {  for (var " + M + "=" + f + "; " + M + "<errors; " + M + "++) { var " + L + " = vErrors[" + M + "]; if (" + L + ".dataPath === undefined) " + L + ".dataPath = (dataPath || '') + " + e.errorPath + "; if (" + L + ".schemaPath === undefined) { " + L + '.schemaPath = "' + d + '"; } ', e.opts.verbose && (a += " " + L + ".schema = " + i + "; " + L + ".data = " + h + "; "), a += " } } ") : y ? (a += "   var err =   ", !1 !== e.createErrors ? (a += " { keyword: '" + (r || "custom") + "' , dataPath: (dataPath || '') + " + e.errorPath + " , schemaPath: " + e.util.toQuotedString(d) + " , params: { keyword: '" + this.keyword + "' } ", !1 !== e.opts.messages && (a += " , message: 'should pass \"" + this.keyword + "\" keyword validation' "), e.opts.verbose && (a += " , schema: validate.schema" + c + " , parentSchema: validate.schema" + e.schemaPath + " , data: " + h + " "), a += " } ") : a += " {} ", a += ";  if (vErrors === null) vErrors = [err]; else vErrors.push(err); errors++; ", !e.compositeRule && u && (e.async ? a += " throw new ValidationError(vErrors); " : a += " validate.errors = vErrors; return false; ")) : !1 === b.errors ? a += " " + z + " " : (a += " if (Array.isArray(" + S + ")) { if (vErrors === null) vErrors = " + S + "; else vErrors = vErrors.concat(" + S + "); errors = vErrors.length;  for (var " + M + "=" + f + "; " + M + "<errors; " + M + "++) { var " + L + " = vErrors[" + M + "]; if (" + L + ".dataPath === undefined) " + L + ".dataPath = (dataPath || '') + " + e.errorPath + ";  " + L + '.schemaPath = "' + d + '";  ', e.opts.verbose && (a += " " + L + ".schema = " + i + "; " + L + ".data = " + h + "; "), a += " } } else { " + z + " } "), a += " } ", u && (a += " else { ")
        }
        return a
    }
}, function(e) {
    e.exports = {
        $schema: "http://json-schema.org/draft-07/schema#",
        $id: "https://raw.githubusercontent.com/epoberezkin/ajv/master/lib/refs/data.json#",
        description: "Meta-schema for $data reference (JSON Schema extension proposal)",
        type: "object",
        required: ["$data"],
        properties: {
            $data: {
                type: "string",
                anyOf: [{
                    format: "relative-json-pointer"
                }, {
                    format: "json-pointer"
                }]
            }
        },
        additionalProperties: !1
    }
}, function(e) {
    e.exports = {
        $schema: "http://json-schema.org/draft-07/schema#",
        $id: "http://json-schema.org/draft-07/schema#",
        title: "Core schema meta-schema",
        definitions: {
            schemaArray: {
                type: "array",
                minItems: 1,
                items: {
                    $ref: "#"
                }
            },
            nonNegativeInteger: {
                type: "integer",
                minimum: 0
            },
            nonNegativeIntegerDefault0: {
                allOf: [{
                    $ref: "#/definitions/nonNegativeInteger"
                }, {
                    default: 0
                }]
            },
            simpleTypes: {
                enum: ["array", "boolean", "integer", "null", "number", "object", "string"]
            },
            stringArray: {
                type: "array",
                items: {
                    type: "string"
                },
                uniqueItems: !0,
                default: []
            }
        },
        type: ["object", "boolean"],
        properties: {
            $id: {
                type: "string",
                format: "uri-reference"
            },
            $schema: {
                type: "string",
                format: "uri"
            },
            $ref: {
                type: "string",
                format: "uri-reference"
            },
            $comment: {
                type: "string"
            },
            title: {
                type: "string"
            },
            description: {
                type: "string"
            },
            default: !0,
            readOnly: {
                type: "boolean",
                default: !1
            },
            examples: {
                type: "array",
                items: !0
            },
            multipleOf: {
                type: "number",
                exclusiveMinimum: 0
            },
            maximum: {
                type: "number"
            },
            exclusiveMaximum: {
                type: "number"
            },
            minimum: {
                type: "number"
            },
            exclusiveMinimum: {
                type: "number"
            },
            maxLength: {
                $ref: "#/definitions/nonNegativeInteger"
            },
            minLength: {
                $ref: "#/definitions/nonNegativeIntegerDefault0"
            },
            pattern: {
                type: "string",
                format: "regex"
            },
            additionalItems: {
                $ref: "#"
            },
            items: {
                anyOf: [{
                    $ref: "#"
                }, {
                    $ref: "#/definitions/schemaArray"
                }],
                default: !0
            },
            maxItems: {
                $ref: "#/definitions/nonNegativeInteger"
            },
            minItems: {
                $ref: "#/definitions/nonNegativeIntegerDefault0"
            },
            uniqueItems: {
                type: "boolean",
                default: !1
            },
            contains: {
                $ref: "#"
            },
            maxProperties: {
                $ref: "#/definitions/nonNegativeInteger"
            },
            minProperties: {
                $ref: "#/definitions/nonNegativeIntegerDefault0"
            },
            required: {
                $ref: "#/definitions/stringArray"
            },
            additionalProperties: {
                $ref: "#"
            },
            definitions: {
                type: "object",
                additionalProperties: {
                    $ref: "#"
                },
                default: {}
            },
            properties: {
                type: "object",
                additionalProperties: {
                    $ref: "#"
                },
                default: {}
            },
            patternProperties: {
                type: "object",
                additionalProperties: {
                    $ref: "#"
                },
                propertyNames: {
                    format: "regex"
                },
                default: {}
            },
            dependencies: {
                type: "object",
                additionalProperties: {
                    anyOf: [{
                        $ref: "#"
                    }, {
                        $ref: "#/definitions/stringArray"
                    }]
                }
            },
            propertyNames: {
                $ref: "#"
            },
            const: !0,
            enum: {
                type: "array",
                items: !0,
                minItems: 1,
                uniqueItems: !0
            },
            type: {
                anyOf: [{
                    $ref: "#/definitions/simpleTypes"
                }, {
                    type: "array",
                    items: {
                        $ref: "#/definitions/simpleTypes"
                    },
                    minItems: 1,
                    uniqueItems: !0
                }]
            },
            format: {
                type: "string"
            },
            contentMediaType: {
                type: "string"
            },
            contentEncoding: {
                type: "string"
            },
            if: {
                $ref: "#"
            },
            then: {
                $ref: "#"
            },
            else: {
                $ref: "#"
            },
            allOf: {
                $ref: "#/definitions/schemaArray"
            },
            anyOf: {
                $ref: "#/definitions/schemaArray"
            },
            oneOf: {
                $ref: "#/definitions/schemaArray"
            },
            not: {
                $ref: "#"
            }
        },
        default: !0
    }
}, function(e) {
    e.exports = {
        $id: "https://schemas.3d.si.edu/public_api/math.schema.json",
        $schema: "http://json-schema.org/draft-07/schema#",
        title: "Math",
        description: "Definitions for mathematical compound objects such as vectors and matrices.",
        definitions: {
            vector2: {
                description: "2-component vector.",
                $id: "#vector2",
                type: "array",
                items: {
                    type: "number"
                },
                minItems: 2,
                maxItems: 2,
                default: [0, 0]
            },
            vector3: {
                description: "3-component vector.",
                $id: "#vector3",
                type: "array",
                items: {
                    type: "number"
                },
                minItems: 3,
                maxItems: 3,
                default: [0, 0, 0]
            },
            vector4: {
                description: "4-component vector.",
                $id: "#vector4",
                type: "array",
                items: {
                    type: "number"
                },
                minItems: 4,
                maxItems: 4,
                default: [0, 0, 0, 0]
            },
            matrix3: {
                description: "3 by 3, matrix, storage: column-major.",
                $id: "#matrix3",
                type: "array",
                items: {
                    type: "number"
                },
                minItems: 9,
                maxItems: 9,
                default: [1, 0, 0, 0, 1, 0, 0, 0, 1]
            },
            matrix4: {
                description: "4 by 4 matrix, storage: column-major.",
                $id: "#matrix4",
                type: "array",
                items: {
                    type: "number"
                },
                minItems: 16,
                maxItems: 16,
                default: [1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1]
            },
            boundingBox: {
                description: "Axis-aligned 3D bounding box.",
                $id: "#boundingBox",
                type: "object",
                properties: {
                    min: {
                        $ref: "#vector3"
                    },
                    max: {
                        $ref: "#vector3"
                    }
                },
                required: ["min", "max"]
            }
        }
    }
}, function(e) {
    e.exports = {
        $id: "https://schemas.3d.si.edu/public_api/presentation.schema.json",
        $schema: "http://json-schema.org/draft-07/schema#",
        title: "Smithsonian 3D Presentation",
        description: "Describes a 3D scene containing one or multiple 3D items.",
        definitions: {
            node: {
                $id: "#node",
                type: "object",
                properties: {
                    children: {
                        type: "array",
                        description: "The indices of this node's children.",
                        items: {
                            type: "integer",
                            minimum: 0
                        },
                        uniqueItems: !0,
                        minItems: 1
                    },
                    matrix: {
                        description: "A floating-point 4x4 transformation matrix stored in column-major order.",
                        type: "array",
                        items: {
                            type: "number"
                        },
                        minItems: 16,
                        maxItems: 16,
                        default: [1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1]
                    },
                    translation: {
                        description: "The node's translation along the x, y, and z axes.",
                        type: "array",
                        items: {
                            type: "number"
                        },
                        minItems: 3,
                        maxItems: 3,
                        default: [0, 0, 0]
                    },
                    rotation: {
                        description: "The node's unit quaternion rotation in the order (x, y, z, w), where w is the scalar.",
                        type: "array",
                        items: {
                            type: "number",
                            minimum: -1,
                            maximum: 1
                        },
                        minItems: 4,
                        maxItems: 4,
                        default: [0, 0, 0, 1]
                    },
                    scale: {
                        description: "The node's non-uniform scale, given as the scaling factors along the x, y, and z axes.",
                        type: "array",
                        items: {
                            type: "number"
                        },
                        minItems: 3,
                        maxItems: 3,
                        default: [1, 1, 1]
                    },
                    item: {
                        description: "The index of the item in this node.",
                        type: "integer",
                        minimum: 0
                    },
                    reference: {
                        description: "The index of the reference in this node.",
                        type: "integer",
                        minimum: 0
                    },
                    camera: {
                        description: "The index of the camera in this node.",
                        type: "integer",
                        minimum: 0
                    },
                    light: {
                        description: "The index of the light in this node.",
                        type: "integer",
                        minimum: 0
                    }
                },
                not: {
                    anyOf: [{
                        required: ["matrix", "translation"]
                    }, {
                        required: ["matrix", "rotation"]
                    }, {
                        required: ["matrix", "scale"]
                    }]
                }
            },
            reference: {
                $id: "#reference",
                type: "object",
                properties: {
                    mimeType: {
                        type: "string"
                    },
                    uri: {
                        type: "string"
                    }
                },
                required: ["uri"]
            },
            camera: {
                $id: "#camera",
                type: "object",
                properties: {
                    type: {
                        description: "Specifies if the camera uses a perspective or orthographic projection.",
                        type: "string",
                        enum: ["perspective", "orthographic"]
                    },
                    perspective: {
                        description: "A perspective camera containing properties to create a perspective projection matrix.",
                        type: "object",
                        properties: {
                            yfov: {
                                type: "number",
                                description: "The floating-point vertical field of view in radians.",
                                exclusiveMinimum: 0
                            },
                            aspectRatio: {
                                type: "number",
                                description: "The floating-point aspect ratio of the field of view.",
                                exclusiveMinimum: 0
                            },
                            znear: {
                                type: "number",
                                description: "The floating-point distance to the near clipping plane.",
                                exclusiveMinimum: 0
                            },
                            zfar: {
                                type: "number",
                                description: "The floating-point distance to the far clipping plane.",
                                exclusiveMinimum: 0
                            }
                        },
                        required: ["yfov", "znear"]
                    },
                    orthographic: {
                        description: "An orthographic camera containing properties to create an orthographic projection matrix.",
                        type: "object",
                        properties: {
                            xmag: {
                                type: "number",
                                description: "The floating-point horizontal magnification of the view. Must not be zero."
                            },
                            ymag: {
                                type: "number",
                                description: "The floating-point vertical magnification of the view. Must not be zero."
                            },
                            znear: {
                                type: "number",
                                description: "The floating-point distance to the near clipping plane.",
                                exclusiveMinimum: 0
                            },
                            zfar: {
                                type: "number",
                                description: "The floating-point distance to the far clipping plane. `zfar` must be greater than `znear`.",
                                exclusiveMinimum: 0
                            }
                        },
                        required: ["xmag", "ymag", "znear", "zfar"]
                    }
                },
                required: ["type"],
                not: {
                    required: ["perspective", "orthographic"]
                }
            },
            light: {
                $id: "#light",
                type: "object",
                properties: {
                    type: {
                        description: "Specifies the type of the light source.",
                        type: "string",
                        enum: ["ambient", "directional", "point", "spot", "hemisphere"]
                    },
                    color: {
                        $ref: "#colorRGB"
                    },
                    intensity: {
                        type: "number",
                        minimum: 0,
                        default: 1
                    },
                    castShadow: {
                        type: "boolean",
                        default: !1
                    },
                    point: {
                        type: "object",
                        properties: {
                            distance: {
                                type: "number",
                                minimum: 0
                            },
                            decay: {
                                type: "number",
                                minimum: 0
                            }
                        }
                    },
                    spot: {
                        type: "object",
                        properties: {
                            distance: {
                                type: "number",
                                minimum: 0
                            },
                            decay: {
                                type: "number",
                                minimum: 0
                            },
                            angle: {
                                type: "number",
                                minimum: 0
                            },
                            penumbra: {
                                type: "number",
                                minimum: 0
                            }
                        }
                    },
                    hemisphere: {
                        type: "object",
                        properties: {
                            groundColor: {
                                $ref: "#colorRGB"
                            }
                        }
                    }
                },
                required: ["type"],
                not: {
                    required: ["point", "spot", "hemisphere"]
                }
            },
            colorRGB: {
                $id: "#colorRGB",
                type: "array",
                items: {
                    type: "number",
                    minimum: 0,
                    maximum: 1
                },
                minItems: 3,
                maxItems: 3,
                default: [1, 1, 1]
            }
        },
        type: "object",
        properties: {
            asset: {
                type: "object",
                properties: {
                    copyright: {
                        type: "string",
                        description: "A copyright message to credit the content creator."
                    },
                    generator: {
                        type: "string",
                        description: "Tool that generated this presentation description."
                    },
                    version: {
                        type: "string",
                        description: "Version of this presentation description."
                    }
                }
            },
            scene: {
                description: "The root nodes of the scene.",
                type: "object",
                properties: {
                    nodes: {
                        description: "The indices of each root node.",
                        type: "array",
                        items: {
                            type: "integer",
                            minimum: 0
                        },
                        uniqueItems: !0,
                        minItems: 1
                    }
                },
                minItems: 1
            },
            nodes: {
                description: "An array of nodes.",
                type: "array",
                items: {
                    $ref: "#node"
                },
                minItems: 1
            },
            items: {
                description: "An array if items.",
                type: "array",
                items: {
                    $ref: "item.schema.json"
                },
                minItems: 1
            },
            references: {
                description: "An array of references.",
                type: "array",
                items: {
                    $ref: "#reference"
                },
                minItems: 1
            },
            cameras: {
                description: "An array of cameras.",
                type: "array",
                items: {
                    $ref: "#camera"
                },
                minItems: 1
            },
            lights: {
                description: "An array of lights.",
                type: "array",
                items: {
                    $ref: "#light"
                },
                minItems: 1
            },
            story: {
                description: "Presentation-level tours and snapshots",
                $ref: "story.schema.json"
            },
            explorer: {
                description: "Explorer global settings.",
                $ref: "explorer.schema.json"
            }
        },
        required: ["scene", "nodes"]
    }
}, function(e) {
    e.exports = {
        $id: "https://schemas.3d.si.edu/public_api/explorer.schema.json",
        $schema: "http://json-schema.org/draft-07/schema#",
        title: "Smithsonian 3D Presentation - Explorer",
        description: "contains information about viewer configuration (scene environment, UI, etc.)",
        type: "object",
        properties: {
            renderer: {
                type: "object",
                properties: {
                    units: {},
                    shader: {},
                    exposure: {},
                    gamma: {},
                    environment: {}
                }
            },
            reader: {
                type: "object",
                properties: {
                    enabled: {
                        type: "boolean",
                        default: !1
                    },
                    document: {
                        description: "URI of the document currently displayed in the reader",
                        type: "string",
                        minLength: 1
                    }
                }
            },
            tools: {
                $comment: "To be defined"
            }
        }
    }
}, function(e) {
    e.exports = {
        $id: "https://schemas.3d.si.edu/public_api/item.schema.json",
        $schema: "http://json-schema.org/draft-07/schema#",
        title: "3D Item",
        description: "Describes a Smithsonian DPO 3D repository item.",
        type: "object",
        properties: {
            meta: {
                description: "Meta data about this item, including title, record info, collection, etc.",
                $ref: "meta.schema.json"
            },
            process: {
                description: "Information about how this item was digitized and processed.",
                $ref: "process.schema.json"
            },
            model: {
                description: "Describes the visual representations (models, derivatives).",
                $ref: "model.schema.json"
            },
            documents: {
                description: "References to external documents (articles, media files) containing additional information.",
                $ref: "documents.schema.json"
            },
            annotations: {
                description: "Spatial annotations (hot spots, hot zones). Annotations can reference documents.",
                $ref: "annotations.schema.json"
            },
            story: {
                description: "Animated tours and snapshots.",
                $ref: "story.schema.json"
            }
        },
        required: ["meta"]
    }
}, function(e) {
    e.exports = {
        $id: "https://schemas.3d.si.edu/public_api/meta.schema.json",
        $schema: "http://json-schema.org/draft-07/schema#",
        title: "Meta",
        description: "Meta data about a 3D item, including title, record info, collection, etc.",
        type: "object",
        properties: {},
        required: []
    }
}, function(e) {
    e.exports = {
        $id: "https://schemas.3d.si.edu/public_api/process.schema.json",
        $schema: "http://json-schema.org/draft-07/schema#",
        title: "Process",
        description: "Information about how a 3D item was digitized and processed.",
        type: "object",
        properties: {},
        required: []
    }
}, function(e) {
    e.exports = {
        $id: "https://schemas.3d.si.edu/public_api/model.schema.json",
        $schema: "http://json-schema.org/draft-07/schema#",
        title: "Model",
        description: "Describes the visual representations (models, derivatives) of a 3D item.",
        definitions: {
            material: {
                $id: "#material",
                type: "object",
                properties: {}
            },
            asset: {
                description: "an individual resource for a 3D model.",
                $id: "#asset",
                type: "object",
                properties: {
                    uri: {
                        type: "string",
                        minLength: 1
                    },
                    type: {
                        type: "string",
                        enum: ["Model", "Geometry", "Image", "Points", "Volume"]
                    },
                    mimeType: {
                        type: "string",
                        minLength: 1
                    },
                    byteSize: {
                        type: "integer",
                        minimum: 1
                    },
                    numFaces: {
                        type: "integer",
                        minimum: 1
                    },
                    imageSize: {
                        type: "integer",
                        minimum: 1
                    },
                    mapType: {
                        type: "string",
                        enum: ["Color", "Normal", "Occlusion", "Emissive", "MetallicRoughness", "Zone"]
                    }
                },
                required: ["uri", "type"]
            }
        },
        type: "object",
        properties: {
            units: {
                type: "string",
                enum: ["mm", "cm", "m", "in", "ft"]
            },
            transform: {
                description: "Local transformation matrix, defines the 'neutral pose' of this model.",
                $ref: "math.schema.json#/definitions/matrix4"
            },
            boundingBox: {
                description: "Bounding box for this model, shared by all derivatives.",
                $ref: "math.schema.json#/definitions/boundingBox"
            },
            material: {
                description: "Surface properties for this model, shared by all derivatives.",
                $ref: "#material"
            },
            derivatives: {
                description: "List of visual representations derived from the master model.",
                type: "array",
                items: {
                    type: "object",
                    properties: {
                        usage: {
                            description: "usage categories for a derivative.",
                            type: "string",
                            enum: ["Web", "Print", "Editorial"]
                        },
                        quality: {
                            type: "string",
                            enum: ["Thumb", "Low", "Medium", "High", "Highest", "LOD", "Stream"]
                        },
                        assets: {
                            description: "List of individual resources this derivative is composed of.",
                            type: "array",
                            items: {
                                $ref: "#asset"
                            }
                        }
                    }
                }
            }
        },
        required: ["units", "derivatives"]
    }
}, function(e) {
    e.exports = {
        $id: "https://schemas.3d.si.edu/public_api/annotations.schema.json",
        $schema: "http://json-schema.org/draft-07/schema#",
        title: "Annotations",
        description: "Spatial annotations (hot spots, hot zones) on a 3D item. Annotations can reference documents.",
        definitions: {
            annotation: {
                $id: "#annotation",
                type: "object",
                properties: {
                    title: {
                        type: "string"
                    },
                    description: {
                        type: "string"
                    },
                    position: {
                        description: "Position where the annotation is anchored, in local item coordinates.",
                        $ref: "math.schema.json#/definitions/vector3"
                    },
                    direction: {
                        description: "Direction of the stem of this annotation, usually corresponds to the surface normal.",
                        $ref: "math.schema.json#/definitions/vector3"
                    },
                    index: {
                        description: "Index of the zone on the zone texture.",
                        type: "integer",
                        minimum: 0
                    },
                    expanded: {
                        description: "Flag indicating whether this annotation is displayed in expanded state.",
                        type: "boolean",
                        default: !1
                    },
                    snapshot: {
                        description: "The animation state to recall when this annotation is activated.",
                        type: "integer",
                        minimum: 0
                    },
                    documents: {
                        description: "Array of document indices, listing documents related to this annotation.",
                        type: "array",
                        items: {
                            type: "integer",
                            minimum: 0
                        }
                    },
                    groups: {
                        description: "Array of group indices, listing all groups this annotation belongs to.",
                        type: "array",
                        items: {
                            type: "integer",
                            minimum: 0
                        }
                    }
                },
                required: ["title", "position", "direction"]
            },
            group: {
                $id: "#group",
                type: "object",
                properties: {
                    title: {
                        type: "string"
                    },
                    description: {
                        type: "string"
                    }
                },
                required: ["title"]
            }
        },
        type: "object",
        properties: {
            annotations: {
                type: "array",
                items: {
                    $ref: "#annotation"
                }
            },
            groups: {
                type: "array",
                items: {
                    $ref: "#group"
                }
            }
        },
        required: ["annotations"],
        additionalProperties: !1
    }
}, function(e) {
    e.exports = {
        $id: "https://schemas.3d.si.edu/public_api/story.schema.json",
        $schema: "http://json-schema.org/draft-07/schema#",
        title: "Story",
        description: "Animated tours and snapshots for a 3D item.",
        definitions: {
            snapshot: {
                $id: "#snapshot",
                type: "object",
                properties: {
                    title: {
                        type: "string"
                    },
                    description: {
                        type: "string"
                    },
                    properties: {
                        type: "array",
                        items: {
                            properties: {
                                path: {
                                    type: "string",
                                    minLength: 1
                                }
                            },
                            required: ["path", "value"]
                        }
                    }
                }
            },
            tourstep: {
                $id: "#tourstep",
                type: "object",
                properties: {
                    snapshot: {
                        type: "integer",
                        minimum: 0
                    },
                    transitionTime: {
                        type: "number",
                        minimum: 0
                    },
                    transitionCurve: {
                        type: "string"
                    },
                    transitionCutPoint: {
                        type: "number",
                        minimum: 0,
                        maximum: 1
                    }
                },
                required: ["snapshot"]
            }
        },
        type: "object",
        properties: {
            snapshots: {
                type: "array",
                items: {
                    $ref: "#snapshot"
                }
            },
            tours: {
                type: "array",
                items: {
                    type: "object",
                    properties: {
                        title: {
                            type: "string"
                        },
                        description: {
                            type: "string"
                        },
                        steps: {
                            type: "array",
                            items: {
                                $ref: "#tourstep"
                            }
                        }
                    }
                }
            }
        },
        required: []
    }
}, function(e) {
    e.exports = {
        $id: "https://schemas.3d.si.edu/public_api/documents.schema.json",
        $schema: "http://json-schema.org/draft-07/schema#",
        title: "Document",
        description: "Reference to an external document, article or media file.",
        definitions: {
            document: {
                $id: "#document",
                type: "object",
                properties: {
                    title: {
                        type: "string"
                    },
                    description: {
                        type: "string"
                    },
                    mimeType: {
                        type: "string"
                    },
                    uri: {
                        type: "string"
                    }
                },
                required: ["title", "uri"]
            }
        },
        type: "object",
        properties: {
            mainDocument: {
                description: "Index of the main document. This is the default document displayed with the item.",
                type: "integer",
                minimum: 0
            },
            documents: {
                type: "array",
                items: {
                    $ref: "#document"
                }
            }
        },
        required: ["documents"],
        additionalProperties: !1
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(19),
        i = n(17),
        a = n(158),
        o = n(173),
        s = n(26),
        l = n(13),
        c = n(174),
        d = n(175),
        u = n(176),
        h = n(32),
        p = n(33),
        f = n(34),
        m = n(35),
        v = n(177),
        g = n(178),
        y = n(179);
    t.default = class {
        constructor(e, t) {
            const n = this.entity = e.createEntity("Presentation");
            this._sceneComponent = n.createComponent(a.default), n.createComponent(h.default), n.createComponent(p.default), n.createComponent(f.default), this._cameraComponent = null, this.lightsEntity = null, this.items = [], this.presentationUrl = "", this.loaders = t
        }
        get url() {
            return this.presentationUrl
        }
        get path() {
            return r.default(".", this.presentationUrl)
        }
        get cameraComponent() {
            return this._cameraComponent
        }
        get cameraTransform() {
            return this._cameraComponent ? this._cameraComponent.transform : null
        }
        get lightsTransform() {
            return this.lightsEntity ? this.lightsEntity.getComponent(i.default) : null
        }
        get sceneComponent() {
            return this._sceneComponent
        }
        get scene() {
            return this._sceneComponent ? this._sceneComponent.scene : null
        }
        get camera() {
            return this._cameraComponent ? this._cameraComponent.camera : null
        }
        dispose() {
            this.entity.dispose()
        }
        inflate(e, t, n) {
            const r = this.entity,
                i = this._sceneComponent;
            t && (this.presentationUrl = t), e.scene.nodes.forEach(t => {
                const r = e.nodes[t];
                this.inflateNode(i, r, e, n)
            });
            const a = e.explorer || {},
                o = r.getOrCreateComponent(v.default);
            a.renderer && o.fromData(a.renderer);
            const l = r.getOrCreateComponent(g.default);
            return a.reader && l.fromData(a.reader), this._cameraComponent = i.getComponentInSubtree(s.default), this.lightsEntity = i.findEntityInSubtree("Lights"), this
        }
        deflate() {
            const e = {
                    asset: {
                        copyright: "Copyright Smithsonian Institution",
                        generator: "Voyager Presentation Parser",
                        version: "1.0"
                    },
                    scene: {
                        nodes: []
                    }
                },
                t = this._sceneComponent.children;
            return t.length > 0 && (e.nodes = [], t.forEach(t => {
                const n = this.deflateNode(t, e);
                e.scene.nodes.push(n)
            })), e.explorer = {
                renderer: this.entity.getComponent(v.default).toData(),
                reader: this.entity.getComponent(g.default).toData()
            }, e
        }
        inflateNode(e, t, n, a) {
            let l, h, p = !1;
            if (void 0 !== t.reference) {
                const e = n.references[t.reference];
                "application/si-dpo-3d.item+json" === e.mimeType && 0 === Number(e.uri) && a && (l = a.entity, this.items.push(a), p = !0)
            }
            l || (l = e.createEntity("Node"));
            const f = l.getOrCreateComponent(i.default);
            if (f.fromData(t), e.addChild(f), void 0 !== t.item) {
                const e = n.items[t.item],
                    r = new y.default(l, this.loaders).inflate(e, this.path);
                this.items.push(r), h = "Item"
            } else if (void 0 === t.reference || p) {
                if (void 0 !== t.camera) {
                    h = "Camera";
                    const e = n.cameras[t.camera];
                    f.createComponent(s.default).fromData(e)
                } else if (void 0 !== t.light) {
                    h = "Light";
                    const e = n.lights[t.light];
                    "directional" === e.type ? f.createComponent(c.default).fromData(e) : "point" === e.type ? f.createComponent(d.default).fromData(e) : "spot" === e.type && f.createComponent(u.default).fromData(e)
                }
            } else {
                const e = n.references[t.reference];
                if ("application/si-dpo-3d.item+json" === e.mimeType)
                    if (0 === Number(e.uri)) {
                        h = "Reference";
                        const e = n.references[t.reference];
                        f.createComponent(o.default).fromData(e)
                    } else {
                        h = "Item";
                        const i = r.default(e.uri, this.presentationUrl);
                        this.loaders.loadJSON(i).then(e => this.loaders.validateItem(e).then(e => {
                            const t = new y.default(l, this.loaders).inflate(e, i);
                            this.items.push(t)
                        })).catch(e => {
                            console.log(`failed to create item from reference uri: ${e}`), l.name = "Reference";
                            const r = n.references[t.reference];
                            f.createComponent(o.default).fromData(r)
                        })
                    } else {
                    h = "Reference";
                    const e = n.references[t.reference];
                    f.createComponent(o.default).fromData(e)
                }
            }
            l.name = t.name || h || "Entity", t.children && t.children.forEach(e => {
                const t = n.nodes[e];
                this.inflateNode(f, t, n, a)
            })
        }
        deflateNode(e, t) {
            const n = e.toData(),
                r = e.entity;
            r.name && (n.name = r.name), t.nodes.push(n);
            const i = t.nodes.length - 1,
                a = e.getComponent(s.default),
                c = e.getComponent(l.default),
                d = e.getComponent(o.default),
                u = e.getComponent(m.default);
            if (a) t.cameras = t.cameras || [], t.cameras.push(a.toData()), n.camera = t.cameras.length - 1;
            else if (c) t.lights = t.lights || [], t.lights.push(c.toData()), n.light = t.lights.length - 1;
            else if (d) t.references = t.references || [], t.references.push(d.toData()), n.reference = t.references.length - 1;
            else if (u) {
                const r = this.items.find(t => t.entity === e.entity);
                r && (t.items = t.items || [], t.items.push(r.deflate()), n.item = t.items.length - 1)
            }
            const h = e.children;
            return h.length > 0 && (n.children = [], h.forEach(e => {
                const r = this.deflateNode(e, t);
                n.children.push(r)
            })), i
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = new r.Vector3;
    t.default = class extends r.Group {
        constructor(e) {
            super(), this.annotation = e;
            const t = new r.ConeBufferGeometry(.8, 1.6, 24);
            t.translate(0, -.8, 0), t.rotateX(Math.PI);
            const n = new r.MeshPhongMaterial({
                    color: "red"
                }),
                a = new r.Mesh(t, n);
            i.fromArray(e.direction), a.quaternion.setFromUnitVectors(r.Object3D.DefaultUp, i), a.position.fromArray(e.position), this.add(a)
        }
        onPointer(e, t) {
            return e.isPrimary && "up" === e.type && console.log(this.annotation.title), !1
        }
        onTrigger(e, t) {
            return !1
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(160),
        i = n(183),
        a = n(262),
        o = n(157),
        s = n(25),
        l = n(28),
        c = n(23),
        d = n(21),
        u = n(263),
        h = n(264),
        p = n(270),
        f = n(18);
    t.default = class {
        constructor() {
            this.animHandler = 0, console.log("3D Foundation Project"), console.log("(c) 2018 Smithsonian Institution"), console.log("https://3d.si.edu"), this.onAnimationFrame = this.onAnimationFrame.bind(this), this.onLoadingStart = this.onLoadingStart.bind(this), this.onLoadingProgress = this.onLoadingProgress.bind(this), this.onLoadingCompleted = this.onLoadingCompleted.bind(this), this.onLoadingError = this.onLoadingError.bind(this), this.commander = new r.default, this.registry = new i.default, h.registerComponents(this.registry), this.system = new p.default(this.registry), this.context = new u.default, this.main = this.system.createEntity("Main"), this.systemController = this.main.createComponent(o.default), this.systemController.createActions(this.commander), this.renderController = this.main.createComponent(s.default), this.renderController.createActions(this.commander), this.presentationController = this.main.createComponent(l.default), this.presentationController.createActions(this.commander), this.pickManip = this.main.createComponent(c.default), this.renderController.setNextManip(this.pickManip), this.orbitManip = this.main.createComponent(d.default), this.pickManip.next.component = this.orbitManip;
            const e = this.presentationController.loaders.manager;
            e.onStart = this.onLoadingStart, e.onProgress = this.onLoadingProgress, e.onLoad = this.onLoadingCompleted, e.onError = this.onLoadingError
        }
        loadItem(e, t) {
            this.presentationController.closeAll(), this.presentationController.loadItem(e, t)
        }
        loadPresentation(e) {
            this.presentationController.closeAll(), this.presentationController.loadPresentation(e)
        }
        loadModel(e) {
            this.presentationController.closeAll(), this.presentationController.loadModel(e)
        }
        loadGeometryAndTexture(e, t) {
            this.presentationController.closeAll(), this.presentationController.loadGeometryAndTexture(e, t)
        }
        parseArguments(e) {
            const t = a.default("presentation") || e.presentationUrl,
                n = a.default("item") || a.default("i") || e.itemUrl,
                r = a.default("template") || a.default("t") || e.templateUrl,
                i = a.default("model") || a.default("m") || e.modelUrl,
                o = a.default("geometry") || a.default("g") || e.geometryUrl,
                s = a.default("texture") || a.default("tex") || e.textureUrl,
                l = a.default("quality") || a.default("q") || e.quality;
            let c = f.EDerivativeQuality[l];
            c = void 0 !== c ? c : f.EDerivativeQuality.Medium;
            const d = this.presentationController;
            t ? (console.log(`loading presentation from arguments url: ${t}`), d.loadPresentation(t).catch(e => console.error(e))) : n ? (console.log(`loading item from arguments url: ${n}`), d.loadItem(n, r).catch(e => console.error(e))) : i ? (console.log(`loading model from arguments url: ${i}`), d.loadModel(i, c).catch(e => console.error(e))) : o ? (console.log(`loading geometry from arguments url: ${o}`), d.loadGeometryAndTexture(o, s, c).catch(e => console.error(e))) : e.presentation ? (console.log("parsing/opening presentation data from arguments..."), d.openPresentation(e.presentation).catch(e => console.error(e))) : e.item && (console.log("parsing/opening item data from arguments..."), d.openItem(e.item).catch(e => console.error(e)))
        }
        start() {
            0 === this.animHandler && (this.context.start(), this.animHandler = window.requestAnimationFrame(this.onAnimationFrame))
        }
        stop() {
            0 !== this.animHandler && (this.context.stop(), window.cancelAnimationFrame(this.animHandler), this.animHandler = 0)
        }
        renderFrame() {
            this.context.advance(), this.system.update(this.context), this.system.tick(this.context);
            const e = this.presentationController.activePresentation;
            if (!e) return;
            const t = e.scene,
                n = e.camera;
            t && n && this.renderController.renderViews(t, n)
        }
        onAnimationFrame() {
            this.renderFrame(), this.animHandler = window.requestAnimationFrame(this.onAnimationFrame)
        }
        onLoadingStart() {
            console.log("Loading files...")
        }
        onLoadingProgress(e, t, n) {
            console.log(`Loaded ${t} of ${n} files: ${e}`)
        }
        onLoadingCompleted() {
            console.log("Loading completed")
        }
        onLoadingError() {
            console.error("Loading error")
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    }), t.default = function(e, t) {
        t || (t = window.location.href), e = e.replace(/[\[\]]/g, "\\$&");
        const n = new RegExp("[?&]" + e + "(=([^&#]*)|&|#|$)").exec(t);
        if (n) return n[2] ? decodeURIComponent(n[2].replace(/\+/g, " ")) : ""
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    t.default = class {
        constructor() {
            this.reset()
        }
        start() {
            this._secondsStopped > 0 && (this._secondsStarted += .001 * Date.now() - this._secondsStopped, this._secondsStopped = 0)
        }
        stop() {
            0 === this._secondsStopped && (this._secondsStopped = .001 * Date.now())
        }
        advance() {
            this.time = new Date;
            const e = .001 * this.time.valueOf() - this._secondsStarted;
            this.secondsDelta = e - this.secondsElapsed, this.secondsElapsed = e, this.frameNumber++
        }
        reset() {
            this.time = new Date, this.secondsElapsed = 0, this.secondsDelta = 0, this.frameNumber = 0, this._secondsStarted = .001 * Date.now(), this._secondsStopped = this._secondsStarted
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(36),
        i = n(181),
        a = n(26),
        o = n(8),
        s = n(14),
        l = n(174),
        c = n(32),
        d = n(28),
        u = n(33),
        h = n(11),
        p = n(265),
        f = n(13),
        m = n(15),
        v = n(267),
        g = n(35),
        y = n(16),
        _ = n(5),
        x = n(21),
        E = n(23),
        b = n(175),
        P = n(269),
        w = n(180),
        S = n(178),
        M = n(173),
        L = n(25),
        C = n(177),
        T = n(158),
        D = n(182),
        R = n(176),
        A = n(157),
        I = n(34),
        O = n(17);
    t.registerComponents = function(e) {
        e.registerComponentType([r.default, i.default, a.default, o.default, s.default, l.default, c.default, d.default, u.default, h.default, p.default, f.default, m.default, v.default, g.default, y.default, _.default, x.default, E.default, b.default, P.default, w.default, S.default, M.default, L.default, C.default, T.default, D.default, R.default, A.default, I.default, O.default])
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(4),
        i = n(266),
        a = n(5);
    class o extends a.default {
        constructor() {
            super(...arguments), this.ins = this.makeProps({
                sca: r.default.Number("Scale", 1)
            })
        }
        create() {
            super.create(), this.object3D = new i.default({
                size: 20,
                mainDivisions: 2,
                subDivisions: 10,
                mainColor: "#c0c0c0",
                subColor: "#606060"
            })
        }
        update() {}
        render(e) {}
        get grid() {
            return this.object3D
        }
    }
    o.type = "Grid", t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0);
    t.default = class extends r.LineSegments {
        constructor(e) {
            const t = new r.Color(e.mainColor),
                n = new r.Color(e.subColor),
                i = e.mainDivisions * e.subDivisions,
                a = e.size / i,
                o = .5 * e.size,
                s = [],
                l = [];
            for (let r = 0, c = 0, d = -o; r <= i; ++r, d += a) {
                s.push(-o, 0, d, o, 0, d), s.push(d, 0, -o, d, 0, o);
                const i = r % e.subDivisions == 0 ? t : n;
                i.toArray(l, c), c += 3, i.toArray(l, c), c += 3, i.toArray(l, c), c += 3, i.toArray(l, c), c += 3
            }
            const c = new r.BufferGeometry;
            c.addAttribute("position", new r.Float32BufferAttribute(s, 3)), c.addAttribute("color", new r.Float32BufferAttribute(l, 3)), super(c, new r.LineBasicMaterial({
                vertexColors: r.VertexColors
            }))
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(4),
        a = n(268),
        o = n(5);
    class s extends o.default {
        constructor() {
            super(...arguments), this.ins = this.makeProps({
                geo: i.default.Object("Geometry", a.GeometryObject),
                mat: i.default.Object("Material", a.MaterialObject)
            })
        }
        create() {
            super.create(), this.object3D = new r.Mesh, this.object3D.matrixAutoUpdate = !1
        }
        update() {
            const {
                geo: e,
                mat: t
            } = this.ins;
            e.changed && (this.mesh.geometry = e.value.object), t.changed && (this.mesh.material = t.value.object)
        }
        get mesh() {
            return this.object3D
        }
    }
    s.type = "Mesh", t.default = s
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(165);
    class i extends r.default {}
    i.type = "Geometry", t.GeometryObject = i;
    class a extends r.default {}
    a.type = "Material", t.MaterialObject = a
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(0),
        i = n(4),
        a = n(15);
    class o extends a.default {
        constructor() {
            super(...arguments), this.outs = this.makeProps({
                mat: i.default.Matrix4("Matrix")
            }), this.matrix = new r.Matrix4, this.viewportWidth = 100, this.viewportHeight = 100
        }
        update() {}
        tick() {}
        onPointer(e) {
            return super.onPointer(e)
        }
        onTrigger(e) {
            return super.onTrigger(e)
        }
        setFromMatrix(e) {
            this.matrix.copy(e)
        }
    }
    o.type = "PoseManip", t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(192);
    t.default = class extends r.default {
        constructor() {
            super(...arguments), this.renderables = []
        }
        render(e) {
            const t = this.renderables;
            for (let n = 0, r = t.length; n < r; ++n) t[n].render(e)
        }
        didAddComponent(e) {
            e.render && this.renderables.push(e)
        }
        willRemoveComponent(e) {
            const t = this.renderables.indexOf(e);
            t >= 0 && this.renderables.splice(t, 1)
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    t.default = class {
        constructor() {
            this.visited = {}, this.visiting = {}, this.sorted = []
        }
        sort(e) {
            for (let t = 0, n = e.length; t < n; ++t) this.visit(e[t]);
            const t = this.sorted;
            return this.visited = {}, this.visiting = {}, this.sorted = [], t
        }
        visit(e) {
            const t = this.visited,
                n = this.visiting;
            if (t[e.id] || n[e.id]) return;
            n[e.id] = !0;
            const r = e.outs.properties;
            for (let e = 0, t = r.length; e < t; ++e) {
                const t = r[e].outLinks;
                for (let e = 0, n = t.length; e < n; ++e) {
                    const n = t[e].destination.props,
                        r = n.properties;
                    for (let e = 0, t = r.length; e < t; ++e) {
                        const t = r[e].outLinks;
                        for (let e = 0, n = t.length; e < n; ++e) {
                            const n = t[e].destination.props;
                            this.visit(n.linkable)
                        }
                    }
                    this.visit(n.linkable)
                }
            }
            n[e.id] = void 0, t[e.id] = !0, this.sorted.unshift(e)
        }
    }
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = n(0),
        a = n(273),
        o = n(274),
        s = n(275),
        l = n(156),
        c = n(25),
        d = n(276),
        u = n(277);
    class h extends r.Component {
        constructor(e) {
            super(e), this.renderer = null, this.viewportLayout = null, this.canvasWidth = 0, this.canvasHeight = 0, this.onCanvas = this.onCanvas.bind(this), this.onCanvasResize = this.onCanvasResize.bind(this), this.onQuadSplitChange = this.onQuadSplitChange.bind(this), this.containerRef = r.createRef()
        }
        get container() {
            return this.containerRef.current
        }
        componentDidMount() {
            const e = this.props.system.getComponent(c.default);
            e && (this.viewportLayout = e.registerView(this), this.viewportLayout.on("layout", this.onLayout, this), this.forceUpdate())
        }
        componentWillUnmount() {
            const e = this.props.system.getComponent(c.default);
            e && (e.unregisterView(this), this.viewportLayout.off("layout", this.onLayout, this), this.viewportLayout = null)
        }
        render() {
            const {
                className: e,
                system: t
            } = this.props, n = this.viewportLayout, i = n ? n.layoutMode : l.EViewportLayoutMode.Single, c = n ? n.horizontalSplit : .5, h = n ? n.verticalSplit : .5;
            return r.createElement(s.default, {
                className: e,
                handler: this
            }, r.createElement(o.default, {
                onCanvas: this.onCanvas,
                onResize: this.onCanvasResize
            }), r.createElement(a.default, {
                ref: this.containerRef
            }), r.createElement(u.default, {
                system: t
            }), r.createElement(d.default, {
                mode: i,
                horizontalSplit: c,
                verticalSplit: h,
                onChange: this.onQuadSplitChange
            }), r.createElement("div", {
                className: "sv-logo"
            }, r.createElement("img", {
                src: "/lib/javascripts/voyager/images/si-dpo3d-logo-neg.svg"
            })))
        }
        onPointer(e) {
            return !!this.viewportLayout && this.viewportLayout.onPointer(e)
        }
        onTrigger(e) {
            return !!this.viewportLayout && this.viewportLayout.onTrigger(e)
        }
        onCanvas(e) {
            this.renderer && (this.renderer.dispose(), this.renderer = null), e.canvas && (this.renderer = new i.WebGLRenderer({
                canvas: e.canvas,
                antialias: !0
            }), this.renderer.autoClear = !1)
        }
        onCanvasResize(e) {
            this.canvasWidth = e.width, this.canvasHeight = e.height, this.renderer && this.renderer.setSize(e.width, e.height, !1), this.viewportLayout && this.viewportLayout.setCanvasSize(e.width, e.height)
        }
        onLayout(e) {
            this.forceUpdate()
        }
        onQuadSplitChange(e) {
            this.viewportLayout && this.viewportLayout.setSplit(e.horizontalSplit, e.verticalSplit)
        }
    }
    h.defaultProps = {
        className: "sv-explorer-view"
    }, t.default = h
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1);
    class i extends r.Component {
        constructor() {
            super(...arguments), this.onRenderContent = null
        }
        render() {
            const {
                className: e,
                style: t
            } = this.props, n = this.onRenderContent ? this.onRenderContent() : null;
            return r.createElement("div", {
                className: e,
                style: t
            }, n)
        }
    }
    i.defaultProps = {
        className: "ff-container"
    }, t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1);
    class i extends r.Component {
        constructor(e) {
            super(e), this.onRef = this.onRef.bind(this), this.onResize = this.onResize.bind(this), this.canvas = null
        }
        render() {
            const {
                className: e,
                style: t
            } = this.props;
            return r.createElement("div", {
                className: e,
                style: t
            }, r.createElement("canvas", {
                style: i.style,
                ref: this.onRef
            }))
        }
        onRef(e) {
            this.canvas = e, e ? window.addEventListener("resize", this.onResize) : window.removeEventListener("resize", this.onResize);
            const {
                id: t,
                index: n,
                onCanvas: r
            } = this.props;
            r && r({
                canvas: e,
                id: t,
                index: n,
                sender: this
            }), this.onResize()
        }
        onResize() {
            const e = this.canvas;
            if (!e) return;
            const t = e.clientWidth,
                n = e.clientHeight,
                {
                    id: r,
                    index: i,
                    onResize: a
                } = this.props;
            a && a({
                canvas: e,
                width: t,
                height: n,
                id: r,
                index: i,
                sender: this
            })
        }
    }
    i.defaultProps = {
        className: "ff-canvas"
    }, i.style = {
        display: "block",
        width: "100%",
        height: "100%"
    }, t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1);
    class i extends r.Component {
        constructor(e) {
            super(e), this.onPointerDown = this.onPointerDown.bind(this), this.onPointerMove = this.onPointerMove.bind(this), this.onPointerUpOrCancel = this.onPointerUpOrCancel.bind(this), this.onDoubleClick = this.onDoubleClick.bind(this), this.onContextMenu = this.onContextMenu.bind(this), this.onWheel = this.onWheel.bind(this), this.elementRef = r.createRef(), this.activePointers = {}, this.activeType = "", this.downPointerCount = 0, this.centerX = 0, this.centerY = 0
        }
        render() {
            const {
                className: e,
                children: t
            } = this.props, n = {
                className: e,
                ref: this.elementRef,
                "touch-action": "none",
                onPointerDown: this.onPointerDown,
                onPointerMove: this.onPointerMove,
                onPointerUp: this.onPointerUpOrCancel,
                onPointerCancel: this.onPointerUpOrCancel,
                onDoubleClick: this.onDoubleClick,
                onContextMenu: this.onContextMenu,
                onWheel: this.onWheel
            };
            return r.createElement("div", n, t)
        }
        onPointerDown(e) {
            if (this.activeType && e.pointerType !== this.activeType) return;
            this.activeType = e.pointerType, this.activePointers[e.pointerId] = e, this.downPointerCount++, !1 !== this.props.capture && this.elementRef.current.setPointerCapture(e.pointerId);
            const t = this.createManipPointerEvent(e, "down");
            this.sendPointerEvent(t) && (e.stopPropagation(), e.preventDefault())
        }
        onPointerMove(e) {
            this.activePointers[e.pointerId] = e;
            const t = this.createManipPointerEvent(e, "move");
            this.sendPointerEvent(t) && (e.stopPropagation(), e.preventDefault())
        }
        onPointerUpOrCancel(e) {
            this.activePointers[e.pointerId] = e, this.downPointerCount--;
            const t = this.createManipPointerEvent(e, "up");
            0 === t.activePointerCount && (this.activeType = "", this.downPointerCount = 0), this.sendPointerEvent(t) && (e.stopPropagation(), e.preventDefault()), this.activePointers[e.pointerId] = void 0
        }
        onDoubleClick(e) {
            this.sendTriggerEvent(this.createManipTriggerEvent(e, "dblclick")) && e.preventDefault()
        }
        onContextMenu(e) {
            this.sendTriggerEvent(this.createManipTriggerEvent(e, "contextmenu")), e.preventDefault()
        }
        onWheel(e) {
            this.sendTriggerEvent(this.createManipTriggerEvent(e, "wheel")) && e.preventDefault()
        }
        createManipPointerEvent(e, t) {
            let n = 0,
                r = 0,
                i = 0,
                a = [];
            for (let e in this.activePointers) {
                const t = this.activePointers[e];
                t && (a.push(t), i++, n += t.clientX, r += t.clientY)
            }
            r /= i;
            let o = (n /= i) - this.centerX,
                s = r - this.centerY;
            return "down" !== t && "up" !== t || (o = 0, s = 0), this.centerX = n, this.centerY = r, {
                originalEvent: e,
                type: t,
                source: e.pointerType,
                sender: this,
                id: this.props.id,
                index: this.props.index,
                isPrimary: e.isPrimary,
                activePointerList: a,
                activePointerCount: i,
                downPointerCount: this.downPointerCount,
                centerX: n,
                centerY: r,
                movementX: o,
                movementY: s,
                shiftKey: e.shiftKey,
                ctrlKey: e.ctrlKey,
                altKey: e.altKey,
                metaKey: e.metaKey
            }
        }
        createManipTriggerEvent(e, t) {
            let n = 0;
            return "wheel" === t && (n = e.deltaY), {
                originalEvent: e,
                sender: this,
                id: this.props.id,
                index: this.props.index,
                type: t,
                wheel: n,
                centerX: e.clientX,
                centerY: e.clientY,
                shiftKey: e.shiftKey,
                ctrlKey: e.ctrlKey,
                altKey: e.altKey,
                metaKey: e.metaKey
            }
        }
        sendPointerEvent(e) {
            const t = this.props;
            return t.handler ? t.handler.onPointer(e) : t.onPointer ? t.onPointer(e) : void 0
        }
        sendTriggerEvent(e) {
            const t = this.props;
            return t.handler ? t.handler.onTrigger(e) : t.onTrigger ? t.onTrigger(e) : void 0
        }
    }
    i.defaultProps = {
        className: "ff-manip-target"
    }, t.default = i
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = n(201),
        a = n(156);
    class o extends r.Component {
        constructor(e) {
            super(e), this.onHorizontalResize = this.onHorizontalResize.bind(this), this.onVerticalResize = this.onVerticalResize.bind(this), this.horizontalSplit = e.horizontalSplit, this.verticalSplit = e.verticalSplit
        }
        render() {
            const {
                className: e,
                mode: t,
                horizontalSplit: n,
                verticalSplit: o
            } = this.props, s = t === a.EViewportLayoutMode.HorizontalSplit || t === a.EViewportLayoutMode.Quad, l = t === a.EViewportLayoutMode.VerticalSplit || t === a.EViewportLayoutMode.Quad;
            return r.createElement("div", {
                className: e
            }, s ? r.createElement(i.SplitterContainer, {
                direction: "horizontal",
                onResize: this.onHorizontalResize
            }, r.createElement(i.SplitterSection, {
                size: n
            }), r.createElement(i.SplitterSection, {
                size: 1 - n
            })) : null, l ? r.createElement(i.SplitterContainer, {
                direction: "vertical",
                onResize: this.onVerticalResize
            }, r.createElement(i.SplitterSection, {
                size: o
            }), r.createElement(i.SplitterSection, {
                size: 1 - o
            })) : null)
        }
        onHorizontalResize(e) {
            this.horizontalSplit = e.sizes[0], this.emitChange(e.isDragging)
        }
        onVerticalResize(e) {
            this.verticalSplit = e.sizes[0], this.emitChange(e.isDragging)
        }
        emitChange(e) {
            const {
                id: t,
                index: n,
                mode: r,
                onChange: i
            } = this.props;
            i && i({
                id: t,
                index: n,
                mode: r,
                horizontalSplit: this.horizontalSplit,
                verticalSplit: this.verticalSplit,
                isDragging: e,
                sender: this
            })
        }
    }
    o.defaultProps = {
        className: "sv-quad-split-overlay",
        mode: a.EViewportLayoutMode.Single,
        horizontalSplit: .5,
        verticalSplit: .5
    }, t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = n(10),
        a = n(202),
        o = n(278);
    class s extends r.Component {
        constructor(e) {
            super(e)
        }
        render() {
            const {
                className: e,
                system: t
            } = this.props;
            return r.createElement(i.default, {
                className: e,
                position: "fill",
                direction: "vertical"
            }, r.createElement(i.default, {
                direction: "horizontal"
            }, r.createElement(o.default, {
                system: t,
                portal: this
            }), r.createElement(a.default, null)), r.createElement(a.default, null))
        }
    }
    s.defaultProps = {
        className: "explorer-overlay-view"
    }, t.default = s
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = n(10),
        a = n(279),
        o = n(281),
        s = n(283);
    class l extends r.Component {
        render() {
            const {
                className: e,
                system: t,
                portal: n
            } = this.props;
            return r.createElement(i.default, {
                className: e,
                direction: "horizontal"
            }, r.createElement(a.default, {
                portal: n,
                anchor: "bottom",
                modal: !0,
                icon: "fas fa-eye",
                title: "View/Projection Settings"
            }, r.createElement(o.default, {
                className: "sv-explorer-popup-menu sv-viewport-menu",
                system: t
            })), r.createElement(a.default, {
                portal: n,
                anchor: "bottom",
                modal: !0,
                icon: "fas fa-paint-brush",
                title: "Render Mode"
            }, r.createElement(s.default, {
                className: "sv-explorer-popup-menu sv-render-menu",
                system: t
            })))
        }
    }
    l.defaultProps = {
        className: "sv-popup-menu-bar"
    }, t.default = l
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = n(22),
        a = n(280);
    class o extends r.Component {
        constructor(e) {
            super(e), this.onRefDialog = this.onRefDialog.bind(this), this.onTapButton = this.onTapButton.bind(this), this.onTapModal = this.onTapModal.bind(this), this.dialog = null, this.state = {
                popupVisible: !1
            }
        }
        render() {
            const e = this.props;
            return r.createElement(a.default, {
                ref: this.onRefDialog,
                id: e.id,
                anchor: e.anchor,
                justify: e.justify,
                align: e.align,
                portal: e.portal,
                modal: e.modal,
                visible: this.state.popupVisible,
                onTapModal: this.onTapModal
            }, r.createElement(a.Anchor, null, r.createElement(i.default, {
                id: e.id,
                className: e.className,
                text: e.text,
                icon: e.icon,
                faIcon: e.faIcon,
                image: e.image,
                title: e.title,
                disabled: e.disabled,
                onTap: this.onTapButton
            })), e.children)
        }
        onRefDialog(e) {
            this.dialog = e
        }
        onTapButton(e) {
            this.setState(e => ({
                popupVisible: !e.popupVisible
            }))
        }
        onTapModal(e) {
            this.setState(e => {
                const t = !e.popupVisible;
                return !t && this.dialog && this.dialog.anchorElement && this.dialog.anchorElement.focus(), {
                    popupVisible: t
                }
            })
        }
    }
    o.defaultProps = {
        className: "ff-popup-button"
    }, t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = n(159);
    class a extends r.Component {
        render() {
            const e = r.Children.only(this.props.children);
            return r.cloneElement(e, {
                ref: this.props.elementRef
            })
        }
    }
    t.Anchor = a;
    class o extends r.Component {
        constructor(e) {
            super(e), this.onRefAnchor = this.onRefAnchor.bind(this), this.onRefDialog = this.onRefDialog.bind(this), this.onRefModalPlane = this.onRefModalPlane.bind(this), this.onModalPlaneDown = this.onModalPlaneDown.bind(this), this.onModalPlaneKeyPress = this.onModalPlaneKeyPress.bind(this), this.calculateLayout = this.calculateLayout.bind(this), this.dialogElement = null, this.anchorElement = null, this.modalElement = null, this.portalElement = null, this.parentElement = document.createElement("div")
        }
        componentDidMount() {
            if (document.body.appendChild(this.parentElement), window.addEventListener("resize", this.calculateLayout), this.props.portal) {
                const e = i.findDOMNode(this.props.portal);
                e instanceof HTMLElement && (this.portalElement = e)
            }
        }
        componentDidUpdate() {
            this.calculateLayout()
        }
        componentWillUnmount() {
            document.body.removeChild(this.parentElement), window.removeEventListener("resize", this.calculateLayout), this.portalElement = null, this.anchorElement = null
        }
        render() {
            const {
                className: e,
                style: t,
                visible: n,
                modal: s,
                children: l
            } = this.props, c = Object.assign({}, o.style, t), d = r.Children.toArray(l);
            let u = null,
                h = d;
            d.length > 0 && d[0].type === a && (u = r.cloneElement(d[0], {
                elementRef: this.onRefAnchor
            }), h = d.slice(1));
            let p = null;
            if (n) {
                const t = r.createElement("div", {
                    ref: this.onRefDialog,
                    className: e,
                    style: c
                }, h);
                p = s ? r.createElement("div", {
                    ref: this.onRefModalPlane,
                    className: e + " ff-modal",
                    style: o.modalStyle,
                    onPointerDown: this.onModalPlaneDown,
                    onKeyUp: this.onModalPlaneKeyPress
                }, t) : t
            }
            const f = n ? i.createPortal(p, this.parentElement) : null;
            return r.createElement(r.Fragment, null, u, f)
        }
        onRefAnchor(e) {
            if (e) {
                const t = i.findDOMNode(e);
                t instanceof HTMLElement && (this.anchorElement = t)
            } else this.anchorElement = null
        }
        onRefDialog(e) {
            this.dialogElement = e
        }
        onRefModalPlane(e) {
            this.modalElement = e
        }
        onModalPlaneDown(e) {
            if (e.target !== this.modalElement) return;
            const {
                id: t,
                index: n,
                onTapModal: r
            } = this.props;
            r && r({
                id: t,
                index: n,
                sender: this
            })
        }
        onModalPlaneKeyPress(e) {
            const {
                id: t,
                index: n,
                onTapModal: r
            } = this.props;
            27 === e.keyCode && r && r({
                id: t,
                index: n,
                sender: this
            })
        }
        calculateLayout() {
            const e = this.dialogElement;
            if (!e) return;
            const t = e.getBoundingClientRect();
            let {
                anchor: n,
                justify: r,
                align: i
            } = this.props, a = 0, o = 0;
            const s = "start" === r ? 0 : "end" === r ? t.width : .5 * t.width,
                l = "start" === i ? 0 : "end" === i ? t.height : .5 * t.height;
            if (this.anchorElement) {
                const e = this.anchorElement.getBoundingClientRect();
                let c = 0,
                    d = 0;
                switch (r || (r = "left" === n ? "start" : "right" === n ? "start" : "center"), i || (i = "top" === n ? "start" : "bottom" === n ? "start" : "center"), r) {
                    case "start":
                        c = 0;
                        break;
                    case "end":
                        c = e.width;
                        break;
                    case "center":
                        c = .5 * e.width
                }
                switch (i) {
                    case "start":
                        d = 0;
                        break;
                    case "end":
                        d = e.height;
                        break;
                    case "center":
                        d = .5 * e.height
                }
                switch (n) {
                    case "left":
                        a = e.left - t.width, o = e.top + d - l;
                        break;
                    case "right":
                        a = e.right, o = e.top + d - l;
                        break;
                    case "top":
                        a = e.left + c - s, o = e.top - t.height;
                        break;
                    case "bottom":
                        a = e.left + c - s, o = e.bottom
                }
            } else {
                switch (r) {
                    case "start":
                        a = 0;
                        break;
                    case "end":
                        a = window.innerWidth - t.width;
                        break;
                    default:
                        a = .5 * (window.innerWidth - t.width)
                }
                switch (i) {
                    case "start":
                        o = 0;
                        break;
                    case "end":
                        o = window.innerHeight - t.height;
                        break;
                    default:
                        o = .5 * (window.innerHeight - t.height)
                }
            }
            if (this.portalElement) {
                const e = this.portalElement.getBoundingClientRect();
                a = Math.max(e.left, a), a = Math.min(e.right - t.width, a), o = Math.max(e.top, o), o = Math.min(e.bottom - t.height, o)
            }
            e.style.left = a + "px", e.style.top = o + "px"
        }
    }
    o.defaultProps = {
        className: "ff-dialog"
    }, o.style = {
        position: "absolute",
        zIndex: 1e3
    }, o.modalStyle = {
        position: "absolute",
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        zIndex: 1e3
    }, t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = n(10),
        a = n(282),
        o = n(161),
        s = n(22),
        l = n(21),
        c = n(157);
    class d extends r.Component {
        constructor(e) {
            super(e), this.onSelectProjection = this.onSelectProjection.bind(this), this.onSelectViewPreset = this.onSelectViewPreset.bind(this), this.controller = e.system.getComponent(c.default)
        }
        componentDidMount() {
            this.controller.addOutputListener(l.default, "View.Projection", this.onProjectionChanged, this), this.controller.addOutputListener(l.default, "View.Preset", this.onPresetChanged, this)
        }
        componentWillUnmount() {
            this.controller.removeOutputListener(l.default, "View.Projection", this.onProjectionChanged, this), this.controller.removeOutputListener(l.default, "View.Preset", this.onPresetChanged, this)
        }
        render() {
            const e = this.controller.getOutputValue(l.default, "View.Preset"),
                t = this.controller.getOutputValue(l.default, "View.Projection");
            return r.createElement(i.default, {
                className: this.props.className,
                direction: "vertical"
            }, r.createElement(o.default, {
                text: "Projection"
            }), r.createElement(i.default, {
                direction: "horizontal"
            }, r.createElement(s.default, {
                index: l.EProjectionType.Perspective,
                text: "Perspective",
                icon: "fas fa-video",
                title: "Perspective Projection",
                selected: t === l.EProjectionType.Perspective,
                focused: t === l.EProjectionType.Perspective,
                onTap: this.onSelectProjection
            }), r.createElement(s.default, {
                index: l.EProjectionType.Orthographic,
                text: "Orthographic",
                icon: "fas fa-video",
                title: "Orthographic Projection",
                selected: t === l.EProjectionType.Orthographic,
                focused: t === l.EProjectionType.Orthographic,
                onTap: this.onSelectProjection
            })), r.createElement(o.default, {
                text: "View"
            }), r.createElement(a.default, {
                className: "sv-cube-group",
                justifyContent: "center"
            }, r.createElement(s.default, {
                index: l.EViewPreset.Top,
                className: "ff-control ff-button sv-cube",
                text: "T",
                title: "Top View",
                selected: e === l.EViewPreset.Top,
                style: {
                    gridColumnStart: 2,
                    gridRowStart: 1
                },
                onTap: this.onSelectViewPreset
            }), r.createElement(s.default, {
                index: l.EViewPreset.Left,
                className: "ff-control ff-button sv-cube",
                text: "L",
                title: "Left View",
                selected: e === l.EViewPreset.Left,
                style: {
                    gridColumnStart: 1,
                    gridRowStart: 2
                },
                onTap: this.onSelectViewPreset
            }), r.createElement(s.default, {
                index: l.EViewPreset.Front,
                className: "ff-control ff-button sv-cube",
                text: "F",
                title: "Front View",
                selected: e === l.EViewPreset.Front,
                style: {
                    gridColumnStart: 2,
                    gridRowStart: 2
                },
                onTap: this.onSelectViewPreset
            }), r.createElement(s.default, {
                index: l.EViewPreset.Right,
                className: "ff-control ff-button sv-cube",
                text: "R",
                title: "Right View",
                selected: e === l.EViewPreset.Right,
                style: {
                    gridColumnStart: 3,
                    gridRowStart: 2
                },
                onTap: this.onSelectViewPreset
            }), r.createElement(s.default, {
                index: l.EViewPreset.Back,
                className: "ff-control ff-button sv-cube",
                text: "B",
                title: "Back View",
                selected: e === l.EViewPreset.Back,
                style: {
                    gridColumnStart: 4,
                    gridRowStart: 2
                },
                onTap: this.onSelectViewPreset
            }), r.createElement(s.default, {
                index: l.EViewPreset.Bottom,
                className: "ff-control ff-button sv-cube",
                text: "B",
                title: "Bottom View",
                selected: e === l.EViewPreset.Bottom,
                style: {
                    gridColumnStart: 2,
                    gridRowStart: 3
                },
                onTap: this.onSelectViewPreset
            })))
        }
        onSelectProjection(e) {
            this.controller.actions.setInputValue(l.default, "View.Projection", e.index)
        }
        onSelectViewPreset(e) {
            this.controller.actions.setInputValue(l.default, "View.Preset", e.index), this.setState({
                viewPreset: e.index
            })
        }
        onProjectionChanged() {
            this.forceUpdate()
        }
        onPresetChanged() {
            this.forceUpdate()
        }
    }
    d.defaultProps = {
        className: "sv-viewport-menu"
    }, t.default = d
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = {
            boxSizing: "border-box",
            display: "grid"
        },
        a = function(e) {
            return "number" == typeof e ? e <= 1 ? (100 * e).toString() + "%" : e.toString() + "px" : e
        },
        o = function(e) {
            const {
                className: t,
                style: n,
                position: o,
                columns: s,
                rows: l,
                autoColumns: c,
                autoRows: d,
                columnGap: u,
                rowGap: h,
                justifyItems: p,
                alignItems: f,
                justifyContent: m,
                alignContent: v,
                children: g
            } = e, y = Object.assign({}, i, n);
            switch (o) {
                case "fill":
                    y.position = "absolute", y.top = 0, y.right = 0, y.bottom = 0, y.left = 0;
                    break;
                case "relative":
                    y.position = "relative";
                    break;
                case "absolute":
                    y.position = "absolute"
            }
            return s && (y.gridTemplateColumns = s), l && (y.gridTemplateRows = l), c && (y.gridAutoColumns = a(c)), d && (y.gridAutoRows = a(d)), u && (y.gridColumnGap = a(u)), h && (y.gridRowGap = a(h)), p && (y.justifyItems = p), f && (y.alignItems = f), m && (y.justifyContent = m), v && (y.alignContent = v), r.createElement("div", {
                className: t,
                style: y
            }, g)
        };
    o.defaultProps = {
        className: "ff-grid-container"
    }, t.default = o
}, function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    });
    const r = n(1),
        i = n(10),
        a = n(161),
        o = n(22),
        s = n(158),
        l = n(28);
    class c extends r.Component {
        constructor(e) {
            super(e), this.onSelectShaderMode = this.onSelectShaderMode.bind(this), this.controller = e.system.getComponent(l.default)
        }
        componentDidMount() {
            this.controller.addInputListener(s.default, "Shader.Mode", this.onShaderModeChanged, this)
        }
        componentWillUnmount() {
            this.controller.removeInputListener(s.default, "Shader.Mode", this.onShaderModeChanged, this)
        }
        render() {
            const e = this.controller.getInputValue(s.default, "Shader.Mode");
            return r.createElement(i.default, {
                className: this.props.className,
                direction: "vertical"
            }, r.createElement(a.default, {
                text: "Render mode"
            }), r.createElement(o.default, {
                index: s.EShaderMode.Default,
                text: "Standard",
                title: "Display model in standard mode",
                selected: e === s.EShaderMode.Default,
                focused: e === s.EShaderMode.Default,
                onTap: this.onSelectShaderMode
            }), r.createElement(o.default, {
                index: s.EShaderMode.Clay,
                text: "Clay",
                title: "Display model without colors",
                selected: e === s.EShaderMode.Clay,
                focused: e === s.EShaderMode.Clay,
                onTap: this.onSelectShaderMode
            }), r.createElement(o.default, {
                index: s.EShaderMode.XRay,
                text: "X-Ray",
                title: "Display model in X-Ray mode",
                selected: e === s.EShaderMode.XRay,
                focused: e === s.EShaderMode.XRay,
                onTap: this.onSelectShaderMode
            }), r.createElement(o.default, {
                index: s.EShaderMode.Normals,
                text: "Normals",
                title: "Display normals",
                selected: e === s.EShaderMode.Normals,
                focused: e === s.EShaderMode.Normals,
                onTap: this.onSelectShaderMode
            }), r.createElement(o.default, {
                index: s.EShaderMode.Wireframe,
                text: "Wireframe",
                title: "Display model as wireframe",
                selected: e === s.EShaderMode.Wireframe,
                focused: e === s.EShaderMode.Wireframe,
                onTap: this.onSelectShaderMode
            }))
        }
        onSelectShaderMode(e) {
            this.controller.actions.setInputValue(s.default, "Shader.Mode", e.index)
        }
        onShaderModeChanged(e) {
            this.forceUpdate()
        }
    }
    c.defaultProps = {
        className: "sv-render-menu"
    }, t.default = c
}, , , , , , , , , function(e, t, n) {
    "use strict";
    Object.defineProperty(t, "__esModule", {
        value: !0
    }), n(293);
    const r = n(1),
        i = n(159),
        a = n(272),
        o = n(261);
    class s extends o.default {
        constructor(e) {
            console.log("Voyager Explorer"), super(), this.start(), this.parseArguments(e), i.render(r.createElement(a.default, {
                system: this.system
            }), e.element)
        }
    }
    t.default = s, window.Voyager = s
}, function(e, t, n) {}]);