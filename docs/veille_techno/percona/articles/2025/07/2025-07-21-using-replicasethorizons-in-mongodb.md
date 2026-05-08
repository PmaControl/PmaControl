---
title: Using replicaSetHorizons in MongoDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-replicasethorizons-in-mongodb/
  post_id: 35119
source_author:
  name: Ivan Groenewold
  slug: ivan-groenewold
  url: https://www.percona.com/blog/author/ivan-groenewold/
  website: ''
published_at: '2025-07-21T15:32:04'
published_at_gmt: '2025-07-21T15:32:04'
modified_at: '2026-03-26T20:13:26'
modified_at_gmt: '2026-03-26T20:13:26'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- Percona Toolkit
matched_filters:
- search:percona-toolkit
categories:
- Cloud
- Insight for DBAs
- MongoDB
category_slugs:
- cloud
- insight-for-dbas
- mongodb
tags:
- Docker
- Kubernetes
- MongoDB
tag_slugs:
- docker
- kubernetes
- mongodb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Using-replicaSetHorizons-in-MongoDB.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using replicaSetHorizons in MongoDB

Source: [Percona Blog](https://www.percona.com/blog/using-replicasethorizons-in-mongodb/)

Auteur source: [Ivan Groenewold](https://www.percona.com/blog/author/ivan-groenewold/)

Publication: 2025-07-21T15:32:04

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When running MongoDB replica sets in containerized environments like Docker or Kubernetes, making nodes reachable from inside the cluster as well as from external clients can be a challenge. To solve this problem, this post will explain the Horizons feature of Percona Server for MongoDB. Let’s start by looking at what happens behind the scenes … Continued

## Structure detectee

- H2: Node auto-discovery
- H2: The node identity crisis
- H2: The port issue
- H2: What is Horizons?
- H2: Example scenario: MongoDB Replica Set in Docker
- H3: Step 1: Get your certificates ready
- H3: Step 2: Docker compose setup
- H3: Step 3: Initiate the replica set with Horizons
- H3: Step 4: Connect from inside Docker
- H3: Step 5: Connect from outside Docker
- H3: Step 6: Check the identities returned
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Using replicaSetHorizons in MongoDB](https://www.percona.com/wp-content/uploads/2026/03/Using-replicaSetHorizons-in-MongoDB.jpg)
- content / image: [Screenshot-2025-07-18-at-12.45.35-PM.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2025-07-18-at-12.45.35-PM.png)
- content / image: [Switch-to-percona-for-mongodb.jpg](https://www.percona.com/wp-content/uploads/2026/03/Switch-to-percona-for-mongodb.jpg)

## Auteur source

Passionate about technology, Ivan Groenewold is a seasoned professional with extensive experience in database management, cloud infrastructure, and software development. With a focus on optimizing performance and scalability, Ivan excels in designing and implementing solutions for complex systems, particularly in MongoDB and cloud-native environments. Known for problem-solving and a results-driven mindset, Ivan combines technical expertise with a commitment to continuous learning, delivering high-quality solutions for modern enterprise applications.
