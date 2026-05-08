---
title: Benchmarking IBM eXFlash™ DIMM with sysbench fileio
source:
  name: Percona Blog
  url: https://www.percona.com/blog/benchmarking-exflash-with-sysbench-fileio/
  post_id: 8247
source_author:
  name: Peter Boros
  slug: peter-boros
  url: https://www.percona.com/blog/author/peter-boros/
  website: ''
published_at: '2014-08-12T12:00:56'
published_at_gmt: '2014-08-12T12:00:56'
modified_at: '2026-04-28T22:06:21'
modified_at_gmt: '2026-04-28T22:06:21'
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
- Insight for DBAs
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags:
- Diablo Technologies
- DIMM
- eXFlash
- flash storage
tag_slugs:
- diablo-technologies
- dimm
- exflash
- flash-storage
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_async_mixed_iops.png
image_count: 16
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Benchmarking IBM eXFlash™ DIMM with sysbench fileio

Source: [Percona Blog](https://www.percona.com/blog/benchmarking-exflash-with-sysbench-fileio/)

Auteur source: [Peter Boros](https://www.percona.com/blog/author/peter-boros/)

Publication: 2014-08-12T12:00:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Diablo Technologies engaged Percona to benchmark IBM eXFlash™ DIMMs in various aspects. An eXFlash™ DIMM itself is quite an interesting piece of technology. In a nutshell, it’s flash storage, which you can put in the memory DIMM slots. Enabled by Diablo’s Memory Channel Storage™ technology, this practically means low latency and some unique performance characteristics. … Continued

## Structure detectee

- H2: Environment
- H2: Asynchronous IO
- H2: Reads
- H2: Writes
- H2: Mixed
- H2: Synchronous IO
- H2: Reads
- H2: Writes
- H2: Mixed
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Benchmarking IBM eXFlash™ DIMM with sysbench fileio](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_async_mixed_iops.png)
- content / image: [exflash_fileio_async_read_tp](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_async_read_tp.png)
- content / image: [exflash_fileio_async_read_lat](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_async_read_lat.png)
- content / image: [exflash_fileio_async_write_tp](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_async_write_tp.png)
- content / image: [exflash_fileio_async_write_lat](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_async_write_lat.png)
- content / image: [exflash_fileio_async_mixed_tp](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_async_mixed_tp.png)
- content / image: [exflash_fileio_async_mixed_lat](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_async_mixed_lat.png)
- content / image: [exflash_fileio_sync_read_tp](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_sync_read_tp.png)
- content / image: [exflash_fileio_sync_read_lat](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_sync_read_lat.png)
- content / image: [exflash_fileio_sync_read_lat_zoomed](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_sync_read_lat_zoomed.png)
- content / image: [exflash_fileio_sync_write_tp](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_sync_write_tp.png)
- content / image: [exflash_fileio_sync_write_lat](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_sync_write_lat.png)
- content / image: [exflash_fileio_sync_write_lat_zoomed](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_sync_write_lat_zoomed.png)
- content / image: [exflash_fileio_sync_mixed_tp](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_sync_mixed_tp.png)
- content / image: [exflash_fileio_sync_mixed_lat](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_sync_mixed_lat.png)
- content / image: [exflash_fileio_sync_mixed_lat_zoomed](https://www.percona.com/wp-content/uploads/2026/03/exflash_fileio_sync_mixed_lat_zoomed.png)

## Auteur source

Peter is a Principal Architect at Percona's European consulting team, his special interests are performance tuning and automation for large scale systems. Before Percona, he worked at Zuora, Dropbox, and Sun microsystems, also taught MySQL courses for Oracle University. He currently lives in Debrecen, Hungary with his wife and kids.
