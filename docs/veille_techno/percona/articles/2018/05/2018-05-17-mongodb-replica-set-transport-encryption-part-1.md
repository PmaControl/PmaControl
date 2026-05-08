---
title: Deploy a MongoDB Replica Set with Transport Encryption (Part 1)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-replica-set-transport-encryption-part-1/
  post_id: 18663
source_author:
  name: Corrado Pandiani
  slug: corrado-pandiani
  url: https://www.percona.com/blog/author/corrado-pandiani/
  website: ''
published_at: '2018-05-17T19:32:27'
published_at_gmt: '2018-05-17T19:32:27'
modified_at: '2026-03-26T20:19:12'
modified_at_gmt: '2026-03-26T20:19:12'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- ProxySQL
matched_filters:
- search:proxysql
categories:
- Insight for DBAs
- Insight for Developers
- MongoDB
- Security
category_slugs:
- insight-for-dbas
- insight-for-developers
- mongodb
- security
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/replicaset3.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Deploy a MongoDB Replica Set with Transport Encryption (Part 1)

Source: [Percona Blog](https://www.percona.com/blog/mongodb-replica-set-transport-encryption-part-1/)

Auteur source: [Corrado Pandiani](https://www.percona.com/blog/author/corrado-pandiani/)

Publication: 2018-05-17T19:32:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this article series, we will talk about the basic high availability architecture of a MongoDB: the MongoDB replica set. We’ll cover it in three parts: Part 1 (this post): We’ll introduce basic replica set concepts, how it works and what its main features Part 2: We’ll provide a step-by-step guide to configure a three-node … Continued

## Structure detectee

- H3: What is a Replica Set
- H3: How a Replica Set works
- H3: Arbiter node
- H3: Priority
- H3: Hidden members
- H3: Delayed member
- H3: Next

## Images et graphiques reperes

- featured / image: [Deploy a MongoDB Replica Set with Transport Encryption (Part 1)](https://www.percona.com/wp-content/uploads/2026/03/replicaset3.png)
- content / image: [replicaset1.png](https://www.percona.com/wp-content/uploads/2026/03/replicaset1.png)
- content / image: [replicaset2.png](https://www.percona.com/wp-content/uploads/2026/03/replicaset2.png)
- content / image: [replicaset4.png](https://www.percona.com/wp-content/uploads/2026/03/replicaset4.png)
- content / image: [replicaset5.png](https://www.percona.com/wp-content/uploads/2026/03/replicaset5.png)

## Auteur source

Prior to joining Percona as a Senior Consultant, Corrado spent more than 20 years in developing web sites and designing and administering MySQL. He is a MySQL enthusiast since version 3.23 and his skills are focused on performances and architectural design. He's also a trainer and a MongoDB consultant.
