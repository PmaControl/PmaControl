---
title: Setting up XFS on Hardware RAID — the simple edition
source:
  name: Percona Blog
  url: https://www.percona.com/blog/setting-up-xfs-the-simple-edition/
  post_id: 3263
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2011-12-16T17:10:58'
published_at_gmt: '2011-12-16T17:10:58'
modified_at: '2026-05-05T17:48:56'
modified_at_gmt: '2026-05-05T17:48:56'
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
- Performance
- raid
- Storage Engine
- Tips
- Tuning
- xfs
tag_slugs:
- performance
- raid
- storage-engine
- tips
- tuning
- xfs
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Setting up XFS on Hardware RAID — the simple edition

Source: [Percona Blog](https://www.percona.com/blog/setting-up-xfs-the-simple-edition/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2011-12-16T17:10:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There are about a gazillion FAQs and HOWTOs out there that talk about XFS configuration, RAID IO alignment, and mount point options. I wanted to try to put some of that information together in a condensed and simplified format that will work for the majority of use cases. This is not meant to cover every … Continued

## Structure detectee

- H2: RAID setup
- H2: Partitioning
- H2: Aligning the Partitions
- H2: Create the Filesystem
- H2: Mount the filesystem
- H2: Setting the IO scheduler

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
