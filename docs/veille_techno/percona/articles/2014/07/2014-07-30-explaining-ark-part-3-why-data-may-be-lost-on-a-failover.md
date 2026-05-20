---
title: 'Explaining Ark Part 3: Why Data May Be Lost on a Failover'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/explaining-ark-part-3-why-data-may-be-lost-on-a-failover/
  post_id: 9885
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2014-07-30T14:55:43'
published_at_gmt: '2014-07-30T14:55:43'
modified_at: '2026-03-25T18:29:18'
modified_at_gmt: '2026-03-25T18:29:18'
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
category_slugs:
- mysql
tags:
- Ark
- consensus
- elections
- explaining_ark
- Failover
- MongoDB
- Replication
- tokumx
tag_slugs:
- ark
- consensus
- elections
- explaining_ark
- failover
- mongodb
- replication
- tokumx
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Explaining Ark Part 3: Why Data May Be Lost on a Failover

Source: [Percona Blog](https://www.percona.com/blog/explaining-ark-part-3-why-data-may-be-lost-on-a-failover/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2014-07-30T14:55:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is the third post in a series of posts that explains Ark, a consensus algorithm we’ve developed for TokuMX and MongoDB to fix known issues in elections and failover. The tech report we released last week describes the algorithm in full detail. These posts are a layman’s explanation. In the first post, I discussed … Continued
