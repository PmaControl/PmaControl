---
title: Doing a rolling upgrade of Percona XtraDB Cluster from 5.5 to 5.6
source:
  name: Percona Blog
  url: https://www.percona.com/blog/rolling-upgrade-percona-xtradb-cluster-5-5-5-6/
  post_id: 8095
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2014-05-08T10:00:34'
published_at_gmt: '2014-05-08T10:00:34'
modified_at: '2026-03-25T17:30:19'
modified_at_gmt: '2026-03-25T17:30:19'
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
- galera
- Jay Janssen
- Percona XtraDB Cluster
- Upgrade
tag_slugs:
- galera
- jay-janssen
- percona-xtradb-cluster
- upgrade
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Doing a rolling upgrade of Percona XtraDB Cluster from 5.5 to 5.6

Source: [Percona Blog](https://www.percona.com/blog/rolling-upgrade-percona-xtradb-cluster-5-5-5-6/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2014-05-08T10:00:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Overview Percona XtraDB Cluster 5.6 has been GA for several months now and people are thinking more and more about moving from 5.5 to 5.6. Most people don’t want to upgrade all at once, but would prefer a rolling upgrade to avoid downtime and ensure 5.6 is behaving in a stable fashion before putting all of … Continued

## Structure detectee

- H2: Overview
- H2: The basic upgrade flow
- H2: Why can’t I write to the 5.6 nodes?
- H2: Some alternatives
- H3: Does writing to the 5.6 nodes REALLY break things?
- H3: Using Async replication to upgrade
- H3: Just take the outage
- H2: Conclusion

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
