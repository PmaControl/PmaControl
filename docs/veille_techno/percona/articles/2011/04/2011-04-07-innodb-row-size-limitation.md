---
title: Innodb row size limitation
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-row-size-limitation/
  post_id: 2776
source_author:
  name: Fernando Ipar
  slug: fernando
  url: https://www.percona.com/blog/author/fernando/
  website: http://www.percona.com/blog
published_at: '2011-04-07T21:04:36'
published_at_gmt: '2011-04-07T21:04:36'
modified_at: '2026-04-28T21:25:07'
modified_at_gmt: '2026-04-28T21:25:07'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
category_slugs:
- insight-for-developers
- mysql
tags:
- InnoDB
- Storage Engine
- Tips
tag_slugs:
- innodb
- storage-engine
- tips
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Innodb row size limitation

Source: [Percona Blog](https://www.percona.com/blog/innodb-row-size-limitation/)

Auteur source: [Fernando Ipar](https://www.percona.com/blog/author/fernando/)

Publication: 2011-04-07T21:04:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently worked on a customer case where at seemingly random times, inserts would fail with Innodb error 139. This is a rather simple problem, but due to it’s nature, it may only affect you after you already have a system running in production for a while.

## Auteur source

Fernando is part of Percona's team working as Senior Consultant. Prior to joining Percona, Fernando worked as a consultant for financial services institutions, telcos, and technology providers.
