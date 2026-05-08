---
title: Setting Up a MySQL and Orchestrator Docker Environment
source:
  name: Percona Blog
  url: https://www.percona.com/blog/setting-up-a-mysql-and-orchestrator-docker-environment/
  post_id: 22451
source_author:
  name: Agustín
  slug: agustin-gallego
  url: https://www.percona.com/blog/author/agustin-gallego/
  website: ''
published_at: '2020-06-01T13:57:23'
published_at_gmt: '2020-06-01T13:57:23'
modified_at: '2026-05-05T22:42:19'
modified_at_gmt: '2026-05-05T22:42:19'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
- Open Source
category_slugs:
- insight-for-dbas
- mysql
- open-source
tags:
- Docker
- insight for DBAs
- MySQL
- mysql-and-variants
- orchestrator
tag_slugs:
- docker
- insight-for-dbas
- mysql
- mysql-and-variants
- orchestrator
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-and-Orchestrator.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Setting Up a MySQL and Orchestrator Docker Environment

Source: [Percona Blog](https://www.percona.com/blog/setting-up-a-mysql-and-orchestrator-docker-environment/)

Auteur source: [Agustín](https://www.percona.com/blog/author/agustin-gallego/)

Publication: 2020-06-01T13:57:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In a previous post, Using DBdeployer to manage MySQL, Percona Server, and MariaDB sandboxes, we covered how we use DBdeployer within the Support Team to easily create MySQL environments for testing purposes. Here, I will expand on what Peter wrote in Installing MySQL with Docker, to create environments with more than one node. In particular, … Continued

## Structure detectee

- H2: Docker Compose
- H2: Orchestrator
- H2: Running the Containers
- H3: Running the Orchestrator Container
- H3: Cleaning Up
- H2: Orchestrator and High Availability
- H2: Testing Slave Promotion
- H3: Cleaning Up
- H2: Summary
- H2: Relevant Links

## Images et graphiques reperes

- featured / image: [Setting Up a MySQL and Orchestrator Docker Environment](https://www.percona.com/wp-content/uploads/2026/03/MySQL-and-Orchestrator.png)
- content / image: [MySQL and Orchestrator](https://www.percona.com/wp-content/uploads/2026/03/MySQL-and-Orchestrator-300x168.png)
- content / image: [Orchestrator's web UI showing configured cluster](https://www.percona.com/wp-content/uploads/2026/03/screenshot-1-1024x458.png)
- content / image: [Orchestrator's view on current master-slave setup](https://www.percona.com/wp-content/uploads/2026/03/screenshot-2-1024x474.png)
- content / image: [Orchestrator's view on current master-slave setup](https://www.percona.com/wp-content/uploads/2026/03/screenshot-3-1024x450.png)
- content / image: [slave-promotion.gif](https://www.percona.com/wp-content/uploads/2026/03/slave-promotion.gif)

## Auteur source

Agustín joined Percona's Support team in December 2013, after being part of the Administrative team from February 2012. He has previously worked as a Cambridge IT examinations Supervisor and as a Junior BI, SQL & C# developer. He is studying to get a Computer Systems Engineer degree at the Universidad de la República, in Uruguay.
