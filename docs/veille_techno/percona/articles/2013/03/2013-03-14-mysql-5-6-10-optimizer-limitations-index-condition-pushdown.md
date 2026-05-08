---
title: 'MySQL 5.6.10 Optimizer Limitations: Index Condition Pushdown'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-5-6-10-optimizer-limitations-index-condition-pushdown/
  post_id: 6697
source_author:
  name: Jaime Crespo
  slug: jaime-crespo
  url: https://www.percona.com/blog/author/jaime-crespo/
  website: http://www.percona.com/about-us/our-team/jaime-crespo/
published_at: '2013-03-14T10:00:53'
published_at_gmt: '2013-03-14T10:00:53'
modified_at: '2026-05-04T22:02:25'
modified_at_gmt: '2026-05-04T22:02:25'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
- Percona Events
category_slugs:
- insight-for-developers
- mysql
- percona-events
tags:
- Index Condition Pushdown
- Jaime Crespo
- MySQL 5.6.10
tag_slugs:
- index-condition-pushdown
- jaime-crespo
- mysql-5-6-10
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/percona-webinars.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 5.6.10 Optimizer Limitations: Index Condition Pushdown

Source: [Percona Blog](https://www.percona.com/blog/mysql-5-6-10-optimizer-limitations-index-condition-pushdown/)

Auteur source: [Jaime Crespo](https://www.percona.com/blog/author/jaime-crespo/)

Publication: 2013-03-14T10:00:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

While preparing the webinar I will deliver this Friday, I ran into a quite interesting (although not very impacting) optimizer issue: a “SELECT *” taking half the time to execute than the same “SELECT one_indexed_column” query in MySQL 5.6.10. This turned into a really nice exercise for checking the performance and inner workings of one … Continued

## Images et graphiques reperes

- featured / image: [MySQL 5.6.10 Optimizer Limitations: Index Condition Pushdown](https://www.percona.com/wp-content/uploads/2026/03/percona-webinars.png)

## Auteur source

Jaime is a former Percona employee. He was a Senior MySQL Instructor for Percona, providing training throughout Europe. He is a certified MySQL DBA, DEV and cluster DBA and his favourite areas are query and schema optimisation and solutions for HA.
