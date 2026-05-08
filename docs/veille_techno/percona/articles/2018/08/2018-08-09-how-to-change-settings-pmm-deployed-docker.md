---
title: How to Change Settings for PMM Deployed via Docker
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-change-settings-pmm-deployed-docker/
  post_id: 19152
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2018-08-09T09:21:05'
published_at_gmt: '2018-08-09T09:21:05'
modified_at: '2026-05-05T17:55:37'
modified_at_gmt: '2026-05-05T17:55:37'
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
- Cloud
- MySQL
- Percona Software
category_slugs:
- cloud
- mysql
- percona-software
tags:
- Configuration Variables
- troubleshooting configuration issues
tag_slugs:
- configuration-variables
- troubleshooting-configuration-issues
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/change-settings-for-PMM-deployed-docker.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Change Settings for PMM Deployed via Docker

Source: [Percona Blog](https://www.percona.com/blog/how-to-change-settings-pmm-deployed-docker/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2018-08-09T09:21:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When deployed through Docker Percona Monitoring and Management (PMM) uses environment variables for its configuration For example, if you want to adjust metrics resolution you can pass - e METRICS_RESOLUTION = Ns as an option to the docker run command: Shell docker run -d -p 80:80 --volumes-from pmm-data --name pmm-server --restart always -e METRICS_RESOLUTION=2s percona/pmm-server:1 1 2 3 4 5 6 7 docker run - d - p 80 : 80 -- volumes - from pmm - data -- name pmm - server -- restart always - e METRICS_RESOLUTION = 2s percona / pmm - server : 1 You would think if you want to change the setting for existing installation you can just stop the container … Continued

## Images et graphiques reperes

- featured / image: [How to Change Settings for PMM Deployed via Docker](https://www.percona.com/wp-content/uploads/2026/03/change-settings-for-PMM-deployed-docker.jpg)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
