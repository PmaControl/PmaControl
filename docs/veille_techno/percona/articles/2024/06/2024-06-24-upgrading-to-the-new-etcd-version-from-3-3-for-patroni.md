---
title: Upgrading to the New Etcd Version From 3.3 for Patroni
source:
  name: Percona Blog
  url: https://www.percona.com/blog/upgrading-to-the-new-etcd-version-from-3-3-for-patroni/
  post_id: 28709
source_author:
  name: Jobin Augustine
  slug: jobin-augustine
  url: https://www.percona.com/blog/author/jobin-augustine/
  website: ''
published_at: '2024-06-24T13:20:48'
published_at_gmt: '2024-06-24T13:20:48'
modified_at: '2026-03-26T20:07:23'
modified_at_gmt: '2026-03-26T20:07:23'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- PostgreSQL
category_slugs:
- insight-for-dbas
- postgresql
tags:
- Jobin PP
- PostgreSQL
tag_slugs:
- jobin-planetpostgresql
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Upgrading-to-the-New-Etcd-Version-From-3.3-for-Patroni.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Upgrading to the New Etcd Version From 3.3 for Patroni

Source: [Percona Blog](https://www.percona.com/blog/upgrading-to-the-new-etcd-version-from-3-3-for-patroni/)

Auteur source: [Jobin Augustine](https://www.percona.com/blog/author/jobin-augustine/)

Publication: 2024-06-24T13:20:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We have been promoting and using Patroni as the best high availability framework for PostgreSQL, and Etcd was the preferred/recommended DCS for the Patroni cluster. Both Patroni and Etcd have been part of PostgreSQL distribution from Percona for years now. But one area where we were stuck was the Etcd version, and we continued to … Continued

## Structure detectee

- H3: Etcd API version change
- H3: Etcd configuration in YAML
- H3: Bootstrapping Etcd cluster
- H3: Patroni configuration change
- H2: Upgrading Etcd version 3.3 to 3.5

## Images et graphiques reperes

- featured / image: [Upgrading to the New Etcd Version From 3.3 for Patroni](https://www.percona.com/wp-content/uploads/2026/03/Upgrading-to-the-New-Etcd-Version-From-3.3-for-Patroni.jpg)
- content / image: [Screenshot-from-2024-06-12-13-37-20.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-from-2024-06-12-13-37-20.png)
- content / image: [Screenshot-from-2024-06-12-12-51-43.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-from-2024-06-12-12-51-43.png)

## Auteur source

Jobin Augustine is a PostgreSQL expert, enthusiast, and Open Source advocate with more than 25 years of experience as a consultant, architect, administrator, writer, developer, and trainer. He is an active participant in Open Source communities, with a primary focus on database performance and optimization. A contributor to various open-source projects and an active blogger, Jobin also loves to code in C++ and Python. He is a senior member of the PostgreSQL community in India and a regular speaker at many international conferences. Jobin holds a Master’s in Computer Applications from NIT Calicut. He joined Percona in 2018 to launch their PostgreSQL chapter and currently serves as the Tech Lead for PostgreSQL. Prior to Percona, he worked at OpenSCG as an architect and was part of the BigSQL core team. His earlier career includes a decade-long tenure at Dell as a Senior Database Advisor, along with roles at several other technology firms.
