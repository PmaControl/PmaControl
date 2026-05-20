---
title: Understanding Multi-node writing conflict metrics in Percona XtraDB Cluster and Galera
source:
  name: Percona Blog
  url: https://www.percona.com/blog/understanding-multi-node-writing-conflict-metrics-in-percona-xtradb-cluster-and-galera/
  post_id: 6456
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2012-11-20T21:33:44'
published_at_gmt: '2012-11-20T21:33:44'
modified_at: '2026-05-05T23:06:11'
modified_at_gmt: '2026-05-05T23:06:11'
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

# Understanding Multi-node writing conflict metrics in Percona XtraDB Cluster and Galera

Source: [Percona Blog](https://www.percona.com/blog/understanding-multi-node-writing-conflict-metrics-in-percona-xtradb-cluster-and-galera/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2012-11-20T21:33:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have addressed previously how multi-node writing causes unexpected deadlocks in PXC, at least, it is unexpected unless you know how Galera replication works. This is a complicated topic and I personally feel like I’m only just starting to wrap my head around it. The magic of Galera replication The short of it is that … Continued

## Structure detectee

- H2: The magic of Galera replication
- H2: Seeing when replication conflicts happen
- H2: wsrep_local_cert_failures
- H2: wsrep_local_bf_aborts
- H2: Testing it all out

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
