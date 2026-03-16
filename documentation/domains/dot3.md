# Domaine Dot3

## Role metier

`Dot3` est le moteur de cartographie avancée. Il fabrique des graphes de topologie a partir des donnees de collecte et du referentiel pour representer masters, replicas, proxys, VIP, Galera, MaxScale et liens de service.

## Capacites observees

- creation d'objets d'information `dot3_information`
- generation de groupes de rendu par type de topologie
- construction de liens et de labels
- persistance de graphes et clusters derives
- export/download des graphes
- enrichissement des noeuds VIP et ProxySQL
- calcul d'indices utiles au SST Galera

## Regles metier detaillees

### Le graphe n'est pas decoratif

Les graphes servent a produire une representation exploitable de l'etat du parc. Les labels, indices SST, hostgroups, VIP et groupes MaxScale montrent une intention metier forte: aider l'operateur a comprendre les dependances en un coup d'oeil.

### Les entites inconnues doivent rester visibles

Le code cree ou signale des noeuds ProxySQL inconnus. Cela traduit une regle importante: l'incoherence de topologie doit etre visualisee, pas masquee.

### Le rendu doit fusionner plusieurs sources

`Dot3` consomme alias, metrics, wsrep, VIP, couleurs, legendes et informations de cluster. La topologie est donc une projection composee, pas une simple lecture d'une table.

## Valeur pour l'exploitation

Sans ce domaine, PmaControl perdrait une grande part de sa valeur differenciante: la comprehension topologique des environnements complexes. C'est un domaine de restitution tres riche, au croisement du monitoring, du referentiel et des proxys.

## Chantiers recommandes

- formaliser un modele de graphe interne independant de l'UI
- tester les resolvers de liens et de destinations
- sortir le rendu Graphviz et la persistance en services distincts
- standardiser les couleurs, legendes et conventions de nommage
