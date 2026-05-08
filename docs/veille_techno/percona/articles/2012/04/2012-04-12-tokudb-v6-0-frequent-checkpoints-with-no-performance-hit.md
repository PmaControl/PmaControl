---
title: 'TokuDB v6.0: Frequent Checkpoints with No Performance Hit'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tokudb-v6-0-frequent-checkpoints-with-no-performance-hit/
  post_id: 9656
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2012-04-12T23:29:37'
published_at_gmt: '2012-04-12T23:29:37'
modified_at: '2026-03-25T18:21:34'
modified_at_gmt: '2026-03-25T18:21:34'
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
- Announcement
- Benchmarking
- MySQL
- NewSQL
- Percona
- TokuDB
- Tokutek
- update
tag_slugs:
- announcement
- benchmarking
- mysql
- newsql
- cap-percona
- tokudb
- tokutek
- update
featured_image_url: https://www.percona.com/blog/wp-content/uploads/2012/04/InnoCheckpoint.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TokuDB v6.0: Frequent Checkpoints with No Performance Hit

Source: [Percona Blog](https://www.percona.com/blog/tokudb-v6-0-frequent-checkpoints-with-no-performance-hit/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2012-04-12T23:29:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Checkpointing — which involves periodically writing out dirty pages from memory — is central to the design of crash recovery for both TokuDB and InnoDB. A key issue in designing a checkpointing system is how often to checkpoint, and TokuDB takes a very different approach from InnoDB. How often and how much InnoDB checkpoints is … Continued

## Images et graphiques reperes

- content / image: [InnoCheckpoint.png](https://www.percona.com/blog/wp-content/uploads/2012/04/InnoCheckpoint.png)
- content / image: [Sysbench performance with different compressors](https://www.percona.com/blog/wp-content/uploads/2012/04/CompressionPrefV6.0.png)
