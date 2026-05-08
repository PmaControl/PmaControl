---
title: Avoiding SST when adding new Percona XtraDB Cluster node
source:
  name: Percona Blog
  url: https://www.percona.com/blog/avoiding-sst-when-adding-new-percona-xtradb-cluster-node/
  post_id: 3709
source_author:
  name: Frederic Descamps
  slug: lefred
  url: https://www.percona.com/blog/author/lefred/
  website: http://www.lefred.be
published_at: '2012-08-02T13:11:05'
published_at_gmt: '2012-08-02T13:11:05'
modified_at: '2026-04-28T21:38:01'
modified_at_gmt: '2026-04-28T21:38:01'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Avoiding SST when adding new Percona XtraDB Cluster node

Source: [Percona Blog](https://www.percona.com/blog/avoiding-sst-when-adding-new-percona-xtradb-cluster-node/)

Auteur source: [Frederic Descamps](https://www.percona.com/blog/author/lefred/)

Publication: 2012-08-02T13:11:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Some people want to use a backup to prepare a new Percona XtraDB Cluster node. They want this to avoid State Snapshot Transfer that could slow down the donor (depending of the SST method you are using, the donor can be blocked. I will cover this in a future blog post). As backup are generally … Continued

## Auteur source

Frédéric joined Percona in June 2011, he is an experienced Open Source consultant with expertise in infrastructure projects as well in development tracks and database administration. Frédéric is a believer of devops culture.
