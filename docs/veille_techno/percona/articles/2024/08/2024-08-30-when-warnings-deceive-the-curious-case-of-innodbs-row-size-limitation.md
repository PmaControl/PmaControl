---
title: 'When Warnings Deceive: The Curious Case of InnoDB’s Row Size Limitation'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/when-warnings-deceive-the-curious-case-of-innodbs-row-size-limitation/
  post_id: 28915
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2024-08-30T14:10:58'
published_at_gmt: '2024-08-30T14:10:58'
modified_at: '2026-03-26T20:26:00'
modified_at_gmt: '2026-03-26T20:26:00'
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
- InnoDB
- MySQL
- mysql-and-variants
tag_slugs:
- innodb
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Understanding-Charset-Levels-in-MySQL.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# When Warnings Deceive: The Curious Case of InnoDB’s Row Size Limitation

Source: [Percona Blog](https://www.percona.com/blog/when-warnings-deceive-the-curious-case-of-innodbs-row-size-limitation/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2024-08-30T14:10:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Mysterious warning Recently, I was involved in an investigation whose goal was to find out the reason for a warning message like this: [Warning] [MY-011825] [InnoDB] Cannot add field `c11` in table `db1`.`test` because after adding it, the row size is 8484 which is greater than maximum allowed size (8126) for a record on index leaf page. 1 [ Warning ] [ MY - 011825 ] [ InnoDB ] Cannot add field ` c11 ` in table ` db1 ` . ` test ` because after adding it , the row size is 8484 which is greater than maximum allowed size ( 8126 ) for a record on index leaf page . The message looks clear, isn’t it? Well, the problem was that this particular table had not been changed for years, and so no DDL (ALTER) query was involved here. Moreover, there were not … Continued

## Structure detectee

- H2: Mysterious warning
- H2: Reproduction attempts
- H2: Table definition cache
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [When Warnings Deceive: The Curious Case of InnoDB’s Row Size Limitation](https://www.percona.com/wp-content/uploads/2026/03/Understanding-Charset-Levels-in-MySQL.jpg)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
