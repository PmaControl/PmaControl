---
title: Reclaiming space on your Docker PMM server deployment
source:
  name: Percona Blog
  url: https://www.percona.com/blog/reclaiming-space-on-your-docker-pmm-server-deployment/
  post_id: 19520
source_author:
  name: Agustín
  slug: agustin-gallego
  url: https://www.percona.com/blog/author/agustin-gallego/
  website: ''
published_at: '2018-10-23T12:39:39'
published_at_gmt: '2018-10-23T12:39:39'
modified_at: '2026-05-05T19:25:10'
modified_at_gmt: '2026-05-05T19:25:10'
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
- Insight for Developers
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- percona-software
tags:
- DevOps
- maintenance
tag_slugs:
- devops
- maintenance
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/reclaiming-space-Docker-PMM.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Reclaiming space on your Docker PMM server deployment

Source: [Percona Blog](https://www.percona.com/blog/reclaiming-space-on-your-docker-pmm-server-deployment/)

Auteur source: [Agustín](https://www.percona.com/blog/author/agustin-gallego/)

Publication: 2018-10-23T12:39:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently we had a customer that had issues with a filled disk on the server hosting their Docker pmm-server environment. They were not able to access the web UI, or even stop the pmm-server container because they had filled the /var/ mount point. Setting correct expectations The best way to avoid these kinds of issues … Continued

## Structure detectee

- H2: Setting correct expectations
- H2: Removing unused containers
- H2: Reclaiming space from unused Docker images
- H2: Reclaiming space from orphaned Docker volumes
- H2: Planning ahead

## Images et graphiques reperes

- featured / image: [Reclaiming space on your Docker PMM server deployment](https://www.percona.com/wp-content/uploads/2026/03/reclaiming-space-Docker-PMM.jpg)
- content / image: [reclaiming space Docker PMM](https://www.percona.com/wp-content/uploads/2026/03/reclaiming-space-Docker-PMM-300x200.jpg)

## Auteur source

Agustín joined Percona's Support team in December 2013, after being part of the Administrative team from February 2012. He has previously worked as a Cambridge IT examinations Supervisor and as a Junior BI, SQL & C# developer. He is studying to get a Computer Systems Engineer degree at the Universidad de la República, in Uruguay.
