---
title: High Insertion Rates into a TokuDB Table with Durable Transactions
source:
  name: Percona Blog
  url: https://www.percona.com/blog/high-insertion-rates-into-a-tokudb-table-with-durable-transactions/
  post_id: 9493
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2010-01-05T22:33:54'
published_at_gmt: '2010-01-05T22:33:54'
modified_at: '2026-03-25T18:13:47'
modified_at_gmt: '2026-03-25T18:13:47'
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
- ACID
- iiBench
- MySQL
- TokuDB
tag_slugs:
- acid
- iibench
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# High Insertion Rates into a TokuDB Table with Durable Transactions

Source: [Percona Blog](https://www.percona.com/blog/high-insertion-rates-into-a-tokudb-table-with-durable-transactions/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2010-01-05T22:33:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We recently made transactions in TokuDB 3.0 durable. We write table changes into a log file so that in the event of a crash, the table changes up to the last checkpoint can be replayed. Durability requires the log file to be fsync’ed when a transaction is committed. Unfortunately, fsync’s are not free, and may … Continued

## Structure detectee

- H3: Decrease the fsync cost
- H3: Amortize the fsync cost with large transactions
- H3: Relax durability
- H3: Summary
