---
title: 'Don’t Trust, Verify: How MyDumper’s Checksums Validates Data Consistency'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/dont-trust-verify-how-mydumpers-checksums-validates-data-consistency/
  post_id: 35206
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2025-08-25T14:01:27'
published_at_gmt: '2025-08-25T14:01:27'
modified_at: '2026-03-26T20:25:19'
modified_at_gmt: '2026-03-26T20:25:19'
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
category_slugs:
- insight-for-dbas
- mysql
tags:
- mydumper
- MySQL
- mysql-and-variants
tag_slugs:
- mydumper
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MyDumpers-Checksums-Validates-Data-Consistency.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Don’t Trust, Verify: How MyDumper’s Checksums Validates Data Consistency

Source: [Percona Blog](https://www.percona.com/blog/dont-trust-verify-how-mydumpers-checksums-validates-data-consistency/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2025-08-25T14:01:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

How do you know if your backup is truly reliable? The last thing you want is to discover your data is corrupted during a critical restore or during a migration. While MyDumper is a powerful tool for logical backups, its -M option takes backup integrity to the next level by creating checksums. This often-overlooked feature … Continued

## Structure detectee

- H2: Do you test your backups?
- H2: mydumper checksum options
- H2: myloader checksum options
- H2: Is there any downside?
- H2: When is it useful?

## Images et graphiques reperes

- featured / image: [Don’t Trust, Verify: How MyDumper’s Checksums Validates Data Consistency](https://www.percona.com/wp-content/uploads/2026/03/MyDumpers-Checksums-Validates-Data-Consistency.jpg)
- content / image: [MySQL-Vector-Search-Survey.png](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Vector-Search-Survey.png)
- content / image: [mysql-performance-tuning-1-3.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1-3.png)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
