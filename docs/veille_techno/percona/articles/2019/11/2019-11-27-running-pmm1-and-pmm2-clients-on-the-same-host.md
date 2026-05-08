---
title: Running PMM1 and PMM2 Clients on the Same Host
source:
  name: Percona Blog
  url: https://www.percona.com/blog/running-pmm1-and-pmm2-clients-on-the-same-host/
  post_id: 20692
source_author:
  name: Vadim Yalovets
  slug: vadim-yalovets
  url: https://www.percona.com/blog/author/vadim-yalovets/
  website: ''
published_at: '2019-11-27T14:09:01'
published_at_gmt: '2019-11-27T14:09:01'
modified_at: '2026-04-29T14:45:19'
modified_at_gmt: '2026-04-29T14:45:19'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
- ProxySQL
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
- search:proxysql
- tag:percona-monitoring-and-management:2166
- tag:pmm:2167
categories:
- Monitoring
- Percona Services
- Percona Software
category_slugs:
- monitoring
- percona-services
- percona-software
tags:
- Percona Monitoring and Management
- Percona Software
- PMM
tag_slugs:
- percona-monitoring-and-management
- percona-software
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Running-PMM1-and-PMM2-Clients.png
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Running PMM1 and PMM2 Clients on the Same Host

Source: [Percona Blog](https://www.percona.com/blog/running-pmm1-and-pmm2-clients-on-the-same-host/)

Auteur source: [Vadim Yalovets](https://www.percona.com/blog/author/vadim-yalovets/)

Publication: 2019-11-27T14:09:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Want to try out Percona Monitoring and Management 2 (PMM 2) but you’re not ready to turn off your PMM 1 environment? This blog is for you! Keep in mind that the methods described are not intended to be a long-term migration strategy, but rather, simply a way to deploy a few clients in order … Continued

## Structure detectee

- H2: Install and Setup pmm2-client Connectivity to Server2
- H4: Download pmm2-client Tarball
- H4: Extract Files From pmm2-client Tarball
- H4: Register and Generate Configuration File
- H4: Start pmm-agent
- H4: Check the Current State of the Agent
- H4: Add MySQL Service
- H2: Remove pmm-client and Switch Completely to pmm2-client
- H4: Configure Percona Repositories
- H4: Remove pmm-client
- H4: Install pmm2-client
- H4: Configure pmm2-client
- H4: Check Monitored Services

## Images et graphiques reperes

- featured / image: [Running PMM1 and PMM2 Clients on the Same Host](https://www.percona.com/wp-content/uploads/2026/03/Running-PMM1-and-PMM2-Clients.png)
- content / image: [Running PMM1 and PMM2 Clients](https://www.percona.com/wp-content/uploads/2026/03/Running-PMM1-and-PMM2-Clients-300x168.png)
- content / image: [Screenshot_20191118_163604-1024x446.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20191118_163604-1024x446.png)
- content / image: [Screenshot_20190717_181204-300x148.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20190717_181204-300x148.png)
- content / image: [Screenshot_20190717_181222-300x146.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20190717_181222-300x146.png)
- content / image: [Screenshot_20190717_181943-300x146.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20190717_181943-300x146.png)
- content / image: [Screenshot_20190717_182120-300x146.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20190717_182120-300x146.png)
- content / image: [Screenshot_20191118_215058-1024x279.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot_20191118_215058-1024x279.png)

## Auteur source

Software Engineer
