---
title: Troubleshooting PostgreSQL on Kubernetes With Coroot
source:
  name: Percona Blog
  url: https://www.percona.com/blog/troubleshooting-postgresql-on-kubernetes-with-coroot/
  post_id: 28415
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2024-05-08T18:11:31'
published_at_gmt: '2024-05-08T18:11:31'
modified_at: '2026-03-26T20:07:25'
modified_at_gmt: '2026-03-26T20:07:25'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
categories:
- Cloud
- Percona Software
- PostgreSQL
category_slugs:
- cloud
- percona-software
- postgresql
tags:
- Coroot
- ebpf
- Kubernetes
- observability
- operators
- PostgreSQL
- Sergeys PP
tag_slugs:
- coroot
- ebpf
- kubernetes
- observability
- operators
- postgresql
- sergeys-planetpostgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-on-Kubernetes-With-Coroot.jpg
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Troubleshooting PostgreSQL on Kubernetes With Coroot

Source: [Percona Blog](https://www.percona.com/blog/troubleshooting-postgresql-on-kubernetes-with-coroot/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2024-05-08T18:11:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Coroot, an open source observability tool powered by eBPF, went generally available with version 1.0 last week. As this tool is cloud-native, we were curious to know how it can help troubleshoot databases on Kubernetes. In this blog post, we will see how to quickly debug PostgreSQL with Coroot and Percona Operator for PostgreSQL. Prepare … Continued

## Structure detectee

- H2: Prepare
- H3: Install Coroot
- H3: Deploy PostgreSQL cluster with Operator
- H3: More insights with PostgreSQL agent
- H2: Coroot in action
- H3: Discovery
- H3: SLO
- H3: Logs
- H3: Profiling
- H3: PostgreSQL-specific metrics

## Images et graphiques reperes

- featured / image: [Troubleshooting PostgreSQL on Kubernetes With Coroot](https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-on-Kubernetes-With-Coroot.jpg)
- content / image: [Coroot in action](https://www.percona.com/wp-content/uploads/2026/03/blog_coroot_0-1024x448.png)
- content / image: [Service Level Objectives](https://www.percona.com/wp-content/uploads/2026/03/blog_coroot_1-1024x257.png)
- content / image: [blog_coroot_2-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/blog_coroot_2-scaled.png)
- content / image: [blog_coroot_6-1024x447.png](https://www.percona.com/wp-content/uploads/2026/03/blog_coroot_6-1024x447.png)
- content / image: [PostgreSQL-specific metrics](https://www.percona.com/wp-content/uploads/2026/03/blog_coroot_7-1024x260.png)
- content / image: [blog_coroot_3-1024x389.png](https://www.percona.com/wp-content/uploads/2026/03/blog_coroot_3-1024x389.png)
- content / image: [blog_coroot_4-1024x366.png](https://www.percona.com/wp-content/uploads/2026/03/blog_coroot_4-1024x366.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
