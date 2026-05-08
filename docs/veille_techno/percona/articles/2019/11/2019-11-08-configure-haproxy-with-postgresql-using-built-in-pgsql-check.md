---
title: Configure HAProxy with PostgreSQL Using Built-in pgsql-check
source:
  name: Percona Blog
  url: https://www.percona.com/blog/configure-haproxy-with-postgresql-using-built-in-pgsql-check/
  post_id: 21172
source_author:
  name: Jobin Augustine
  slug: jobin-augustine
  url: https://www.percona.com/blog/author/jobin-augustine/
  website: ''
published_at: '2019-11-08T14:35:13'
published_at_gmt: '2019-11-08T14:35:13'
modified_at: '2026-03-26T20:09:49'
modified_at_gmt: '2026-03-26T20:09:49'
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
- PostgreSQL
tag_slugs:
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Configure-HAProxy-PostgreSQL-pgsql-check.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Configure HAProxy with PostgreSQL Using Built-in pgsql-check

Source: [Percona Blog](https://www.percona.com/blog/configure-haproxy-with-postgresql-using-built-in-pgsql-check/)

Auteur source: [Jobin Augustine](https://www.percona.com/blog/author/jobin-augustine/)

Publication: 2019-11-08T14:35:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We discussed one of the traditional ways to configure HAProxy with PostgreSQL in our previous blog about HAProxy using Xinetd. There we briefly mentioned the limitation of the HAProxy’s built-in pgsql-check health check option. It lacks features to detect and differentiate the Primary and Hot-Standby. It tries to establish a connection to the database instance … Continued

## Structure detectee

- H2: Concept
- H2: Demonstration Setup
- H2: Preparing HAProxy
- H2: Integration of pg_hba.conf with failover / switchover procedure
- H2: Testing
- H2: Advantages and Disadvantages of pgsql-check

## Images et graphiques reperes

- featured / image: [Configure HAProxy with PostgreSQL Using Built-in pgsql-check](https://www.percona.com/wp-content/uploads/2026/03/Configure-HAProxy-PostgreSQL-pgsql-check.png)
- content / image: [PostgreSQL](https://www.percona.com/wp-content/uploads/2026/03/slonik_with_black_text_and_tagline-300x190.png)
- content / image: [Enterprise PostgreSQL](https://www.percona.com/wp-content/uploads/2026/03/Enterprise-PostgreSQL-Buyers-Guide-Banner.png)
- content / image: [HAProxy_pgsql_allgreen.png](https://www.percona.com/wp-content/uploads/2026/03/HAProxy_pgsql_allgreen.png)
- content / image: [HAProxy_pgsql_afterupdate.png](https://www.percona.com/wp-content/uploads/2026/03/HAProxy_pgsql_afterupdate.png)

## Auteur source

Jobin Augustine is a PostgreSQL expert, enthusiast, and Open Source advocate with more than 25 years of experience as a consultant, architect, administrator, writer, developer, and trainer. He is an active participant in Open Source communities, with a primary focus on database performance and optimization. A contributor to various open-source projects and an active blogger, Jobin also loves to code in C++ and Python. He is a senior member of the PostgreSQL community in India and a regular speaker at many international conferences. Jobin holds a Master’s in Computer Applications from NIT Calicut. He joined Percona in 2018 to launch their PostgreSQL chapter and currently serves as the Tech Lead for PostgreSQL. Prior to Percona, he worked at OpenSCG as an architect and was part of the BigSQL core team. His earlier career includes a decade-long tenure at Dell as a Senior Database Advisor, along with roles at several other technology firms.
