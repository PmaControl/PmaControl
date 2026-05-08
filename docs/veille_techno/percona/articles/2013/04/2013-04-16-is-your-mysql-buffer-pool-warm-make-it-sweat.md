---
title: Is your MySQL buffer pool warm? Make it sweat!
source:
  name: Percona Blog
  url: https://www.percona.com/blog/is-your-mysql-buffer-pool-warm-make-it-sweat/
  post_id: 6841
source_author:
  name: Peter Boros
  slug: peter-boros
  url: https://www.percona.com/blog/author/peter-boros/
  website: ''
published_at: '2013-04-16T10:00:48'
published_at_gmt: '2013-04-16T10:00:48'
modified_at: '2026-05-05T22:29:40'
modified_at_gmt: '2026-05-05T22:29:40'
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
- Insight for Developers
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- percona-software
tags:
- Groupon
- Kyle Oppenheim
- MySQL buffer pool
- MySQL High Availability
- Percona Playback
- Peter Boros
- Replaying Queries
- Warm standby server
tag_slugs:
- groupon
- kyle-oppenheim
- mysql-buffer-pool
- mysql-high-availability
- percona-playback
- peter-boros
- replaying-queries
- warm-standby-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/playback_architecture.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Is your MySQL buffer pool warm? Make it sweat!

Source: [Percona Blog](https://www.percona.com/blog/is-your-mysql-buffer-pool-warm-make-it-sweat/)

Auteur source: [Peter Boros](https://www.percona.com/blog/author/peter-boros/)

Publication: 2013-04-16T10:00:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Today’s blog post diving into the waters of the MySQL buffer pool is a cross-post from Groupon’s engineering blog, and is Part 1 of 2. Thank you to Kyle Oppenheim at Groupon for contributing to this project and post. We’ll be posting Part 2 on Thursday. I’ll be at the Percona Live MySQL Conference and … Continued

## Structure detectee

- H2: Replaying Queries
- H2: Benchmarks

## Images et graphiques reperes

- featured / image: [Is your MySQL buffer pool warm? Make it sweat!](https://www.percona.com/wp-content/uploads/2026/03/playback_architecture.png)
- content / image: [Disk read I/O for chunk 1 followed by 2 - MySQL buffer pool](https://www.percona.com/wp-content/uploads/2026/03/disk_io_chunk_1and2.png)
- content / image: [Disk read IO for chunk 3 followed by 4 - MySQL buffer pool](https://www.percona.com/wp-content/uploads/2026/03/graph_warmup_io_3_4.png)
- content / image: [disk_io_chunk_1and1 - MySQL buffer pool](https://www.percona.com/wp-content/uploads/2026/03/disk_io_chunk_1and1.png)

## Auteur source

Peter is a Principal Architect at Percona's European consulting team, his special interests are performance tuning and automation for large scale systems. Before Percona, he worked at Zuora, Dropbox, and Sun microsystems, also taught MySQL courses for Oracle University. He currently lives in Debrecen, Hungary with his wife and kids.
