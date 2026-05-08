---
title: MySQL encryption performance, revisited
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-encryption-performance-revisited/
  post_id: 7505
source_author:
  name: Ernie Souhrada
  slug: percona_ews
  url: https://www.percona.com/blog/author/percona_ews/
  website: http://www.percona.com
published_at: '2013-11-18T13:08:35'
published_at_gmt: '2013-11-18T13:08:35'
modified_at: '2026-05-05T21:54:24'
modified_at_gmt: '2026-05-05T21:54:24'
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
- Security
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
- security
tags:
- Benchmarks
- encryption
- Ernie Souhrada
- security
- SSL
tag_slugs:
- benchmarks
- encryption
- ernie-souhrada
- security
- ssl
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/connection-throughput2-e1384772220651.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL encryption performance, revisited

Source: [Percona Blog](https://www.percona.com/blog/mysql-encryption-performance-revisited/)

Auteur source: [Ernie Souhrada](https://www.percona.com/blog/author/percona_ews/)

Publication: 2013-11-18T13:08:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is part two on a two-part series on the performance implications of in-flight data encryption with MySQL. In the first part, I focused specifically on the impact of using MySQL’s built-in SSL support with some rather surprising results. Certainly it was expected that query throughput would be lower with SSL than without, but I … Continued

## Structure detectee

- H4: Test Environment
- H4: External Encryption Technology
- H4: Connection Performance over High-Latency Links
- H4: Parting Thoughts

## Images et graphiques reperes

- featured / image: [MySQL encryption performance, revisited](https://www.percona.com/wp-content/uploads/2026/03/connection-throughput2-e1384772220651.png)
- content / image: [us_to_ireland_throughput](https://www.percona.com/wp-content/uploads/2026/03/us_to_ireland_throughput-e1384773880248.png)
- content / image: [us_to_us_throughput](https://www.percona.com/wp-content/uploads/2026/03/us_to_us_throughput-e1384774118369.png)

## Auteur source

Ernie joined Percona in April 2012 as a Senior Consultant. In his previous lives, he has been everything from a Perl/Java developer to a Linux sysadmin, a MySQL DBA to a Cisco network engineer, and a security auditor to an IT engineering manager, many of these things all at the same time. When not working on MySQL, he might be found on the ski slope, at a psytrance festival, or at the nearest sushi bar.
