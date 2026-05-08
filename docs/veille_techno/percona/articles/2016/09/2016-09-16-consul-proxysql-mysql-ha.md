---
title: Consul, ProxySQL and MySQL HA
source:
  name: Percona Blog
  url: https://www.percona.com/blog/consul-proxysql-mysql-ha/
  post_id: 15653
source_author:
  name: Nik Vyzas
  slug: nik-vyzas
  url: https://www.percona.com/blog/author/nik-vyzas/
  website: http://www.percona.com/blog/
published_at: '2016-09-16T15:20:53'
published_at_gmt: '2016-09-16T15:20:53'
modified_at: '2026-05-05T18:17:36'
modified_at_gmt: '2026-05-05T18:17:36'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- MySQL
category_slugs:
- mysql
tags:
- Consul
- High Availability
- MHA
- MySQL
- proxy
- ProxySQL
tag_slugs:
- consul
- high-availability
- mha
- mysql
- proxy
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ProxySQL.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Consul, ProxySQL and MySQL HA

Source: [Percona Blog](https://www.percona.com/blog/consul-proxysql-mysql-ha/)

Auteur source: [Nik Vyzas](https://www.percona.com/blog/author/nik-vyzas/)

Publication: 2016-09-16T15:20:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When it comes to “decision time” about which type of MySQL HA (high-availability) solution to implement, and how to architect the solution, many questions come to mind. The most important questions are: “What are the best tools to provide HA and Load Balancing?” “Should I be deploying this proxy tool on my application servers or … Continued

## Structure detectee

- H4: Installation of Consul:
- H4: Configuration of Consul on Application Server (used as ‘bootstrap’ node):
- H4: Configuration of Consul on Proxy Servers:
- H4: Installation & Configuration of ProxySQL:
- H4: MySQL Configuration:
- H4: Testing Consul:
- H4: Testing ProxySQL:

## Images et graphiques reperes

- featured / image: [Consul, ProxySQL and MySQL HA](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL.png)
- content / image: [Consul ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-09-06-at-17.38.49.png)
- content / image: [Consul GUI](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2016-09-09-at-13.50.34.png)

## Auteur source

Nik Vyzas joined the Percona Remote DBA Team in 2015 and is based out of Athens, Greece. Nik has 10+ years experience working with various SQL, NoSQL and Open-source databases. He has worked at a number of companies including Accenture and Pythian. When he is not working, Nik enjoys coding, gaming and cycling.
