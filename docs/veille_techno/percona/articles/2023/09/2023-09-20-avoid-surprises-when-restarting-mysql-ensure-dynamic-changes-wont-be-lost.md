---
title: Avoid Surprises When Restarting MySQL — Ensure Dynamic Changes Won’t Be Lost
source:
  name: Percona Blog
  url: https://www.percona.com/blog/avoid-surprises-when-restarting-mysql-ensure-dynamic-changes-wont-be-lost/
  post_id: 27474
source_author:
  name: Mauricio Cacho
  slug: mauricio-cacho
  url: https://www.percona.com/blog/author/mauricio-cacho/
  website: ''
published_at: '2023-09-20T12:19:03'
published_at_gmt: '2023-09-20T12:19:03'
modified_at: '2026-03-26T20:27:13'
modified_at_gmt: '2026-03-26T20:27:13'
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
- tag:percona-toolkit:378
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- Percona Toolkit
tag_slugs:
- mysql
- mysql-and-variants
- percona-toolkit
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/restarting-mysql.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Avoid Surprises When Restarting MySQL — Ensure Dynamic Changes Won’t Be Lost

Source: [Percona Blog](https://www.percona.com/blog/avoid-surprises-when-restarting-mysql-ensure-dynamic-changes-wont-be-lost/)

Auteur source: [Mauricio Cacho](https://www.percona.com/blog/author/mauricio-cacho/)

Publication: 2023-09-20T12:19:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you’re a DBA, one of your “easiest” tasks is to stop/start MySQL during a maintenance window, but even that could lead to unwanted scenarios if you modify some dynamic parameters at some point in your instance. Here’s a brief story of how this could happen, to make it clearer: You’re a DBA managing a … Continued

## Structure detectee

- H2: Introduction of pt-config-diff and the situation
- H2: How to check dynamic changes
- H2: Things to be aware of
- H3: Final thoughts

## Images et graphiques reperes

- featured / image: [Avoid Surprises When Restarting MySQL — Ensure Dynamic Changes Won’t Be Lost](https://www.percona.com/wp-content/uploads/2026/03/restarting-mysql.jpg)

## Auteur source

Mauricio started working with MySQL back in 2014, and started with DBA tasks a few months later; quickly after that, he became passionate about troubleshooting MySQL issues and optimizing overall performance for databases.
