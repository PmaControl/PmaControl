"object" !== typeof JSON && (JSON = {});
(function() {
    function n(a) {
        return 10 > a ? "0" + a : a
    }
    function q(a) {
        c.lastIndex = 0;
        return c.test(a) ? '"' + a.replace(c, function(a) {
            var b = g[a];
            return"string" === typeof b ? b : "\\u" + ("0000" + a.charCodeAt(0).toString(16)).slice(-4)
        }) + '"' : '"' + a + '"'
    }
    function k(a, c) {
        var f, h, p, g, r = b, l, e = c[a];
        e && "object" === typeof e && "function" === typeof e.toJSON && (e = e.toJSON(a));
        "function" === typeof m && (e = m.call(c, a, e));
        switch (typeof e) {
            case "string":
                return q(e);
            case "number":
                return isFinite(e) ? String(e) : "null";
            case "boolean":
            case "null":
                return String(e);
            case "object":
                if (!e)
                    return"null";
                b += d;
                l = [];
                if ("[object Array]" === Object.prototype.toString.apply(e)) {
                    g = e.length;
                    for (f = 0; f < g; f += 1)
                        l[f] = k(f, e) || "null";
                    p = 0 === l.length ? "[]" : b ? "[\n" + b + l.join(",\n" + b) + "\n" + r + "]" : "[" + l.join(",") + "]";
                    b = r;
                    return p
                }
                if (m && "object" === typeof m)
                    for (g = m.length, f = 0; f < g; f += 1)
                        "string" === typeof m[f] && (h = m[f], (p = k(h, e)) && l.push(q(h) + (b ? ": " : ":") + p));
                else
                    for (h in e)
                        Object.prototype.hasOwnProperty.call(e, h) && (p = k(h, e)) && l.push(q(h) + (b ? ": " : ":") + p);
                p = 0 === l.length ? "{}" : b ? "{\n" + b + l.join(",\n" + b) + "\n" + r + "}" : "{" + l.join(",") + "}";
                b = r;
                return p
            }
    }
    "function" !== typeof Date.prototype.toJSON && (Date.prototype.toJSON = function() {
        return isFinite(this.valueOf()) ? this.getUTCFullYear() + "-" + n(this.getUTCMonth() + 1) + "-" + n(this.getUTCDate()) + "T" + n(this.getUTCHours()) + ":" + n(this.getUTCMinutes()) + ":" + n(this.getUTCSeconds()) + "Z" : null
    }, String.prototype.toJSON = Number.prototype.toJSON = Boolean.prototype.toJSON = function() {
        return this.valueOf()
    });
    var a, c, b, d, g, m;
    "function" !== typeof JSON.stringify && (c = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g, g = {"\b": "\\b", "\t": "\\t", "\n": "\\n", "\f": "\\f", "\r": "\\r", '"': '\\"', "\\": "\\\\"}, JSON.stringify = function(a, c, f) {
        var h;
        d = b = "";
        if ("number" === typeof f)
            for (h = 0; h < f; h += 1)
                d += " ";
        else
            "string" === typeof f && (d = f);
        if ((m = c) && "function" !== typeof c && ("object" !== typeof c || "number" !== typeof c.length))
            throw Error("JSON.stringify");
        return k("", {"": a})
    });
    "function" !== typeof JSON.parse && (a = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g, JSON.parse = function(b, c) {
        function f(a, b) {
            var d, g, e = a[b];
            if (e && "object" === typeof e)
                for (d in e)
                    Object.prototype.hasOwnProperty.call(e, d) && (g = f(e, d), void 0 !== g ? e[d] = g : delete e[d]);
            return c.call(a, b, e)
        }
        var d;
        b = String(b);
        a.lastIndex = 0;
        a.test(b) && (b = b.replace(a, function(a) {
            return"\\u" + ("0000" + a.charCodeAt(0).toString(16)).slice(-4)
        }));
        if (/^[\],:{}\s]*$/.test(b.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]").replace(/(?:^|:|,)(?:\s*\[)+/g, "")))
            return d = eval("(" + b + ")"), "function" === typeof c ? f({"": d}, "") : d;
        throw new SyntaxError("JSON.parse");
    })
})();
window.KData = function() {
    function n() {
        var a = {};
        a.screen_height = screen.height;
        a.screen_width = screen.width;
        a.screen_color_depth = screen.colorDepth;
        a.user_agent = navigator.userAgent;
        a.browser_codename = navigator.appCodeName;
        a.browser_appname = navigator.appName;
        a.browser_version = navigator.appVersion;
        a.browser_language = navigator.language;
        var c = [];
        if (navigator.plugins && 0 < navigator.plugins.length)
            for (var b = 0; b < navigator.plugins.length; b++)
                c.push(navigator.plugins[b].name);
        else
            for (var d = "AgControl.AgControl;ShockwaveFlash.ShockwaveFlash;AcroPDF.PDF;PDF.PdfCtrl;QuickTime.QuickTime;rmocx.RealPlayer G2 Control;rmocx.RealPlayer G2 Control.1;RealPlayer.RealPlayer(tm) ActiveX Control (32-bit);RealVideo.RealVideo(tm) ActiveX Control (32-bit);RealPlayer;SWCtl.SWCtl;WMPlayer.OCX;Skype.Detection".split(";"), b = 0; b < d.length; b++)
                try {
                    new ActiveXObject(d[b]), c.push(d[b])
                } catch (g) {
                }
        a.installed_plugins = c;
        c = "AndaleMono;Andale Mono;AppleGothic;Arial;Arial Black;Arial Bold;Arial Narrow;Arial Rounded MT Bold;Avantgarde;Avant Garde;Baskerville;Baskerville old face;Baskerville Old Face;Big Caslon;Bitstream Charter;Bitstream Vera Sans Bold;Bitstream Vera Sans Mono;Bodoni MT;Book Antiqua;Bookman;Bookman Old Style;Brush Script MT;Calibri;Calisto MT;Cambria;Candara;CenturyGothic;Century Gothic;Charcoal;Consolas;Copperplate;Copperplate Gothic Light;Courier;Courier Bold;Courier New;Dejavu Sans;Didot;Didot LT STD;Franklin Gothic;Franklin Gothic Bold;Franklin Gothic Medium;Frutiger;Frutiger Linotype;Futura;Gadget;Garamond;Geneva;Georgia;Gill Sans;Gill Sans MT;Goudy Old Style;Haettenschweiler;Helvetica;Helvetica Inserat;Helvetica Neue;Helvetica Rounded;Hoefler Text;Impact;ITC Franklin Gothic;Lucida Bright;Lucida Console;Lucida Grande;Lucida Sans;Lucida Sans Typewriter;Lucida Sans Unicode;Lucida Typewriter;monaco;Monaco;Nimbus Roman No9 L;Optima;Palatino;Palatino Linotype;Palatino LT STD;Papyrus;Perpetua;Rockwell;Rockwell Bold;Rockwell Extra Bold;Segoe;Segoe UI;Tahoma;Times;TimesNewRoman;Times New Roman;Trebuchet MS;URW Palladio L;Verdana".split(";");
        b = document.getElementsByTagName("body")[0];
        d = document.createElement("span");
        b.appendChild(d);
        d.innerHTML = "abcedfghijklmnopqrstuvwxyz";
        d.style.fontSize = "72px";
        d.style.fontFamily = "serif";
        for (var m = d.offsetHeight, n = d.offsetWidth, k = [], f = 0; f < c.length; f++)
            d.style.fontFamily = '"' + c[f] + '", "serif"', d.offsetWidth == n && d.offsetHeight == m || k.push(c[f]);
        b.removeChild(d);
        a.installed_fonts = k;
        c = [];
        if (navigator.mimeTypes && 0 < navigator.mimeTypes.length)
            for (b = 0; b < navigator.mimeTypes.length; b++)
                c.push(navigator.mimeTypes[b].type);
        a.accepted_mimetypes = c;
        a.operating_system = navigator.plateform;
        a.timezone_offset = (new Date).getTimezoneOffset();
        a.java_enabled = navigator.javaEnabled();
        a.localstorage_enabled = window.localStorage ? !0 : !1;
        a.sessionstorage_enabled = window.sessionStorage ? !0 : !1;
        a.indexdb_enabled = !!window.indexedDB;
        a.cookie_enabled = navigator.cookieEnabled;
        return a
    }
    function q(a, c) {
        if (window.XDomainRequest) {
            var b = new XDomainRequest;
            b.open(a, c);
            return b
        }
        return window.XMLHttpRequest ? (b = new XMLHttpRequest, b.open(a, c, !0), b.withCredentials = !0, b) : null
    }
    function k(a) {
        a = a || {};
        var c = "http:";
        "undefined" !== typeof a.secure ? (c = a.secure ? "https:" : "http:", delete a.secure) : c = document.location.protocol;
        if ("string" !== typeof a.customer)
            console.error("Invalid var type for 'customer' argument");
        else {
            var b = a.customer;
            delete a.customer;
            if ("string" !== typeof a.type)
                console.error("Invalid var type for 'type' argument");
            else {
                var d = a.type;
                delete a.type;
                if ("yes" == navigator.doNotTrack)
                    console.info("Enforcing DoNotTrack privacy policy");
                else {
                    a = {customer: b, type: d, data: a, fingerprint: n()};
                    var g = q("POST", c + "//tracking.kdata.fr/tag");
                    g.onload = function() {
                        if (g.responseText) {
                            var a = JSON.parse(g.responseText);
                            if (!0 == a.do_cookie_match && void 0 != a.url) {
                                var b = document.createElement("img");
                                b.src = c + "//" + a.url;
                                document.body.appendChild(b)
                            }
                        }
                    };
                    g.setRequestHeader("Content-Type", "text/plain; charset=utf-8");
                    g.send(JSON.stringify(a))
                }
            }
        }
    }
    return{sendEvent: k, send: k}
}();