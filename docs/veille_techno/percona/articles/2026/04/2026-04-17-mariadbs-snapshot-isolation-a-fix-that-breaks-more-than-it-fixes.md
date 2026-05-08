---
title: 'MariaDB’s Snapshot Isolation: A Fix That Breaks More Than It Fixes'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mariadbs-snapshot-isolation-a-fix-that-breaks-more-than-it-fixes/
  post_id: 43777
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2026-04-17T07:58:28'
published_at_gmt: '2026-04-17T07:58:28'
modified_at: '2026-04-17T07:59:59'
modified_at_gmt: '2026-04-17T07:59:59'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
matched_filters:
- category:mariadb:1281
- category:mysql:83
categories:
- MariaDB
- MySQL
category_slugs:
- mariadb
- mysql
tags:
- MariaDB
- MySQL
tag_slugs:
- mariadb
- mysql
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MariaDB’s Snapshot Isolation: A Fix That Breaks More Than It Fixes

Source: [Percona Blog](https://www.percona.com/blog/mariadbs-snapshot-isolation-a-fix-that-breaks-more-than-it-fixes/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2026-04-17T07:58:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Jepsen’s analysis of MySQL 8.0.34 walked through a set of concurrency and isolation anomalies in InnoDB. MariaDB, which inherits the same codebase, took the report seriously and shipped a response: a new server variable called innodb_snapshot_isolation, turned on by default starting in 11.8. The announcement claims that with the flag enabled, Repeatable Read in MariaDB … Continued

## Structure detectee

- H2: A quick refresher on what snapshot isolation promises
- H2: What MySQL does today
- H2: What MariaDB is supposed to do
- H2: What actually happens
- H2: The another problem: compatibility
- H2: Where that leaves us

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
