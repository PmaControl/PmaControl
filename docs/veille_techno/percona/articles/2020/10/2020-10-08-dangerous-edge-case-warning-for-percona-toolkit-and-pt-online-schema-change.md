---
title: Dangerous Edge Case Warning for Percona Toolkit and pt-online-schema-change
source:
  name: Percona Blog
  url: https://www.percona.com/blog/dangerous-edge-case-warning-for-percona-toolkit-and-pt-online-schema-change/
  post_id: 23178
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2020-10-08T13:40:07'
published_at_gmt: '2020-10-08T13:40:07'
modified_at: '2026-05-05T17:52:13'
modified_at_gmt: '2026-05-05T17:52:13'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
- tag:percona-toolkit:378
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- insight for DBAs
- MySQL
- mysql-and-variants
- Percona Software
- Percona Toolkit
- pt-online-schema-change
tag_slugs:
- insight-for-dbas
- mysql
- mysql-and-variants
- percona-software
- percona-toolkit
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Toolkit-and-pt-online-schema-change.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Dangerous Edge Case Warning for Percona Toolkit and pt-online-schema-change

Source: [Percona Blog](https://www.percona.com/blog/dangerous-edge-case-warning-for-percona-toolkit-and-pt-online-schema-change/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2020-10-08T13:40:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I was dealing with an unexpected issue raised by our Support customer, in which data became inconsistent after a schema change was applied. After some investigation, it turned out that affected tables had a special word in the comments of some columns, which triggered an already known (and fixed) issue with the TableParser.pm library … Continued

## Structure detectee

- H3: References:

## Images et graphiques reperes

- featured / image: [Dangerous Edge Case Warning for Percona Toolkit and pt-online-schema-change](https://www.percona.com/wp-content/uploads/2026/03/Percona-Toolkit-and-pt-online-schema-change.png)
- content / image: [Percona Toolkit and pt-online-schema-change](https://www.percona.com/wp-content/uploads/2026/03/Percona-Toolkit-and-pt-online-schema-change-300x157.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
