---
title: Increase the Ability to Securely Replicate Your Data and Restrict Replication To Row-based Events in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/increase-the-ability-to-securely-replicate-your-data-and-restrict-replication-to-row-based-events-in-mysql/
  post_id: 26757
source_author:
  name: Gaurav Pareek
  slug: gaurav-pareek
  url: https://www.percona.com/blog/author/gaurav-pareek/
  website: ''
published_at: '2023-03-21T13:25:17'
published_at_gmt: '2023-03-21T13:25:17'
modified_at: '2026-03-26T20:29:59'
modified_at_gmt: '2026-03-26T20:29:59'
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
tags:
- MySQL
- mysql-and-variants
- security
tag_slugs:
- mysql
- mysql-and-variants
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_database_monitoring_blue_navy_colored_texture_35588dbb-cc91-4a19-bb33-743dccb4b525.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Increase the Ability to Securely Replicate Your Data and Restrict Replication To Row-based Events in MySQL

Source: [Percona Blog](https://www.percona.com/blog/increase-the-ability-to-securely-replicate-your-data-and-restrict-replication-to-row-based-events-in-mysql/)

Auteur source: [Gaurav Pareek](https://www.percona.com/blog/author/gaurav-pareek/)

Publication: 2023-03-21T13:25:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I’ll discuss the use case for replication. We want to improve our ability to replicate your data and limit replication to row-based events securely, wherein we do not have control over the source(s). The replica doesn’t have checking capabilities when processing replicated transactions as of MySQL 8.0.18. It does this to carry … Continued

## Structure detectee

- H2: Configure user on replica
- H3: Limitation
- H2: Configure REQUIRE_ROW_FORMAT
- H2: Summary

## Images et graphiques reperes

- featured / image: [Increase the Ability to Securely Replicate Your Data and Restrict Replication To Row-based Events in MySQL](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_database_monitoring_blue_navy_colored_texture_35588dbb-cc91-4a19-bb33-743dccb4b525.png)

## Auteur source

Gaurav has worked as a DBA for more than 7 years, he has worked in healthcare and finance. He likes cricket, travelling and trekking. He Joined percona in 2021.
