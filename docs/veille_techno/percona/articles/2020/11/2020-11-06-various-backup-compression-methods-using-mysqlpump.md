---
title: Various Backup Compression Methods Using Mysqlpump
source:
  name: Percona Blog
  url: https://www.percona.com/blog/various-backup-compression-methods-using-mysqlpump/
  post_id: 23441
source_author:
  name: Mani Paluru
  slug: mani-paluru
  url: https://www.percona.com/blog/author/mani-paluru/
  website: ''
published_at: '2020-11-06T14:49:20'
published_at_gmt: '2020-11-06T14:49:20'
modified_at: '2026-05-05T16:34:35'
modified_at_gmt: '2026-05-05T16:34:35'
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
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- backup
- insight for DBAs
- insight for developers
- MySQL
- mysql-and-variants
tag_slugs:
- backup
- insight-for-dbas
- insight-for-developers
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Backup-Compression-Methods-Using-Mysqlpump.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Various Backup Compression Methods Using Mysqlpump

Source: [Percona Blog](https://www.percona.com/blog/various-backup-compression-methods-using-mysqlpump/)

Auteur source: [Mani Paluru](https://www.percona.com/blog/author/mani-paluru/)

Publication: 2020-11-06T14:49:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Mysqlpump is a client program that was released with MySQL 5.7.8 and is used to perform logical backups in a better way. Mysqlpump supports parallelism and it has the capability of creating compressed output. Pablo already wrote a blog about this utility (The mysqlpump Utility), and in this blog, I am going to explore the … Continued

## Structure detectee

- H2: Overview
- H2: Lab Setup
- H2: Compression with Lz4
- H2: Compression with Zlib
- H2: How to Decompress the Backup

## Images et graphiques reperes

- featured / image: [Various Backup Compression Methods Using Mysqlpump](https://www.percona.com/wp-content/uploads/2026/03/Backup-Compression-Methods-Using-Mysqlpump.png)
- content / image: [Backup Compression Methods Using Mysqlpump](https://www.percona.com/wp-content/uploads/2026/03/Backup-Compression-Methods-Using-Mysqlpump-300x168.png)
- content / image: [Screenshot-2020-11-05-at-11.10.11-AM-1024x669.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2020-11-05-at-11.10.11-AM-1024x669.png)
- content / image: [Screenshot-2020-11-05-at-11.10.53-AM-1024x661.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2020-11-05-at-11.10.53-AM-1024x661.png)

## Auteur source

Mani Works as DBA for Percona in Managed services team, He has several years of experience in managing multiple Mysql client companies.
