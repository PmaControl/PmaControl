---
title: 'High Availability with MySQL Fabric: Part I | Percona'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/high-availability-mysql-fabric-part/
  post_id: 8052
source_author:
  name: Martin Arrieta
  slug: martin-arrieta
  url: https://www.percona.com/blog/author/martin-arrieta/
  website: ''
published_at: '2014-05-15T13:00:25'
published_at_gmt: '2014-05-15T13:00:25'
modified_at: '2026-05-04T22:17:42'
modified_at_gmt: '2026-05-04T22:17:42'
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
- DB
- Fernando Ipar
- High Availability
- Martin Arrieta
- MySQL Fabric
- Replication
tag_slugs:
- db
- fernando-ipar
- high-availability
- martin-arrieta
- mysql-fabric
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Blog-post-02-HA-part-1.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# High Availability with MySQL Fabric: Part I | Percona

Source: [Percona Blog](https://www.percona.com/blog/high-availability-mysql-fabric-part/)

Auteur source: [Martin Arrieta](https://www.percona.com/blog/author/martin-arrieta/)

Publication: 2014-05-15T13:00:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In our previous post, we introduced the MySQL Fabric utility and said we would dig deeper into it. This post is the first part of our test of MySQL Fabric’s High Availability (HA) functionality. Today, we’ll review MySQL Fabric’s HA concepts, and then walk you through the setup of a 3-node cluster with one Primary and two … Continued

## Structure detectee

- H2: Our lab
- H2: Set up
- H3: MySQL configuration
- H2: Creating a High Availability Cluster:
- H3: Creating a group
- H3: Add the servers to the group
- H3: Promote a node as a master
- H3: Failure detection
- H3: What’s next

## Images et graphiques reperes

- featured / image: [High Availability with MySQL Fabric: Part I | Percona](https://www.percona.com/wp-content/uploads/2026/03/Blog-post-02-HA-part-1.png)
- content / image: [Fabric Lab](https://www.percona.com/wp-content/uploads/2026/03/Fabric-Lab.png)

## Auteur source

Martin joined Percona in January 2012. He has been using Linux and open source technologies since 1999. Martin has worked with Apache, DNS's, mail servers, iptables and MySQL servers.
