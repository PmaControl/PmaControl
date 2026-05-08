---
title: Using keepalived for HA on top of Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-keepalived-ha-top-percona-xtradb-cluster/
  post_id: 7456
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2013-10-15T12:33:28'
published_at_gmt: '2013-10-15T12:33:28'
modified_at: '2026-05-04T22:10:26'
modified_at_gmt: '2026-05-04T22:10:26'
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
- clustercheck
- HA
- haproxy
- keepalived
- Percona XtraDB Cluster
tag_slugs:
- clustercheck
- ha
- haproxy
- keepalived
- percona-xtradb-cluster
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using keepalived for HA on top of Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/using-keepalived-ha-top-percona-xtradb-cluster/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2013-10-15T12:33:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona XtraDB Cluster (PXC) itself manages quorum and node failure. Minorities of nodes in a network partition situation will move themselves into a Non-primary state and not allow any DB activity. Nodes in such a state will be easily detectable via SHOW GLOBAL STATUS variables. It’s common to use HAproxy with PXC for load balancing purposes, but … Continued

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
