---
title: Taking backups on Percona XtraDB Cluster (without stalls!)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/taking-backups-percona-xtradb-cluster-without-stalls-flow-control/
  post_id: 7422
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2013-10-08T10:00:50'
published_at_gmt: '2013-10-08T10:00:50'
modified_at: '2026-05-04T22:09:05'
modified_at_gmt: '2026-05-04T22:09:05'
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
tags:
- flow control
tag_slugs:
- flow-control
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Taking backups on Percona XtraDB Cluster (without stalls!)

Source: [Percona Blog](https://www.percona.com/blog/taking-backups-percona-xtradb-cluster-without-stalls-flow-control/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2013-10-08T10:00:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I occasionally see customers who are taking backups from their PXC clusters that complain that the cluster “stalls” during the backup. As I wrote about in a previous blog post, often these stalls are really just Flow Control. But why would a backup cause Flow control? Most backups I know of (even Percona XtraBackup) take … Continued

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
