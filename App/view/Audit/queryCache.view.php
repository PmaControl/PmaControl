<?php


if (!empty($data['query_cache']) && $data['query_cache'] === "ON")
{

    echo "\n==== Analyse sur le query cache ====\n";


    echo "Le query cache est activé sur ce serveur, et en général cela n'est pas une bonne chose, ce cache à été créé en avec MySQL 3.25 il y a plus de 20 ans et ";
    echo "n'a pas presque pas subi d'évolution depuis. Ce dernier souffre d'une conception vieillisante ne dispose que d'un verrou\n";
    echo "^ Variable ^ Value ^\n";
    foreach($data['variable'] as $key => $val)
    {
        echo "|".$key."|".$val."|\n";

    }
    echo "\n";

    echo "<note>Le query cache peut ralentir les performances sur des serveurs avec beaucoup d’écritures (INSERT/UPDATE/DELETE), car toute modification invalide les entrées du cache concernées.</note>\n";

    echo "<note>Il est retiré dans MySQL 8.0 car souvent contre-productif dans des environnements modernes (et MariaDB pourrait suivre cette voie).</note>\n";

    echo "\n=== Variable ===\n";


    echo "=== Efficacité du cache (cache hit ratio) :===\n";
    echo "\n";

    echo "^ Status ^ Value ^\n";
    foreach($data['cache'] as $key => $val)
    {
        echo "|".$key."|".$val."|\n";

    }
    echo "\n";


    echo "<code ini>Qcache_hits / (Qcache_hits + Qcache_inserts + Qcache_not_cached)</code>\n";
    
    echo "<code ini>\n";
    echo "Qcache_hits       = ".$data['cache']['qcache_hits']."\n";
    echo "Qcache_inserts    = ".$data['cache']['qcache_inserts']."\n";
    echo "Qcache_not_cached = ".$data['cache']['qcache_not_cached']."\n\n";
    echo "Ratio = ".$data['cache']['qcache_hits']." / ( ".$data['cache']['qcache_hits']." + ".$data['cache']['qcache_inserts']
    ." + ".$data['cache']['qcache_not_cached']." ) * 100 => ".$data['ratio'] ."%";

    echo "\n</code>";
    echo "\n";


    echo "
^ Situation                                      ^ Recommandation                                                  ^
| Base majoritairement en lecture (peu d’écriture) | ✔️ Peut être utile                                               |
| Beaucoup d’écritures/modifications             | ❌ À éviter                                                      |
| Ratio de cache hit > 60 %                      | 👍 Cache efficace                                                |
| Qcache_lowmem_prunes très élevé                | ⚠️ Ajuster la taille du cache                                   |
| Qcache_not_cached très élevé                   | ❌ Peu de requêtes cacheables ou configuration sous-optimale    |";

echo "\n";

    echo "=== Ratio cache hits par rapport à l’ensemble des SELECT ===\n";

    echo "Formule :";
    echo "<code ini>\n";
    echo "qcache_hits / com_select";
    echo "</code>\n";

    echo "Calcul :";
    echo "<code ini>\n";
    echo $data['cache']['qcache_hits']." / ".$data['cache']['com_select']." => ".$data['ratio_efficacite']." %\n";
    echo "</code>\n";
}