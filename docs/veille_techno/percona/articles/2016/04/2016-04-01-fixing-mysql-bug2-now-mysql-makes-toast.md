---
title: 'Fixing MySQL Bug#2: now MySQL makes toast!'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/fixing-mysql-bug2-now-mysql-makes-toast/
  post_id: 14863
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2016-04-01T14:15:14'
published_at_gmt: '2016-04-01T14:15:14'
modified_at: '2026-05-05T18:03:46'
modified_at_gmt: '2026-05-05T18:03:46'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- MySQL makes toast
tag_slugs:
- mysql-makes-toast
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/bug_2_fix_pi.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Fixing MySQL Bug#2: now MySQL makes toast!

Source: [Percona Blog](https://www.percona.com/blog/fixing-mysql-bug2-now-mysql-makes-toast/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2016-04-01T14:15:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Historical MySQL Bug#2, opened 12 Sep 2002, states that MySQL Connector/J doesn’t make toast. It hasn’t been fixed for more than 14 years. I’ve finally created a patch for it. First of all: why only fix this for MySQL Connector/J? We should make sure the server can do this for any implementation! With this fix, … Continued

## Images et graphiques reperes

- featured / image: [Fixing MySQL Bug#2: now MySQL makes toast!](https://www.percona.com/wp-content/uploads/2026/03/bug_2_fix_pi.jpg)
- content / image: [bug_2_fix_pi-300x242.jpg](https://www.percona.com/wp-content/uploads/2026/03/bug_2_fix_pi-300x242.jpg)
- content / image: [MySQL makes toast](https://www.percona.com/wp-content/uploads/2026/03/bug2_fixed-1024x838.jpg)
- content / image: [MySQL makes toast](https://www.percona.com/wp-content/uploads/2026/03/bug_2_fix_pi_wiring-221x300.jpg)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
