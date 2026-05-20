---
title: Customizing the New MongoDB Concurrency Algorithm
source:
  name: Percona Blog
  url: https://www.percona.com/blog/customizing-the-new-mongodb-concurrency-algorithm/
  post_id: 35348
source_author:
  name: Pablo Claudino
  slug: pablo-claudino
  url: https://www.percona.com/blog/author/pablo-claudino/
  website: ''
published_at: '2025-10-22T13:07:35'
published_at_gmt: '2025-10-22T13:07:35'
modified_at: '2026-03-26T20:13:18'
modified_at_gmt: '2026-03-26T20:13:18'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- MongoDB
- Percona Software
category_slugs:
- insight-for-dbas
- mongodb
- percona-software
tags:
- MongoDB
- percona server for MongoDB
tag_slugs:
- mongodb
- percona-server-for-mongodb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Customizing-the-New-MongoDB-Concurrency-Algorithm.jpg
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Customizing the New MongoDB Concurrency Algorithm

Source: [Percona Blog](https://www.percona.com/blog/customizing-the-new-mongodb-concurrency-algorithm/)

Auteur source: [Pablo Claudino](https://www.percona.com/blog/author/pablo-claudino/)

Publication: 2025-10-22T13:07:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

On some occasions, we realize the necessity of throttling the number of requests that MongoDB tries to execute per second, be it due to resource saturation remediation, machine change planning, or performance tests. The most direct way of doing this is by tuning the WiredTiger transaction ticket parameters. Applying this throttle provides more controlled and … Continued

## Structure detectee

- H2: Environment
- H2: Process
- H2: Metric outputs
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Customizing the New MongoDB Concurrency Algorithm](https://www.percona.com/wp-content/uploads/2026/03/Customizing-the-New-MongoDB-Concurrency-Algorithm.jpg)
- content / image: [Mongo scenarios](https://www.percona.com/wp-content/uploads/2026/03/tickets.20250910-1024x470.png)
- content / image: [Customizing the New MongoDB Concurrency Algorithm](https://www.percona.com/wp-content/uploads/2026/03/resources.20250910-1024x496.png)
- content / image: [tickets.20250911-1024x469.png](https://www.percona.com/wp-content/uploads/2026/03/tickets.20250911-1024x469.png)
- content / image: [resources.20250911-1024x496.png](https://www.percona.com/wp-content/uploads/2026/03/resources.20250911-1024x496.png)
- content / image: [Switch-to-percona-for-mongodb-5.png](https://www.percona.com/wp-content/uploads/2026/03/Switch-to-percona-for-mongodb-5.png)
