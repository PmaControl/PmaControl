---
title: ProxySQL Overhead — Explained and Measured
source:
  name: Percona Blog
  url: https://www.percona.com/blog/proxysql-overhead-explained-and-measured/
  post_id: 23025
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-08-28T15:06:53'
published_at_gmt: '2020-08-28T15:06:53'
modified_at: '2026-03-23T15:39:07'
modified_at_gmt: '2026-03-23T15:39:07'
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
- MySQL
- mysql-and-variants
- ProxySQL
tag_slugs:
- mysql
- mysql-and-variants
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PZ_ProxySQL.png
image_count: 7
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ProxySQL Overhead — Explained and Measured

Source: [Percona Blog](https://www.percona.com/blog/proxysql-overhead-explained-and-measured/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-08-28T15:06:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

ProxySQL brings a lot of value to your MySQL infrastructures such as Caching or Connection Multiplexing but it does not come free — your database needs to go through additional processing traffic which adds some overhead. In this blog post, we’re going to discuss where this overhead comes from and measure such overhead. Types of … Continued

## Structure detectee

- H2: Types of Overhead and Where it Comes From

## Images et graphiques reperes

- featured / image: [ProxySQL Overhead — Explained and Measured](https://www.percona.com/wp-content/uploads/2026/03/PZ_ProxySQL.png)
- content / image: [PZ_ProxySQL-300x157.png](https://www.percona.com/wp-content/uploads/2026/03/PZ_ProxySQL-300x157.png)
- content / image: [NetworkOverhead.png](https://www.percona.com/wp-content/uploads/2026/03/NetworkOverhead.png)
- content / image: [ProxySQLvsMySQL.png](https://www.percona.com/wp-content/uploads/2026/03/ProxySQLvsMySQL.png)
- content / image: [DirectandProxySQL.png](https://www.percona.com/wp-content/uploads/2026/03/DirectandProxySQL.png)
- content / image: [TCP.png](https://www.percona.com/wp-content/uploads/2026/03/TCP.png)
- content / image: [connection.png](https://www.percona.com/wp-content/uploads/2026/03/connection.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
