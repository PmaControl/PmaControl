---
title: Tuning MongoDB for Bulk Loads
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tuning-mongodb-for-bulk-loads/
  post_id: 22305
source_author:
  name: Ivan Groenewold
  slug: ivan-groenewold
  url: https://www.percona.com/blog/author/ivan-groenewold/
  website: ''
published_at: '2020-05-05T18:18:29'
published_at_gmt: '2020-05-05T18:18:29'
modified_at: '2026-03-26T20:16:43'
modified_at_gmt: '2026-03-26T20:16:43'
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
- Insight for DBAs
- MongoDB
category_slugs:
- insight-for-dbas
- mongodb
tags:
- insight for DBAs
- MongoDB
- WiredTiger
tag_slugs:
- insight-for-dbas
- mongodb
- wiredtiger
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/tuning-mongodb-for-bulk-loads.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Tuning MongoDB for Bulk Loads

Source: [Percona Blog](https://www.percona.com/blog/tuning-mongodb-for-bulk-loads/)

Auteur source: [Ivan Groenewold](https://www.percona.com/blog/author/ivan-groenewold/)

Publication: 2020-05-05T18:18:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

On a recent project, we were tasked with loading several billion records into MongoDB. That prompted us to dig a bit deeper into WiredTiger knobs & turns, which turned out to be a very interesting experience. What we noticed is the load started at a decent rate, but after some time it started to slow … Continued

## Structure detectee

- H2: Understanding WiredTiger Checkpoints
- H2: Eviction Process
- H3: Controlling the WiredTiger Cache Size
- H3: Limiting the Amount of Dirty Pages
- H3: Sizing Eviction Threads
- H2: Eviction Tuning
- H3: Final Words

## Images et graphiques reperes

- featured / image: [Tuning MongoDB for Bulk Loads](https://www.percona.com/wp-content/uploads/2026/03/tuning-mongodb-for-bulk-loads.png)
- content / image: [WiredTiger MongoDB Checkpoints](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-29-at-12.50.11-PM.png)
- content / image: [MongoDB Eviction](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-29-at-12.53.44-PM.png)
- content / image: [Screen-Shot-2020-04-29-at-12.55.26-PM.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-29-at-12.55.26-PM.png)
- content / image: [WiredTiger Cache Pages](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-29-at-12.57.22-PM.png)
- content / image: [Watch the recorded webinar](https://www.percona.com/wp-content/uploads/2026/03/071d8950-51cc-4cd5-9cc0-004a4e54379d.png)

## Auteur source

Passionate about technology, Ivan Groenewold is a seasoned professional with extensive experience in database management, cloud infrastructure, and software development. With a focus on optimizing performance and scalability, Ivan excels in designing and implementing solutions for complex systems, particularly in MongoDB and cloud-native environments. Known for problem-solving and a results-driven mindset, Ivan combines technical expertise with a commitment to continuous learning, delivering high-quality solutions for modern enterprise applications.
