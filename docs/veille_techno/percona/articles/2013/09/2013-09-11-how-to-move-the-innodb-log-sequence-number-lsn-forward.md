---
title: How to move the InnoDB log sequence number (LSN) forward
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-move-the-innodb-log-sequence-number-lsn-forward/
  post_id: 7293
source_author:
  name: Nickolay Ihalainen
  slug: nickolay-ihalainen
  url: https://www.percona.com/blog/author/nickolay-ihalainen/
  website: ''
published_at: '2013-09-11T05:00:53'
published_at_gmt: '2013-09-11T05:00:53'
modified_at: '2026-03-25T17:06:40'
modified_at_gmt: '2026-03-25T17:06:40'
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
- InnoDB
- log sequence number
tag_slugs:
- innodb
- log-sequence-number
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/InnoDB_LSN.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to move the InnoDB log sequence number (LSN) forward

Source: [Percona Blog](https://www.percona.com/blog/how-to-move-the-innodb-log-sequence-number-lsn-forward/)

Auteur source: [Nickolay Ihalainen](https://www.percona.com/blog/author/nickolay-ihalainen/)

Publication: 2013-09-11T05:00:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post focuses on the problem of the InnoDB log sequence number being in the future. Preface: What is an InnoDB log sequence number? The log sequence number (LSN) is an important database parameter used by InnoDB in many places. The most important use is for crash recovery and buffer pool purge control. Internally, the InnoDB … Continued

## Structure detectee

- H4: Preface: What is an InnoDB log sequence number?
- H4: Now for the problem: LSN being in the future!
- H4: The solution: some methods to change the LSN
- H4: Possible issues: How to avoid database corruption after you change the LSN
- H4: How could corruption happen to start with?

## Images et graphiques reperes

- featured / image: [How to move the InnoDB log sequence number (LSN) forward](https://www.percona.com/wp-content/uploads/2026/03/InnoDB_LSN.jpg)

## Auteur source

Nickolay joined Percona in December 2010, after working for several years at what is now the most popular cinema site in Russia. During the time he was there, Nickolay and a small team of developers were responsible for scaling the site into one which now serves over a million unique visitors per day. Prior to that, he worked for several other companies, including NetUp, which provides ISP billing and IPTV solutions, and eHouse, the oldest Russian e-commerce company. Nickolay has a great deal of experience in both systems administration and programming. His experience includes extensive hands-on work with a broad range of technologies, including SQL, MySQL, PHP, C, C++, Python, Java, XML, OS parameter tuning (Linux, Solaris), caching techniques (e.g., memcached), RAID, file systems, SMTP, POP3, Apache, networking and network data formats, and many others. He is an expert in scalability, performance, and system reliability.
