---
title: Debugging MariaDB Galera Cluster SST Problems – A Tale of a Funny Experience
source:
  name: Percona Blog
  url: https://www.percona.com/blog/debugging-mariadb-galera-cluster-sst-problems-a-tale-of-a-funny-experience/
  post_id: 19912
source_author:
  name: Francisco Bordenave
  slug: francisco-bordenave
  url: https://www.percona.com/blog/author/francisco-bordenave/
  website: ''
published_at: '2019-02-12T13:25:41'
published_at_gmt: '2019-02-12T13:25:41'
modified_at: '2026-04-27T21:10:52'
modified_at_gmt: '2026-04-27T21:10:52'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
- XtraBackup
matched_filters:
- category:mariadb:1281
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- MariaDB
- MySQL
category_slugs:
- mariadb
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MariaDB-galera-cluster-starting-time.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Debugging MariaDB Galera Cluster SST Problems – A Tale of a Funny Experience

Source: [Percona Blog](https://www.percona.com/blog/debugging-mariadb-galera-cluster-sst-problems-a-tale-of-a-funny-experience/)

Auteur source: [Francisco Bordenave](https://www.percona.com/blog/author/francisco-bordenave/)

Publication: 2019-02-12T13:25:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, I had to work on an emergency for a customer who was having a problem restarting a MariaDB Galera Cluster. After a failure in the cluster they decided to restart the cluster entirely following the right path: bootstrapping the first node, and then adding the rest of the members, one by one. Everything went … Continued

## Structure detectee

- H3: Identifying the issue…
- H4: MariaDB Cluster dies in the SST process after 90 seconds
- H3: On reflection…

## Images et graphiques reperes

- featured / image: [Debugging MariaDB Galera Cluster SST Problems – A Tale of a Funny Experience](https://www.percona.com/wp-content/uploads/2026/03/MariaDB-galera-cluster-starting-time.jpg)
- content / image: [MariaDB galera cluster starting time](https://www.percona.com/wp-content/uploads/2026/03/MariaDB-galera-cluster-starting-time-300x201.jpg)

## Auteur source

Francisco has been working in MySQL since 2006, he has worked for several companies which includes Health Care industry to Gaming. Over the last 6 years he has been working as a Remote DBA and Database Consultant which help him to acquire a lot of technical and multi-cultural skills. He lives in La Plata, Argentina and during his free time he likes to play football, spent time with family and friends and cook.
