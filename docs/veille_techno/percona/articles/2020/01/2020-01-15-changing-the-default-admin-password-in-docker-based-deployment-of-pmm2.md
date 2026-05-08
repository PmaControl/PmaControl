---
title: Changing the Default Admin Password in Docker-Based Deployment of PMM2
source:
  name: Percona Blog
  url: https://www.percona.com/blog/changing-the-default-admin-password-in-docker-based-deployment-of-pmm2/
  post_id: 21475
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-01-15T17:19:34'
published_at_gmt: '2020-01-15T17:19:34'
modified_at: '2026-05-05T17:34:18'
modified_at_gmt: '2026-05-05T17:34:18'
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
- tag:pmm:2167
categories:
- Monitoring
- Percona Software
category_slugs:
- monitoring
- percona-software
tags:
- Percona Monitoring and Management
- Percona Software
- PMM
tag_slugs:
- percona-monitoring-and-management
- percona-software
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/default-password-pmm2.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Changing the Default Admin Password in Docker-Based Deployment of PMM2

Source: [Percona Blog](https://www.percona.com/blog/changing-the-default-admin-password-in-docker-based-deployment-of-pmm2/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-01-15T17:19:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

April 2021 Update: From Percona Monitoring and Management 2.27, the procedure is now simplified and it is possible by running: docker exec pmm-server change-admin-password <new_password> 1 docker exec pmm - server change - admin - password < new_password > If you’re automating Percona Monitoring and Management 2 (PMM) deployment with docker, you may want to set a different admin user password upon installation instead of being required to change it upon first login. … Continued

## Images et graphiques reperes

- featured / image: [Changing the Default Admin Password in Docker-Based Deployment of PMM2](https://www.percona.com/wp-content/uploads/2026/03/default-password-pmm2.png)
- content / image: [default password pmm2](https://www.percona.com/wp-content/uploads/2026/03/default-password-pmm2-300x168.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
