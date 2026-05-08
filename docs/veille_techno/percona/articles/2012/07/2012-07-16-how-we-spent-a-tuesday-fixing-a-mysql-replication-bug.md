---
title: How We Spent a Tuesday Fixing a MySQL Replication Bug
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-we-spent-a-tuesday-fixing-a-mysql-replication-bug/
  post_id: 9694
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2012-07-16T14:45:07'
published_at_gmt: '2012-07-16T14:45:07'
modified_at: '2026-03-25T18:23:26'
modified_at_gmt: '2026-03-25T18:23:26'
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
- MySQL
- NewSQL
- Replication
- Storage Engine
- TokuDB
- Tokutek
tag_slugs:
- mysql
- newsql
- replication
- storage-engine
- tokudb
- tokutek
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How We Spent a Tuesday Fixing a MySQL Replication Bug

Source: [Percona Blog](https://www.percona.com/blog/how-we-spent-a-tuesday-fixing-a-mysql-replication-bug/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2012-07-16T14:45:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We found a simple XA transaction that crashes MySQL 5.5 replication. This simple transaction inserts a row into an InnoDB table and a TokuDB table. The bug was caused by a flaw in the logging code exposed by the transaction’s use of two XA storage engines (TokuDB and InnoDB). This bug was fixed in the … Continued
