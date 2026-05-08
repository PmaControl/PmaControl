---
title: Partitioning, Free Lunches, & Indexing, Part 2
source:
  name: Percona Blog
  url: https://www.percona.com/blog/partitioning-free-lunches-and-indexing-part-2/
  post_id: 9534
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2011-01-28T19:26:51'
published_at_gmt: '2011-01-28T19:26:51'
modified_at: '2026-03-25T18:15:43'
modified_at_gmt: '2026-03-25T18:15:43'
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
- MySQL
- partitioning
- TokuDB
tag_slugs:
- mysql
- partitioning
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Partitioning, Free Lunches, & Indexing, Part 2

Source: [Percona Blog](https://www.percona.com/blog/partitioning-free-lunches-and-indexing-part-2/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2011-01-28T19:26:51

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Review In part one, I presented a very brief and particular view of partitioning. I covered what partitioning is, with hardly a mention of why one would use partitioning. In this post, I’ll talk about a few use cases often cited as justification for using partitions. Lots of disks → Lots of partitioning of tables … Continued

## Structure detectee

- H3: Review
- H3: Lots of disks → Lots of partitioning of tables
- H3: Avoiding Table Scans
- H3: If partitions are indexes, are they good ones?
- H3: Conclusion
