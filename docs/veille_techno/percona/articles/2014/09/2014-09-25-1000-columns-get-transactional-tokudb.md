---
title: More then 1000 columns – get transactional with TokuDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/1000-columns-get-transactional-tokudb/
  post_id: 8563
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2014-09-25T15:03:58'
published_at_gmt: '2014-09-25T15:03:58'
modified_at: '2026-04-28T22:11:26'
modified_at_gmt: '2026-04-28T22:11:26'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- InnoDB
- MyISAM
- Percona Server for MySQL
- Primary
- Przemysław Malkowski
- TokuDB
tag_slugs:
- innodb
- myisam
- percona-server
- primary
- przemyslaw-malkowski
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# More then 1000 columns – get transactional with TokuDB

Source: [Percona Blog](https://www.percona.com/blog/1000-columns-get-transactional-tokudb/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2014-09-25T15:03:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I encountered a specific situation in which a customer was forced to stay with the MyISAM engine due to a legacy application using tables with over 1000 columns. Unfortunately InnoDB has a limit at this point. I did not expect to hear this argument for MyISAM. It is usually about full text search or … Continued

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
