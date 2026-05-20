---
title: Implications of Metadata Locking Changes in MySQL 5.5
source:
  name: Percona Blog
  url: https://www.percona.com/blog/implications-of-metadata-locking-changes-in-mysql-5-5/
  post_id: 6574
source_author:
  name: Ovais Tariq
  slug: ovaistariq
  url: https://www.percona.com/blog/author/ovaistariq/
  website: http://www.percona.com/blog/
published_at: '2013-02-01T19:23:06'
published_at_gmt: '2013-02-01T19:23:06'
modified_at: '2026-04-28T21:49:24'
modified_at_gmt: '2026-04-28T21:49:24'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- alter table
- ddl
- metadata locking
- query cache
- table cache
- transaction
- Transaction isolation
tag_slugs:
- alter-table
- ddl
- metadata-locking
- query-cache
- table-cache
- transaction
- transaction-isolation
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Implications of Metadata Locking Changes in MySQL 5.5

Source: [Percona Blog](https://www.percona.com/blog/implications-of-metadata-locking-changes-in-mysql-5-5/)

Auteur source: [Ovais Tariq](https://www.percona.com/blog/author/ovaistariq/)

Publication: 2013-02-01T19:23:06

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

While most of the talk recently has mostly been around the new changes in MySQL 5.6 (and that is understandable), I have had lately some very interesting cases to deal with, with respect to the Metadata Locking related changes that were introduced in MySQL 5.5.3. It appears that the implications of Metadata Locking have not … Continued

## Structure detectee

- H3: Metadata Locking behavior prior to MySQL 5.5.3
- H3: Metadata Locking behavior starting MySQL 5.5.3
- H3: When can ALTER render the table inaccessible?
- H3: Metadata Locking and Query Cache
- H3: Metadata Locking and Table Cache
- H3: Consequences
