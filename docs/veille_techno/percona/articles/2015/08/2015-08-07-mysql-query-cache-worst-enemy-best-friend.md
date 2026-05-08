---
title: 'The MySQL query cache: Worst enemy or best friend?'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-query-cache-worst-enemy-best-friend/
  post_id: 9420
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2015-08-07T10:00:39'
published_at_gmt: '2015-08-07T10:00:39'
modified_at: '2026-04-28T22:22:47'
modified_at_gmt: '2026-04-28T22:22:47'
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
- MySQL
category_slugs:
- benchmarks
- mysql
tags:
- Magento
- MySQL query cache
- Oracle
- Primary
- Stephane Combaudon
- sysbench
tag_slugs:
- magento
- mysql-query-cache
- oracle
- primary
- stephane-combaudon
- sysbench
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/qcache_on.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The MySQL query cache: Worst enemy or best friend?

Source: [Percona Blog](https://www.percona.com/blog/mysql-query-cache-worst-enemy-best-friend/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2015-08-07T10:00:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

During the last couple of months I have been involved in an unusually high amount of performance audits for e-commerce applications running with Magento. And although the systems were quite different, they also had one thing in common: the MySQL query cache was very useful. That was counter-intuitive for me as I’ve always expected the … Continued

## Structure detectee

- H2: Some context
- H2: A simple test
- H2: Results – MySQL query cache ON
- H2: Results – MySQL query cache OFF
- H2: Conclusion
- H2: Annex: sysbench commands

## Images et graphiques reperes

- featured / image: [The MySQL query cache: Worst enemy or best friend?](https://www.percona.com/wp-content/uploads/2026/03/qcache_on.png)
- content / image: [qcache_off](https://www.percona.com/wp-content/uploads/2026/03/qcache_off.png)

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
