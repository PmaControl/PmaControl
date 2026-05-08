---
title: Enabling crash-safe slaves with MySQL 5.6
source:
  name: Percona Blog
  url: https://www.percona.com/blog/enabling-crash-safe-slaves-with-mysql-5-6/
  post_id: 7346
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2013-09-13T07:01:02'
published_at_gmt: '2013-09-13T07:01:02'
modified_at: '2026-04-28T21:55:50'
modified_at_gmt: '2026-04-28T21:55:50'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- crash-safe slaves
- MySQL 5.6
tag_slugs:
- crash-safe-slaves
- mysql-5-6
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Enabling crash-safe slaves with MySQL 5.6

Source: [Percona Blog](https://www.percona.com/blog/enabling-crash-safe-slaves-with-mysql-5-6/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2013-09-13T07:01:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Being able to configure slaves to be crash-safe is one of the major improvements of MySQL 5.6 with regards to replication. However we noticed confusion on how to enable this feature correctly, so let’s clarify how it should be done. In short 1. Stop MySQL on slave 2. Add relay_log_info_repository = TABLE and relay_log_recovery = … Continued

## Structure detectee

- H2: In short
- H2: The details

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
