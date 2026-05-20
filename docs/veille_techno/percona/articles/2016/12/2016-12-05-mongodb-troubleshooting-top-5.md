---
title: 'MongoDB Troubleshooting: My Top 5'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-troubleshooting-top-5/
  post_id: 15968
source_author:
  name: David Murphy
  slug: david-murphy
  url: https://www.percona.com/blog/author/david-murphy/
  website: ''
published_at: '2016-12-05T19:38:58'
published_at_gmt: '2016-12-05T19:38:58'
modified_at: '2026-03-26T20:22:05'
modified_at_gmt: '2026-03-26T20:22:05'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
- search:pmm
categories:
- MongoDB
category_slugs:
- mongodb
tags:
- greps
- MongoDB troubleshooting
- oplog
- profiler
tag_slugs:
- greps
- mongodb-troubleshooting
- oplog
- profiler
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Troubleshooting-e1480963086227.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB Troubleshooting: My Top 5

Source: [Percona Blog](https://www.percona.com/blog/mongodb-troubleshooting-top-5/)

Auteur source: [David Murphy](https://www.percona.com/blog/author/david-murphy/)

Publication: 2016-12-05T19:38:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll discuss my top five go-to tips for MongoDB troubleshooting. Every DBA has a war chest of their go-to solutions for any support issues they run into for a specific technology. MongoDB is no different. Even if you have picked it because it’s a good fit and it runs well for … Continued

## Structure detectee

- H2: Table of Contents
- H2: Common greps to use
- H4: Is an index being built?
- H4: What’s happening right now?
- H2: Did any elections happen? Why did they happen?
- H2: Is replication lagged, do I have enough oplog?
- H4: Checking lag information:
- H4: Oplog Size and Range:
- H2: Taming the profiler
- H2: CurrentOp and killOp explained
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [MongoDB Troubleshooting: My Top 5](https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Troubleshooting-e1480963086227.jpg)

## Auteur source

David is the Practice Manager for MongoDB @ Percona. He joined Percona in Oct 2015, before that he has been deep in both the MySQL and MongoDB database communities for some time. Other passions include DevOps , tool building, and security.
