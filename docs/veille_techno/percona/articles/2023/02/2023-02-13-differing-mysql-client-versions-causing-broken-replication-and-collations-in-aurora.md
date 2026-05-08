---
title: Differing MySQL Client Versions Causing Broken Replication and Collations in Aurora
source:
  name: Percona Blog
  url: https://www.percona.com/blog/differing-mysql-client-versions-causing-broken-replication-and-collations-in-aurora/
  post_id: 26550
source_author:
  name: Peter Sylvester
  slug: peter-sylvester
  url: https://www.percona.com/blog/author/peter-sylvester/
  website: ''
published_at: '2023-02-13T14:30:12'
published_at_gmt: '2023-02-13T14:30:12'
modified_at: '2026-03-26T20:30:12'
modified_at_gmt: '2026-03-26T20:30:12'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- AWS
- cloud
- MySQL
- mysql-and-variants
tag_slugs:
- aws
- cloud
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Differing-MySQL-Client-Versions-Causing-Broken-Replication.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Differing MySQL Client Versions Causing Broken Replication and Collations in Aurora

Source: [Percona Blog](https://www.percona.com/blog/differing-mysql-client-versions-causing-broken-replication-and-collations-in-aurora/)

Auteur source: [Peter Sylvester](https://www.percona.com/blog/author/peter-sylvester/)

Publication: 2023-02-13T14:30:12

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, I was working with my colleagues Edwin Wang and Taras Onishchuk and found an interesting edge case involving a situation where a replica running Percona Server for MySQL 5.7, external to AWS Aurora instance version 2.10.2 (5.7-compatible), broke. I recreated the issue in my lab with a simple create database statement, as you will … Continued

## Images et graphiques reperes

- featured / image: [Differing MySQL Client Versions Causing Broken Replication and Collations in Aurora](https://www.percona.com/wp-content/uploads/2026/03/Differing-MySQL-Client-Versions-Causing-Broken-Replication.jpg)

## Auteur source

Peter Sylvester is one of the Senior MySQL Database Administrators within Percona's managed services team and has been with Percona since October of 2021.
