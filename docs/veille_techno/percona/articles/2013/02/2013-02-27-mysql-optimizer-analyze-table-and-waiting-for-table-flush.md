---
title: 'MySQL optimizer: ANALYZE TABLE and Waiting for table flush'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-optimizer-analyze-table-and-waiting-for-table-flush/
  post_id: 6587
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2013-02-27T11:42:26'
published_at_gmt: '2013-02-27T11:42:26'
modified_at: '2026-05-04T21:58:24'
modified_at_gmt: '2026-05-04T21:58:24'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- ANALYZE TABLE
- InnoDB
- Miguel Angel Nieto
- MySQL Optimizer
- table flush
tag_slugs:
- analyze-table
- innodb
- miguel-angel-nieto
- mysql-optimizer
- table-flush
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-optimizer.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL optimizer: ANALYZE TABLE and Waiting for table flush

Source: [Percona Blog](https://www.percona.com/blog/mysql-optimizer-analyze-table-and-waiting-for-table-flush/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2013-02-27T11:42:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The MySQL optimizer makes the decision of what execution plan to use based on the information provided by the storage engines. That information is not accurate in some engines like InnoDB and they are based in statistics calculations therefore sometimes some tune is needed. In InnoDB these statistics are calculated automatically, check the following blog … Continued

## Structure detectee

- H3: Waiting for table flush:
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [MySQL optimizer: ANALYZE TABLE and Waiting for table flush](https://www.percona.com/wp-content/uploads/2026/03/MySQL-optimizer.png)

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
