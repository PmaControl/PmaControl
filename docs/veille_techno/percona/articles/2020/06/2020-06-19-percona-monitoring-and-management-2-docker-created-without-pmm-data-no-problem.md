---
title: Percona Monitoring and Management 2 Docker Created Without pmm-data? No Problem!
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-monitoring-and-management-2-docker-created-without-pmm-data-no-problem/
  post_id: 22554
source_author:
  name: Carlos Tutte
  slug: carlos-tutte
  url: https://www.percona.com/blog/author/carlos-tutte/
  website: ''
published_at: '2020-06-19T17:18:47'
published_at_gmt: '2020-06-19T17:18:47'
modified_at: '2026-05-05T16:29:31'
modified_at_gmt: '2026-05-05T16:29:31'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
categories:
- Insight for DBAs
- Monitoring
- Percona Software
category_slugs:
- insight-for-dbas
- monitoring
- percona-software
tags:
- insight for DBAs
- Monitoring
- mysql-and-variants
- Percona Monitoring and Management
- Percona Software
tag_slugs:
- insight-for-dbas
- monitoring
- mysql-and-variants
- percona-monitoring-and-management
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PMM-Docker-Container-Pmm-data.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Monitoring and Management 2 Docker Created Without pmm-data? No Problem!

Source: [Percona Blog](https://www.percona.com/blog/percona-monitoring-and-management-2-docker-created-without-pmm-data-no-problem/)

Auteur source: [Carlos Tutte](https://www.percona.com/blog/author/carlos-tutte/)

Publication: 2020-06-19T17:18:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Disclaimer: This blog post is about migrating Percona Monitoring and Management 2 (PMM) data between PMM2 versions, and not for migrating data from PMM1 to PMM2. Restoring data from PMM1 to PMM2 is NOT supported since there were many architectural changes. I recently worked on a customer case where he was not using a pmm-data … Continued

## Structure detectee

- H2: Checking if pmm-data is Holding /srv:
- H2: Backup Existing Data
- H2: Restore Into a New pmm-data Mounted in /srv:
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Percona Monitoring and Management 2 Docker Created Without pmm-data? No Problem!](https://www.percona.com/wp-content/uploads/2026/03/PMM-Docker-Container-Pmm-data.png)
- content / image: [PMM Docker Container Pmm-data](https://www.percona.com/wp-content/uploads/2026/03/PMM-Docker-Container-Pmm-data-300x168.png)

## Auteur source

Computer engineer from Montevideo, Uruguay, joined Percona on February 2018, first as a support engineer, then moving to the consulting team. Working in complex IT solutions for more than 10 years, Carlos now specializes in MySQL and related technologies
