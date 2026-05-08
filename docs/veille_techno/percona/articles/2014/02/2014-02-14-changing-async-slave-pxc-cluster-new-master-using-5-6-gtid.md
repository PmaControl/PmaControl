---
title: Changing an async slave of a PXC cluster to a new Master using 5.6 and GTID
source:
  name: Percona Blog
  url: https://www.percona.com/blog/changing-async-slave-pxc-cluster-new-master-using-5-6-gtid/
  post_id: 7809
source_author:
  name: Frederic Descamps
  slug: lefred
  url: https://www.percona.com/blog/author/lefred/
  website: http://www.lefred.be
published_at: '2014-02-14T11:00:24'
published_at_gmt: '2014-02-14T11:00:24'
modified_at: '2026-04-28T22:01:56'
modified_at_gmt: '2026-04-28T22:01:56'
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
category_slugs:
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Changing an async slave of a PXC cluster to a new Master using 5.6 and GTID

Source: [Percona Blog](https://www.percona.com/blog/changing-async-slave-pxc-cluster-new-master-using-5-6-gtid/)

Auteur source: [Frederic Descamps](https://www.percona.com/blog/author/lefred/)

Publication: 2014-02-14T11:00:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Before Percona XtraBackup 2.1.7 and Percona XtraDB Cluster 5.6.15-25.3, rsync was the only SST method supporting GTID in the way that it was possible to move an asynchronous slave from one Galera node to another one (related bug). Indeed, previous versions of Percona XtraBackup didn’t copy any binary log and due to that, moving the … Continued

## Auteur source

Frédéric joined Percona in June 2011, he is an experienced Open Source consultant with expertise in infrastructure projects as well in development tracks and database administration. Frédéric is a believer of devops culture.
