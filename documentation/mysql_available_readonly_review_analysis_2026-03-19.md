# Analyse des points de review sur `mysql_available = 2`

Date: 2026-03-19  
Sujet: analyse des deux remarques de review sur l'introduction de `mysql_available = 2`

## Résumé

Les deux remarques de review sont fondées.

Il y a actuellement deux problèmes distincts :

1. dans [Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php), toute erreur du probe proxy complémentaire est reclassée en `mysql_available = 2`, ce qui mélange :
   - un vrai cas "read only"
   - et un échec technique dur

2. dans [ServerStateTimeline.php](/srv/www/pmacontrol/App/Library/ServerStateTimeline.php), le nouveau statut `2` est bien agrégé et coloré, mais la colonne ratio ne l'intègre pas, ce qui rend le tableau trompeur.

Ces deux points peuvent induire un opérateur en erreur sur l'état réel d'un proxy ou d'un listener.

## Point 1: régression de classification dans `Aspirateur.php`

### Code concerné

Bloc proxy dans [Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php#L461):

```php
else if (!empty($IS_PROXY)){
    $error_ori = '';
    try{
        $mysql_tested->sql_query("SELECT 1;");
        $sql ="BEGIN";
        $mysql_tested->sql_query($sql);
        $sql ="SELECT 1";
        $mysql_tested->sql_query($sql);
        $sql ="COMMIT";
        $mysql_tested->sql_query($sql);
    }
    catch(Exception $e){
        $error_ori = $e->getMessage();
        ...
    }
    finally
    {
        $available = empty($error_ori) ? 1 : 2; // 2 => cas read only
        $this->setService($id_mysql_server, $ping, $error_filter, $available, 'mysql');

        if ($available === 0) {
            $mysql_tested->sql_close();
            return false;
        }
    }
}
```

### Ce que faisait le code avant

Avant la modification, la logique était :

- connexion initiale OK => `mysql_available = 1`
- si le probe complémentaire échoue => `mysql_available = 0`

Donc toute erreur du bloc proxy était considérée comme une indisponibilité.

### Ce que fait le code maintenant

La logique est devenue :

- connexion initiale OK => `mysql_available = 1`
- si n'importe quelle erreur survient dans le probe complémentaire => `mysql_available = 2`

Conséquence immédiate :

- la branche `if ($available === 0)` dans le `finally` n'est plus atteignable
- le worker continue donc son exécution comme si le serveur était seulement dans un état "warning/read only"
- même si l'erreur est en réalité un échec dur

### Pourquoi c'est dangereux

Le bloc `catch` ne filtre pas la nature de l'erreur. Il capture tout `Exception`, donc potentiellement :

- perte de connexion
- timeout
- erreur protocolaire
- permission error
- route MySQL Router invalide
- backend indisponible
- session cassée après `BEGIN`
- toute autre erreur SQL ou réseau remontée par le driver

Or toutes ces erreurs ne veulent pas dire "read only".

Le code actuel confond donc :

- "je peux encore parler à l'endpoint mais il est dans un état logique read-only"
- et "le probe complémentaire a échoué pour une raison quelconque"

### Effet de bord le plus important

Le worker continue ensuite comme si l'état était seulement dégradé :

```php
if ($available === 0) {
    return false;
}
```

Comme `available` vaut maintenant seulement `1` ou `2`, les cas d'échec dur ne short-circuitent plus ici.

Cela peut provoquer :

- une disponibilité surévaluée
- la poursuite de collecte sur un endpoint en réalité cassé
- des erreurs secondaires plus loin dans le flux
- une incohérence entre l'UI et l'état réellement exploitable

### Pourquoi le commentaire de review est juste

La remarque de review dit:

> preserve hard failures instead of converting every probe error to read-only

C'est exactement le problème.

Le code actuel ne préserve plus l'information "hard failure".  
Il l'écrase systématiquement en `2`.

### Ce que devrait faire une implémentation saine

Il faudrait distinguer au minimum 3 cas :

1. `1` => probe OK
2. `2` => état explicitement reconnu comme "read only" ou "read path only"
3. `0` => échec dur du probe

Le point clé est le mot "explicitement".  
Il ne faut pas déduire `2` par défaut sur n'importe quelle exception.

### Ce qu'il manque aujourd'hui pour justifier `2`

Le code n'a actuellement :

- ni détection sémantique fiable du read-only
- ni parsing d'un code erreur spécifique
- ni branche conditionnelle qui prouve "ce type précis d'erreur == read only"

Donc la mutation `error => 2` n'est pas défendable techniquement dans l'état actuel.

## Point 2: incohérence UI/data sur le ratio dans `ServerStateTimeline.php`

### Code concerné

Agrégation du bucket dans [ServerStateTimeline.php](/srv/www/pmacontrol/App/Library/ServerStateTimeline.php#L146):

```php
if (in_array(0, $normalized, true)) {
    return 0;
}

if (in_array(2, $normalized, true)) {
    return 2;
}

if (in_array(1, $normalized, true)) {
    return 1;
}
```

Donc la série peut maintenant contenir :

- `0`
- `1`
- `2`
- `null`

Mais le ratio reste calculé comme ceci plus bas :

```php
$oneCount = 0;
$signalCount = 0;

foreach ($values as $value) {
    if ($value === 1) {
        $oneCount++;
        $signalCount++;
    } elseif ($value === 0) {
        $signalCount++;
    }
}

return [
    'one' => $oneCount,
    'signal' => $signalCount,
    'label' => $oneCount . ' / ' . $signalCount,
];
```

### Conséquence

Le statut `2` :

- est visible dans les buckets
- est visible dans la couleur
- est visible dans `current_status`
- mais n'entre pas dans le ratio

Donc une machine entièrement bleue peut afficher :

```text
0 / 0
```

alors que l'écran affiche bien un signal présent.

### Pourquoi c'est trompeur

`0 / 0` est aussi la signature d'un vrai "pas de données".

Donc deux situations différentes deviennent visuellement identiques :

1. aucune donnée collectée
2. buckets tous à `2`

Cette ambiguïté casse la lisibilité du tableau.

### Pourquoi le commentaire de review est juste

La remarque dit:

> the new read-only state is not represented correctly in the main table or during live updates

C'est exact :

- le live connaît `2`
- l'agrégation connaît `2`
- le ratio ne connaît pas `2`

Donc la synthèse ne représente pas fidèlement la série affichée.

## Analyse fonctionnelle: que devrait représenter le ratio ?

Le libellé actuel est :

```text
(nombre de 1) / (nombre 0 + 1)
```

Ce libellé a été conçu avant l'arrivée du statut `2`.

À partir du moment où `2` devient un état métier visible, il faut choisir explicitement l'une de ces politiques :

### Option A: conserver le sens historique strict

Le ratio reste :

- numérateur = nombre de `1`
- dénominateur = nombre de `0` + `1`

Dans ce cas, il faut afficher séparément le nombre de `2`, sinon le tableau est trompeur.

Exemple :

```text
0 / 0  (read only: 360)
```

### Option B: faire du ratio un vrai "signal ratio"

Le dénominateur devient :

- `0 + 1 + 2`

Et on peut afficher :

```text
green / all_signals
```

Exemple :

```text
0 / 360
```

Ce n'est plus ambigu.

### Option C: abandonner ce ratio pour un triplet

Par exemple :

```text
1: 120 / 2: 40 / 0: 10
```

C'est le plus fidèle si `2` devient un vrai état métier important.

## Synthèse

### P1

Le point P1 est une vraie régression de monitoring.

Pourquoi :

- toutes les erreurs du probe proxy sont reclassées en `2`
- les échecs durs ne sont plus distingués
- la garde `if ($available === 0)` devient morte dans ce bloc
- le worker peut continuer dans un état faux

### P2

Le point P2 est une vraie incohérence de représentation.

Pourquoi :

- les séries connaissent `2`
- la UI connaît `2`
- le ratio ne connaît pas `2`
- un serveur bleu peut apparaître comme `0 / 0`, soit "pas de données"

## Recommandation

Sans modifier le code maintenant, la direction technique correcte est :

1. réintroduire la possibilité d'un vrai `0` dans le bloc proxy de [Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php)
2. n'utiliser `2` que pour un cas explicitement identifié comme read-only
3. décider contractuellement ce que doit représenter la colonne ratio dans `Server/state`
4. aligner ensuite l'UI, le live et la doc sur ce contrat

## Conclusion

Les deux commentaires de review sont corrects.

Le point P1 est le plus grave :

- il touche la vérité métier de la disponibilité
- et pas seulement le rendu

Le point P2 est moins dangereux, mais il rend `Server/state` ambigu dès qu'un bucket bleu apparaît.
