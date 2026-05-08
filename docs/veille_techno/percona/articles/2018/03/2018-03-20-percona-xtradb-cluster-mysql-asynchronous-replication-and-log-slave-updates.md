---
title: MySQL Asynchronous Replication, Percona XtraDB Cluster, and log-slave-updates
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-mysql-asynchronous-replication-and-log-slave-updates/
  post_id: 18254
source_author:
  name: Kenn Takara
  slug: kenn-takara
  url: https://www.percona.com/blog/author/kenn-takara/
  website: ''
published_at: '2018-03-20T00:06:06'
published_at_gmt: '2018-03-20T00:06:06'
modified_at: '2026-03-20T21:44:35'
modified_at_gmt: '2026-03-20T21:44:35'
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
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Figure2.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Asynchronous Replication, Percona XtraDB Cluster, and log-slave-updates

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-mysql-asynchronous-replication-and-log-slave-updates/)

Auteur source: [Kenn Takara](https://www.percona.com/blog/author/kenn-takara/)

Publication: 2018-03-20T00:06:06

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, I’ve been looking into issues with the interactions between MySQL asynchronous replication and Galera replication. In this blog post, I’d like to share what I’ve learned. MySQL asynchronous replication and Galera replication These interactions are complicated due to the number of factors involved (Galera replication vs. asynchronous replication, replication filters, and row-based vs. statement-based … Continued

## Structure detectee

- H2: MySQL asynchronous replication and Galera replication
- H4: The Problem
- H4: Some background information
- H4: Why is the data not replicating?
- H4: The Solution
- H4: Recommendations/Best Practices
- H4: You May Also Like

## Images et graphiques reperes

- featured / image: [MySQL Asynchronous Replication, Percona XtraDB Cluster, and log-slave-updates](https://www.percona.com/wp-content/uploads/2026/03/Figure2.png)
- content / image: [MySQL asynchronous replication](https://www.percona.com/wp-content/uploads/2026/03/Figure1-300x284.png)
- content / image: [MySQL asynchronous replication](https://www.percona.com/wp-content/uploads/2026/03/Figure2-300x284.png)
- content / image: [Get the Solution Brief](https://www.percona.com/wp-content/uploads/2026/03/4e81811d-657b-4eb1-83e7-6c6960ba57b3.png)
