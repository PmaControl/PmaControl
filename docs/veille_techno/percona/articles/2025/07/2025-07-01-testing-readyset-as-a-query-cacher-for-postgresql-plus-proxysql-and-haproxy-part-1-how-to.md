---
title: 'Testing ReadySet as a Query Cacher for PostgreSQL (Plus ProxySQL and HAproxy) Part 1: How-To'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/testing-readyset-as-a-query-cacher-for-postgresql-plus-proxysql-and-haproxy-part-1-how-to/
  post_id: 35016
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2025-07-01T14:15:07'
published_at_gmt: '2025-07-01T14:15:07'
modified_at: '2026-05-05T17:13:45'
modified_at_gmt: '2026-05-05T17:13:45'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- ProxySQL
matched_filters:
- category:proxysql:2261
- search:proxysql
categories:
- Benchmarks
- PostgreSQL
- ProxySQL
category_slugs:
- benchmarks
- postgresql
- proxysql
tags:
- Fernando PP
- PostgreSQL
- ProxySQL
tag_slugs:
- fernando-planetpostgresql
- postgresql
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Testing-ReadySet-as-a-Query-Cacher-for-PostgreSQL.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Testing ReadySet as a Query Cacher for PostgreSQL (Plus ProxySQL and HAproxy) Part 1: How-To

Source: [Percona Blog](https://www.percona.com/blog/testing-readyset-as-a-query-cacher-for-postgresql-plus-proxysql-and-haproxy-part-1-how-to/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2025-07-01T14:15:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A couple of weeks ago, I attended a PGDay event in Blumenau, a city not far away from where I live in Brazil. Opening the day were former Percona colleagues Marcelo Altmann and Wagner Bianchi, showcasing ReadySet’s support for PostgreSQL. Readyset is a source-available database cache service that differs from other solutions by not relying … Continued

## Structure detectee

- H2: A simple test suite
- H2: Environment
- H2: Methodology
- H2: Installing and configuring ReadySet
- H2: Initial snapshot and data replication
- H2: Caching data
- H2: ProxySQL
- H2: Installing ProxySQL
- H2: Configuring ProxySQL
- H2: HAproxy
- H2: Installing HAproxy
- H2: Configuring HAproxy

## Images et graphiques reperes

- featured / image: [Testing ReadySet as a Query Cacher for PostgreSQL (Plus ProxySQL and HAproxy) Part 1: How-To](https://www.percona.com/wp-content/uploads/2026/03/Testing-ReadySet-as-a-Query-Cacher-for-PostgreSQL.jpg)
- content / image: [Selection_4721-1024x361.png](https://www.percona.com/wp-content/uploads/2026/03/Selection_4721-1024x361.png)
- content / image: [enterprise PostgreSQL](https://www.percona.com/wp-content/uploads/2026/03/10-pitfalls_what-to-look-for-enterprise-postgres-1-1.png)

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
