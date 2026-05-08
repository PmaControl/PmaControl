---
title: Log rotate and the (deleted) MySQL log file mystery
source:
  name: Percona Blog
  url: https://www.percona.com/blog/log-rotate-and-the-deleted-mysql-log-file-mystery/
  post_id: 8717
source_author:
  name: Daniel Guzmán Burgos
  slug: daniel-guzman-burgos
  url: https://www.percona.com/blog/author/daniel-guzman-burgos/
  website: ''
published_at: '2014-11-12T15:41:48'
published_at_gmt: '2014-11-12T15:41:48'
modified_at: '2026-05-04T20:57:18'
modified_at_gmt: '2026-05-04T20:57:18'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
matched_filters:
- category:mariadb:1281
- category:mysql:83
categories:
- Insight for DBAs
- MariaDB
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mariadb
- mysql
- percona-software
tags:
- cron
- Daniel Guzmán Burgos
- Linux
- Logrotate
- MySQL logs
- percona monitoring plugins
- Percona Nagios Plugin
- Primary
tag_slugs:
- cron
- daniel-guzman-burgos
- linux
- logrotate
- mysql-logs
- percona-monitoring-plugins
- percona-nagios-plugin
- primary
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/IMG_1097.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Log rotate and the (deleted) MySQL log file mystery

Source: [Percona Blog](https://www.percona.com/blog/log-rotate-and-the-deleted-mysql-log-file-mystery/)

Auteur source: [Daniel Guzmán Burgos](https://www.percona.com/blog/author/daniel-guzman-burgos/)

Publication: 2014-11-12T15:41:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Did your logging stop working after you set up logrotate? Then this post might be for you. Some time ago, Peter Boros wrote about Rotating MySQL Slow Logs safely, explaining the steps of a “best practice” log rotate/archive. This post will add more info about the topic. When running logrotate for MySQL (after proper setting … Continued

## Structure detectee

- H2: Did your logging stop working after you set up logrotate? Then this post might be for you.
- H2: The situation:
- H2: Why did this happen?
- H2: So where is it? How can I find it again?
- H2: Can I recover the file contents?
- H2: How did this happen?
- H2: What is the solution?
- H2: Can I get an alert if this happens to me?

## Images et graphiques reperes

- featured / image: [Log rotate and the (deleted) MySQL log file mystery](https://www.percona.com/wp-content/uploads/2026/03/IMG_1097.jpg)
- content / image: [Archive](https://www.percona.com/wp-content/uploads/2026/03/IMG_1097-300x300.jpg)
  Caption: Archive your log files!

## Auteur source

Daniel studied Electronic Engineering, but quickly becomes interested in all data things. He has worked as a DBA since 2007 for several companies. Working for Percona since 2014, he is the PMM Tech Lead
