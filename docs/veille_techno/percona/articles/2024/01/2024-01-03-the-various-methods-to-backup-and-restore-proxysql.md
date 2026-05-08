---
title: The Various Methods to Backup and Restore ProxySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-various-methods-to-backup-and-restore-proxysql/
  post_id: 27899
source_author:
  name: Balchandar Reddy Voodem
  slug: balchandar-voodem
  url: https://www.percona.com/blog/author/balchandar-voodem/
  website: ''
published_at: '2024-01-03T14:02:27'
published_at_gmt: '2024-01-03T14:02:27'
modified_at: '2026-03-26T20:26:48'
modified_at_gmt: '2026-03-26T20:26:48'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
- XtraBackup
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:percona-xtrabackup
- search:proxysql
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
- ProxySQL
category_slugs:
- insight-for-dbas
- mysql
- proxysql
tags:
- MySQL
- mysql-and-variants
- ProxySQL
tag_slugs:
- mysql
- mysql-and-variants
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/3d-rendering-digital-city-seamless-loop-futuristic-hi-tech-background-1.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The Various Methods to Backup and Restore ProxySQL

Source: [Percona Blog](https://www.percona.com/blog/the-various-methods-to-backup-and-restore-proxysql/)

Auteur source: [Balchandar Reddy Voodem](https://www.percona.com/blog/author/balchandar-voodem/)

Publication: 2024-01-03T14:02:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

ProxySQL is a high-performance SQL proxy that runs as a daemon watched by a monitoring process. The process monitors the daemon and restarts it in case of a crash to minimize downtime. The daemon accepts incoming traffic from MySQL clients and forwards it to backend MySQL servers. The proxy is designed to run continuously without … Continued

## Structure detectee

- H3: Config file backup:
- H3: Mysqldump:
- H3: Physical snapshot:
- H4: Conclusion
- H4: Related links:

## Images et graphiques reperes

- featured / image: [The Various Methods to Backup and Restore ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/3d-rendering-digital-city-seamless-loop-futuristic-hi-tech-background-1.jpg)
- content / image: [proxysql-1.jpeg](https://www.percona.com/wp-content/uploads/2026/03/proxysql-1.jpeg)
