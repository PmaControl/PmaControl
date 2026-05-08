---
title: PostgreSQL Tuple-Level Statistics With pgstattuple
source:
  name: Percona Blog
  url: https://www.percona.com/blog/postgresql-tuple-level-statistics-with-pgstattuple/
  post_id: 27511
source_author:
  name: Shivam Dhapatkar
  slug: shivam-dhapatkar
  url: https://www.percona.com/blog/author/shivam-dhapatkar/
  website: ''
published_at: '2023-10-09T12:08:01'
published_at_gmt: '2023-10-09T12:08:01'
modified_at: '2026-03-26T20:07:46'
modified_at_gmt: '2026-03-26T20:07:46'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
categories:
- Monitoring
- Percona Software
- PostgreSQL
category_slugs:
- monitoring
- percona-software
- postgresql
tags:
- Postgres Bloat
- postgres extensions
- PostgreSQL
- Shivam PP
- vacuum
tag_slugs:
- postgres-bloat
- postgres-extensions
- postgresql
- shivam-planetpostgresql
- vacuum
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-Tuple-Level-Statistics-pgstattuple.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# PostgreSQL Tuple-Level Statistics With pgstattuple

Source: [Percona Blog](https://www.percona.com/blog/postgresql-tuple-level-statistics-with-pgstattuple/)

Auteur source: [Shivam Dhapatkar](https://www.percona.com/blog/author/shivam-dhapatkar/)

Publication: 2023-10-09T12:08:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Since Postgres table bloat degrades database performance, we can improve its performance by removing the table bloat. We can use the pgstattuple extension to identify the bloated tables. This extension provides several functions for obtaining tuple-level statistics. Because the pgstattuple functions produce extensive page-level information, access to them is, by default, limited. Only the pg_stat_scan_tables … Continued

## Structure detectee

- H3: pgstattuple functions
- H3: pgstattuple queries to check table bloat

## Images et graphiques reperes

- featured / image: [PostgreSQL Tuple-Level Statistics With pgstattuple](https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-Tuple-Level-Statistics-pgstattuple.jpg)
