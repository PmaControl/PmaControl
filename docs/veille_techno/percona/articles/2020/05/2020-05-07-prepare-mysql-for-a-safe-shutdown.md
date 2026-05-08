---
title: Prepare MySQL for a Safe Shutdown
source:
  name: Percona Blog
  url: https://www.percona.com/blog/prepare-mysql-for-a-safe-shutdown/
  post_id: 22273
source_author:
  name: Jake Davis
  slug: jake-davis
  url: https://www.percona.com/blog/author/jake-davis/
  website: ''
published_at: '2020-05-07T14:19:55'
published_at_gmt: '2020-05-07T14:19:55'
modified_at: '2026-04-27T21:38:16'
modified_at_gmt: '2026-04-27T21:38:16'
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
- insight for DBAs
- MySQL
tag_slugs:
- insight-for-dbas
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/prepare-mysql-safe-shutdown.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Prepare MySQL for a Safe Shutdown

Source: [Percona Blog](https://www.percona.com/blog/prepare-mysql-for-a-safe-shutdown/)

Auteur source: [Jake Davis](https://www.percona.com/blog/author/jake-davis/)

Publication: 2020-05-07T14:19:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In Percona’s Managed Services, we start up and shut down MySQL servers all the time. Over the years, we’ve seen various issues occur due to the way servers are shut down. No matter if it is a situation where unexpected errors appear in the log or you’re stuck waiting for hours for a server to … Continued

## Structure detectee

- H3: 1. Stop Replication.
- H3: 2. Commit, Rollback, or Kill Long-Running Transactions.
- H3: 3. Clean up the Processlist.
- H3: 4. Configure InnoDB for Max Flushing.
- H3: 5. Dump the Buffer Pool.
- H3: 6. Flush the Logs.
- H4: Summary

## Images et graphiques reperes

- featured / image: [Prepare MySQL for a Safe Shutdown](https://www.percona.com/wp-content/uploads/2026/03/prepare-mysql-safe-shutdown.png)
- content / image: [prepare mysql safe shutdown](https://www.percona.com/wp-content/uploads/2026/03/prepare-mysql-safe-shutdown-300x168.png)

## Auteur source

Jake has been a Percona DBA on the Managed Services team since 2018. He enjoys killing queries and clean failovers. You can find him listening to podcasts on all things Linux and tinkering with open source projects in his home environment.
