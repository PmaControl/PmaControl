---
title: super_read_only and GTID replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/super_read_only-gtid-replication/
  post_id: 9986
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2015-09-08T07:00:07'
published_at_gmt: '2015-09-08T07:00:07'
modified_at: '2026-05-05T22:30:35'
modified_at_gmt: '2026-05-05T22:30:35'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- GTID-replication
- MySQL 5.7.8
- Percona Server 5.6.21
- Stephane Combaudon
- super_read_only
- WebScaleSQL
tag_slugs:
- gtid-replication
- mysql-5-7-8
- percona-server-5-6-21
- stephane-combaudon
- super_read_only
- webscalesql
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# super_read_only and GTID replication

Source: [Percona Blog](https://www.percona.com/blog/super_read_only-gtid-replication/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2015-09-08T07:00:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Server 5.6.21+ and MySQL 5.7.8+ offer the super_read_only option that was first implemented in WebscaleSQL. Unlike read_only, this option prevents all users from running writes (even those with the SUPER privilege). Sure enough, this is a great feature, but what’s the relation with GTID? Read on! TL;DR Enabling super_read_only on all slaves when using … Continued

## Structure detectee

- H2: TL;DR
- H2: GTID replication is awesome…
- H2: … but there’s a catch
- H2: super_read_only can help

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
