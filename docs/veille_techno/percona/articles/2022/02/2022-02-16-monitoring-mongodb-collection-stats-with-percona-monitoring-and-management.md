---
title: Monitoring MongoDB Collection Stats with Percona Monitoring and Management
source:
  name: Percona Blog
  url: https://www.percona.com/blog/monitoring-mongodb-collection-stats-with-percona-monitoring-and-management/
  post_id: 25434
source_author:
  name: Ivan Groenewold
  slug: ivan-groenewold
  url: https://www.percona.com/blog/author/ivan-groenewold/
  website: ''
published_at: '2022-02-16T14:51:03'
published_at_gmt: '2022-02-16T14:51:03'
modified_at: '2026-03-26T20:14:54'
modified_at_gmt: '2026-03-26T20:14:54'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
- tag:pmm:2167
categories:
- Insight for DBAs
- MongoDB
- Monitoring
- Percona Software
category_slugs:
- insight-for-dbas
- mongodb
- monitoring
- percona-software
tags:
- MongoDB
- Monitoring
- PMM
tag_slugs:
- mongodb
- monitoring
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Monitoring-MongoDB-Collection-Stats.png
image_count: 6
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Monitoring MongoDB Collection Stats with Percona Monitoring and Management

Source: [Percona Blog](https://www.percona.com/blog/monitoring-mongodb-collection-stats-with-percona-monitoring-and-management/)

Auteur source: [Ivan Groenewold](https://www.percona.com/blog/author/ivan-groenewold/)

Publication: 2022-02-16T14:51:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One approach to get to know a MongoDB system we are not familiar with is to start by checking the busiest collections. MongoDB provides the top administrative command for this purpose. From the mongo shell, we can run db.adminCommand(“top”) to get a snapshot of all the collections at a specific point in time: ...<br> "test.testcol" : {<br> "total" : {<br> "time" : 17432,<br> "count" : 58<br> },<br> "readLock" : {<br> "time" : 358,<br> "count" : 57<br> },<br> "writeLock" : {<br> "time" : 17074,<br> "count" : 1<br> },<br> "queries" : {<br> "time" : 100,<br> "count" : 1<br> },<br> "getmore" : {<br> "time" : 0,<br> "count" : 0<br> },<br> "insert" : {<br> "time" : 17074,<br> "count" : 1<br> },<br> "update" : {<br> "time" : 0,<br> "count" : 0<br> },<br> "remove" : {<br> "time" : 0,<br> "count" : 0<br> },<br> "commands" : {<br> "time" : 0,<br> "count" : 0<br> }<br> }<br> ...<br> 1 . . . < br...

## Structure detectee

- H2: Top Metrics in Percona Monitoring and Management (PMM)
- H2: Collection and Index Stats
- H2: Enabling the Additional Collectors
- H2: Creating Dashboards
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Monitoring MongoDB Collection Stats with Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Monitoring-MongoDB-Collection-Stats.png)
- content / image: [Monitoring MongoDB Collection Stats](https://www.percona.com/wp-content/uploads/2026/03/Monitoring-MongoDB-Collection-Stats-300x157.png)
- content / image: [MongoDB Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2022-02-14-at-1.52.31-PM.png)
- content / image: [PMM Collection and Index Stats](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2022-02-14-at-1.52.39-PM.png)
- content / image: [PMM metrics](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2022-02-14-at-1.52.46-PM.png)
- content / graph_or_chart: [MongoDB dashboard](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2022-02-14-at-1.53.35-PM-1024x444.png)

## Auteur source

Passionate about technology, Ivan Groenewold is a seasoned professional with extensive experience in database management, cloud infrastructure, and software development. With a focus on optimizing performance and scalability, Ivan excels in designing and implementing solutions for complex systems, particularly in MongoDB and cloud-native environments. Known for problem-solving and a results-driven mindset, Ivan combines technical expertise with a commitment to continuous learning, delivering high-quality solutions for modern enterprise applications.
