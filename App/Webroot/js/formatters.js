(function (window) {
    "use strict";

    var defaultUnits = {
        fr: ["o", "Ko", "Mo", "Go", "To", "Po"],
        si: ["B", "KB", "MB", "GB", "TB", "PB"],
        iec: ["B", "KiB", "MiB", "GiB", "TiB", "PiB"]
    };

    function bytes(value, decimals, style) {
        var numericValue = Number(value);
        var precision = decimals === undefined ? 2 : Number(decimals);
        var units = defaultUnits[style || "fr"] || defaultUnits.fr;

        if (!isFinite(numericValue)) {
            return "";
        }

        if (numericValue === 0) {
            return numericValue.toFixed(precision) + " " + units[0];
        }

        var factor = Math.floor(Math.log(Math.abs(numericValue)) / Math.log(1024));
        factor = Math.min(units.length - 1, Math.max(0, factor));

        return (numericValue / Math.pow(1024, factor)).toFixed(precision) + " " + units[factor];
    }

    function number(value, decimals) {
        var numericValue = Number(value);
        var precision = decimals === undefined ? 2 : Number(decimals);

        if (!isFinite(numericValue)) {
            return "";
        }

        return Math.round((numericValue + Number.EPSILON) * Math.pow(10, precision)) / Math.pow(10, precision);
    }

    window.PmaFormat = window.PmaFormat || {};
    window.PmaFormat.bytes = bytes;
    window.PmaFormat.number = number;

    window.FileConvertSize = function (value) {
        return bytes(value);
    };
}(window));
