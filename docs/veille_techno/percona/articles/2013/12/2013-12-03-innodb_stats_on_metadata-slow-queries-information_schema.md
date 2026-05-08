---
title: 'INFORMATION_SCHEMA: innodb_stats_on_metadata and slow queries'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb_stats_on_metadata-slow-queries-information_schema/
  post_id: 7563
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2013-12-03T08:00:07'
published_at_gmt: '2013-12-03T08:00:07'
modified_at: '2026-05-05T16:52:27'
modified_at_gmt: '2026-05-05T16:52:27'
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
- information_schema
- InnoDB
- innodb_stats_on_metadata
- Stephane Combaudon
- Tips
tag_slugs:
- information_schema
- innodb
- innodb_stats_on_metadata
- stephane-combaudon
- tips
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# INFORMATION_SCHEMA: innodb_stats_on_metadata and slow queries

Source: [Percona Blog](https://www.percona.com/blog/innodb_stats_on_metadata-slow-queries-information_schema/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2013-12-03T08:00:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

INFORMATION_SCHEMA is usually the place to go when you want to get facts about a system (how many tables do we have? what are the 10 largest tables? What is data size and index size for table t?, etc). However it is also quite common that such queries are very slow and create lots of … Continued

## Structure detectee

- H2: The problem
- H2: What is innodb_stats_on_metadata?
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
