---
title: Is Synchronous Replication right for your app?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/is-synchronous-replication-right-for-your-app/
  post_id: 6955
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2013-05-14T10:00:46'
published_at_gmt: '2013-05-14T10:00:46'
modified_at: '2026-05-04T22:04:37'
modified_at_gmt: '2026-05-04T22:04:37'
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
- async
- Galera replication
- hotspots
- Jay Janssen
- Percona XtraDB Cluster
- pxc
- Synchronous Replication
tag_slugs:
- async
- galera-replication
- hotspots
- jay-janssen
- percona-xtradb-cluster
- pxc
- synchronous-replication
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Is Synchronous Replication right for your app?

Source: [Percona Blog](https://www.percona.com/blog/is-synchronous-replication-right-for-your-app/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2013-05-14T10:00:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I talk with lot of people who are really interested in Percona XtraDB Cluster (PXC) and mostly they are interested in PXC as a high-availability solution. But, what they tend not to think too much about is if moving from async to synchronous replication is right for their application or not. Facts about Galera replication … Continued

## Structure detectee

- H2: Facts about Galera replication
- H2: Callaghan’s Law
- H2: Applied to a standalone Innodb instance
- H2: What about semi-sync MySQL replication?
- H2: Applied to a Galera cluster
- H2: What about WAN clusters?
- H2: Some things the rule does not mean on Galera
- H2: So what about my application?
- H2: Examples of hotspots
- H2: Results
- H2: Workarounds
- H3: Write to one node
- H3: wsrep_retry_autocommit
- H3: retry deadlocks
- H3: batch writes
- H3: change your schema
- H2: Conclusion

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
