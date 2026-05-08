---
title: 'Understanding Basic Flow Control Activity in MySQL Group Replication: Part One'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understanding-basic-flow-control-activity-in-mysql-group-replication-part-one/
  post_id: 28730
source_author:
  name: Anil Joshi
  slug: anil-joshi
  url: https://www.percona.com/blog/author/anil-joshi/
  website: ''
published_at: '2024-06-24T17:44:30'
published_at_gmt: '2024-06-24T17:44:30'
modified_at: '2026-03-26T20:26:15'
modified_at_gmt: '2026-03-26T20:26:15'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:monitoring:2104
- category:mysql:83
categories:
- Insight for DBAs
- Insight for Developers
- Monitoring
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- monitoring
- mysql
tags:
- flow control
- group replication
- MySQL
- MySQL Group Replication
- MySQL Replication
- mysql-and-variants
tag_slugs:
- flow-control
- group-replication
- mysql
- mysql-group-replication
- mysql-replication
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Understanding-Basic-Flow-Control-Activity-in-MySQL-Group-Replication.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Understanding Basic Flow Control Activity in MySQL Group Replication: Part One

Source: [Percona Blog](https://www.percona.com/blog/understanding-basic-flow-control-activity-in-mysql-group-replication-part-one/)

Auteur source: [Anil Joshi](https://www.percona.com/blog/author/anil-joshi/)

Publication: 2024-06-24T17:44:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Flow control is not a new term, and we have already heard it a lot of times in Percona XtraDB Cluster/Galera-based environments. In very simple terms, it means the cluster node can’t keep up with the cluster write pace. The write rate is too high, or the nodes are oversaturated. Flow control helps avoid excessive … Continued

## Structure detectee

- H3: Session1:
- H3: Session2:
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Understanding Basic Flow Control Activity in MySQL Group Replication: Part One](https://www.percona.com/wp-content/uploads/2026/03/Understanding-Basic-Flow-Control-Activity-in-MySQL-Group-Replication.jpg)

## Auteur source

I am Anil Joshi, and I work for Percona as a support engineer. I've worked with some well-known Open Source database technologies (MySQL/MariaDB, MongoDB, and Redis) for almost ten years. I am keenly interested in learning new databases and writing database content.
