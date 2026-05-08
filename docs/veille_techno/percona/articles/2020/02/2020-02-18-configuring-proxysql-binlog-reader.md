---
title: Configuring ProxySQL Binlog Reader
source:
  name: Percona Blog
  url: https://www.percona.com/blog/configuring-proxysql-binlog-reader/
  post_id: 21050
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2020-02-18T17:26:18'
published_at_gmt: '2020-02-18T17:26:18'
modified_at: '2026-05-05T16:22:07'
modified_at_gmt: '2026-05-05T16:22:07'
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
- ProxySQL
category_slugs:
- proxysql
tags:
- ProxySQL
tag_slugs:
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Configuring-ProxySQL-Binlog-Reader.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Configuring ProxySQL Binlog Reader

Source: [Percona Blog](https://www.percona.com/blog/configuring-proxysql-binlog-reader/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2020-02-18T17:26:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In a previous post, MySQL High Availability: Stale Reads and How to Fix Them, I’ve talked about the challenges of scaling out reads, where some types of applications cannot tolerate reading stale data. One of the ways of fixing it is by using ProxySQL Binlog Reader. Long story short, binlog reader is a lightweight binary … Continued

## Structure detectee

- H2: Compile Binlog Reader:
- H2: Running Binlog Reader:
- H2: Configuring ProxySQL:
- H2: Network Traffic:
- H2: Testing:
- H2: Monitoring:
- H2: Summary:

## Images et graphiques reperes

- featured / image: [Configuring ProxySQL Binlog Reader](https://www.percona.com/wp-content/uploads/2026/03/Configuring-ProxySQL-Binlog-Reader.png)
- content / image: [Configuring ProxySQL Binlog Reader](https://www.percona.com/wp-content/uploads/2026/03/Configuring-ProxySQL-Binlog-Reader-300x168.png)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
