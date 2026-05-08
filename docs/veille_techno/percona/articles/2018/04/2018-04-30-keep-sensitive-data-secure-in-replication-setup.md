---
title: Keep Sensitive Data Secure in a Replication Setup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/keep-sensitive-data-secure-in-replication-setup/
  post_id: 18481
source_author:
  name: Nickolay Ihalainen
  slug: nickolay-ihalainen
  url: https://www.percona.com/blog/author/nickolay-ihalainen/
  website: ''
published_at: '2018-04-30T21:47:29'
published_at_gmt: '2018-04-30T21:47:29'
modified_at: '2026-05-05T19:11:09'
modified_at_gmt: '2026-05-05T19:11:09'
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
- Security
category_slugs:
- insight-for-dbas
- mysql
- security
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/keep-sensitive-data-secure-e1525124703983.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Keep Sensitive Data Secure in a Replication Setup

Source: [Percona Blog](https://www.percona.com/blog/keep-sensitive-data-secure-in-replication-setup/)

Auteur source: [Nickolay Ihalainen](https://www.percona.com/blog/author/nickolay-ihalainen/)

Publication: 2018-04-30T21:47:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post describes how to keep sensitive data secure on slave servers in a MySQL async replication setup. Almost every web application has a sensitive data: passwords, SNN, credit cards, emails, etc. Splitting the database to secure and “public” parts allows for restricting user and application parts access to sensitive data.

## Structure detectee

- H3: Field encryption
- H4: Field encryption example
- H4: Summary
- H3: Replication filters
- H4: Master-side
- H4: Summary

## Images et graphiques reperes

- featured / image: [Keep Sensitive Data Secure in a Replication Setup](https://www.percona.com/wp-content/uploads/2026/03/keep-sensitive-data-secure-e1525124703983.jpg)
- content / image: [Keep sensitive data secure](https://www.percona.com/wp-content/uploads/2026/03/keep-sensitive-data-secure-300x200.jpg)

## Auteur source

Nickolay joined Percona in December 2010, after working for several years at what is now the most popular cinema site in Russia. During the time he was there, Nickolay and a small team of developers were responsible for scaling the site into one which now serves over a million unique visitors per day. Prior to that, he worked for several other companies, including NetUp, which provides ISP billing and IPTV solutions, and eHouse, the oldest Russian e-commerce company. Nickolay has a great deal of experience in both systems administration and programming. His experience includes extensive hands-on work with a broad range of technologies, including SQL, MySQL, PHP, C, C++, Python, Java, XML, OS parameter tuning (Linux, Solaris), caching techniques (e.g., memcached), RAID, file systems, SMTP, POP3, Apache, networking and network data formats, and many others. He is an expert in scalability, performance, and system reliability.
