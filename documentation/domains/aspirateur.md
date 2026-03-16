# Domaine Aspirateur

## Role metier

`Aspirateur` est le moteur de collecte. Il explore les serveurs MySQL et les hotes systeme, detecte des roles, reconstruit des liaisons et produit les donnees qui alimentent monitoring, topologie, API et dashboards. C'est le capteur principal du produit.

## Capacites observees

- connexion MySQL et SSH
- collecte hardware, OS, CPU, memoire, disques
- lecture processlist, variables, wsrep, metadata locks, digests
- detection d'alias, de VIP, de proxys et de destinations de service
- production de fichiers pivots et empreintes md5
- alimentation des structures d'integration et de series temporelles

## Regles metier detaillees

### Identification du serveur reel

Le domaine essaie de relier plusieurs representations d'un meme noeud:

- IP de connexion
- hostname expose
- alias DNS
- IP remontee par SSH
- `wsrep_node_address`
- VIP eventuelles

Le metier ici est tres clair: la plateforme doit raisonner sur l'identite technique reelle, meme en presence de NAT, VIP ou couches proxy.

### Classification automatique

Le code peut faire evoluer le referentiel applicatif a partir des constats de collecte, par exemple en marquant un serveur comme proxy ou en mettant a jour des liaisons de VIP. La collecte n'est pas neutre: elle enrichit et corrige le modele de reference.

### Collecte hybride

Aspirateur combine deux mondes:

- verite BD exposee par le serveur MySQL
- verite systeme exposee par SSH

Cette dualite permet de reconstruire une image plus fiable du noeud que ne le ferait une seule source.

## Architecture metier

`Aspirateur` devrait idealement etre decoupe en sous-services:

- `MysqlProbeService`
- `SshProbeService`
- `VipResolutionService`
- `PivotWriterService`
- `RuntimeClassificationService`
- `ProxyAndClusterDiscoveryService`

## Valeur metier

C'est ce domaine qui rend possible:

- le monitoring fin
- les vues cluster
- les graphes DOT
- la remontee de diagnostics offline
- l'auto-discovery de structure

## Risques et chantiers

- forte dependance aux binaires systeme
- grand nombre d'appels shell/SSH
- logique de resolution complexe a tester
- effets de bord sur le referentiel
