<?php

echo '<style>
.server-state-table { table-layout: fixed; }
.server-state-table th:nth-child(1) { width: 280px; }
.server-state-table th:nth-child(2) { width: 140px; }
.server-state-table th:nth-child(3) { width: 100px; }
.server-state-chart-wrap { width: 100%; height: 28px; }
.server-state-chart-wrap canvas { display:block; width:100% !important; height:28px !important; }
.server-state-status { display:inline-block; min-width:60px; font-weight:700; text-align:center; }
.server-state-status.up { color:#2ca25f; }
.server-state-status.down { color:#de2d26; }
.server-state-status.na { color:#7f7f7f; }
#server-state-root .loading { color:#666; padding:16px 0; }
</style>';

echo '<div class="well">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());
echo '</div>';

echo '<h2>'.__('Server State').'</h2>';
echo '<p>'.__('Green = mysql_available=1, Red = mysql_available=0, Grey = no value').'</p>';
echo '<div id="server-state-root"><div class="loading">Loading...</div></div>';
