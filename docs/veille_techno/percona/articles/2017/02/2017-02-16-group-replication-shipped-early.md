---
title: 'Group Replication: Shipped Too Early'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/group-replication-shipped-early/
  post_id: 16351
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2017-02-16T00:02:25'
published_at_gmt: '2017-02-16T00:02:25'
modified_at: '2026-05-05T19:44:21'
modified_at_gmt: '2026-05-05T19:44:21'
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
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- group replication
- High Availability
- MySQL
- Oracle
tag_slugs:
- group-replication
- high-availability
- mysql
- oracle
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/group-replication.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Group Replication: Shipped Too Early

Source: [Percona Blog](https://www.percona.com/blog/group-replication-shipped-early/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2017-02-16T00:02:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post is my overview of Group Replication technology. With Oracle clearly entering the “open source high availability solutions” arena with the release of their brand new Group Replication solution, I believe it is time to review the quality of the first GA (production ready) release. TL;DR: Having examined the technology, it is my … Continued

## Structure detectee

- H3: No automatic provisioning
- H3: Bug: stale reads on nodes
- H3: Bug: nodes become unusable after a big transaction, refusing to execute further transactions
- H3: Obscure error messages
- H3: Discussion:
- H3: My recommendation:

## Images et graphiques reperes

- featured / image: [Group Replication: Shipped Too Early](https://www.percona.com/wp-content/uploads/2026/03/group-replication.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
