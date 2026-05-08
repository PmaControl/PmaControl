---
title: Verifying backup integrity with CHECK TABLES
source:
  name: Percona Blog
  url: https://www.percona.com/blog/verifying-backup-integrity-with-check-tables/
  post_id: 3339
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2012-02-01T17:03:34'
published_at_gmt: '2012-02-01T17:03:34'
modified_at: '2026-03-23T22:14:34'
modified_at_gmt: '2026-03-23T22:14:34'
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
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Verifying backup integrity with CHECK TABLES

Source: [Percona Blog](https://www.percona.com/blog/verifying-backup-integrity-with-check-tables/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2012-02-01T17:03:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

An attendee to Espen’s recent webinar asked how to check tables for corruption. This kind of ties into my recent post on InnoDB’s handling of corrupted pages, because the best way to check for corruption is with CHECK TABLES, but if a page is corrupt, InnoDB will crash the server to prevent access to the … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
