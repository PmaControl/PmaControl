# Serveurs VIP — Documentation fonctionnelle

Cette documentation décrit le fonctionnement **fonctionnel** des serveurs de type VIP
d’après les fichiers :

- `App/Controller/Aspirateur.php`
- `App/Controller/Dot3.php`

---

## 1) Objectif métier

Un serveur **VIP** représente une IP/DNS d’entrée qui redirige vers un serveur MySQL réel
ou un proxy. Le système doit :

1. **Résoudre la destination réelle** du VIP.
2. **Conserver l’historique** (destination précédente + date).
3. **Afficher deux flèches** dans Dot3 :
   - vers `destination_id` (style **filled**),
   - vers `destination_previous_id` (style **dashed**).

---

## 2) Règles métier principales

### 2.1 Association obligatoire
Chaque serveur VIP doit être associé à **un serveur classique ou un proxy**.

### 2.2 Mise à jour de l’historique
À chaque cycle :

- Si `destination_id != 0`
- et `destination_id != destination_previous_id`
- et **nouveau** `destination_id` différent de l’ancien

Alors :

- `destination_previous_id` prend l’ancienne valeur de `destination_id`
- `destination_previous_date` conserve la date associée à cette ancienne destination

### 2.3 Cas d’absence de destination
Si `destination_id == 0`, **on ne touche pas** à l’historique (previous).

---

## 3) Aspirateur.php — Collecte VIP

### 3.1 Déclenchement
Dans `tryMysqlConnection()` :

- si `is_vip = 1`, on **ne collecte pas** les métriques MySQL classiques.
- on appelle uniquement `buildVipMetrics()` puis export `vip`.

### 3.2 buildVipMetrics()

Cette fonction :

1. Résout l’IP VIP (DNS → IP si besoin).
2. Cherche la destination réelle (`resolveVipDestinationId`).
3. Récupère l’historique (`getPreviousVipDestinationState`).
4. Applique les règles métier de bascule :
   - changement de destination ⇒ l’ancienne devient `destination_previous_id`.
   - si `destination_id = 0` ⇒ on ne modifie pas le précédent.

### 3.3 Champs exportés

| Champ | Description |
|------|-------------|
| `ip` | IP VIP résolue |
| `port` | Port VIP |
| `destination_id` | Destination active |
| `destination_date` | Date activation active |
| `destination_previous_id` | Destination précédente |
| `destination_previous_date` | Date activation précédente |

---

## 4) Dot3.php — Rendu Graphique

### 4.1 Construction des liens VIP
Dans `buildLinkVIP()` :

- **Lien actif** vers `destination_id` → style **filled**.
- **Lien précédent** vers `destination_previous_id` → style **dashed**.

Les deux flèches partent du nœud VIP et pointent vers le serveur réel.

### 4.2 Ports Graphviz utilisés

- `vip_active` pour le lien actif
- `vip_previous` pour le lien previous

---

## 5) Résumé

Le système VIP suit ce cycle :

1. Aspirateur calcule `destination_id` et historise l’ancienne valeur.
2. Dot3 affiche **deux flèches** :
   - active (filled),
   - previous (dashed).

---

> Document limité aux fichiers `Aspirateur.php` et `Dot3.php`.