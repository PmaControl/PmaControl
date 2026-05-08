---
title: 'Lock, Stock and MySQL Backups: Data Guaranteed Webinar Follow Up Questions'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/lock-stock-and-mysql-backups-data-guaranteed-webinar-follow-up-questions/
  post_id: 17317
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2017-09-14T22:35:13'
published_at_gmt: '2017-09-14T22:35:13'
modified_at: '2026-03-20T21:30:06'
modified_at_gmt: '2026-03-20T21:30:06'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
- Webinars
category_slugs:
- insight-for-dbas
- mysql
- webinars
tags:
- Backups
- MySQL
- Recovery
- webinar
tag_slugs:
- backups
- mysql
- recovery
- webinar
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/harddrive-1348504_640-e1505242174762.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Lock, Stock and MySQL Backups: Data Guaranteed Webinar Follow Up Questions

Source: [Percona Blog](https://www.percona.com/blog/lock-stock-and-mysql-backups-data-guaranteed-webinar-follow-up-questions/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2017-09-14T22:35:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Hello again! On August 16, we delivered a webinar on MySQL backups. As always, we’ve had a number of interesting questions. Some of them we’ve answered on the webinar, but we’d like to share some of them here in writing. What is the best way to maintain daily full backups, but selective restores omitting certain … Continued

## Structure detectee

- H3: What is the best way to maintain daily full backups, but selective restores omitting certain archive tables?
- H3: Can you recommend a good script on github for mysqlbinlog backup?
- H3: mysqlbinlog can stream binary logs to a remote server. Doesn’t simply copying the binlog to the remote location just as affective. Especially if done frequently using a cronjob that runs rsync?
- H3: How is possible to create a backup using xtrabackup compressed directly to a volume with low capacity? Considering that is needed to use –apply-log step.
- H3: How can you keep connection credentials secure for automated backup?
- H3: I missed the name of your github repo. Also for mysqlbinlog parsing? (same question)
- H3: Which one is faster between mydumper and 5.7 mysqlpump?
- H3: If we wanted to migrate a 2.5TB database over a VPN connection, which backup and restore method would you recommend? The method would need to be resilient. This would be for migrating an on premise db to a MySQL RDS instance at AWS.
- H3: What about if I have 1TB of data to backup and restore to a new server, how much time does it take, can we restore/stream at the same time while taking a backup?
- H3: Is mydumper your product, and how fast will it take to backup a few millions of data?
- H3: Will it lock my table during the process? How to restore the mydumper?

## Images et graphiques reperes

- featured / image: [Lock, Stock and MySQL Backups: Data Guaranteed Webinar Follow Up Questions](https://www.percona.com/wp-content/uploads/2026/03/harddrive-1348504_640-e1505242174762.jpg)
- content / image: [MySQL Backups](https://www.percona.com/wp-content/uploads/2026/03/harddrive-1348504_640.jpg)

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
