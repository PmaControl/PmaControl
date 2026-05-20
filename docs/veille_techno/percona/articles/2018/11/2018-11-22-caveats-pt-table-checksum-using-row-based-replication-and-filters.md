---
title: Caveats With pt-table-checksum Using Row-Based Replication, and Replication Filters
source:
  name: Percona Blog
  url: https://www.percona.com/blog/caveats-pt-table-checksum-using-row-based-replication-and-filters/
  post_id: 19598
source_author:
  name: James Lawrie
  slug: james-lawrie
  url: https://www.percona.com/blog/author/james-lawrie/
  website: ''
published_at: '2018-11-22T15:20:50'
published_at_gmt: '2018-11-22T15:20:50'
modified_at: '2026-05-05T20:22:55'
modified_at_gmt: '2026-05-05T20:22:55'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- pt-table-checksum
- row based
tag_slugs:
- pt-table-checksum
- row-based
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pt-table-checksum-caveat-for-replication-use.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Caveats With pt-table-checksum Using Row-Based Replication, and Replication Filters

Source: [Percona Blog](https://www.percona.com/blog/caveats-pt-table-checksum-using-row-based-replication-and-filters/)

Auteur source: [James Lawrie](https://www.percona.com/blog/author/james-lawrie/)

Publication: 2018-11-22T15:20:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As per the documentation, pt-table-checksum is a tool to perform online replication consistency checks by executing checksum queries on the master, which produces different results on replicas that are inconsistent with the master. The master and each slave insert checksums into the percona.checksums table, and these are later compared for differences. It’s fairly obvious that … Continued

## Structure detectee

- H2: More resources

## Images et graphiques reperes

- featured / image: [Caveats With pt-table-checksum Using Row-Based Replication, and Replication Filters](https://www.percona.com/wp-content/uploads/2026/03/pt-table-checksum-caveat-for-replication-use.jpg)

## Auteur source

James has spent over a decade in a variety of Linux and MySQL support roles, with a specific interest in reliability and performance through simplicity. He spends his free time riding motorbikes, lifting weights just to put them back down again, or studying Polish.
