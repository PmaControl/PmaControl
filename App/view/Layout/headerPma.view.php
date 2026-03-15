<?php

use \Glial\I18n\I18n;
use \Glial\Synapse\FactoryController;

echo "<!DOCTYPE html>\n";
echo "<html lang=\"".I18n::Get()."\">";
echo "<head>\n";

echo "<!--\n";
echo SITE_LOGO;
echo "Powered by 68koncept (www.68koncept.com)\n";
echo "-->";
echo "<meta charset=utf-8 />\n";
echo "<meta name=\"Keywords\" content=\"\" />\n";
echo "<meta name=\"Description\" content=\"\" />\n";
echo "<meta name=\"Author\" content=\"Aurelien LEQUOY\" />\n";
echo "<meta name=\"robots\" content=\"index,follow,all\" />\n";
echo "<meta name=\"generator\" content=\"GLIALE 1.1\" />\n";
echo "<meta name=\"runtime\" content=\"[PAGE_GENERATION]\" />\n";
echo "<link rel=\"shortcut icon\" href=\"favicon.ico\" />";
echo "<title>".strip_tags($GLIALE_TITLE)." - ".SITE_NAME." ".SITE_VERSION."</title>\n";
?>
<link href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAABdFBMVEUAAABDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMdDhMeYweDeAAAAe3RSTlMAAQIDBAUGBwgJCgsNDg8QERITFxgaHB4gISIjJSYnKCksLS4vMTIzNTY3ODk7PD0+QEFCR0lMTVFSVFZZW11hYmNna2xtb3B0dXd4fIKDiYuOj5KVnZ6goqWoqq2wtbq+wMHDxcfI0dPV2drc3uTm6Ovt7/Hz9ff5+/0KBW49AAABeUlEQVQYGXXBC1sSURQF0H1nREEJAjPMNM0hNCWzBMwkzd4lVqb2kMpKBPMJoiXuP+8cBpEP5q4Fx5WpjwVeKmXnehUujefZ6uSVF46OTbo7HUfVd2oFIKg3DEE9C+KYWgMQs9TZNlA1eURXyx7UGPF1NttZCKAmCpvRM7Gw+nu7WCnv5rPvUoM+2LxXIfjzloIL/8tTC4JkeWk0ZKJB5425HEkLokTH4bcPi8/S86+XVvNndPRDJKmzpVAVO6CrjIkaFft8xiaFJ12oicOmuuPzK78KxUp5d2v9TbK/A7ZACIL7j4No1Rb9SguCttJKerTH5zEUoMz20FDybZ42C2KHWhGIyH9qvIfDm6nQxZ8h1JnRzF82OvmSCuLCjzCE0RUZuZ+YmX44NhhqgzDTtyHIbMyDFt0vjmlBUOQWEzf97aYCDI/v2tjTtX+0DUNsUCsM4d+jxiM4jPQRXayFUaf6nufYqPhpwosmynf9zr3ETOrB3YGgibpzl+IgDrtTZxkAAAAASUVORK5CYII=" rel="icon" type="image/x-icon">
<link rel="stylesheet" type="text/css" href="<?= CSS ?>bootstrap.css">
<link rel="stylesheet" type="text/css" href="<?= CSS ?>bootstrap-editable.css" />
<link rel="stylesheet" type="text/css" href="<?= CSS ?>bootstrap-select.min.css" />
<link rel="stylesheet" type="text/css" href="<?= CSS ?>bootstrap-datetimepicker.min.css" />

<link rel="stylesheet" type="text/css" href="<?= CSS ?>autocomplete.css" />
<link rel="stylesheet" type="text/css" href="<?= CSS ?>notification.style.css" />
<link rel="stylesheet" type="text/css" href="<?= CSS ?>title.css" />
<link rel="stylesheet" type="text/css" href="<?= CSS ?>reporting.css" />

<link rel="stylesheet" type="text/css" href="<?= CSS ?>pmacontrol.css" />

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.3.6/css/bootstrap-colorpicker.min.css" />




<link href="<?= CSS ?>font-awesome.min.css" rel="stylesheet">
<style>
.pmacontrol-info-bubble {
    position: fixed;
    z-index: 99999;
    max-width: 480px;
    padding: 6px 10px;
    background: rgba(20, 20, 20, 0.96);
    color: #fff;
    border-radius: 4px;
    font-size: 12px;
    line-height: 1.4;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    pointer-events: none;
    display: none;
    white-space: nowrap;
}
</style>
<script>
(function () {
    if (window.__pmacontrolInfoBubbleLoaded) {
        return;
    }
    window.__pmacontrolInfoBubbleLoaded = true;

    function ensureBubble() {
        var bubble = document.querySelector(".pmacontrol-info-bubble");
        if (bubble) {
            return bubble;
        }

        bubble = document.createElement("div");
        bubble.className = "pmacontrol-info-bubble";
        document.body.appendChild(bubble);
        return bubble;
    }

    function hideBubble() {
        var bubble = document.querySelector(".pmacontrol-info-bubble");
        if (bubble) {
            bubble.style.display = "none";
        }
    }

    document.addEventListener("mouseover", function (event) {
        var target = event.target.closest("[data-info]");
        if (!target) {
            hideBubble();
            return;
        }

        var info = target.getAttribute("data-info") || "";
        if (!info) {
            hideBubble();
            return;
        }

        var bubble = ensureBubble();
        bubble.textContent = info;
        bubble.style.display = "block";
        bubble.style.left = (event.clientX + 14) + "px";
        bubble.style.top = (event.clientY + 14) + "px";
    });

    document.addEventListener("mousemove", function (event) {
        var bubble = document.querySelector(".pmacontrol-info-bubble");
        if (!bubble || bubble.style.display !== "block") {
            return;
        }

        bubble.style.left = (event.clientX + 14) + "px";
        bubble.style.top = (event.clientY + 14) + "px";
    });

    document.addEventListener("mouseout", function (event) {
        if (!event.target.closest("[data-info]")) {
            return;
        }
        hideBubble();
    });
})();
</script>
</head>
<body>

    <?php
    if ($data['auth'] !== 1) {
        FactoryController::addNode("Menu", "show", array("1"));
    } else {
        FactoryController::addNode("Menu", "show", array("3"));
    }
