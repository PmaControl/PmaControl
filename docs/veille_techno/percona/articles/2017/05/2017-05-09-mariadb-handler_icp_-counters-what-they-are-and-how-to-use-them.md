---
title: 'MariaDB Handler_icp_% Counters: What They Are, and How To Use Them'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mariadb-handler_icp_-counters-what-they-are-and-how-to-use-them/
  post_id: 16873
source_author:
  name: Agustín
  slug: agustin-gallego
  url: https://www.percona.com/blog/author/agustin-gallego/
  website: ''
published_at: '2017-05-09T19:39:50'
published_at_gmt: '2017-05-09T19:39:50'
modified_at: '2026-05-05T18:40:04'
modified_at_gmt: '2026-05-05T18:40:04'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- counters
- Handler_icp_%
- Handler_icp_attempts
- Handler_icp_matches
- ICP
- MariaDB
tag_slugs:
- counters
- handler_icp_
- handler_icp_attempts
- handler_icp_matches
- icp
- mariadb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Handler_icp_-counters.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MariaDB Handler_icp_% Counters: What They Are, and How To Use Them

Source: [Percona Blog](https://www.percona.com/blog/mariadb-handler_icp_-counters-what-they-are-and-how-to-use-them/)

Auteur source: [Agustín](https://www.percona.com/blog/author/agustin-gallego/)

Publication: 2017-05-09T19:39:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post we’ll see how MariaDB’s Handler_icp_% counters status counters (Handler_icp_attempts and Handler_icp_matches) measure ICP-related work done by the server and storage engine layers, and how to see if our queries are getting any gains by using them. These counters (as seen in SHOW STATUS output) are MariaDB-specific. In a later post, we will … Continued

## Images et graphiques reperes

- featured / image: [MariaDB Handler_icp_% Counters: What They Are, and How To Use Them](https://www.percona.com/wp-content/uploads/2026/03/Handler_icp_-counters.jpg)

## Auteur source

Agustín joined Percona's Support team in December 2013, after being part of the Administrative team from February 2012. He has previously worked as a Cambridge IT examinations Supervisor and as a Junior BI, SQL & C# developer. He is studying to get a Computer Systems Engineer degree at the Universidad de la República, in Uruguay.
