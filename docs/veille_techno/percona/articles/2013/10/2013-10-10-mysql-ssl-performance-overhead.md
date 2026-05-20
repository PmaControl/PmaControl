---
title: SSL Performance Overhead in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-ssl-performance-overhead/
  post_id: 7423
source_author:
  name: Ernie Souhrada
  slug: percona_ews
  url: https://www.percona.com/blog/author/percona_ews/
  website: http://www.percona.com
published_at: '2013-10-10T13:00:01'
published_at_gmt: '2013-10-10T13:00:01'
modified_at: '2026-05-05T17:41:17'
modified_at_gmt: '2026-05-05T17:41:17'
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
- Insight for Developers
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- insight-for-developers
- mysql
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/sysbench-throughput.png
image_count: 3
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# SSL Performance Overhead in MySQL

Source: [Percona Blog](https://www.percona.com/blog/mysql-ssl-performance-overhead/)

Auteur source: [Ernie Souhrada](https://www.percona.com/blog/author/percona_ews/)

Publication: 2013-10-10T13:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

NOTE: This is part 1 of what will be a two-part series on the performance implications of using in-flight data encryption. Some of you may recall my security webinar from back in mid-August; one of the follow-up questions that I was asked was about the performance impact of enabling SSL connections. My answer was 25%, … Continued

## Structure detectee

- H3: Test 1: Connection Pool
- H3: Test 2: Connection Time
- H3: Analysis and Parting Thoughts

## Images et graphiques reperes

- featured / graph_or_chart: [SSL Performance Overhead in MySQL](https://www.percona.com/wp-content/uploads/2026/03/sysbench-throughput.png)
- content / image: [sysbench-response-time](https://www.percona.com/wp-content/uploads/2026/03/sysbench-response-time.png)
- content / graph_or_chart: [connection-throughput](https://www.percona.com/wp-content/uploads/2026/03/connection-throughput.png)

## Auteur source

Ernie joined Percona in April 2012 as a Senior Consultant. In his previous lives, he has been everything from a Perl/Java developer to a Linux sysadmin, a MySQL DBA to a Cisco network engineer, and a security auditor to an IT engineering manager, many of these things all at the same time. When not working on MySQL, he might be found on the ski slope, at a psytrance festival, or at the nearest sushi bar.
