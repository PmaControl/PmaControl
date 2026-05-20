---
title: Lost innodb tables, xfs and binary grep
source:
  name: Percona Blog
  url: https://www.percona.com/blog/lost-innodb-tables-xfs-and-binary-grep/
  post_id: 2500
source_author:
  name: Aurimas Mikalauskas
  slug: inner
  url: https://www.percona.com/blog/author/inner/
  website: http://www.speedemy.com/
published_at: '2010-11-09T10:26:26'
published_at_gmt: '2010-11-09T10:26:26'
modified_at: '2026-03-23T21:47:04'
modified_at_gmt: '2026-03-23T21:47:04'
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
tags:
- Backups
- bgrep
- grep
- InnoDB
- Recovery
- Tips
- Tools
- xfs
tag_slugs:
- backups
- bgrep
- grep
- innodb
- recovery
- tips
- tools
- xfs
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Lost innodb tables, xfs and binary grep

Source: [Percona Blog](https://www.percona.com/blog/lost-innodb-tables-xfs-and-binary-grep/)

Auteur source: [Aurimas Mikalauskas](https://www.percona.com/blog/author/inner/)

Publication: 2010-11-09T10:26:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Before I start a story about the data recovery case I worked on yesterday, here’s a quick tip – having a database backup does not mean you can restore from it. Always verify your backup can be used to restore the database! If not automatically, do this manually, at least once a month. No, seriously … Continued

## Auteur source

Aurimas joined Percona as a first MySQL Performance Consultant in 2006, a few months after Peter and Vadim founded the company. His primary focus is on high performance, but he also specializes in full text search, high availability, content caching techniques and MySQL data recovery.
