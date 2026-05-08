---
title: Setting up a ProxySQL Sidecar Container
source:
  name: Percona Blog
  url: https://www.percona.com/blog/setting-up-a-proxysql-sidecar-container/
  post_id: 21641
source_author:
  name: Jake Davis
  slug: jake-davis
  url: https://www.percona.com/blog/author/jake-davis/
  website: ''
published_at: '2020-03-23T14:15:53'
published_at_gmt: '2020-03-23T14:15:53'
modified_at: '2026-04-27T21:31:05'
modified_at_gmt: '2026-04-27T21:31:05'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- ProxySQL
matched_filters:
- category:proxysql:2261
- search:proxysql
categories:
- Insight for DBAs
- ProxySQL
category_slugs:
- insight-for-dbas
- proxysql
tags:
- insight for DBAs
- ProxySQL
tag_slugs:
- insight-for-dbas
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Setting-up-a-ProxySQL-Sidecar-Container.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Setting up a ProxySQL Sidecar Container

Source: [Percona Blog](https://www.percona.com/blog/setting-up-a-proxysql-sidecar-container/)

Auteur source: [Jake Davis](https://www.percona.com/blog/author/jake-davis/)

Publication: 2020-03-23T14:15:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, a client of ours, Duolingo, was using Aurora was reaching the max connection limit of 16,000 (that is Aurora’s hard limit for all instance classes). In this case, Percona recommended implementing ProxySQL to manage connections (now their max connections only peak to 6000!). Duolingo’s main application is run in AWS ECS (Elastic Container Service) … Continued

## Structure detectee

- H2: What is a Sidecar?
- H2: Launch A ProxySQL Sidecar
- H3: Prerequisites
- H3: Configuration
- H3: Review
- H2: Summary

## Images et graphiques reperes

- featured / image: [Setting up a ProxySQL Sidecar Container](https://www.percona.com/wp-content/uploads/2026/03/Setting-up-a-ProxySQL-Sidecar-Container.png)
- content / image: [Docker-Sidecar.png](https://www.percona.com/wp-content/uploads/2026/03/Docker-Sidecar.png)
- content / image: [wordpress-1024x661.png](https://www.percona.com/wp-content/uploads/2026/03/wordpress-1024x661.png)

## Auteur source

Jake has been a Percona DBA on the Managed Services team since 2018. He enjoys killing queries and clean failovers. You can find him listening to podcasts on all things Linux and tinkering with open source projects in his home environment.
