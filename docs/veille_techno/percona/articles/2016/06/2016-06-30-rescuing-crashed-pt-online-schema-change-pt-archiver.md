---
title: Rescuing a crashed pt-online-schema-change with pt-archiver
source:
  name: Percona Blog
  url: https://www.percona.com/blog/rescuing-crashed-pt-online-schema-change-pt-archiver/
  post_id: 15395
source_author:
  name: Manjot Singh
  slug: manjot-singh
  url: https://www.percona.com/blog/author/manjot-singh/
  website: ''
published_at: '2016-06-30T21:20:03'
published_at_gmt: '2016-06-30T21:20:03'
modified_at: '2026-05-05T18:10:58'
modified_at_gmt: '2026-05-05T18:10:58'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- pt-archiver
- pt-online-schema-change
tag_slugs:
- mysql
- pt-archiver
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/crashed-pt-online-schema-change.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Rescuing a crashed pt-online-schema-change with pt-archiver

Source: [Percona Blog](https://www.percona.com/blog/rescuing-crashed-pt-online-schema-change-pt-archiver/)

Auteur source: [Manjot Singh](https://www.percona.com/blog/author/manjot-singh/)

Publication: 2016-06-30T21:20:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This article discusses how to salvage a crashed pt-online-schema-change by leveraging pt-archiver and executing queries to ensure that the data gets accurately migrated. I will show you how to continue the data copy process, and how to safely close out the pt-online-schema-change via manual operations such as RENAME TABLE and DROP TRIGGER commands. The normal … Continued

## Images et graphiques reperes

- featured / image: [Rescuing a crashed pt-online-schema-change with pt-archiver](https://www.percona.com/wp-content/uploads/2026/03/crashed-pt-online-schema-change.jpg)
- content / image: [crashed pt-online-schema-change](https://www.percona.com/wp-content/uploads/2026/03/crashed-pt-online-schema-change-300x284.jpg)

## Auteur source

Manjot Singh is an Architect with Percona in California. He loves to learn about new technologies and apply them to real world problems. Manjot is a veteran of startup and Fortune 50 enterprise companies alike with a few years spent in government, education, and hospital IT.
