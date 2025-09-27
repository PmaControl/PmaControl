<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class Color
{

    static $colorMap = [
    'aliceblue' => '#F0F8FF',
    'antiquewhite' => '#FAEBD7',
    'antiquewhite1' => '#FFEFDB',
    'antiquewhite2' => '#EEDFCC',
    'antiquewhite3' => '#CDC0B0',
    'antiquewhite4' => '#8B8378',
    'aquamarine' => '#7FFFD4',
    'aquamarine1' => '#7FFFD4',
    'aquamarine2' => '#76EEC6',
    'aquamarine3' => '#66CDAA',
    'aquamarine4' => '#458B74',
    'azure' => '#F0FFFF',
    'azure1' => '#F0FFFF',
    'azure2' => '#E0EEEE',
    'azure3' => '#C1CDCD',
    'azure4' => '#838B8B',
    'beige' => '#F5F5DC',
    'bisque' => '#FFE4C4',
    'bisque1' => '#FFE4C4',
    'bisque2' => '#EED5B7',
    'bisque3' => '#CDB79E',
    'bisque4' => '#8B7D6B',
    'black' => '#000000',
    'blanchedalmond' => '#FFEBCD',
    'blue' => '#0000FF',
    'blue1' => '#0000FF',
    'blue2' => '#0000EE',
    'blue3' => '#0000CD',
    'blue4' => '#00008B',
    'blueviolet' => '#8A2BE2',
    'brown' => '#A52A2A',
    'brown1' => '#FF4040',
    'brown2' => '#EE3B3B',
    'brown3' => '#CD3333',
    'brown4' => '#8B2323',
    'burlywood' => '#DEB887',
    'burlywood1' => '#FFD39B',
    'burlywood2' => '#EEC591',
    'burlywood3' => '#CDAA7D',
    'burlywood4' => '#8B7355',
    'cadetblue' => '#5F9EA0',
    'cadetblue1' => '#98F5FF',
    'cadetblue2' => '#8EE5EE',
    'cadetblue3' => '#7AC5CD',
    'cadetblue4' => '#53868B',
    'chartreuse' => '#7FFF00',
    'chartreuse1' => '#7FFF00',
    'chartreuse2' => '#76EE00',
    'chartreuse3' => '#66CD00',
    'chartreuse4' => '#458B00',
    'chocolate' => '#D2691E',
    'chocolate1' => '#FF7F24',
    'chocolate2' => '#EE7621',
    'chocolate3' => '#CD661D',
    'chocolate4' => '#8B4513',
    'coral' => '#FF7F50',
    'coral1' => '#FF7256',
    'coral2' => '#EE6A50',
    'coral3' => '#CD5B45',
    'coral4' => '#8B3E2F',
    'cornflowerblue' => '#6495ED',
    'cornsilk' => '#FFF8DC',
    'cornsilk1' => '#FFF8DC',
    'cornsilk2' => '#EEE8CD',
    'cornsilk3' => '#CDC8B1',
    'cornsilk4' => '#8B8878',
    'crimson' => '#DC143C',
    'cyan' => '#00FFFF',
    'cyan1' => '#00FFFF',
    'cyan2' => '#00EEEE',
    'cyan3' => '#00CDCD',
    'cyan4' => '#008B8B',
    'darkgoldenrod' => '#B8860B',
    'darkgoldenrod1' => '#FFB90F',
    'darkgoldenrod2' => '#EEAD0E',
    'darkgoldenrod3' => '#CD950C',
    'darkgoldenrod4' => '#8B6508',
    'darkgreen' => '#006400',
    'darkkhaki' => '#BDB76B',
    'darkolivegreen' => '#556B2F',
    'darkolivegreen1' => '#CAFF70',
    'darkolivegreen2' => '#A9F5A9',
    'darkolivegreen3' => '#93E093',
    'darkolivegreen4' => '#6C9C6C',
    'darkorange' => '#FF8C00',
    'darkorange1' => '#FF7F00',
    'darkorange2' => '#EE7600',
    'darkorange3' => '#CD6600',
    'darkorange4' => '#8B4500',
    'darkorchid' => '#9932CC',
    'darkorchid1' => '#BF3EFF',
    'darkorchid2' => '#B23AEE',
    'darkorchid3' => '#9A32CD',
    'darkorchid4' => '#68228B',
    'darksalmon' => '#E9967A',
    'darkseagreen' => '#8FBC8F',
    'darkseagreen1' => '#C1FFC1',
    'darkseagreen2' => '#B4EEB4',
    'darkseagreen3' => '#9BCD9B',
    'darkseagreen4' => '#698B69',
    'darkslateblue' => '#483D8B',
    'darkslategray' => '#2F4F4F',
    'darkslategray1' => '#97FFFF',
    'darkslategray2' => '#8DEEEE',
    'darkslategray3' => '#79CDCD',
    'darkslategray4' => '#528B8B',
    'darkslategrey' => '#2F4F4F',
    'darkturquoise' => '#00CED1',
    'darkviolet' => '#9400D3',
    'deeppink' => '#FF1493',
    'deeppink1' => '#FF1493',
    'deeppink2' => '#EE1289',
    'deeppink3' => '#CD1076',
    'deeppink4' => '#8B0A50',
    'deepskyblue' => '#00BFFF',
    'deepskyblue1' => '#00BFFF',
    'deepskyblue2' => '#00B2EE',
    'deepskyblue3' => '#009ACD',
    'deepskyblue4' => '#00688B',
    'dimgray' => '#696969',
    'dimgrey' => '#696969',
    'dodgerblue' => '#1E90FF',
    'dodgerblue1' => '#1E90FF',
    'dodgerblue2' => '#1C86EE',
    'dodgerblue3' => '#1874CD',
    'dodgerblue4' => '#104E8B',
    'firebrick' => '#B22222',
    'firebrick1' => '#FF3030',
    'firebrick2' => '#EE2C2C',
    'firebrick3' => '#CD2626',
    'firebrick4' => '#8B1A1A',
    'floralwhite' => '#FFFAF0',
    'forestgreen' => '#228B22',
    'gainsboro' => '#DCDCDC',
    'ghostwhite' => '#F8F8FF',
    'gold' => '#FFD700',
    'gold1' => '#FFD700',
    'gold2' => '#EEC900',
    'gold3' => '#CDAD00',
    'gold4' => '#8B7500',
    'goldenrod' => '#DAA520',
    'goldenrod1' => '#FFC125',
    'goldenrod2' => '#EEB422',
    'goldenrod3' => '#CD9B1D',
    'goldenrod4' => '#8B6914',
    'gray' => '#BEBEBE',
    'gray0' => '#000000',
    'gray1' => '#030303',
    'gray10' => '#1A1A1A',
    'gray11' => '#1C1C1C',
    'gray12' => '#1F1F1F',
    'gray13' => '#212121',
    'gray14' => '#242424',
    'gray15' => '#262626',
    'gray16' => '#292929',
    'gray17' => '#2B2B2B',
    'gray18' => '#2E2E2E',
    'gray19' => '#303030',
    'gray2' => '#333333',
    'gray20' => '#333333',
    'gray21' => '#363636',
    'gray22' => '#383838',
    'gray23' => '#3B3B3B',
    'gray24' => '#3D3D3D',
    'gray25' => '#404040',
    'gray26' => '#424242',
    'gray27' => '#454545',
    'gray28' => '#474747',
    'gray29' => '#4A4A4A',
    'gray3' => '#4D4D4D',
    'gray30' => '#4D4D4D',
    'gray31' => '#4F4F4F',
    'gray32' => '#525252',
    'gray33' => '#545454',
    'gray34' => '#575757',
    'gray35' => '#595959',
    'gray36' => '#5C5C5C',
    'gray37' => '#5E5E5E',
    'gray38' => '#616161',
    'gray39' => '#636363',
    'gray4'  => '#666666',
    'gray40' => '#666666',
    'gray41' => '#696969',
    'gray42' => '#6B6B6B',
    'gray43' => '#6E6E6E',
    'gray44' => '#707070',
    'gray45' => '#737373',
    'gray46' => '#757575',
    'gray47' => '#787878',
    'gray48' => '#7A7A7A',
    'gray49' => '#7D7D7D',
    'gray5'  => '#7F7F7F',
    'gray50' => '#7F7F7F',
    'gray51' => '#828282',
    'gray52' => '#858585',
    'gray53' => '#878787',
    'gray54' => '#8A8A8A',
    'gray55' => '#8C8C8C',
    'gray56' => '#8F8F8F',
    'gray57' => '#919191',
    'gray58' => '#949494',
    'gray59' => '#969696',
    'gray6'  => '#999999',
    'gray60' => '#999999',
    'gray61' => '#9C9C9C',
    'gray62' => '#9E9E9E',
    'gray63' => '#A1A1A1',
    'gray64' => '#A3A3A3',
    'gray65' => '#A6A6A6',
    'gray66' => '#A8A8A8',
    'gray67' => '#ABABAB',
    'gray68' => '#ADADAD',
    'gray69' => '#B0B0B0',
    'gray7'  => '#B3B3B3',
    'gray70' => '#B3B3B3',
    'gray71' => '#B5B5B5',
    'gray72' => '#B8B8B8',
    'gray73' => '#BABABA',
    'gray74' => '#BDBDBD',
    'gray75' => '#BFBFBF',
    'gray76' => '#C2C2C2',
    'gray77' => '#C4C4C4',
    'gray78' => '#C7C7C7',
    'gray79' => '#C9C9C9',
    'gray8'  => '#CCCCCC',
    'gray80' => '#CCCCCC',
    'gray81' => '#CFCFCF',
    'gray82' => '#D1D1D1',
    'gray83' => '#D4D4D4',
    'gray84' => '#D6D6D6',
    'gray85' => '#D9D9D9',
    'gray86' => '#DBDBDB',
    'gray87' => '#DEDEDE',
    'gray88' => '#E0E0E0',
    'gray89' => '#E3E3E3',
    'gray9'  => '#E5E5E5',
    'gray90' => '#E5E5E5',
    'gray91' => '#E8E8E8',
    'gray92' => '#EBEBEB',
    'gray93' => '#EDEDED',
    'gray94' => '#F0F0F0',
    'gray95' => '#F2F2F2',
    'gray96' => '#F5F5F5',
    'gray97' => '#F7F7F7',
    'gray98' => '#FAFAFA',
    'gray99' => '#FCFCFC',
    'gray100' => '#FFFFFF',
    'honeydew' => '#F0FFF0',
    'honeydew1' => '#F0FFF0',
    'honeydew2' => '#E0EEE0',
    'honeydew3' => '#C1CDC1',
    'honeydew4' => '#838B83',
    'hotpink' => '#FF69B4',
    'hotpink1' => '#FF6EB4',
    'hotpink2' => '#EE6AA7',
    'hotpink3' => '#CD6090',
    'hotpink4' => '#8B3A62',
    'indianred' => '#CD5C5C',
    'indianred1' => '#FF6A6A',
    'indianred2' => '#EE6363',
    'indianred3' => '#CD5555',
    'indianred4' => '#8B3A3A',
    'indigo' => '#4B0082',
    'ivory' => '#FFFFF0',
    'ivory1' => '#FFFFF0',
    'ivory2' => '#EEEEE0',
    'ivory3' => '#CDCDC1',
    'ivory4' => '#8B8B83',
    'khaki' => '#F0E68C',
    'khaki1' => '#FFF68F',
    'khaki2' => '#EEE685',
    'khaki3' => '#CDC673',
    'khaki4' => '#8B864E',
    'lavender' => '#E6E6FA',
    'lavenderblush' => '#FFF0F5',
    'lavenderblush1' => '#FFF0F5',
    'lavenderblush2' => '#EEE0E5',
    'lavenderblush3' => '#CDC1C5',
    'lavenderblush4' => '#8B8386',
    'lawngreen' => '#7CFC00',
    'lemonchiffon' => '#FFFACD',
    'lemonchiffon1' => '#FFFACD',
    'lemonchiffon2' => '#EEE9BF',
    'lemonchiffon3' => '#CDC9A5',
    'lemonchiffon4' => '#8B8970',
    'lightblue' => '#ADD8E6',
    'lightblue1' => '#BFEFFF',
    'lightblue2' => '#B2DFEE',
    'lightblue3' => '#9AC0CD',
    'lightblue4' => '#68838B',
    'lightcoral' => '#F08080',
    'lightcyan' => '#E0FFFF',
    'lightcyan1' => '#E0FFFF',
    'lightcyan2' => '#D1EEEE',
    'lightcyan3' => '#B4CDCD',
    'lightcyan4' => '#7A8B8B',
    'lightgoldenrod' => '#EEDD82',
    'lightgoldenrod1' => '#FFEC8B',
    'lightgoldenrod2' => '#EEDC82',
    'lightgoldenrod3' => '#CDBE70',
    'lightgoldenrod4' => '#8B814C',
    'lightgoldenrodyellow' => '#FAFAD2',
    'lightgray' => '#D3D3D3',
    'lightgrey' => '#D3D3D3',
    'lightpink' => '#FFB6C1',
    'lightpink1' => '#FFAEB9',
    'lightpink2' => '#EEA2AD',
    'lightpink3' => '#CD8C95',
    'lightpink4' => '#8B5F65',
    'lightsalmon' => '#FFA07A',
    'lightsalmon1' => '#FFA07A',
    'lightsalmon2' => '#EE9572',
    'lightsalmon3' => '#CD8162',
    'lightsalmon4' => '#8B5742',
    'lightseagreen' => '#20B2AA',
    'lightskyblue' => '#87CEFA',
    'lightskyblue1' => '#B0E2FF',
    'lightskyblue2' => '#A4D3EE',
    'lightskyblue3' => '#8DB6CD',
    'lightskyblue4' => '#607B8B',
    'lightslateblue' => '#8470FF',
    'lightslategray' => '#778899',
    'lightslategrey' => '#778899',
    'lightsteelblue' => '#B0C4DE',
    'lightsteelblue1' => '#CAE1FF',
    'lightsteelblue2' => '#BCD2EE',
    'lightsteelblue3' => '#A2B5CD',
    'lightsteelblue4' => '#6E7B8B',
    'lightyellow' => '#FFFFE0',
    'lightyellow1' => '#FFFFE0',
    'lightyellow2' => '#EEEED1',
    'lightyellow3' => '#CDCDB4',
    'lightyellow4' => '#8B8B7A',
    'limegreen' => '#32CD32',
    'linen' => '#FAF0E6',
    'magenta' => '#FF00FF',
    'magenta1' => '#FF00FF',
    'magenta2' => '#EE00EE',
    'magenta3' => '#CD00CD',
    'magenta4' => '#8B008B',
    'maroon' => '#B03060',
    'maroon1' => '#FF34B3',
    'maroon2' => '#EE30A7',
    'maroon3' => '#CD2990',
    'maroon4' => '#8B1C62',
    'mediumaquamarine' => '#66CDAA',
    'mediumblue' => '#0000CD',
    'mediumorchid' => '#BA55D3',
    'mediumorchid1' => '#E066FF',
    'mediumorchid2' => '#D15FEE',
    'mediumorchid3' => '#B452CD',
    'mediumorchid4' => '#7A378B',
    'mediumpurple' => '#9370DB',
    'mediumpurple1' => '#AB82FF',
    'mediumpurple2' => '#9F79EE',
    'mediumpurple3' => '#8968CD',
    'mediumpurple4' => '#5D478B',
    'mediumseagreen' => '#3CB371',
    'mediumslateblue' => '#7B68EE',
    'mediumspringgreen' => '#00FA9A',
    'mediumturquoise' => '#48D1CC',
    'mediumvioletred' => '#C71585',
    'midnightblue' => '#191970',
    'mintcream' => '#F5FFFA',
    'mistyrose' => '#FFE4E1',
    'mistyrose1' => '#FFE4E1',
    'mistyrose2' => '#EED5D2',
    'mistyrose3' => '#CDB7B5',
    'mistyrose4' => '#8B7D7B',
    'moccasin' => '#FFE4B5',
    'navajowhite' => '#FFDEAD',
    'navajowhite1' => '#FFDEAD',
    'navajowhite2' => '#EECFA1',
    'navajowhite3' => '#CDB38B',
    'navajowhite4' => '#8B795E',
    'navy' => '#000080',
    'navyblue' => '#000080',
    'none' => '#00000000',
    'oldlace' => '#FDF5E6',
    'olivedrab' => '#6B8E23',
    'olivedrab1' => '#C0FF3E',
    'olivedrab2' => '#B3EE3A',
    'olivedrab3' => '#9ACD32',
    'olivedrab4' => '#698B22',
    'orange' => '#FFA500',
    'orange1' => '#FFA500',
    'orange2' => '#EE9A00',
    'orange3' => '#CD8500',
    'orange4' => '#8B5A00',
    'orangered' => '#FF4500',
    'orangered1' => '#FF4500',
    'orangered2' => '#EE4000',
    'orangered3' => '#CD3700',
    'orangered4' => '#8B2500',
    'orchid' => '#DA70D6',
    'orchid1' => '#FF83FA',
    'orchid2' => '#EE7AE9',
    'orchid3' => '#CD69C9',
    'orchid4' => '#8B4789'];

    static function setFontColor($type)
    {
        $hex = substr(md5($type), 0, 6);

        return $hex;

        //return $hex['background'];
    }

    static function setBackgroundColor($type)
    {
        /*
          $hex = self::setFontColor($type);
          $ret = hex2bin('ffffff') ^ hex2bin($hex);
          return bin2hex($ret);
         */

        $hex = self::setFontColor($type);


        return $hex;
        //return self::getFontColor($hex);

        //return $hex['background'];
    }

    public function testColor($param)
    {
        $text = $param[0];

        echo $text." : #".$this->setFontColor($text)." : #".$this->setBackgroundColor($text)."\n";
    }

    static public function getFontColor($b_color)
    {

        $white               = '#FFFFFF'; //couleur blanc
        $black               = '#000000'; //couleur noir
        $seuilCouleur        = 165; //seuil qui détermine l'intensité de couleur
        $opposite_red        = '#FFFF00'; //couleur de police pour fond rouge
        $opposite_yellow     = '#000CFF'; //couleur de police pour fond jaune
        $opposite_green      = '#0000FF'; //couleur de police pour fond vert
        $opposite_clear_blue = '#1900FF'; //couleur de police pour fond bleu clair
        $opposite_dark_blue  = '#EEFF00'; //couleur de police pour fond bleu foncé
        $opposite_purple     = '#FFFF00'; //couleur de police pour fond violet
        $tmp                 = 0;

        $r = hexdec(substr($b_color, 1, 2));
        $g = hexdec(substr($b_color, 3, 2));
        $b = hexdec(substr($b_color, 5, 2));

        $ecart1       = $r - $g;
        $ecart2       = $g - $b;
        $ecart3       = $r - $b;
        $limite_ecart = 120;
        $total        = $r + $g + $b;

        //on peut determiner si une couleur est essentiellement composée de noir ou blanc en regardant si l'écart entre les nombres est faible
        if (abs($ecart1) < $limite_ecart && abs($ecart2) < $limite_ecart && abs($ecart3) < $limite_ecart) {

            $limite = 420; //seuil qui détermine le niveau de noir ou blanc
            if ($total < $limite) {
                $f_color = $white;
            } else {
                $f_color = $black;
            }
        } else { //si une couleur domine plus que le noir et blanc
            $color    = array();
            $color[0] = $r;
            $color[1] = $g;
            $color[2] = $b;

            foreach ($color as $val) {
                if ($val > $seuilCouleur) {
                    $tmp++;
                } else {
                    $f_color = $white;
                }
            }

            //si la couleur rouge, vert ou bleu foncé domine
            if ($tmp == 1) {
                if ($r > $seuilCouleur) {//on determine si la couleur est rouge
                    $f_color = $opposite_red;
                }
                if ($g > $seuilCouleur) {//on determine si la couleur est verte
                    $f_color = $opposite_green;
                }
                if ($b > $seuilCouleur) {//on determine si la couleur est bleu foncée
                    $f_color = $opposite_dark_blue;
                }
            }

            //si la couleur jaune, bleu clair ou violet domine
            if ($tmp == 2) {
                if ($r > $seuilCouleur && $g > $seuilCouleur) {//on determine si la couleur est jaune
                    $f_color = $opposite_yellow;
                }
                if ($g > $seuilCouleur && $b > $seuilCouleur) {//on determine si la couleur est bleu claire
                    $f_color = $opposite_clear_blue;
                }
                if ($r > $seuilCouleur && $b > $seuilCouleur) {//on determine si la couleur est violet
                    $f_color = $opposite_purple;
                }
            }
        }
        return $f_color; //on retourne la couleur de police appropriée
    }

    static public function isDark()
    {
        //0.3*(couleur_rouge) + 0.59*(couleur_verte) +(0.11*(couleur_bleue)
        //est supérieur ou égal à 128
        //C'est une couleur claire ... sinon c'est une couleur foncée
    }
}