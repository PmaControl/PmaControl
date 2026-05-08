---
title: Migrating PostgreSQL to Kubernetes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/migrating-postgresql-to-kubernetes/
  post_id: 24690
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2025-07-01T12:03:53'
published_at_gmt: '2025-07-01T12:03:53'
modified_at: '2026-05-05T17:15:10'
modified_at_gmt: '2026-05-05T17:15:10'
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
- cloud
- Kubernetes
- Kubernetes Operator
- Percona Software
- Planet PostgreSQL
- PostgreSQL
- Sergeys PP
tag_slugs:
- cloud
- kubernetes
- kubernetes-operator
- percona-software
- planet-postgresql
- postgresql
- sergeys-planetpostgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Migrating-PostgreSQL-to-Kubernetes.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Migrating PostgreSQL to Kubernetes

Source: [Percona Blog](https://www.percona.com/blog/migrating-postgresql-to-kubernetes/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2025-07-01T12:03:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post was originally published in 2021 and was updated in 2025. Kubernetes adoption keeps climbing, and databases are often one of the last workloads teams try to move. The reasons are clear: PostgreSQL is critical, downtime isn’t an option, and migration can feel risky. But with the right approach, you can modernize without bringing … Continued

## Structure detectee

- H2: Goal
- H2: Migration
- H3: Prerequisites
- H3: Configure the source
- H3: Configure the target
- H3: Verify and troubleshoot
- H3: Common issues
- H3: Cutover
- H2: Wrapping up

## Images et graphiques reperes

- featured / image: [Migrating PostgreSQL to Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/Migrating-PostgreSQL-to-Kubernetes.png)
- content / image: [Migrating PostgreSQL to Kubernetes](https://www.percona.com/wp-content/uploads/2026/03/pb-blog-1024x359.png)

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
