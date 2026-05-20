---
title: Removing PostgreSQL Bottlenecks Caused by High Traffic
source:
  name: Percona Blog
  url: https://www.percona.com/blog/removing-postgresql-bottlenecks-caused-by-high-traffic/
  post_id: 22323
source_author:
  name: Robert Bernier
  slug: robert-bernier
  url: https://www.percona.com/blog/author/robert-bernier/
  website: ''
published_at: '2020-05-29T16:04:24'
published_at_gmt: '2020-05-29T16:04:24'
modified_at: '2026-03-26T20:09:43'
modified_at_gmt: '2026-03-26T20:09:43'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:pmm
categories:
- Insight for DBAs
- PostgreSQL
category_slugs:
- insight-for-dbas
- postgresql
tags:
- High Availability
- insight for DBAs
- PostgreSQL
tag_slugs:
- high-availability
- insight-for-dbas
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-Bottlenecks-High-Traffic.png
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Removing PostgreSQL Bottlenecks Caused by High Traffic

Source: [Percona Blog](https://www.percona.com/blog/removing-postgresql-bottlenecks-caused-by-high-traffic/)

Auteur source: [Robert Bernier](https://www.percona.com/blog/author/robert-bernier/)

Publication: 2020-05-29T16:04:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Taking our cue from Peter Zaitsev’s article on MySQL Performance Bottlenecks, I’m going to talk a little about the PostgreSQL perspective of removing bottlenecks caused by high traffic. Many stacks these days are implemented by trusting the Object Relational Mapper, ORM, to do the right thing with PostgreSQL while one creates critical business logic … Continued

## Structure detectee

- H2: About Monitoring and Statistics
- H3: Method 1: The Percona Distribution For PostgreSQL
- H3: Method 2: Compile and Install (community postgres repository)
- H3: Creating The Extension
- H3: Restart The Server
- H2: About “Categories” and “Potential Impact”
- H2: Tuning Performance Parameters
- H2: Session Connections: Managing
- H2: Autovacuum: Basic
- H2: Autovacuum: Advanced
- H2: Bloat
- H2: Data Hotspots
- H2: Competing Application Processes
- H2: Replication Latency
- H2: Server Environment

## Images et graphiques reperes

- featured / image: [Removing PostgreSQL Bottlenecks Caused by High Traffic](https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-Bottlenecks-High-Traffic.png)
- content / image: [Find and fix PostgreSQL issues faster](https://www.percona.com/wp-content/uploads/2026/03/3cae0983-f73f-43b2-a78a-185ea9d20d2b.png)
- content / image: [PostgreSQL Bottlenecks High Traffic](https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-Bottlenecks-High-Traffic-300x168.png)
- content / image: [Postgres traffic](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.16.41-PM-1024x426-1.png)
- content / image: [postgresql instances](https://www.percona.com/wp-content/uploads/2026/03/PMM-Connection-Details-session-connections-1024x528-1.png)
- content / image: [pmm query](https://www.percona.com/wp-content/uploads/2026/03/PMM-QUERY-Analytics-data-hotspots-1024x499-1.png)
- content / image: [postgresql tuple activity](https://www.percona.com/wp-content/uploads/2026/03/PMM-Server-Environment-II-1-1024x539-1.png)
- content / image: [postgresql high traffic](https://www.percona.com/wp-content/uploads/2026/03/PMM-Server-Environment-1024x528-1.png)

## Auteur source

Robert's first working computer was the very user-friendly IBM 360 with an awesome 4MB RAM. After a round of much needed therapy overcoming the trauma of programming with punch cards he discovered the IBM-XT and the miracle of DOS 2.0. Years later, Robert became enamored with Linux and the opensource world and after meeting one of the members of CORE his primary focus had become all things PostgreSQL. Robert has since then worked in mom and pop companies, fortune 50 corporations and a number of very cool environments including the famed Los Alamos National Laboratory in New Mexico, birthplace of the atomic age. Although reluctant to leave the enjoyable experience of California's Silicon Valley commuter life, he returned to the Pacific Northwest and once again experienced real weather. These days, he serves as the PostgreSQL Consultant here at Percona.
