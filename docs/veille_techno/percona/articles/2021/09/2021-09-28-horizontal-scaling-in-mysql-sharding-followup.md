---
title: Horizontal Scaling in MySQL – Sharding Followup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/horizontal-scaling-in-mysql-sharding-followup/
  post_id: 24874
source_author:
  name: Mike Benshoof
  slug: mbenshoof
  url: https://www.percona.com/blog/author/mbenshoof/
  website: ''
published_at: '2021-09-28T13:51:58'
published_at_gmt: '2021-09-28T13:51:58'
modified_at: '2026-05-05T22:45:39'
modified_at_gmt: '2026-05-05T22:45:39'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- insight for DBAs
- MySQL
- mysql-and-variants
- sharding
tag_slugs:
- insight-for-dbas
- mysql
- mysql-and-variants
- sharding
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Horizontal-Scaling-in-MySQL-Sharding.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Horizontal Scaling in MySQL – Sharding Followup

Source: [Percona Blog](https://www.percona.com/blog/horizontal-scaling-in-mysql-sharding-followup/)

Auteur source: [Mike Benshoof](https://www.percona.com/blog/author/mbenshoof/)

Publication: 2021-09-28T13:51:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In a previous post, A Horizontal Scalability Mindset for MySQL, I discussed the concerns around growing individual MySQL instances too large and some basic strategies: Optimizing/minimizing size with proper data types Removing unused/duplicate indexes Keeping your Primary Keys small Pruning data Finally, if those methods have been exhausted, I touched on horizontal sharding as the … Continued

## Structure detectee

- H2: What is Sharding?
- H2: Sharding Considerations and Challenges
- H2: How Do I Split My Data?
- H2: What if My Data Spans Shards?
- H2: How Do I Find My Data?
- H3: Finding the ShardID
- H3: Connecting to the Shard
- H2: Sample ProxySQL Implementation

## Images et graphiques reperes

- featured / image: [Horizontal Scaling in MySQL – Sharding Followup](https://www.percona.com/wp-content/uploads/2026/03/Horizontal-Scaling-in-MySQL-Sharding.png)
- content / image: [Horizontal Scaling in MySQL Sharding](https://www.percona.com/wp-content/uploads/2026/03/Horizontal-Scaling-in-MySQL-Sharding-300x168.png)

## Auteur source

Michael joined Percona in 2012 as a US based consultant and is currently a Technical Account Manager. Prior to joining Percona, Michael spent several years in a DevOps role maintaining a SaaS application specializing in social networking. His experiences include application development and scaling, systems administration, along with database administration and design. He enjoys designing extensible and flexible solutions to problems. When not working, he enjoys time outdoors, grilling, most sports, and spending time with the family.
