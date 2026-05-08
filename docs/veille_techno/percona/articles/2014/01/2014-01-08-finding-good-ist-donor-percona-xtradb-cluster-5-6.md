---
title: Finding a good IST donor in Percona XtraDB Cluster 5.6
source:
  name: Percona Blog
  url: https://www.percona.com/blog/finding-good-ist-donor-percona-xtradb-cluster-5-6/
  post_id: 7652
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2014-01-08T11:00:45'
published_at_gmt: '2014-01-08T11:00:45'
modified_at: '2026-05-05T16:53:22'
modified_at_gmt: '2026-05-05T16:53:22'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- Gcache
- IST donor
- Jay Janssen
- Percona XtraDB Cluster 5.6
tag_slugs:
- gcache
- ist-donor
- jay-janssen
- percona-xtradb-cluster-5-6
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Finding a good IST donor in Percona XtraDB Cluster 5.6

Source: [Percona Blog](https://www.percona.com/blog/finding-good-ist-donor-percona-xtradb-cluster-5-6/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2014-01-08T11:00:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Gcache and IST The Gcache is a memory-based cache of recent Galera transactions that is local to each node in a cluster. If a node leaves and rejoins the cluster, it can use the gcache from another node that stayed in the cluster (i.e., its donor node) to fetch the transactions it missed (IST) as … Continued

## Structure detectee

- H2: Gcache and IST
- H2: Along comes PXC 5.6.15 RC1
- H2: What it looks like

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
