---
title: The Trouble with Point Queries
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-trouble-with-point-queries/
  post_id: 9443
source_author:
  name: Tokutek
  slug: tokutek
  url: https://www.percona.com/blog/author/tokutek/
  website: ''
published_at: '2008-01-18T11:44:07'
published_at_gmt: '2008-01-18T11:44:07'
modified_at: '2026-03-25T18:07:01'
modified_at_gmt: '2026-03-25T18:07:01'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The Trouble with Point Queries

Source: [Percona Blog](https://www.percona.com/blog/the-trouble-with-point-queries/)

Auteur source: [Tokutek](https://www.percona.com/blog/author/tokutek/)

Publication: 2008-01-18T11:44:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Insertion and Queries Databases are complicated beasts, but I’d like to focus on the storage engine, just the part that talks to the storage system, and doesn’t have to worry about SQL, etc.: just transactions, concurrency, compression, updates and queries. In the next couple of blog entry, I’d like to just focus on updates (insertions … Continued
