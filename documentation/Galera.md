# Galera Cluster — Règles PmaControl

Cette documentation décrit les règles spécifiques utilisées par **Dot3** pour
calculer l’état du cluster Galera et notamment l’indicateur **Nodes available**
affiché dans la box Galera (`Nodes available : X/Y`).

## Définition de *Nodes available*

Dans Dot3, **X** (Nodes available) ne correspond pas simplement aux nœuds
en ligne (`mysql_available=1`). L’objectif est de refléter *les nœuds réellement
actifs et utilisables dans le quorum*.

La règle appliquée est :

- Le nœud doit être **Primary** (`wsrep_cluster_status=Primary`)
- Et il doit être :
  - **Synced** (`wsrep_local_state_comment=Synced`)
  - **ou** en état **Donor/Desync/Unsync** **si** `wsrep_desync = OFF`

Autrement dit :

```
Nodes available (X) =
  mysql_available=1
  AND wsrep_cluster_status=Primary
  AND (
        wsrep_local_state_comment=Synced
        OR (Donor/Desync/Unsync AND wsrep_desync=OFF)
      )
```

✅ Cette règle permet d’exclure les nœuds :
- `Non-Primary`
- `Disconnected`
- `Inconsistent`
- `Donor` **avec** `wsrep_desync=ON` (désynchronisé volontairement)

## Nombre total de nœuds (Y)

Le dénominateur **Y** est **toujours le nombre total de nœuds détectés dans le cluster**.
Il n’est **jamais modifié** par cette règle (même si un nœud est offline).

Cela permet de conserver un indicateur stable sur la taille réelle du cluster.

## Emplacement dans le code

- Calcul effectué dans :
  `pmacontrol/App/Controller/Dot3.php` → `buildGaleraCluster()`

- Affichage dans :
  `pmacontrol/App/Library/Graphviz.php` → `generateGalera()`

***

Si tu veux compléter cette doc (ex : segments, quorum, garb), dis‑le moi 👍