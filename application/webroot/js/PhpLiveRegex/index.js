/*
 * jQuery throttle / debounce - v1.1 - 3/7/2010
 * http://benalman.com/projects/jquery-throttle-debounce-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function (b, c) {
    var $ = b.jQuery || b.Cowboy || (b.Cowboy = {}), a;
    $.throttle = a = function (e, f, j, i) {
        var h, d = 0;
        if (typeof f !== "boolean") {
            i = j;
            j = f;
            f = c
        }
        function g() {
            var o = this, m = +new Date() - d, n = arguments;
            function l() {
                d = +new Date();
                j.apply(o, n)
            }
            function k() {
                h = c
            }
            if (i && !h) {
                l()
            }
            h && clearTimeout(h);
            if (i === c && m > e) {
                l()
            } else {
                if (f !== true) {
                    h = setTimeout(i ? k : l, i === c ? e - m : e)
                }
            }
        }
        if ($.guid) {
            g.guid = j.guid = j.guid || $.guid++
        }
        return g
    };
    $.debounce = function (d, e, f) {
        return f === c ? a(d, e, false) : a(d, f, e !== false)
    }
})(this);

PHPREGEX_EVAL = null;
PERMALINK_DIRTY = false;
function evalRegex() {
    PERMALINK_DIRTY = true;
    $(".tab-content").fadeTo(100, 0.5);

    if (PHPREGEX_EVAL !== null) {
        PHPREGEX_EVAL.abort();
    }
    PHPREGEX_EVAL = $.post(GLIAL_LINK+"/PhpLiveRegex/evaluate",
            {"regex_1": $("#regex_1").val(),
                "regex_2": $("#regex_2").val(),
                "replacement": $("#replacement").val(),
                "examples": $("#examples").val()},
            function (data) {
                document.getElementById("preg-match").innerHTML = data.preg_match;
                document.getElementById("preg-match-all").innerHTML = data.preg_match_all;
                document.getElementById("preg-replace").innerHTML = data.preg_replace;
                document.getElementById("preg-grep").innerHTML = data.preg_grep;
                document.getElementById("preg-split").innerHTML = data.preg_split;

                (function () {

                    var tip = document.createElement('div'),
                            refs = document.querySelectorAll('.ref');

                    for (var i = 0, m = refs.length; i < m; i++) {
                        var kbds = refs[i].querySelectorAll('[data-toggle]'),
                                tippable = refs[i].querySelectorAll('[data-tip]'),
                                tips = refs[i].querySelectorAll('div');

                        for (var j = 0, n = kbds.length; j < n; j++) {
                            if (kbds[j].parentNode !== refs[i])
                                kbds[j].onclick = function (e) {
                                    ('exp' in this.dataset) ? delete this.dataset.exp : this.dataset.exp = 1;
                                }
                        }

                        [].filter.call(tips, function (node) {
                            return node.parentNode == refs[i];
                        });

                        for (var j = 0, n = tippable.length; j < n; j++) {
                            tippable[j].tipRef = tips[tippable[j].dataset.tip];
                            tippable[j].onmouseover = function () {
                                tip.className = 'ref visible';
                                tip.innerHTML = this.tipRef.innerHTML;
                                window.clearTimeout(tip.fadeOut);
                            };
                            tippable[j].onmouseout = function () {
                                tip.className = 'ref visible fadingOut';
                                tip.fadeOut = window.setTimeout(function () {
                                    tip.innerHTML = '';
                                    tip.className = '';
                                }, 250);
                            };
                        }

                        refs[i].onmousemove = function (e) {
                            if (tip.className.indexOf('visible') < 0)
                                return;
                            tip.style.top = ((document.documentElement.clientHeight - e.clientY) < tip.offsetHeight + 20 ? Math.max(e.pageY - tip.offsetHeight, 0) : e.pageY) + 'px';
                            tip.style.left = ((document.documentElement.clientWidth - e.clientX) < tip.offsetWidth + 20 ? Math.max(e.pageX - tip.offsetWidth, 0) : e.pageX) + 'px';
                        };
                    }

                    tip.id = 'rTip';
                    document.body.appendChild(tip);
                })();

                $(".tab-content").fadeTo(100, 1.0);
            }, "json");
}

evalRegex();

$('#regex_1 , #regex_2 , #examples , #replacement').keyup($.debounce(250, evalRegex));


$('#clear').click(function () {
    $("#regex_1 , #regex_2 , #examples , #replacement").val("");
    evalRegex();
});