---
title: Deadlock Troubleshooting in Percona Server for MySQL 5.7
source:
  name: Percona Blog
  url: https://www.percona.com/blog/deadlock-troubleshooting-in-percona-server-for-mysql-5-7/
  post_id: 22922
source_author:
  name: Carlos Tutte
  slug: carlos-tutte
  url: https://www.percona.com/blog/author/carlos-tutte/
  website: ''
published_at: '2020-08-12T18:54:56'
published_at_gmt: '2020-08-12T18:54:56'
modified_at: '2026-04-27T22:12:16'
modified_at_gmt: '2026-04-27T22:12:16'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
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
- insight for DBAs
- insight for developers
- MySQL
- mysql-and-variants
- Percona Server for MySQL
- Percona Software
tag_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- mysql-and-variants
- percona-server
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Deadlock-Troubleshooting-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Deadlock Troubleshooting in Percona Server for MySQL 5.7

Source: [Percona Blog](https://www.percona.com/blog/deadlock-troubleshooting-in-percona-server-for-mysql-5-7/)

Auteur source: [Carlos Tutte](https://www.percona.com/blog/author/carlos-tutte/)

Publication: 2020-08-12T18:54:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Disclaimer: the following script only works for Percona Server for MySQL 5.7, and relies on enabling performance schema (PS) instrumentation which can add overhead on high concurrent systems, and is not intended for continuous production usage as it’s a POC (proof of concept). Introduction In Percona Support, we frequently receive tickets related to deadlocks and … Continued

## Structure detectee

- H3: Introduction
- H2: Installation
- H2: Generating a Deadlock
- H2: Limitations of the Script
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Deadlock Troubleshooting in Percona Server for MySQL 5.7](https://www.percona.com/wp-content/uploads/2026/03/Deadlock-Troubleshooting-MySQL.png)
- content / image: [Deadlock Troubleshooting MySQL](https://www.percona.com/wp-content/uploads/2026/03/Deadlock-Troubleshooting-MySQL-300x157.png)

## Auteur source

Computer engineer from Montevideo, Uruguay, joined Percona on February 2018, first as a support engineer, then moving to the consulting team. Working in complex IT solutions for more than 10 years, Carlos now specializes in MySQL and related technologies
