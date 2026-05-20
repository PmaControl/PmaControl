---
title: Interactive Debugging of Transaction Conflicts with TokuDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/interactive-debugging-of-transaction-conflicts-with-tokudb/
  post_id: 9813
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2013-10-16T16:08:53'
published_at_gmt: '2013-10-16T16:08:53'
modified_at: '2026-05-05T22:38:05'
modified_at_gmt: '2026-05-05T22:38:05'
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
- TokuDB
tag_slugs:
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Interactive Debugging of Transaction Conflicts with TokuDB

Source: [Percona Blog](https://www.percona.com/blog/interactive-debugging-of-transaction-conflicts-with-tokudb/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2013-10-16T16:08:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I am developing a concurrent application that uses TokuDB to store its database. Sometimes, one of my SQL statements returns with a ‘lock wait timeout exceeded’ error. How do I identify the cause of this error? First, I need to understand a little bit about how TokuDB transactions use locks. Then, I need to understand … Continued

## Structure detectee

- H2: Transactions and Locks
- H2: Example
