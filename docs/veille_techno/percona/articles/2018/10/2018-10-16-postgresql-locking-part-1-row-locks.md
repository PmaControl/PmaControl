---
title: 'PostgreSQL locking, Part 1: Row Locks'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/postgresql-locking-part-1-row-locks/
  post_id: 19450
source_author:
  name: Nickolay Ihalainen
  slug: nickolay-ihalainen
  url: https://www.percona.com/blog/author/nickolay-ihalainen/
  website: ''
published_at: '2018-10-16T14:26:00'
published_at_gmt: '2018-10-16T14:26:00'
modified_at: '2026-03-26T20:10:58'
modified_at_gmt: '2026-03-26T20:10:58'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- Percona Toolkit
matched_filters:
- search:percona-toolkit
categories:
- Insight for DBAs
- Insight for Developers
- PostgreSQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- postgresql
tags:
- internals
- Locking
- locks
tag_slugs:
- internals
- locking
- locks
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-row-level-locks.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# PostgreSQL locking, Part 1: Row Locks

Source: [Percona Blog](https://www.percona.com/blog/postgresql-locking-part-1-row-locks/)

Auteur source: [Nickolay Ihalainen](https://www.percona.com/blog/author/nickolay-ihalainen/)

Publication: 2018-10-16T14:26:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

An understanding of PostgreSQL locking is important to build scalable applications and avoid downtime. Modern computers and servers have many CPU cores and it’s possible to execute multiple queries in parallel. Databases containing many consistent structures with changes made by queries or background processes running in parallel could crash a database or even corrupt data. … Continued

## Structure detectee

- H2: Row locks – an overview
- H3: Example environment
- H3: Row locks
- H3: pg_locks
- H3: pg_stat_activity
- H3: Source code-level investigation
- H2: Summary

## Images et graphiques reperes

- featured / image: [PostgreSQL locking, Part 1: Row Locks](https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL-row-level-locks.jpg)
- content / image: [row signing with postgresql](https://www.percona.com/wp-content/uploads/2026/03/row-signing-with-postgresql.jpg)

## Auteur source

Nickolay joined Percona in December 2010, after working for several years at what is now the most popular cinema site in Russia. During the time he was there, Nickolay and a small team of developers were responsible for scaling the site into one which now serves over a million unique visitors per day. Prior to that, he worked for several other companies, including NetUp, which provides ISP billing and IPTV solutions, and eHouse, the oldest Russian e-commerce company. Nickolay has a great deal of experience in both systems administration and programming. His experience includes extensive hands-on work with a broad range of technologies, including SQL, MySQL, PHP, C, C++, Python, Java, XML, OS parameter tuning (Linux, Solaris), caching techniques (e.g., memcached), RAID, file systems, SMTP, POP3, Apache, networking and network data formats, and many others. He is an expert in scalability, performance, and system reliability.
