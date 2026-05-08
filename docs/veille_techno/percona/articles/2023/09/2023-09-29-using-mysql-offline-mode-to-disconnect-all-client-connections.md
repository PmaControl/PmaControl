---
title: Using MySQL Offline Mode To Disconnect All Client Connections
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-mysql-offline-mode-to-disconnect-all-client-connections/
  post_id: 27522
source_author:
  name: Bhuvanes Waran
  slug: bhuvanes-waran
  url: https://www.percona.com/blog/author/bhuvanes-waran/
  website: ''
published_at: '2023-09-29T12:15:23'
published_at_gmt: '2023-09-29T12:15:23'
modified_at: '2026-03-26T20:27:10'
modified_at_gmt: '2026-03-26T20:27:10'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
- search:pmm
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Offline-Mode.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using MySQL Offline Mode To Disconnect All Client Connections

Source: [Percona Blog](https://www.percona.com/blog/using-mysql-offline-mode-to-disconnect-all-client-connections/)

Auteur source: [Bhuvanes Waran](https://www.percona.com/blog/author/bhuvanes-waran/)

Publication: 2023-09-29T12:15:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As a DBA, one of the very frequent tasks is to stop/start MySQL service for batching or some other activities. Before stopping MySQL, we may need to check if there are any active connections; if there are, we may need to kill all those. Generally, we use pt-kill to kill the application connections or prepare … Continued

## Structure detectee

- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Using MySQL Offline Mode To Disconnect All Client Connections](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Offline-Mode.png)

## Auteur source

Bhuvan works as a MySQL DBA in Percona since 2021.
