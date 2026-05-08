---
title: Percona XtraBackup – A workaround to the failed assertion bug
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtrabackup-a-workaround-for-failed-assertion-bug/
  post_id: 7540
source_author:
  name: Paul Namuag
  slug: paul-namuag
  url: https://www.percona.com/blog/author/paul-namuag/
  website: ''
published_at: '2013-11-27T11:00:09'
published_at_gmt: '2013-11-27T11:00:09'
modified_at: '2026-05-04T22:12:15'
modified_at_gmt: '2026-05-04T22:12:15'
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
- tag:percona-xtrabackup:330
categories:
- MySQL
- Percona Services
- Percona Software
category_slugs:
- mysql
- percona-services
- percona-software
tags:
- failed assertion bug
- Percona XtraBackup
- workaround
tag_slugs:
- failed-assertion-bug
- percona-xtrabackup
- workaround
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraBackup – A workaround to the failed assertion bug

Source: [Percona Blog](https://www.percona.com/blog/percona-xtrabackup-a-workaround-for-failed-assertion-bug/)

Auteur source: [Paul Namuag](https://www.percona.com/blog/author/paul-namuag/)

Publication: 2013-11-27T11:00:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently conducted a test backup of my “master-slave” setup in my VirtualBox as I was migrating from Percona Server 5.6.12 to version 5.6.13-rel61.0 with Percona XtraBackup v2.2.0 rev. 4885. However, doing the backup on my slave, I encountered this problem: Shell [04] Compressing and streaming ./test/checksum.ibd [01] Compressing and streaming ./mysql/slave_master_info.ibd Assertion "to_read % cursor->page_size == 0" failed at fil_cur.cc:293 innobackupex: Error: The xtrabackup child process has died at /usr/bin/innobackupex line 2641. 1 2 3 4 [ 04 ] Compressing and streaming . / test / checksum .ibd [ 01 ] Compressing and streaming . / mysql / slave_master_info .ibd Assertion "to_read % cursor->page_size == 0" failed at fil_cur .cc : 293 innobackupex : Error : The xtrabackup child process has died at / usr / bin / innobackupex line 2641. This is related to a bug posted by my colleag...

## Auteur source

Paul Namuag is a Support Engineer at Percona. Prior from joining Percona, he works as Software Developer/Software Engineer and has extensive knowledge in various programming languages. He likes to play with his guitar, jogging, play basketball, go to church, read books, and play with his dog Nadine.
