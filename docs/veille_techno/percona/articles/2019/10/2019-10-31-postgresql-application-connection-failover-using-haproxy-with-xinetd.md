---
title: PostgreSQL Application Connection Failover Using HAProxy with xinetd
source:
  name: Percona Blog
  url: https://www.percona.com/blog/postgresql-application-connection-failover-using-haproxy-with-xinetd/
  post_id: 21138
source_author:
  name: Jobin Augustine
  slug: jobin-augustine
  url: https://www.percona.com/blog/author/jobin-augustine/
  website: ''
published_at: '2019-10-31T15:00:35'
published_at_gmt: '2019-10-31T15:00:35'
modified_at: '2026-03-26T20:09:49'
modified_at_gmt: '2026-03-26T20:09:49'
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
- PostgreSQL
tag_slugs:
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-Application-Connection-Failover-HAProxy.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# PostgreSQL Application Connection Failover Using HAProxy with xinetd

Source: [Percona Blog](https://www.percona.com/blog/postgresql-application-connection-failover-using-haproxy-with-xinetd/)

Auteur source: [Jobin Augustine](https://www.percona.com/blog/author/jobin-augustine/)

Publication: 2019-10-31T15:00:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently we published a blog about a very simple application failover using libpq features which could be the simplest of all automatic application connection routing. In this blog post, we are discussing how a proxy server using HAProxy can be used for connection routing which is a well-known technique with very wide deployment. There are … Continued

## Structure detectee

- H3: On HAProxy
- H2: Concept:
- H2: Installation and Configuration
- H2: Configuring HAProxy to use xinetd
- H2: Verification and Testing
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [PostgreSQL Application Connection Failover Using HAProxy with xinetd](https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-Application-Connection-Failover-HAProxy.png)
- content / image: [PostgreSQL](https://www.percona.com/wp-content/uploads/2026/03/slonik_with_black_text_and_tagline-300x190.png)

## Auteur source

Jobin Augustine is a PostgreSQL expert, enthusiast, and Open Source advocate with more than 25 years of experience as a consultant, architect, administrator, writer, developer, and trainer. He is an active participant in Open Source communities, with a primary focus on database performance and optimization. A contributor to various open-source projects and an active blogger, Jobin also loves to code in C++ and Python. He is a senior member of the PostgreSQL community in India and a regular speaker at many international conferences. Jobin holds a Master’s in Computer Applications from NIT Calicut. He joined Percona in 2018 to launch their PostgreSQL chapter and currently serves as the Tech Lead for PostgreSQL. Prior to Percona, he worked at OpenSCG as an architect and was part of the BigSQL core team. His earlier career includes a decade-long tenure at Dell as a Senior Database Advisor, along with roles at several other technology firms.
