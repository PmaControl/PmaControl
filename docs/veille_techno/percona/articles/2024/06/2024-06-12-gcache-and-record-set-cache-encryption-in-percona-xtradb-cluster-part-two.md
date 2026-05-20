---
title: GCache and Record-Set Cache Encryption in Percona XtraDB Cluster – Part Two
source:
  name: Percona Blog
  url: https://www.percona.com/blog/gcache-and-record-set-cache-encryption-in-percona-xtradb-cluster-part-two/
  post_id: 28603
source_author:
  name: Kamil Holubicki
  slug: kamil-holubicki
  url: https://www.percona.com/blog/author/kamil-holubicki/
  website: ''
published_at: '2024-06-12T14:51:25'
published_at_gmt: '2024-06-12T14:51:25'
modified_at: '2026-03-26T20:26:19'
modified_at_gmt: '2026-03-26T20:26:19'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- Percona XtraDB Cluster
tag_slugs:
- mysql
- mysql-and-variants
- percona-xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/GCache-and-Record-Set-Cache-Encryption-in-Percona-XtraDB-Cluster-1.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# GCache and Record-Set Cache Encryption in Percona XtraDB Cluster – Part Two

Source: [Percona Blog](https://www.percona.com/blog/gcache-and-record-set-cache-encryption-in-percona-xtradb-cluster-part-two/)

Auteur source: [Kamil Holubicki](https://www.percona.com/blog/author/kamil-holubicki/)

Publication: 2024-06-12T14:51:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Keeping Your Data Safe: An Introduction to Data-at-Rest Encryption in Percona XtraDB Cluster. In the first part of this blog post, we learned how to enable GCache and Record-Set cache encryption in Percona XtraDB Cluster. This part will explore the details of the implementation to understand what happens behind the scenes. How does it work … Continued

## Structure detectee

- H2: How does it work internally?
- H2: How does it work?
- H3: Case 1: No pages mapped to VM1, encryption cache empty
- H3: Case 2: SIGSEGV occurs, page protection is PROT_NONE, no free physical pages (cache full)
- H3: Case 3: sync() requested
- H1: What about encryption keys?

## Images et graphiques reperes

- featured / image: [GCache and Record-Set Cache Encryption in Percona XtraDB Cluster – Part Two](https://www.percona.com/wp-content/uploads/2026/03/GCache-and-Record-Set-Cache-Encryption-in-Percona-XtraDB-Cluster-1.jpg)
- content / image: [Galera-SST-GCache-mmap-1.jpg](https://www.percona.com/wp-content/uploads/2026/03/Galera-SST-GCache-mmap-1.jpg)
- content / image: [Galera-SST-GCache-enc-mmap-1-1.jpg](https://www.percona.com/wp-content/uploads/2026/03/Galera-SST-GCache-enc-mmap-1-1.jpg)
- content / image: [Galera-SST-CacheEncryption-HLD.jpg](https://www.percona.com/wp-content/uploads/2026/03/Galera-SST-CacheEncryption-HLD.jpg)
