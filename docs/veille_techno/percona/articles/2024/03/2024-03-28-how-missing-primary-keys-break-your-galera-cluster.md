---
title: How Missing Primary Keys Break Your Galera Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-missing-primary-keys-break-your-galera-cluster/
  post_id: 28274
source_author:
  name: Yoann La Cancellera
  slug: yoann-lacancellera
  url: https://www.percona.com/blog/author/yoann-lacancellera/
  website: ''
published_at: '2024-03-28T16:50:18'
published_at_gmt: '2024-03-28T16:50:18'
modified_at: '2026-03-26T20:26:35'
modified_at_gmt: '2026-03-26T20:26:35'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- galera
- Galera Flow Control
- MySQL
- mysql-and-variants
- Percona XtraDB Cluster
- primary key
tag_slugs:
- galera
- galera-flow-control
- mysql
- mysql-and-variants
- percona-xtradb-cluster
- primary-key
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/How-Missing-Primary-Keys-Break-Your-Galera-Cluster.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Missing Primary Keys Break Your Galera Cluster

Source: [Percona Blog](https://www.percona.com/blog/how-missing-primary-keys-break-your-galera-cluster/)

Auteur source: [Yoann La Cancellera](https://www.percona.com/blog/author/yoann-lacancellera/)

Publication: 2024-03-28T16:50:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Any Galera documentation about limitations will state that tables must have primary keys. They state that DELETEs are unsupported and other DMLs could have unwanted side-effects such as inconsistent ordering: rows can appear in different order on different nodes in your cluster. If you are not actively relying on row orders, this could seem acceptable. … Continued

## Structure detectee

- H2: The risk of lacking primary keys
- H2: Story of a production recurrent failure
- H2: Why such severe symptoms
- H2: Symptoms to look for
- H3: Quick reproduction of the issue
- H3: Blocked commits
- H3: Flow control
- H3: Locks
- H2: Solutions and workarounds
- H3: Find the responsible table
- H3: slave_rows_search_algorithms
- H3: pxc_strict_mode
- H2: Having primary keys everywhere

## Images et graphiques reperes

- featured / image: [How Missing Primary Keys Break Your Galera Cluster](https://www.percona.com/wp-content/uploads/2026/03/How-Missing-Primary-Keys-Break-Your-Galera-Cluster.jpg)
