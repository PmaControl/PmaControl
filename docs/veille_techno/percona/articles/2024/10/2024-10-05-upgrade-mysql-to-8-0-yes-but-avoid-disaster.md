---
title: Upgrade MySQL to 8.0? Yes, but Avoid Disaster!
source:
  name: Percona Blog
  url: https://www.percona.com/blog/upgrade-mysql-to-8-0-yes-but-avoid-disaster/
  post_id: 27202
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2024-10-05T11:56:38'
published_at_gmt: '2024-10-05T11:56:38'
modified_at: '2026-04-16T16:49:23'
modified_at_gmt: '2026-04-16T16:49:23'
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
- MySQL
- MySQL 8.0
- mysql-and-variants
- Upgrade
tag_slugs:
- mysql
- mysql-8-0
- mysql-and-variants
- upgrade
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/upgrade-to-mysql-8-1.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Upgrade MySQL to 8.0? Yes, but Avoid Disaster!

Source: [Percona Blog](https://www.percona.com/blog/upgrade-mysql-to-8-0-yes-but-avoid-disaster/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2024-10-05T11:56:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Upgrading to MySQL version 8.0 is a hot topic since version 5.7 is approaching official end of life very soon. MySQL 5.7 EOL is set for the end of October 2023. If you feel unprepared for the upgrade, consider post-EOL support from Percona. But it would be the worst if you proceeded with the upgrade … Continued

## Structure detectee

- H2: Possible downgrade options
- H2: A logical dump/restore downgrade
- H2: Using a 5.7 replica as a backup downgrade path
- H2: Downgrade by restoring the 5.7 backup and applying new binlogs (PITR)
- H2: Upgrade advice

## Images et graphiques reperes

- featured / image: [Upgrade MySQL to 8.0? Yes, but Avoid Disaster!](https://www.percona.com/wp-content/uploads/2026/03/upgrade-to-mysql-8-1.png)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
