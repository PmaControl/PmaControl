---
title: Improved InnoDB fast index creation
source:
  name: Percona Blog
  url: https://www.percona.com/blog/improved-innodb-fast-index-creation/
  post_id: 3174
source_author:
  name: Alexey Kopytov
  slug: alexey-kopytov
  url: https://www.percona.com/blog/author/alexey-kopytov/
  website: ''
published_at: '2011-11-07T06:42:00'
published_at_gmt: '2011-11-07T06:42:00'
modified_at: '2026-05-05T17:38:19'
modified_at_gmt: '2026-05-05T17:38:19'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
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

# Improved InnoDB fast index creation

Source: [Percona Blog](https://www.percona.com/blog/improved-innodb-fast-index-creation/)

Auteur source: [Alexey Kopytov](https://www.percona.com/blog/author/alexey-kopytov/)

Publication: 2011-11-07T06:42:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of the serious limitations in the fast index creation feature introduced in the InnoDB plugin is that it only works when indexes are explicitly created using ALTER TABLE or CREATE INDEX. Peter has already blogged about it before, here I’ll just briefly reiterate other cases that might benefit from that feature: when ALTER TABLE … Continued

## Structure detectee

- H2: ALTER TABLE
- H2: OPTIMIZE TABLE
- H2: mysqldump
- H2: Caveats:
- H2: References:

## Auteur source

Alexey Kopytov is a Principal Software Engineer at Percona. Before joining Percona in 2010 he was a member of the MySQL development team at Oracle. His focus at Percona is development of both Percona Server and Percona XtraBackup.
