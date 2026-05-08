---
title: Ten Recommendations for Running Your MongoDB Environment
source:
  name: Percona Blog
  url: https://www.percona.com/blog/ten-recommendations-for-running-your-mongodb-environment/
  post_id: 27292
source_author:
  name: Zelmar Michelini
  slug: zelmar-michelini
  url: https://www.percona.com/blog/author/zelmar-michelini/
  website: ''
published_at: '2023-07-26T12:56:31'
published_at_gmt: '2023-07-26T12:56:31'
modified_at: '2026-03-26T20:14:20'
modified_at_gmt: '2026-03-26T20:14:20'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- Insight for Developers
- MongoDB
category_slugs:
- insight-for-dbas
- insight-for-developers
- mongodb
tags:
- MongoDB
tag_slugs:
- mongodb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Running-Your-MongoDB-Environment.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Ten Recommendations for Running Your MongoDB Environment

Source: [Percona Blog](https://www.percona.com/blog/ten-recommendations-for-running-your-mongodb-environment/)

Auteur source: [Zelmar Michelini](https://www.percona.com/blog/author/zelmar-michelini/)

Publication: 2023-07-26T12:56:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MongoDB is a non-relational document database that provides support for JSON-like storage. It provides a flexible data model allowing you to easily store unstructured data. First released in 2009, it is the most used NoSQL database and has been downloaded more than 325 million times. MongoDB is popular with developers as it is easy to … Continued

## Structure detectee

- H3: Tip #1:
- H3: Always enable authorization for your production environments
- H3: Tip #2:
- H3: Always upgrade to the latest patch set for any given major version
- H3: Tip #3:
- H3: Always use Replica Sets with at least three full data-bearing nodes for your production environments
- H3: Tip #4:
- H3: Avoid the use of Query types or Operators that can be expensive
- H3: Tip #5:
- H3: Think wisely about your index strategy
- H3: Tip #6:
- H3: Watch for changes in query patterns, application changes, or index usage over time
- H3: Tip #7:
- H3: Don’t run multiple mongoD on the same server
- H3: Tip #8:
- H3: Employ a reliable and robust backup strategy
- H3: Tip #9:
- H3: Know when to shard your replica set and why choosing a shard key is important
- H3: Tip #10:
- H3: Don’t throw money at the problem

## Images et graphiques reperes

- featured / image: [Ten Recommendations for Running Your MongoDB Environment](https://www.percona.com/wp-content/uploads/2026/03/Running-Your-MongoDB-Environment.jpg)

## Auteur source

Zelmar, a MongoDB specialist, joined Percona in 2022 as a Support Engineer. With a sharp focus on performance optimization and a knack for troubleshooting, he's recognized for his expertise in MongoDB intricacies, delivering top-notch support.
