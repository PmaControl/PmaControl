---
title: TOI wsrep_RSU_method in PXC 5.6.24 and up
source:
  name: Percona Blog
  url: https://www.percona.com/blog/toi-wsrep_rsu_method-pxc-5-6-24/
  post_id: 9356
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2015-07-06T15:28:12'
published_at_gmt: '2015-07-06T15:28:12'
modified_at: '2026-05-04T22:38:30'
modified_at_gmt: '2026-05-04T22:38:30'
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
tags:
- Jay Janssen
- MySQL
- Percona XtraDB Cluster
- Primary
- pxc
- wsrep_RSU_method
tag_slugs:
- jay-janssen
- mysql
- percona-xtradb-cluster
- primary
- pxc
- wsrep_rsu_method
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TOI wsrep_RSU_method in PXC 5.6.24 and up

Source: [Percona Blog](https://www.percona.com/blog/toi-wsrep_rsu_method-pxc-5-6-24/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2015-07-06T15:28:12

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I noticed that in the latest release of Percona XtraDB Cluster (PXC), the behavior of wsrep_OSU_method changed somewhat. Prior to this release, the variable was GLOBAL only, meaning to use it you would: mysql> set GLOBAL wsrep_OSU_method='RSU'; mysql> ALTER TABLE ... mysql> set GLOBAL wsrep_OSU_method='TOI'; 1 2 3 mysql > set GLOBAL wsrep_OSU_method = 'RSU' ; mysql > ALTER TABLE . . . mysql > set GLOBAL wsrep_OSU_method = 'TOI' ; This had the (possibly negative) side-effect that ALL DDL’s issued on this node would be affected by the setting while in … Continued

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
