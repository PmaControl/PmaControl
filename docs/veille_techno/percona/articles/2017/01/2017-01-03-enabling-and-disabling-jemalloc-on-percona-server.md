---
title: Enabling and Disabling Jemalloc on Percona Server
source:
  name: Percona Blog
  url: https://www.percona.com/blog/enabling-and-disabling-jemalloc-on-percona-server/
  post_id: 16076
source_author:
  name: Daniel Guzmán Burgos
  slug: daniel-guzman-burgos
  url: https://www.percona.com/blog/author/daniel-guzman-burgos/
  website: ''
published_at: '2017-01-03T20:29:00'
published_at_gmt: '2017-01-03T20:29:00'
modified_at: '2026-05-05T18:25:37'
modified_at_gmt: '2026-05-05T18:25:37'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- jemalloc
- MySQL
- Percona Server for MySQL
tag_slugs:
- jemalloc
- mysql
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-CDC.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Enabling and Disabling Jemalloc on Percona Server

Source: [Percona Blog](https://www.percona.com/blog/enabling-and-disabling-jemalloc-on-percona-server/)

Auteur source: [Daniel Guzmán Burgos](https://www.percona.com/blog/author/daniel-guzman-burgos/)

Publication: 2017-01-03T20:29:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post discusses enabling and disabling jemalloc on Percona Server for MySQL. The benefits of jemalloc versus glibc for use with MySQL have been widely discussed. With jemalloc (along with Transparent Huge Pages disabled) you have less memory fragmentation, and thus more efficient resource management of the available server memory. For standard installations of Percona … Continued

## Structure detectee

- H2: Enabling Jemalloc on Percona Server
- H2: Disabling Jemalloc on Percona Server
- H2: How to Know if Jemalloc is Being Used?

## Images et graphiques reperes

- featured / image: [Enabling and Disabling Jemalloc on Percona Server](https://www.percona.com/wp-content/uploads/2026/03/MySQL-CDC.jpg)

## Auteur source

Daniel studied Electronic Engineering, but quickly becomes interested in all data things. He has worked as a DBA since 2007 for several companies. Working for Percona since 2014, he is the PMM Tech Lead
