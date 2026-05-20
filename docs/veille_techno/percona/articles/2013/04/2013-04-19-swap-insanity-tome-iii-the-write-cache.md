---
title: 'The write cache: Swap insanity tome III'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/swap-insanity-tome-iii-the-write-cache/
  post_id: 6788
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2013-04-19T10:00:07'
published_at_gmt: '2013-04-19T10:00:07'
modified_at: '2026-03-25T16:48:06'
modified_at_gmt: '2026-03-25T16:48:06'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Hardware and Storage
- Insight for DBAs
- MySQL
category_slugs:
- hardware-and-storage
- insight-for-dbas
- mysql
tags:
- High Availability
- swapping
- Tuning
- write cache
tag_slugs:
- high-availability
- swapping
- tuning
- write-cache
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The write cache: Swap insanity tome III

Source: [Percona Blog](https://www.percona.com/blog/swap-insanity-tome-iii-the-write-cache/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2013-04-19T10:00:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Swapping has always been something bad for MySQL performance but it is even more important for HA systems. It is so important to avoid swapping with HA that NDB cluster basically forbids calling malloc after the startup phase and hence its rather complex configuration. Probably most readers of this blog know (or should know) about … Continued

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
