---
title: PXC – Incremental State transfers in detail
source:
  name: Percona Blog
  url: https://www.percona.com/blog/pxc-incremental-state-transfers-in-detail/
  post_id: 9426
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2015-08-05T10:00:23'
published_at_gmt: '2015-08-05T10:00:23'
modified_at: '2026-04-28T22:23:03'
modified_at_gmt: '2026-04-28T22:23:03'
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
- Galera's
- incremental state transfers
- Jay Janssen
- MySQL
- Percona XtraDB Cluster
- Primary
- pxc
tag_slugs:
- galeras
- incremental-state-transfers
- jay-janssen
- mysql
- percona-xtradb-cluster
- primary
- pxc
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# PXC – Incremental State transfers in detail

Source: [Percona Blog](https://www.percona.com/blog/pxc-incremental-state-transfers-in-detail/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2015-08-05T10:00:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

IST Basics State transfers in Galera remain a mystery to most people. Incremental State transfers (as opposed to full State Snapshot transfers) are used under the following conditions: The Joiner node reports Galera a valid Galera GTID to the cluster The Donor node selected contains all the transactions the Joiner needs to catch … Continued

## Structure detectee

- H2: IST Basics
- H2: IST states
- H2: Joining: receiving State Transfer
- H2: Joining
- H2: Joined
- H2: Flow control during Joining/Joined states
- H2: Synced
- H2: Conclusion

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
