<?php

use App\Library\Format;

echo '<table class="table table-condensed table-bordered table-striped" >';

echo '<tr>';
echo '<th>'.__("Top").'</th>';
echo '<th>'.__("Major version").'</th>';
echo '<th>'.__("Latest version").'</th>';
echo '<th>'.__("All").'</th>';
echo '</tr>';

function trierVersions(array &$versions) {
    usort($versions, function($a, $b) {
        return version_compare($a, $b);
    });
}
function eclaircie($hexColor, $lightenPercent = 20) {

    // Supprime le dièse au début si présent
    $hexColor = ltrim($hexColor, '#');

    // Convertit l'hexadécimal en RGB
    $r = hexdec(substr($hexColor, 0, 2));
    $g = hexdec(substr($hexColor, 2, 2));
    $b = hexdec(substr($hexColor, 4, 2));

    // Calcule l'augmentation basée sur le pourcentage pour claircir la couleur
    $r += (255 - $r) * ($lightenPercent / 100);
    $g += (255 - $g) * ($lightenPercent / 100);
    $b += (255 - $b) * ($lightenPercent / 100);

    // S'assure que les valeurs ne dépassent pas 255
    $r = min(255, $r);
    $g = min(255, $g);
    $b = min(255, $b);

    // Retourne le nouveau code RGB
    return sprintf('RGB(%d, %d, %d)', $r, $g, $b);
}




foreach($data['image'] as $elem)
{
    $versions = explode(",",$elem['all_version']);
    trierVersions($versions);

    echo '<tr>';
    echo '<td>'.Format::getLogo(strtolower($elem['display_name']) ).' '.$elem['display_name'].'</td>';
    echo '<td>'.$elem['main'].'</td>';
    echo '<td>'.$elem['latest_version'].'</td>';
    echo '<td width="66%">';
    $bg = $elem['background'];
    foreach($versions as $version)
    {
        $elem['background'] = $bg;
        
        if (empty($data['tag'][$elem['name']][$version]))
        {
            $elem['background'] = eclaircie($elem['background'], 50);
        }

        echo '<span title="Docker" class="label" style="color:#'.$elem['color'].'; background:'.$elem['background'].'">'.$version.'</span> ';
        //echo $version." ";
    }
    
    echo '</td>';
    echo '</tr>';
    



}
echo '<table>';