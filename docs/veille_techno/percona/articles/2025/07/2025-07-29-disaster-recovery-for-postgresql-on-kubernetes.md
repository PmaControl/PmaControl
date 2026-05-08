---
title: Disaster Recovery for PostgreSQL on Kubernetes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/disaster-recovery-for-postgresql-on-kubernetes/
  post_id: 27018
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2025-07-29T12:58:23'
published_at_gmt: '2025-07-29T12:58:23'
modified_at: '2026-05-05T17:10:27'
modified_at_gmt: '2026-05-05T17:10:27'
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
- disaster recovery
- Kubernetes
- operators
- PostgreSQL
- Sergeys PP
tag_slugs:
- disaster-recovery
- kubernetes
- operators
- postgresql
- sergeys-planetpostgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/disaster-recover-for-PostgreSQL-on-Kubernetes.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Disaster Recovery for PostgreSQL on Kubernetes

Source: [Percona Blog](https://www.percona.com/blog/disaster-recovery-for-postgresql-on-kubernetes/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2025-07-29T12:58:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post was originally published in 2023, and we’ve updated it in 2025 for clarity and relevance. Downtime is more than an inconvenience. For many organizations, even a short outage can mean lost revenue, broken customer trust, or compliance issues. PostgreSQL is a cornerstone for critical applications, and disaster recovery (DR) is essential when running … Continued

## Structure detectee

- H2: Overview of the solution
- H2: Setting up the main site backups
- H2: Configuring the disaster recovery site
- H2: Promoting the standby cluster during failover
- H2: Avoiding split-brain scenarios
- H2: Automating failover for faster recovery
- H3: Final thoughts: Making PostgreSQL disaster recovery Kubernetes-ready

## Images et graphiques reperes

- featured / image: [Disaster Recovery for PostgreSQL on Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/disaster-recover-for-PostgreSQL-on-Kubernetes.jpg)
- content / image: [Becoming-Kubernetes-Ready-with-Percona-Banner-1.png](https://www.percona.com/wp-content/uploads/2026/03/Becoming-Kubernetes-Ready-with-Percona-Banner-1.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
