---
title: 'ProxySQL Query Cache: What It Is, How It Works'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/proxysql-query-cache/
  post_id: 17927
source_author:
  name: Ananias Tsalouchidis
  slug: ananias-tsalouchidis
  url: https://www.percona.com/blog/author/ananias-tsalouchidis/
  website: ''
published_at: '2018-02-07T18:40:05'
published_at_gmt: '2018-02-07T18:40:05'
modified_at: '2026-05-05T20:30:24'
modified_at_gmt: '2026-05-05T20:30:24'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:proxysql
categories:
- Insight for DBAs
- MySQL
- ProxySQL
category_slugs:
- insight-for-dbas
- mysql
- proxysql
tags:
- database
- MySQL
- Open Source
- ProxySQL
- query cache
tag_slugs:
- database
- mysql
- open-source
- proxysql
- query-cache
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/query_cache_proxy_sql-e1517981403132.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ProxySQL Query Cache: What It Is, How It Works

Source: [Percona Blog](https://www.percona.com/blog/proxysql-query-cache/)

Auteur source: [Ananias Tsalouchidis](https://www.percona.com/blog/author/ananias-tsalouchidis/)

Publication: 2018-02-07T18:40:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll present the ProxySQL query cache functionality. This is a query caching mechanism on top of ProxySQL. As there are already many how-tos regarding the ProxySQL prerequisites and installation process, we are going to skip these steps. For those who are already familiar with ProxySQL query cache configuration, let’s go directly … Continued

## Structure detectee

- H2: What is ProxySQL Query Cache
- H4: How it Works
- H4: Configuration
- H4: Add mysql_query_rules To Be Cached
- H4: Points of Interest
- H4: ProxySQL Query Cache Limitations
- H4: Conclusion
- H4: You May Also Like

## Images et graphiques reperes

- featured / image: [ProxySQL Query Cache: What It Is, How It Works](https://www.percona.com/wp-content/uploads/2026/03/query_cache_proxy_sql-e1517981403132.png)
- content / image: [ProxySQL query cache](https://www.percona.com/wp-content/uploads/2026/03/proxy_sql_by_range-1024x436.png)
- content / image: [ProxySQL query cache](https://www.percona.com/wp-content/uploads/2026/03/proxy_sql_by_pk-1024x442.png)

## Auteur source

Ananias is a Principal MySQL DBA who joined Percona on May 2017. He holds a BSc and a MSc in computer science and has a 10+ years working experience as a systems and databases administrator. He loves databases and perl scripting. He has worked for big companies and academic institutions and has also been involved into numerous research programs.
