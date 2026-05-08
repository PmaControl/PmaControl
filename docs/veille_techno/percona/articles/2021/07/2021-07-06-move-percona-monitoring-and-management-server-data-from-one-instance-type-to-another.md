---
title: Move Percona Monitoring and Management Server Data From One Instance Type to Another
source:
  name: Percona Blog
  url: https://www.percona.com/blog/move-percona-monitoring-and-management-server-data-from-one-instance-type-to-another/
  post_id: 24552
source_author:
  name: Vadim Yalovets
  slug: vadim-yalovets
  url: https://www.percona.com/blog/author/vadim-yalovets/
  website: ''
published_at: '2021-07-06T13:02:42'
published_at_gmt: '2021-07-06T13:02:42'
modified_at: '2026-04-27T22:30:01'
modified_at_gmt: '2026-04-27T22:30:01'
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
- Insight for Developers
- Monitoring
- Percona Software
category_slugs:
- insight-for-developers
- monitoring
- percona-software
tags:
- Monitoring
- Percona Monitoring and Management
- Percona Software
tag_slugs:
- monitoring
- percona-monitoring-and-management
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Move-Percona-Monitoring-and-Management-Server-Data.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Move Percona Monitoring and Management Server Data From One Instance Type to Another

Source: [Percona Blog](https://www.percona.com/blog/move-percona-monitoring-and-management-server-data-from-one-instance-type-to-another/)

Auteur source: [Vadim Yalovets](https://www.percona.com/blog/author/vadim-yalovets/)

Publication: 2021-07-06T13:02:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Monitoring and Management (PMM2) Server runs as a Docker container, a Virtual appliance, or as an instance on Amazon or Azure cloud services. Here I’ll show how to move the PMM Server and its data from one type to another. Note, this is only for PMM2 to PMM2—you can’t migrate data from PMM Server … Continued

## Structure detectee

- H2: Export Data
- H2: Prepare New Server
- H2: Switch Services to New Server
- H2: Check Status
- H2: Export/Import VictoriaMetrics Data
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Move Percona Monitoring and Management Server Data From One Instance Type to Another](https://www.percona.com/wp-content/uploads/2026/03/Move-Percona-Monitoring-and-Management-Server-Data.png)
- content / image: [Move Percona Monitoring and Management Server Data](https://www.percona.com/wp-content/uploads/2026/03/Move-Percona-Monitoring-and-Management-Server-Data-300x157.png)
- content / image: [pmm-admin status](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20210625_174900-1024x399.png)
- content / image: [Screenshot_20210625_190241.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20210625_190241.png)
- content / image: [Check Status PMM](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20210625_190545-1024x370.png)
- content / image: [Grafana UI](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20210625_190749-1024x381.png)

## Auteur source

Software Engineer
