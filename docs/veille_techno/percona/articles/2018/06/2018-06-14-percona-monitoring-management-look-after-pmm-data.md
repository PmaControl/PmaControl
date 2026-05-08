---
title: 'Percona Monitoring and Management: Look After Your pmm-data Container'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-monitoring-management-look-after-pmm-data/
  post_id: 18825
source_author:
  name: Dev Montiontactic
  slug: mt_admin
  url: https://www.percona.com/blog/author/mt_admin/
  website: ''
published_at: '2018-06-14T12:58:26'
published_at_gmt: '2018-06-14T12:58:26'
modified_at: '2026-05-05T19:16:52'
modified_at_gmt: '2026-05-05T19:16:52'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
- search:pmm
- tag:pmm:2167
categories:
- Percona Software
category_slugs:
- percona-software
tags:
- container
- data only container
- data volume
- Docker
- docker volume
- Grafana
- PMM
- pmm-admin
- pmm-admin list
- pmm-data
- Prometheus
- time series databases
tag_slugs:
- container
- data-only-container
- data-volume
- docker
- docker-volume
- grafana
- pmm
- pmm-admin
- pmm-admin-list
- pmm-data
- prometheus
- time-series-databases
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/looking-after-containers.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Monitoring and Management: Look After Your pmm-data Container

Source: [Percona Blog](https://www.percona.com/blog/percona-monitoring-management-look-after-pmm-data/)

Auteur source: [Dev Montiontactic](https://www.percona.com/blog/author/mt_admin/)

Publication: 2018-06-14T12:58:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you have already deployed PMM server using Docker you might be aware that we begin by creating a special container for persistent PMM data. In this post, I aim to explain the importance of pmm-data container when you deploy PMM server with Docker. By the end of this post, you will have a fair … Continued

## Structure detectee

- H3: What is the purpose of pmm-data?
- H3: Why do we use docker create ?
- H3: Why does pmm-data not run ?
- H3: Why can’t I remove pmm-data container ? What happens if I delete it ?
- H3: Some do’s and don’ts

## Images et graphiques reperes

- featured / image: [Percona Monitoring and Management: Look After Your pmm-data Container](https://www.percona.com/wp-content/uploads/2026/03/looking-after-containers.jpg)
- content / image: [looking after pmm-datamcontainers](https://www.percona.com/wp-content/uploads/2026/03/looking-after-containers-300x199.jpg)
- content / image: [storage_bindmount-300x204.png](https://www.percona.com/wp-content/uploads/2026/03/storage_bindmount-300x204.png)
- content / image: [Screen-Shot-2018-05-30-at-3.56.01-PM-1-1024x548.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2018-05-30-at-3.56.01-PM-1-1024x548.png)
