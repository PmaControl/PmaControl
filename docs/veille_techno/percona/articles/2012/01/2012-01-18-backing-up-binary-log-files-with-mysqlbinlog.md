---
title: Backing up binary log files with mysqlbinlog
source:
  name: Percona Blog
  url: https://www.percona.com/blog/backing-up-binary-log-files-with-mysqlbinlog/
  post_id: 3298
source_author:
  name: Tamas Kozak
  slug: tamas
  url: https://www.percona.com/blog/author/tamas/
  website: ''
published_at: '2012-01-18T20:06:53'
published_at_gmt: '2012-01-18T20:06:53'
modified_at: '2026-05-04T20:52:49'
modified_at_gmt: '2026-05-04T20:52:49'
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

# Backing up binary log files with mysqlbinlog

Source: [Percona Blog](https://www.percona.com/blog/backing-up-binary-log-files-with-mysqlbinlog/)

Auteur source: [Tamas Kozak](https://www.percona.com/blog/author/tamas/)

Publication: 2012-01-18T20:06:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Backing up binary logs are essential part of creating good backup infrastructure as it gives you the possibility for point in time recovery. After restoring a database from backup you have the option to recover changes that happend after taking a backup. The problem with this approach was that you had to do periodic filesystem … Continued
