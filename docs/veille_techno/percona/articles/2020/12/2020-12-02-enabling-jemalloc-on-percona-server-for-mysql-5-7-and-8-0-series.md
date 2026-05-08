---
title: Enabling jemalloc on Percona Server for MySQL 5.7 and 8.0 Series
source:
  name: Percona Blog
  url: https://www.percona.com/blog/enabling-jemalloc-on-percona-server-for-mysql-5-7-and-8-0-series/
  post_id: 23522
source_author:
  name: Vinicius Grippa
  slug: vinicius-grippa
  url: https://www.percona.com/blog/author/vinicius-grippa/
  website: ''
published_at: '2020-12-02T14:55:43'
published_at_gmt: '2020-12-02T14:55:43'
modified_at: '2026-04-27T22:19:26'
modified_at_gmt: '2026-04-27T22:19:26'
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
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- insight for DBAs
- MySQL
- mysql-and-variants
- Percona Software
tag_slugs:
- insight-for-dbas
- mysql
- mysql-and-variants
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/jemalloc-on-Percona-Server-for-MySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Enabling jemalloc on Percona Server for MySQL 5.7 and 8.0 Series

Source: [Percona Blog](https://www.percona.com/blog/enabling-jemalloc-on-percona-server-for-mysql-5-7-and-8-0-series/)

Auteur source: [Vinicius Grippa](https://www.percona.com/blog/author/vinicius-grippa/)

Publication: 2020-12-02T14:55:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The benefits of jemalloc versus glibc memory allocator for use with MySQL have been widely discussed. With jemalloc (along with Transparent Huge Pages disabled) there is less memory fragmentation, and thus more efficient resource management of the server memory. For MySQL 5.6, installing jemalloc is enough to enable it when starting the MySQL process. However, … Continued

## Structure detectee

- H3: Enabling jemalloc on Percona Server for MySQL
- H3: Using systemd Services ( systemctl Command)
- H3: Starting Manually
- H3: Check if MySQL is Using jemalloc
- H3: Conclusion
- H4: Useful Resources

## Images et graphiques reperes

- featured / image: [Enabling jemalloc on Percona Server for MySQL 5.7 and 8.0 Series](https://www.percona.com/wp-content/uploads/2026/03/jemalloc-on-Percona-Server-for-MySQL.png)
- content / image: [jemalloc on Percona Server for MySQL](https://www.percona.com/wp-content/uploads/2026/03/jemalloc-on-Percona-Server-for-MySQL-300x168.png)

## Auteur source

Vinicius Grippa is a Lead Database Engineer at Percona, an Oracle ACE Director, MySQL Rockstar, and co-author of Learning MySQL. With a Bachelor’s degree in Computer Science and 18 years of experience, he specializes in designing databases for mission-critical applications, focusing on MySQL and MongoDB ecosystems. As part of Percona’s Support team, he has assisted customers in resolving complex database challenges across a wide range of scenarios. An active member of the open-source community, he leads the MySQL User Group in Brazil and engages in knowledge sharing through Slack, Meetups, and international conferences, including FOSDEM, Percona Live, and events across Europe, Asia, and the Americas.
