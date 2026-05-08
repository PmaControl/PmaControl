---
title: 'Virident vCache vs. FlashCache: Part 2'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/virident-vcache-vs-flashcache-part-2/
  post_id: 6972
source_author:
  name: Ernie Souhrada
  slug: percona_ews
  url: https://www.percona.com/blog/author/percona_ews/
  website: http://www.percona.com
published_at: '2013-05-17T10:00:56'
published_at_gmt: '2013-05-17T10:00:56'
modified_at: '2026-03-25T16:57:08'
modified_at_gmt: '2026-03-25T16:57:08'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Hardware and Storage
- MySQL
category_slugs:
- benchmarks
- hardware-and-storage
- mysql
tags:
- Benchmarks
- Ernie Souhrada
- FlashCache
- MLC
- PCIe
- sysbench
- vCache
- Virident
tag_slugs:
- benchmarks
- ernie-souhrada
- flashcache
- mlc
- pcie
- sysbench
- vcache
- virident
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/vcache_trx_params.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Virident vCache vs. FlashCache: Part 2

Source: [Percona Blog](https://www.percona.com/blog/virident-vcache-vs-flashcache-part-2/)

Auteur source: [Ernie Souhrada](https://www.percona.com/blog/author/percona_ews/)

Publication: 2013-05-17T10:00:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is the second part in a two-part series comparing Virident’s vCache to FlashCache. The first part was focused on usability and feature comparison; in this post, we’ll look at some sysbench test results. Disclosure: The research and testing conducted for this post were sponsored by Virident. First, some background information. All tests were conducted … Continued

## Structure detectee

- H3: vCache vs. vCache – MySQL parameter testing
- H3: vCache vs. FlashCache – the basics
- H3: vCache vs. FlashCache – dirty page threshold
- H3: Conclusion
- H3: Base MySQL & Benchmark Configuration

## Images et graphiques reperes

- featured / image: [Virident vCache vs. FlashCache: Part 2](https://www.percona.com/wp-content/uploads/2026/03/vcache_trx_params.png)
- content / image: [vcache_response_params](https://www.percona.com/wp-content/uploads/2026/03/vcache_response_params.png)
- content / image: [vcache_fcache_trx_params](https://www.percona.com/wp-content/uploads/2026/03/vcache_fcache_trx_params.png)
- content / image: [vcache_fcache_read_write](https://www.percona.com/wp-content/uploads/2026/03/vcache_fcache_read_write.png)
- content / image: [cpu-usage-all](https://www.percona.com/wp-content/uploads/2026/03/cpu-usage-all-1024x768.png)
- content / image: [vcache-dirty_trx_params](https://www.percona.com/wp-content/uploads/2026/03/vcache-dirty_trx_params.png)

## Auteur source

Ernie joined Percona in April 2012 as a Senior Consultant. In his previous lives, he has been everything from a Perl/Java developer to a Linux sysadmin, a MySQL DBA to a Cisco network engineer, and a security auditor to an IT engineering manager, many of these things all at the same time. When not working on MySQL, he might be found on the ski slope, at a psytrance festival, or at the nearest sushi bar.
