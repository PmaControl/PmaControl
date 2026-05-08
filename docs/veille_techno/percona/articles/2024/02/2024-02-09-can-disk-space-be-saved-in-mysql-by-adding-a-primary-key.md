---
title: Can Disk Space Be Saved in MySQL by Adding a Primary Key?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/can-disk-space-be-saved-in-mysql-by-adding-a-primary-key/
  post_id: 28114
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2024-02-09T14:07:25'
published_at_gmt: '2024-02-09T14:07:25'
modified_at: '2026-03-26T20:26:38'
modified_at_gmt: '2026-03-26T20:26:38'
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
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Disk-Space-Be-Saved-in-MySQL-by-Adding-a-Primary-Key.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Can Disk Space Be Saved in MySQL by Adding a Primary Key?

Source: [Percona Blog](https://www.percona.com/blog/can-disk-space-be-saved-in-mysql-by-adding-a-primary-key/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2024-02-09T14:07:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Historically, MySQL does not require explicit primary key defined on tables, and it’s like that by default till this day (MySQL version 8.3.0). Such a requirement is imposed through two replication methods, though: Group Replication and Percona XtraDB Cluster (PXC), where using tables without a primary key is not allowed by default. There are many … Continued

## Structure detectee

- H2: Hidden (internal) clustered index (GEN_CLUST_INDEX) vs. generated invisible primary key (GIPK)

## Images et graphiques reperes

- featured / image: [Can Disk Space Be Saved in MySQL by Adding a Primary Key?](https://www.percona.com/wp-content/uploads/2026/03/Disk-Space-Be-Saved-in-MySQL-by-Adding-a-Primary-Key.jpg)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
