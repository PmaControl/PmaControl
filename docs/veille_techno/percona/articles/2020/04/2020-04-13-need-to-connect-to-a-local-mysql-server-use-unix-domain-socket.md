---
title: Need to Connect to a Local MySQL Server? Use Unix Domain Socket!
source:
  name: Percona Blog
  url: https://www.percona.com/blog/need-to-connect-to-a-local-mysql-server-use-unix-domain-socket/
  post_id: 22190
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-04-13T13:31:48'
published_at_gmt: '2020-04-13T13:31:48'
modified_at: '2026-03-23T15:15:53'
modified_at_gmt: '2026-03-23T15:15:53'
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
- MySQL
- Percona Software
category_slugs:
- benchmarks
- mysql
- percona-software
tags:
- Benchmarks
- MySQL
- Percona Server for MySQL
- Percona Software
- Unix Domain Socket
tag_slugs:
- benchmarks
- mysql
- percona-server
- percona-software
- unix-domain-socket
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Unix-Socket-Domain.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Need to Connect to a Local MySQL Server? Use Unix Domain Socket!

Source: [Percona Blog](https://www.percona.com/blog/need-to-connect-to-a-local-mysql-server-use-unix-domain-socket/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-04-13T13:31:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When connecting to a local MySQL instance, you have two commonly used methods: use TCP/IP protocol to connect to local address – “localhost” or 127.0.0.1 – or use Unix Domain Socket. If you have a choice (if your application supports both methods), use Unix Domain Socket as this is both more secure and more efficient. … Continued

## Structure detectee

- H3: Benchmarking TCP/IP Connection vs Unix Domain Socket for MySQL
- H3: Single Thread and 64 Thread Benchmark Run
- H3: Running MySQLDump
- H3: 100K QPS Injection Benchmark
- H3: 200K QPS Injection Benchmark
- H3: Summary

## Images et graphiques reperes

- featured / image: [Need to Connect to a Local MySQL Server? Use Unix Domain Socket!](https://www.percona.com/wp-content/uploads/2026/03/Unix-Socket-Domain.png)
- content / image: [Unix Socket Domain](https://www.percona.com/wp-content/uploads/2026/03/Unix-Socket-Domain-300x168.png)
- content / image: [image2-2-2-1024x630.png](https://www.percona.com/wp-content/uploads/2026/03/image2-2-2-1024x630.png)
- content / image: [image1-2-2-1024x627.png](https://www.percona.com/wp-content/uploads/2026/03/image1-2-2-1024x627.png)
- content / image: [image3-2-2-1024x613.png](https://www.percona.com/wp-content/uploads/2026/03/image3-2-2-1024x613.png)
- content / image: [image4-2-2-1024x616.png](https://www.percona.com/wp-content/uploads/2026/03/image4-2-2-1024x616.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
