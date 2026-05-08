---
title: Multiple column index vs multiple indexes with MySQL 5.6
source:
  name: Percona Blog
  url: https://www.percona.com/blog/multiple-column-index-vs-multiple-indexes-with-mysql-56/
  post_id: 7608
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-01-03T15:10:30'
published_at_gmt: '2014-01-03T15:10:30'
modified_at: '2026-05-04T22:13:35'
modified_at_gmt: '2026-05-04T22:13:35'
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
- Index Condition Pushdown
- MySQL 5.5 vs. MySQL 5.6
- MySQL 5.6
- Optimizer
tag_slugs:
- index-condition-pushdown
- mysql-5-5-vs-mysql-5-6
- mysql-5-6
- optimizer
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Multiple column index vs multiple indexes with MySQL 5.6

Source: [Percona Blog](https://www.percona.com/blog/multiple-column-index-vs-multiple-indexes-with-mysql-56/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-01-03T15:10:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A question often comes when talking about indexing: should we use multiple column indexes or multiple indexes on single columns? Peter Zaitsev wrote about it back in 2008 and the conclusion then was that a multiple column index is most often the best solution. But with all the recent optimizer improvements, is there anything different with … Continued

## Structure detectee

- H2: Setup
- H2: ICP: FORCE INDEX to the rescue
- H2: Additional thoughts
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
